#!/bin/sh
####################################
#
# Execute gulp targets to prepare static assets
#
####################################

# exit on error
set -e

# force compile on first build
test -e compiled/drupal/themes/custom/een/scss || forceCompile=true

# check for changes stripping out 'Common subdirectories' string
csdTemplatesChanges=`diff --exclude="*.git*" -r drupal/themes/custom/een/scss compiled/drupal/themes/custom/een/scss | grep "Common subdirectories" -v || true`
jsTemplatesChanges=`diff --exclude="*.git*" -r drupal/themes/custom/een/js compiled/drupal/themes/custom/een/js | grep "Common subdirectories" -v || true`

# check if diff is empty (no changes)
if [ ! -z "$csdTemplatesChanges" ] || [ ! -z "$jsTemplatesChanges" ] || [ ! -z "$forceCompile" ];then
    echo "drupal/themes/custom/een has changed:"
    echo $csdTemplatesChanges
    echo $jsTemplatesChanges
    echo "running gulp"

    ./node_modules/gulp-cli/bin/gulp.js --verbose

else
    echo "drupal/themes/custom/een has not changed, not running gulp"
fi


