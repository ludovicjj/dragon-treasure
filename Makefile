#---DOCKER---#
DOCKER = docker
DOCKER_RUN = $(DOCKER) run
DOCKER_COMPOSE = docker compose
DOCKER_COMPOSE_UP = $(DOCKER_COMPOSE) up -d
DOCKER_COMPOSE_DOWN = $(DOCKER_COMPOSE) down
DOCKER_COMPOSE_STOP = $(DOCKER_COMPOSE) stop
#------------#

#---SYMFONY---#
SYMFONY = symfony
SYMFONY_SERVER_START = $(SYMFONY) serve -d
SYMFONY_SERVER_STOP = $(SYMFONY) server:stop
SYMFONY_CONSOLE = $(SYMFONY) console
#-------------#

## === DOCKER ===============================================
docker-up: ## Start docker containers.
	$(DOCKER_COMPOSE_UP)
.PHONY: docker-up

docker-stop: ## Stop docker containers.
	$(DOCKER_COMPOSE_STOP)
.PHONY: docker-stop

docker-down: ## Stop and remove docker containers.
	$(DOCKER_COMPOSE_DOWN)
.PHONY: docker-down
#-------------------------------------------------------------#

## === SYMFONY ===============================================
sf-start: ## Start symfony server.
	$(SYMFONY_SERVER_START)
.PHONY: sf-start

sf-stop: ## Stop symfony server.
	$(SYMFONY_SERVER_STOP)
.PHONY: sf-stop

sf-cc: ## Clear symfony cache.
	$(SYMFONY_CONSOLE) cache:clear
.PHONY: sf-cc

sf-dc: ## Create symfony database.
	$(SYMFONY_CONSOLE) doctrine:database:create --if-not-exists
.PHONY: sf-dc

sf-dd: ## Drop symfony database.
	$(SYMFONY_CONSOLE) doctrine:database:drop --if-exists --force
.PHONY: sf-dd

sf-mm: ## Make migration.
	$(SYMFONY_CONSOLE) make:migration
.PHONY: sf-mm

sf-dmm: ## Migrate.
	$(SYMFONY_CONSOLE) doctrine:migrations:migrate --no-interaction
.PHONY: sf-dmm

sf-fixtures: ## Load Fixtures.
	$(SYMFONY_CONSOLE) doctrine:fixtures:load --no-interaction
.PHONY: sf-fixtures

sf-dump-current-api-config: ## Dump current configuration of api platform.
	$(SYMFONY_CONSOLE) debug:config api_platform
.PHONY: sf-dump-current-api-config

sf-dump-api-config: ## Dump a full tree of possible configuration of api platform.
	$(SYMFONY_CONSOLE) config:dump api_platform
.PHONY: sf-dump-api-config

sf-dump-env: ## Dump Env var into env.*.
	$(SYMFONY_CONSOLE) debug:dotenv
.PHONY: sf-dump-env

sf-dump-env-container: ## Dump Env container.
	$(SYMFONY_CONSOLE) debug:container --env-vars
.PHONY: sf-dump-env-container

sf-dump-params: ## Dump container parameters.
	$(SYMFONY_CONSOLE) debug:container --parameters
.PHONY: sf-dump-params

sf-dump-routes: ## Dump routes.
	$(SYMFONY_CONSOLE) debug:router
.PHONY: sf-dump-routes

sf-test: ## run test
	$(SYMFONY) php bin/phpunit
.PHONY: sf-test
#------------------------------------------------------------#