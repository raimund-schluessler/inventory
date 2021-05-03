FROM ubuntu:20.04
RUN	set -xe; \
	export DEBIAN_FRONTEND=noninteractive; \
	apt-get update -yqq; \
	apt-get install \
		git \
		sudo \
		php-xdebug \
		php7.3-sqlite3 \
		php7.3-curl \
		php7.3-xml \
		php7.3-mbstring \
		php7.3-json \
		php7.3-zip \
		php7.3-gd \
		php7.3-intl \
		php7.3-pgsql \
		postgresql-client \
		unzip \
		curl \
		wget \
		nodejs \
		npm \
		sed -yqq > /dev/null 2>&1; \
	npm install -g jshint > /dev/null 2>&1;

RUN	curl --location --output /usr/local/bin/phpunit \
	https://phar.phpunit.de/phpunit-7.phar; \
	chmod +x /usr/local/bin/phpunit;
