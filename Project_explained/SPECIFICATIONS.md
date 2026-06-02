# Doctor Clinic DevOps System Specifications

## 1. Project Title
Doctor Clinic DevOps System

## 2. Project Context
This project is based on a Laravel clinic management platform that supports patients, doctors, and administrators in one integrated web application. The original application handles appointments, doctor availability, specialty management, user management, reports, and public content pages.

This DevOps version extends the original application by adding containerization, CI/CD automation, Kubernetes deployment through Helm, backup procedures, monitoring preparation, and Infrastructure as Code foundations.

## 3. Business Need
Healthcare and clinic systems require reliability, repeatability, and maintainability. Manual deployment and unstructured environments increase operational risk, slow delivery, and introduce avoidable configuration errors.

The business need of this project is to transform the clinic system into a deployable and maintainable platform using DevOps practices that improve:
- deployment consistency
- automated validation
- operational readiness
- backup and recovery
- infrastructure preparation
- future scalability

## 4. Project Objectives
The main objectives of this project are:
- containerize the Laravel clinic application
- prepare a reproducible local environment using Docker Compose
- automate testing and delivery through CI/CD pipelines
- deploy the application to Kubernetes using Helm
- organize environment-specific configuration for development and production
- document monitoring and operational strategy
- automate backup operations
- provide an Infrastructure as Code starter layer
- organize the project for professional DevOps delivery

## 5. Project Scope

### Included in scope
- Laravel application execution
- Docker image build
- Docker Compose local stack
- MySQL service integration
- Nginx reverse proxy integration
- GitLab CI/CD pipeline
- Kubernetes deployment with Helm
- backup automation script
- monitoring planning
- Terraform starter configuration
- technical delivery documentation

## 6. Stakeholders and Roles

### Patient
- browse specialties and doctors
- create appointments
- access patient dashboard

### Doctor
- manage profile
- define availability
- review appointments
- access doctor dashboard

### Admin
- manage users
- approve doctors
- manage specialties
- manage reports
- manage settings and pages

### DevOps / System Maintainer
- maintain Docker environments
- manage CI/CD pipelines
- maintain Kubernetes deployment configuration
- manage backup and recovery procedures
- monitor system operational readiness

## 7. Functional Requirements
The platform shall provide:
- user authentication and authorization
- patient registration and login
- doctor registration and approval workflow
- doctor availability scheduling
- appointment booking and management
- specialty management
- admin control panel
- public pages
- automated test execution
- CI/CD pipeline execution
- containerized application runtime
- database migration execution during deployment
- backup script for database and storage
- Kubernetes-based deployment
- monitoring strategy documentation

## 8. Non-Functional Requirements
The platform should satisfy the following non-functional requirements:
- reproducible environment setup
- automated validation before delivery
- maintainable deployment configuration
- clear project structure
- portability between local, CI, and Kubernetes environments
- recoverability through backups
- extensibility for future monitoring and infrastructure automation
- consistent deployment across environments

## 9. Technology Stack

### Application Layer
- Laravel 12
- PHP 8.2
- Blade
- Tailwind CSS
- Alpine.js
- Vite
- MySQL 8

### DevOps Layer
- Docker
- Docker Compose
- GitLab CI/CD
- Kubernetes / K3s
- Helm
- Proxmox
- PowerShell backup script
- Terraform configuration

## 10. Solution Design
The solution is organized around the following layers:

### Application Layer
Laravel application with Blade frontend and MySQL database.

### Container Layer
Docker is used to package the application runtime. Docker Compose is used for local orchestration of:
- app
- nginx
- mysql

### CI/CD Layer
GitLab CI/CD is used to automate testing, image build, registry push, and deployment steps.

### Deployment Layer
Helm is used to package and deploy Kubernetes resources for:
- namespace creation
- secret management
- persistent volume claim
- MySQL deployment and service
- Laravel application deployment and service
- migration job execution

### Operations Layer
Operational readiness is supported through:
- backup scripting
- monitoring preparation
- technical documentation
- deployment verification procedures

### IaC Layer
Terraform starter files demonstrate Infrastructure as Code structure for future extension and automation.

## 11. Data Management
The system uses MySQL as the primary relational database. Application data includes users, specialties, doctor profiles, availabilities, appointments, reports, and related records.

### Data Storage
- MySQL stores operational data
- Laravel storage contains public files and generated application data
- logs are stored within Laravel storage
- container and cluster logs can be monitored at runtime level

### Data Protection
- backup procedures include database export
- storage files can be archived
- future enhancements can add remote retention, encryption, and automated restore verification

## 12. CI/CD Strategy
The CI/CD strategy validates and delivers the project automatically through GitLab CI/CD.

### GitLab CI/CD
The pipeline provides staged execution for:
- test
- build
- deploy

The pipeline performs:
- environment preparation
- Docker image build
- container registry push
- Helm-based deployment
- rollout verification
- database migration execution

Separate deployment flows are used for development and production branches.

## 13. Deployment Strategy

### Local Deployment
Local deployment is performed with Docker Compose.

### Production Deployment
Production deployment is performed through Helm on a K3s Kubernetes cluster.

The production cluster runs on **three Proxmox virtual machines**:
- **1 control-plane node**
- **2 worker nodes**

This allows the project to run in a realistic multi-node environment instead of a single local machine.

### Current State
The current project includes an active Kubernetes deployment workflow, not only deployment preparation. The application is built, deployed, and validated through the CI/CD pipeline, with database migration executed as part of the release process.

## 14. Backup and Recovery Strategy
A PowerShell backup script is included to:
- export a MySQL database dump
- archive Laravel storage files

This supports a basic recovery workflow and demonstrates operational maintenance practices.

## 15. Monitoring Strategy
Monitoring is currently approached at two levels:
- locally for application and container behavior
- on the virtual machines and Kubernetes cluster for infrastructure and deployment verification

This project includes monitoring preparation and operational checks, with future extension planned for a more complete monitoring stack.

## 16. Infrastructure as Code Strategy
Terraform starter files are included to demonstrate:
- variable-driven configuration
- output definitions
- reusable project structure
- future extensibility toward real infrastructure provisioning

## 17. Risks and Limitations
Current limitations include:
- limited production hardening
- basic secret management approach
- no full external monitoring stack yet
- no ingress and HTTPS configuration yet
- no cloud-specific Terraform provisioning yet
- workload scheduling is not yet restricted only to worker nodes

## 18. Future Improvements
Future versions can add:
- ingress and HTTPS
- Prometheus and Grafana stack
- Alertmanager integration
- stronger secret management
- Terraform modules for real infrastructure
- stricter node scheduling policies
- release versioning and rollback strategy
- advanced monitoring and alerting

## 19. Conclusion
The Doctor Clinic DevOps System transforms a standard Laravel clinic application into a professionally structured DevOps project. It demonstrates containerization, CI/CD automation, Helm-based Kubernetes deployment, backup planning, monitoring readiness, and Infrastructure as Code foundations suitable for academic delivery and future professional extension.