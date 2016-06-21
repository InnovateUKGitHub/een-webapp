################################################################################
################################################################################
#                                                                              #
#                                  Variables                                   #
#                                                                              #
################################################################################

.DEFAULT: build

UUID ?= "f4543550-d17a-4ac0-9798-efe6aff4aeef"
DRUSH ?= ../bin/drush

################################################################################
#                                                                              #
#                               Public Commands                                #
#                                                                              #
################################################################################

build: install

install:
	@echo "Installing site..."
	@make -s reset-db
	@make -s install-dependencies
	@make -s install-site
	@make -s default-admin
	@make -s clear-cache
	@make -s import-config

install-site:
	@./script/install-site.sh

install-dependencies:
	@echo "Installing dependencies..."
	@sh -c "cd drupal && composer install --optimize-autoloader"

clear-cache:
	@echo "Clearing cache..."
	@sh -c "cd drupal && $(DRUSH) cr"

reset-db:
	@./script/reset-db.sh 2>&1 | grep -v "Warning: Using a password on the command line interface can be insecure."


default-admin:
	@echo "Changing default user..."
	@sh -c "cd drupal && $(DRUSH) upwd admin --password=password -y"

delete-shortcut:
	@echo "Deleting shortcut_set due to drupal bug..."
	@sh -c "cd drupal && $(DRUSH) ev '\\Drupal::entityManager()->getStorage(\"shortcut_set\")->load(\"default\")->delete();'"

import-config:
	@echo "Importing configuration..."
	@sh -c "cd drupal && $(DRUSH) cset system.site uuid $(UUID) -y"
	@sh -c "cd drupal && $(DRUSH) config-import deploy -y"
	@echo "Done."

export-config:
	@echo "Exporting configuration..."
	@sh -c "cd drupal && $(DRUSH) config-export deploy -y"
	@echo "Done."
