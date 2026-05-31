provider "proxmox" {
  endpoint  = var.proxmox_endpoint
  api_token = "${var.proxmox_username}!${var.proxmox_token_id}=${var.proxmox_token_secret}"
  insecure  = var.proxmox_insecure
}

resource "proxmox_virtual_environment_vm" "gitlab_runner" {
  vm_id     = var.runner_vm_id
  name      = var.runner_vm_name
  node_name = var.proxmox_node
  started   = false

  clone {
    vm_id        = var.template_vm_id
    datastore_id = var.datastore_id
    full         = true
  }

  cpu {
    cores = var.runner_cores
    type  = "x86-64-v2-AES"
  }

  memory {
    dedicated = var.runner_memory
  }

  agent {
    enabled = true
  }

  network_device {
    bridge = var.network_bridge
  }

  initialization {
    datastore_id = var.datastore_id
  }

  on_boot = true
}