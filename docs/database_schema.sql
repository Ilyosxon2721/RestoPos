-- ============================================================
-- RestoPOS - Полная схема базы данных
-- Версия: 1.0
-- СУБД: MySQL 8.0+ / PostgreSQL 16+
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- РАЗДЕЛ 1: МУЛЬТИТЕНАНТНОСТЬ И ОРГАНИЗАЦИИ
-- ============================================================

-- Организации (тенанты) - владельцы бизнеса
CREATE TABLE organizations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    legal_name VARCHAR(255),
    inn VARCHAR(20),
    logo VARCHAR(500),
    subdomain VARCHAR(100) UNIQUE,
    subscription_plan ENUM('trial', 'starter', 'business', 'enterprise') DEFAULT 'trial',
    subscription_expires_at TIMESTAMP NULL,
    settings JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_subdomain (subdomain),
    INDEX idx_subscription (subscription_plan, subscription_expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Филиалы/Заведения
CREATE TABLE branches (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE,
    organization_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(500),
    city VARCHAR(100),
    phone VARCHAR(50),
    email VARCHAR(255),
    timezone VARCHAR(50) DEFAULT 'Asia/Tashkent',
    currency_code CHAR(3) DEFAULT 'UZS',
    working_hours JSON,
    settings JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    INDEX idx_org (organization_id),
    INDEX idx_city (city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 2: ПОЛЬЗОВАТЕЛИ И ПРАВА ДОСТУПА
-- ============================================================

-- Пользователи системы
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE,
    organization_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    pin_code VARCHAR(10),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100),
    avatar VARCHAR(500),
    locale VARCHAR(10) DEFAULT 'ru',
    email_verified_at TIMESTAMP NULL,
    two_factor_secret VARCHAR(255),
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    UNIQUE KEY uk_org_email (organization_id, email),
    INDEX idx_phone (phone),
    INDEX idx_pin (organization_id, pin_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Роли
CREATE TABLE roles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NULL, -- NULL = системная роль
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT,
    is_system BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    UNIQUE KEY uk_org_slug (organization_id, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Права доступа
CREATE TABLE permissions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    module VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Связь ролей и прав
CREATE TABLE role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Связь пользователей и ролей
CREATE TABLE user_roles (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NULL, -- NULL = все филиалы
    PRIMARY KEY (user_id, role_id, branch_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 3: ПЕРСОНАЛ
-- ============================================================

-- Сотрудники (расширение пользователей)
CREATE TABLE employees (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    branch_id BIGINT UNSIGNED NOT NULL,
    position VARCHAR(100),
    hire_date DATE,
    birth_date DATE,
    passport_series VARCHAR(20),
    passport_number VARCHAR(20),
    address TEXT,
    emergency_contact VARCHAR(255),
    emergency_phone VARCHAR(50),
    salary_type ENUM('hourly', 'monthly', 'percent', 'mixed') DEFAULT 'monthly',
    hourly_rate DECIMAL(12,2) DEFAULT 0,
    monthly_salary DECIMAL(12,2) DEFAULT 0,
    sales_percent DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    INDEX idx_branch (branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Смены сотрудников
CREATE TABLE employee_shifts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    clock_in TIMESTAMP NOT NULL,
    clock_out TIMESTAMP NULL,
    break_minutes INT DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    INDEX idx_employee_date (employee_id, clock_in)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Начисления ЗП
CREATE TABLE salary_calculations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    hours_worked DECIMAL(8,2) DEFAULT 0,
    base_salary DECIMAL(12,2) DEFAULT 0,
    sales_bonus DECIMAL(12,2) DEFAULT 0,
    tips DECIMAL(12,2) DEFAULT 0,
    bonuses DECIMAL(12,2) DEFAULT 0,
    penalties DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    status ENUM('draft', 'approved', 'paid') DEFAULT 'draft',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_period (period_start, period_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 4: ЗАЛЫ И СТОЛЫ
-- ============================================================

-- Залы
CREATE TABLE halls (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    branch_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    INDEX idx_branch (branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Столы
CREATE TABLE tables (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    hall_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,
    capacity INT DEFAULT 4,
    shape ENUM('square', 'round', 'rectangle') DEFAULT 'square',
    pos_x INT DEFAULT 0,
    pos_y INT DEFAULT 0,
    width INT DEFAULT 100,
    height INT DEFAULT 100,
    status ENUM('free', 'occupied', 'reserved', 'unavailable') DEFAULT 'free',
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hall_id) REFERENCES halls(id) ON DELETE CASCADE,
    INDEX idx_hall_status (hall_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 5: МЕНЮ И ТОВАРЫ
-- ============================================================

-- Категории меню
CREATE TABLE categories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255),
    description TEXT,
    image VARCHAR(500),
    color VARCHAR(7),
    sort_order INT DEFAULT 0,
    is_visible BOOLEAN DEFAULT TRUE,
    available_from TIME NULL,
    available_to TIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_org_parent (organization_id, parent_id),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Цехи производства
CREATE TABLE workshops (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    branch_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    printer_id BIGINT UNSIGNED NULL,
    color VARCHAR(7),
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Единицы измерения
CREATE TABLE units (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,
    short_name VARCHAR(10) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Товары/Блюда
CREATE TABLE products (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE,
    organization_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NULL,
    workshop_id BIGINT UNSIGNED NULL,
    unit_id BIGINT UNSIGNED NULL,
    type ENUM('dish', 'drink', 'product', 'service', 'semi_finished') DEFAULT 'dish',
    name VARCHAR(255) NOT NULL,
    name_uz VARCHAR(255),
    name_en VARCHAR(255),
    slug VARCHAR(255),
    sku VARCHAR(100),
    barcode VARCHAR(100),
    description TEXT,
    description_uz TEXT,
    description_en TEXT,
    image VARCHAR(500),
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    cost_price DECIMAL(12,2) DEFAULT 0,
    calories INT,
    proteins DECIMAL(8,2),
    fats DECIMAL(8,2),
    carbohydrates DECIMAL(8,2),
    weight DECIMAL(10,3),
    cooking_time INT, -- в минутах
    is_weighable BOOLEAN DEFAULT FALSE,
    is_visible BOOLEAN DEFAULT TRUE,
    is_available BOOLEAN DEFAULT TRUE,
    in_stop_list BOOLEAN DEFAULT FALSE,
    stop_list_reason VARCHAR(255),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE SET NULL,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL,
    INDEX idx_org_category (organization_id, category_id),
    INDEX idx_barcode (barcode),
    INDEX idx_sku (sku),
    FULLTEXT idx_search (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Цены по филиалам (если отличаются)
CREATE TABLE product_prices (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    UNIQUE KEY uk_product_branch (product_id, branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 6: МОДИФИКАТОРЫ
-- ============================================================

-- Группы модификаторов
CREATE TABLE modifier_groups (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    name_uz VARCHAR(255),
    name_en VARCHAR(255),
    type ENUM('single', 'multiple') DEFAULT 'single',
    is_required BOOLEAN DEFAULT FALSE,
    min_selections INT DEFAULT 0,
    max_selections INT DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Модификаторы
CREATE TABLE modifiers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    modifier_group_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    name_uz VARCHAR(255),
    name_en VARCHAR(255),
    price_adjustment DECIMAL(12,2) DEFAULT 0,
    is_default BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (modifier_group_id) REFERENCES modifier_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Связь товаров и групп модификаторов
CREATE TABLE product_modifier_groups (
    product_id BIGINT UNSIGNED NOT NULL,
    modifier_group_id BIGINT UNSIGNED NOT NULL,
    sort_order INT DEFAULT 0,
    PRIMARY KEY (product_id, modifier_group_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (modifier_group_id) REFERENCES modifier_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 7: ИНГРЕДИЕНТЫ И ТЕХКАРТЫ
-- ============================================================

-- Ингредиенты
CREATE TABLE ingredients (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100),
    barcode VARCHAR(100),
    min_stock DECIMAL(12,3) DEFAULT 0,
    current_cost DECIMAL(12,4) DEFAULT 0,
    loss_percent DECIMAL(5,2) DEFAULT 0, -- % потерь при обработке
    shelf_life_days INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    INDEX idx_org (organization_id),
    INDEX idx_barcode (barcode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Технологические карты
CREATE TABLE tech_cards (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    output_quantity DECIMAL(10,3) NOT NULL DEFAULT 1,
    description TEXT,
    cooking_instructions TEXT,
    version INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Состав техкарты
CREATE TABLE tech_card_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tech_card_id BIGINT UNSIGNED NOT NULL,
    ingredient_id BIGINT UNSIGNED NULL,
    semi_finished_id BIGINT UNSIGNED NULL, -- ссылка на полуфабрикат (product)
    quantity DECIMAL(12,4) NOT NULL,
    loss_percent DECIMAL(5,2) DEFAULT 0,
    gross_quantity DECIMAL(12,4) GENERATED ALWAYS AS (quantity * (1 + loss_percent / 100)) STORED,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (tech_card_id) REFERENCES tech_cards(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
    FOREIGN KEY (semi_finished_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_tech_card (tech_card_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 8: СКЛАД
-- ============================================================

-- Склады
CREATE TABLE warehouses (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    branch_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('main', 'kitchen', 'bar', 'freezer') DEFAULT 'main',
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Поставщики
CREATE TABLE suppliers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    legal_name VARCHAR(255),
    inn VARCHAR(20),
    contact_person VARCHAR(255),
    phone VARCHAR(50),
    email VARCHAR(255),
    address TEXT,
    payment_terms INT DEFAULT 0, -- дни отсрочки
    notes TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Поставки (приходные накладные)
CREATE TABLE supplies (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    number VARCHAR(50),
    document_number VARCHAR(100),
    document_date DATE,
    total_amount DECIMAL(14,2) DEFAULT 0,
    status ENUM('draft', 'pending', 'received', 'cancelled') DEFAULT 'draft',
    received_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_warehouse_date (warehouse_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Позиции поставки
CREATE TABLE supply_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    supply_id BIGINT UNSIGNED NOT NULL,
    ingredient_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(12,3) NOT NULL,
    price DECIMAL(12,4) NOT NULL,
    total DECIMAL(14,2) GENERATED ALWAYS AS (quantity * price) STORED,
    expiry_date DATE,
    batch_number VARCHAR(100),
    FOREIGN KEY (supply_id) REFERENCES supplies(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Остатки на складе
CREATE TABLE stock (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    ingredient_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(12,3) NOT NULL DEFAULT 0,
    reserved_quantity DECIMAL(12,3) DEFAULT 0,
    average_cost DECIMAL(12,4) DEFAULT 0,
    last_supply_date TIMESTAMP NULL,
    last_supply_price DECIMAL(12,4),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
    UNIQUE KEY uk_warehouse_ingredient (warehouse_id, ingredient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Партии (для FIFO учёта)
CREATE TABLE stock_batches (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    ingredient_id BIGINT UNSIGNED NOT NULL,
    supply_item_id BIGINT UNSIGNED NULL,
    initial_quantity DECIMAL(12,3) NOT NULL,
    remaining_quantity DECIMAL(12,3) NOT NULL,
    cost_price DECIMAL(12,4) NOT NULL,
    expiry_date DATE,
    batch_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
    FOREIGN KEY (supply_item_id) REFERENCES supply_items(id) ON DELETE SET NULL,
    INDEX idx_warehouse_ingredient (warehouse_id, ingredient_id),
    INDEX idx_expiry (expiry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Движение товаров
CREATE TABLE stock_movements (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    ingredient_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('supply', 'sale', 'write_off', 'transfer_in', 'transfer_out', 'production', 'inventory', 'return') NOT NULL,
    quantity DECIMAL(12,3) NOT NULL, -- положительное или отрицательное
    cost_price DECIMAL(12,4),
    reference_type VARCHAR(50), -- orders, supplies, write_offs, transfers, inventories
    reference_id BIGINT UNSIGNED,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_warehouse_date (warehouse_id, created_at),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Списания
CREATE TABLE write_offs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    number VARCHAR(50),
    reason ENUM('spoilage', 'damage', 'theft', 'expired', 'other') NOT NULL,
    total_amount DECIMAL(14,2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Позиции списания
CREATE TABLE write_off_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    write_off_id BIGINT UNSIGNED NOT NULL,
    ingredient_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(12,3) NOT NULL,
    cost_price DECIMAL(12,4) NOT NULL,
    total DECIMAL(14,2) GENERATED ALWAYS AS (quantity * cost_price) STORED,
    FOREIGN KEY (write_off_id) REFERENCES write_offs(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Перемещения между складами
CREATE TABLE transfers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    from_warehouse_id BIGINT UNSIGNED NOT NULL,
    to_warehouse_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    number VARCHAR(50),
    status ENUM('draft', 'sent', 'received', 'cancelled') DEFAULT 'draft',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (from_warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (to_warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Позиции перемещения
CREATE TABLE transfer_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    transfer_id BIGINT UNSIGNED NOT NULL,
    ingredient_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(12,3) NOT NULL,
    FOREIGN KEY (transfer_id) REFERENCES transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Инвентаризации
CREATE TABLE inventories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    number VARCHAR(50),
    status ENUM('draft', 'in_progress', 'completed', 'cancelled') DEFAULT 'draft',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Позиции инвентаризации
CREATE TABLE inventory_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    inventory_id BIGINT UNSIGNED NOT NULL,
    ingredient_id BIGINT UNSIGNED NOT NULL,
    expected_quantity DECIMAL(12,3) NOT NULL,
    actual_quantity DECIMAL(12,3),
    difference DECIMAL(12,3) GENERATED ALWAYS AS (actual_quantity - expected_quantity) STORED,
    cost_price DECIMAL(12,4),
    FOREIGN KEY (inventory_id) REFERENCES inventories(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 9: ЗАКАЗЫ И ПРОДАЖИ
-- ============================================================

-- Кассовые смены
CREATE TABLE cash_shifts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    branch_id BIGINT UNSIGNED NOT NULL,
    terminal_id BIGINT UNSIGNED NULL,
    opened_by BIGINT UNSIGNED NOT NULL,
    closed_by BIGINT UNSIGNED NULL,
    opened_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL,
    opening_cash DECIMAL(12,2) DEFAULT 0,
    closing_cash DECIMAL(12,2),
    expected_cash DECIMAL(12,2),
    cash_difference DECIMAL(12,2),
    total_sales DECIMAL(14,2) DEFAULT 0,
    total_refunds DECIMAL(14,2) DEFAULT 0,
    total_cash_payments DECIMAL(14,2) DEFAULT 0,
    total_card_payments DECIMAL(14,2) DEFAULT 0,
    total_orders INT DEFAULT 0,
    status ENUM('open', 'closed') DEFAULT 'open',
    notes TEXT,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (opened_by) REFERENCES users(id),
    FOREIGN KEY (closed_by) REFERENCES users(id),
    INDEX idx_branch_status (branch_id, status),
    INDEX idx_opened (opened_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Кассовые операции (внесения/изъятия)
CREATE TABLE cash_operations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    cash_shift_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('deposit', 'withdrawal') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    reason VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cash_shift_id) REFERENCES cash_shifts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Заказы
CREATE TABLE orders (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE,
    branch_id BIGINT UNSIGNED NOT NULL,
    cash_shift_id BIGINT UNSIGNED NULL,
    table_id BIGINT UNSIGNED NULL,
    customer_id BIGINT UNSIGNED NULL,
    waiter_id BIGINT UNSIGNED NULL,
    
    order_number VARCHAR(50) NOT NULL,
    type ENUM('dine_in', 'takeaway', 'delivery', 'preorder') DEFAULT 'dine_in',
    source ENUM('pos', 'website', 'app', 'aggregator', 'phone', 'qr') DEFAULT 'pos',
    
    guests_count INT DEFAULT 1,
    
    subtotal DECIMAL(14,2) DEFAULT 0,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    discount_reason VARCHAR(255),
    service_charge DECIMAL(12,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(14,2) DEFAULT 0,
    
    status ENUM('new', 'accepted', 'preparing', 'ready', 'served', 'completed', 'cancelled') DEFAULT 'new',
    payment_status ENUM('unpaid', 'partial', 'paid', 'refunded') DEFAULT 'unpaid',
    
    notes TEXT,
    
    opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    accepted_at TIMESTAMP NULL,
    ready_at TIMESTAMP NULL,
    closed_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (cash_shift_id) REFERENCES cash_shifts(id) ON DELETE SET NULL,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (waiter_id) REFERENCES employees(id) ON DELETE SET NULL,
    
    INDEX idx_branch_date (branch_id, created_at),
    INDEX idx_status (status),
    INDEX idx_number (order_number),
    INDEX idx_table (table_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Позиции заказа
CREATE TABLE order_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    
    name VARCHAR(255) NOT NULL, -- сохраняем на момент заказа
    quantity DECIMAL(10,3) NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    total_price DECIMAL(12,2) NOT NULL,
    cost_price DECIMAL(12,2) DEFAULT 0,
    
    course INT DEFAULT 1, -- номер курса подачи
    status ENUM('pending', 'sent', 'preparing', 'ready', 'served', 'cancelled') DEFAULT 'pending',
    
    sent_to_kitchen_at TIMESTAMP NULL,
    ready_at TIMESTAMP NULL,
    
    comment TEXT,
    cancelled_reason VARCHAR(255),
    cancelled_by BIGINT UNSIGNED NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (cancelled_by) REFERENCES users(id),
    
    INDEX idx_order (order_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Модификаторы позиции заказа
CREATE TABLE order_item_modifiers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_item_id BIGINT UNSIGNED NOT NULL,
    modifier_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    price_adjustment DECIMAL(12,2) DEFAULT 0,
    quantity INT DEFAULT 1,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE,
    FOREIGN KEY (modifier_id) REFERENCES modifiers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 10: ОПЛАТЫ
-- ============================================================

-- Способы оплаты
CREATE TABLE payment_methods (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('cash', 'card', 'transfer', 'bonus', 'credit', 'other') NOT NULL,
    is_fiscal BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Платежи
CREATE TABLE payments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE,
    order_id BIGINT UNSIGNED NOT NULL,
    payment_method_id BIGINT UNSIGNED NOT NULL,
    cash_shift_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    
    amount DECIMAL(12,2) NOT NULL,
    change_amount DECIMAL(12,2) DEFAULT 0, -- сдача для наличных
    
    status ENUM('pending', 'completed', 'refunded', 'cancelled') DEFAULT 'pending',
    
    transaction_id VARCHAR(255), -- ID транзакции из платёжной системы
    payment_data JSON, -- дополнительные данные от платёжной системы
    
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id),
    FOREIGN KEY (cash_shift_id) REFERENCES cash_shifts(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    
    INDEX idx_order (order_id),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Чеки (фискальные)
CREATE TABLE receipts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    payment_id BIGINT UNSIGNED NULL,
    
    type ENUM('sale', 'refund', 'precheck') NOT NULL,
    number VARCHAR(100),
    fiscal_number VARCHAR(100),
    fiscal_sign VARCHAR(255),
    
    amount DECIMAL(12,2) NOT NULL,
    
    status ENUM('pending', 'printed', 'sent', 'error') DEFAULT 'pending',
    error_message TEXT,
    
    receipt_data JSON,
    
    printed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    
    INDEX idx_fiscal (fiscal_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 11: КЛИЕНТЫ И ЛОЯЛЬНОСТЬ
-- ============================================================

-- Клиенты
CREATE TABLE customers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE,
    organization_id BIGINT UNSIGNED NOT NULL,
    
    phone VARCHAR(50),
    email VARCHAR(255),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    birth_date DATE,
    gender ENUM('male', 'female', 'other'),
    
    loyalty_card_number VARCHAR(100),
    bonus_balance DECIMAL(12,2) DEFAULT 0,
    total_spent DECIMAL(14,2) DEFAULT 0,
    total_orders INT DEFAULT 0,
    
    discount_percent DECIMAL(5,2) DEFAULT 0,
    customer_group_id BIGINT UNSIGNED NULL,
    
    notes TEXT,
    tags JSON,
    
    last_visit_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    
    INDEX idx_org_phone (organization_id, phone),
    INDEX idx_card (loyalty_card_number),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Группы клиентов
CREATE TABLE customer_groups (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    bonus_earn_percent DECIMAL(5,2) DEFAULT 0,
    min_spent_to_join DECIMAL(14,2) DEFAULT 0,
    color VARCHAR(7),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Бонусные транзакции
CREATE TABLE bonus_transactions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NULL,
    
    type ENUM('earn', 'spend', 'adjust', 'expire') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    balance_after DECIMAL(12,2) NOT NULL,
    
    description VARCHAR(255),
    expires_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    
    INDEX idx_customer (customer_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Акции
CREATE TABLE promotions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    
    name VARCHAR(255) NOT NULL,
    description TEXT,
    
    type ENUM('discount', 'bonus_multiply', 'gift', 'combo', 'happy_hour', 'buy_x_get_y') NOT NULL,
    
    discount_type ENUM('percent', 'fixed') NULL,
    discount_value DECIMAL(12,2) NULL,
    
    conditions JSON, -- условия срабатывания
    
    min_order_amount DECIMAL(12,2),
    max_discount_amount DECIMAL(12,2),
    
    applicable_to ENUM('all', 'categories', 'products') DEFAULT 'all',
    applicable_ids JSON, -- IDs категорий или товаров
    
    start_date TIMESTAMP NULL,
    end_date TIMESTAMP NULL,
    
    active_days JSON, -- дни недели
    active_hours_from TIME,
    active_hours_to TIME,
    
    usage_limit INT,
    usage_count INT DEFAULT 0,
    usage_limit_per_customer INT,
    
    promo_code VARCHAR(50),
    
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    
    INDEX idx_active (is_active, start_date, end_date),
    INDEX idx_promo_code (promo_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 12: ДОСТАВКА
-- ============================================================

-- Зоны доставки
CREATE TABLE delivery_zones (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    branch_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    polygon JSON NOT NULL, -- координаты полигона
    delivery_fee DECIMAL(12,2) DEFAULT 0,
    min_order_amount DECIMAL(12,2) DEFAULT 0,
    free_delivery_from DECIMAL(12,2) NULL,
    estimated_time INT, -- минуты
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Курьеры
CREATE TABLE couriers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NULL, -- если штатный
    branch_id BIGINT UNSIGNED NOT NULL,
    
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    
    vehicle_type ENUM('foot', 'bicycle', 'motorcycle', 'car') DEFAULT 'car',
    vehicle_number VARCHAR(50),
    
    status ENUM('offline', 'available', 'busy') DEFAULT 'offline',
    current_location_lat DECIMAL(10,8),
    current_location_lng DECIMAL(11,8),
    last_location_at TIMESTAMP NULL,
    
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Заказы на доставку
CREATE TABLE delivery_orders (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL UNIQUE,
    courier_id BIGINT UNSIGNED NULL,
    delivery_zone_id BIGINT UNSIGNED NULL,
    
    address TEXT NOT NULL,
    address_details VARCHAR(255), -- квартира, подъезд, этаж
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    
    contact_name VARCHAR(255),
    contact_phone VARCHAR(50) NOT NULL,
    
    delivery_fee DECIMAL(12,2) DEFAULT 0,
    
    scheduled_at TIMESTAMP NULL, -- запланированное время доставки
    estimated_delivery_at TIMESTAMP NULL,
    
    status ENUM('pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'failed') DEFAULT 'pending',
    
    assigned_at TIMESTAMP NULL,
    picked_up_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    
    delivery_notes TEXT,
    failure_reason VARCHAR(255),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (courier_id) REFERENCES couriers(id) ON DELETE SET NULL,
    FOREIGN KEY (delivery_zone_id) REFERENCES delivery_zones(id) ON DELETE SET NULL,
    
    INDEX idx_status (status),
    INDEX idx_courier (courier_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 13: БРОНИРОВАНИЕ
-- ============================================================

-- Бронирования
CREATE TABLE reservations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE,
    branch_id BIGINT UNSIGNED NOT NULL,
    table_id BIGINT UNSIGNED NULL,
    customer_id BIGINT UNSIGNED NULL,
    
    guest_name VARCHAR(255) NOT NULL,
    guest_phone VARCHAR(50) NOT NULL,
    guest_email VARCHAR(255),
    guests_count INT NOT NULL,
    
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    duration_minutes INT DEFAULT 120,
    
    deposit_amount DECIMAL(12,2) DEFAULT 0,
    deposit_paid BOOLEAN DEFAULT FALSE,
    
    status ENUM('pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    
    source ENUM('phone', 'website', 'app', 'walk_in') DEFAULT 'phone',
    
    special_requests TEXT,
    internal_notes TEXT,
    
    confirmed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason VARCHAR(255),
    
    reminder_sent BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    
    INDEX idx_branch_date (branch_id, reservation_date),
    INDEX idx_status (status),
    INDEX idx_phone (guest_phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 14: НАСТРОЙКИ И ОБОРУДОВАНИЕ
-- ============================================================

-- Терминалы
CREATE TABLE terminals (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    branch_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    device_id VARCHAR(255) UNIQUE,
    type ENUM('pos', 'kds', 'customer_display', 'kiosk') DEFAULT 'pos',
    settings JSON,
    last_seen_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Принтеры
CREATE TABLE printers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    branch_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('receipt', 'kitchen', 'label') NOT NULL,
    connection_type ENUM('network', 'usb', 'bluetooth') DEFAULT 'network',
    ip_address VARCHAR(45),
    port INT DEFAULT 9100,
    paper_width INT DEFAULT 80, -- мм
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 15: ЛОГИ И АУДИТ
-- ============================================================

-- Лог действий
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id BIGINT UNSIGNED,
    
    old_values JSON,
    new_values JSON,
    
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_org_date (organization_id, created_at),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_user (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Уведомления
CREATE TABLE notifications (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    
    type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    data JSON,
    
    read_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, read_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- РАЗДЕЛ 16: НАЧАЛЬНЫЕ ДАННЫЕ
-- ============================================================

-- Системные роли
INSERT INTO roles (name, slug, is_system) VALUES
    ('Владелец', 'owner', TRUE),
    ('Директор', 'director', TRUE),
    ('Администратор', 'admin', TRUE),
    ('Бухгалтер', 'accountant', TRUE),
    ('Старший официант', 'head_waiter', TRUE),
    ('Официант', 'waiter', TRUE),
    ('Бармен', 'bartender', TRUE),
    ('Кассир', 'cashier', TRUE),
    ('Повар', 'cook', TRUE),
    ('Курьер', 'courier', TRUE);

-- Базовые права доступа
INSERT INTO permissions (name, slug, module) VALUES
    -- Заказы
    ('Просмотр заказов', 'orders.view', 'orders'),
    ('Создание заказов', 'orders.create', 'orders'),
    ('Редактирование заказов', 'orders.edit', 'orders'),
    ('Отмена заказов', 'orders.cancel', 'orders'),
    ('Применение скидок', 'orders.discount', 'orders'),
    
    -- Меню
    ('Просмотр меню', 'menu.view', 'menu'),
    ('Управление меню', 'menu.manage', 'menu'),
    ('Управление техкартами', 'menu.tech_cards', 'menu'),
    
    -- Склад
    ('Просмотр склада', 'warehouse.view', 'warehouse'),
    ('Приёмка поставок', 'warehouse.supply', 'warehouse'),
    ('Списание', 'warehouse.write_off', 'warehouse'),
    ('Инвентаризация', 'warehouse.inventory', 'warehouse'),
    
    -- Персонал
    ('Просмотр персонала', 'staff.view', 'staff'),
    ('Управление персоналом', 'staff.manage', 'staff'),
    
    -- Финансы
    ('Просмотр финансов', 'finance.view', 'finance'),
    ('Управление финансами', 'finance.manage', 'finance'),
    ('Кассовые операции', 'finance.cash_operations', 'finance'),
    
    -- Отчёты
    ('Просмотр отчётов', 'reports.view', 'reports'),
    ('Экспорт отчётов', 'reports.export', 'reports'),
    
    -- Клиенты
    ('Просмотр клиентов', 'customers.view', 'customers'),
    ('Управление клиентами', 'customers.manage', 'customers'),
    
    -- Настройки
    ('Настройки заведения', 'settings.branch', 'settings'),
    ('Настройки системы', 'settings.system', 'settings');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- КОНЕЦ СХЕМЫ
-- ============================================================
