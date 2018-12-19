#!/bin/bash

#set -e # bail on error
set -a # auto-export variables

. .env

docker-compose up --build

echo "Done!"