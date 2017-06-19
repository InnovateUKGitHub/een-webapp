#!/bin/bash
####################################
#
# Load in environment vars for the APPLICATION_ENV specified
#
####################################

# causes all variables defined from now on to be exported
set -a

APPLICATION_ENV=$1
PACKAGE=$2

. build/steps/utilities/load-env-vars.sh

if [ "$aws_cf_stack" = "" ] ; then
    # integration_v2 needs to use it's internal IP
    if [ "$ip" = "212.159.160.166" ] ; then 
        ip=10.0.1.33
    fi
    HOSTS="$ip"
else
   HOSTS=`build/steps/utilities/lookup-ec2-ips.sh $aws_cli_profile $aws_cf_stack`
fi

USER=$sshuser

echo "Uploading ${PACKAGE} to hosts: $HOSTS"

for HOSTNAME in ${HOSTS} ; do
    echo "Uploading ${PACKAGE}.tar.gz to ${USER}@${HOSTNAME}:~/packages/"
    scp -o 'StrictHostKeyChecking no' "${PACKAGE}.tar.gz" ${USER}@${HOSTNAME}:~/packages/
done