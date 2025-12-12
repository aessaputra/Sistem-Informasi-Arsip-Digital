# =============================================================================
# Makefile - Docker Development Commands (FrankenPHP)
# Sanapati - Sistem Informasi Arsip Digital
# =============================================================================

.PHONY: help build up down restart logs shell artisan composer npm test migrate fresh seed install

# Auto-detect docker or podman-compose (Windows compatible)
ifeq ($(OS),Windows_NT)
    COMPOSE := docker-compose
else
    COMPOSE := $(shell command -v docker-compose 2>/dev/null || command -v podman-compose 2>/dev/null)
endif

# Default target
help:
	@echo ""
	@echo "🧟 FrankenPHP Docker Commands"
	@echo "=============================="
	@echo ""
	@echo "📦 Setup:"
	@echo "  make setup        - First-time setup"
	@echo "  make install      - Install dependencies"
	@echo ""
	@echo "🚀 Development:"
	@echo "  make dev          - Start development"
	@echo "  make dev-full     - Start with Vite hot reload"
	@echo "  make down         - Stop containers"
	@echo "  make restart      - Restart containers"
	@echo ""
	@echo "🏭 Production:"
	@echo "  make build        - Build production images"
	@echo "  make prod         - Start production"
	@echo "  make prod-down    - Stop production"
	@echo ""
	@echo "🔧 Utilities:"
	@echo "  make shell        - PHP container shell"
	@echo "  make logs         - View logs"
	@echo "  make ps           - Show containers"
	@echo ""
	@echo "📝 Laravel:"
	@echo "  make artisan c='...'     - Run artisan"
	@echo "  make migrate             - Run migrations"
	@echo "  make fresh               - Fresh migrate + seed"
	@echo "  make test                - Run all tests"
	@echo ""

# =============================================================================
# Setup
# =============================================================================

setup:
	@echo "🚀 Setting up FrankenPHP environment..."
	@if [ ! -f .env ]; then \
		cp .env.docker.example .env; \
		echo "✅ Created .env"; \
	fi
	@$(COMPOSE) build
	@$(COMPOSE) up -d
	@echo "⏳ Waiting for containers..."
	@sleep 15
	@$(COMPOSE) exec app composer install
	@$(COMPOSE) exec app php artisan key:generate
	@$(COMPOSE) exec app php artisan migrate
	@$(COMPOSE) exec app php artisan storage:link
	@echo ""
	@echo "✅ Setup complete!"
	@echo "   App: http://localhost:8000"

install:
	@$(COMPOSE) exec app composer install
	@$(COMPOSE) run --rm node npm install

# =============================================================================
# Development
# =============================================================================

dev:
	@echo "🧟 Starting FrankenPHP development..."
	@$(COMPOSE) up -d
	@echo "✅ App: http://localhost:8000"

dev-full:
	@$(COMPOSE) --profile frontend up -d
	@echo "✅ App: http://localhost:8000"
	@echo "   Vite: http://localhost:5173"

down:
	@$(COMPOSE) down

restart:
	@$(COMPOSE) restart

# =============================================================================
# Production
# =============================================================================

build:
	@$(COMPOSE) -f docker-compose.prod.yml build

prod:
	@$(COMPOSE) -f docker-compose.prod.yml up -d

prod-down:
	@$(COMPOSE) -f docker-compose.prod.yml down

# =============================================================================
# Utilities
# =============================================================================

shell:
	@$(COMPOSE) exec app bash

logs:
	@$(COMPOSE) logs -f

logs-app:
	@$(COMPOSE) logs -f app

ps:
	@$(COMPOSE) ps

# =============================================================================
# Laravel Commands
# =============================================================================

artisan:
	@$(COMPOSE) exec app php artisan $(c)

migrate:
	@$(COMPOSE) exec app php artisan migrate

fresh:
	@$(COMPOSE) exec app php artisan migrate:fresh --seed

seed:
	@$(COMPOSE) exec app php artisan db:seed

test:
	@$(COMPOSE) exec app php artisan test

tinker:
	@$(COMPOSE) exec app php artisan tinker

cache-clear:
	@$(COMPOSE) exec app php artisan cache:clear
	@$(COMPOSE) exec app php artisan config:clear
	@$(COMPOSE) exec app php artisan route:clear
	@$(COMPOSE) exec app php artisan view:clear

optimize:
	@$(COMPOSE) exec app php artisan config:cache
	@$(COMPOSE) exec app php artisan route:cache
	@$(COMPOSE) exec app php artisan view:cache

# =============================================================================
# Package Management
# =============================================================================

composer:
	@$(COMPOSE) exec app composer $(c)

npm:
	@$(COMPOSE) run --rm node npm $(c)

# =============================================================================
# Cleanup
# =============================================================================

clean:
	@$(COMPOSE) down -v --remove-orphans

clean-all:
	@$(COMPOSE) down -v --remove-orphans --rmi local
