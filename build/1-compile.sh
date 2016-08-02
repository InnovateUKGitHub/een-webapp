#!/bin/sh
####################################
#
# Commands to be executed during the Jenkins CI build to prepare sources for packaging.
#
# ./compile
#
# This may include:
# * Minification of JS (Gulp)
# * Compression of CSS (Gulp)
# * Installation of dependencies (Gulp)
#
####################################

# causes all variables defined from now on to be exported
set -a

# exit on error
set -e

workspace=`pwd`

# build argument to force the compile scripts regardless of json changes
forceCompile=$1

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

# force compile on first build
test -e $htdocs || forceCompile=true

$workspace/build/steps/compile/composer.sh || { echo "Executing $workspace/build/steps/compile/composer.sh failed" ; exit 1; }
$workspace/build/steps/compile/npm.sh || { echo "Executing $workspace/build/steps/compile/npm.sh failed" ; exit 1; }
$workspace/build/steps/compile/gulp.sh || { echo "Executing $workspace/build/steps/compile/grunt.sh failed" ; exit 1; }
