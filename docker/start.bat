@echo off
REM RestoPOS Docker Start Script for Windows

echo Starting RestoPOS Development Environment...

REM Check if .env exists
if not exist .env (
    copy .env.example .env
    echo Created .env file from .env.example
)

REM Start main services
docker-compose up -d

echo.
echo ==================================
echo RestoPOS is starting...
echo ==================================
echo.
echo Services:
echo   - App:         http://localhost:8080
echo   - phpMyAdmin:  docker-compose --profile dev up -d
echo   - Mailpit:     docker-compose --profile dev up -d
echo   - Frontend:    docker-compose --profile frontend up -d
echo   - WebSocket:   docker-compose --profile websocket up -d
echo   - Queue:       docker-compose --profile worker up -d
echo.
echo Commands:
echo   - docker-compose exec app php artisan [command]
echo   - docker-compose exec app composer [command]
echo   - docker-compose logs -f app
echo.
