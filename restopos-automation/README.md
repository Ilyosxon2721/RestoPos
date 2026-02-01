# RestoPOS - Development Automation Files

Файлы автоматизации разработки для проекта RestoPOS.

## 📁 Структура

```
restopos-automation/
├── .vscode/                    # VS Code настройки
│   ├── settings.json           # Настройки редактора
│   ├── extensions.json         # Рекомендуемые расширения
│   ├── tasks.json              # Задачи автоматизации
│   └── launch.json             # Конфигурации отладки
├── .claude/                    # Claude Code команды
│   └── commands/
│       ├── create-module.md    # Создание доменного модуля
│       ├── create-livewire.md  # Создание Livewire компонента
│       ├── create-api.md       # Создание API endpoint
│       ├── code-review.md      # Код ревью
│       └── generate-tests.md   # Генерация тестов
├── docker/                     # Docker конфигурации
│   ├── Dockerfile              # PHP образ
│   ├── nginx/                  # Nginx конфигурации
│   └── php/                    # PHP настройки
├── scripts/                    # Скрипты автоматизации
│   ├── dev.ps1                 # PowerShell (Windows)
│   └── dev.bat                 # Batch (Windows CMD)
├── CLAUDE.md                   # Инструкции для Claude Code
├── Makefile                    # Unix команды (Mac/Linux)
├── docker-compose.yml          # Docker Compose
├── .env.example                # Пример переменных окружения
└── .editorconfig               # Настройки редактора
```

---

## 🚀 Быстрый старт

### Вариант 1: Локальная разработка (без Docker)

#### Mac/Linux:
```bash
# Копируйте файлы в корень проекта
cp -r restopos-automation/* /path/to/restopos/

# Установка
make setup

# Запуск
make dev
```

#### Windows (PowerShell):
```powershell
# Копируйте файлы в корень проекта
Copy-Item -Recurse restopos-automation\* C:\path\to\restopos\

# Установка
.\scripts\dev.ps1 setup

# Запуск
.\scripts\dev.ps1 dev
```

#### Windows (CMD):
```cmd
# Установка
scripts\dev.bat setup

# Запуск
scripts\dev.bat dev
```

### Вариант 2: Docker разработка

```bash
# Mac/Linux
make up

# Windows
.\scripts\dev.ps1 up
# или
docker compose up -d
```

После запуска:
- **Приложение**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8080 (профиль tools)
- **Mailpit**: http://localhost:8025
- **Redis Commander**: http://localhost:8081 (профиль tools)

---

## 🛠️ VS Code Setup

1. Откройте проект в VS Code
2. Установите рекомендуемые расширения (появится уведомление)
3. Перезапустите VS Code

### Задачи (Tasks)
Нажмите `Ctrl+Shift+P` → "Tasks: Run Task":
- 🚀 Serve (Dev Server)
- 🎨 Vite (Frontend Dev)
- 🔄 Dev: Start All
- 🐳 Docker: Up/Down
- 📦 Migrate
- 🧪 Test: All
- 🔍 Pint (Code Style)

### Отладка (Debug)
Нажмите `F5` и выберите:
- PHP: Listen for Xdebug
- PHPUnit: Debug Current Test
- JS: Chrome

---

## 🤖 Claude Code

### Установка
1. Установите расширение Claude Code в VS Code
2. Файлы `.claude/commands/` автоматически доступны

### Команды
В терминале Claude Code:

```
/create-module Order
/create-livewire Orders/CreateOrder
/create-api orders
/code-review app/Services/OrderService.php
/generate-tests OrderService
```

### CLAUDE.md
Файл `CLAUDE.md` содержит:
- Архитектуру проекта
- Стандарты кодирования
- Git workflow
- API паттерны
- Инструкции для Claude

---

## 📋 Makefile команды (Mac/Linux)

| Команда | Описание |
|---------|----------|
| `make help` | Показать все команды |
| `make install` | Полная установка |
| `make setup` | Быстрая настройка |
| `make dev` | Запустить dev-серверы |
| `make up` | Docker up |
| `make down` | Docker down |
| `make test` | Запустить тесты |
| `make lint` | Проверить код |
| `make fix` | Исправить стиль |

---

## 🪟 Windows команды

### PowerShell
```powershell
.\scripts\dev.ps1 help      # Показать все команды
.\scripts\dev.ps1 install   # Полная установка
.\scripts\dev.ps1 dev       # Запустить dev-серверы
.\scripts\dev.ps1 test      # Запустить тесты
```

### CMD
```cmd
scripts\dev.bat help
scripts\dev.bat install
scripts\dev.bat dev
scripts\dev.bat test
```

---

## 🐳 Docker профили

```bash
# Основные сервисы
docker compose up -d

# С инструментами (phpMyAdmin, Redis Commander)
docker compose --profile tools up -d

# С поиском (Meilisearch)
docker compose --profile search up -d

# Всё вместе
docker compose --profile tools --profile search up -d
```

---

## 📝 Конфигурация

### .env
```bash
# Скопируйте пример
cp .env.example .env

# Для Docker измените:
DB_HOST=mysql
REDIS_HOST=redis
MAIL_HOST=mailpit
```

### VS Code settings.json
Настройки уже оптимизированы для:
- PHP/Laravel разработки
- Blade шаблонов
- Tailwind CSS
- Livewire
- Xdebug

---

## ❓ FAQ

### Как добавить Xdebug в Docker?
Уже включен в Dockerfile. Настройте IDE на порт 9003.

### Как запустить тесты в Docker?
```bash
docker compose exec app php artisan test
```

### Как войти в MySQL в Docker?
```bash
make mysql-cli
# или
docker compose exec mysql mysql -urestopos -psecret restopos
```

### Как очистить Docker?
```bash
docker compose down -v  # Удалит volumes
docker system prune -a  # Удалит всё неиспользуемое
```

---

## 📞 Поддержка

При проблемах проверьте:
1. Версии: PHP 8.3+, Node 20+, Docker 24+
2. Порты не заняты: 8000, 3306, 6379, 8025
3. Docker запущен

---

Made with ❤️ for RestoPOS
