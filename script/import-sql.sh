#!/bin/bash -e

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
source "${SCRIPT_DIR}/mysql.environment"

MYSQL_CONNECTION_ARGS="--port=${MYSQL_PORT} --host=${MYSQL_HOST} --user=${MYSQL_ADMIN_USER} --password=${MYSQL_ADMIN_PASSWORD}"

MYSQL_FOLDER="${SCRIPT_DIR}/../db/update/*"

for FILE in ${MYSQL_FOLDER}
do
    mysql ${MYSQL_CONNECTION_ARGS} < ${FILE}
done
