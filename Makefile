.DEFAULT_GOAL := help

.PHONY: help
help: ## Show available commands
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

.PHONY: route-list
route-list: ## List all registered routes
	@php artisan route:list --ansi --except-vendor

.PHONY: rl
rl: route-list ## Alias for route-list

.PHONY: pint
pint: ## Run Pint code style fixer
	@export XDEBUG_MODE=off
	@$(CURDIR)/vendor/bin/pint --parallel
	@unset XDEBUG_MODE

.PHONY: test-pint
test-pint: ## Run Pint code style fixer in test mode
	@export XDEBUG_MODE=off
	@$(CURDIR)/vendor/bin/pint --test --parallel
	@unset XDEBUG_MODE=off

.PHONY: rector
rector: ## Run Rector
	@$(CURDIR)/vendor/bin/rector process

.PHONY: test-rector
test-rector: ## Run Rector in test mode
	@$(CURDIR)/vendor/bin/rector process --dry-run

.PHONY: phpstan
phpstan: ## Run PHPStan
	@$(CURDIR)/vendor/bin/phpstan analyse --ansi --memory-limit=2G

.PHONY: p
p: phpstan ## Alias for phpstan

.PHONY: test-phpstan
test-phpstan: ## Run PHPStan in test mode
	@$(CURDIR)/vendor/bin/phpstan analyse --ansi --memory-limit=2G

.PHONY: format
format: rector pint ## Run Pint and Rector and try to fixes the source code

.PHONY: f
f: format ## Alias for format

.PHONY: check
check: test-rector test-pint test-phpstan ## Run Pint, PHPStan with Rector in dry-run mode

.PHONY: c
c: check ## Alias for check

.PHONY: test
test: ## Run all tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact

.PHONY: t
t: test ## Alias for test

.PHONY: test-unit
test-unit: ## Run unit tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact --group=unit

.PHONY: test-feature
test-feature: ## Run feature tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact --group=feature

.PHONY: setup-test-db
setup-test-db: ## Create the testing database for running tests
	@PGHOST=localhost PGUSER=postgres PGPASSWORD=postgres createdb test_sycorax 2>/dev/null || echo "Database test_sycorax already exists"

## Module Tests

.PHONY: test-module-applications
test-module-applications: ## Run applications module tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact app-modules/applications/tests/

.PHONY: test-module-candidates
test-module-candidates: ## Run candidates module tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact app-modules/candidates/tests/

.PHONY: test-module-feedback
test-module-feedback: ## Run feedback module tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact app-modules/feedback/tests/

.PHONY: test-module-links
test-module-links: ## Run links module tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact app-modules/links/tests/

.PHONY: test-module-location
test-module-location: ## Run location module tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact app-modules/location/tests/

.PHONY: test-module-panel-admin
test-module-panel-admin: ## Run panel-admin module tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact app-modules/panel-admin/tests/

.PHONY: test-module-panel-app
test-module-panel-app: ## Run panel-app module tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact app-modules/panel-app/tests/

.PHONY: test-module-panel-organization
test-module-panel-organization: ## Run panel-organization module tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact app-modules/panel-organization/tests/

.PHONY: test-module-permissions
test-module-permissions: ## Run permissions module tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact app-modules/permissions/tests/

.PHONY: test-module-recruitment
test-module-recruitment: ## Run recruitment module tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact app-modules/recruitment/tests/

.PHONY: test-module-screening
test-module-screening: ## Run screening module tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact app-modules/screening/tests/

.PHONY: test-module-teams
test-module-teams: ## Run teams module tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact app-modules/teams/tests/

.PHONY: test-module-term
test-module-term: ## Run term module tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact app-modules/term/tests/

.PHONY: test-module-users
test-module-users: ## Run users module tests
	@$(CURDIR)/vendor/bin/pest --parallel --compact app-modules/users/tests/

.PHONY: migrate-fresh
migrate-fresh: ## Run migrations and seed the database
	@php artisan migrate:fresh --seed

.PHONY: dev-seed
dev-seed: ## Run developer seed
	@php artisan db:seed --class=Database\\Seeders\\DevelopmentSeeder

.PHONY: env-up
env-up: ## Start the development environment
	@docker compose --file docker-compose.yml up --detach

.PHONY: env-down
env-down: ## Start the development environment
	@docker compose --file docker-compose.yml down --rmi all --volumes

.PHONY: dev
dev: ## Start the server
	@composer run-script dev

.PHONY: setup
setup: ## Setup the project
	@composer run-script setup
