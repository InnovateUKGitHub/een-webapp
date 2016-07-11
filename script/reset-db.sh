#!/bin/bash -e

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
source "${SCRIPT_DIR}/mysql.environment"

MYSQL_CONNECTION_ARGS="--port=${MYSQL_PORT} --host=${MYSQL_HOST} --user=${MYSQL_ADMIN_USER} --password=${MYSQL_ADMIN_PASSWORD}"

echo "Deleting old database config..."
mysql ${MYSQL_CONNECTION_ARGS} --execute "DROP DATABASE IF EXISTS ${DB};"
mysql ${MYSQL_CONNECTION_ARGS} --execute "GRANT USAGE ON *.* TO '${MYSQL_APP_USER}'@'${MYSQL_HOST}';"
mysql ${MYSQL_CONNECTION_ARGS} --execute "DROP USER '${MYSQL_APP_USER}'@'${MYSQL_HOST}';"

echo "Creating mysql users and access..."
mysql ${MYSQL_CONNECTION_ARGS} --execute "CREATE DATABASE ${DB};"
mysql ${MYSQL_CONNECTION_ARGS} --execute "CREATE USER '${MYSQL_APP_USER}'@'${MYSQL_HOST}' IDENTIFIED BY '${MYSQL_APP_PASSWORD}';"
mysql ${MYSQL_CONNECTION_ARGS} --execute "GRANT ALL on ${DB}.* to '${MYSQL_APP_USER}'@'%' IDENTIFIED BY '${MYSQL_APP_PASSWORD}';"
mysql ${MYSQL_CONNECTION_ARGS} --execute "FLUSH PRIVILEGES;"

