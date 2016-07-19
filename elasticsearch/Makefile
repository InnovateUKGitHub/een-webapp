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
	@echo "Installing elastic-search..."
	@make -s install-dependencies
	@make -s clear-cache

install-dependencies:
	@echo "Installing elasticsearch dependencies..."
	@sh -c "composer install --optimize-autoloader"

clear-cache:
	@echo "Clearing cache..."
	@sh -c "rm -rf data/cache/module*"

test:
	@echo "Running elasticsearch unit test..."
	@sh -c "vendor/bin/phpunit"

test-coverage:
	@echo "Running elasticsearch unit test with coverage..."
	@sh -c "vendor/bin/phpunit  --coverage-html "`pwd`/html-coverage""

generate:
	@sh -c "php public/index.php generate"

delete:
	@sh -c "php public/index.php delete"

################################################################################
#                                                                              #
#                               Shortcuts                                      #
#                                                                              #
################################################################################

cc: clear-cache
