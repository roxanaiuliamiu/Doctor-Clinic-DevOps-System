# Project Overview

## General Scope
This repository contains a containerized application designed to be built, tested, deployed, and monitored in a cloud-style environment.  
The project demonstrates a complete DevOps workflow, from application source code to production-style deployment on a Kubernetes cluster.

The main objective is not only to run the application, but also to show how modern deployment practices can be applied in a reproducible and scalable way.  
For this reason, the project includes:

- application source code
- containerization with Docker
- CI/CD automation with GitLab CI/CD
- deployment to a Kubernetes cluster
- infrastructure composed of virtual machines
- monitoring with Prometheus and Grafana

The repository therefore represents both:
1. the **application itself**
2. the **deployment and operations workflow** around it

---

## Repository Purpose
The purpose of the repository is to centralize all files required to:

- develop the application
- package it into a container image
- automate build and deployment steps
- run the application on Kubernetes
- observe the health and metrics of the system

This makes the repository a complete project artifact rather than only a codebase.

---

# Containerization

## Why Containerization Was Used
The application was containerized to ensure that it runs the same way in every environment.  
Without containerization, differences between local development, virtual machines, and production servers may lead to configuration problems.

Using Docker provides:

- portability
- reproducibility
- isolation
- easier deployment
- compatibility with Kubernetes

## How It Works
A Docker image is built from the application source code using a `Dockerfile`.  
This image contains everything needed to run the application, including dependencies and runtime configuration.

The application can then be started as a container locally for testing, and the same image can be pushed to a registry and deployed into Kubernetes.

## Benefits in This Project
In this project, containerization was important because it allowed the same application package to be used in:
- local development
- CI/CD pipelines
- Kubernetes deployment

This guarantees consistency between environments.

---

# GitLab CI/CD

## Purpose of the CI/CD Pipeline
GitLab CI/CD was used to automate the software delivery process.  
Instead of manually building and deploying the application, the pipeline performs these tasks automatically when code is pushed to the repository.

This improves:
- reliability
- speed
- repeatability
- traceability

## Typical Pipeline Responsibilities
The CI/CD pipeline is responsible for steps such as:

- checking out the repository
- installing dependencies
- running tests
- building the Docker image
- pushing the image to a container registry
- triggering or supporting deployment steps

## Why It Matters
The pipeline ensures that every version of the application goes through the same standardized process.  
This reduces human error and makes deployment more professional and maintainable.

## DevOps Value
Including GitLab CI/CD in the project shows that the repository is not only about development, but also about **continuous integration** and **continuous delivery**, which are essential parts of modern DevOps practices.

---

# Virtual Machine Infrastructure

## Cluster Topology
The infrastructure is based on **three virtual machines**:

- **1 master node**
- **2 worker nodes**

This setup simulates a small distributed environment similar to a real production cluster.

## Role of the Master Node
The master node is responsible for cluster control and orchestration.  
It runs the Kubernetes control-plane components and manages scheduling, state, and communication between nodes.

In this project, the master node is also the main administration point from which Kubernetes and Helm commands are executed.

## Role of the Worker Nodes
The worker nodes are responsible for running the deployed workloads.  
They host application containers and supporting services scheduled by Kubernetes.

Using two worker nodes increases realism because the cluster is no longer limited to a single machine.  
It allows workloads to be distributed across multiple nodes.

## Why This VM Setup Was Chosen
This topology demonstrates:
- cluster-based deployment
- separation between control and workload execution
- multi-node orchestration
- better scalability than a single-node setup

It also reflects real-world Kubernetes architecture in a simplified but meaningful way.

---

# Kubernetes Deployment

## Why Kubernetes Was Used
Kubernetes was used to orchestrate the deployment of the application containers across the cluster.  
Instead of running containers manually, Kubernetes manages them automatically.

This provides:
- automated scheduling
- self-healing
- scaling possibilities
- service discovery
- declarative deployment management

## k3s as the Kubernetes Distribution
The cluster was created using **k3s**, a lightweight Kubernetes distribution.  
k3s is well suited for learning, lab environments, and lightweight production use cases.

It provides the core Kubernetes functionality while being simpler to install and manage than a full upstream Kubernetes setup.

## Deployment Process
The application was deployed to the cluster using Kubernetes manifests or equivalent deployment definitions.  
These resources define:

- the application deployment
- the pods to run
- the service used to expose the application
- any required configuration or networking

Kubernetes then ensures that the desired number of replicas stays running.

## Advantages of This Deployment Model
This approach ensures that:
- application instances are automatically restarted if they fail
- workloads can be distributed to worker nodes
- deployment is declarative and repeatable
- the environment is closer to modern cloud-native standards

---

# Monitoring

## Monitoring Objective
Monitoring was included to observe the health and performance of the Kubernetes environment and the deployed application.

This is important because deployment alone is not enough in a production-style system.  
It must also be possible to:
- collect metrics
- visualize system health
- detect problems
- analyze performance

## Initial Local Monitoring
Before deploying monitoring inside Kubernetes, Prometheus and Grafana were first used locally.  
This local setup served as a validation step to confirm that metrics collection and visualization worked correctly.

It helped verify:
- metric exposure
- Prometheus scraping
- Grafana dashboard access
- general observability flow

## In-Cluster Monitoring
After local validation, monitoring was deployed properly inside the k3s cluster using the **kube-prometheus-stack** Helm chart.

This stack installed:
- **Prometheus**
- **Grafana**
- **Alertmanager**
- **kube-state-metrics**
- **node-exporter**
- **Prometheus Operator**

## Why In-Cluster Monitoring Is Better
Running monitoring inside the cluster is more appropriate than keeping it only on a local machine because:

- it is part of the deployed infrastructure
- it does not depend on the developer laptop
- it follows Kubernetes-native practices
- it can monitor nodes, pods, and cluster resources directly

## Issue Encountered
During deployment, one `node-exporter` pod failed because port `9100` was already in use by an old standalone `node_exporter` service on one VM.

This issue was diagnosed through pod logs and port inspection commands, then resolved by stopping the old service so that the Kubernetes-managed exporter could start successfully.

## Access to Monitoring Services
Because Grafana and Prometheus were initially deployed as internal `ClusterIP` services, they were not directly reachable from outside the cluster.  
To make them accessible, the services were exposed using `NodePort`.

This allowed access to:
- Grafana through the VM public IP and assigned NodePort
- Prometheus through the VM public IP and assigned NodePort

## Monitoring Value in the Project
Monitoring completes the project by adding observability to the deployment.  
It demonstrates that the platform is not only deployed, but also operationally visible and manageable.

---

# End-to-End Project Summary

This project demonstrates a complete DevOps and cloud-native workflow:

1. the application is developed and stored in the repository
2. it is containerized using Docker
3. GitLab CI/CD automates build and delivery tasks
4. the infrastructure is built on 3 virtual machines
5. the application is deployed on a k3s Kubernetes cluster
6. monitoring is implemented using Prometheus and Grafana

The result is a project that covers not only software development, but also infrastructure, automation, orchestration, and observability.

---

# Technologies Used

- **Docker** for containerization
- **GitLab CI/CD** for automation
- **k3s / Kubernetes** for orchestration
- **Helm** for package deployment
- **Prometheus** for metrics collection
- **Grafana** for visualization
- **Virtual Machines** for cluster infrastructure

---

# Conclusion
The repository represents a full deployment pipeline and runtime environment rather than only application code.  
Its value lies in demonstrating how a modern application can be packaged, automated, deployed on a Kubernetes cluster, and monitored in a structured and scalable way.