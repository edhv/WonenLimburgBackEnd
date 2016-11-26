## Based on https://github.com/Wodby/docker4wordpress
phpmyadmin localhost:8001
Developed on Wordpress v.4.6.1 


NOtes
- Wordpress page edit templates list is based on user level, and set in functions.php

## Development
gulp develop

## Deploy
gulp upload-dev

## Requirement
- Docker
- Docker Compose
- Npm
- Gulp

### Setup Docker environment
- Clone this repo
- Download the latest worpdress and paste the contents in this folder, beware to not overwrite the wp-content folder
- docker-compose up
- import the databse using localhost:8001

### Backup database
docker-compose exec mariadb sh -c 'exec mysqldump --all-databases -uroot -p"root-password"' > databases.sql


