# Laravel Docker Development Makefile
# Usage: make <command>

# Variables
COMPOSE_DEV = docker compose -f compose.dev.yaml
COMPOSE_PROD = docker compose -f compose.prod.yaml
SHELL := /bin/bash
NODE_PATH = /home/www/.nvm/versions/node/v22.0.0/bin
NODE_CMD = bash -c export PATH=$(NODE_PATH):\$$PATH && npm
WORKSPACE = $(COMPOSE_DEV) exec workspace
ARTISAN = $(WORKSPACE) php artisan
COMPOSER = $(WORKSPACE) composer

# Colors for output
RED := \033[0;31m
GREEN := \033[0;32m
YELLOW := \033[0;33m
BLUE := \033[0;34m
PURPLE := \033[0;35m
CYAN := \033[0;36m
WHITE := \033[0;37m
RESET := \033[0m

# Default target
.DEFAULT_GOAL := help

# Help target
.PHONY: help
help: ## Show this help message
	@echo -e "$(CYAN)Laravel Docker Development Commands$(RESET)"
	@echo -e "$(YELLOW)================================$(RESET)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[0;32m%-20s\033[0m %s\n", $$1, $$2}'
	@echo ""
	@echo -e "$(CYAN)Examples:$(RESET)"
	@echo "  make up          # Start development environment"
	@echo "  make down        # Stop and remove containers"
	@echo "  make shell       # Access workspace container"
	@echo "  make logs        # View container logs"

# =============================================================================
# DOCKER COMPOSE COMMANDS
# =============================================================================

.PHONY: up
up: ## Start development environment
	@echo -e "$(GREEN)Starting development environment...$(RESET)"
	$(COMPOSE_DEV) up -d

.PHONY: up-build
up-build: ## Start development environment and rebuild containers
	@echo -e "$(GREEN)Building and starting development environment...$(RESET)"
	$(COMPOSE_DEV) up -d --build

.PHONY: down
down: ## Stop and remove containers
	@echo -e "$(YELLOW)Stopping development environment...$(RESET)"
	$(COMPOSE_DEV) down

.PHONY: down-volumes
down-volumes: ## Stop and remove containers with volumes
	@echo -e "$(RED)Stopping development environment and removing volumes...$(RESET)"
	$(COMPOSE_DEV) down -v

.PHONY: restart
restart: ## Restart development environment
	@echo -e "$(BLUE)Restarting development environment...$(RESET)"
	$(COMPOSE_DEV) restart

.PHONY: logs
logs: ## View container logs
	$(COMPOSE_DEV) logs -f

.PHONY: logs-web
logs-web: ## View web container logs
	$(COMPOSE_DEV) logs -f web

.PHONY: logs-php
logs-php: ## View PHP-FPM container logs
	$(COMPOSE_DEV) logs -f php-fpm

.PHONY: logs-db
logs-redis: ## View redis container logs
	$(COMPOSE_DEV) logs -f redis

.PHONY: logs-workspace
logs-workspace: ## View workspace container logs
	$(COMPOSE_DEV) logs -f workspace

.PHONY: logs-db
logs-db: ## View database container logs
	$(COMPOSE_DEV) logs -f postgres

# =============================================================================
# CONTAINER ACCESS
# =============================================================================

.PHONY: shell
shell: ## Access workspace container shell
	$(WORKSPACE) bash

.PHONY: shell-root
shell-root: ## Access workspace container as root
	$(COMPOSE_DEV) exec -u root workspace bash

.PHONY: shell-php
shell-php: ## Access PHP-FPM container shell
	$(COMPOSE_DEV) exec php-fpm bash

.PHONY: shell-web
shell-web: ## Access web container shell
	$(COMPOSE_DEV) exec web sh

.PHONY: shell-db
shell-db: ## Access database container shell
	$(COMPOSE_DEV) exec postgres psql -U laravel -d app

# =============================================================================
# LARAVEL COMMANDS
# =============================================================================

.PHONY: artisan
artisan: ## Run artisan command (usage: make artisan cmd="migrate")
	@if [ -z "$(cmd)" ]; then \
		echo -e "$(RED)Error: Please specify a command$(RESET)"; \
		echo "Usage: make artisan cmd=\"migrate\""; \
		exit 1; \
	fi
	$(ARTISAN) $(cmd)

.PHONY: migrate
migrate: ## Run database migrations
	$(ARTISAN) migrate

.PHONY: migrate-fresh
migrate-fresh: ## Fresh migration with seed
	$(ARTISAN) migrate:fresh --seed

.PHONY: migrate-rollback
migrate-rollback: ## Rollback last migration
	$(ARTISAN) migrate:rollback

.PHONY: seed
seed: ## Run database seeders
	$(ARTISAN) db:seed

.PHONY: cache-clear
cache-clear: ## Clear all Laravel caches
	$(ARTISAN) cache:clear
	$(ARTISAN) config:clear
	$(ARTISAN) route:clear
	$(ARTISAN) view:clear

.PHONY: optimize
optimize: ## Optimize Laravel for production
	$(ARTISAN) optimize

.PHONY: test
test: ## Run All tests
	$(COMPOSER) test

# =============================================================================
# COMPOSER COMMANDS
# =============================================================================

.PHONY: composer-install
composer-install: ## Install Composer dependencies
	$(COMPOSER) install

.PHONY: composer-update
composer-update: ## Update Composer dependencies
	$(COMPOSER) update

.PHONY: composer-require
composer-require: ## Install Composer package (usage: make composer-require pkg="laravel/sanctum")
	@if [ -z "$(pkg)" ]; then \
		echo -e "$(RED)Error: Please specify a package$(RESET)"; \
		echo "Usage: make composer-require pkg=\"laravel/sanctum\""; \
		exit 1; \
	fi
	$(COMPOSER) require $(pkg)

.PHONY: composer-dev
composer-dev: ## Install Composer dev dependencies
	$(COMPOSER) install --dev

# =============================================================================
# NODE.JS COMMANDS
# =============================================================================

.PHONY: npm-install
npm-install: ## Install npm dependencies
	$(WORKSPACE) $(NODE_CMD) install

.PHONY: frontend-up
frontend-up: ## Run npm dev server
	$(WORKSPACE) $(NODE_CMD) run dev

.PHONY: npm-build
npm-build: ## Build npm assets for production
	$(WORKSPACE) $(NODE_CMD) run build

.PHONY: npm-watch
npm-watch: ## Run npm watch
	$(WORKSPACE) $(NODE_CMD) run watch

# =============================================================================
# DATABASE COMMANDS
# =============================================================================

.PHONY: db-backup
db-backup: ## Create database backup
	@mkdir -p backups
	$(COMPOSE_DEV) exec postgres pg_dump -U laravel -d app > backups/backup_$$(date +%Y%m%d_%H%M%S).sql

.PHONY: db-restore
db-restore: ## Restore database from backup (usage: make db-restore file="backups/backup_20231201_120000.sql")
	@if [ -z "$(file)" ]; then \
		echo -e "$(RED)Error: Please specify a backup file$(RESET)"; \
		echo "Usage: make db-restore file=\"backups/backup_20231201_120000.sql\""; \
		exit 1; \
	fi
	$(COMPOSE_DEV) exec -T postgres psql -U laravel -d app < $(file)

# =============================================================================
# MAINTENANCE COMMANDS
# =============================================================================

.PHONY: clean
clean: ## Clean up Docker resources
	@echo -e "$(YELLOW)Cleaning up Docker resources...$(RESET)"
	docker system prune -f
	docker volume prune -f

.PHONY: clean-all
clean-all: ## Clean up all Docker resources (including images)
	@echo -e "$(RED)Cleaning up all Docker resources...$(RESET)"
	docker system prune -a -f
	docker volume prune -f

.PHONY: rebuild
rebuild: ## Rebuild all containers
	@echo -e "$(BLUE)Rebuilding all containers...$(RESET)"
	$(COMPOSE_DEV) down
	$(COMPOSE_DEV) build --no-cache
	$(COMPOSE_DEV) up -d

.PHONY: status
status: ## Show container status
	$(COMPOSE_DEV) ps

# =============================================================================
# PRODUCTION COMMANDS
# =============================================================================

.PHONY: prod-up
prod-up: ## Start production environment
	@echo -e "$(GREEN)Starting production environment...$(RESET)"
	$(COMPOSE_PROD) up -d

.PHONY: prod-down
prod-down: ## Stop production environment
	@echo -e "$(YELLOW)Stopping production environment...$(RESET)"
	$(COMPOSE_PROD) down

.PHONY: prod-build
prod-build: ## Build production environment
	@echo -e "$(BLUE)Building production environment...$(RESET)"
	$(COMPOSE_PROD) build --no-cache

.PHONY: prod-logs
prod-logs: ## View production logs
	$(COMPOSE_PROD) logs -f

# =============================================================================
# UTILITY COMMANDS
# =============================================================================

.PHONY: check
check: ## Check if containers are running
	@echo -e "$(CYAN)Checking container status...$(RESET)"
	@if $(COMPOSE_DEV) ps | grep -q "Up"; then \
		echo -e "$(GREEN)✓ Application containers are running$(RESET)"; \
	else \
		echo -e "$(RED)✗ Application containers are not running$(RESET)"; \
		echo "Run 'make up or make prod-up' to start them"; \
	fi

.PHONY: ports
ports: ## Show exposed ports
	@echo -e "$(CYAN)Exposed ports:$(RESET)"
	@echo "  Laravel App: http://localhost"
	@echo "  Vite Dev Server: http://localhost:5173"
	@echo "  PostgreSQL: localhost:5432"
	@echo "  Redis: localhost:6379 (internal)"

.PHONY: info
info: ## Show project information
	@echo -e "$(CYAN)Project Information:$(RESET)"
	@echo "  PHP Version: 8.4"
	@echo "  Node Version: 22.0.0"
	@echo "  Database: PostgreSQL 16"
	@echo "  Cache: Redis"
	@echo "  Web Server: Nginx"
	@echo ""
	@echo -e "$(CYAN)Quick Start:$(RESET)"
	@echo "  1. make up-build    # Start development environment"
	@echo "  2. make frontend-up     # (Optional) Start frontend dev server"
	@echo "  3. Visit http://localhost"

# =============================================================================
# DEVELOPMENT WORKFLOW
# =============================================================================

.PHONY: dev
dev: ## Start development workflow
	@echo -e "$(GREEN)Starting development workflow...$(RESET)"
	$(COMPOSE_DEV) up -d
	$(WORKSPACE) $(NODE_CMD) run dev

.PHONY: stop-dev
stop-dev: ## Stop development workflow
	@echo -e "$(YELLOW)Stopping development workflow...$(RESET)"
	$(COMPOSE_DEV) down
