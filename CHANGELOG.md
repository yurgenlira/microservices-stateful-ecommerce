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
