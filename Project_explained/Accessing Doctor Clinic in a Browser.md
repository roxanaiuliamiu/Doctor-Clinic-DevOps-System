# Accessing Doctor Clinic in a Browser

This guide explains the exact changes and commands needed to expose the `doctor-clinic-web` application and access it from a local browser.

---

## 1. Change the Kubernetes web Service to NodePort

Edit:

`helm/doctor-clinic/templates/service.yaml`

Use this configuration:

```yaml
apiVersion: v1
kind: Service
metadata:
  name: doctor-clinic-web
  namespace: {{ .Values.namespace }}
spec:
  selector:
    app: doctor-clinic-web
  ports:
    - port: {{ .Values.service.web.port }}
      targetPort: 80
      nodePort: 30080
  type: NodePort
```

This exposes the web app on Kubernetes node port `30080`.

---

## 2. Redeploy and verify the Service

After pushing your change and redeploying, verify the service:

```bash
kubectl --kubeconfig=/home/ubuntu/kubeconfig-prod.yaml get svc -n doctor-clinic-prod
```

Expected output:

```text
doctor-clinic-web   NodePort   ...   80:30080/TCP
mysql               ClusterIP  ...   3306/TCP
```

---

## 3. Verify the application works inside the Kubernetes VM

Run on the Kubernetes VM:

```bash
curl -I http://127.0.0.1:30080
curl -I http://10.10.10.20:30080
```

Expected result:

```text
HTTP/1.1 200 OK
```

This confirms:
- Laravel is working
- Nginx is working
- Kubernetes NodePort is working

---

## 4. Fix Proxmox VM networking

The Kubernetes VM (`VM 221`) must be attached to the private bridge `vmbr1` so that its internal IP `10.10.10.20` matches the Proxmox network.

Run on the Proxmox host:

```bash
qm set 221 -net0 virtio=BC:24:11:0B:3F:75,bridge=vmbr1
qm reboot 221
```

Verify:

```bash
qm config 221
brctl show
```

Expected:

```text
net0: virtio=BC:24:11:0B:3F:75,bridge=vmbr1
```

and `tap221i0` should appear under `vmbr1`.

---

## 5. Allow the app port through the Proxmox host firewall

The Proxmox host allowed ports like `22`, `32691`, and `9100`, but not `30080`.

Add a forwarding rule on the Proxmox host:

```bash
iptables -A FORWARD -d 10.10.10.20/32 -p tcp --dport 30080 -j ACCEPT
```

Test from the Proxmox host:

```bash
curl -I http://10.10.10.20:30080
```

Expected result:

```text
HTTP/1.1 200 OK
```

---

## 6. Access the app from the laptop using an SSH tunnel

Because the laptop cannot directly reach the private subnet `10.10.10.0/24`, use the Proxmox host as a jump point.

From Windows PowerShell:

```powershell
ssh -L 8081:10.10.10.20:30080 -i $HOME\.ssh\id_ed25519 root@51.158.200.127
```

Keep that session open.

Then open in your browser:

```text
http://localhost:8081
```

This forwards:

- `localhost:8081` on the laptop
- to `10.10.10.20:30080` through the Proxmox host

---

## 7. Optional: save the iptables rule permanently

Without saving, the firewall rule may disappear after reboot.

On the Proxmox host:

```bash
apt-get update
apt-get install -y iptables-persistent
netfilter-persistent save
```

---

## Summary

### Files changed
- `helm/doctor-clinic/templates/service.yaml`

### Key infrastructure changes
- Exposed `doctor-clinic-web` as `NodePort`
- Moved VM `221` to `vmbr1`
- Allowed TCP port `30080` in Proxmox forwarding rules

### Final browser URL
```text
http://localhost:8081
```

using the SSH tunnel:

```powershell
ssh -L 8081:10.10.10.20:30080 -i $HOME\.ssh\id_ed25519 root@51.158.200.127
```