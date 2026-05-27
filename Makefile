COMPOSE          = docker compose --env-file services/app/.env
DC_DIAGRAMS      = docker compose -f docker-compose.docs.yml
TF_FLAGS         = --profile terravision
LEFTHOOK_VERSION := 2.1.6
LEFTHOOK         := .tools/lefthook

.PHONY: up down restart logs shell db redis-cli fresh status \
        test lint fix analyze quality diagrams diagrams-open \
        terravision terravision-drawio terravision-view erd \
        load-smoke load-test \
        project-init hooks help

up: ## Start all services (app, postgres, redis)
	$(COMPOSE) up -d

down: ## Stop all services
	$(COMPOSE) down

restart: ## Restart all services
	$(COMPOSE) restart

logs: ## Stream logs from all services
	$(COMPOSE) logs -f

shell: ## Open a shell inside the app container
	$(COMPOSE) exec app sh

db: ## Open a psql session
	$(COMPOSE) exec postgres psql -U $${DB_USERNAME:-ecommerce} -d $${DB_DATABASE:-ecommerce}

redis-cli: ## Open a redis-cli session
	$(COMPOSE) exec redis redis-cli -a $${REDIS_PASSWORD:-redispassword}

fresh: ## Drop all tables, re-run migrations and seeders
	$(COMPOSE) exec app php artisan migrate:fresh --seed

status: ## Show container status
	$(COMPOSE) ps

test: ## Run Pest test suite
	$(COMPOSE) exec app ./vendor/bin/pest --coverage --min=60

lint: ## Run Laravel Pint (check only)
	$(COMPOSE) exec app ./vendor/bin/pint --test

fix: ## Run Laravel Pint (auto-fix)
	$(COMPOSE) exec app ./vendor/bin/pint

analyze: ## Run PHPStan level 6
	$(COMPOSE) exec app ./vendor/bin/phpstan analyse

quality: test lint analyze ## Run test + lint + analyze

diagrams: ## Export C4 diagrams to docs/architecture/exports/
	mkdir -p docs/architecture/exports
	docker run --rm \
	  -v "$$(pwd)/docs/architecture:/docs" \
	  extenda/structurizr-to-png:latest \
	  --path workspace.dsl \
	  --output exports \
	  --render-with structurizr

diagrams-open: ## Start Structurizr Lite and open in the browser
	$(COMPOSE) -f docker-compose.docs.yml up -d structurizr
	xdg-open http://localhost:8081 2>/dev/null || open http://localhost:8081

terravision: ## Generate AWS infrastructure diagram (PNG) from Terraform source
	mkdir -p docs/architecture/exports
	TV_FORMAT=png $(DC_DIAGRAMS) $(TF_FLAGS) run --rm terravision
	mv docs/architecture/exports/infrastructure-aws.dot.png docs/architecture/exports/infrastructure.png

terravision-drawio: ## Generate AWS infrastructure diagram (DrawIO) from Terraform source
	mkdir -p docs/architecture/exports
	TV_FORMAT=drawio $(DC_DIAGRAMS) $(TF_FLAGS) run --rm terravision
	mv docs/architecture/exports/infrastructure-aws.drawio docs/architecture/exports/infrastructure.drawio

terravision-view: ## Generate interactive HTML diagram and open in browser
	mkdir -p docs/architecture/exports
	$(DC_DIAGRAMS) $(TF_FLAGS) run --rm terravision-view
	mv docs/architecture/exports/infrastructure-aws.html docs/architecture/exports/infrastructure.html
	xdg-open docs/architecture/exports/infrastructure.html 2>/dev/null || open docs/architecture/exports/infrastructure.html

erd: ## Generate DB schema docs in docs/database/
	mkdir -p docs/database
	docker run --rm \
	  --network microservices-stateful-ecommerce_backend \
	  -v "$$(pwd)/docs/database:/output" \
	  ghcr.io/k1low/tbls:v1.94.5 doc \
	  "postgres://$${DB_USERNAME:-ecommerce}:$${DB_PASSWORD:-secret}@postgres:5432/$${DB_DATABASE:-ecommerce}?sslmode=disable" \
	  --force /output

load-smoke: ## Smoke test: 1 VU 30s against APP_URL (e.g. make load-smoke APP_URL=https://xxx.awsapprunner.com)
	docker run --rm \
	  -v "$$(pwd)/tests/load:/scripts" \
	  -e APP_URL=$(APP_URL) \
	  grafana/k6:latest run /scripts/smoke.js

load-test: ## Load test: ramp to 100 VUs to verify App Runner autoscaling (requires APP_URL)
	docker run --rm \
	  -v "$$(pwd)/tests/load:/scripts" \
	  -e APP_URL=$(APP_URL) \
	  grafana/k6:latest run /scripts/autoscale.js

project-init: ## First-time setup: env, build, deps, hooks
	@[ -f services/app/.env ] || cp services/app/.env.example services/app/.env
	$(COMPOSE) build app
	$(COMPOSE) up -d
	$(COMPOSE) exec app sh -c "cd /var/www && composer install"
	$(MAKE) hooks

$(LEFTHOOK):
	mkdir -p .tools
	curl -sSfL "https://github.com/evilmartians/lefthook/releases/download/v$(LEFTHOOK_VERSION)/lefthook_$(LEFTHOOK_VERSION)_Linux_x86_64" \
	  -o $(LEFTHOOK)
	chmod +x $(LEFTHOOK)

hooks: $(LEFTHOOK) ## Install git hooks (lefthook)
	$(LEFTHOOK) install

help: ## Show available targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
	  awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}' | sort
