#!/bin/bash
####################################
#
# Commands to be executed after package installation on the target server
#
#REBUILD_DATABASE=$1
#REBUILD_DRUPAL_CONFIG=$2
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

$workspace/build/steps/deploy/deploy.sh $1 $2