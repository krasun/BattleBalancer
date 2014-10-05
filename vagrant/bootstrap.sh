apt-get update
apt-get install curl
apt-get install -y php5 php5-cli php5-curl
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
cd /home/vagrant/battle-balancer
composer install