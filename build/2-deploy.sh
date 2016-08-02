#!/bin/bash
####################################
#
# Commands to be executed after package installation on the target server
# 
# ./deploy integration
# 
# This may include:
# * Execution of database deltas
# * Creation of logs
# * Permissions on folders (resources/logs)
# * Creation and activation of VirtualHost
# * Extraction of base resources
#
####################################

# causes all variables defined from now on to be exported
set -a

# exit on error
set -e

workspace=`pwd`

# load in remote environment vars if not set
if [ "$APPLICATION_ENV" = "" ]; then
  . /etc/environment
fi

# confirm APPLICATION_ENV is set
if [ "$APPLICATION_ENV" = "" ]; then
  echo "APPLICATION_ENV not set" 
  exit 1;
fi

# argument passed into script via cli
#htdocs=$(dirname $(readlink -f "$0"))

# template variable injected by deb package post deploy hook
#htdocs=<%= htdocs %>

basePropsPath=$workspace/build/properties/base.properties
envPropsPath=$workspace/build/properties/$APPLICATION_ENV.properties

test -e $basePropsPath || (echo "Base props not found: " $basePropsPath && exit 1;)
test -e $envPropsPath || (echo "Env props not found: " $envPropsPath && exit 1;)

# load in environment properties
. $basePropsPath
. $envPropsPath

if [ "$APPLICATION_ENV" = "" ] || [ "$htdocs" = "" ] || [ "$appid" = "" ]; then
  echo "Required vars not set APPLICATION_ENV:$APPLICATION_ENV htdocs:$htdocs appid:$appid" && exit 1;
fi

$workspace/build/steps/deploy/drupal.sh
$workspace/build/steps/deploy/folders.sh

# only run these steps on a single node (0)
if [ "$AWS_EC2_NODE_NO" = "" ] || [ "$AWS_EC2_NODE_NO" = "0" ] ; then
  echo "AWS vars AWS_EC2_NODE_NO:$AWS_EC2_NODE_NO AWS_EC2_NODE_NO:$AWS_EC2_NODE_NO"
  $workspace/build/steps/deploy/mysql.sh
  $workspace/build/steps/deploy/s3-maintenance.sh
fi

$workspace/build/steps/deploy/apache.sh
