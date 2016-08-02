#!/bin/sh
####################################
#
# Install PHP dependencies via Composer
#
####################################

# copy requires sources into compiled dir ready for packaging
test -e compiled || mkdir compiled

# full sync also deleting files that don't exist on source, excluding resources
rsync -av --delete --exclude='/compiled' --exclude='/*.tar.gz' --exclude='*.git*' -O -p --no-g . compiled/
