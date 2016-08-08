#!/bin/bash
####################################
#
# PHPUnit tests
# 
####################################

#rm -rf $workspace/test/PHPUnit/reports
cd drupal
./vendor/bin/phpunit -d zend.enable_gc=0 --coverage-html "`pwd`/html-coverage"
cd ..
