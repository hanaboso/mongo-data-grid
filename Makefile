.PHONY: init-dev

IMAGE=dkr.hanaboso.net/hanaboso/mongodatagrid/
PHP=dkr.hanaboso.net/hanaboso/symfony3-base:php-7.3
DC=docker-compose
DE=docker-compose exec -T php
DM=docker-compose exec -T mariadb

.env:
	sed -e "s|{DEV_UID}|$(shell id -u)|g" \
		-e "s|{DEV_GID}|$(shell id -u)|g" \
		.env.dist >> .env;

# Docker
docker-up-force: .env
	$(DC) pull
	$(DC) up -d --force-recreate --remove-orphans

docker-down-clean: .env
	$(DC) down -v

dev-build: .env
	cd docker/php-dev && docker pull ${PHP} && docker build -t ${IMAGE}app:dev . && docker push ${IMAGE}app:dev

# Composer
composer-install:
	$(DE) composer install --ignore-platform-reqs

composer-update:
	$(DE) composer update --ignore-platform-reqs

composer-outdated:
	$(DE) composer outdated

# Console
clear-cache:
	$(DE) sudo rm -rf temp

# App dev
init-dev: docker-up-force composer-install

codesniffer:
	$(DE) ./vendor/bin/phpcs --standard=./ruleset.xml --colors -p src/ tests/

phpstan:
	$(DE) ./vendor/bin/phpstan analyse -c ./phpstan.neon -l 7 src/ tests/

phpintegration:
	$(DE) ./vendor/bin/phpunit -c phpunit.xml.dist --colors --stderr tests/Integration

test: docker-up-force composer-install fasttest

fasttest: clear-cache codesniffer phpstan phpintegration
