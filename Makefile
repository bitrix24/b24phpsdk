# This file is part of the B24PhpSdk package.
#
#  For the full copyright and license information, please view the MIT-LICENSE.txt
#  file that was distributed with this source code.
#!/usr/bin/env make

export COMPOSE_HTTP_TIMEOUT=120
export DOCKER_CLIENT_TIMEOUT=120

.DEFAULT_GOAL := help

%:
	@: # silence

# load default and personal env-variables
ENV := $(PWD)/tests/.env
ENV_LOCAL := $(PWD)/tests/.env.local
include $(ENV)
-include $(ENV_LOCAL)

help:
	@echo "-------------------------"
	@echo "    Bitrix24 PHP SDK"
	@echo "-------------------------"
	@echo ""
	@echo "docker-init               - first installation"
	@echo "docker-up                 - run docker"
	@echo "docker-down               - stop docker"
	@echo "docker-down-clear         - stop docker and remove orphaned containers"
	@echo "docker-pull               - download images and ignore pull failures"
	@echo "docker-restart            - restart containers"
	@echo "composer-install          - install dependencies from composer"
	@echo "composer-update           - update dependencies from composer"
	@echo "composer-dumpautoload     - regenerate composer autoload file"
	@echo "composer                  - run composer and pass arguments"
	@echo "php-dev-server-up         - start php dev-server"
	@echo "php-dev-server-down       - stop php dev-server"
	@echo "php-cli-bash              - run container php-cli and open shell with arguments"
	@echo "ngrok-up                  - start ngrok"
	@echo "ngrok-down                - stop ngrok"
	@echo "lint-allowed-licenses     - lint dependencies for valid licenses"
	@echo "lint-cs-fixer             - lint source code with php-cs-fixer"
	@echo "lint-cs-fixer-fix         - fix source code with php-cs-fixer"
	@echo "lint-phpstan              - lint source code with phpstan"
	@echo "lint-rector               - lint source code with rector"
	@echo "lint-rector-fix           - fix source code with rector"
	@echo ""


.PHONY: docker-init
docker-init:
	@echo "remove all containers"
	docker-compose down --remove-orphans
	@echo "build containers"
	docker-compose build
	@echo "install dependencies"
	docker-compose run --rm php-cli composer install
	@echo "change owner of var folder for access from container"
    docker-compose run --rm php-cli chown -R www-data:www-data /var/www/html/var/
	@echo "run application…"
	docker-compose up -d

.PHONY: docker-up
docker-up:
	@echo "run application…"
	docker-compose up --build -d

.PHONY: docker-down
docker-down:
	@echo "stop application and remove containers"
	docker-compose down --remove-orphans

.PHONY: docker-down-clear
docker-down-clear:
	@echo "stop application and remove containers with volumes"
	docker-compose down -v --remove-orphans

.PHONY: docker-pull
docker-pull:
	docker compose pull --ignore-pull-failures

.PHONY: docker-restart
docker-restart: down up

# work with composer in docker container
.PHONY: composer-install
composer-install:
	@echo "install dependencies…"
	docker-compose run --rm php-cli composer install

.PHONY: composer-update
composer-update:
	@echo "update dependencies…"
	docker-compose run --rm php-cli composer update

.PHONY: composer-dumpautoload
composer-dumpautoload:
	docker-compose run --rm php-cli composer dumpautoload

.PHONY: composer
# call composer with any parameters
# make composer install
# make composer "install --no-dev"
composer:
	docker-compose run --rm php-cli composer $(filter-out $@,$(MAKECMDGOALS))

# linters and tests
.PHONY: lint-allowed-licenses
lint-allowed-licenses:
	docker-compose run --rm php-cli vendor/bin/composer-license-checker

.PHONY: lint-cs-fixer
lint-cs-fixer:
	docker-compose run --rm php-cli vendor/bin/php-cs-fixer check --verbose --diff

.PHONY: lint-cs-fixer-fix
lint-cs-fixer-fix:
	docker-compose run --rm php-cli vendor/bin/php-cs-fixer fix --verbose --diff

.PHONY: lint-phpstan
lint-phpstan:
	docker-compose run --rm php-cli vendor/bin/phpstan --memory-limit=2G analyse -vvv

.PHONY: lint-rector
lint-rector:
	docker-compose run --rm php-cli vendor/bin/rector process --dry-run

.PHONY: lint-rector-fix
lint-rector-fix:
	docker-compose run --rm php-cli vendor/bin/rector process

# unit tests
.PHONY: test-unit
test-unit:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite unit_tests --display-warnings

# integration tests with granularity by api-scope
.PHONY: test-integration-scope-telephony
test-integration-scope-telephony:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_telephony

.PHONY: test-integration-scope-workflows
test-integration-scope-workflows:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_workflows

.PHONY: test-integration-scope-im
test-integration-scope-im:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_im

.PHONY: test-integration-scope-placement
test-integration-scope-placement:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_placement

.PHONY: test-integration-scope-im-open-lines
test-integration-scope-im-open-lines:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_im_open_lines

.PHONY: test-integration-scope-user
test-integration-scope-user:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_user

.PHONY: test-integration-scope-user-consent
test-integration-scope-user-consent:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_user_consent

.PHONY: test-integration-core
test-integration-core:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_core

.PHONY: test-integration-scope-entity
test-integration-scope-entity:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_entity

.PHONY: test-integration-scope-ai-admin
test-integration-scope-ai-admin:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_ai_admin

.PHONY: test-integration-scope-crm
test-integration-scope-crm:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_crm

.PHONY: test-integration-scope-crm-activity
test-integration-scope-crm-activity:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_crm_activity

# work dev environment
.PHONY: php-dev-server-up
php-dev-server-up:
	docker-compose run --rm -p 10080:10080 php-cli php -S 0.0.0.0:10080 -t tests/ApplicationBridge

.PHONY: php-dev-server-down
php-dev-server-down:
	docker-compose down --remove-orphans

.PHONY: php-cli-bash
php-cli-bash:
	docker-compose run --rm php-cli sh $(filter-out $@,$(MAKECMDGOALS))

.PHONY: ngrok-up
ngrok-up:
	ngrok http 127.0.0.1:10080

.PHONY: ngrok-down
ngrok-down:
	@pids=$$(ps aux | grep ' ngrok http' | grep -v 'grep' | awk '{print $$2}'); \
	if [ -n "$$pids" ]; then \
		echo "Killing process(es): $$pids"; \
		echo "$$pids" | xargs kill; \
	else \
		echo "No ngrok process found."; \
	fi


debug-show-env:
	@echo BITRIX24_WEBHOOK $(BITRIX24_WEBHOOK)
	@echo DOCUMENTATION_DEFAULT_TARGET_BRANCH $(DOCUMENTATION_DEFAULT_TARGET_BRANCH)

# build documentation
build-documentation:
	php bin/console b24-dev:generate-coverage-documentation \
	--webhook=$(BITRIX24_WEBHOOK) \
	--repository-url=https://github.com/bitrix24/b24phpsdk \
	--repository-branch=$(DOCUMENTATION_DEFAULT_TARGET_BRANCH) \
	--file=docs/EN/Services/bitrix24-php-sdk-methods.md

show-sdk-coverage-statistics:
	php bin/console b24-dev:show-sdk-coverage-statistics \
	--webhook=$(BITRIX24_WEBHOOK)

dev-show-fields-description:
	php bin/console b24-dev:show-fields-description --webhook=$(BITRIX24_WEBHOOK)

# build examples for rest-api documentation
build-examples-for-documentation:
	@php bin/console b24-dev:generate-examples \
	--examples-folder=docs/api \
	--prompt-template=docs/api/file-templates/gpt/master-prompt-template.md \
	--example-template=docs/api/file-templates/examples/master-example.php \
	--openai-api-key=$(DOCUMENTATION_OPEN_AI_API_KEY) \
	--docs-repo-folder=$(DOCUMENTATION_REPOSITORY_FOLDER)

