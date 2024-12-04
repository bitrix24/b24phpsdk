# This file is part of the B24PhpSdk package.
#
#  For the full copyright and license information, please view the MIT-LICENSE.txt
#  file that was distributed with this source code.
#!/usr/bin/env make

export COMPOSE_HTTP_TIMEOUT=120
export DOCKER_CLIENT_TIMEOUT=120

default:
	@echo "make needs target:"
	@egrep -e '^\S+' ./Makefile | grep -v default | sed -r 's/://' | sed -r 's/^/ - /'

%:
	@: # silence

# load default and personal env-variables
ENV := $(PWD)/tests/.env
ENV_LOCAL := $(PWD)/tests/.env.local
include $(ENV)
-include $(ENV_LOCAL)

init:
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

up:
	@echo "run application…"
	docker-compose up --build -d

down:
	@echo "stop application and remove containers"
	docker-compose down --remove-orphans

down-clear:
	@echo "stop application and remove containers with volumes"
	docker-compose down -v --remove-orphans

restart: down up

php-cli-bash:
	docker-compose run --rm php-cli sh $(filter-out $@,$(MAKECMDGOALS))

composer-install:
	@echo "install dependencies…"
	docker-compose run --rm php-cli composer install
composer-update:
	@echo "update dependencies…"
	docker-compose run --rm php-cli composer update
composer-dumpautoload:
	docker-compose run --rm php-cli composer dumpautoload
# call composer with any parameters
# make composer install
# make composer "install --no-dev"
composer:
	docker-compose run --rm php-cli composer $(filter-out $@,$(MAKECMDGOALS))

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

dev-show-fields-description:
	php bin/console b24:util:show-fields-description --webhook=$(BITRIX24_WEBHOOK)

# build examples for rest-api documentation
build-examples-for-documentation:
	@php bin/console b24-dev:generate-examples \
	--examples-folder=docs/api \
	--prompt-template=docs/api/file-templates/gpt/master-prompt-template.md \
	--example-template=docs/api/file-templates/examples/master-example.php \
	--openai-api-key=$(DOCUMENTATION_OPEN_AI_API_KEY) \
	--docs-repo-folder=$(DOCUMENTATION_REPOSITORY_FOLDER)

# check allowed licenses
lint-allowed-licenses:
	vendor/bin/composer-license-checker

# linters & code style
lint-cs-fixer:
	vendor/bin/php-cs-fixer check --verbose --diff
lint-cs-fixer-fix:
	vendor/bin/php-cs-fixer fix --verbose --diff
lint-phpstan:
	vendor/bin/phpstan --memory-limit=1G analyse -v
lint-rector:
	vendor/bin/rector process --dry-run
lint-rector-fix:
	vendor/bin/rector process

# unit tests
test-unit:
	vendor/bin/phpunit --testsuite unit_tests --display-warnings

# integration tests with granularity by api-scope
test-integration-scope-telephony:
	vendor/bin/phpunit --testsuite integration_tests_scope_telephony
test-integration-scope-workflows:
	vendor/bin/phpunit --testsuite integration_tests_scope_workflows
test-integration-scope-im:
	vendor/bin/phpunit --testsuite integration_tests_scope_im
test-integration-scope-placement:
	vendor/bin/phpunit --testsuite integration_tests_scope_placement
test-integration-scope-im-open-lines:
	vendor/bin/phpunit --testsuite integration_tests_scope_im_open_lines
test-integration-scope-user:
	vendor/bin/phpunit --testsuite integration_tests_scope_user
test-integration-scope-user-consent:
	vendor/bin/phpunit --testsuite integration_tests_scope_user_consent
test-integration-core:
	vendor/bin/phpunit --testsuite integration_tests_core