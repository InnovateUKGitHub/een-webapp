#!/bin/bash
####################################
#
# Tests to be executed after package installation on the target server
# 
####################################

# causes all variables defined from now on to be exported
set -a

workspace=`pwd`

# load in remote environment vars if not set
if [ "$APPLICATION_ENV" = "" ]; then
  . /etc/environment
fi

# confirm APPLICATION_ENV is set
if [ "$APPLICATION_ENV" = "" ]; then
  echo "APPLICATION_ENV not set" 
  exit
fi

# argument passed into script via cli
#htdocs=$(dirname $(readlink -f "$0"))

# template variable injected by deb package post deploy hook
#htdocs=<%= htdocs %>

basePropsPath=$workspace/build/properties/base.properties
envPropsPath=$workspace/build/properties/$APPLICATION_ENV.properties

test -e $basePropsPath || echo "Base props not found: " $basePropsPath exit
test -e $envPropsPath || echo "Env props not found: " $envPropsPath exit

# load in environment properties
. $basePropsPath
. $envPropsPath

if [ "$APPLICATION_ENV" = "" ] || [ "$htdocs" = "" ] || [ "$appid" = "" ]; then
  echo "Required vars not set APPLICATION_ENV:$APPLICATION_ENV htdocs:$htdocs appid:$appid" && exit
fi

$workspace/build/steps/reports/phpdox.sh || { echo "Executing $workspace/build/steps/reports/phpdox.sh failed" ; exit 1; }