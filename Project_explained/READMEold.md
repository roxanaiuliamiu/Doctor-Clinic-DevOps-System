# Doctor Clinic DevOps System

A CI/CD-enabled deployment workflow for the **Doctor Clinic** application using **GitLab CI/CD**, **Docker**, **GitLab Container Registry**, **Helm**, and **Kubernetes**.

This project demonstrates how to:
- build and test an application in GitLab CI/CD
- package the application as a Docker image
- push the image to the GitLab Container Registry
- validate Kubernetes deployment manifests with Helm
- separate **development** and **production** deployment flows
- use a GitLab Docker runner to automate the pipeline

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Architecture](#architecture)
3. [Architecture Diagram](#architecture-diagram)
4. [Repository Structure](#repository-structure)
5. [CI/CD Pipeline Overview](#cicd-pipeline-overview)
6. [Branch Strategy](#branch-strategy)
7. [Installation and Prerequisites](#installation-and-prerequisites)
8. [Configuration](#configuration)
9. [Usage](#usage)
10. [Deployment Flow](#deployment-flow)
11. [Helm Configuration](#helm-configuration)
12. [Troubleshooting and Debugging Summary](#troubleshooting-and-debugging-summary)
13. [Security Notes](#security-notes)
14. [Current Status](#current-status)
15. [Next Improvements](#next-improvements)
16. [Exam Explanation Summary](#exam-explanation-summary)

---

## Project Overview

The goal of this project is to automate the build and deployment lifecycle of the **Doctor Clinic** application.

At this stage, the system supports:

- **test** stage
- **build** stage
- **development deployment**
- **production deployment**
- Helm chart validation with environment-specific values
- registry authentication using GitLab CI predefined variables

The pipeline runs on a **GitLab Docker runner** and uses different container images depending on the job:
- a Docker image for test/build work
- a Helm/Kubectl image for deployment work

This creates a clean separation between:
- application packaging
- infrastructure validation
- environment deployment

---

## Architecture

The system is composed of the following parts:

### 1. Source Code Repository
The application code, Helm chart, and CI/CD configuration are stored in the Git repository.

### 2. GitLab CI/CD Pipeline
GitLab automatically triggers the pipeline when code is pushed to selected branches.

### 3. GitLab Runner
A **Docker executor runner** picks up jobs and executes them inside Linux containers.

### 4. Docker Build Process
The application is built into a Docker image during the `build` stage.

### 5. GitLab Container Registry
The built image is pushed to GitLab’s container registry. GitLab provides predefined variables and a CI job token for job authentication. ([docs.gitlab.com](https://docs.gitlab.com/ci/variables/predefined_variables/?utm_source=openai))

### 6. Helm Chart
The Helm chart defines Kubernetes resources such as:
- Services
- Secrets
- Deployments
- Namespaces
- environment-specific configuration

### 7. Kubernetes Cluster
Deployment jobs connect to Kubernetes using kubeconfig data stored in GitLab CI/CD variables and apply the Helm release.

---

## Architecture Diagram

### High-Level Flow

```text
Developer
   |
   | git push
   v
GitHub / GitLab Repository
   |
   | triggers pipeline
   v
GitLab CI/CD
   |
   v
GitLab Runner (Docker Executor, Linux)
   |
   +----------------------+
   |                      |
   | test/build jobs      | deploy jobs
   | image: docker:24     | image: helm-kubectl
   v                      v
Docker Build          Helm + kubectl
   |                      |
   v                      v
GitLab Container      Kubernetes Cluster
Registry              (dev / prod namespaces)
```

### Branch-Based Deployment Logic

```text
dev branch
   ├── test
   ├── build
   └── deploy_dev

main branch
   ├── test
   ├── build
   └── deploy_prod (manual)
```

### Helm Deployment Flow

```text
GitLab variable (KUBE_CONFIG_DEV / KUBE_CONFIG_PROD)
   |
   v
base64 decode inside CI job
   |
   v
temporary kubeconfig file
   |
   v
export KUBECONFIG
   |
   v
helm lint
   |
   v
helm upgrade --install
   |
   v
kubectl rollout status
```

---

## Repository Structure

Example project structure:

```text
.
├── .gitlab-ci.yml
├── README.md
├── helm/
│   └── doctor-clinic/
│       ├── Chart.yaml
│       ├── values.yaml
│       ├── values-dev.yaml
│       ├── values-prod.yaml
│       └── templates/
│           ├── deployment-web.yaml
│           ├── service-web.yaml
│           ├── service-mysql.yaml
│           ├── secret.yaml
│           └── ...
├── Dockerfile
└── application source files
```

---

## CI/CD Pipeline Overview

The GitLab pipeline is defined in `.gitlab-ci.yml`.

### Stages

```yaml
stages:
  - test
  - build
  - deploy_dev
  - deploy_prod
```

### Stage Responsibilities

#### `test`
Checks the execution environment and validates tool availability using Docker containers.

Typical checks:
- Docker CLI
- PHP container
- Composer container

#### `build`
Builds the application image and pushes it to the GitLab Container Registry.

#### `deploy_dev`
Deploys to the development namespace using Helm and Kubernetes.

#### `deploy_prod`
Deploys to the production namespace. This job is **manual** to reduce risk.

GitLab documents predefined variables for job scripts and provides `CI_JOB_TOKEN` as a short-lived token available while the job runs. In shell scripts, CI variables are referenced as `$VARIABLE`. ([docs.gitlab.com](https://docs.gitlab.com/ci/variables/predefined_variables/?utm_source=openai))

---

## Branch Strategy

Two branches are used:

### `dev`
Used for development and continuous deployment to the development environment.

Runs:
- `test`
- `build`
- `deploy_dev`

### `main`
Used for stable integration and production release preparation.

Runs:
- `test`
- `build`
- `deploy_prod` as a **manual** action

This ensures:
- fast feedback in development
- controlled production releases

---

## Installation and Prerequisites

To work with this project locally or in CI, the following tools are relevant.

### Local Development Tools
Recommended:
- Git
- Docker Desktop or Docker Engine
- Kubernetes cluster access
- Helm
- kubectl

### CI Runner Requirements
The active GitLab runner for this project uses:
- **Docker executor**
- **Linux environment**
- tags: `docker`, `ci`

That means CI jobs run inside containers, not in the local Windows shell.

### Verify Local Docker
```bash
docker --version
docker ps -a
```

### Verify Kubernetes Tools
```bash
kubectl version --client
helm version
```

---

## Configuration

### 1. GitLab CI/CD Variables

The following GitLab variables are required for deployment:

- `KUBE_CONFIG_DEV`
- `KUBE_CONFIG_PROD`

These store **base64-encoded kubeconfig files**.

The pipeline decodes them during the job and exports `KUBECONFIG`.

### 2. GitLab Predefined Variables

The pipeline also relies on predefined GitLab variables such as:
- `CI_JOB_TOKEN`
- `CI_REGISTRY`
- `CI_REGISTRY_IMAGE`
- `CI_COMMIT_SHORT_SHA`

GitLab provides these variables automatically in pipelines. ([docs.gitlab.com](https://docs.gitlab.com/ci/variables/predefined_variables/?utm_source=openai))

### 3. Registry Authentication

The build job uses:

```sh
echo "$CI_JOB_TOKEN" | docker login -u "gitlab-ci-token" "$CI_REGISTRY" --password-stdin
```

This uses GitLab’s CI job token to authenticate to the container registry. GitLab documents both the predefined variable model and CI job token behavior. ([docs.gitlab.com](https://docs.gitlab.com/ci/variables/predefined_variables/?utm_source=openai))

---

## Usage

### Push to Development
Push code to the `dev` branch:

```bash
git checkout dev
git add .
git commit -m "Update development pipeline"
git push origin dev
git push gitlab dev
```

This triggers:
- test
- build
- deploy to development

### Push to Main
Push code to the `main` branch:

```bash
git checkout main
git add .
git commit -m "Prepare production release"
git push origin main
git push gitlab main
```

This triggers:
- test
- build
- manual production deploy job

### Trigger Production Deployment
After the `main` pipeline succeeds:
1. open the pipeline in GitLab
2. locate `deploy_prod`
3. run it manually

---

## Deployment Flow

### Development Deployment
The `deploy_dev` job:
1. checks that `KUBE_CONFIG_DEV` exists
2. decodes the kubeconfig
3. sets the `KUBECONFIG` environment variable
4. runs `helm lint`
5. performs `helm upgrade --install`
6. checks rollout status

### Production Deployment
The `deploy_prod` job performs the same logic but targets the production namespace and is manually triggered.

Kubernetes documents `kubectl rollout status` as the command to watch the status of the latest rollout. ([kubernetes.io](https://kubernetes.io/docs/reference/kubectl/generated/kubectl_rollout/kubectl_rollout_status?utm_source=openai))

---

## Helm Configuration

The Helm chart uses:
- `values.yaml` for base configuration
- `values-dev.yaml` for development overrides
- `values-prod.yaml` for production overrides

### Important Values Structure

The templates require nested keys such as:

```yaml
service:
  web:
    port: 80
    targetPort: 80
  mysql:
    port: 3306
    targetPort: 3306
```

and:

```yaml
secret:
  APP_KEY: "..."
  DB_PASSWORD: "..."
  MYSQL_ROOT_PASSWORD: "..."
```

### Why This Matters
During debugging, Helm failed because template files expected values like:
- `.Values.service.web.port`
- `.Values.service.mysql.port`
- `.Values.secret.APP_KEY`

If these keys are missing or structured differently, `helm lint` fails before deployment.

---

## Troubleshooting and Debugging Summary

This project required debugging in several layers.

### 1. Runner Environment Mismatch
At first, it looked like commands should work in PowerShell, but the real CI environment was a **Linux Docker runner**.

#### Impact
PowerShell syntax such as:

```powershell
$env:CI_JOB_TOKEN
```

did not work.

#### Fix
Use shell syntax:

```sh
$CI_JOB_TOKEN
```

GitLab documents that job-script variable access in Bash/sh uses `$VARIABLE`. ([docs.gitlab.com](https://docs.gitlab.com/ci/variables/job_scripts/?utm_source=openai))

---

### 2. Runner Availability Issues
There were cases where jobs stayed pending because the runner had stopped contacting GitLab.

#### Lesson
A runner can appear in the UI but still be stale if the last contact time is too old.

---

### 3. Wrong Job Image for Deployment
Deployment originally ran in `docker:24`, which does not contain:
- `kubectl`
- `helm`

#### Error
```text
kubectl: not found
```

#### Fix
Use a deployment image that includes Helm and kubectl.

---

### 4. YAML Parsing Errors
Inline PowerShell-like script syntax caused YAML parsing issues.

#### Fix
Use Linux shell commands and job-appropriate images.

---

### 5. Helm Values Mismatch
The chart templates expected nested values that were not initially present in the values files.

#### Fix
Align the values structure with the template structure.

---

## Security Notes

### 1. CI Job Token
The pipeline uses `CI_JOB_TOKEN`, which is short-lived and valid only while the job runs. GitLab documents this token behavior explicitly. ([docs.gitlab.com](https://docs.gitlab.com/ci/jobs/ci_job_token/?utm_source=openai))

### 2. Secrets
At this stage, secrets in Helm values may still be placeholders for testing and validation.

For a stronger production design:
- do not hardcode real secrets in Git
- use protected CI/CD variables
- or use an external secret manager

### 3. Kubeconfig Warning
During CI, Helm emitted warnings that the generated kubeconfig file was group-readable/world-readable. These were warnings, not the root cause of job failures.

---

## Current Status

At this stage, the project has achieved:

- working GitLab runner execution
- Docker-based CI jobs
- registry login using GitLab CI variables
- Docker image build and push
- Kubernetes/Helm tooling in deployment jobs
- Helm lint-based validation
- separation between development and production flows

This means the CI/CD foundation is functional and suitable for presentation as a DevOps workflow.

---

## Next Improvements

Recommended next steps:

1. complete full Helm chart validation across all templates
2. replace placeholder secrets with secure CI/CD-managed secrets
3. add application-level automated tests
4. improve ingress/domain/TLS configuration
5. add rollback and monitoring procedures
6. add environment URLs and release notes

---

## Exam Explanation Summary

If you need to explain the project orally, you can present it like this:

### Short Version
This project implements a DevOps pipeline for the Doctor Clinic application using GitLab CI/CD, Docker, Helm, and Kubernetes. The pipeline builds a Docker image, pushes it to GitLab Container Registry, validates deployment manifests with Helm, and separates development and production deployments.

### Key Technical Decisions
- A **Docker executor runner** was used, so jobs run in Linux containers.
- CI scripts had to use Linux shell syntax, not PowerShell syntax.
- The GitLab Container Registry was accessed using the built-in `CI_JOB_TOKEN`.
- Deployment jobs required a dedicated image containing `kubectl` and `helm`.
- Helm chart debugging was done incrementally by aligning values files with template expectations.

### Why This Matters
This project demonstrates practical DevOps skills:
- pipeline design
- runner debugging
- container registry authentication
- Kubernetes deployment preparation
- Helm chart troubleshooting
- environment separation between dev and prod

---

## Example Commands

### Push to both remotes
```bash
git push origin main
git push gitlab main
```

### Check current branch
```bash
git branch --show-current
```

### Validate Helm manually
```bash
helm lint ./helm/doctor-clinic -f ./helm/doctor-clinic/values.yaml -f ./helm/doctor-clinic/values-prod.yaml
```

### Watch rollout
```bash
kubectl rollout status deployment/doctor-clinic-web -n doctor-clinic-prod --timeout=180s
```

---

## Conclusion

The Doctor Clinic DevOps System now includes a structured CI/CD foundation with:
- automated validation
- image packaging
- registry publishing
- Kubernetes deployment preparation
- separate dev/prod flow control

It is a strong intermediate-stage DevOps implementation and a solid base for completing full automated deployment in the next phase.