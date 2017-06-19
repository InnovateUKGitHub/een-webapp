#!/bin/sh
####################################
# 
# Lookup EC2 instance IPs using AWS CLI
#
# Usage: ./lookup-ec2-ips.sh makespace makespace-stage
#
# Arguments:
# * AWS_CF_STACK=makespace-stage
# * AWS_CLI_PROFILE=makespace
#
####################################

AWS_CLI_PROFILE=$1
AWS_CF_STACK=$2

if [ "$AWS_CF_STACK" = "" ] || [ "$AWS_CLI_PROFILE" = "" ]; then
  echo "Required vars not set AWS_CF_STACK:$AWS_CF_STACK AWS_CLI_PROFILE:$AWS_CLI_PROFILE" && exit 1;
fi

raw_output=`aws ec2 describe-instances --profile $AWS_CLI_PROFILE --filter "Name=tag:aws:cloudformation:stack-name,Values=$AWS_CF_STACK*" "Name=instance-state-name,Values=running"`

# extract IPs from reservations
instances=`echo "$raw_output" | jq ".Reservations[]" | jq '.Instances[]' -r`
ips=`echo "$instances" | jq '.PublicIpAddress' -r`

echo "$ips"