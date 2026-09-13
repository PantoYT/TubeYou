#!/usr/bin/env bash
set -u

if [ "$(id -u)" -ne 0 ]; then
    echo "Uruchom przez sudo: sudo bash $0" >&2
    exit 2
fi

KUBECTL="k3s kubectl"

section() {
    printf '\n=== %s ===\n' "$1"
}

section "pody, uslugi i endpointy"
$KUBECTL get pods -A -o wide
$KUBECTL -n tubeyou get svc,endpoints,endpointslice -o wide
$KUBECTL -n kube-system get svc,endpoints,endpointslice -l k8s-app=kube-dns -o wide

section "DNS widziany z aplikacji"
$KUBECTL -n tubeyou exec deploy/app -- cat /etc/resolv.conf
for name in mysql redis kubernetes.default.svc.cluster.local; do
    printf '%-40s ' "$name"
    if timeout 8 $KUBECTL -n tubeyou exec deploy/app -- getent hosts "$name"; then
        :
    else
        echo "BRAK (exit $?)"
    fi
done

section "TCP z aplikacji: pod IP kontra Service DNS"
mysql_ip="$($KUBECTL -n tubeyou get pod mysql-0 -o jsonpath='{.status.podIP}')"
tcp_probe='$socket=@fsockopen($argv[1],(int)$argv[2],$errno,$error,2); if ($socket) { echo "OK\n"; fclose($socket); exit(0); } fwrite(STDERR,"FAIL $errno $error\n"); exit(1);'
printf 'mysql pod %s:3306: ' "$mysql_ip"
$KUBECTL -n tubeyou exec deploy/app -- php -r "$tcp_probe" "$mysql_ip" 3306 || true
printf 'mysql service mysql:3306: '
$KUBECTL -n tubeyou exec deploy/app -- php -r "$tcp_probe" mysql 3306 || true

section "Flannel i forwarding"
cat /run/flannel/subnet.env 2>&1 || true
sysctl net.ipv4.ip_forward net.ipv4.conf.all.forwarding 2>&1 || true
ip route

section "reguly kube-proxy dla DNS, MySQL i NodePort"
iptables-save 2>&1 \
    | grep -E 'KUBE-|10\.43\.0\.10|10\.43\.96\.7|30180' \
    | head -n 240 || true

section "lokalny NodePort"
curl -sS -D - --max-time 5 http://127.0.0.1:30180/health.php || true

section "ostatnie bledy K3s"
journalctl -u k3s --since '-15 minutes' --no-pager \
    | grep -Ei 'flannel|iptables|proxy|dns|error|fail' \
    | tail -n 160 || true
