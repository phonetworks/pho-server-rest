#!/usr/bin/env bash
sudo apt-get update

cd /tmp
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update
sudo apt-get remove php7.0 -y
sudo apt-get install -y redis-server gcc 
sudo apt-get install -y curl php7.1 php7.1-fpm php7.1-cli php7.1-common php7.1-mbstring php7.1-gd php7.1-intl php7.1-xml php7.1-mysql php7.1-mcrypt php7.1-zip php7.1-dev

# modified
wget https://github.com/phonetworks/pho-cli/raw/master/bin/ubuntu-16.04/libgraphqlparser_0.6.0-0ubuntu1_amd64.deb
wget https://github.com/phonetworks/pho-cli/raw/master/bin/ubuntu-16.04/php-graphql_0.6.0-0ubuntu1_amd64.deb
sudo dpkg -i ./libgraphqlparser_0.6.0-0ubuntu1_amd64.deb
sudo dpkg -i ./php-graphql_0.6.0-0ubuntu1_amd64.deb
# modified ends

sudo apt-get install -y composer
sudo mv /etc/php/7.0/mods-available/z_graphql.ini /etc/php/7.1/mods-available/z_graphql.ini
sudo ln -s /etc/php/7.1/mods-available/z_graphql.ini /etc/php/7.1/cli/conf.d/21-graphql.ini
sudo ln -s /usr/lib/php/20151012/graphql.so /usr/lib/php/20160303/graphql.so
git clone https://github.com/dosten/graphql-parser-php
cd graphql-parser-php
sudo phpize
sudo sed -i 's/graphql_parse_string/graphql_parse_string_with_experimental_schema_support/g' graphql.c
sudo ./configure
sudo make && sudo make install

# part 2
sudo useradd pho
sudo mkdir /var/log/pho
sudo chown -R pho.pho /var/log/pho
sudo apt-get install -y supervisor
sudo touch /etc/supervisor/conf.d/pho.conf
sudo echo -e "[program:pho]\ncommand=php /opt/pho-server-rest/run.php\nuser=pho\nautostart=true\nautorestart=true\nstderr_logfile=/var/log/pho/long.err.log\nstdout_logfile=/var/log/pho/long.out.log" > /etc/supervisor/conf.d/pho.conf
cd /opt/pho-server-rest
composer install
sudo supervisorctl reload
sudo supervisorctl update
sudo systemctl enable supervisor
