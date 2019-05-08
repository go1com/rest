RESTful service builder [![Build Status](https://travis-ci.org/go1com/rest.svg?branch=master)](https://travis-ci.org/go1com/rest)
====

Base on [slim 3.x](https://www.slimframework.com/)

## Install

```
composer require go1/rest:dev-master

# To use CLI
composer require symfony/console:^v4.2.3 symfony/yaml:^v4.2.3
```

## CLI

### Usage

```
php vendor/bin/rest-cli.php composer > composer.json
php vendor/bin/rest-cli.php docker-compose > docker-compose.yml
```

### Build phar file

```
# Install https://github.com/clue/phar-composer
git clone git@github.com:go1com/rest.git
cd rest
COMPOSER=composer-cli.json composer install --no-dev -vvv
cp rest-cli.php index.php
phar-composer build ./ -v
```
