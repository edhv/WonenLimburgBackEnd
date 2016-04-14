FROM wordpress:latest

# Install mysql-client
RUN apt-get update && apt-get -y upgrade
RUN apt-get install -y mysql-client unzip
ENV TERM xterm

# Install wp-cli
ADD https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar /usr/local/bin/wp
RUN chmod +x /usr/local/bin/wp

# Configure PHP
RUN docker-php-ext-install zip

# Configure Apache
RUN sed -i "s/AllowOverride None/AllowOverride All/g" /etc/apache2/apache2.conf
RUN a2enmod rewrite

# remove cache
RUN rm -rf /usr/local/etc/php/conf.d/opcache-recommended.ini


# Fix OSX permission / owner problem
RUN usermod -u 1000 www-data
RUN chown -R www-data /var/www/html


# Add some scripts for development
RUN mkdir -p /scripts
ADD ./scripts/backup-db.sh /scripts/backup-db.sh
ADD ./scripts/restore-db.sh /scripts/restore-db.sh
RUN chmod 755 /scripts/*.sh