#!/bin/bash
####################################
#
# Commands to be executed after package installation on the target server
# 
# ./deploy integration
# 
# This may include:
# * Execution of database deltas
# * Creation of logs
# * Permissions on folders (resources/logs)
# * Creation and activation of VirtualHost
# * Extraction of base resources
#
####################################


# exit on error
set -e


if [ "$elasticsearchhostname" = "" ]; then
	echo 'Elasticsearch hostname not set.. check settings'
else 
    echo "\$settings['create_elasticsearch_indices_from_drupal'] = TRUE;" >> $workspace/drupal/sites/default/settings.php
fi

