# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Versioning Convention

**MAJOR.MINOR.PATCH**

- **Major** → Architecture changes (new service)
- **Minor** → New features or module
- **Patch** → Bug fix, dependency update, hotfix

## [Unreleased]

## [0.2.0]

### Added

- DDD boundaries: `PublicApi/` interfaces and `readonly` DTOs per module (User, Catalog, Ordering, Inventory)
- `NoCrossModuleModelImportRule` PHPStan custom rule — CI blocks cross-module Eloquent model imports
- Cache decorator pattern (`CachingXxxService`) per module; TTLs configurable in `config/modules.php`
- Unit tests for all 4 modules: base services and caching decorators — 9 files, 45 test cases, 86% coverage; `phpunit.xml` registers `Unit`, `Feature` and `Modules` suites
- `artisan cache:stats` — Redis hit/miss ratio via CLI
- `GET /api/health` — verifies PostgreSQL and Redis connectivity; returns 503 on dependency failure
- `GET /` redirects to `/up` — eliminates default Laravel welcome page and framework version exposure
- k6 load testing scripts: `tests/load/smoke.js` (1 VU 30s) and `tests/load/autoscale.js` (ramp to 100 VUs); `make load-smoke` and `make load-test` targets
- Sentry integration (`sentry/sentry-laravel`): `SentryRequestContext` middleware attaches request ID, user ID and route to every error event; `ValidationException` and `AuthenticationException` filtered from reports
- CD workflow (`cd.yml`): AWS OIDC authentication, ECR push with SHA tag, App Runner deployment with 900s stability wait; uses `cache-from: type=gha` to reuse CI `build` layer cache — no double build; `paths: services/app/**` prevents deploy on doc/infra/workflow-only commits
- CI `changes` job with `dorny/paths-filter@v4`: `quality` and `build` run only when `services/app/**` changes; doc/infra-only PRs skip both jobs
- `pre-commit` hook `diagram-freshness`: blocks commits that modify `docs/architecture/workspace.dsl` without staging updated PNG exports
- `pre-commit` hook `diagram-terravision`: blocks commits that modify Terraform resource files without staging updated `infrastructure*` exports
- `make terravision`, `make terravision-drawio`, `make terravision-view` targets — AWS infrastructure diagram from Terraform HCL via Terravision container

### Changed

- `tests/Feature/ExampleTest.php`: assertion target updated from `GET /` (302) to `GET /up` (200) after root route redirect was introduced
- Docker Compose: `redis:7.4.9-alpine` replaced with `valkey/valkey:8.0-alpine` — dev/prod parity with ElastiCache Valkey 8.0
- `phpstan.neon`: `excludePaths` added for `app/Modules/*/Tests/*`; `NoCrossModuleModelImportRule` registered as custom rule
- `make test` enforces 60% coverage threshold (`--min=60`); CI `quality` job mirrors the same threshold
- CI `quality` and `build` jobs now conditional on PHP file changes via `changes` job

### Removed

- CI `diagrams` job — C4 diagrams are now generated locally (`make diagrams`) and committed as part of the PR; `pre-commit` hook enforces freshness

### Infrastructure

- Terraform 1.15 + AWS Provider 6.x: VPC `10.0.0.0/16` (2 AZs), Security Groups (SG-to-SG ingress only), RDS, ElastiCache, S3, App Runner, ECR — organized under `environments/{shared,dev}/`
- S3 remote state with native lockfile (`use_lockfile = true`) — no DynamoDB table required
- Amazon RDS PostgreSQL 18 on `db.t3.micro` (20 GB gp3): AES-256 encryption at rest, `log_min_duration_statement=500ms`, backups disabled in dev
- Amazon ElastiCache Valkey 8.0 on `cache.t3.micro`: TLS in-transit, auth token, 1 node in dev / 2 nodes with automatic failover in prod
- AWS App Runner: 1–10 instances, `max_concurrency=25`, VPC Connector routing egress to RDS and ElastiCache in private subnets; `auto_deployments_enabled=false`
- Amazon S3: SSE-S3 encryption, versioning enabled, lifecycle policy (`temp/` expires 7 days, all objects transition to IA after 30 days)
- ECR repository in `environments/shared/` — image registry is environment-agnostic; images promoted across dev → prod by SHA tag

### Security

- GitHub Actions OIDC — federated identity eliminates static AWS access keys in repository
- AWS SSM Parameter Store `SecureString` for `APP_KEY`, `DB_PASSWORD`, `REDIS_AUTH_TOKEN`, `SENTRY_DSN` — injected at App Runner startup, never baked into the image
- SG-to-SG ingress rules — `rds-sg` accepts 5432 from `app-sg` only; `redis-sg` accepts 6379 from `app-sg` only; no CIDR-based ingress for internal traffic
- `APP_DEBUG=false` enforced in production via SSM-injected environment

### Documentation

- C4 `workspace.dsl` updated: Container view (RDS, ElastiCache, S3, Sentry) + Component view (4 DDD modules with PublicApi boundaries) + Deployment diagram L4 (App Runner, VPC, subnets, NAT Gateway, ECR, CI/CD topology)
- `docs/architecture/exports/`: `structurizr-AWSDeployment.png`, `structurizr-Components.png`, `structurizr-Containers.png` regenerated; `infrastructure.drawio.svg` and `infrastructure.html` added via Terravision
- README restructured for DevOps portfolio: PHP/Laravel/Terraform/AWS/CI/CD badges; `AWSDeployment` as hero diagram; remaining C4 diagrams in collapsible block; section renamed from `What the MVP demonstrates` to `Technical Highlights`

## [v0.1.0]

### Added

- Laravel 13 project scaffold with PHP 8.5
- DDD-ready module structure: User, Catalog, Ordering, Inventory
- `ModuleServiceProvider` per domain with isolated migrations and routes
- Shared VS Code settings and extension recommendations (`.vscode/`)
- Docker multi-stage build: base, composer-deps, development (Alpine + Xdebug), production stages
- `HEALTHCHECK` instruction embedded in production image (`wget /up`, interval 30s)
- Nginx 1.27 embedded in production image communicating with PHP-FPM via Unix socket
- Docker Compose stack: app, postgres (18.3-alpine), redis (7.4.9-alpine); Structurizr in `docker-compose.docs.yml`
- Makefile with targets: `project-init`, `hooks`, `up`, `down`, `restart`, `logs`, `fresh`, `shell`, `db`, `redis-cli`, `test`, `lint`, `fix`, `analyze`, `quality`, `diagrams`, `diagrams-open`, `erd`, `status`, `help`
- PostgreSQL 18 schema: users, categories, products, orders, order_items, payments, inventory_stocks, inventory_movements
- Database seeders: 100 users, 20 categories, 500 products, 500 inventory stocks, 50 orders
- Redis dual-database setup: DB 0 (cache) and DB 1 (sessions) with isolated Laravel connections
- GitHub Actions CI: three parallel jobs — `quality` (Pest + PHPStan + Pint, coverage comment on PR), `build` (Docker + Trivy), `diagrams` (C4 export validation)
- Composer dependency cache and Docker GHA layer cache in CI
- C4 architecture diagrams via Structurizr DSL; PNG export via `extenda/structurizr-to-png`; viewer via `docker-compose.docs.yml`
- lefthook git hooks: `commit-msg` (Conventional Commits regex), `pre-commit` (PHP lint + Pint sobre staged files via Docker), `pre-push` (PHPStan full analysis via Docker)

### Security

- Non-root user (`www`, UID 1000) in production Docker image
- `.env` excluded from version control; `.env.example` with safe placeholder values
- Trivy CRITICAL severity scan blocking CI merge
- Redis `requirepass` authentication enforced in all environments
- Nginx security headers: X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, Referrer-Policy

### Infrastructure

- Docker Compose services pinned to exact patch versions: `postgres:18.3-alpine`, `redis:7.4.9-alpine`, `structurizr/structurizr:2026.04.19`
- GitHub Actions runner pinned to `ubuntu-24.04` across all jobs
- `extenda/structurizr-to-png:latest` — documented exception to pinning rule (no versioned releases)
- PHP-FPM pool `pm = ondemand`; `PHP_FPM_MAX_CHILDREN` tunable at runtime via env var without image rebuild
- `docker-compose.docs.yml` separates documentation tooling (Structurizr) from app stack

### Documentation

- `README.md` with setup instructions, module structure, architecture diagrams and Makefile reference
- `.github/pull_request_template.md` with quality and changelog checklist
- C4 Context, Container and Component diagrams in `docs/architecture/workspace.dsl`
- `docs/database/schema.svg` + per-table Markdowns generated from live schema via `make erd` (tbls)
