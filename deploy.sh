#!/bin/sh

clear
echo "Down Service...."
docker-compose down --remove-orphans
docker system prune -a -f
echo ""

echo "Build and Up Service...."
docker-compose build --no-cache --pull
docker-compose up -d --remove-orphans
SUCCESS_UP_SERVICE=$?
if [[ ${SUCCESS_UP_SERVICE} -eq 0 ]]; then
    echo "Clear Redundant"   
    docker system prune -f
fi