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
I spent more than 3 hours. Maybe 3 or 4 hours of api development and 3 or 4 hours for swagger documentation, readme, datafixtures and installation tests.