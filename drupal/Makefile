################################################################################
################################################################################
#                                                                              #
#                                  Variables                                   #
#                                                                              #
################################################################################

.DEFAULT: build

# UUID Use by drupal to differentiate site
UUID ?= "f4543550-d17a-4ac0-9798-efe6aff4aeef"

# Location of the drush executable (drupal manager)
DRUSH ?= ../bin/drush

################################################################################
#                                                                              #
#                               Public Commands                                #
#                                                                              #
################################################################################

build: install

install:
	@echo "Installing drupal..."
	@make -s reset-db
	@make -s install-dependencies
	@make -s install-site
	@make -s default-admin
	@make -s clear-cache
	@make -s delete-shortcut
	@make -s import-config
	@make -s import-sql
	@make -s install-module
	@echo "Installation completed"
	@echo "Go to http://enn/ to connect"
	@echo "Default administrator:"
	@echo "login: admin"
	@echo "password: password"

install-site:
	@../script/install-site.sh

install-module:
	$(DRUSH) en opportunity_search -y

install-dependencies:
	@echo "Installing drupal dependencies..."
	@sh -c "composer install --optimize-autoloader"

clear-cache:
	@echo "Clearing drupal cache..."
	@sh -c "$(DRUSH) cr"
	@sh -c "$(DRUSH) cc css-js"

reset-db:
	@../script/reset-db.sh 2>&1 | grep -v "Warning: Using a password on the command line interface can be insecure."

default-admin:
	@echo "Changing default user admin password..."
	@sh -c "$(DRUSH) upwd admin --password=password -y"

delete-shortcut:
	@echo "Deleting shortcut_set due to drupal bug..."
	@sh -c "$(DRUSH) ev '\\Drupal::entityManager()->getStorage(\"shortcut_set\")->load(\"default\")->delete();'"

import-config:
	@echo "Importing configuration..."
	@sh -c "$(DRUSH) cset system.site uuid $(UUID) -y"
	@sh -c "$(DRUSH) config-import deploy -y"

export-config:
	@echo "Exporting configuration..."
	@sh -c "$(DRUSH) config-export deploy -y"

export-sql:
	@echo "Do a script to export the wanted tables in a new file"

import-sql:
	@echo "Importing database..."
	@../script/import-sql.sh

test:
	@echo "Running drupal unit test..."

################################################################################
#                                                                              #
#                               Shortcuts                                      #
#                                                                              #
################################################################################

cc: clear-cache
