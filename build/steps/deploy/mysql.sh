#!/bin/bash
####################################
#
# Create base database if required and run any database deltas
#
####################################

cd $htdocs

sqldir=$htdocs/db/update

# run database deltas?
if [ -d $sqldir ]; then

    #Update db with new value
    mysql -h$dbhost -u$dbuser -p$dbpass -P$dbport < $htdocs/db/update/een_menu_link_content.sql
    mysql -h$dbhost -u$dbuser -p$dbpass -P$dbport < $htdocs/db/update/een_menu_link_content_data.sql
    mysql -h$dbhost -u$dbuser -p$dbpass -P$dbport < $htdocs/db/update/een_menu_tree.sql

fi