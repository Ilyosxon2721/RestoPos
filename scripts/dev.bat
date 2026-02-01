@echo off
setlocal enabledelayedexpansion

:: RestoPOS Development Commands for Windows CMD
:: Usage: dev.bat <command>

if "%1"=="" goto :help
if "%1"=="help" goto :help
if "%1"=="install" goto :install
if "%1"=="setup" goto :setup
if "%1"=="dev" goto :dev
if "%1"=="serve" goto :serve
if "%1"=="vite" goto :vite
if "%1"=="horizon" goto :horizon
if "%1"=="queue" goto :queue
if "%1"=="tinker" goto :tinker
if "%1"=="up" goto :docker_up
if "%1"=="down" goto :docker_down
if "%1"=="restart" goto :docker_restart
if "%1"=="logs" goto :docker_logs
if "%1"=="shell" goto :docker_shell
if "%1"=="build" goto :docker_build
if "%1"=="migrate" goto :migrate
if "%1"=="migrate-fresh" goto :migrate_fresh
if "%1"=="rollback" goto :rollback
if "%1"=="seed" goto :seed
if "%1"=="test" goto :test
if "%1"=="test-parallel" goto :test_parallel
if "%1"=="test-coverage" goto :test_coverage
if "%1"=="lint" goto :lint
if "%1"=="fix" goto :fix
if "%1"=="stan" goto :stan
if "%1"=="ide" goto :ide
if "%1"=="cache-clear" goto :cache_clear
if "%1"=="cache" goto :cache
if "%1"=="build-prod" goto :build_prod
echo Unknown command: %1
goto :help

:help
echo.
echo  RestoPOS Development Commands
echo  =============================
echo.
echo  SETUP:
echo    install        - Full project installation
echo    setup          - Quick setup for developer
echo.
echo  DEVELOPMENT:
echo    dev            - Start dev servers (serve + vite)
echo    serve          - Start Laravel server only
echo    vite           - Start Vite only
echo    horizon        - Start Laravel Horizon
echo    queue          - Start queue worker
echo    tinker         - Open Laravel Tinker
echo.
echo  DOCKER:
echo    up             - Start Docker containers
echo    down           - Stop Docker containers
echo    restart        - Restart containers
echo    logs           - Show container logs
echo    shell          - Enter app container
echo    build          - Rebuild Docker images
echo.
echo  DATABASE:
echo    migrate        - Run migrations
echo    migrate-fresh  - Recreate DB with seeds
echo    rollback       - Rollback migrations
echo    seed           - Run seeders
echo.
echo  TESTING:
echo    test           - Run all tests
echo    test-parallel  - Run tests in parallel
echo    test-coverage  - Tests with coverage
echo.
echo  CODE QUALITY:
echo    lint           - Check code style
echo    fix            - Fix code style
echo    stan           - Static analysis
echo    ide            - Generate IDE helpers
echo.
echo  CACHE:
echo    cache-clear    - Clear all cache
echo    cache          - Cache for production
echo.
echo  PRODUCTION:
echo    build-prod     - Build for production
echo.
goto :eof

:install
echo Installing RestoPOS...
call composer install
call npm install
if not exist .env copy .env.example .env
call php artisan key:generate
call php artisan migrate --seed
call npm run build
echo Installation complete!
goto :eof

:setup
echo Setting up development environment...
call composer install
call npm install
if not exist .env copy .env.example .env
call php artisan key:generate
echo Setup complete! Run 'dev.bat dev' to start
goto :eof

:dev
echo Starting development servers...
start "Laravel" cmd /c "php artisan serve"
call npm run dev
goto :eof

:serve
php artisan serve
goto :eof

:vite
npm run dev
goto :eof

:horizon
php artisan horizon
goto :eof

:queue
php artisan queue:work --tries=3
goto :eof

:tinker
php artisan tinker
goto :eof

:docker_up
docker compose up -d
echo Containers started
echo App: http://localhost:8000
echo Mail: http://localhost:8025
goto :eof

:docker_down
docker compose down
goto :eof

:docker_restart
docker compose restart
goto :eof

:docker_logs
docker compose logs -f
goto :eof

:docker_shell
docker compose exec app sh
goto :eof

:docker_build
docker compose build --no-cache
goto :eof

:migrate
php artisan migrate
goto :eof

:migrate_fresh
php artisan migrate:fresh --seed
goto :eof

:rollback
php artisan migrate:rollback
goto :eof

:seed
php artisan db:seed
goto :eof

:test
php artisan test
goto :eof

:test_parallel
php artisan test --parallel
goto :eof

:test_coverage
php artisan test --coverage --coverage-html=coverage
goto :eof

:lint
call .\vendor\bin\pint --test
call .\vendor\bin\phpstan analyse
goto :eof

:fix
.\vendor\bin\pint
goto :eof

:stan
.\vendor\bin\phpstan analyse
goto :eof

:ide
php artisan ide-helper:generate
php artisan ide-helper:models -N
php artisan ide-helper:meta
goto :eof

:cache_clear
php artisan optimize:clear
goto :eof

:cache
php artisan optimize
goto :eof

:build_prod
call npm run build
call php artisan optimize
echo Production build complete!
goto :eof
