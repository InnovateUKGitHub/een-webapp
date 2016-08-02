#!/bin/bash
####################################
#
# Create base database if required and run any database deltas
#
####################################

if [ "$aws_cli_profile" = "" ] || [ "$aws_maintenance_s3_bucket" = "" ] || [ "$htdocs" = "" ]; then  
  echo "Required vars to deploy maintenance site not set: htdocs:$htdocs aws_cli_profile:$aws_cli_profile aws_maintenance_s3_bucket:$aws_maintenance_s3_bucket" && exit 0;
else
  aws s3 cp $htdocs/httpdocs/maintenance/ s3://$aws_maintenance_s3_bucket/ --acl public-read --recursive --profile $aws_cli_profile
fi