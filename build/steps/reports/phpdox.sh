#!/bin/bash
####################################
#
# PHPDox
# 
####################################

if [ "$phpdox" = "true" ]; then
  if [ ! -d $workspace/docs ]; then
    mkdir $workspace/docs
  fi
  if [ ! -d $workspace/docs/phpdox/build ]; then
    mkdir -p $workspace/docs/phpdox/build
  fi
  # generate phploc files    
  $workspace/bin/phploc application library/Aerian --log-xml $workspace/docs/phpdox/build/phploc.xml
  # generate phpdox files        
  $workspace/bin/phpdox -f $workspace/docs/phpdox/phpdox.xml   
fi