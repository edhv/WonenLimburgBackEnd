## Based on https://github.com/Wodby/docker4wordpress
Developed on Wordpress v.4.6.1 

## Development
gulp develop

## Tools
PhpMyAdmin: http://localhost:8001
Webserver: http://locahost:8000

## Requirements
- Docker
- Docker Compose
- Npm
- Gulp

### Setup Docker environment
- Clone this repo
- Download the latest worpdress and paste the contents in this folder, beware to not overwrite the wp-content folder
- docker-compose up
- import the databse using localhost:8001 ( phpmyadmin )

### Backup database
docker-compose exec mariadb sh -c 'exec mysqldump --all-databases -uroot -p"root-password"' > databases.sql
