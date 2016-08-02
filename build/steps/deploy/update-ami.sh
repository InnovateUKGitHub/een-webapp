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

if [ "$APPLICATION_ENV" = "" ] || [ "$htdocs" = "" ] || [ "$appid" = "" ] || [ "$aws_cli_profile" = "" ] || [ "$aws_cf_stack" = "" ]; then
  echo "Required vars not set APPLICATION_ENV:$APPLICATION_ENV htdocs:$htdocs appid:$appid aws_cli_profile:$aws_cli_profile aws_cf_stack:$aws_cf_stack" && exit 1;
fi

# set UniqueId for imageId
if [ "$BUILD_NUMBER" = "" ]; then
    uniqueId=`date +%Y-%m-%d_%H-%M-%S`
else
    uniqueId=$BUILD_NUMBER
fi

runningInstanceId=`aws ec2 describe-instances --profile $aws_cli_profile --filter "Name=tag:aws:cloudformation:stack-name,Values=$aws_cf_stack" "Name=instance-state-name,Values=running" | jq ".Reservations[$AWS_EC2_NODE_NO]" | jq '.Instances[].InstanceId' -r`
imageName="ubuntu-14.04-aerian-$APPLICATION_ENV-$uniqueId"
imageDescription="100 GB, Apache, PHP, NPM, Compass, NewRelic, XDebug disabled, baseproject, BBC SSL Certificate, Encrypted RDS"

if [ "$runningInstanceId" = "" ] || [ "$imageName" = "" ] || [ "$imageDescription" = "" ]; then
  echo "Required vars not set runningInstanceId:$runningInstanceId imageName:$imageName imageDescription:$imageDescription" && exit 1;
fi

echo "Creating a new AMI snapshot for EC2 InstanceId: $runningInstanceId"
createImageResponse=`aws ec2 create-image --profile $aws_cli_profile --instance-id $runningInstanceId --name $imageName --description imageDescription --no-reboot`
newAmiId=`echo $createImageResponse | jq .ImageId -r`

stackInfo=`aws cloudformation describe-stacks --profile $aws_cli_profile --stack-name $aws_cf_stack`
currentParameters=`echo $stackInfo | jq '.Stacks[].Parameters'`
currentAmiId=`echo $stackInfo | jq '.Stacks[].Parameters[] | select(.ParameterKey == "EC2InstanceAmi") | .ParameterValue' -r`

echo "currentParameters: $currentParameters"
echo "currentAmiId: $currentAmiId"
echo "newAmiId: $newAmiId"

newParameters=`echo $currentParameters | sed "s/$currentAmiId/$newAmiId/g"` 

if [ "$currentAmiId" = "" ] || [ "$newAmiId" = "" ] || [ "$newParameters" = "" ]; then
  echo "Required vars not set currentAmiId:$currentAmiId newAmiId:$newAmiId newParameters:$newParameters" && exit 1;
fi

echo "New Parameters: $newParameters"
echo "Updating stack: $aws_cf_stack"
aws cloudformation update-stack --profile $aws_cli_profile --stack-name $aws_cf_stack --capabilities CAPABILITY_IAM --use-previous-template --parameters "$newParameters"