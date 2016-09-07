#!/bin/sh
####################################
#
# Install required npm modules
#
####################################

# exit on error
set -e

# force compile on first build
test -e compiled/package.json || forceCompile=true

npmChanges=`diff package.json compiled/package.json || true`

if [ ! -z "$npmChanges" ] || [ ! -z "$forceCompile" ];then
    echo "package.json has changed:"
    npm install --verbose

    echo "copy gov images to theme folder"
    test -e drupal/themes/custom/een/images || mkdir -p drupal/themes/custom/een/css/images
    cp -r node_modules/govuk_frontend_toolkit/images/* drupal/themes/custom/een/css/images
    cp -r node_modules/govuk_template_mustache/assets/images/* drupal/themes/custom/een/css/images
    cp -r node_modules/govuk_template_mustache/assets/stylesheets/images/* drupal/themes/custom/een/css/images

    echo "copy flags images to theme folder"
    cp -r node_modules/flag-icon-css/flags drupal/themes/custom/een/

else
    echo "package.json has not changed, not running npm"
fi
