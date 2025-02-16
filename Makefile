install:
	composer install

PORT ?= 8000
start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT)  -t public

start-local:
	php -S localhost:8080 -t public public/index.php

validate:
	composer validate

lint:
	composer exec --verbose phpcs -- --standard=PSR12 app public templates

lint-fix:
	composer exec --verbose phpcbf -- --standard=PSR12 app public templates
