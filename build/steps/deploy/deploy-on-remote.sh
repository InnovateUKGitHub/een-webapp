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
DEPLOY_METHOD=$3
REBUILD_DATABASE=$4
REBUILD_DRUPAL_CONFIG=$5

echo "APPLICATION_ENV=$APPLICATION_ENV"
echo "PACKAGE=$PACKAGE"
echo "DEPLOY_METHOD=$DEPLOY_METHOD"
echo "REBUILD_DATABASE=$REBUILD_DATABASE"
echo "REBUILD_DRUPAL_CONFIG=$REBUILD_DRUPAL_CONFIG"

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

if [ "$DEPLOY_METHOD" = "tarball" ] ; then

    echo "Deploying by tarball: ~/packages/${PACKAGE}.tar.gz"

read -d '' SCRIPT << EOF
    cd ~/packages/;
    test -e ${htdocs} || sudo mkdir ${htdocs} --parents;
    sudo tar -zxf ${PACKAGE}.tar.gz -C ${htdocs};
    cd ${htdocs};
    export APPLICATION_ENV=${APPLICATION_ENV};
    export REBUILD_DATABASE=${REBUILD_DATABASE};
    export REBUILD_DRUPAL_CONFIG=${REBUILD_DRUPAL_CONFIG};

    sudo -E ./build/steps/deploy/deploy.sh ${REBUILD_DATABASE} ${REBUILD_DRUPAL_CONFIG};
EOF

else
    # trim off commit sha -072fcbc
    FOLDER=${PACKAGE:0:${#PACKAGE}-8}

    echo "Deploying by rsync: ~/packages/${FOLDER}"

read -d '' SCRIPT << EOF
    cd ~/packages/${FOLDER};

    test -e ${htdocs} || sudo mkdir ${htdocs} --parents;

    # full sync also deleting files that don't exist on source, excluding resources
    sudo rsync -av --delete --exclude='/logs/*' --exclude='/cache/*' --exclude='/db/update' -O -p --no-g . ${htdocs}/

    export APPLICATION_ENV=${APPLICATION_ENV};
    export REBUILD_DATABASE=${REBUILD_DATABASE};
    export REBUILD_DRUPAL_CONFIG=${REBUILD_DRUPAL_CONFIG};

    # execute remaining deploy steps
    sudo -E ./build/steps/deploy/deploy.sh ${REBUILD_DATABASE} ${REBUILD_DRUPAL_CONFIG};
EOF

fi

echo "Executing deploy script on hosts: $HOSTS"

#AWS_EC2_NODE_NO=0

for HOSTNAME in ${HOSTS} ; do
    echo "Executing deploy script on ${USER}@${HOSTNAME}"
    ssh -o 'StrictHostKeyChecking no' -l ${USER} ${HOSTNAME} "${SCRIPT}"
    #ssh -o 'StrictHostKeyChecking no' -l ${USER} ${HOSTNAME} "AWS_EC2_NODE_NO=${AWS_EC2_NODE_NO}; ${SCRIPT}"
    #AWS_EC2_NODE_NO=1
done