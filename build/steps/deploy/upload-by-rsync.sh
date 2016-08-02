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

. build/steps/deploy/load-env-vars.sh

if [ "$aws_cf_stack" = "" ] ; then
    # integration_v2 needs to use it's internal IP
    if [ "$ip" = "212.159.160.166" ] ; then 
        ip=10.0.1.33
    fi
    HOSTS="$ip"
else
   HOSTS=`build/steps/deploy/lookup-ec2-ips.sh $aws_cli_profile $aws_cf_stack`
fi

USER="jenkins"

# trim off commit sha -072fcbc
FOLDER=${PACKAGE:0:${#PACKAGE}-8}
echo "Rsyncing ${PACKAGE} to hosts: $HOSTS"
cd compiled/

for HOSTNAME in ${HOSTS} ; do
    echo "Rsyncing code to ${USER}@${HOSTNAME}:~/packages/${FOLDER}/"
    ssh -o 'StrictHostKeyChecking no' -l ${USER} ${HOSTNAME} "mkdir -p ~/packages/${FOLDER}"
    rsync -av --delete -O -p --no-g . ${USER}@${HOSTNAME}:~/packages/${FOLDER}
done