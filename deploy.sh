#!/bin/bash

set -e

ME=`whoami`
PHP_VERSION=7.3
NOW=`date +%Y-%m-%d-%H-%M-%S`
export DOTENV_NAME="transcopy-dotenv-${NOW}"
export IMAGE_NAME="${DOCKER_ORG}/transcopy:${NOW}"
export APP_PORT=80
export PI_IP=192.168.1.86

docker buildx build --push --build-arg=PHP_VERSION=${PHP_VERSION} --target=prod --platform linux/amd64,linux/arm/v7 -t ${IMAGE_NAME} .

export DOCKER_HOST=ssh://${ME}@${PI_IP}

cat .env | docker secret create ${DOTENV_NAME} -
docker  stack deploy -c docker-stack.yml transcopy
