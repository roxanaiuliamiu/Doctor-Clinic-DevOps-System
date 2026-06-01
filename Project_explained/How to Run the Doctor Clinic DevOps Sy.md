# How to Run the Doctor Clinic DevOps System

This guide explains how to run the **Doctor Clinic DevOps System** locally and through the **GitLab CI/CD pipeline**.

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Clone the Repository](#clone-the-repository)
3. [Check Your Branch](#check-your-branch)
4. [Run the Project Locally](#run-the-project-locally)
5. [Run the CI/CD Pipeline](#run-the-cicd-pipeline)
6. [Run Development Deployment](#run-development-deployment)
7. [Run Production Deployment](#run-production-deployment)
8. [Required GitLab Variables](#required-gitlab-variables)
9. [Useful Commands](#useful-commands)
10. [Troubleshooting](#troubleshooting)

---

## Prerequisites

Before running the project, make sure you have:

- Git
- Docker Desktop or Docker Engine
- access to the repository
- access to GitLab CI/CD
- Kubernetes cluster access for deployment
- Helm
- kubectl

Optional but useful:
- Node.js
- npm
- PHP
- Composer

---

## Clone the Repository

Clone the repository from GitHub or GitLab.

### From GitHub
```bash
git clone <your-github-repository-url>
cd Doctor-Clinic-DevOps-System
```

### From GitLab
```bash
git clone <your-gitlab-repository-url>
cd Doctor-Clinic-DevOps-System
```

---

## Check Your Branch

Check the current branch:

```bash
git branch --show-current
```

Main branches used in this project:
- `dev`
- `main`

---

## Run the Project Locally

There are two common ways to run the project locally.

### Option 1: Run with Docker
If the project includes a Dockerfile, build the image:

```bash
docker build -t doctor-clinic-app .
```

Then run it:

```bash
docker run -p 8080:80 doctor-clinic-app
```

If the application uses Laravel, PHP-FPM, MySQL, or Nginx in separate containers, run them according to your Docker Compose or Kubernetes setup if available.

---

### Option 2: Run basic tool checks locally
Check Docker:

```bash
docker --version
```

Check kubectl:

```bash
kubectl version --client
```

Check Helm:

```bash
helm version
```

Check Node.js:

```bash
node --version
npm --version
```

Check PHP and Composer if installed locally:

```bash
php --version
composer --version
```

---

## Run the CI/CD Pipeline

The main way to run this project is through **GitLab CI/CD**.

The pipeline stages are:
- `test`
- `build`
- `deploy_dev`
- `deploy_prod`

### Pipeline behavior by branch

#### On `dev`
Runs:
- `test`
- `build`
- `deploy_dev`

#### On `main`
Runs:
- `test`
- `build`
- `deploy_prod` as a **manual** job

---

## Run Development Deployment

To trigger development deployment:

```bash
git checkout dev
git add .
git commit -m "Run development pipeline"
git push origin dev
git push gitlab dev
```

This will trigger:
1. test
2. build
3. development deploy

The development deployment uses:
- `KUBE_CONFIG_DEV`
- Helm chart files
- `values.yaml`
- `values-dev.yaml`

---

## Run Production Deployment

To prepare a production deployment:

```bash
git checkout main
git add .
git commit -m "Prepare production pipeline"
git push origin main
git push gitlab main
```

This will trigger:
1. test
2. build
3. manual production deployment job

### To actually deploy to production
1. open the project in GitLab
2. open the latest pipeline for `main`
3. locate the `deploy_prod` job
4. click **Run** manually

This job uses:
- `KUBE_CONFIG_PROD`
- Helm chart files
- `values.yaml`
- `values-prod.yaml`

---

## Required GitLab Variables

To run deployment jobs, you must configure these variables in GitLab:

### Required
- `KUBE_CONFIG_DEV`
- `KUBE_CONFIG_PROD`

These should contain the **base64-encoded kubeconfig** for each cluster/environment.

### Predefined GitLab variables used automatically
The pipeline also uses GitLab predefined variables such as:
- `CI_JOB_TOKEN`
- `CI_REGISTRY`
- `CI_REGISTRY_IMAGE`
- `CI_COMMIT_SHORT_SHA`

These are provided automatically by GitLab CI/CD during pipeline execution.

---

## Useful Commands

### Build Docker image locally
```bash
docker build -t doctor-clinic-app .
```

### Show running containers
```bash
docker ps
```

### Show all containers
```bash
docker ps -a
```

### Validate Helm chart
```bash
helm lint ./helm/doctor-clinic -f ./helm/doctor-clinic/values.yaml -f ./helm/doctor-clinic/values-dev.yaml
```

### Validate production Helm chart
```bash
helm lint ./helm/doctor-clinic -f ./helm/doctor-clinic/values.yaml -f ./helm/doctor-clinic/values-prod.yaml
```

### Deploy manually with Helm
Development:
```bash
helm upgrade --install doctor-clinic ./helm/doctor-clinic \
  -n doctor-clinic-dev \
  --create-namespace \
  -f ./helm/doctor-clinic/values.yaml \
  -f ./helm/doctor-clinic/values-dev.yaml
```

Production:
```bash
helm upgrade --install doctor-clinic ./helm/doctor-clinic \
  -n doctor-clinic-prod \
  --create-namespace \
  -f ./helm/doctor-clinic/values.yaml \
  -f ./helm/doctor-clinic/values-prod.yaml
```

### Watch deployment rollout
Development:
```bash
kubectl rollout status deployment/doctor-clinic-web -n doctor-clinic-dev --timeout=180s
```

Production:
```bash
kubectl rollout status deployment/doctor-clinic-web -n doctor-clinic-prod --timeout=180s
```

---

## Troubleshooting

### 1. Pipeline stuck in pending
Check:
- runner is online
- runner has the correct tags
- runner has recent contact with GitLab
- runner is not paused

---

### 2. Docker registry login fails
Make sure the pipeline uses:

```sh
echo "$CI_JOB_TOKEN" | docker login -u "gitlab-ci-token" "$CI_REGISTRY" --password-stdin
```

Do not use PowerShell-style variables on a Linux Docker runner.

---

### 3. `kubectl: not found`
This means the deploy job is using the wrong container image.

Use a job image that includes:
- kubectl
- helm

---

### 4. Helm lint fails
This usually means the values structure does not match the templates.

Examples of required keys:
- `service.web.port`
- `service.mysql.port`
- `secret.APP_KEY`
- `secret.DB_PASSWORD`
- `secret.MYSQL_ROOT_PASSWORD`

---

### 5. Production deploy should not run automatically
This is expected.  
`deploy_prod` is configured as a **manual job** for safety.

---

## Recommended Execution Order

For a safe workflow, use this order:

1. validate the Helm chart
2. push to `dev`
3. confirm test/build/deploy_dev
4. push to `main`
5. confirm test/build
6. manually trigger `deploy_prod`

---

## Summary

To run this project successfully:

- use Docker for local and CI builds
- use GitLab CI/CD for automated pipeline execution
- use `dev` for development deployment
- use `main` for production preparation
- manually trigger production deployment
- store kubeconfig files in GitLab variables
- validate Helm charts before deployment

This is the intended run workflow for the current project stage.