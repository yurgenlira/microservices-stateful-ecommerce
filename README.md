# Microservices Stateful E-commerce

[![CI](https://github.com/yurgenlira/microservices-stateful-ecommerce/actions/workflows/ci.yml/badge.svg)](https://github.com/yurgenlira/microservices-stateful-ecommerce/actions/workflows/ci.yml)

A production-oriented e-commerce platform built as a series of progressive levels in a single repository — each level a self-contained, production-ready deliverable that builds directly on the previous one without rewrites, evolving from a containerized Laravel monolith to a fully distributed microservices architecture on Amazon EKS with GitOps, observability and platform engineering.

**Current state: Monolith MVP** — foundational monolith with modular DDD structure, full local dev environment via Docker Compose, and a CI pipeline with quality gates and security scanning.

## What the MVP demonstrates

**Containerization**
- Multi-stage Docker build — separate stages for `base`, `composer-deps`, `development` (Alpine + Xdebug for coverage) and `production` (non-root, no dev dependencies)
- Nginx embedded in both development and production images communicating with PHP-FPM via Unix socket

**CI/CD Pipeline** (GitHub Actions)
- Three parallel jobs: `quality` (Pest + PHPStan L6 + Pint), `build` (Docker multi-stage + Trivy), `diagrams` (Structurizr DSL validation)
- Composer dependency cache and Docker GHA layer cache
- Trivy blocking merge on CRITICAL CVEs

**Developer Experience**
- `make project-init && make fresh` sets up the full environment from a fresh clone
- `make quality` enforces test coverage ≥ 60%, static analysis and code style in one command
- Structured JSON logging to `stderr` — compatible with CloudWatch and Grafana Loki out of the box

**Architecture as Code**
- C4 diagrams (Context, Containers, Components) in Structurizr DSL — versioned in Git, exported to PNG via `extenda/structurizr-to-png` (Puppeteer, no server required), validated in CI
- ERD generated from live PostgreSQL schema via `tbls`

**Modular DDD Structure**
- Four domain modules (`User`, `Catalog`, `Ordering`, `Inventory`), each owning its migrations, factories, routes and service provider
- No cross-module database imports — domain boundaries enforced from the first commit to enable future service extraction

## Prerequisites

- Docker 27+
- Docker Compose v2
- GNU Make

## Quick Start

\`\`\`bash
git clone https://github.com/yurgenlira/microservices-stateful-ecommerce.git
cd microservices-stateful-ecommerce
cp services/app/.env.example services/app/.env
make project-init
make fresh
\`\`\`

App available at `http://localhost:8080`.

## Module Structure

\`\`\`
services/app/app/Modules/
├── User/
│   ├── Database/Migrations/    # users table
│   ├── Database/Factories/
│   ├── Http/Controllers/
│   ├── Http/Requests/
│   ├── Models/
│   ├── Providers/ModuleServiceProvider.php
│   ├── Routes/api.php
│   ├── Services/
│   └── Tests/
├── Catalog/                    # categories, products
├── Ordering/                   # orders, order_items, payments
└── Inventory/                  # inventory_stocks, inventory_movements
\`\`\`

Each module owns its migrations, factories, routes, and service provider. No cross-module imports at the database layer.

## Architecture

Diagrams follow the [C4 model](https://c4model.com). Three levels are maintained — Level 4 (Code) is intentionally omitted because IDEs and the source code itself are the authoritative reference for class-level detail.

| Level | Diagram | What it shows |
|---|---|---|
| L1 — Context | SystemContext | External actors (Customer, Admin) and the system boundary |
| L2 — Containers | Containers | Laravel app, PostgreSQL and Redis — runtime deployables and their protocols |
| L3 — Components | Components | The four DDD modules inside the Laravel app and their DB table ownership |

![System Context](docs/architecture/exports/structurizr-SystemContext.png)

![Containers](docs/architecture/exports/structurizr-Containers.png)

![Components](docs/architecture/exports/structurizr-Components.png)

> `make diagrams` — exports all three PNGs using `extenda/structurizr-to-png` (Puppeteer renderer, no server required).
> `make diagrams-open` — starts Structurizr Lite locally at `http://localhost:8081` for interactive editing.

## Database Schema

![ERD](docs/database/schema.svg)

> Run `make erd` to regenerate after schema changes (requires `make up`).

## Makefile Targets

| Target | Description |
|---|---|
| `make project-init` | First-time setup: env, build, deps, hooks |
| `make hooks` | Install git hooks (lefthook) |
| `make up` | Start app services (app, postgres, redis) |
| `make down` | Stop all services |
| `make fresh` | Drop all tables, re-run migrations and seeders |
| `make shell` | Open a shell inside the app container |
| `make db` | Open a psql session |
| `make redis-cli` | Open a redis-cli session |
| `make test` | Run Pest test suite |
| `make lint` | Run Laravel Pint (check only) |
| `make fix` | Run Laravel Pint (auto-fix) |
| `make analyze` | Run PHPStan level 6 |
| `make quality` | Run test + lint + analyze |
| `make diagrams` | Export C4 diagrams to `docs/architecture/exports/` |
| `make diagrams-open` | Start Structurizr Lite at `http://localhost:8081` |
| `make erd` | Generate DB schema docs in `docs/database/` |
| `make status` | Show container status |
| `make help` | Show all available targets |

## Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 (PHP 8.5) |
| Database | PostgreSQL 18 |
| Cache / Sessions | Redis 7.4 |
| Web server | Nginx 1.27 (embedded in app container) |
| CI | GitHub Actions — quality + build + diagrams jobs |
| Architecture docs | Structurizr DSL + extenda/structurizr-to-png |

## Environment Variables

Copy `services/app/.env.example` to `services/app/.env` and adjust if needed. Required variables:

| Variable | Description |
|---|---|
| `APP_KEY` | Generated automatically by `make project-init` |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | PostgreSQL credentials |
| `REDIS_PASSWORD` | Redis auth password |
| `REDIS_CACHE_DB` | Redis database index for cache (default: 0) |
| `REDIS_SESSION_DB` | Redis database index for sessions (default: 1) |

## Changelog

See [Changelog](CHANGELOG.md)
