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
	@echo ""
	@echo "composer-install          - install dependencies from composer"
	@echo "composer-update           - update dependencies from composer"
	@echo "composer-dumpautoload     - regenerate composer autoload file"
	@echo "composer                  - run composer and pass arguments"
	@echo ""
	@echo "php-dev-server-up         - start php dev-server"
	@echo "php-dev-server-down       - stop php dev-server"
	@echo "php-cli-bash              - run container php-cli and open shell with arguments"
	@echo "ngrok-up                  - start ngrok"
	@echo "ngrok-down                - stop ngrok"
	@echo ""
	@echo "lint-all                  - lint codebase with all linters step by step"
	@echo "lint-allowed-licenses     - lint dependencies for valid licenses"
	@echo "lint-cs-fixer             - lint source code with php-cs-fixer"
	@echo "lint-cs-fixer-fix         - fix source code with php-cs-fixer"
	@echo "lint-phpstan              - lint source code with phpstan"
	@echo "lint-rector               - lint source code with rector"
	@echo "lint-rector-fix           - fix source code with rector"
	@echo ""
	@echo "test-unit                 - run unit tests"
	@echo "test-integration-calendar-event - run Calendar Event integration tests"
	@echo "test-integration-calendar-resource - run Calendar Resource integration tests"
	@echo "test-integration-sale-basket-property - run BasketProperty integration tests"
	@echo "test-integration-sale-cashbox-handler - run CashboxHandler integration tests"
	@echo "test-integration-sale-cashbox - run Cashbox integration tests"
	@echo "test-integration-sale-delivery - run Delivery integration tests"
	@echo "test-integration-sale-delivery-extra-service - run DeliveryExtraService integration tests"
	@echo "test-integration-scope-paysystem - run Payment System integration tests"
	@echo "test-integration-scope-im-open-lines-connector - run IMOpenLines Connector integration tests"
	@echo "test-integration-sale-payment-item-basket - run PaymentItemBasket integration tests"
	@echo "test-integration-sale-payment-item-shipment - run PaymentItemShipment integration tests"
	@echo "test-integration-sale-property-relation - run PropertyRelation integration tests"
	@echo "test-integration-landing-page - run Landing Page integration tests"
	@echo "test-integration-landing-syspage - run Landing SysPage integration tests"
	@echo "test-integration-landing-repo - run Landing Repo integration tests"
	@echo "test-integration-landing-demos - run Landing Demos integration tests"
	@echo "test-integration-landing-role - run Landing Role integration tests"
	@echo "test-integration-scope-landing-template - run Landing Template integration tests"
	@echo "test-integration-im-open-lines-config - run IMOpenLines Config integration tests"
	@echo "test-integration-im-open-lines-crm-chat - run IMOpenLines CRMChat integration tests"
	@echo "test-integration-im-open-lines-session - run IMOpenLines Session integration tests"
	@echo "test-integration-im-open-lines-operator - run IMOpenLines Operator integration tests"
	@echo "test-integration-scope-lists - run Lists integration tests"
	@echo "test-integration-lists-service - run Lists Service integration tests"
	@echo "test-integration-lists-field - run Lists Field integration tests"
	@echo "test-integration-lists-section - run Lists Section integration tests"
	@echo "test-integration-lists-element - run Lists Element integration tests"

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

.PHONY: lint-all
lint-all: lint-allowed-licenses lint-cs-fixer lint-phpstan lint-rector

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

.PHONY: test-integration-scope-paysystem
test-integration-scope-paysystem:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_paysystem

.PHONY: test-integration-scope-im-open-lines-connector
test-integration-scope-im-open-lines-connector:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_im_open_lines_connector

.PHONY: test-integration-paysystem-service
test-integration-paysystem-service:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_paysystem_service

.PHONY: test-integration-paysystem-settings
test-integration-paysystem-settings:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_paysystem_settings

.PHONY: test-integration-scope-im-open-lines
test-integration-scope-im-open-lines:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_im_open_lines

.PHONY: test-integration-im-open-lines-config
test-integration-im-open-lines-config:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_open_lines_config

.PHONY: test-integration-im-open-lines-crm-chat
test-integration-im-open-lines-crm-chat:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_open_lines_crm_chat

.PHONY: test-integration-im-open-lines-session
test-integration-im-open-lines-session:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_open_lines_session

.PHONY: test-integration-im-open-lines-operator
test-integration-im-open-lines-operator:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_open_lines_operator

.PHONY: test-integration-scope-lists
test-integration-scope-lists:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_lists

.PHONY: test-integration-lists-service
test-integration-lists-service:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_lists_service

.PHONY: test-integration-lists-field
test-integration-lists-field:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_lists_field

.PHONY: test-integration-lists-section
test-integration-lists-section:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_lists_section

.PHONY: test-integration-lists-element
test-integration-lists-element:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_lists_element

.PHONY: test-integration-scope-user
test-integration-scope-user:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_user

.PHONY: test-integration-scope-user-consent
test-integration-scope-user-consent:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_user_consent

.PHONY: test-integration-core
test-integration-core:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_core

.PHONY: test-integration-core-list
test-integration-core-list:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_core-list

.PHONY: test-integration-scope-entity
test-integration-scope-entity:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_entity

.PHONY: test-integration-scope-ai-admin
test-integration-scope-ai-admin:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_ai_admin

.PHONY: test-integration-scope-log
test-integration-scope-log:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_log

.PHONY: test-integration-scope-sale
test-integration-scope-sale:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_sale

.PHONY: test-integration-scope-landing
test-integration-scope-landing:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_landing

.PHONY: test-integration-scope-landing-template
test-integration-scope-landing-template:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_landing_template

.PHONY: test-integration-landing-block
test-integration-landing-block:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_landing_block

.PHONY: test-integration-landing-site
test-integration-landing-site:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_landing_site

.PHONY: test-integration-landing-page
test-integration-landing-page:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_landing_page

.PHONY: test-integration-landing-syspage
test-integration-landing-syspage:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_landing_syspage

.PHONY: test-integration-landing-repo
test-integration-landing-repo:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_landing_repo

.PHONY: test-integration-landing-demos
test-integration-landing-demos:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_landing_demos

.PHONY: test-integration-landing-role
test-integration-landing-role:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_landing_role

.PHONY: test-integration-scope-sonet-group
test-integration-scope-sonet-group:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_sonet_group

.PHONY: test-integration-scope-disk
test-integration-scope-disk:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_disk

.PHONY: test-integration-disk-service
test-integration-disk-service:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_disk_service

.PHONY: test-integration-disk-file
test-integration-disk-file:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_disk_file

.PHONY: test-integration-disk-storage
test-integration-disk-storage:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_disk_storage
.PHONY: test-integration-scope-calendar
test-integration-scope-calendar:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_calendar

.PHONY: test-integration-calendar-event
test-integration-calendar-event:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_calendar_event

.PHONY: test-integration-calendar-resource
test-integration-calendar-resource:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_calendar_resource

.PHONY: test-integration-sale-status
test-integration-sale-status:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_status

.PHONY: test-integration-sale-status-lang
test-integration-sale-status-lang:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_status_lang

.PHONY: test-integration-scope-sale-shipment
test-integration-scope-sale-shipment:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_shipment

.PHONY: test-integration-scope-sale-shipment-property
test-integration-scope-sale-shipment-property:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_shipment_property

.PHONY: test-integration-scope-sale-shipment-property-value
test-integration-scope-sale-shipment-property-value:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_shipment_property_value

.PHONY: test-integration-scope-sale-shipment-item
test-integration-scope-sale-shipment-item:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_shipment_item

.PHONY: test-integration-sale-basket-property
test-integration-sale-basket-property:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_basket_property

.PHONY: test-integration-sale-basket
test-integration-sale-basket:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_basket

.PHONY: test-integration-sale-order
test-integration-sale-order:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_order

.PHONY: test-integration-sale-cashbox-handler
test-integration-sale-cashbox-handler:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_cashbox_handler

.PHONY: test-integration-sale-cashbox
test-integration-sale-cashbox:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_cashbox

.PHONY: test-integration-sale-payment-item-basket
test-integration-sale-payment-item-basket:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_payment_item_basket

.PHONY: test-integration-sale-payment-item-shipment
test-integration-sale-payment-item-shipment:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_payment_item_shipment

.PHONY: test-integration-sale-property-relation
test-integration-sale-property-relation:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_property_relation

.PHONY: test-integration-scope-crm
test-integration-scope-crm:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_crm

.PHONY: integration_tests_scope_crm_address
integration_tests_scope_crm_address:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_crm_address

.PHONY: integration_tests_scope_crm_deal_details
integration_tests_scope_crm_deal_details:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_crm_deal_details

.PHONY: integration_tests_scope_crm_contact_details
integration_tests_scope_crm_contact_details:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_crm_contact_details

.PHONY: integration_tests_lead_userfield
integration_tests_lead_userfield:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_lead_userfield

.PHONY: integration_tests_lead_userfield_use_case
integration_tests_lead_userfield_use_case:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_lead_userfield_use_case

.PHONY: integration_tests_scope_crm_currency
integration_tests_scope_crm_currency:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_crm_currency

.PHONY: integration_tests_deal_recurring
integration_tests_deal_recurring:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_deal_recurring

.PHONY: integration_tests_lead_contacts
integration_tests_lead_contacts:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_lead_contacts

.PHONY: integration_tests_lead_details
integration_tests_lead_details:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_lead_details

.PHONY: integration_tests_scope_automation
integration_tests_scope_automation:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_automation

.PHONY: integration_tests_crm_item
integration_tests_crm_item:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_crm_item

.PHONY: integration_tests_lead_productrows
integration_tests_lead_productrows:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_lead_productrows

.PHONY: integration_tests_crm_quote
integration_tests_crm_quote:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_crm_quote

.PHONY: integration_tests_crm_requisite
integration_tests_crm_requisite:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_crm_requisite

.PHONY: integration_tests_crm_preset_field
integration_tests_crm_preset_field:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_crm_preset_field

.PHONY: integration_tests_crm_requisite_userfield
integration_tests_crm_requisite_userfield:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_crm_requisite_userfield

.PHONY: integration_tests_crm_status
integration_tests_crm_status:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_crm_status

.PHONY: integration_tests_crm_timeline
integration_tests_crm_timeline:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_crm_timeline

.PHONY: integration_tests_department
integration_tests_department:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_department

.PHONY: integration_tests_task
integration_tests_task:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_task

.PHONY: integration_tests_sale
integration_tests_sale:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale

.PHONY: integration_tests_sale_payment
integration_tests_sale_payment:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_payment

.PHONY: test-integration-sale-delivery-handler
test-integration-sale-delivery-handler:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_delivery_handler

.PHONY: test-integration-sale-delivery
test-integration-sale-delivery:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_delivery

.PHONY: test-integration-sale-delivery-extra-service
test-integration-sale-delivery-extra-service:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_delivery_extra_service
.PHONY: integration_tests_sale_payment_item_basket
integration_tests_sale_payment_item_basket:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_sale_payment_item_basket

.PHONY: integration_tests_crm_documentgenerator_numerator
integration_tests_crm_documentgenerator_numerator:
	docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_crm_documentgenerator_numerator

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
	docker-compose run --rm php-cli php bin/console b24-dev:generate-coverage-documentation \
	--webhook=$(BITRIX24_WEBHOOK) \
	--repository-url=https://github.com/bitrix24/b24phpsdk \
	--repository-branch=$(DOCUMENTATION_DEFAULT_TARGET_BRANCH) \
	--file=docs/EN/Services/bitrix24-php-sdk-methods.md

show-sdk-coverage-statistics:
	docker-compose run --rm php-cli php bin/console b24-dev:show-sdk-coverage-statistics \
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
