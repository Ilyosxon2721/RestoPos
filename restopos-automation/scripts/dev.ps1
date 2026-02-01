# RestoPOS Development Commands for Windows
# Использование: .\dev.ps1 <command>

param(
    [Parameter(Position=0)]
    [string]$Command = "help"
)

# Colors
function Write-Cyan($text) { Write-Host $text -ForegroundColor Cyan }
function Write-Green($text) { Write-Host $text -ForegroundColor Green }
function Write-Yellow($text) { Write-Host $text -ForegroundColor Yellow }

# Commands
function Show-Help {
    Write-Cyan "`n🚀 RestoPOS Development Commands`n"
    Write-Yellow "Установка:"
    Write-Host "  install        - Полная установка проекта"
    Write-Host "  setup          - Быстрая настройка для разработчика"
    Write-Yellow "`nРазработка:"
    Write-Host "  dev            - Запустить dev-серверы (serve + vite)"
    Write-Host "  serve          - Запустить только Laravel сервер"
    Write-Host "  vite           - Запустить только Vite"
    Write-Host "  horizon        - Запустить Laravel Horizon"
    Write-Host "  queue          - Запустить очереди"
    Write-Host "  tinker         - Открыть Laravel Tinker"
    Write-Yellow "`nDocker:"
    Write-Host "  up             - Запустить Docker контейнеры"
    Write-Host "  down           - Остановить Docker контейнеры"
    Write-Host "  restart        - Перезапустить контейнеры"
    Write-Host "  logs           - Показать логи контейнеров"
    Write-Host "  shell          - Войти в контейнер приложения"
    Write-Host "  build          - Пересобрать Docker образы"
    Write-Yellow "`nБаза данных:"
    Write-Host "  migrate        - Запустить миграции"
    Write-Host "  migrate-fresh  - Пересоздать БД с сидами"
    Write-Host "  rollback       - Откатить миграции"
    Write-Host "  seed           - Запустить сиды"
    Write-Yellow "`nТестирование:"
    Write-Host "  test           - Запустить все тесты"
    Write-Host "  test-parallel  - Тесты параллельно"
    Write-Host "  test-coverage  - Тесты с покрытием"
    Write-Yellow "`nКачество кода:"
    Write-Host "  lint           - Проверить код"
    Write-Host "  fix            - Исправить стиль кода"
    Write-Host "  stan           - Статический анализ"
    Write-Host "  ide            - Сгенерировать IDE хелперы"
    Write-Yellow "`nКэш:"
    Write-Host "  cache-clear    - Очистить весь кэш"
    Write-Host "  cache          - Закэшировать для production"
    Write-Yellow "`nProduction:"
    Write-Host "  build-prod     - Собрать для production"
    Write-Host ""
}

function Invoke-Install {
    Write-Cyan "🚀 Installing RestoPOS..."
    composer install
    npm install
    if (!(Test-Path .env)) { Copy-Item .env.example .env }
    php artisan key:generate
    php artisan migrate --seed
    npm run build
    Write-Green "✅ Installation complete!"
}

function Invoke-Setup {
    Write-Cyan "📦 Setting up development environment..."
    composer install
    npm install
    if (!(Test-Path .env)) { Copy-Item .env.example .env }
    php artisan key:generate
    Write-Green "✅ Setup complete! Run '.\dev.ps1 dev' to start"
}

function Invoke-Dev {
    Write-Cyan "🔥 Starting development servers..."
    Start-Process -NoNewWindow powershell -ArgumentList "-Command", "php artisan serve"
    npm run dev
}

function Invoke-Serve { php artisan serve }
function Invoke-Vite { npm run dev }
function Invoke-Horizon { php artisan horizon }
function Invoke-Queue { php artisan queue:work --tries=3 }
function Invoke-Tinker { php artisan tinker }

function Invoke-DockerUp {
    docker compose up -d
    Write-Green "✅ Containers started"
    Write-Host "App: http://localhost:8000"
    Write-Host "Mail: http://localhost:8025"
}

function Invoke-DockerDown { docker compose down }
function Invoke-DockerRestart { docker compose restart }
function Invoke-DockerLogs { docker compose logs -f }
function Invoke-DockerShell { docker compose exec app sh }
function Invoke-DockerBuild { docker compose build --no-cache }

function Invoke-Migrate { php artisan migrate }
function Invoke-MigrateFresh { php artisan migrate:fresh --seed }
function Invoke-Rollback { php artisan migrate:rollback }
function Invoke-Seed { php artisan db:seed }

function Invoke-Test { php artisan test }
function Invoke-TestParallel { php artisan test --parallel }
function Invoke-TestCoverage { php artisan test --coverage --coverage-html=coverage }

function Invoke-Lint {
    .\vendor\bin\pint --test
    .\vendor\bin\phpstan analyse
}
function Invoke-Fix { .\vendor\bin\pint }
function Invoke-Stan { .\vendor\bin\phpstan analyse }
function Invoke-Ide {
    php artisan ide-helper:generate
    php artisan ide-helper:models -N
    php artisan ide-helper:meta
}

function Invoke-CacheClear { php artisan optimize:clear }
function Invoke-Cache { php artisan optimize }

function Invoke-BuildProd {
    npm run build
    php artisan optimize
    Write-Green "✅ Production build complete"
}

# Execute command
switch ($Command) {
    "help"          { Show-Help }
    "install"       { Invoke-Install }
    "setup"         { Invoke-Setup }
    "dev"           { Invoke-Dev }
    "serve"         { Invoke-Serve }
    "vite"          { Invoke-Vite }
    "horizon"       { Invoke-Horizon }
    "queue"         { Invoke-Queue }
    "tinker"        { Invoke-Tinker }
    "up"            { Invoke-DockerUp }
    "down"          { Invoke-DockerDown }
    "restart"       { Invoke-DockerRestart }
    "logs"          { Invoke-DockerLogs }
    "shell"         { Invoke-DockerShell }
    "build"         { Invoke-DockerBuild }
    "migrate"       { Invoke-Migrate }
    "migrate-fresh" { Invoke-MigrateFresh }
    "rollback"      { Invoke-Rollback }
    "seed"          { Invoke-Seed }
    "test"          { Invoke-Test }
    "test-parallel" { Invoke-TestParallel }
    "test-coverage" { Invoke-TestCoverage }
    "lint"          { Invoke-Lint }
    "fix"           { Invoke-Fix }
    "stan"          { Invoke-Stan }
    "ide"           { Invoke-Ide }
    "cache-clear"   { Invoke-CacheClear }
    "cache"         { Invoke-Cache }
    "build-prod"    { Invoke-BuildProd }
    default         { Write-Yellow "Unknown command: $Command"; Show-Help }
}
