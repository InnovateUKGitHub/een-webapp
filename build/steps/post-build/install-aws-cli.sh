#!/bin/sh
####################################
#
# Create a new AMI post build and update the existing CloudFormation template.
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

if [ "$APPLICATION_ENV" = "" ] || [ "$htdocs" = "" ] || [ "$appid" = "" ] || [ "$aws_access_key_id" = "" ] || [ "$aws_secret_access_key" = "" ]; then
  echo "Required vars not set APPLICATION_ENV:$APPLICATION_ENV htdocs:$htdocs appid:$appid aws_access_key_id:$aws_access_key_id aws_secret_access_key:$aws_secret_access_key" && exit 1;
fi

# TODO move into chef?

configDest=~/.aws/config

if [ ! -f "$configDest" ]; then

    echo "Installing AWS CLI"

    curl -O https://bootstrap.pypa.io/get-pip.py
    python get-pip.py
    pip install awscli

    apt-get install jq

    echo "Creating AWS CLI profile"

    cd $htdocs/build/templates/aws
    mkdir ~/.aws

    cp cli-config $configDest

    sed -i "s@%aws_cli_profile%@$aws_cli_profile@" $configDest
    sed -i "s@%aws_access_key_id%@$aws_access_key_id@" $configDest
    sed -i "s@%aws_secret_access_key%@$aws_secret_access_key@" $configDest
fi