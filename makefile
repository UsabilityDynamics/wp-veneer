## Build Plugin
##
##

NAME = wp-veneer

# Default Install Action
default:
	make install

# Build for Distribution
build:
	echo Building $(NAME).
	npm install --production
	composer install --prefer-dist --no-dev --no-interaction
	grunt build

# Build for Distribution
install:
	@echo Installing $(NAME).
	rm -rf composer.lock
	rm -rf vendor
	composer update --prefer-dist --no-dev --no-interaction

# Build for repository commit
release:
	make install
	git rm --cached vendor/libraries/usabilitydynamics/lib-api
	git rm --cached vendor/libraries/usabilitydynamics/lib-ui
	git rm --cached vendor/libraries/usabilitydynamics/lib-utility
	git rm --cached vendor/libraries/usabilitydynamics/lib-settings
	git add . --all
	git commit -m '[ci skip]'
	git push
