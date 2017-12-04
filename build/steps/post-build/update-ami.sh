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

if [ "$APPLICATION_ENV" = "" ] || [ "$aws_cli_profile" = "" ] || [ "$aws_cf_stack" = "" ]; then
  echo "Required vars not set APPLICATION_ENV:$APPLICATION_ENV aws_cli_profile:$aws_cli_profile aws_cf_stack:$aws_cf_stack" && exit 1;
fi

# set UniqueId for imageId
if [ "$BUILD_NUMBER" = "" ]; then
    uniqueId=`date +%Y-%m-%d_%H-%M-%S`
else
    #sanitizedGitBranch=${GIT_BRANCH//[^a-zA-Z0-9-]/}
    uniqueId=$BUILD_NUMBER-$GIT_BRANCH-$GIT_COMMIT
fi

runningInstanceId=`aws ec2 describe-instances --profile $aws_cli_profile --filter "Name=tag:aws:cloudformation:stack-name,Values=$aws_cf_stack*" "Name=instance-state-name,Values=running" | jq ".Reservations[0]" | jq '.Instances[].InstanceId' -r`
imageName="ubuntu-16.04-aerian-$APPLICATION_ENV-$runningInstanceId-$uniqueId"
imageDescription="'100 GB, Apache, PHP, NPM'"

if [ "$runningInstanceId" = "" ] || [ "$imageName" = "" ] || [ "$imageDescription" = "" ]; then
  echo "Required vars not set runningInstanceId:$runningInstanceId imageName:$imageName imageDescription:$imageDescription" && exit 1;
fi

echo "Creating a new AMI snapshot for EC2 InstanceId: $runningInstanceId"
createImageResponse=`aws ec2 create-image --profile $aws_cli_profile --instance-id $runningInstanceId --name $imageName --description 'Created automatically post build' --no-reboot`
newAmiId=`echo "$createImageResponse" | jq .ImageId -r`

month=`date +%Y-%m`
lastMonth=`date --date='-1 month' +'%Y-%m'`

echo "New AMI snapshot: $newAmiId"
aws ec2 create-tags --profile $aws_cli_profile --resources $newAmiId --tags Key=Stack,Value=$aws_cf_stack Key=Month,Value=$month Key=Source,Value="Created automatically post build" Key=appversion,Value=$appversion Key=appid,Value=$appid

echo "Removing old AMI images/snapshots (older than two months)"

# per stack
thisMonthsAmis=`aws ec2 describe-images --profile $aws_cli_profile --owner "self" --filter "Name=tag:Month,Values=$month" "Name=image-type,Values=machine" "Name=tag:Stack,Values=$aws_cf_stack" "Name=tag:appid,Values=$appid" | jq ".Images[].ImageId" -r`
lastMonthsAmis=`aws ec2 describe-images --profile $aws_cli_profile --owner "self" --filter "Name=tag:Month,Values=$lastMonth" "Name=image-type,Values=machine" "Name=tag:Stack,Values=$aws_cf_stack" "Name=tag:appid,Values=$appid" | jq ".Images[].ImageId" -r`
# all 
#thisMonthsAmis=`aws ec2 describe-images --profile $aws_cli_profile --owner "self" --filter "Name=tag:Month,Values=$month" "Name=image-type,Values=machine" "Name=tag:appid,Values=$appid" | jq ".Images[].ImageId" -r`
#lastMonthsAmis=`aws ec2 describe-images --profile $aws_cli_profile --owner "self" --filter "Name=tag:Month,Values=$lastMonth" "Name=image-type,Values=machine" "Name=tag:appid,Values=$appid" | jq ".Images[].ImageId" -r`

echo "AMI's from $month: $thisMonthsAmis"
echo "AMI's from $lastMonth: $lastMonthsAmis"
amisToKeep=`echo "$newAmiId\n$thisMonthsAmis\n$lastMonthsAmis"`
echo "AMI's to keep: $amisToKeep"

if [ "$thisMonthsAmis" = "" ] || [ "$lastMonthsAmis" = "" ] || [ "$newAmiId" = "" ] || [ "$amisToKeep" = "" ]; then
  echo "Not purging, AMI's to keep not set: newAmiId:$newAmiId lastMonthsAmis:$lastMonthsAmis thisMonthsAmis:$thisMonthsAmis amisToKeep:$amisToKeep" 
else
    # per stack
    allStackAmis=`aws ec2 describe-images --profile $aws_cli_profile --owner "self" --filter "Name=tag:Stack,Values=$aws_cf_stack" "Name=image-type,Values=machine" "Name=tag:Source,Values=Created automatically post build" "Name=tag:appid,Values=$appid" | jq ".Images[].ImageId" -r`
    # all
    #allStackAmis=`aws ec2 describe-images --profile $aws_cli_profile --owner "self" --filter "Name=image-type,Values=machine" "Name=tag:appid,Values=$appid" | jq ".Images[].ImageId" -r`

    allStackAmisCount=`echo "$allStackAmis" | wc -l`
    amiPurgeMin=40
    amiPurgeCount=0
    amiPurgeLimit=20

    if [ "$allStackAmisCount" -ge "$amiPurgeMin" ]; then
        echo "Found $allStackAmisCount AMIs, continuing purge..."
        for amiToDelete in $allStackAmis; do
            keepAmi="false"
            for amiToKeep in $amisToKeep; do     
                if [ "$amiToDelete" = "$amiToKeep" ]; then
                    keepAmi="true"
                    break            
                fi
            done
            if [ "$keepAmi" = "false" ]; then
                echo "Deregistering AMI image: '$amiToDelete'"
                aws ec2 deregister-image --profile $aws_cli_profile --image-id $amiToDelete
                snapshotId=`aws ec2 describe-snapshots --profile $aws_cli_profile --filter "Name=description,Values=*$amiToDelete*" | jq ".Snapshots[].SnapshotId" -r`
                echo "Deleting snapshot: '$snapshotId'"
                aws ec2 delete-snapshot --profile $aws_cli_profile --snapshot-id $snapshotId

                # increment counter and check if limit has been reached
                amiPurgeCount=$((amiPurgeCount+1))
                if [ "$amiPurgeCount" -ge "$amiPurgeLimit" ]; then
                    echo "Reached AMI delete limit of $amiPurgeLimit, finishing purge..."
                    break
                fi
            else  
                echo "Keeping AMI image: '$amiToDelete'"     
            fi      
        done
    else
        echo "Found $allStackAmisCount AMIs, not purging"
    fi
fi

stackInfo=`aws cloudformation describe-stacks --profile $aws_cli_profile --stack-name $aws_cf_stack`
currentParameters=`echo "$stackInfo" | jq '.Stacks[].Parameters'`
currentAmiId=`echo "$stackInfo" | jq '.Stacks[].Parameters[] | select(.ParameterKey == "PresentationEC2Ami") | .ParameterValue' -r`

echo "currentParameters: $currentParameters"
echo "currentAmiId: $currentAmiId"
echo "newAmiId: $newAmiId"

newParameters=`echo "$currentParameters" | sed "s/$currentAmiId/$newAmiId/g"`

if [ "$currentAmiId" = "" ] || [ "$newAmiId" = "" ] || [ "$newParameters" = "" ]; then
  echo "Required vars not set currentAmiId:$currentAmiId newAmiId:$newAmiId newParameters:$newParameters" && exit 1;
fi

echo "New Parameters: $newParameters"
echo "Updating stack: $aws_cf_stack"
aws cloudformation update-stack --profile $aws_cli_profile --stack-name $aws_cf_stack --capabilities CAPABILITY_IAM --use-previous-template --parameters "$newParameters"
