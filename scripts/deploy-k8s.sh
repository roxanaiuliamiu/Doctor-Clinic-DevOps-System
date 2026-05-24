#!/bin/bash
set -e

kubectl create namespace doctor-clinic --dry-run=client -o yaml | kubectl apply -f -
helm upgrade --install doctor-clinic ./helm/doctor-clinic -n doctor-clinic