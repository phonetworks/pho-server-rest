cd /opt/pho-server-rest
sudo composer install
sudo supervisorctl reload
sudo supervisorctl update
sudo systemctl enable supervisor
sudo service supervisor start
