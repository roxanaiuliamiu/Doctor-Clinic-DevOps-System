# Doctor Clinic Production Deployment Runbook

## Purpose
This document records the main problems encountered during production deployment, the fixes applied, and the key commands used to verify and recover the environment.

---

## Environment
- Kubernetes namespace: `doctor-clinic-prod`
- Kubernetes access on runner host:
  - `KUBECONFIG=/home/gitlab-runner/.kube/config`
- Deployment executed from GitLab CI using the internal shell runner on the VM host.

---

## Main problems found

### 1. GitLab deploy job could not access the private Kubernetes cluster
**Cause**
- The deploy job was originally running in an environment that could not reach the private cluster network.

**Fix**
- Use the internal GitLab shell runner on the VM host.
- Ensure the runner has `kubectl`, `helm`, and the kubeconfig file.

**Verification commands**
```bash
kubectl cluster-info
kubectl get nodes -o wide
kubectl version --client
helm version
```

---

### 2. MySQL user was not created
**Cause**
- Helm template expected:
  ```yaml
  .Values.mysql.user
  ```
- But `values.yaml` used:
  ```yaml
  mysql.username
  ```
- And `values-prod.yaml` did not define the MySQL user at all.

**Fix**
- Standardize on:
  ```yaml
  mysql.user
  ```

**Correct values**
```yaml
mysql:
  image: "mysql:8.0"
  database: "doctors_db"
  user: "doctors_user"
```

Production override:
```yaml
mysql:
  database: "doctors_prod"
  user: "doctors_user"
```

**Why this mattered**
- Laravel migration job used:
  - `DB_HOST=mysql`
  - `DB_DATABASE=doctors_prod`
  - `DB_USERNAME=doctors_user`
- Without `MYSQL_USER`, the MySQL container did not create the expected database user.

---

### 3. Old MySQL persistent data kept the bad initialization state
**Cause**
- MySQL Docker image only creates the database and user on first startup with an empty data directory.
- After the bad configuration was applied once, the PVC kept the incorrect state.

**Fix**
- Delete the MySQL Deployment and PVC so MySQL initializes from scratch with the corrected values.

**Commands**
```bash
KUBECONFIG=/home/gitlab-runner/.kube/config kubectl delete deployment -n doctor-clinic-prod mysql
KUBECONFIG=/home/gitlab-runner/.kube/config kubectl delete pvc -n doctor-clinic-prod mysql-pvc
```

**Warning**
- This deletes the MySQL data.
- Only safe for a fresh/test environment or when data loss is acceptable.

---

### 4. Helm upgrade failed on the migration Job because Jobs are immutable
**Cause**
- Kubernetes does not allow changing `spec.template` on an existing Job.

**Error**
```text
Job.batch "doctor-clinic-migrate" is invalid: field is immutable
```

**Fix**
- Delete the old migration Job before each deploy.

**Command**
```bash
kubectl delete job doctor-clinic-migrate -n doctor-clinic-prod --ignore-not-found=true
```

---

### 5. Deploy pipeline succeeded before checking whether migrations finished
**Cause**
- CI only checked the web and MySQL Deployments.
- It did not wait for the migration Job to complete.

**Fix**
- Add a wait step for the migration Job and print its logs.

**Commands**
```bash
kubectl wait --for=condition=complete job/doctor-clinic-migrate -n doctor-clinic-prod --timeout=180s
kubectl logs -n doctor-clinic-prod job/doctor-clinic-migrate
```

---

### 6. MySQL rollout became unreliable during updates
**Cause**
- MySQL is stateful and uses a PVC.
- Rolling updates can be awkward with a single replica and persistent storage.
- Readiness configuration can affect rollout timing.

**Fixes applied / recommended**
- Add a MySQL readiness probe.
- Prefer `strategy: Recreate` for the MySQL Deployment to avoid overlapping old and new Pods with the same PVC.

**Recommended MySQL deployment settings**
```yaml
strategy:
  type: Recreate
```

```yaml
readinessProbe:
  tcpSocket:
    port: 3306
  initialDelaySeconds: 20
  periodSeconds: 5
  timeoutSeconds: 3
  failureThreshold: 12
```

---

## Final working CI deploy job

```yaml
deploy_prod:
  stage: deploy_prod
  tags:
    - internal
  script:
    - export KUBECONFIG=/home/gitlab-runner/.kube/config
    - echo "Checking cluster access"
    - kubectl cluster-info
    - kubectl get nodes -o wide
    - kubectl version --client
    - helm version
    - helm lint ./helm/doctor-clinic -f ./helm/doctor-clinic/values.yaml -f ./helm/doctor-clinic/values-prod.yaml
    - kubectl delete job doctor-clinic-migrate -n doctor-clinic-prod --ignore-not-found=true
    - helm upgrade --install doctor-clinic ./helm/doctor-clinic -n doctor-clinic-prod --create-namespace -f ./helm/doctor-clinic/values.yaml -f ./helm/doctor-clinic/values-prod.yaml --set image.repository="$IMAGE_NAME" --set image.tag="$IMAGE_TAG"
    - kubectl rollout status deployment/doctor-clinic-web -n doctor-clinic-prod --timeout=180s
    - kubectl rollout status deployment/mysql -n doctor-clinic-prod --timeout=300s
    - kubectl wait --for=condition=complete job/doctor-clinic-migrate -n doctor-clinic-prod --timeout=180s
    - kubectl logs -n doctor-clinic-prod job/doctor-clinic-migrate
    - kubectl get all -n doctor-clinic-prod
  when: manual
  only:
    - main
```

---

## Main verification commands

### Check cluster access
```bash
KUBECONFIG=/home/gitlab-runner/.kube/config kubectl cluster-info
KUBECONFIG=/home/gitlab-runner/.kube/config kubectl get nodes -o wide
```

### Check resources in production namespace
```bash
KUBECONFIG=/home/gitlab-runner/.kube/config kubectl get all -n doctor-clinic-prod
```

### Check pods
```bash
KUBECONFIG=/home/gitlab-runner/.kube/config kubectl get pods -n doctor-clinic-prod
```

### Check jobs
```bash
KUBECONFIG=/home/gitlab-runner/.kube/config kubectl get jobs -n doctor-clinic-prod
```

### Check migration logs
```bash
KUBECONFIG=/home/gitlab-runner/.kube/config kubectl logs -n doctor-clinic-prod job/doctor-clinic-migrate
```

### Check MySQL logs
```bash
KUBECONFIG=/home/gitlab-runner/.kube/config kubectl logs -n doctor-clinic-prod deploy/mysql
```

### Describe failed pod
```bash
KUBECONFIG=/home/gitlab-runner/.kube/config kubectl describe pod -n doctor-clinic-prod <pod-name>
```

---

## Successful final state
A successful production deployment should show:

- `doctor-clinic-web` Deployment:
  - `2/2` available
- `mysql` Deployment:
  - `1/1` available
- `doctor-clinic-migrate` Job:
  - `Complete 1/1`

Example checks:
```bash
kubectl get deployments -n doctor-clinic-prod
kubectl get jobs -n doctor-clinic-prod
kubectl get pods -n doctor-clinic-prod
```

---

## Final conclusion
The production deployment succeeded after:
1. running deploys from the internal runner with cluster access
2. fixing the Helm values key mismatch for the MySQL user
3. deleting the old MySQL PVC so the database could initialize correctly
4. deleting the old migration Job before each Helm upgrade
5. waiting for the migration Job to complete in CI
6. improving MySQL rollout behavior for persistent storage