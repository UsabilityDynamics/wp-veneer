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
	composer install --prefer-dist --no-dev --no-interaction

# Build for repository commit
release:
	echo Pushing $(NAME).
	rm -rf composer.lock
	composer update --prefer-dist --no-dev --no-interaction
	git add . --all
	git commit -m '[ci skip]'
	git push
