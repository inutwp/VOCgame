#!/bin/sh

clear
echo "Down Service...."
docker-compose down --remove-orphans
docker system prune -a -f
echo "Build and Up Service...."
docker-compose up -d --build --remove-orphans
SUCCESS_UP_SERVICE=$?
if [[ ${SUCCESS_UP_SERVICE} -eq 0 ]]; then
    echo "Clear Redundant"   
    docker system prune -f
fi