#!/bin/bash -e

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
source "${SCRIPT_DIR}/mysql.environment"

# Inelegant nested if/else blocks due to my bash ignorance (n.d. Marco)
if [[ -z "$MYSQL_ADMIN_PASSWORD"  ]]
then
    echo "Empty mysql password provided: skipping password parameter when connecting!"
    MYSQL_PASSWORD_ARG=""
else
    if [[ ! `mysql --port=${MYSQL_PORT} --host=${MYSQL_HOST} --user=${MYSQL_ADMIN_USER} --password=${MYSQL_ADMIN_PASSWORD} --execute "SELECT 1;"` ]]
    then
        echo "Provided mysql password is incorrect, trying without password!"
        MYSQL_PASSWORD_ARG=""
    else
        MYSQL_PASSWORD_ARG="--password=${MYSQL_ADMIN_PASSWORD}"
    fi
fi


MYSQL_CONNECTION_ARGS="--port=${MYSQL_PORT} --host=${MYSQL_HOST} --user=${MYSQL_ADMIN_USER} ${MYSQL_PASSWORD_ARG}"

echo "Deleting old database config..."
mysql ${MYSQL_CONNECTION_ARGS} --execute "DROP DATABASE IF EXISTS ${DB};"
mysql ${MYSQL_CONNECTION_ARGS} --execute "GRANT USAGE ON *.* TO '${MYSQL_APP_USER}'@'${MYSQL_HOST}';"
mysql ${MYSQL_CONNECTION_ARGS} --execute "DROP USER '${MYSQL_APP_USER}'@'${MYSQL_HOST}';"

echo "Creating mysql users and access..."
mysql ${MYSQL_CONNECTION_ARGS} --execute "CREATE DATABASE ${DB};"
mysql ${MYSQL_CONNECTION_ARGS} --execute "CREATE USER '${MYSQL_APP_USER}'@'${MYSQL_HOST}' IDENTIFIED BY '${MYSQL_APP_PASSWORD}';"
mysql ${MYSQL_CONNECTION_ARGS} --execute "GRANT ALL on ${DB}.* to '${MYSQL_APP_USER}'@'%' IDENTIFIED BY '${MYSQL_APP_PASSWORD}';"
mysql ${MYSQL_CONNECTION_ARGS} --execute "FLUSH PRIVILEGES;"

