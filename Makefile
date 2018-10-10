.PHONY: init-dev

IMAGE=dkr.hanaboso.net/hanaboso/mongodatagrid/
PHP=dkr.hanaboso.net/hanaboso/symfony3-base:php-7.2
DC=docker-compose
DE=docker-compose exec -T php
DM=docker-compose exec -T mariadb

.env:
	sed -e "s|{DEV_UID}|$(shell id -u)|g" \
		-e "s|{DEV_GID}|$(shell id -u)|g" \
		.env.dist >> .env;

docker-pull:
	$(DC) pull

docker-up-force: .env docker-pull
	$(DC) up -d --force-recreate --remove-orphans

docker-down:
	$(DC) down

destroy-dev:
	$(DC) down -v

dev-build: .env
	cd docker/php-dev && docker pull ${PHP} && docker build -t ${IMAGE}app:dev . && docker push ${IMAGE}app:dev

init-dev: .env docker-pull docker-up-force composer-install clear-cache

composer-install:
	$(DE) composer install --ignore-platform-reqs

composer-update:
	$(DE) composer update --ignore-platform-reqs

clear-cache:
	$(DE) rm -rf temp/*
	$(DE) touch temp/.gitkeep

codesniffer:
	$(DE) vendor/bin/phpcs --standard=ruleset.xml --colors -p src tests

phpstan:
	$(DE) vendor/bin/phpstan analyse --memory-limit=512M -c phpstan.neon -l 7 src tests

phpintegration: clear-cache
	$(DE) vendor/bin/phpunit -c phpunit.xml.dist --colors tests/Integration

fasttest: codesniffer phpstan phpintegration

test: docker-up-force composer-install fasttest