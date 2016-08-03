#!/bin/bash

echo "Deleting old database config..."
mysql -h$dbhost -u$dbuser -p$dbpass -P$dbport --execute "DROP DATABASE IF EXISTS $dbname;"
#mysql -h$dbhost -u$dbuser -p$dbpass -P$dbport --execute "GRANT USAGE ON *.* TO '${MYSQL_APP_USER}'@'${MYSQL_HOST}';"
#mysql -h$dbhost -u$dbuser -p$dbpass -P$dbport --execute "DROP USER '${MYSQL_APP_USER}'@'${MYSQL_HOST}';"

echo "Creating mysql database..."
mysql -h$dbhost -u$dbuser -p$dbpass -P$dbport --execute "CREATE DATABASE $dbname;"
#mysql ${MYSQL_CONNECTION_ARGS} --execute "CREATE USER '${MYSQL_APP_USER}'@'${MYSQL_HOST}' IDENTIFIED BY '${MYSQL_APP_PASSWORD}';"
#mysql ${MYSQL_CONNECTION_ARGS} --execute "GRANT ALL on ${DB}.* to '${MYSQL_APP_USER}'@'%' IDENTIFIED BY '${MYSQL_APP_PASSWORD}';"
#mysql ${MYSQL_CONNECTION_ARGS} --execute "FLUSH PRIVILEGES;"

echo "Importing mysql schema and data"
mysql -h$dbhost -u$dbuser -p$dbpass -P$dbport -D$dbname < $htdocs/db/init/initial-database.sql