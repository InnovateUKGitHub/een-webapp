################################################################################
################################################################################
#                                                                              #
#                                  Variables                                   #
#                                                                              #
################################################################################

.DEFAULT: build

# Location of the drush executable (drupal manager)
DRUSH ?= ../bin/drush

################################################################################
#                                                                              #
#                               Public Commands                                #
#                                                                              #
################################################################################

build: install

install:
	@sh -c "./build/1-compile.sh"
	@sh -c "./build/2-deploy.sh"
	@sh -c "./build/3-test.sh"

sass:
	@echo "Updating css"
	@sh -c "./build/steps/compile/gulp.sh"
	@sh -c "cd drupal && make -s clear-cache"

test:
	@sh -c "./build/3-test.sh"

################################################################################
#                                                                              #
#                               Developer                                      #
#                                                                              #
################################################################################

install-module:
	@sh -c "cd drupal && $(DRUSH) en opportunities elastic_search -y"

delete-module:
	@sh -c "cd drupal && $(DRUSH) pm-uninstall opportunities elastic_search -y"

install-dependencies:
	@echo "Installing drupal dependencies..."
	@sh -c "cd drupal && composer install --optimize-autoloader"

clear-cache:
	@echo "Clearing drupal cache..."
	@sh -c "cd drupal && $(DRUSH) cr"

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
	@sh -c "cd drupal && $(DRUSH) entity-updates"

################################################################################
#                                                                              #
#                               Shortcuts                                      #
#                                                                              #
################################################################################

cc: clear-cache
