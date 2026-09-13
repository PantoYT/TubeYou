# TubeYou

A self-hosted, YouTube-inspired video platform built from scratch with PHP,
MySQL and vanilla JavaScript.

[Open the live app](https://tubeyou.panto-dev.com)

> TubeYou runs on a personal homelab server, so the demo can be temporarily
> unavailable during maintenance or a server restart.

## What it does

- uploads videos and creates thumbnails/transcodes with FFmpeg;
- streams MP4 files with HTTP Range support and selectable quality;
- provides subscriptions, likes/dislikes, playlists, Watch Later and history;
- supports threaded comments, sorting, pinning, editing and reactions;
- includes channels, avatars, banners, Studio and notifications;
- verifies accounts and resets passwords through Resend;
- scores the homepage feed and caches frequently requested data.

## Stack

- PHP 8.2 and Apache, without an application framework;
- MySQL 8 for persistent relational data;
- Redis 7 for sessions, cache and distributed rate limiting;
- FFmpeg/ffprobe for video processing;
- Docker Compose for local development;
- single-node K3s for production;
- Cloudflare Tunnel for public ingress;
- Cloudflare Email Routing for `panto@panto-dev.com`;
- Resend for transactional mail from `no-reply@mail.panto-dev.com`.

## Production architecture

```text
Browser
   │ HTTPS
Cloudflare ── Email Routing ──> Gmail
   │ Tunnel
HP homelab server :30180
   │
K3s ── PHP/Apache ── MySQL
             └────── Redis
```

The application currently runs as one K3s replica. Kubernetes provides
declarative deployment and health-based restarts, but automatic horizontal
scaling is not enabled. Uploads and MySQL data live on persistent volumes.

Detailed server setup, secret handling, firewall rules and recovery commands
are documented in [deploy/README.md](deploy/README.md).

## Local development with Docker

Requirements: Docker with Compose.

```bash
git clone https://github.com/PantoYT/TubeYou.git
cd TubeYou
cp .env.example .env
docker compose up --build -d
```

Open [http://localhost:8080](http://localhost:8080). MySQL creates the schema
and demo data only when its volume is empty.

Mail-dependent flows require a valid `RESEND_API_KEY` and a verified sending
domain. Keep the key in `.env` locally and in the `tubeyou-mail` Kubernetes
Secret on production; never commit it.

For the older XAMPP/LAN workflow, see
[docs/instrukcje.md](docs/instrukcje.md).

## Performance

- paginated homepage, Shorts and comments;
- Redis-backed feed/suggestion cache and rate limiting;
- native image lazy loading with asynchronous decoding outside the initial
  viewport;
- eager loading for the first visible thumbnails;
- video metadata preload and poster images;
- optimized Composer autoload in the production image.

## Security

- prepared PDO statements and server-side input validation;
- CSRF protection on state-changing requests;
- session ID rotation after login;
- upload MIME checks and disabled directory listing;
- trusted-proxy handling for client IP rate limits;
- production exception handling without exposing stack traces;
- secrets excluded from both the image build context and Git.

## CI/CD

GitHub Actions runs CI on pushes and pull requests:

- Docker Compose validation;
- shell and PHP syntax checks;
- production image build;
- Composer metadata validation;
- FFmpeg and required PHP extension checks.

Production deployment is intentionally manual for now: the image is built on
the HP server, imported into K3s containerd and rolled out with the scripts in
`deploy/`. Automated CD still needs a secure delivery path to the private
homelab; no public SSH port or privileged GitHub runner is configured.

## Project structure

```text
controllers/  HTTP handlers
models/       PDO repositories
views/        server-rendered templates
services/     external integrations
helpers/      auth, CSRF, cache and rate limiting
database/     schema and deterministic demo seed
public/       front controller, assets and uploads
docker/       container configuration
deploy/       K3s manifests and operational scripts
```

## Screenshots

### Home

![TubeYou home](docs/screenshots/home.png)

### Video page

![TubeYou video page](docs/screenshots/video.png)

### Profile

![TubeYou profile](docs/screenshots/profile.png)

### Comments

![TubeYou comments](docs/screenshots/comments.png)

## Roadmap

- [x] Self-hosted K3s deployment
- [x] Redis sessions, cache and rate limiting
- [x] Transactional email and inbound alias
- [x] CI validation
- [x] Native lazy loading pass
- [ ] Automated CD to the private server
- [ ] Comment mentions
- [ ] Community posts

## License

MIT — see [LICENSE](LICENSE).
