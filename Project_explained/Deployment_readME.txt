# Doctor Clinic DevOps System - Deployment Guide

This README only covers how the application is deployed:

- locally, using Docker Compose
- on the VM, using Terraform, GitLab Runner, GitLab CI/CD, Helm, and Kubernetes

---

## 1. Local deployment

The project includes a `docker-compose.yml` file that starts the application locally with three containers:

- `app` - Laravel/PHP-FPM application
- `nginx` - web server
- `mysql` - MySQL database

### Local architecture

- The Laravel app runs inside the `app` container.
- Nginx serves the app and forwards PHP requests to the `app` container.
- MySQL runs in its own container with a persistent Docker volume.
- The application is exposed on:
  - `http://localhost:8080`
- The MySQL database is exposed on:
  - `localhost:3307`

### Services defined in Docker Compose

- **app**
  - built from the root `Dockerfile`
  - mounts the repository into `/var/www`
- **nginx**
  - uses `nginx:alpine`
  - uses `docker/nginx/default.conf`
  - maps port `8080` to container port `80`
- **mysql**
  - uses `mysql:8.0`
  - creates a database and user from environment values in `docker-compose.yml`
  - stores database data in the `doktors_mysql_data` volume

### Local prerequisites

Make sure you have installed:

- Docker
- Docker Compose

### Local deployment steps

1. Clone the repository:
   ```bash
   git clone https://github.com/roxanaiuliamiu/Doctor-Clinic-DevOps-System.git
   cd Doctor-Clinic-DevOps-System
   ```

2. Create the application environment file:
   ```bash
   cp .env.example .env
   ```

3. Start the containers:
   ```bash
   docker compose up -d --build
   ```

4. Generate the Laravel application key:
   ```bash
   docker compose exec app php artisan key:generate
   ```

5. Run database migrations:
   ```bash
   docker compose exec app php artisan migrate
   ```

6. Open the application in your browser:
   ```text
   http://localhost:8080
   ```

### Local database connection

The Docker Compose setup defines MySQL with these values:

- database: `doktors`
- username: `doktors_user`
- password: `doktors_pass`
- root password: `root`

If needed, align the values in your `.env` file with the Docker Compose service:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=doktors
DB_USERNAME=doktors_user
DB_PASSWORD=doktors_pass
```

### Stopping local deployment

```bash
docker compose down
```

To also remove the database volume:

```bash
docker compose down -v
```

---

## 2. VM deployment

The VM deployment is structured around a CI/CD-driven workflow:

1. Terraform provisions a VM in Proxmox
2. The VM is intended to host a GitLab Runner
3. GitLab CI builds and pushes the application Docker image
4. GitLab CI deploys the application to Kubernetes using Helm

This means the VM is not just a machine that runs the Laravel app directly.  
Instead, it acts as part of the deployment infrastructure, mainly by supporting the runner and Kubernetes deployment process.

---

## 3. Provisioning the VM with Terraform

The repository contains Terraform files under:

```text
terraform/proxmox
```

These files define a Proxmox VM resource named:

- `gitlab_runner`

### What Terraform provisions

The Terraform configuration clones a Proxmox VM template and creates a VM with configurable:

- VM ID
- VM name
- CPU cores
- memory
- datastore
- network bridge

### Example Terraform variables

The example file is:

```text
terraform/proxmox/terraform.tfvars.example
```

It includes values such as:

- `proxmox_endpoint`
- `proxmox_username`
- `proxmox_password`
- `proxmox_node`
- `network_bridge`
- `template_vm_id`
- `runner_vm_id`
- `runner_vm_name`

### Provisioning steps

1. Go to the Terraform folder:
   ```bash
   cd terraform/proxmox
   ```

2. Copy the example variables file:
   ```bash
   cp terraform.tfvars.example terraform.tfvars
   ```

3. Edit `terraform.tfvars` with your Proxmox environment values.

4. Initialize Terraform:
   ```bash
   terraform init
   ```

5. Review the execution plan:
   ```bash
   terraform plan
   ```

6. Create the VM:
   ```bash
   terraform apply
   ```

After this, the VM should exist in Proxmox and can be used as the GitLab Runner host.

---

## 4. CI/CD deployment flow on the VM

The repository includes a GitLab CI pipeline in:

```text
.gitlab-ci.yml
```

The pipeline has three stages:

- `test`
- `build`
- `deploy`

### Test stage

On the `dev` and `main` branches, the pipeline:

- copies `.env.example` to `.env`
- builds a test Docker image
- runs:
  - `php artisan key:generate --force`
  - `php artisan test`

### Build stage

The pipeline then:

- logs in to the GitLab container registry
- builds the application Docker image
- pushes the image to the registry

The image name is:

```text
registry.gitlab.com/miulina/doctor-clinic-devops-system/doctor-clinic-app
```

The tag is based on:

```text
$CI_COMMIT_SHORT_SHA
```

### Deploy stage

Deployment is performed by GitLab Runner jobs tagged for internal Kubernetes execution.

Two deployment targets are defined:

- `deploy_dev` -> runs for the `dev` branch
- `deploy_prod` -> runs for the `main` branch

These jobs:

- connect to Kubernetes using a configured `KUBECONFIG`
- delete any previous migration job
- run `helm upgrade --install`
- wait for MySQL and application deployments to become ready
- wait for the migration job to complete

---

## 5. Kubernetes / Helm deployment on the VM side

The actual application deployment is managed with the Helm chart in:

```text
helm/doctor-clinic
```

A helper script is also included:

```text
scripts/deploy-k8s.sh
```

That script:

- creates the `doctor-clinic` namespace if needed
- runs:
  ```bash
  helm upgrade --install doctor-clinic ./helm/doctor-clinic -n doctor-clinic
  ```

### Helm environments

The chart includes:

- `values.yaml` - base values
- `values-dev.yaml` - development overrides
- `values-prod.yaml` - production overrides

### Dev deployment

For the `dev` branch, the pipeline deploys to:

```text
doctor-clinic-dev
```

Characteristics:

- 1 application replica
- debug enabled
- development database name

### Prod deployment

For the `main` branch, the pipeline deploys to:

```text
doctor-clinic-prod
```

Characteristics:

- 2 application replicas
- debug disabled
- production database name
- larger persistent volume size

### Secrets used during deployment

The deployment injects runtime secrets such as:

- `APP_KEY`
- `DB_PASSWORD`
- `MYSQL_ROOT_PASSWORD`

These are passed into Helm during the pipeline and should be stored securely in GitLab CI/CD variables.

---

## 6. Deploy helper image

The repository also includes `Dockerfile.deploy`.

This image installs:

- `bash`
- `curl`
- `git`
- `kubectl`
- `helm`

Its role is to provide a deployment environment for Kubernetes/Helm operations, which supports the VM-based CI/CD workflow.

---

## 7. Summary of deployment model

### Local deployment
Used for development on a workstation.

Stack:
- Docker Compose
- Laravel app container
- Nginx container
- MySQL container

Access:
- application: `http://localhost:8080`
- MySQL: `localhost:3307`

### VM deployment
Used for infrastructure-backed automated deployment.

Stack:
- Proxmox VM provisioned by Terraform
- GitLab Runner hosted on the VM
- GitLab CI/CD pipeline
- Docker image build and push
- Kubernetes deployment through Helm

Targets:
- `doctor-clinic-dev` for `dev`
- `doctor-clinic-prod` for `main`

---

## 8. Useful commands

### Local
```bash
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose down
```

### Terraform
```bash
cd terraform/proxmox
terraform init
terraform plan
terraform apply
```

### Kubernetes / Helm
```bash
kubectl create namespace doctor-clinic --dry-run=client -o yaml | kubectl apply -f -
helm upgrade --install doctor-clinic ./helm/doctor-clinic -n doctor-clinic
```

---

## 9. Notes

- Local deployment is fully containerized with Docker Compose.
- VM deployment is oriented around CI/CD automation rather than manual app hosting.
- The VM infrastructure is provisioned in Proxmox.
- Application rollout in non-local environments is performed through Kubernetes and Helm.