COMPOSE := docker compose -f docker/docker-compose.yml

all: up install test

up:
	$(COMPOSE) up -d

install:
	$(COMPOSE) run --rm php composer install

test:
	$(COMPOSE) run --rm php composer run-script test

coverage:
	$(COMPOSE) run -e XDEBUG_MODE=coverage --rm php  vendor/bin/phpunit --coverage-clover build/logs/clover.xml

down: 
	$(COMPOSE) down -v

ci: up install coverage down