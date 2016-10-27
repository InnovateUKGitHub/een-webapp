################################################################################
################################################################################
#                                                                              #
#                                  Variables                                   #
#                                                                              #
################################################################################

.DEFAULT: build

# Location of the drush executable (drupal manager)
CURRENT_DIR := $(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
DRUSH := $(CURRENT_DIR)/bin/drush

################################################################################
#                                                                              #
#                               Public Commands                                #
#                                                                              #
################################################################################

build: install

install:
	@sh -c "echo ';zend_extension=xdebug.so' | sudo tee /etc/php/5.6/cli/conf.d/20-xdebug.ini"
	@sh -c "./build/1-compile.sh"
	@sh -c "sudo APPLICATION_ENV=development_vagrant ./build/2-deploy.sh"
	@sh -c "sudo chown -R vagrant:vagrant /home/vagrant/.drush"
	@sh -c "echo 'zend_extension=xdebug.so' | sudo tee /etc/php/5.6/cli/conf.d/20-xdebug.ini"
	@sh -c "./build/3-test.sh"
	@sh -c "./build/6-copy-file.sh"

gulp:
	@echo "Updating css & js"
	@sh -c "./build/steps/compile/gulp.sh"

test:
	@sh -c "./build/3-test.sh"

################################################################################
#                                                                              #
#                               Developer                                      #
#                                                                              #
################################################################################

npm:
	@sh -c "npm install"

clear-cache:
	@echo "Clearing cache..."
	@sh -c "cd drupal && $(DRUSH) cr"

install-module:
	@sh -c "cd drupal && $(DRUSH) pm-enable service_connection een_common opportunities events -y"

delete-module:
	@sh -c "cd drupal && $(DRUSH) pm-uninstall een_common opportunities events service_connection -y"

install-dependencies:
	@echo "Installing dependencies..."
	@sh -c "cd drupal && composer install --optimize-autoloader"

default-admin:
	@echo "Changing default user admin password..."
	@sh -c "cd drupal && $(DRUSH) upwd admin --password=password -y"

delete-shortcut:
	@echo "Deleting shortcut_set due to drupal bug..."
	@sh -c "cd drupal && $(DRUSH) ev '\\Drupal::entityManager()->getStorage(\"shortcut_set\")->load(\"default\")->delete();'"

import-config:
	@echo "Importing configuration..."
	@sh -c "cd drupal && $(DRUSH) cset system.site uuid $(UUID) -y"
	@sh -c "cd drupal && $(DRUSH) config-import deploy -y"

export-config:
	@echo "Exporting configuration..."
	@sh -c "cd drupal && $(DRUSH) config-export deploy -y"

export-sql:
	@echo "Exporting database..."
	@sh -c "cd drupal && $(DRUSH) sql-dump > ../db/init/initial-database.sql"

update-entity:
	@echo "Updating database..."
	@sh -c "cd drupal && $(DRUSH) entity-updates -y"

################################################################################
#                                                                              #
#                               Shortcuts                                      #
#                                                                              #
################################################################################

cc: clear-cache
