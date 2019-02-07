#!/bin/bash

cd /home/web/een/drupal
../bin/drush mi --group=een_events 2>&1 > /dev/null
../bin/drush migrate-reset-status merlin_opportunities_xml_2017_1_a
../bin/drush mi --group=een_opportunities --update 2>&1 > /dev/null