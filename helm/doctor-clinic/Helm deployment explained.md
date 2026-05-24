# Helm Deployment Logic for Doctor Clinic DevOps System

This document explains the logic behind using Helm to deploy the **Doctor Clinic DevOps System** on Kubernetes.

---

## 1. Why use Helm?

The repository already contains a `k8s/` folder with raw Kubernetes YAML manifests. Those files are useful for understanding the system and for manual deployment, but Helm is better for real deployment because it allows:

- reusable templates
- centralized configuration
- easier upgrades
- environment-specific values
- cleaner secret/config handling
- simpler install and rollback process

In short:

- `k8s/` = static Kubernetes manifests
- `helm/` = dynamic, reusable deployment packaging

Helm does not replace Kubernetes. Helm generates Kubernetes resources from templates.

---

## 2. Why keep the `k8s/` folder?

The `k8s/` folder should be kept because it still has value:

- it shows the original deployment structure
- it helps understand the application components
- it can be used for manual `kubectl apply -f` deployments
- it serves as a reference when creating Helm templates

The Helm chart is built by converting the ideas in `k8s/` into parameterized templates.

---

## 3. Application architecture being deployed

This project is not just one container. It is a multi-component web application.

Main components:

1. **Laravel application container**
   - runs the PHP application
   - processes business logic
   - connects to MySQL

2. **Nginx container**
   - receives HTTP traffic
   - serves the web application
   - forwards PHP requests to the app container

3. **MySQL database**
   - stores persistent application data

4. **Secrets and configuration**
   - provide application settings and credentials

5. **Persistent storage**
   - keeps MySQL data after restarts

This means the deployment logic must handle **networking**, **configuration**, and **storage**, not only container startup.

---

## 4. Why create a Helm chart?

A Helm chart groups all Kubernetes resources into one installable package.

Without Helm:
- every YAML file is applied manually
- changing image tags means editing manifests
- secrets and config are harder to manage consistently
- upgrades are more manual

With Helm:
- deployment becomes configurable through `values.yaml`
- image versions can be changed without editing templates
- secrets and settings are centralized
- the app can be installed and upgraded more safely

---

## 5. Logic behind each Helm file

### `Chart.yaml`
This file describes the chart itself.

Its purpose:
- defines chart name
- defines chart version
- defines app version
- tells Helm this is an application chart

This is metadata, not deployment logic.

---

### `values.yaml`
This is the main configuration file.

Its purpose:
- stores the values used by the templates
- keeps environment-specific data in one place
- avoids editing template files directly

Typical values included:
- image repository and tag
- app environment variables
- secret values
- service ports
- ingress settings
- storage settings

This file is the main place where deployment settings are customized.

---

### `_helpers.tpl`
This file contains reusable template helpers.

Its purpose:
- centralize naming
- avoid repeating common naming logic
- make the chart easier to maintain

For a small chart, it may only define a chart name and full name.

---

### `secret.yaml`
This file creates a Kubernetes Secret.

Its purpose:
- store sensitive values
- keep passwords and keys out of plain deployment definitions
- inject those values into containers securely

Examples:
- `APP_KEY`
- `DB_PASSWORD`
- `MYSQL_ROOT_PASSWORD`

Secrets are required because Laravel and MySQL need credentials to work properly.

---

### `pvc.yaml`
This file creates a PersistentVolumeClaim.

Its purpose:
- request storage from Kubernetes
- ensure MySQL data survives pod restarts

Without persistent storage:
- MySQL data may be lost when the pod is recreated

This file is important because databases must keep data across deployments and failures.

---

### `deployment-app.yaml`
This file deploys the Laravel application container.

Its purpose:
- start the application pod
- pull the configured image
- inject environment variables
- expose the internal application port
- connect the app to the database using service-based networking

This is the core application workload.

The app deployment usually contains:
- image settings
- environment variables
- secret references
- port definitions
- probes later if needed

---

### `service-app.yaml`
This file creates a Kubernetes Service for the app.

Its purpose:
- give the app deployment a stable internal network name
- allow Nginx or other components to reach the app reliably

In Kubernetes, pods are temporary, but Services give stable networking.

That means other components do not connect to a changing pod IP. They connect to the service name instead.

---

### `deployment-nginx.yaml`
This file deploys the Nginx container.

Its purpose:
- provide the web entry point for the application
- receive HTTP requests
- proxy dynamic requests to the Laravel app container

This deployment exists because the application architecture separates:
- PHP application runtime
- web server layer

That is common in Laravel deployments using PHP-FPM + Nginx.

---

### `service-nginx.yaml`
This file creates a Service for Nginx.

Its purpose:
- expose the Nginx pod inside the cluster
- provide the endpoint that Ingress or external access will use later

This is usually the main user-facing service in the application stack.

---

### `configmap-nginx.yaml`
This file stores the Nginx configuration.

Its purpose:
- separate configuration from the container image
- allow Nginx behavior to be changed without rebuilding the image
- mount the config into the Nginx container

This follows Kubernetes best practice by keeping runtime configuration externalized.

---

### `ingress.yaml`
This file defines an Ingress resource.

Its purpose:
- route HTTP traffic from outside the cluster to the Nginx service
- support host-based access such as `doctor-clinic.local`
- prepare for future DNS and TLS configuration

Ingress is not always needed on day one, but it becomes important for browser access in realistic deployments.

---

## 6. Why use Kubernetes Services instead of direct IPs?

In Kubernetes, pods can be recreated at any time, and their IPs can change.

Services solve that problem.

Examples:
- the Laravel app can reach MySQL using the service name `mysql`
- Nginx can reach the app using the app service name
- users or ingress can reach Nginx using the Nginx service

This makes the application stable even when pods are rescheduled.

---

## 7. Why use Secrets and ConfigMaps?

Kubernetes separates configuration into two main categories:

### Secrets
Used for sensitive data such as:
- passwords
- tokens
- app keys

### ConfigMaps
Used for non-sensitive configuration such as:
- Nginx config
- environment settings
- application options

This separation improves organization and security.

---

## 8. Why persistent storage matters

Application containers can be recreated without a problem, but the database cannot lose data.

MySQL needs persistent storage because:
- database files must survive restarts
- deployments should not destroy application data
- Kubernetes storage must be requested explicitly

That is why the PVC is part of the deployment logic.

---

## 9. Why image repository and tag are in `values.yaml`

The current repository’s raw Kubernetes manifest uses a fixed image name.

That works as a placeholder, but in a real cluster the image should be configurable.

By placing image values in `values.yaml`, you can:
- change the image repository easily
- change the version tag easily
- reuse the chart for development or production
- avoid editing deployment templates for every release

This is one of the main reasons Helm is useful.

---

## 10. Why MySQL may later be moved to a separate Helm chart

In the first version, MySQL can be deployed as part of the same application chart for simplicity.

However, in larger or more mature environments, MySQL is often managed separately because:
- database lifecycle is different from app lifecycle
- upgrades require extra care
- storage needs are special
- official charts often provide stronger defaults

For a school project or first deployment, keeping it simple is acceptable.

---

## 11. Why Helm is better for upgrades

With raw manifests, updates are more manual.

With Helm:
- the release is tracked
- values can be changed cleanly
- upgrades become repeatable
- rollbacks become easier

This is important when changing:
- image versions
- environment variables
- ingress settings
- storage settings

Helm makes deployments more manageable over time.

---

## 12. What happens during deployment

The deployment logic usually follows this order:

1. create or use the namespace
2. create secrets and config
3. create storage claim
4. deploy the database
5. deploy the Laravel app
6. deploy Nginx
7. create services
8. optionally create ingress

This order matters because components depend on each other.

For example:
- the app needs database configuration
- Nginx needs the app service
- MySQL needs storage
- external traffic needs a service or ingress

---

## 13. Why monitoring is separate from the app chart

Prometheus and Grafana are usually installed using their own Helm chart, not mixed directly into the application chart.

Why:
- monitoring is a platform concern
- it is reusable across applications
- it has many components and settings
- it should monitor the whole cluster, not only one app

So the logic is:

- app chart = deploy the Doctor Clinic system
- monitoring chart = deploy Prometheus/Grafana for Kubernetes monitoring

This separation keeps responsibilities clear.

---

## 14. How Prometheus fits into the logic

Prometheus is added after the cluster and app deployment are working.

Its role:
- monitor nodes
- monitor pods
- monitor deployments
- observe resource usage
- later observe MySQL, Nginx, and application health

Prometheus does not replace application deployment. It sits beside it and watches the system.

---

## 15. Why the network/IP problem does not block the architecture logic

Even if the VM IP is not working yet, you can still understand and prepare the full deployment design.

The IP mainly affects:
- external browser access
- ingress exposure
- DNS
- routing

It does not change the internal deployment logic:
- Kubernetes still needs deployments
- services still provide internal networking
- Helm still packages the resources
- storage and secrets still matter
- monitoring still follows the same architecture

So the deployment model remains valid.

---

## 16. Final mental model

The complete logic is:

1. **Proxmox provides the VM**
2. **Linux runs inside the VM**
3. **Kubernetes runs on Linux**
4. **Helm deploys the application resources**
5. **Kubernetes services connect the components**
6. **Persistent storage keeps MySQL data**
7. **Ingress exposes the app later**
8. **Prometheus and Grafana monitor the cluster and app**

This is the full deployment architecture for the Doctor Clinic DevOps System.

---

## 17. Summary

The Helm chart exists to convert the static Kubernetes deployment into a reusable and configurable deployment package.

Each file has a specific role:

- `Chart.yaml` → chart metadata
- `values.yaml` → deployment configuration
- `_helpers.tpl` → reusable template logic
- `secret.yaml` → sensitive values
- `pvc.yaml` → persistent storage
- `deployment-app.yaml` → Laravel app deployment
- `service-app.yaml` → internal app networking
- `deployment-nginx.yaml` → web server deployment
- `service-nginx.yaml` → web service exposure
- `configmap-nginx.yaml` → Nginx configuration
- `ingress.yaml` → external routing

Together, these files define how the application is deployed, connected, configured, and prepared for monitoring in Kubernetes.