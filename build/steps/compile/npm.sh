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
else
    echo "package.json has not changed, not running npm"
fi
