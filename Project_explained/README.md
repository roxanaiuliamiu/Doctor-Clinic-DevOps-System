# Doctor Clinic DevOps System

A Laravel-based clinic management and appointment booking platform enhanced with a complete DevOps delivery layer. This project extends the original Doctor Clinic System by adding containerization, CI/CD automation, deployment preparation, backup procedures, monitoring planning, and Infrastructure as Code foundations.

---

## 1. Project Overview

The Doctor Clinic DevOps System is a web application designed to support the daily workflow of a clinic through three main user roles:

- **Patients** can browse doctors and specialties, then book appointments.
- **Doctors** can manage profiles, availability, and appointments.
- **Admins** can manage users, specialties, reports, settings, and public content.

This repository represents the **DevOps-oriented delivery** of the project, where the application was transformed from a standard Laravel application into a more operationally ready system.

---

## 2. Core Functional Features

### Patient Features
- Register and log in
- Browse specialties and doctors
- Book appointments
- Access patient dashboard
- View appointment information

### Doctor Features
- Register and wait for approval
- Manage doctor profile
- Define work schedule and availability
- View and manage appointments
- Access doctor dashboard and reports

### Admin Features
- Approve or reject doctor registration requests
- Manage users
- Manage specialties
- Manage public pages
- Manage settings
- Access reports and appointments overview

---

## 3. Technology Stack

### Application Layer
- **Backend:** Laravel / PHP 8.2
- **Frontend:** Blade, Tailwind CSS, Alpine.js
- **Build Tool:** Vite
- **Database:** MySQL
- **Authentication:** Laravel Breeze

### DevOps Layer
- **Containerization:** Docker, Docker Compose
- **CI/CD:** GitHub Actions, GitLab CI/CD
- **Deployment Preparation:** Kubernetes manifests
- **Backup Automation:** PowerShell script
- **Monitoring Planning:** Markdown documentation
- **Infrastructure as Code:** Terraform starter structure

---

## 4. DevOps Enhancements Implemented

This project includes the following DevOps deliverables:

- Dockerized Laravel application
- Docker Compose stack for:
  - app
  - nginx
  - mysql
- GitHub Actions CI pipeline
- GitLab CI/CD multi-stage pipeline
- Frontend asset build automation
- Automated test execution
- Kubernetes deployment manifests
- Backup automation script
- Monitoring plan documentation
- Terraform Infrastructure-as-Code starter files
- Institute-oriented project specification and requirement mapping documents

---

## 5. Repository Structure

```bash
app/
bootstrap/
config/
database/
docker/
k8s/
public/
resources/
routes/
scripts/
storage/
terraform/
tests/
.github/workflows/
.gitlab-ci.yml
Dockerfile
docker-compose.yml
README.md
DEVOPS-IMPLEMENTATION.md
monitoring-plan.md
SPECIFICATIONS.md
INSTITUTE-REQUIREMENTS-MAPPING.md
ARCHITECTURE-OVERVIEW.md
DELIVERY-EVIDENCE.md
```

---

## 6. Running the Project Locally with Docker

### Step 1: Build and start containers
```bash
docker compose up -d --build
```

### Step 2: Generate the Laravel application key
```bash
docker compose exec app php artisan key:generate
```

### Step 3: Run database migrations
```bash
docker compose exec app php artisan migrate
```

### Step 4: Seed demo data
```bash
docker compose exec app php artisan db:seed
```

### Step 5: Create storage link
```bash
docker compose exec app php artisan storage:link
```

### Step 6: Open the application
```text
http://localhost:8080
```

---

## 7. Running Automated Tests

To run all Laravel tests inside Docker:

```bash
docker compose exec app php artisan test
```

### Current validation status
- Laravel tests pass successfully
- Migrations run successfully
- Seeders run successfully
- Application loads through nginx container

---

## 8. GitHub CI/CD

The GitHub Actions workflow is available in:

```text
.github/workflows/ci.yml
```

### GitHub pipeline responsibilities
- Checkout repository
- Set up PHP
- Set up Node.js
- Install Composer dependencies
- Install NPM dependencies
- Build Vite frontend assets
- Generate application key
- Configure test environment
- Run migrations
- Clear caches
- Execute automated tests

This provides continuous validation for the GitHub repository version of the project.

---

## 9. GitLab CI/CD

The GitLab CI pipeline is available in:

```text
.gitlab-ci.yml
```

### GitLab pipeline stages
- validate
- build
- test
- package
- docker build preparation
- deploy placeholder

### GitLab pipeline purpose
The pipeline was designed to present a more professional DevOps delivery by validating syntax, building frontend assets, running Laravel tests, packaging the project, and preparing future deployment workflow stages.

---

## 10. Docker and Containerization

The project includes:

- `Dockerfile`
- `docker-compose.yml`
- `docker/nginx/default.conf`

### Containers used
- **app** -> Laravel PHP-FPM container
- **nginx** -> reverse proxy and web server
- **mysql** -> relational database service

### Benefits achieved
- reproducible local environment
- simplified setup
- service separation
- easier future deployment transition

---

## 11. Kubernetes Deployment Preparation

Initial Kubernetes manifests are provided in:

```text
k8s/
```

### Included manifests
- `namespace.yaml`
- `secrets.yaml`
- `pvc.yaml`
- `mysql-deployment.yaml`
- `mysql-service.yaml`
- `app-deployment.yaml`
- `app-service.yaml`
- `nginx-configmap.yaml`
- `nginx-deployment.yaml`
- `nginx-service.yaml`

### Notes
These manifests represent a deployment preparation layer for academic and future practical use. They provide structure for:

- namespace separation
- secret injection
- persistent storage claim
- mysql deployment
- application deployment
- nginx reverse proxy deployment

### Production note
This Kubernetes layer is a starter implementation and can later be improved with:

- registry-based image delivery
- ingress controller
- probes
- autoscaling
- secret vault integration
- better shared storage strategy

---

## 12. Backup and Recovery

Backup automation is available in:

```text
scripts/backup.ps1
```

### What the script does
- creates a MySQL database dump
- archives public storage files
- stores output in timestamped backup folders

### Run backup manually
```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\backup.ps1
```

### Backup objective
This script demonstrates operational readiness and basic recovery support for the project.

---

## 13. Monitoring

Monitoring documentation is available in:

```text
monitoring-plan.md
```

### Monitoring scope
- Laravel application health
- nginx availability
- MySQL availability
- logs
- uptime awareness
- response time awareness
- future Prometheus/Grafana integration plan

This is currently a documented monitoring strategy rather than a fully deployed observability stack.

---

## 14. Terraform / Infrastructure as Code

Terraform starter files are available in:

```text
terraform/
```

### Included Terraform files
- `provider.tf`
- `variables.tf`
- `main.tf`
- `outputs.tf`
- `terraform.tfvars.example`
- `README.md`

### Purpose
This Terraform layer demonstrates:

- structured Infrastructure as Code organization
- variable-driven configuration
- output declarations
- future extensibility for real infrastructure provisioning

This is a starter academic IaC foundation and can later be extended for cloud VMs, Proxmox, networking, storage, or Kubernetes infrastructure provisioning.

---

## 15. Documentation Included

This project contains multiple supporting documents for final delivery:

- `README.md` -> full project overview
- `DEVOPS-IMPLEMENTATION.md` -> summary of DevOps work completed
- `SPECIFICATIONS.md` -> formal project specifications
- `INSTITUTE-REQUIREMENTS-MAPPING.md` -> mapping of institute requirements to deliverables
- `ARCHITECTURE-OVERVIEW.md` -> architecture explanation
- `DELIVERY-EVIDENCE.md` -> evidence checklist for delivery
- `monitoring-plan.md` -> monitoring strategy
- `terraform/README.md` -> Terraform explanation

---

## 16. Current Delivery Status

The project currently demonstrates:

- successful Docker-based execution
- successful Laravel automated tests
- CI/CD implementation on GitHub
- CI/CD implementation on GitLab
- Kubernetes deployment preparation
- backup procedure
- monitoring planning
- Infrastructure as Code starter implementation
- strong technical documentation for academic submission

---

## 17. Remaining Future Improvements

The following are future improvements, not blockers for the current academic delivery:

- publish Docker image to a real container registry
- deploy to a real Kubernetes cluster
- add ingress and HTTPS
- integrate Prometheus and Grafana
- add Alertmanager
- strengthen Kubernetes production readiness
- use production-grade secret management
- extend Terraform toward real infrastructure resources
- add screenshots and architecture diagrams to documentation

---

## 18. Recommended Final Submission Evidence

For final institute submission, it is recommended to include screenshots of:

- Docker Compose running containers
- application home page in browser
- successful Laravel test execution
- GitHub Actions successful pipeline
- GitLab successful pipeline
- Kubernetes manifests folder
- Terraform folder
- backup script execution result

These screenshots will strengthen the presentation of the project during review.

---

## 19. Author

**Muhammed Munir Al Tawil**
