# Docker environment

This docker environment is designed to provide the developers with the ability to develop the library without the need to install locally any services or utilities besides `docker` and `docker compose`. Even `php` and `composer` are not needed to be installed locally.

The environment can be built against any versions of the services. This comes handy both for development and testing purpose. Service versions are defined in the `.env` file.

## Prerequisites
* [Docker Engine](https://docs.docker.com/engine/) `v1.13+`
* [Docker Compose](https://docs.docker.com/compose/) `v2.0+`

## Configuration
```bash
# Initialize environment variables file.
cp .env.dist .env

# Optionally adjust environment variables to your needs.
vim .env
```

## Startup
```bash
# It is going to take quite some time...
docker compose up --detach

# Enjoy!
docker compose exec php bash

# Now inside the container, install composer dependencies
composer install
```

## Running tests

Inside the `php` container:

```bash
# Run tests without a geometry engine
vendor/bin/phpunit

# Run tests with GEOS
ENGINE=GEOS vendor/bin/phpunit

# Run tests with geosop
ENGINE=geosop vendor/bin/phpunit

# Run tests with Postgres + PostGIS
ENGINE=PDO_PGSQL vendor/bin/phpunit

# Run tests with MySQL
ENGINE=PDO_MYSQL MYSQL_HOST=mysql vendor/bin/phpunit

# Run tests with MariaDB
ENGINE=PDO_MYSQL MYSQL_HOST=mariadb vendor/bin/phpunit

# Run tests with SQLite + SpatiaLite
ENGINE=SQLite3 vendor/bin/phpunit
````

## Shutdown
```bash
docker compose down
```
