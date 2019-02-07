#!/bin/bash

cd /home/web/een/drupal
rm -r vendor
rm .composer.lock
./composer.phar update --with-dependencies
