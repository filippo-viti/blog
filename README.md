# Symfony blog
## Requirements
- MySql database
- Composer
- Symfony console
## Database
Using MySql, create a new user and a new database both named ```blog```:
```
CREATE USER blog@localhost IDENTIFIED BY 'blog';
CREATE DATABASE blog;
GRANT ALL PRIVILEGES ON blog.* TO blog@localhost WITH GRANT OPTION;
```
## Local installation
```
composer install
symfony console doctrine:schema:create
symfony console doctrine:fixtures:load
```