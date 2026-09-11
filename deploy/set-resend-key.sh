#!/usr/bin/env bash
set -euo pipefail

namespace="${TUBEYOU_NAMESPACE:-tubeyou}"
secret_name="tubeyou-mail"
tmp_dir="$(mktemp -d)"
key_file="${tmp_dir}/RESEND_API_KEY"

cleanup() {
  unset resend_key
  rm -f -- "${key_file}"
  rmdir -- "${tmp_dir}" 2>/dev/null || true
}
trap cleanup EXIT

read -r -s -p "Resend API key: " resend_key
printf '\n'

if [[ -z "${resend_key}" ]]; then
  echo "Klucz nie może być pusty." >&2
  exit 1
fi

umask 077
printf '%s' "${resend_key}" > "${key_file}"
unset resend_key

kubectl -n "${namespace}" create secret generic "${secret_name}" \
  --from-file="RESEND_API_KEY=${key_file}" \
  --dry-run=client \
  -o yaml | kubectl apply -f -

if [[ "${TUBEYOU_SKIP_RESTART:-0}" != "1" ]] && \
   kubectl -n "${namespace}" get deployment app >/dev/null 2>&1; then
  kubectl -n "${namespace}" rollout restart deployment/app
  kubectl -n "${namespace}" rollout status deployment/app --timeout=120s
fi

echo "Sekret ${secret_name} został zaktualizowany."
