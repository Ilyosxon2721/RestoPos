#!/bin/bash

# RestoPOS Docker Start Script

echo "Starting RestoPOS Development Environment..."

# Check if .env exists
if [ ! -f .env ]; then
    cp .env.example .env
    echo "Created .env file from .env.example"
fi

# Start main services
docker-compose up -d

echo ""
echo "=================================="
echo "RestoPOS is starting..."
echo "=================================="
echo ""
echo "Services:"
echo "  - App:         http://localhost:8080"
echo "  - phpMyAdmin:  docker-compose --profile dev up -d"
echo "  - Mailpit:     docker-compose --profile dev up -d"
echo "  - Frontend:    docker-compose --profile frontend up -d"
echo "  - WebSocket:   docker-compose --profile websocket up -d"
echo "  - Queue:       docker-compose --profile worker up -d"
echo ""
echo "Commands:"
echo "  - docker-compose exec app php artisan <command>"
echo "  - docker-compose exec app composer <command>"
echo "  - docker-compose logs -f app"
echo ""
