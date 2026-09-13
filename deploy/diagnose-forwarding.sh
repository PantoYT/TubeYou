#!/usr/bin/env bash
set -u

if [ "$(id -u)" -ne 0 ]; then
    echo "Uruchom przez sudo: sudo bash $0" >&2
    exit 2
fi

echo "=== backend i polityka FORWARD ==="
iptables --version
iptables -L FORWARD -n -v --line-numbers

echo
echo "=== lancuchy wpiete w FORWARD ==="
for chain in \
    DOCKER-USER DOCKER-FORWARD DOCKER-ISOLATION-STAGE-1 \
    DOCKER-ISOLATION-STAGE-2 KUBE-FORWARD KUBE-SERVICES \
    CNI-FORWARD FLANNEL-FWD ts-forward; do
    echo "--- $chain"
    iptables -L "$chain" -n -v --line-numbers 2>&1 || true
done

echo
echo "=== most cni0 ==="
bridge link show master cni0 2>&1 || true
bridge fdb show br cni0 2>&1 || true
ip neigh show dev cni0 2>&1 || true
sysctl net.bridge.bridge-nf-call-iptables net.bridge.bridge-nf-call-ip6tables 2>&1 || true

echo
echo "=== aktualny test app -> mysql pod ==="
app_pod="$(k3s kubectl -n tubeyou get pod -l app=tubeyou -o jsonpath='{.items[0].metadata.name}')"
mysql_ip="$(k3s kubectl -n tubeyou get pod mysql-0 -o jsonpath='{.status.podIP}')"
echo "app=$app_pod mysql=$mysql_ip"
tcp_probe='$socket=@fsockopen($argv[1],(int)$argv[2],$errno,$error,2); if ($socket) { echo "OK\n"; fclose($socket); exit(0); } fwrite(STDERR,"FAIL $errno $error\n"); exit(1);'
timeout 8 k3s kubectl -n tubeyou exec "$app_pod" -- php -r "$tcp_probe" "$mysql_ip" 3306 || true

echo
echo "=== FORWARD po tescie (liczniki) ==="
iptables -L FORWARD -n -v --line-numbers
