# =====================================================
# WordPress Docker Makefile
# =====================================================

COMPOSE = docker compose --env-file .env -f docker/compose.yml

APP = app
DB = db

.DEFAULT_GOAL := help

.PHONY: help build up down restart stop logs ps shell \
        composer wp db-shell mysql clean fresh

## -------------------------
## Help
## -------------------------

help:
	@echo ""
	@echo "WordPress Docker Commands"
	@echo ""
	@echo "make build         Build Docker images"
	@echo "make up            Start containers"
	@echo "make down          Stop containers"
	@echo "make restart       Restart containers"
	@echo "make stop          Stop containers"
	@echo "make logs          View logs"
	@echo "make ps            Show running containers"
	@echo "make shell         Open bash inside WordPress container"
	@echo "make db-shell      Open MySQL shell"
	@echo "make composer      Run Composer"
	@echo "make wp            Run WP-CLI"
	@echo "make clean         Remove containers"
	@echo "make fresh         Remove everything including database"
	@echo ""

## -------------------------
## Docker
## -------------------------

build:
	$(COMPOSE) build

up:
	$(COMPOSE) up -d

build_refresh:
	$(COMPOSE) build --no-cache
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

restart:
	$(COMPOSE) restart

stop:
	$(COMPOSE) stop

logs:
	$(COMPOSE) logs -f

ps:
	$(COMPOSE) ps

## -------------------------
## Shell
## -------------------------

shell:
	$(COMPOSE) exec $(APP) bash

db-shell:
	$(COMPOSE) exec $(DB) mysql -u root -p

## -------------------------
## Composer
## -------------------------

composer:
	$(COMPOSE) exec $(APP) composer $(cmd)

## -------------------------
## WP CLI
## -------------------------

wp:
	$(COMPOSE) exec $(APP) wp $(cmd) --allow-root

## -------------------------
## Cleanup
## -------------------------

clean:
	$(COMPOSE) down --remove-orphans

fresh:
	$(COMPOSE) down -v --remove-orphans