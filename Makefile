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
	@sh -c "cd drupal && make -s install"
	@make -s npm-install
	@make -s sass

install-dependencies:
	@sh -c "cd drupal && make -s install-dependencies"

clear-cache:
	@sh -c "cd drupal && make -s clear-cache"

npm-install:
	@echo "Installing npm modules"
	@sh -c "npm install"
sass:
	@echo "Updating css"
	@sh -c "grunt sass"
	@sh -c "cd drupal && make -s clear-cache"

test:
	@sh -c "cd drupal && make -s test"

################################################################################
#                                                                              #
#                               Shortcuts                                      #
#                                                                              #
################################################################################

cc: clear-cache
