################################################################################
################################################################################
#                                                                              #
#                                  Variables                                   #
#                                                                              #
################################################################################

.DEFAULT: build

################################################################################
#                                                                              #
#                               Public Commands                                #
#                                                                              #
################################################################################

build: install

install:
	@echo "Installing..."
	@make -s install-drupal
	@echo "\n\n\n"
	@make -s install-elasticsearch

install-drupal:
	@sh -c "cd drupal && make -s install"
	@make -s npm-install
	@make -s sass

install-elasticsearch:
	@sh -c "cd elasticsearch && make -s install"

install-dependencies:
	@sh -c "cd drupal && make -s install-dependencies"
	@sh -c "cd elasticsearch && make -s install-dependencies"

clear-cache:
	@echo "Clearing cache..."
	@sh -c "cd drupal && make -s clear-cache"
	@sh -c "cd elasticsearch && make -s  clear-cache"

npm-install:
	@echo "Installing npm modules"
	@sh -c "npm install"

sass:
	@echo "Updating css"
	@sh -c "grunt sass"
	@make -s clear-cache

test:
	@sh -c "cd drupal && make -s test"
	@sh -c "cd elasticsearch && make -s test"

################################################################################
#                                                                              #
#                               Shortcuts                                      #
#                                                                              #
################################################################################

cc: clear-cache
