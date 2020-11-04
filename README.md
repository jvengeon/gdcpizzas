# Requirements

 1. php > 7.2
 2. mysql
 3. [composer](https://getcomposer.org/)

# Installation
```bash
git clone https://github.com/jvengeon/gdcpizzas.git
cd gdcpizzas
```


 ## If you want use mysql with docker and load fixtures for testing (dev mode)
 
 - Install [symfony cli](https://symfony.com/download)
 
 ```bash
docker-compose up -d 
symfony serve -d
composer install
symfony console doctrine:migrations:migrate
symfony console doctrine:fixtures:load
```

 ## If you have your own mysql and don't use fixtures
 
 - define your env variables in  (APP_ENV=prod and DATABASE_URL) : [symfony documentation](https://symfony.com/doc/current/deployment.html#b-configure-your-environment-variables)
 ```bash
composer install --no-dev --optimize-autoloader
```

If your database does not exists
 ```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

If your database already exists
 ```bash
php bin/console doctrine:migrations:migrate
```

# Documentation

Documentation is provided by swagger UI

In dev :
http://127.0.0.1:8000/api/doc

In production:
YOUR_URL/api/doc

# Improvements with more time

 - Add tests with phpunit
 - Add cache invalidation or use HTTP Validation Strategies instead basic HTTP Expiration Strategies
 - Better documentation in swagger (Find the way to rename schemas)
 - Use translations for error messages
 - Better management of ingredient priority to reorder automatically others ingredients
 - Create custom ParamConverter to handle 404 in JsonResponse
 

# Informations
I spent more than 3 hours. Maybe 4 hours of api development and 3 or 4 hours for swagger documentation, readme, datafixtures and installation tests.

My first idea was to use API Platform to respect the 3 hours, because it respects REST standards, automatically generates the doc and other advanced features.

But I thought it wouldn't show enough of my way of coding, so I chose to code the API myself, even if it meant spending more time on it.

## Experience
 - Symfony 5 : First use, on a daily basis I use symfony 3.4 or 4 since 3 years
 - Swagger: First implementation, on a daily basis, I use apis developed by another team, but i use swagger UI
 - Docker: Daily use for dev environnement since 5 years