#!/bin/sh
set -eu

# panto-fw.sh keeps a final DROP in DOCKER-USER. K3s' FLANNEL-FWD chain is
# reached later, so allow only the CNI paths the single-node cluster needs.
ensure_return() {
  iptables -C DOCKER-USER "$@" -j RETURN 2>/dev/null \
    || iptables -I DOCKER-USER 1 "$@" -j RETURN
}

# Pod-to-pod traffic on this node (MySQL, Redis and cluster DNS).
ensure_return -i cni0 -o cni0

# Pod egress to the LAN/WAN and only stateful reply traffic in the other direction.
ensure_return -i cni0 -o bond0 -m conntrack --ctstate NEW,ESTABLISHED
ensure_return -i bond0 -o cni0 -m conntrack --ctstate RELATED,ESTABLISHED
