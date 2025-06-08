# Makefile

# Имя docker-compose файла
DC=docker compose

# Название контейнера Laravel
APP=app

# Запуск всех контейнеров
up:
	$(DC) up -d --build

# Остановка всех контейнеров
down:
	$(DC) down

# Перезапуск контейнеров
restart: down up

# Доступ внутрь Laravel контейнера
bash:
	$(DC) exec $(APP) bash

# Выполнение artisan команд
artisan:
	$(DC) exec $(APP) php artisan $(filter-out $@,$(MAKECMDGOALS))

# Выполнение composer команд
composer:
	$(DC) exec $(APP) composer $(filter-out $@,$(MAKECMDGOALS))

# Установка зависимостей npm
npm-install:
	$(DC) exec $(APP) npm install

# Запуск reverb
reverb-start:
	$(DC) restart reverb

# Очистка кэшей
cache-clear:
	$(DC) exec $(APP) php artisan config:clear && \
	php artisan route:clear && \
	php artisan view:clear

# Очистка и заново миграция БД
refresh-db:
	$(DC) exec $(APP) php artisan migrate:fresh --seed

# Живой tail логов Laravel
logs:
	$(DC) logs -f $(APP)

# Tail всех логов
logs-all:
	$(DC) logs -f

.PHONY: up down restart bash artisan composer npm-install echo-start cache-clear refresh-db logs logs-all
