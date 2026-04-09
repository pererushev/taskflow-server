.PHONY: up down shell install migrate test lint optimize logs help

up: ## Запуск всех контейнеров
	docker compose up -d

down: ## Остановка контейнеров
	docker compose down

shell: ## Войти в PHP-контейнер
	docker compose exec -w /var/www/html app bash

install: ## Первая настройка проекта
	docker compose run --rm -w /var/www/html app composer install
	docker compose exec -w /var/www/html app cp .env.example .env
	docker compose exec -w /var/www/html app php artisan key:generate
	docker compose exec -w /var/www/html app php artisan storage:link
	docker compose exec -w /var/www/html app php artisan migrate --seed

migrate: ## Применить миграции
	docker compose exec -w /var/www/html app php artisan migrate

test: ## Запустить тесты
	docker compose exec -w /var/www/html app php artisan test

lint: ## Проверка кода (Pint + PHPStan)
	docker compose exec -w /var/www/html app ./vendor/bin/pint
	docker compose exec -w /var/www/html app ./vendor/bin/phpstan analyse

optimize: ## Очистка кэшей
	docker compose exec -w /var/www/html app php artisan optimize:clear

logs: ## Логи приложения
	docker compose logs -f --tail=50 app

help: ## Показать команды
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'