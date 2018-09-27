FROM php:7.1

MAINTAINER Vitaliy Zhuk <v.zhuk@fivelab.org>

RUN \
	apt-get update && \
	apt-get install -y --no-install-recommends \
		zip unzip \
		git

# Install composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
