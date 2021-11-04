#!/bin/sh

clear
echo "Down Service...."
docker-compose down --remove-orphans &&
echo "Build and Up Service...."
docker-compose up -d --force-recreate --build --remove-orphans
