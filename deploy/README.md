# TubeYou na K3s

Docelowy układ na pojedynczym serwerze HP:

- aplikacja PHP/Apache jako jeden `Deployment`;
- MySQL 8 jako pojedynczy `StatefulSet` z PVC 10 GiB;
- uploady na PVC 30 GiB;
- Redis dla sesji, cache i rate limitera;
- `NodePort` 30180, do którego prowadzi istniejący Cloudflare Tunnel.

Sekretów nie ma w repozytorium. Przed pierwszym `kubectl apply` trzeba utworzyć
`tubeyou-secrets`, osobny sekret `tubeyou-mail` oraz ConfigMapę z inicjalnym
schematem i seedem. `schema.sql` jest resetem deweloperskim i wolno uruchomić go
wyłącznie na nowym, pustym PVC.

K3s na tym serwerze musi działać bez wbudowanego Traefika i ServiceLB, ponieważ
porty 80/443 są już używane przez istniejące usługi:

```yaml
disable:
  - traefik
  - servicelb
node-ip: 192.168.33.200
```

Obraz jest budowany lokalnie na serwerze przez Docker i importowany do containerd:

```bash
docker build -t tubeyou:local .
docker save tubeyou:local -o /tmp/tubeyou-image.tar
sudo k3s ctr images import /tmp/tubeyou-image.tar
```

Inicjalizacja zasobów:

```bash
kubectl create namespace tubeyou
kubectl -n tubeyou create secret generic tubeyou-secrets \
  --from-literal=DB_PASSWORD='<losowe-haslo>' \
  --from-literal=MYSQL_ROOT_PASSWORD='<inne-losowe-haslo>'
sudo bash deploy/set-resend-key.sh
kubectl -n tubeyou create configmap tubeyou-db-init \
  --from-file=01-schema.sql=database/schema.sql \
  --from-file=02-seed.sql=database/seed.sql
kubectl apply -f deploy/k8s.yaml
```

Do odbierania poczty skonfiguruj w Cloudflare Email Routing alias
`panto@panto-dev.com` przekazywany na zweryfikowaną prywatną skrzynkę. Resend
powinien wysyłać z osobnej domeny `mail.panto-dev.com` jako
`no-reply@mail.panto-dev.com`; odpowiedzi są kierowane przez `MAIL_REPLY_TO` na
`panto@panto-dev.com`.

Zmiana klucza Resend później wygląda tak samo: uruchom
`sudo bash deploy/set-resend-key.sh`. Skrypt czyta klucz bez wyświetlania go, aktualizuje
tylko sekret pocztowy i restartuje aplikację, jeżeli deployment już istnieje.

Po przygotowaniu nowego obrazu `tubeyou:local` i zapisaniu go jako
`/home/ubuntu/tubeyou-local.tar` pełne wdrożenie wykonuje jedno polecenie:

```bash
sudo bash /home/ubuntu/tubeyou-k3s/deploy/finish-server-update.sh
```

Skrypt poprosi o klucz Resend, zaimportuje obraz do containerd K3s, zastosuje
manifest, poczeka na rollout i usunie tymczasowe archiwum obrazu.

Test z hosta:

```bash
curl http://192.168.33.200:30180/health.php
curl http://192.168.33.200:30180/ready.php
```

Cloudflare Tunnel powinien kierować właściwy hostname na:

```yaml
- hostname: tubeyou.panto-dev.com
  service: http://192.168.33.200:30180
```

## Firewall serwera

Serwerowy łańcuch `DOCKER-USER` kończy się regułą `DROP`, która jest wykonywana
przed `FLANNEL-FWD`. Bez wyjątku aplikacja nie połączy się z MySQL, Redisem ani
CoreDNS-em. Instalacja jest idempotentna:

```bash
sudo bash deploy/install-firewall.sh
```

Skrypt instaluje `panto-fw-k3s.sh` oraz drop-in dla `panto-fwfix.service`.
Reguły przepuszczają ruch `cni0` między podami oraz wychodzący przez `bond0`;
w kierunku odwrotnym wpuszczają wyłącznie pakiety `ESTABLISHED,RELATED`. Nowe
połączenia z LAN/WAN do podów nadal kończą się na serwerowym `DROP`. Pełny skrypt
wdrożeniowy uruchamia ten instalator automatycznie.
