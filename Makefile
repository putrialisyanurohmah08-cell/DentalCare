DOCKER_COMPOSE=docker compose

.PHONY: build up down logs shell install artisan composer npm migrate seed test

build:
	$(DOCKER_COMPOSE) build

up:
	$(DOCKER_COMPOSE) up -d

down:
	$(DOCKER_COMPOSE) down

logs:
	$(DOCKER_COMPOSE) logs -f

shell:
	$(DOCKER_COMPOSE) exec app sh

install:
	$(DOCKER_COMPOSE) exec app composer install
	$(DOCKER_COMPOSE) exec app npm install
	$(DOCKER_COMPOSE) exec app php artisan key:generate
	$(DOCKER_COMPOSE) exec app php artisan migrate --seed

artisan:
	$(DOCKER_COMPOSE) exec app php artisan $(ARGS)

composer:
	$(DOCKER_COMPOSE) exec app composer $(ARGS)

npm:
	$(DOCKER_COMPOSE) exec app npm $(ARGS)

migrate:
	$(DOCKER_COMPOSE) exec app php artisan migrate

seed:
	$(DOCKER_COMPOSE) exec app php artisan db:seed

test:
	$(DOCKER_COMPOSE) exec app php artisan test
