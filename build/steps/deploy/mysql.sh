#!/bin/bash
####################################
#
# Create base database if required and run any database deltas
#
####################################

cd $htdocs

sqldir=$htdocs/db/delta

# run database deltas?
if [ -d $sqldir ]; then

    #TODO Update db with new value
    echo "Updating Database"

fi