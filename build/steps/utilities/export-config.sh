#!/bin/bash

#sudo -E ./build/7-export-config.sh

workspace=`pwd`

cd $workspace/drupal
$workspace/drupal/vendor/bin/drush config-export -y