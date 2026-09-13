#!/usr/bin/env bash
# Instaluje trwały, możliwie wąski wyjątek K3s w serwerowym DOCKER-USER.

set -euo pipefail

if [[ "${EUID}" -ne 0 ]]; then
  exec sudo bash "$0" "$@"
fi

script_dir="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
helper_source="${script_dir}/panto-fw-k3s.sh"
dropin_source="${script_dir}/panto-fwfix-k3s.conf"
helper_target=/usr/local/sbin/panto-fw-k3s.sh
dropin_dir=/etc/systemd/system/panto-fwfix.service.d
dropin_target="${dropin_dir}/k3s.conf"
panto_firewall=/usr/local/sbin/panto-fw.sh
broad_source_line='ensure filter DOCKER-USER -s 10.42.0.0/16 -j RETURN'
emergency_rule=(-s 10.42.0.0/16 -j RETURN)

[[ -f "${helper_source}" && -f "${dropin_source}" ]] || {
  echo "Brak plików reguł obok instalatora." >&2
  exit 1
}

timestamp="$(date +%Y%m%d-%H%M%S)"
[[ ! -e "${helper_target}" ]] || cp -a -- "${helper_target}" "${helper_target}.bak-${timestamp}"
[[ ! -e "${dropin_target}" ]] || cp -a -- "${dropin_target}" "${dropin_target}.bak-${timestamp}"

install -o root -g root -m 0755 "${helper_source}" "${helper_target}"
install -d -o root -g root -m 0755 "${dropin_dir}"
install -o root -g root -m 0644 "${dropin_source}" "${dropin_target}"

# Wczesna diagnostyka mogła dopisać szerszą regułę do bazowego skryptu.
# Usuń wyłącznie tę dokładną linię, zachowując backup całej konfiguracji.
if [[ -f "${panto_firewall}" ]] && grep -Fxq -- "${broad_source_line}" "${panto_firewall}"; then
  cp -a -- "${panto_firewall}" "${panto_firewall}.bak-k3s-${timestamp}"
  temporary="$(mktemp)"
  trap 'rm -f -- "${temporary}"' EXIT
  grep -Fvx -- "${broad_source_line}" "${panto_firewall}" > "${temporary}"
  install -o root -g root -m 0755 "${temporary}" "${panto_firewall}"
fi

systemctl daemon-reload
systemctl restart panto-fwfix.service

# Usuń szeroki wyjątek awaryjny użyty podczas diagnozy. Wąskie reguły interfejsowe
# są już aktywne dzięki ExecStartPost powyżej.
while iptables -C DOCKER-USER "${emergency_rule[@]}" 2>/dev/null; do
  iptables -D DOCKER-USER "${emergency_rule[@]}"
done

iptables -C DOCKER-USER -i cni0 -o cni0 -j RETURN
iptables -C DOCKER-USER -i cni0 -o bond0 -m conntrack --ctstate NEW,ESTABLISHED -j RETURN
iptables -C DOCKER-USER -i bond0 -o cni0 -m conntrack --ctstate RELATED,ESTABLISHED -j RETURN

if k3s kubectl -n tubeyou get deployment/app >/dev/null 2>&1; then
  if ! k3s kubectl -n tubeyou exec deployment/app -- \
    php -r 'exit(@file_get_contents("http://127.0.0.1/ready.php") === false ? 1 : 0);'; then
    # Nie zostawiaj produkcji odciętej, jeśli topologia serwera znowu się zmieniła.
    iptables -C DOCKER-USER "${emergency_rule[@]}" 2>/dev/null \
      || iptables -I DOCKER-USER 1 "${emergency_rule[@]}"
    echo "BLAD: wąskie reguły nie wystarczyły; przywrócono wyjątek awaryjny." >&2
    exit 1
  fi
  k3s kubectl -n tubeyou get pods -o wide
fi

echo "Trwałe reguły K3s są aktywne."
