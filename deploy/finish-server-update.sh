#!/usr/bin/env bash
set -euo pipefail

project_dir="/home/ubuntu/tubeyou-k3s"
image_tar="/home/ubuntu/tubeyou-local.tar"
namespace="tubeyou"

if [[ "${EUID}" -ne 0 ]]; then
  exec sudo bash "$0" "$@"
fi

if [[ ! -f "${image_tar}" ]]; then
  echo "Brak przygotowanego obrazu: ${image_tar}" >&2
  exit 1
fi

cd "${project_dir}"

bash deploy/install-firewall.sh

TUBEYOU_NAMESPACE="${namespace}" TUBEYOU_SKIP_RESTART=1 \
  bash deploy/set-resend-key.sh

k3s ctr images import "${image_tar}"
k3s kubectl apply -f deploy/k8s.yaml
k3s kubectl -n "${namespace}" rollout restart deployment/app
k3s kubectl -n "${namespace}" rollout status deployment/app --timeout=180s
k3s kubectl -n "${namespace}" get pods -o wide

rm -f -- "${image_tar}"
echo "TubeYou wdrożone; tymczasowe archiwum obrazu usunięte."
