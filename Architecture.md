# Architecture

## Diagram-Style Architecture


```

## Short Explanation
The architecture starts with the GitLab repository, where the application source code and CI/CD configuration are stored.  
When code is pushed, GitLab CI/CD triggers an automated pipeline that builds and packages the application into a Docker image.  
This image is stored in a container registry and later pulled by the Kubernetes cluster.

The runtime environment is a **k3s cluster** composed of **one master node** and **two worker nodes**.  
The master node manages cluster orchestration, while the worker nodes run the deployed application workloads.

Inside the cluster, a dedicated monitoring namespace hosts Prometheus, Grafana, Alertmanager, kube-state-metrics, and node-exporter.  
These components collect, process, and visualize metrics from the nodes and Kubernetes resources.

Users and administrators interact with the deployed system either through application services or through monitoring dashboards exposed via NodePort or internal access methods.