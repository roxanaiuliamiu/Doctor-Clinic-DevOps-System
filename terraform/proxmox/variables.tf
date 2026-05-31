variable "proxmox_endpoint" {
  description = "Proxmox API endpoint"
  type        = string
}

variable "proxmox_username" {
  description = "Proxmox username"
  type        = string
}

variable "proxmox_password" {
  description = "Proxmox password"
  type        = string
  sensitive   = true
}

variable "proxmox_insecure" {
  description = "Skip TLS verification for Proxmox API"
  type        = bool
  default     = true
}

variable "proxmox_node" {
  description = "Proxmox node name"
  type        = string
}

variable "datastore_id" {
  description = "Proxmox datastore for cloud-init and cloned disks"
  type        = string
  default     = "local"
}

variable "network_bridge" {
  description = "Proxmox bridge"
  type        = string
}

variable "template_vm_id" {
  description = "Template VM ID"
  type        = number
}

variable "runner_vm_id" {
  description = "New runner VM ID"
  type        = number
}

variable "runner_vm_name" {
  description = "Runner VM name"
  type        = string
  default     = "gitlab-runner-vm"
}

variable "runner_memory" {
  description = "Memory in MB"
  type        = number
  default     = 4096
}

variable "runner_cores" {
  description = "CPU cores"
  type        = number
  default     = 2
}

variable "proxmox_token_id" {
  description = "Proxmox API token ID"
  type        = string
}

variable "proxmox_token_secret" {
  description = "Proxmox API token secret"
  type        = string
  sensitive   = true
}