# SSH and kubeconfig setup steps

## Goal
Connect from your local Windows machine to the Proxmox host, then from the Proxmox host to the VM, then copy `/home/ubuntu/.kube/config` to your local machine.

---

## Part 1 — From local Windows machine to Proxmox host

### Test SSH from local machine to Proxmox host
Run in PowerShell:

```powershell
ssh -i $HOME\.ssh\id_ed25519 root@51.158.200.127
```

If this works without password, your SSH key is correctly installed for `root`.

If it asks for a password, your local public key is probably not yet installed in:

```bash
/root/.ssh/authorized_keys
```

---

## Part 2 — Install your Windows public key on the Proxmox host

### On Windows: show your public key
Run:

```powershell
Get-Content $HOME\.ssh\id_ed25519.pub
```

Copy the full output.

### On the Proxmox host: add that key
Run:

```bash
mkdir -p /root/.ssh
chmod 700 /root/.ssh
nano /root/.ssh/authorized_keys
```

Paste your Windows public key as a new line.

Then save and run:

```bash
chmod 600 /root/.ssh/authorized_keys
```

### Test again from Windows
Run:

```powershell
ssh -i $HOME\.ssh\id_ed25519 root@51.158.200.127
```

---

## Part 3 — From Proxmox host to the VM

### Test VM reachability
Run on the Proxmox host:

```bash
ping -c 4 10.10.10.20
```

### Test SSH to the VM
Run:

```bash
ssh ubuntu@10.10.10.20
```

If prompted with host authenticity, type:

```text
yes
```

If prompted for password, enter the VM password for user `ubuntu`.

---

## Part 4 — Copy kubeconfig from VM to Proxmox host

Run on the Proxmox host:

```bash
scp ubuntu@10.10.10.20:/home/ubuntu/.kube/config /root/kubeconfig-prod.yaml
```

This copies the kubeconfig file from the VM to the Proxmox host.

---

## Part 5 — Copy kubeconfig from Proxmox host to local Windows machine

Run on Windows PowerShell:

```powershell
scp -i $HOME\.ssh\id_ed25519 root@51.158.200.127:/root/kubeconfig-prod.yaml $HOME\Downloads\kubeconfig-prod.yaml
```

---

## Part 6 — Verify the file locally

Run in PowerShell:

```powershell
Get-Content $HOME\Downloads\kubeconfig-prod.yaml | Select-Object -First 20
```

You should see YAML starting with:

```yaml
apiVersion: v1
clusters:
```

---

## Part 7 — Put kubeconfig into GitLab

Go to:

- Settings
- CI/CD
- Variables

Add a variable:

- **Key**: `KUBE_CONFIG_PROD`
- **Type**: `File`

Paste the full contents of `kubeconfig-prod.yaml`.

---

## Part 8 — Use kubeconfig in `.gitlab-ci.yml`

Use this in your deploy job:

```yaml
deploy_prod:
  image: dtzar/helm-kubectl:3.14.4
  stage: deploy_prod
  tags:
    - docker
    - ci
  script:
    - test -n "$KUBE_CONFIG_PROD" || (echo "KUBE_CONFIG_PROD is not set" && exit 1)
    - export KUBECONFIG="$KUBE_CONFIG_PROD"
    - kubectl cluster-info
    - kubectl get nodes -o wide
    - helm version
    - helm lint ./helm/doctor-clinic -f ./helm/doctor-clinic/values.yaml -f ./helm/doctor-clinic/values-prod.yaml
    - helm upgrade --install doctor-clinic ./helm/doctor-clinic -n doctor-clinic-prod --create-namespace -f ./helm/doctor-clinic/values.yaml -f ./helm/doctor-clinic/values-prod.yaml --set image.repository="$IMAGE_NAME" --set image.tag="$IMAGE_TAG"
```

---

## Optional — Direct ProxyJump from local machine to VM

If local SSH to Proxmox works with your key, you can try:

```powershell
scp -i $HOME\.ssh\id_ed25519 -o "ProxyJump=root@51.158.200.127" ubuntu@10.10.10.20:/home/ubuntu/.kube/config $HOME\Downloads\kubeconfig-prod.yaml
```

This skips the manual two-step copy.

Use this only after local SSH to Proxmox is working.

---

## Troubleshooting

### If local SSH to Proxmox says `Permission denied`
Your local public key is not installed correctly on the Proxmox host, or permissions are wrong.

Check on Proxmox:

```bash
ls -ld /root /root/.ssh
ls -l /root/.ssh/authorized_keys
```

Expected:

- `/root/.ssh` should be `700`
- `/root/.ssh/authorized_keys` should be `600`

### If Proxmox cannot SSH to VM
Check on Proxmox:

```bash
ping -c 4 10.10.10.20
ssh -o ConnectTimeout=5 ubuntu@10.10.10.20
```

### If local machine cannot reach `10.10.10.20`
That is normal in your setup. Your local machine is not on the same private network as the VM. Use the Proxmox host as the jump host.

---

## Best working flow
1. Windows -> Proxmox
2. Proxmox -> VM
3. VM kubeconfig -> Proxmox
4. Proxmox kubeconfig -> Windows
5. Windows -> GitLab File variable