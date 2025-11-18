# ----------------------------------------
#  GLOBAL SETTINGS
# ----------------------------------------
SHELL := /bin/bash
DOCKER := docker compose
APP := backend
FRONT := frontend

# ----------------------------------------
#  GENERAL DOCKER COMMANDS
# ----------------------------------------

up:
	$(DOCKER) up -d

down:
	$(DOCKER) down

restart:
	$(DOCKER) down && $(DOCKER) up -d

build:
	$(DOCKER) build

rebuild:
	$(DOCKER) down && $(DOCKER) up -d --build --remove-orphans

logs:
	$(DOCKER) logs -f

logs-build:
	$(DOCKER) logs metal-app

ps:
	$(DOCKER) ps

# ----------------------------------------
#  APP (Laravel) COMMANDS
# ----------------------------------------

app-bash:
	$(DOCKER) exec -it app bash

queue-bash:
	$(DOCKER) exec -it queue bash

app-logs:
	$(DOCKER) logs -f metal-app

queue-logs:
	$(DOCKER) logs -f metal-queue

artisan:
	$(DOCKER) exec app php artisan $(cmd)

migrate:
	$(DOCKER) exec app php artisan migrate

migrate-fresh:
	$(DOCKER) exec app php artisan migrate:fresh --seed

seed:
	$(DOCKER) exec app php artisan db:seed

cache-clear:
	$(DOCKER) exec app php artisan optimize:clear

optimize:
	$(DOCKER) exec app php artisan optimize

tinker:
	$(DOCKER) exec -it app php artisan tinker

# Health debugging
app-health:
	$(DOCKER) inspect --format='{{json .State.Health}}' metal-app | jq

mysql-health:
	$(DOCKER) inspect --format='{{json .State.Health}}' metal-mysql | jq

elastic-health:
	curl -XGET localhost:9200/_cluster/health?pretty

queue-health:
	$(DOCKER) inspect --format='{{json .State.Health}}' metal-queue | jq

# ----------------------------------------
# PHPStan
# ----------------------------------------
stan:
	$(DOCKER) exec app vendor/bin/phpstan analyse --memory-limit=1G

stan-clear:
	rm -rf backend/storage/phpstan

# ----------------------------------------
# Rector
# ----------------------------------------
rector:
	$(DOCKER) exec app vendor/bin/rector process

rector-dry:
	$(DOCKER) exec app vendor/bin/rector process --dry-run

# ----------------------------------------
# Pint
# ----------------------------------------
pint:
	$(DOCKER) exec app vendor/bin/pint

pint-dry:
	$(DOCKER) exec app vendor/bin/pint --test

# ----------------------------------------
# Code Quality & Testing
# ----------------------------------------
lint:
	$(DOCKER) exec app composer lint

lint-fix:
	$(DOCKER) exec app composer lint:fix

test:
	$(DOCKER) exec app composer test

# ----------------------------------------
#  COMPOSER
# ----------------------------------------

composer:
	$(DOCKER) exec app composer $(cmd)

composer-install:
	$(DOCKER) exec app composer install

composer-update:
	$(DOCKER) exec app composer update

# ----------------------------------------
#  FRONTEND (Nuxt) COMMANDS
# ----------------------------------------

front-bash:
	$(DOCKER) exec -it frontend bash

front-dev:
	$(DOCKER) exec -it frontend npm run dev

front-build:
	$(DOCKER) exec frontend npm run build

front-install:
	$(DOCKER) exec frontend npm install

front-logs:
	$(DOCKER) logs -f metal-frontend

# ----------------------------------------
#  ELASTICSEARCH COMMANDS
# ----------------------------------------

es-health:
	curl -X GET "localhost:9200/_cluster/health?pretty"

es-indexes:
	curl -X GET "localhost:9200/_cat/indices?v"

es-delete-index:
	curl -X DELETE "localhost:9200/$(index)"

# ----------------------------------------
#  REDIS COMMANDS
# ----------------------------------------

redis-cli:
	$(DOCKER) exec -it redis redis-cli

redis-flush:
	$(DOCKER) exec -it redis redis-cli FLUSHALL

# ----------------------------------------
#  SOKETI / WEBSOCKETS
# ----------------------------------------

soketi-logs:
	$(DOCKER) logs -f metal-soketi

# ----------------------------------------
#  UTILITIES
# ----------------------------------------

setup:
	@echo "🚀 Setting up Metal Exchange application..."
	@echo "📦 Building and starting containers..."
	$(DOCKER) up -d --build
	@echo "⏳ Waiting for services to be healthy..."
	sleep 10
	@echo "🔑 Generating application key..."
	$(DOCKER) exec app php artisan key:generate
	@echo "🗃️ Running migrations..."
	$(DOCKER) exec app php artisan migrate --force
	@echo "🌱 Seeding database..."
	$(DOCKER) exec app php artisan db:seed
	@echo "✅ Setup complete! Application available at http://localhost:8000"

install:
	make composer-install
	make front-install

reset:
	make down
	make setup

health:
	@echo "🏥 Checking service health..."
	@echo "MySQL:" && make mysql-health
	@echo "App:" && make app-health
	@echo "Elasticsearch:" && make elastic-health

help:
	@echo "Available commands:"
	@echo ""
	@echo " Setup:"
	@echo "   make setup             - Complete application setup (build, migrate, seed)"
	@echo "   make health            - Check all service health status"
	@echo ""
	@echo " Docker:"
	@echo "   make up                - Start all containers"
	@echo "   make down              - Stop all containers"
	@echo "   make restart           - Restart containers"
	@echo "   make rebuild           - Rebuild and start fresh"
	@echo ""
	@echo " Backend (Laravel):"
	@echo "   make artisan cmd=xxx   - Run any artisan command"
	@echo "   make migrate           - Run migrations"
	@echo "   make migrate-fresh     - Fresh migration + seed"
	@echo "   make seed              - Seed database"
	@echo "   make app-bash          - SSH into app container"
	@echo "   make queue-bash        - SSH into queue worker"
	@echo ""
	@echo " Frontend (Nuxt):"
	@echo "   make front-dev         - Run dev server"
	@echo "   make front-build       - Build frontend"
	@echo "   make front-bash        - Shell into frontend container"
	@echo ""
	@echo " Elasticsearch:"
	@echo "   make es-health         - Check cluster health"
	@echo "   make es-indexes        - List indexes"
	@echo "   make es-delete-index index=name"
	@echo ""
	@echo " Redis:"
	@echo "   make redis-cli         - Open redis cli"
	@echo "   make redis-flush       - Flush redis"
	@echo ""
	@echo " Utilities:"
	@echo "   make install           - Install dependencies (backend + frontend)"
	@echo "   make reset             - Full project reset"