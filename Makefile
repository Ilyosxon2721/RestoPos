# RestoPOS Makefile
# Использование: make <command>

.PHONY: help install dev start stop restart build test lint clean deploy

# Colors
GREEN  := \033[0;32m
YELLOW := \033[0;33m
CYAN   := \033[0;36m
RESET  := \033[0m

# Default target
.DEFAULT_GOAL := help

##@ Помощь
help: ## Показать эту справку
	@awk 'BEGIN {FS = ":.*##"; printf "\n${CYAN}RestoPOS Development Commands${RESET}\n\nUsage:\n  make ${GREEN}<target>${RESET}\n"} /^[a-zA-Z_0-9-]+:.*?##/ { printf "  ${GREEN}%-15s${RESET} %s\n", $$1, $$2 } /^##@/ { printf "\n${YELLOW}%s${RESET}\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

##@ Установка
install: ## Полная установка проекта
	@echo "${CYAN}🚀 Installing RestoPOS...${RESET}"
	composer install
	npm install
	cp -n .env.example .env || true
	php artisan key:generate
	php artisan migrate --seed
	npm run build
	@echo "${GREEN}✅ Installation complete!${RESET}"

setup: ## Быстрая настройка для нового разработчика
	@echo "${CYAN}📦 Setting up development environment...${RESET}"
	composer install
	npm install
	cp -n .env.example .env || true
	php artisan key:generate
	@echo "${GREEN}✅ Setup complete! Run 'make dev' to start${RESET}"

##@ Разработка
dev: ## Запустить dev-серверы (serve + vite)
	@echo "${CYAN}🔥 Starting development servers...${RESET}"
	npx concurrently -n "laravel,vite" -c "green,blue" "php artisan serve" "npm run dev"

serve: ## Запустить только Laravel сервер
	php artisan serve

vite: ## Запустить только Vite
	npm run dev

horizon: ## Запустить Laravel Horizon
	php artisan horizon

queue: ## Запустить очереди
	php artisan queue:work --tries=3

schedule: ## Запустить планировщик
	php artisan schedule:work

tinker: ## Открыть Laravel Tinker
	php artisan tinker

##@ Docker
up: ## Запустить Docker контейнеры
	docker compose up -d
	@echo "${GREEN}✅ Containers started${RESET}"
	@echo "App: http://localhost:8000"
	@echo "Mail: http://localhost:8025"

down: ## Остановить Docker контейнеры
	docker compose down

restart: ## Перезапустить контейнеры
	docker compose restart

logs: ## Показать логи контейнеров
	docker compose logs -f

shell: ## Войти в контейнер приложения
	docker compose exec app sh

mysql-cli: ## Войти в MySQL CLI
	docker compose exec mysql mysql -urestopos -psecret restopos

redis-cli: ## Войти в Redis CLI
	docker compose exec redis redis-cli

build: ## Пересобрать Docker образы
	docker compose build --no-cache

tools: ## Запустить с инструментами (phpMyAdmin, Redis Commander)
	docker compose --profile tools up -d

##@ База данных
migrate: ## Запустить миграции
	php artisan migrate

migrate-fresh: ## Пересоздать БД с сидами
	php artisan migrate:fresh --seed

rollback: ## Откатить миграции
	php artisan migrate:rollback

seed: ## Запустить сиды
	php artisan db:seed

##@ Тестирование
test: ## Запустить все тесты
	php artisan test

test-parallel: ## Запустить тесты параллельно
	php artisan test --parallel

test-coverage: ## Тесты с отчётом покрытия
	php artisan test --coverage --coverage-html=coverage

test-unit: ## Только unit тесты
	php artisan test --testsuite=Unit

test-feature: ## Только feature тесты
	php artisan test --testsuite=Feature

##@ Качество кода
lint: ## Проверить код (Pint + PHPStan)
	./vendor/bin/pint --test
	./vendor/bin/phpstan analyse

fix: ## Исправить стиль кода (Pint)
	./vendor/bin/pint

stan: ## Статический анализ (PHPStan)
	./vendor/bin/phpstan analyse

ide: ## Сгенерировать IDE хелперы
	php artisan ide-helper:generate
	php artisan ide-helper:models -N
	php artisan ide-helper:meta

##@ Кэш
cache-clear: ## Очистить весь кэш
	php artisan optimize:clear

cache: ## Закэшировать для production
	php artisan optimize

##@ Production
build-prod: ## Собрать для production
	npm run build
	php artisan optimize
	@echo "${GREEN}✅ Production build complete${RESET}"

deploy: ## Деплой (для CI/CD)
	composer install --no-dev --optimize-autoloader
	npm ci
	npm run build
	php artisan migrate --force
	php artisan optimize
	php artisan queue:restart
	@echo "${GREEN}✅ Deployment complete${RESET}"

##@ Утилиты
clean: ## Очистить временные файлы
	rm -rf node_modules
	rm -rf vendor
	rm -rf storage/framework/cache/*
	rm -rf storage/framework/sessions/*
	rm -rf storage/framework/views/*
	rm -rf bootstrap/cache/*
	rm -rf coverage
	@echo "${GREEN}✅ Cleaned${RESET}"

routes: ## Показать все маршруты
	php artisan route:list

models: ## Показать информацию о моделях
	php artisan model:show

env: ## Показать текущее окружение
	php artisan env

key: ## Сгенерировать новый ключ
	php artisan key:generate

storage-link: ## Создать симлинк storage
	php artisan storage:link
