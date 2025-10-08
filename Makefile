.PHONY: build install test clean help

help: ## Show this help message
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Build the image (one-time)
	docker build -t php-dev .

install: ## Install dependencies
	docker run --rm -v $$(pwd):/app php-dev composer install

test: ## Run PHPUnit
	docker run --rm -v $$(pwd):/app php-dev composer dump-env test
	docker run --rm -v $$(pwd):/app php-dev ./vendor/bin/phpunit --display-deprecations

requirements: ## Show PHP requirements
	docker run --rm -v $$(pwd):/app php-dev composer check-platform-reqs

upgrade: ## Upgrade dependencies
	docker run --rm -v $$(pwd):/app php-dev composer update

clean: ## Clean up
	rm -rf vendor/