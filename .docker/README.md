# Docker environment

This docker environment is designed to provide the developers with the ability to develop the library without the need to install locally any services or utilities besides `docker` and `docker compose`. Even `php` and `composer` are not needed to be installed locally.

The environment can be built against any versions of the services. This comes handy both for development and testing purpose. Service versions are defined in the `.env` file.

## Prerequisites
* [Docker Engine](https://docs.docker.com/engine/) `v1.13+`
* [Docker Compose](https://docs.docker.com/compose/) `v2.0+`

## Configuration
```
# Initialize environment variables file.
cp .env.dist .env

# Optionally adjust environment variables to your needs.
vim .env
```

## Startup
```
# It is going to take quite some time...
docker compose up --detach

# Enjoy!
docker compose exec php bash
```

## Shutdown
```
docker compose down
```

## Installing composer dependencies
Assuming the current working directory is `.docker`:
```
docker run --rm --volume ${PWD}/..:/app composer install
```
