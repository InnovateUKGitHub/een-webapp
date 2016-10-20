#!/bin/bash
####################################
#
# Commands to be executed only on a dev environment
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

mkdir -p $workspace/compiled/drupal
cp $workspace/drupal/composer.lock $workspace/compiled/drupal/composer.lock
cp $workspace/package.json $workspace/compiled/package.json

mkdir -p $workspace/compiled/drupal/themes/custom/een/scss
mkdir -p $workspace/compiled/drupal/themes/custom/een/js
cp -r $workspace/drupal/themes/custom/een/scss $workspace/compiled/drupal/themes/custom/een
cp -r $workspace/drupal/themes/custom/een/js $workspace/compiled/drupal/themes/custom/een
