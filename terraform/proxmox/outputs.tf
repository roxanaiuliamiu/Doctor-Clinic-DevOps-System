output "runner_vm_name" {
  value = proxmox_virtual_environment_vm.gitlab_runner.name
}

output "runner_vm_id" {
  value = proxmox_virtual_environment_vm.gitlab_runner.vm_id
}