-- ============================================================
-- E-Commerce Backend Database
-- Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- MySQL 8+
-- Version: 1.0.0
-- ============================================================

CREATE DATABASE IF NOT EXISTS ecommerce_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ecommerce_db;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ============================================================
-- DROP EXISTING OBJECTS (makes file safe to re-import)
-- ============================================================

-- Triggers
DROP TRIGGER IF EXISTS trg_after_insert_user;
DROP TRIGGER IF EXISTS trg_before_insert_review;
DROP TRIGGER IF EXISTS trg_after_insert_review;
DROP TRIGGER IF EXISTS trg_after_update_review;
DROP TRIGGER IF EXISTS trg_after_delete_review;
DROP TRIGGER IF EXISTS trg_after_insert_order_item;
DROP TRIGGER IF EXISTS trg_after_insert_order;
DROP TRIGGER IF EXISTS trg_after_update_order_status;
DROP TRIGGER IF EXISTS trg_before_update_product_stock;
DROP TRIGGER IF EXISTS trg_after_insert_coupon_usage;

-- Views
DROP VIEW IF EXISTS view_active_products;
DROP VIEW IF EXISTS view_deals;
DROP VIEW IF EXISTS view_popular_products;
DROP VIEW IF EXISTS view_popular_categories;
DROP VIEW IF EXISTS view_popular_brands;
DROP VIEW IF EXISTS view_cart_summary;
DROP VIEW IF EXISTS view_orders;
DROP VIEW IF EXISTS view_user_wishlist;
DROP VIEW IF EXISTS view_unread_notification_counts;
DROP VIEW IF EXISTS view_active_coupons;
DROP VIEW IF EXISTS view_valid_tokens;

-- Stored Procedures
DROP PROCEDURE IF EXISTS sp_get_user_for_login;
DROP PROCEDURE IF EXISTS sp_create_token;
DROP PROCEDURE IF EXISTS sp_verify_token;
DROP PROCEDURE IF EXISTS sp_logout;
DROP PROCEDURE IF EXISTS sp_register;
DROP PROCEDURE IF EXISTS sp_check_rate_limit;
DROP PROCEDURE IF EXISTS sp_record_failed_login;
DROP PROCEDURE IF EXISTS sp_create_password_reset;
DROP PROCEDURE IF EXISTS sp_verify_password_reset;
DROP PROCEDURE IF EXISTS sp_get_profile;
DROP PROCEDURE IF EXISTS sp_update_profile;
DROP PROCEDURE IF EXISTS sp_change_password;
DROP PROCEDURE IF EXISTS sp_delete_account;
DROP PROCEDURE IF EXISTS sp_get_home;
DROP PROCEDURE IF EXISTS sp_get_products;
DROP PROCEDURE IF EXISTS sp_get_product_by_slug;
DROP PROCEDURE IF EXISTS sp_get_related_products;
DROP PROCEDURE IF EXISTS sp_search_products;
DROP PROCEDURE IF EXISTS sp_get_product_reviews;
DROP PROCEDURE IF EXISTS sp_add_review;
DROP PROCEDURE IF EXISTS sp_get_category_products;
DROP PROCEDURE IF EXISTS sp_get_brand_products;
DROP PROCEDURE IF EXISTS sp_get_deals;
DROP PROCEDURE IF EXISTS sp_get_cart;
DROP PROCEDURE IF EXISTS sp_add_to_cart;
DROP PROCEDURE IF EXISTS sp_update_cart_item;
DROP PROCEDURE IF EXISTS sp_remove_cart_item;
DROP PROCEDURE IF EXISTS sp_clear_cart;
DROP PROCEDURE IF EXISTS sp_get_checkout_summary;
DROP PROCEDURE IF EXISTS sp_place_order;
DROP PROCEDURE IF EXISTS sp_get_user_orders;
DROP PROCEDURE IF EXISTS sp_get_order_detail;
DROP PROCEDURE IF EXISTS sp_cancel_order;
DROP PROCEDURE IF EXISTS sp_get_user_addresses;
DROP PROCEDURE IF EXISTS sp_create_address;
DROP PROCEDURE IF EXISTS sp_update_address;
DROP PROCEDURE IF EXISTS sp_delete_address;
DROP PROCEDURE IF EXISTS sp_set_default_address;
DROP PROCEDURE IF EXISTS sp_get_wishlist;
DROP PROCEDURE IF EXISTS sp_toggle_wishlist;
DROP PROCEDURE IF EXISTS sp_validate_coupon;
DROP PROCEDURE IF EXISTS sp_create_notification;
DROP PROCEDURE IF EXISTS sp_get_notifications;
DROP PROCEDURE IF EXISTS sp_mark_notifications_read;
DROP PROCEDURE IF EXISTS sp_register_push_token;
DROP PROCEDURE IF EXISTS sp_remove_push_token;
DROP PROCEDURE IF EXISTS sp_get_setting;
DROP PROCEDURE IF EXISTS sp_set_setting;
DROP PROCEDURE IF EXISTS sp_log_audit;
DROP PROCEDURE IF EXISTS sp_update_stock;
DROP PROCEDURE IF EXISTS sp_admin_get_orders;
DROP PROCEDURE IF EXISTS sp_admin_update_order_status;
DROP PROCEDURE IF EXISTS sp_admin_dashboard_stats;

-- Events
DROP EVENT IF EXISTS evt_purge_expired_tokens;
DROP EVENT IF EXISTS evt_purge_password_resets;
DROP EVENT IF EXISTS evt_purge_failed_logins;
DROP EVENT IF EXISTS evt_purge_old_notifications;
DROP EVENT IF EXISTS evt_purge_old_audit_logs;
DROP EVENT IF EXISTS evt_hard_delete_old_users;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    fullname       VARCHAR(255)    NOT NULL,
    email          VARCHAR(255)    NOT NULL,
    password       VARCHAR(255)    NOT NULL,
    gender         ENUM('male','female','other') NOT NULL DEFAULT 'other',
    birthdate      DATE            NULL DEFAULT NULL,
    profile_image  VARCHAR(500)    NULL DEFAULT NULL,
    created_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at     TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    INDEX idx_users_deleted_at (deleted_at),
    INDEX idx_users_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registered user accounts';

-- ============================================================
-- TABLE: user_tokens
-- ============================================================
CREATE TABLE IF NOT EXISTS user_tokens (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id      BIGINT UNSIGNED NOT NULL,
    token        VARCHAR(512)    NOT NULL,
    device_name  VARCHAR(255)    NULL DEFAULT NULL,
    ip_address   VARCHAR(45)     NULL DEFAULT NULL,
    last_used_at TIMESTAMP       NULL DEFAULT NULL,
    expires_at   TIMESTAMP       NULL DEFAULT NULL,
    created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_user_tokens_token (token),
    INDEX idx_user_tokens_user_id   (user_id),
    INDEX idx_user_tokens_expires_at (expires_at),
    INDEX idx_user_tokens_token_expiry (token, expires_at),
    CONSTRAINT fk_user_tokens_user_id
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bearer tokens for authenticated sessions';

-- ============================================================
-- TABLE: categories
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(255)    NOT NULL,
    icon       VARCHAR(500)    NULL DEFAULT NULL,
    image      VARCHAR(500)    NULL DEFAULT NULL,
    is_active  TINYINT(1)      NOT NULL DEFAULT 1,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_categories_is_active (is_active),
    INDEX idx_categories_name      (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Product categories';

-- ============================================================
-- TABLE: brands
-- ============================================================
CREATE TABLE IF NOT EXISTS brands (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(255)    NOT NULL,
    icon       VARCHAR(500)    NULL DEFAULT NULL,
    image      VARCHAR(500)    NULL DEFAULT NULL,
    is_active  TINYINT(1)      NOT NULL DEFAULT 1,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_brands_is_active (is_active),
    INDEX idx_brands_name      (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Product brands';

-- ============================================================
-- TABLE: advertisements
-- ============================================================
CREATE TABLE IF NOT EXISTS advertisements (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title          VARCHAR(500)    NOT NULL,
    image          VARCHAR(500)    NOT NULL,
    redirect_type  ENUM('product','category','brand','url','none') NOT NULL DEFAULT 'none',
    redirect_value VARCHAR(500)    NULL DEFAULT NULL,
    is_active      TINYINT(1)      NOT NULL DEFAULT 1,
    sort_order     SMALLINT        NOT NULL DEFAULT 0,
    created_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_advertisements_is_active  (is_active),
    INDEX idx_advertisements_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Home screen advertisement slider';

-- ============================================================
-- TABLE: delivery_types
-- ============================================================
CREATE TABLE IF NOT EXISTS delivery_types (
    id                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name                    VARCHAR(255)    NOT NULL,
    fee                     DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    estimated_delivery_time VARCHAR(255)    NULL DEFAULT NULL,
    is_active               TINYINT(1)      NOT NULL DEFAULT 1,
    created_at              TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_delivery_types_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Available delivery methods and fees';

-- ============================================================
-- TABLE: payment_types
-- ============================================================
CREATE TABLE IF NOT EXISTS payment_types (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(255)    NOT NULL,
    is_active  TINYINT(1)      NOT NULL DEFAULT 1,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_payment_types_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Supported payment methods';

-- ============================================================
-- TABLE: coupons
-- ============================================================
CREATE TABLE IF NOT EXISTS coupons (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code             VARCHAR(100)    NOT NULL,
    description      VARCHAR(500)    NULL DEFAULT NULL,
    discount_type    ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
    discount_value   DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    min_order_amount DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    max_discount_cap DECIMAL(10,2)   NULL DEFAULT NULL,
    usage_limit      INT UNSIGNED    NULL DEFAULT NULL,
    usage_count      INT UNSIGNED    NOT NULL DEFAULT 0,
    per_user_limit   INT UNSIGNED    NOT NULL DEFAULT 1,
    is_active        TINYINT(1)      NOT NULL DEFAULT 1,
    starts_at        TIMESTAMP       NULL DEFAULT NULL,
    expires_at       TIMESTAMP       NULL DEFAULT NULL,
    created_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_coupons_code  (code),
    INDEX idx_coupons_is_active  (is_active),
    INDEX idx_coupons_expires_at (expires_at),
    INDEX idx_coupons_starts_at  (starts_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Discount coupons';

-- ============================================================
-- TABLE: products
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    brand_id       BIGINT UNSIGNED  NULL DEFAULT NULL,
    category_id    BIGINT UNSIGNED  NULL DEFAULT NULL,
    name           VARCHAR(500)     NOT NULL,
    slug           VARCHAR(600)     NOT NULL,
    description    TEXT             NULL DEFAULT NULL,
    sku            VARCHAR(100)     NULL DEFAULT NULL,
    barcode        VARCHAR(100)     NULL DEFAULT NULL,
    price          DECIMAL(12,2)    NOT NULL DEFAULT 0.00,
    has_offer      TINYINT(1)       NOT NULL DEFAULT 0,
    offer_price    DECIMAL(12,2)    NULL DEFAULT NULL,
    stock_quantity INT UNSIGNED     NOT NULL DEFAULT 0,
    main_image     VARCHAR(500)     NULL DEFAULT NULL,
    is_active      TINYINT(1)       NOT NULL DEFAULT 1,
    average_rating DECIMAL(3,2)     NOT NULL DEFAULT 0.00,
    total_reviews  INT UNSIGNED     NOT NULL DEFAULT 0,
    total_sales    INT UNSIGNED     NOT NULL DEFAULT 0,
    created_at     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at     TIMESTAMP        NULL DEFAULT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_products_slug (slug),
    UNIQUE KEY uq_products_sku  (sku),
    INDEX idx_products_brand_id      (brand_id),
    INDEX idx_products_category_id   (category_id),
    INDEX idx_products_is_active     (is_active),
    INDEX idx_products_has_offer     (has_offer),
    INDEX idx_products_deleted_at    (deleted_at),
    INDEX idx_products_created_at    (created_at),
    INDEX idx_products_price         (price),
    INDEX idx_products_average_rating(average_rating),
    INDEX idx_products_cat_active_offer (category_id, is_active, has_offer, deleted_at),
    INDEX idx_products_brand_active     (brand_id, is_active, deleted_at),
    FULLTEXT INDEX ft_products_name_description (name, description),
    CONSTRAINT fk_products_brand_id
        FOREIGN KEY (brand_id) REFERENCES brands (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_products_category_id
        FOREIGN KEY (category_id) REFERENCES categories (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Main products table';

-- ============================================================
-- TABLE: product_images
-- ============================================================
CREATE TABLE IF NOT EXISTS product_images (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    image_url  VARCHAR(500)    NOT NULL,
    sort_order SMALLINT        NOT NULL DEFAULT 0,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_product_images_product_id (product_id),
    CONSTRAINT fk_product_images_product_id
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Additional product images';

-- ============================================================
-- TABLE: product_options
-- ============================================================
CREATE TABLE IF NOT EXISTS product_options (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    name       VARCHAR(255)    NOT NULL,
    sort_order SMALLINT        NOT NULL DEFAULT 0,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_product_options_product_id (product_id),
    CONSTRAINT fk_product_options_product_id
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Option groups per product (Color, Size, etc.)';

-- ============================================================
-- TABLE: product_option_values
-- ============================================================
CREATE TABLE IF NOT EXISTS product_option_values (
    id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_option_id BIGINT UNSIGNED NOT NULL,
    value             VARCHAR(255)    NOT NULL,
    price_modifier    DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    stock_modifier    INT             NOT NULL DEFAULT 0,
    sort_order        SMALLINT        NOT NULL DEFAULT 0,
    created_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_product_option_values_option_id (product_option_id),
    CONSTRAINT fk_product_option_values_option_id
        FOREIGN KEY (product_option_id) REFERENCES product_options (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Values for each option group';

-- ============================================================
-- TABLE: product_reviews
-- ============================================================
CREATE TABLE IF NOT EXISTS product_reviews (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    rating     TINYINT UNSIGNED NOT NULL DEFAULT 5,
    comment    TEXT             NULL DEFAULT NULL,
    created_at TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_reviews_user_product (user_id, product_id),
    INDEX idx_product_reviews_product_id (product_id),
    INDEX idx_product_reviews_user_id    (user_id),
    INDEX idx_product_reviews_rating     (rating),
    CONSTRAINT chk_product_reviews_rating CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT fk_product_reviews_user_id
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_product_reviews_product_id
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User reviews and ratings for products';

-- ============================================================
-- TABLE: addresses
-- ============================================================
CREATE TABLE IF NOT EXISTS addresses (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         BIGINT UNSIGNED NOT NULL,
    latitude        DECIMAL(10,8)   NULL DEFAULT NULL,
    longitude       DECIMAL(11,8)   NULL DEFAULT NULL,
    city_name       VARCHAR(255)    NOT NULL,
    street_name     VARCHAR(500)    NOT NULL,
    building_number VARCHAR(100)    NULL DEFAULT NULL,
    additional_info TEXT            NULL DEFAULT NULL,
    is_default      TINYINT(1)      NOT NULL DEFAULT 0,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP       NULL DEFAULT NULL,

    PRIMARY KEY (id),
    INDEX idx_addresses_user_id    (user_id),
    INDEX idx_addresses_is_default (is_default),
    INDEX idx_addresses_deleted_at (deleted_at),
    CONSTRAINT fk_addresses_user_id
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User delivery addresses';

-- ============================================================
-- TABLE: carts
-- ============================================================
CREATE TABLE IF NOT EXISTS carts (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_carts_user_id (user_id),
    CONSTRAINT fk_carts_user_id
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Shopping carts';

-- ============================================================
-- TABLE: cart_items
-- ============================================================
CREATE TABLE IF NOT EXISTS cart_items (
    id                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    cart_id               BIGINT UNSIGNED NOT NULL,
    product_id            BIGINT UNSIGNED NOT NULL,
    quantity              INT UNSIGNED    NOT NULL DEFAULT 1,
    selected_options_json JSON            NULL DEFAULT NULL,
    created_at            TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_cart_items_cart_id    (cart_id),
    INDEX idx_cart_items_product_id (product_id),
    CONSTRAINT fk_cart_items_cart_id
        FOREIGN KEY (cart_id) REFERENCES carts (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cart_items_product_id
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Items inside a user cart';

-- ============================================================
-- TABLE: orders
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id          BIGINT UNSIGNED NOT NULL,
    address_id       BIGINT UNSIGNED NULL DEFAULT NULL,
    delivery_type_id BIGINT UNSIGNED NULL DEFAULT NULL,
    payment_type_id  BIGINT UNSIGNED NULL DEFAULT NULL,
    coupon_id        BIGINT UNSIGNED NULL DEFAULT NULL,
    coupon_code      VARCHAR(100)    NULL DEFAULT NULL,
    subtotal         DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    delivery_fee     DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    discount_amount  DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    total_price      DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    status           ENUM('pending','preparing','delivering','finished','canceled','rejected') NOT NULL DEFAULT 'pending',
    notes            TEXT            NULL DEFAULT NULL,
    -- Address snapshot
    address_city     VARCHAR(255)    NULL DEFAULT NULL,
    address_street   VARCHAR(500)    NULL DEFAULT NULL,
    address_building VARCHAR(100)    NULL DEFAULT NULL,
    address_info     TEXT            NULL DEFAULT NULL,
    address_latitude  DECIMAL(10,8)  NULL DEFAULT NULL,
    address_longitude DECIMAL(11,8)  NULL DEFAULT NULL,
    -- Delivery snapshot
    delivery_name    VARCHAR(255)    NULL DEFAULT NULL,
    delivery_time    VARCHAR(255)    NULL DEFAULT NULL,
    -- Payment snapshot
    payment_name     VARCHAR(255)    NULL DEFAULT NULL,
    created_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_orders_user_id         (user_id),
    INDEX idx_orders_status          (status),
    INDEX idx_orders_created_at      (created_at),
    INDEX idx_orders_delivery_type_id(delivery_type_id),
    INDEX idx_orders_payment_type_id (payment_type_id),
    INDEX idx_orders_coupon_id       (coupon_id),
    INDEX idx_orders_user_status     (user_id, status, created_at),
    CONSTRAINT fk_orders_user_id
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_orders_address_id
        FOREIGN KEY (address_id) REFERENCES addresses (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_orders_delivery_type_id
        FOREIGN KEY (delivery_type_id) REFERENCES delivery_types (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_orders_payment_type_id
        FOREIGN KEY (payment_type_id) REFERENCES payment_types (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_orders_coupon_id
        FOREIGN KEY (coupon_id) REFERENCES coupons (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer orders with immutable snapshots';

-- ============================================================
-- TABLE: order_items
-- ============================================================
CREATE TABLE IF NOT EXISTS order_items (
    id                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id               BIGINT UNSIGNED NOT NULL,
    product_id             BIGINT UNSIGNED NULL DEFAULT NULL,
    product_name_snapshot  VARCHAR(500)    NOT NULL,
    product_price_snapshot DECIMAL(12,2)   NOT NULL,
    offer_price_snapshot   DECIMAL(12,2)   NULL DEFAULT NULL,
    quantity               INT UNSIGNED    NOT NULL DEFAULT 1,
    selected_options_json  JSON            NULL DEFAULT NULL,
    created_at             TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_order_items_order_id   (order_id),
    INDEX idx_order_items_product_id (product_id),
    CONSTRAINT fk_order_items_order_id
        FOREIGN KEY (order_id) REFERENCES orders (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_order_items_product_id
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Line items per order with product snapshots';

-- ============================================================
-- TABLE: order_status_history
-- ============================================================
CREATE TABLE IF NOT EXISTS order_status_history (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id   BIGINT UNSIGNED NOT NULL,
    status     ENUM('pending','preparing','delivering','finished','canceled','rejected') NOT NULL,
    note       TEXT            NULL DEFAULT NULL,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_order_status_history_order_id (order_id),
    INDEX idx_order_status_history_status   (status),
    CONSTRAINT fk_order_status_history_order_id
        FOREIGN KEY (order_id) REFERENCES orders (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit trail for order status changes';

-- ============================================================
-- TABLE: coupon_usages
-- ============================================================
CREATE TABLE IF NOT EXISTS coupon_usages (
    id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    coupon_id BIGINT UNSIGNED NOT NULL,
    user_id   BIGINT UNSIGNED NOT NULL,
    order_id  BIGINT UNSIGNED NOT NULL,
    used_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_coupon_usages_coupon_id (coupon_id),
    INDEX idx_coupon_usages_user_id   (user_id),
    INDEX idx_coupon_usages_order_id  (order_id),
    CONSTRAINT fk_coupon_usages_coupon_id
        FOREIGN KEY (coupon_id) REFERENCES coupons  (id) ON DELETE CASCADE  ON UPDATE CASCADE,
    CONSTRAINT fk_coupon_usages_user_id
        FOREIGN KEY (user_id)   REFERENCES users    (id) ON DELETE CASCADE  ON UPDATE CASCADE,
    CONSTRAINT fk_coupon_usages_order_id
        FOREIGN KEY (order_id)  REFERENCES orders   (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracks coupon redemptions per user per order';

-- ============================================================
-- TABLE: wishlists
-- ============================================================
CREATE TABLE IF NOT EXISTS wishlists (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_wishlists_user_product (user_id, product_id),
    INDEX idx_wishlists_user_id    (user_id),
    INDEX idx_wishlists_product_id (product_id),
    CONSTRAINT fk_wishlists_user_id
        FOREIGN KEY (user_id)    REFERENCES users    (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_wishlists_product_id
        FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User product wishlists';

-- ============================================================
-- TABLE: notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED NOT NULL,
    type       VARCHAR(100)    NOT NULL,
    title      VARCHAR(500)    NOT NULL,
    body       TEXT            NOT NULL,
    data_json  JSON            NULL DEFAULT NULL,
    is_read    TINYINT(1)      NOT NULL DEFAULT 0,
    read_at    TIMESTAMP       NULL DEFAULT NULL,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_notifications_user_id    (user_id),
    INDEX idx_notifications_is_read    (is_read),
    INDEX idx_notifications_type       (type),
    INDEX idx_notifications_created_at (created_at),
    INDEX idx_notifications_user_unread(user_id, is_read, created_at),
    CONSTRAINT fk_notifications_user_id
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='In-app notifications per user';

-- ============================================================
-- TABLE: push_tokens
-- ============================================================
CREATE TABLE IF NOT EXISTS push_tokens (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED NOT NULL,
    token      VARCHAR(512)    NOT NULL,
    platform   ENUM('android','ios','web') NOT NULL DEFAULT 'android',
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_push_tokens_token (token),
    INDEX idx_push_tokens_user_id (user_id),
    CONSTRAINT fk_push_tokens_user_id
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Device push notification tokens';

-- ============================================================
-- TABLE: failed_login_attempts
-- ============================================================
CREATE TABLE IF NOT EXISTS failed_login_attempts (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email        VARCHAR(255)    NOT NULL,
    ip_address   VARCHAR(45)     NOT NULL,
    attempted_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_failed_login_email     (email),
    INDEX idx_failed_login_ip        (ip_address),
    INDEX idx_failed_login_attempted (attempted_at),
    INDEX idx_failed_login_email_time(email, attempted_at),
    INDEX idx_failed_login_ip_time   (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracks failed auth attempts for brute-force protection';

-- ============================================================
-- TABLE: password_resets
-- ============================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email      VARCHAR(255)    NOT NULL,
    token      VARCHAR(512)    NOT NULL,
    expires_at TIMESTAMP       NOT NULL,
    used_at    TIMESTAMP       NULL DEFAULT NULL,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_password_resets_token  (token),
    INDEX idx_password_resets_email      (email),
    INDEX idx_password_resets_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='One-time password reset tokens';

-- ============================================================
-- TABLE: audit_logs
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NULL DEFAULT NULL,
    action      VARCHAR(100)    NOT NULL,
    entity_type VARCHAR(100)    NULL DEFAULT NULL,
    entity_id   BIGINT UNSIGNED NULL DEFAULT NULL,
    ip_address  VARCHAR(45)     NULL DEFAULT NULL,
    user_agent  VARCHAR(500)    NULL DEFAULT NULL,
    meta_json   JSON            NULL DEFAULT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_audit_logs_user_id    (user_id),
    INDEX idx_audit_logs_action     (action),
    INDEX idx_audit_logs_entity     (entity_type, entity_id),
    INDEX idx_audit_logs_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Security and activity audit trail';

-- ============================================================
-- TABLE: app_settings
-- ============================================================
CREATE TABLE IF NOT EXISTS app_settings (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(255)    NOT NULL,
    value       TEXT            NULL DEFAULT NULL,
    type        ENUM('string','integer','boolean','json') NOT NULL DEFAULT 'string',
    description VARCHAR(500)    NULL DEFAULT NULL,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_app_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Application-wide configuration store';

-- ============================================================
-- TABLE: schema_migrations
-- ============================================================
CREATE TABLE IF NOT EXISTS schema_migrations (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    version     VARCHAR(100)    NOT NULL,
    description VARCHAR(500)    NULL DEFAULT NULL,
    executed_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_schema_migrations_version (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracks applied database migrations';

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- TRIGGERS
-- ============================================================

DELIMITER $$

-- Auto-create cart on user registration
CREATE TRIGGER trg_after_insert_user
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO carts (user_id) VALUES (NEW.id);
END$$

-- Prevent duplicate review before insert
CREATE TRIGGER trg_before_insert_review
BEFORE INSERT ON product_reviews
FOR EACH ROW
BEGIN
    IF (SELECT COUNT(*) FROM product_reviews
        WHERE user_id = NEW.user_id AND product_id = NEW.product_id) > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'User already reviewed this product.';
    END IF;
END$$

-- Recalculate average_rating and total_reviews after INSERT review
CREATE TRIGGER trg_after_insert_review
AFTER INSERT ON product_reviews
FOR EACH ROW
BEGIN
    UPDATE products
    SET average_rating = (SELECT ROUND(AVG(rating),2) FROM product_reviews WHERE product_id = NEW.product_id),
        total_reviews  = (SELECT COUNT(*)              FROM product_reviews WHERE product_id = NEW.product_id)
    WHERE id = NEW.product_id;
END$$

-- Recalculate after UPDATE review
CREATE TRIGGER trg_after_update_review
AFTER UPDATE ON product_reviews
FOR EACH ROW
BEGIN
    UPDATE products
    SET average_rating = (SELECT ROUND(AVG(rating),2) FROM product_reviews WHERE product_id = NEW.product_id),
        total_reviews  = (SELECT COUNT(*)              FROM product_reviews WHERE product_id = NEW.product_id)
    WHERE id = NEW.product_id;
END$$

-- Recalculate after DELETE review
CREATE TRIGGER trg_after_delete_review
AFTER DELETE ON product_reviews
FOR EACH ROW
BEGIN
    UPDATE products
    SET average_rating = COALESCE((SELECT ROUND(AVG(rating),2) FROM product_reviews WHERE product_id = OLD.product_id), 0.00),
        total_reviews  = (SELECT COUNT(*) FROM product_reviews WHERE product_id = OLD.product_id)
    WHERE id = OLD.product_id;
END$$

-- Increment total_sales after order item inserted
CREATE TRIGGER trg_after_insert_order_item
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    IF NEW.product_id IS NOT NULL THEN
        UPDATE products SET total_sales = total_sales + NEW.quantity WHERE id = NEW.product_id;
    END IF;
END$$

-- Auto-insert first status history on order creation
CREATE TRIGGER trg_after_insert_order
AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    INSERT INTO order_status_history (order_id, status, note) VALUES (NEW.id, NEW.status, 'Order placed');
END$$

-- Log status change to history when order status updates
CREATE TRIGGER trg_after_update_order_status
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF OLD.status <> NEW.status THEN
        INSERT INTO order_status_history (order_id, status, note) VALUES (NEW.id, NEW.status, NULL);
        INSERT INTO audit_logs (user_id, action, entity_type, entity_id, meta_json)
        VALUES (NEW.user_id, 'order_status_changed', 'orders', NEW.id,
                JSON_OBJECT('old_status', OLD.status, 'new_status', NEW.status));
    END IF;
END$$

-- Prevent stock going negative
CREATE TRIGGER trg_before_update_product_stock
BEFORE UPDATE ON products
FOR EACH ROW
BEGIN
    IF NEW.stock_quantity < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stock quantity cannot be negative.';
    END IF;
END$$

-- Increment coupon usage_count on redemption
CREATE TRIGGER trg_after_insert_coupon_usage
AFTER INSERT ON coupon_usages
FOR EACH ROW
BEGIN
    UPDATE coupons SET usage_count = usage_count + 1 WHERE id = NEW.coupon_id;
END$$

DELIMITER ;

-- ============================================================
-- VIEWS
-- ============================================================

CREATE OR REPLACE VIEW view_active_products AS
SELECT p.id, p.name, p.slug, p.sku, p.price, p.has_offer, p.offer_price,
       p.stock_quantity, p.main_image, p.average_rating, p.total_reviews,
       p.total_sales, p.created_at,
       c.id AS category_id, c.name AS category_name,
       b.id AS brand_id,    b.name AS brand_name
FROM products p
LEFT JOIN categories c ON c.id = p.category_id
LEFT JOIN brands     b ON b.id = p.brand_id
WHERE p.is_active = 1 AND p.deleted_at IS NULL;

CREATE OR REPLACE VIEW view_deals AS
SELECT p.id, p.name, p.slug, p.price, p.offer_price, p.main_image,
       p.average_rating, p.total_sales,
       c.name AS category_name, b.name AS brand_name
FROM products p
LEFT JOIN categories c ON c.id = p.category_id
LEFT JOIN brands     b ON b.id = p.brand_id
WHERE p.is_active = 1 AND p.has_offer = 1 AND p.offer_price IS NOT NULL AND p.deleted_at IS NULL;

CREATE OR REPLACE VIEW view_popular_products AS
SELECT p.id, p.name, p.slug, p.price, p.has_offer, p.offer_price,
       p.stock_quantity, p.main_image, p.average_rating, p.total_sales,
       c.name AS category_name, b.name AS brand_name
FROM products p
LEFT JOIN categories c ON c.id = p.category_id
LEFT JOIN brands     b ON b.id = p.brand_id
WHERE p.is_active = 1 AND p.deleted_at IS NULL
ORDER BY p.total_sales DESC;

CREATE OR REPLACE VIEW view_popular_categories AS
SELECT c.id, c.name, c.icon, c.image,
       COALESCE(SUM(p.total_sales), 0) AS total_category_sales
FROM categories c
LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1 AND p.deleted_at IS NULL
WHERE c.is_active = 1
GROUP BY c.id, c.name, c.icon, c.image
ORDER BY total_category_sales DESC;

CREATE OR REPLACE VIEW view_popular_brands AS
SELECT b.id, b.name, b.icon, b.image,
       COALESCE(SUM(p.total_sales), 0) AS total_brand_sales
FROM brands b
LEFT JOIN products p ON p.brand_id = b.id AND p.is_active = 1 AND p.deleted_at IS NULL
WHERE b.is_active = 1
GROUP BY b.id, b.name, b.icon, b.image
ORDER BY total_brand_sales DESC;

CREATE OR REPLACE VIEW view_cart_summary AS
SELECT c.id AS cart_id, c.user_id,
       COUNT(ci.id) AS total_items,
       COALESCE(SUM(
           CASE WHEN p.has_offer = 1 AND p.offer_price IS NOT NULL
                THEN p.offer_price * ci.quantity
                ELSE p.price      * ci.quantity END
       ), 0.00) AS subtotal
FROM carts c
LEFT JOIN cart_items ci ON ci.cart_id = c.id
LEFT JOIN products   p  ON p.id       = ci.product_id
GROUP BY c.id, c.user_id;

CREATE OR REPLACE VIEW view_orders AS
SELECT o.id, o.user_id, u.fullname AS user_name, u.email AS user_email,
       o.status, o.subtotal, o.delivery_fee, o.discount_amount, o.total_price,
       o.payment_name, o.delivery_name, o.coupon_code, o.created_at
FROM orders o
JOIN users u ON u.id = o.user_id;

CREATE OR REPLACE VIEW view_user_wishlist AS
SELECT w.user_id, w.created_at AS wishlisted_at,
       p.id AS product_id, p.name AS product_name, p.slug, p.price,
       p.has_offer, p.offer_price, p.main_image, p.stock_quantity, p.average_rating,
       c.name AS category_name, b.name AS brand_name
FROM wishlists w
JOIN products   p ON p.id = w.product_id AND p.is_active = 1 AND p.deleted_at IS NULL
LEFT JOIN categories c ON c.id = p.category_id
LEFT JOIN brands     b ON b.id = p.brand_id;

CREATE OR REPLACE VIEW view_unread_notification_counts AS
SELECT user_id, COUNT(*) AS unread_count
FROM notifications
WHERE is_read = 0
GROUP BY user_id;

CREATE OR REPLACE VIEW view_active_coupons AS
SELECT id, code, description, discount_type, discount_value,
       min_order_amount, max_discount_cap, usage_limit, usage_count,
       per_user_limit, starts_at, expires_at
FROM coupons
WHERE is_active = 1
  AND (starts_at  IS NULL OR starts_at  <= NOW())
  AND (expires_at IS NULL OR expires_at >= NOW())
  AND (usage_limit IS NULL OR usage_count < usage_limit);

CREATE OR REPLACE VIEW view_valid_tokens AS
SELECT t.token, t.user_id, t.device_name, t.ip_address,
       t.last_used_at, t.expires_at, u.fullname, u.email
FROM user_tokens t
JOIN users u ON u.id = t.user_id AND u.deleted_at IS NULL
WHERE (t.expires_at IS NULL OR t.expires_at > NOW());

-- ============================================================
-- STORED PROCEDURES
-- ============================================================

DELIMITER $$

-- Auth: fetch user row for login (PHP verifies hash)
CREATE PROCEDURE sp_get_user_for_login(IN p_email VARCHAR(255))
BEGIN
    SELECT id, fullname, email, password, gender, birthdate, profile_image, created_at
    FROM users WHERE email = p_email AND deleted_at IS NULL LIMIT 1;
END$$

-- Auth: create bearer token after successful login
CREATE PROCEDURE sp_create_token(
    IN p_user_id     BIGINT UNSIGNED,
    IN p_token       VARCHAR(512),
    IN p_device_name VARCHAR(255),
    IN p_ip_address  VARCHAR(45),
    IN p_expiry_days INT UNSIGNED
)
BEGIN
    INSERT INTO user_tokens (user_id, token, device_name, ip_address, expires_at, last_used_at)
    VALUES (p_user_id, p_token, p_device_name, p_ip_address,
            DATE_ADD(NOW(), INTERVAL p_expiry_days DAY), NOW());
END$$

-- Auth: verify token, touch last_used_at, return user
CREATE PROCEDURE sp_verify_token(IN p_token VARCHAR(512))
BEGIN
    UPDATE user_tokens SET last_used_at = NOW()
    WHERE token = p_token AND (expires_at IS NULL OR expires_at > NOW());

    SELECT u.id, u.fullname, u.email, u.gender, u.birthdate, u.profile_image, u.created_at
    FROM user_tokens t
    JOIN users u ON u.id = t.user_id
    WHERE t.token = p_token AND u.deleted_at IS NULL
      AND (t.expires_at IS NULL OR t.expires_at > NOW())
    LIMIT 1;
END$$

-- Auth: logout (invalidate token)
CREATE PROCEDURE sp_logout(IN p_token VARCHAR(512), OUT p_success TINYINT)
BEGIN
    DELETE FROM user_tokens WHERE token = p_token;
    SET p_success = ROW_COUNT() > 0;
END$$

-- Auth: register new user
CREATE PROCEDURE sp_register(
    IN  p_fullname  VARCHAR(255),
    IN  p_email     VARCHAR(255),
    IN  p_password  VARCHAR(255),
    IN  p_gender    ENUM('male','female','other'),
    IN  p_birthdate DATE,
    OUT p_user_id   BIGINT UNSIGNED,
    OUT p_success   TINYINT,
    OUT p_message   VARCHAR(255)
)
sp_register: BEGIN
    SET p_success = 0; SET p_user_id = NULL;
    IF (SELECT COUNT(*) FROM users WHERE email = p_email AND deleted_at IS NULL) > 0 THEN
        SET p_message = 'Email already registered.';
        LEAVE sp_register;
    END IF;
    INSERT INTO users (fullname, email, password, gender, birthdate)
    VALUES (p_fullname, p_email, p_password, p_gender, p_birthdate);
    SET p_user_id = LAST_INSERT_ID();
    SET p_success = 1;
    SET p_message = 'Registration successful.';
END$$

-- Auth: rate limit check
CREATE PROCEDURE sp_check_rate_limit(
    IN  p_email      VARCHAR(255),
    IN  p_ip_address VARCHAR(45),
    OUT p_blocked    TINYINT,
    OUT p_message    VARCHAR(255)
)
BEGIN
    DECLARE v_max  INT DEFAULT 5;
    DECLARE v_win  INT DEFAULT 15;
    DECLARE v_cnt  INT DEFAULT 0;
    SET p_blocked = 0;
    SELECT CAST(value AS UNSIGNED) INTO v_max FROM app_settings WHERE setting_key = 'max_failed_login_attempts' LIMIT 1;
    SELECT CAST(value AS UNSIGNED) INTO v_win FROM app_settings WHERE setting_key = 'login_lockout_minutes'      LIMIT 1;
    SELECT COUNT(*) INTO v_cnt FROM failed_login_attempts
    WHERE (email = p_email OR ip_address = p_ip_address)
      AND attempted_at > DATE_SUB(NOW(), INTERVAL v_win MINUTE);
    IF v_cnt >= v_max THEN
        SET p_blocked = 1;
        SET p_message = CONCAT('Too many failed attempts. Try again in ', v_win, ' minutes.');
    ELSE
        SET p_message = 'OK';
    END IF;
END$$

-- Auth: record failed login
CREATE PROCEDURE sp_record_failed_login(IN p_email VARCHAR(255), IN p_ip_address VARCHAR(45))
BEGIN
    INSERT INTO failed_login_attempts (email, ip_address) VALUES (p_email, p_ip_address);
END$$

-- Password reset: create token
CREATE PROCEDURE sp_create_password_reset(
    IN  p_email   VARCHAR(255),
    IN  p_token   VARCHAR(512),
    OUT p_success TINYINT,
    OUT p_message VARCHAR(255)
)
sp_create_password_reset: BEGIN
    DECLARE v_expiry INT DEFAULT 60;
    SET p_success = 1;
    SET p_message = 'If that email is registered you will receive a reset link.';
    IF (SELECT COUNT(*) FROM users WHERE email = p_email AND deleted_at IS NULL) = 0 THEN
        LEAVE sp_create_password_reset;
    END IF;
    SELECT CAST(value AS UNSIGNED) INTO v_expiry FROM app_settings WHERE setting_key = 'password_reset_expiry_min' LIMIT 1;
    DELETE FROM password_resets WHERE email = p_email;
    INSERT INTO password_resets (email, token, expires_at)
    VALUES (p_email, p_token, DATE_ADD(NOW(), INTERVAL v_expiry MINUTE));
END$$

-- Password reset: verify and apply
CREATE PROCEDURE sp_verify_password_reset(
    IN  p_token        VARCHAR(512),
    IN  p_new_password VARCHAR(255),
    OUT p_success      TINYINT,
    OUT p_message      VARCHAR(255)
)
sp_verify_password_reset: BEGIN
    DECLARE v_email   VARCHAR(255);
    DECLARE v_used_at TIMESTAMP;
    DECLARE v_expires TIMESTAMP;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; SET p_success=0; SET p_message='Reset failed.'; END;
    SET p_success = 0;
    SELECT email, used_at, expires_at INTO v_email, v_used_at, v_expires
    FROM password_resets WHERE token = p_token LIMIT 1;
    IF v_email IS NULL    THEN SET p_message = 'Invalid reset token.'; LEAVE sp_verify_password_reset; END IF;
    IF v_used_at IS NOT NULL THEN SET p_message = 'Token already used.'; LEAVE sp_verify_password_reset; END IF;
    IF NOW() > v_expires  THEN SET p_message = 'Token has expired.';   LEAVE sp_verify_password_reset; END IF;
    START TRANSACTION;
    UPDATE users SET password = p_new_password WHERE email = v_email AND deleted_at IS NULL;
    UPDATE password_resets SET used_at = NOW() WHERE token = p_token;
    DELETE FROM user_tokens WHERE user_id = (SELECT id FROM users WHERE email = v_email LIMIT 1);
    COMMIT;
    SET p_success = 1; SET p_message = 'Password reset successful.';
END$$

-- Profile: get
CREATE PROCEDURE sp_get_profile(IN p_user_id BIGINT UNSIGNED)
BEGIN
    SELECT id, fullname, email, gender, birthdate, profile_image, created_at
    FROM users WHERE id = p_user_id AND deleted_at IS NULL LIMIT 1;
END$$

-- Profile: update
CREATE PROCEDURE sp_update_profile(
    IN  p_user_id       BIGINT UNSIGNED,
    IN  p_fullname      VARCHAR(255),
    IN  p_gender        ENUM('male','female','other'),
    IN  p_birthdate     DATE,
    IN  p_profile_image VARCHAR(500),
    OUT p_success       TINYINT,
    OUT p_message       VARCHAR(255)
)
sp_update_profile: BEGIN
    SET p_success = 0;
    IF (SELECT COUNT(*) FROM users WHERE id = p_user_id AND deleted_at IS NULL) = 0 THEN
        SET p_message = 'User not found.'; LEAVE sp_update_profile;
    END IF;
    UPDATE users
    SET fullname = COALESCE(p_fullname, fullname), gender = COALESCE(p_gender, gender),
        birthdate = COALESCE(p_birthdate, birthdate), profile_image = COALESCE(p_profile_image, profile_image)
    WHERE id = p_user_id AND deleted_at IS NULL;
    SET p_success = 1; SET p_message = 'Profile updated.';
END$$

-- Profile: change password (invalidates all tokens)
CREATE PROCEDURE sp_change_password(
    IN  p_user_id      BIGINT UNSIGNED,
    IN  p_new_password VARCHAR(255),
    OUT p_success      TINYINT,
    OUT p_message      VARCHAR(255)
)
sp_change_password: BEGIN
    SET p_success = 0;
    IF (SELECT COUNT(*) FROM users WHERE id = p_user_id AND deleted_at IS NULL) = 0 THEN
        SET p_message = 'User not found.'; LEAVE sp_change_password;
    END IF;
    UPDATE users SET password = p_new_password WHERE id = p_user_id;
    DELETE FROM user_tokens WHERE user_id = p_user_id;
    SET p_success = 1; SET p_message = 'Password changed. All sessions invalidated.';
END$$

-- Profile: soft-delete and anonymize account
CREATE PROCEDURE sp_delete_account(
    IN  p_user_id BIGINT UNSIGNED,
    OUT p_success TINYINT,
    OUT p_message VARCHAR(255)
)
sp_delete_account: BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; SET p_success=0; SET p_message='Account deletion failed.'; END;
    SET p_success = 0;
    IF (SELECT COUNT(*) FROM users WHERE id = p_user_id AND deleted_at IS NULL) = 0 THEN
        SET p_message = 'User not found.'; LEAVE sp_delete_account;
    END IF;
    START TRANSACTION;
    UPDATE users
    SET deleted_at = NOW(),
        email = CONCAT('deleted_', p_user_id, '_', UNIX_TIMESTAMP(), '@removed.invalid'),
        fullname = 'Deleted User', profile_image = NULL, password = 'REDACTED'
    WHERE id = p_user_id;
    DELETE FROM user_tokens  WHERE user_id = p_user_id;
    DELETE FROM push_tokens  WHERE user_id = p_user_id;
    COMMIT;
    SET p_success = 1; SET p_message = 'Account deleted and data anonymized.';
END$$

-- Home screen data
CREATE PROCEDURE sp_get_home(IN p_user_id BIGINT UNSIGNED)
BEGIN
    SELECT id, title, image, redirect_type, redirect_value
    FROM advertisements WHERE is_active = 1 ORDER BY sort_order ASC;

    SELECT p.id, p.name, p.slug, p.price, p.has_offer, p.offer_price,
           p.stock_quantity, p.main_image, p.average_rating
    FROM products p WHERE p.is_active = 1 AND p.deleted_at IS NULL
    ORDER BY p.total_sales DESC LIMIT 10;

    SELECT c.id, c.name, c.icon FROM categories c WHERE c.is_active = 1
    ORDER BY (SELECT COALESCE(SUM(p2.total_sales),0) FROM products p2
              WHERE p2.category_id = c.id AND p2.is_active = 1 AND p2.deleted_at IS NULL) DESC LIMIT 8;

    SELECT b.id, b.name, b.icon FROM brands b WHERE b.is_active = 1
    ORDER BY (SELECT COALESCE(SUM(p3.total_sales),0) FROM products p3
              WHERE p3.brand_id = b.id AND p3.is_active = 1 AND p3.deleted_at IS NULL) DESC LIMIT 8;

    IF p_user_id IS NOT NULL THEN
        SELECT DISTINCT p.id, p.name, p.slug, p.price, p.has_offer,
               p.offer_price, p.main_image, p.average_rating
        FROM order_items oi
        JOIN orders   o ON o.id  = oi.order_id  AND o.user_id = p_user_id
        JOIN products p ON p.id  = oi.product_id AND p.is_active = 1 AND p.deleted_at IS NULL
        ORDER BY o.created_at DESC LIMIT 10;
    END IF;
END$$

-- Products: paginated listing with filters
CREATE PROCEDURE sp_get_products(
    IN p_category_id BIGINT UNSIGNED,
    IN p_brand_id    BIGINT UNSIGNED,
    IN p_has_offer   TINYINT,
    IN p_search      VARCHAR(300),
    IN p_sort_by     VARCHAR(50),
    IN p_page        INT UNSIGNED,
    IN p_per_page    INT UNSIGNED
)
BEGIN
    DECLARE v_offset INT UNSIGNED DEFAULT 0;
    SET v_offset = (p_page - 1) * p_per_page;
    SELECT p.id, p.name, p.slug, p.price, p.has_offer, p.offer_price,
           p.stock_quantity, p.main_image, p.average_rating, p.total_reviews, p.total_sales,
           c.name AS category_name, b.name AS brand_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN brands     b ON b.id = p.brand_id
    WHERE p.is_active = 1 AND p.deleted_at IS NULL
      AND (p_category_id IS NULL OR p.category_id = p_category_id)
      AND (p_brand_id    IS NULL OR p.brand_id    = p_brand_id)
      AND (p_has_offer   IS NULL OR p.has_offer   = p_has_offer)
      AND (p_search IS NULL OR MATCH(p.name, p.description) AGAINST(p_search IN BOOLEAN MODE))
    ORDER BY
        CASE p_sort_by WHEN 'price_asc' THEN p.price END ASC,
        CASE p_sort_by WHEN 'price_desc' THEN p.price
                       WHEN 'rating'     THEN p.average_rating
                       WHEN 'popular'    THEN p.total_sales
                       WHEN 'newest'     THEN UNIX_TIMESTAMP(p.created_at)
        END DESC,
        p.id DESC
    LIMIT p_per_page OFFSET v_offset;
END$$

-- Products: get by slug (with images + options)
CREATE PROCEDURE sp_get_product_by_slug(IN p_slug VARCHAR(600))
BEGIN
    SELECT p.*, c.name AS category_name, c.icon AS category_icon,
                b.name AS brand_name,    b.icon AS brand_icon
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN brands     b ON b.id = p.brand_id
    WHERE p.slug = p_slug AND p.is_active = 1 AND p.deleted_at IS NULL LIMIT 1;

    SELECT image_url, sort_order FROM product_images
    WHERE product_id = (SELECT id FROM products WHERE slug = p_slug AND deleted_at IS NULL LIMIT 1)
    ORDER BY sort_order ASC;

    SELECT po.id AS option_id, po.name AS option_name,
           pov.id AS value_id, pov.value, pov.price_modifier
    FROM product_options po
    JOIN product_option_values pov ON pov.product_option_id = po.id
    WHERE po.product_id = (SELECT id FROM products WHERE slug = p_slug AND deleted_at IS NULL LIMIT 1)
    ORDER BY po.sort_order ASC, pov.sort_order ASC;
END$$

-- Products: related products
CREATE PROCEDURE sp_get_related_products(IN p_product_id BIGINT UNSIGNED, IN p_limit INT UNSIGNED)
BEGIN
    DECLARE v_cat BIGINT UNSIGNED; DECLARE v_brand BIGINT UNSIGNED;
    SELECT category_id, brand_id INTO v_cat, v_brand
    FROM products WHERE id = p_product_id AND deleted_at IS NULL LIMIT 1;
    SELECT p.id, p.name, p.slug, p.price, p.has_offer, p.offer_price,
           p.main_image, p.average_rating, p.stock_quantity,
           (CASE WHEN p.category_id = v_cat   THEN 2 ELSE 0 END +
            CASE WHEN p.brand_id    = v_brand THEN 1 ELSE 0 END) AS relevance
    FROM products p
    WHERE p.id != p_product_id AND p.is_active = 1 AND p.deleted_at IS NULL
      AND (p.category_id = v_cat OR p.brand_id = v_brand)
    ORDER BY relevance DESC, p.total_sales DESC
    LIMIT p_limit;
END$$

-- Products: search
CREATE PROCEDURE sp_search_products(IN p_query VARCHAR(300), IN p_page INT UNSIGNED, IN p_per_page INT UNSIGNED)
BEGIN
    DECLARE v_offset INT UNSIGNED DEFAULT 0;
    SET v_offset = (p_page - 1) * p_per_page;
    SELECT p.id, p.name, p.slug, p.price, p.has_offer, p.offer_price,
           p.stock_quantity, p.main_image, p.average_rating, p.total_reviews,
           c.name AS category_name, b.name AS brand_name,
           MATCH(p.name, p.description) AGAINST(p_query IN BOOLEAN MODE) AS relevance_score
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN brands     b ON b.id = p.brand_id
    WHERE p.is_active = 1 AND p.deleted_at IS NULL
      AND MATCH(p.name, p.description) AGAINST(p_query IN BOOLEAN MODE)
    ORDER BY relevance_score DESC, p.total_sales DESC
    LIMIT p_per_page OFFSET v_offset;
END$$

-- Products: reviews (paginated with stats)
CREATE PROCEDURE sp_get_product_reviews(
    IN p_product_id BIGINT UNSIGNED, IN p_page INT UNSIGNED, IN p_per_page INT UNSIGNED)
BEGIN
    DECLARE v_offset INT UNSIGNED DEFAULT 0;
    SET v_offset = (p_page - 1) * p_per_page;
    SELECT COUNT(*) AS total_reviews, ROUND(AVG(rating),2) AS average_rating,
           SUM(rating=5) AS five_star, SUM(rating=4) AS four_star,
           SUM(rating=3) AS three_star, SUM(rating=2) AS two_star, SUM(rating=1) AS one_star
    FROM product_reviews WHERE product_id = p_product_id;
    SELECT pr.id, pr.rating, pr.comment, pr.created_at,
           u.id AS user_id, u.fullname AS user_name, u.profile_image
    FROM product_reviews pr JOIN users u ON u.id = pr.user_id
    WHERE pr.product_id = p_product_id
    ORDER BY pr.created_at DESC LIMIT p_per_page OFFSET v_offset;
END$$

-- Products: add review (purchase-verified)
CREATE PROCEDURE sp_add_review(
    IN  p_user_id    BIGINT UNSIGNED,
    IN  p_product_id BIGINT UNSIGNED,
    IN  p_rating     TINYINT UNSIGNED,
    IN  p_comment    TEXT,
    OUT p_success    TINYINT,
    OUT p_message    VARCHAR(255)
)
sp_add_review: BEGIN
    SET p_success = 0;
    IF p_rating < 1 OR p_rating > 5 THEN SET p_message='Rating must be 1-5.'; LEAVE sp_add_review; END IF;
    IF (SELECT COUNT(*) FROM products WHERE id=p_product_id AND is_active=1 AND deleted_at IS NULL)=0 THEN
        SET p_message='Product not found.'; LEAVE sp_add_review; END IF;
    IF (SELECT COUNT(*) FROM product_reviews WHERE user_id=p_user_id AND product_id=p_product_id)>0 THEN
        SET p_message='You have already reviewed this product.'; LEAVE sp_add_review; END IF;
    IF (SELECT COUNT(*) FROM order_items oi JOIN orders o ON o.id=oi.order_id
        WHERE o.user_id=p_user_id AND oi.product_id=p_product_id AND o.status='finished')=0 THEN
        SET p_message='You can only review products you have purchased.'; LEAVE sp_add_review; END IF;
    INSERT INTO product_reviews (user_id, product_id, rating, comment) VALUES (p_user_id, p_product_id, p_rating, p_comment);
    SET p_success=1; SET p_message='Review submitted.';
END$$

-- Shortcut procedures for listings
CREATE PROCEDURE sp_get_category_products(IN p_category_id BIGINT UNSIGNED, IN p_sort_by VARCHAR(50), IN p_page INT UNSIGNED, IN p_per_page INT UNSIGNED)
BEGIN CALL sp_get_products(p_category_id, NULL, NULL, NULL, p_sort_by, p_page, p_per_page); END$$

CREATE PROCEDURE sp_get_brand_products(IN p_brand_id BIGINT UNSIGNED, IN p_sort_by VARCHAR(50), IN p_page INT UNSIGNED, IN p_per_page INT UNSIGNED)
BEGIN CALL sp_get_products(NULL, p_brand_id, NULL, NULL, p_sort_by, p_page, p_per_page); END$$

CREATE PROCEDURE sp_get_deals(IN p_sort_by VARCHAR(50), IN p_page INT UNSIGNED, IN p_per_page INT UNSIGNED)
BEGIN CALL sp_get_products(NULL, NULL, 1, NULL, p_sort_by, p_page, p_per_page); END$$

-- Cart: get with totals
CREATE PROCEDURE sp_get_cart(IN p_user_id BIGINT UNSIGNED)
BEGIN
    DECLARE v_cart_id BIGINT UNSIGNED;
    SELECT id INTO v_cart_id FROM carts WHERE user_id = p_user_id LIMIT 1;
    IF v_cart_id IS NULL THEN
        SELECT NULL AS cart_id, 0 AS total_items, 0.00 AS subtotal WHERE 1=0;
    ELSE
        SELECT v_cart_id AS cart_id, p_user_id AS user_id,
               COUNT(ci.id) AS total_items,
               COALESCE(SUM(CASE WHEN p.has_offer=1 AND p.offer_price IS NOT NULL
                   THEN p.offer_price * ci.quantity ELSE p.price * ci.quantity END), 0.00) AS subtotal
        FROM cart_items ci JOIN products p ON p.id = ci.product_id
        WHERE ci.cart_id = v_cart_id;
        SELECT ci.id AS cart_item_id, ci.quantity, ci.selected_options_json,
               p.id AS product_id, p.name AS product_name, p.slug, p.price AS unit_price,
               p.has_offer, p.offer_price, p.main_image, p.stock_quantity,
               CASE WHEN p.has_offer=1 AND p.offer_price IS NOT NULL
                    THEN p.offer_price * ci.quantity ELSE p.price * ci.quantity END AS line_total
        FROM cart_items ci JOIN products p ON p.id = ci.product_id
        WHERE ci.cart_id = v_cart_id;
    END IF;
END$$

-- Cart: add item
CREATE PROCEDURE sp_add_to_cart(
    IN  p_user_id          BIGINT UNSIGNED,
    IN  p_product_id       BIGINT UNSIGNED,
    IN  p_quantity         INT UNSIGNED,
    IN  p_selected_options JSON,
    OUT p_success          TINYINT,
    OUT p_message          VARCHAR(255)
)
sp_add_to_cart: BEGIN
    DECLARE v_cart_id      BIGINT UNSIGNED;
    DECLARE v_stock        INT UNSIGNED DEFAULT 0;
    DECLARE v_existing_qty INT UNSIGNED DEFAULT 0;
    DECLARE v_item_id      BIGINT UNSIGNED;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; SET p_success=0; SET p_message='Failed to update cart.'; END;
    SET p_success = 0;
    SELECT stock_quantity INTO v_stock FROM products
    WHERE id = p_product_id AND is_active = 1 AND deleted_at IS NULL LIMIT 1;
    IF v_stock IS NULL THEN SET p_message='Product not found or inactive.'; LEAVE sp_add_to_cart; END IF;
    SELECT id INTO v_cart_id FROM carts WHERE user_id = p_user_id LIMIT 1;
    IF v_cart_id IS NULL THEN INSERT INTO carts (user_id) VALUES (p_user_id); SET v_cart_id = LAST_INSERT_ID(); END IF;
    SELECT id, quantity INTO v_item_id, v_existing_qty FROM cart_items
    WHERE cart_id = v_cart_id AND product_id = p_product_id LIMIT 1;
    IF (COALESCE(v_existing_qty,0) + p_quantity) > v_stock THEN
        SET p_message='Requested quantity exceeds available stock.'; LEAVE sp_add_to_cart; END IF;
    IF v_item_id IS NOT NULL THEN
        UPDATE cart_items SET quantity = quantity + p_quantity, selected_options_json = p_selected_options WHERE id = v_item_id;
    ELSE
        INSERT INTO cart_items (cart_id, product_id, quantity, selected_options_json) VALUES (v_cart_id, p_product_id, p_quantity, p_selected_options);
    END IF;
    SET p_success=1; SET p_message='Item added to cart.';
END$$

-- Cart: update item quantity
CREATE PROCEDURE sp_update_cart_item(
    IN  p_cart_item_id BIGINT UNSIGNED,
    IN  p_user_id      BIGINT UNSIGNED,
    IN  p_quantity     INT UNSIGNED,
    OUT p_success      TINYINT,
    OUT p_message      VARCHAR(255)
)
sp_update_cart_item: BEGIN
    DECLARE v_product_id BIGINT UNSIGNED; DECLARE v_stock INT UNSIGNED; DECLARE v_cart_id BIGINT UNSIGNED;
    SET p_success = 0;
    SELECT ci.cart_id, ci.product_id INTO v_cart_id, v_product_id
    FROM cart_items ci JOIN carts c ON c.id = ci.cart_id AND c.user_id = p_user_id
    WHERE ci.id = p_cart_item_id LIMIT 1;
    IF v_cart_id IS NULL THEN SET p_message='Cart item not found.'; LEAVE sp_update_cart_item; END IF;
    SELECT stock_quantity INTO v_stock FROM products WHERE id = v_product_id AND is_active = 1 LIMIT 1;
    IF p_quantity > v_stock THEN SET p_message='Requested quantity exceeds available stock.'; LEAVE sp_update_cart_item; END IF;
    IF p_quantity = 0 THEN DELETE FROM cart_items WHERE id = p_cart_item_id; SET p_message='Item removed.';
    ELSE UPDATE cart_items SET quantity = p_quantity WHERE id = p_cart_item_id; SET p_message='Cart item updated.'; END IF;
    SET p_success = 1;
END$$

-- Cart: remove item
CREATE PROCEDURE sp_remove_cart_item(
    IN  p_cart_item_id BIGINT UNSIGNED,
    IN  p_user_id      BIGINT UNSIGNED,
    OUT p_success      TINYINT,
    OUT p_message      VARCHAR(255)
)
BEGIN
    SET p_success = 0;
    DELETE ci FROM cart_items ci
    JOIN carts c ON c.id = ci.cart_id AND c.user_id = p_user_id
    WHERE ci.id = p_cart_item_id;
    IF ROW_COUNT() = 0 THEN SET p_message='Cart item not found.';
    ELSE SET p_success=1; SET p_message='Item removed.'; END IF;
END$$

-- Cart: clear all items
CREATE PROCEDURE sp_clear_cart(IN p_user_id BIGINT UNSIGNED)
BEGIN
    DELETE ci FROM cart_items ci JOIN carts c ON c.id = ci.cart_id AND c.user_id = p_user_id;
END$$

-- Checkout: summary before placing order
CREATE PROCEDURE sp_get_checkout_summary(IN p_user_id BIGINT UNSIGNED, IN p_delivery_type_id BIGINT UNSIGNED)
BEGIN
    DECLARE v_cart_id BIGINT UNSIGNED;
    SELECT id INTO v_cart_id FROM carts WHERE user_id = p_user_id LIMIT 1;
    SELECT ci.id AS cart_item_id, ci.quantity, ci.selected_options_json,
           p.id AS product_id, p.name AS product_name, p.main_image, p.price, p.has_offer, p.offer_price,
           CASE WHEN p.has_offer=1 AND p.offer_price IS NOT NULL
                THEN p.offer_price * ci.quantity ELSE p.price * ci.quantity END AS line_total
    FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = v_cart_id;
    SELECT COALESCE(SUM(CASE WHEN p.has_offer=1 AND p.offer_price IS NOT NULL
               THEN p.offer_price * ci.quantity ELSE p.price * ci.quantity END),0.00) AS subtotal,
           COALESCE(dt.fee,0.00) AS delivery_fee,
           COALESCE(SUM(CASE WHEN p.has_offer=1 AND p.offer_price IS NOT NULL
               THEN p.offer_price * ci.quantity ELSE p.price * ci.quantity END),0.00) + COALESCE(dt.fee,0.00) AS total_price
    FROM cart_items ci JOIN products p ON p.id = ci.product_id
    LEFT JOIN delivery_types dt ON dt.id = p_delivery_type_id AND dt.is_active = 1
    WHERE ci.cart_id = v_cart_id;
    SELECT id, city_name, street_name, building_number, additional_info, latitude, longitude, is_default
    FROM addresses WHERE user_id = p_user_id AND deleted_at IS NULL ORDER BY is_default DESC LIMIT 1;
    SELECT id, name, fee, estimated_delivery_time FROM delivery_types WHERE is_active = 1 ORDER BY fee ASC;
    SELECT id, name FROM payment_types WHERE is_active = 1;
END$$

-- Orders: place order with coupon support
CREATE PROCEDURE sp_place_order(
    IN  p_user_id          BIGINT UNSIGNED,
    IN  p_address_id       BIGINT UNSIGNED,
    IN  p_delivery_type_id BIGINT UNSIGNED,
    IN  p_payment_type_id  BIGINT UNSIGNED,
    IN  p_coupon_code      VARCHAR(100),
    IN  p_notes            TEXT,
    OUT p_order_id         BIGINT UNSIGNED,
    OUT p_error            VARCHAR(500)
)
sp_place_order: BEGIN
    DECLARE v_subtotal       DECIMAL(12,2) DEFAULT 0.00;
    DECLARE v_delivery_fee   DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_discount       DECIMAL(10,2) DEFAULT 0.00;
    DECLARE v_total          DECIMAL(12,2) DEFAULT 0.00;
    DECLARE v_cart_id        BIGINT UNSIGNED;
    DECLARE v_item_count     INT DEFAULT 0;
    DECLARE v_coupon_id      BIGINT UNSIGNED DEFAULT NULL;
    DECLARE v_coupon_success TINYINT DEFAULT 0;
    DECLARE v_coupon_msg     VARCHAR(255);
    DECLARE v_addr_city      VARCHAR(255); DECLARE v_addr_street    VARCHAR(500);
    DECLARE v_addr_building  VARCHAR(100); DECLARE v_addr_info      TEXT;
    DECLARE v_addr_lat       DECIMAL(10,8); DECLARE v_addr_lng     DECIMAL(11,8);
    DECLARE v_del_name       VARCHAR(255); DECLARE v_del_time       VARCHAR(255);
    DECLARE v_pay_name       VARCHAR(255); DECLARE v_out_of_stock   VARCHAR(255) DEFAULT NULL;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; SET p_error='Transaction failed. Order not created.'; SET p_order_id=NULL; END;
    SET p_error=NULL; SET p_order_id=NULL;
    START TRANSACTION;
    SELECT id INTO v_cart_id FROM carts WHERE user_id = p_user_id LIMIT 1;
    IF v_cart_id IS NULL THEN SET p_error='Cart not found.'; ROLLBACK; LEAVE sp_place_order; END IF;
    SELECT COUNT(*) INTO v_item_count FROM cart_items WHERE cart_id = v_cart_id;
    IF v_item_count = 0 THEN SET p_error='Cart is empty.'; ROLLBACK; LEAVE sp_place_order; END IF;
    SELECT p.name INTO v_out_of_stock FROM cart_items ci JOIN products p ON p.id = ci.product_id
    WHERE ci.cart_id = v_cart_id AND ci.quantity > p.stock_quantity LIMIT 1;
    IF v_out_of_stock IS NOT NULL THEN SET p_error=CONCAT('Insufficient stock for: ',v_out_of_stock); ROLLBACK; LEAVE sp_place_order; END IF;
    SELECT city_name, street_name, building_number, additional_info, latitude, longitude
    INTO v_addr_city, v_addr_street, v_addr_building, v_addr_info, v_addr_lat, v_addr_lng
    FROM addresses WHERE id = p_address_id AND user_id = p_user_id AND deleted_at IS NULL LIMIT 1;
    IF v_addr_city IS NULL THEN SET p_error='Address not found.'; ROLLBACK; LEAVE sp_place_order; END IF;
    SELECT name, fee, estimated_delivery_time INTO v_del_name, v_delivery_fee, v_del_time
    FROM delivery_types WHERE id = p_delivery_type_id AND is_active = 1 LIMIT 1;
    IF v_del_name IS NULL THEN SET p_error='Invalid delivery type.'; ROLLBACK; LEAVE sp_place_order; END IF;
    SELECT name INTO v_pay_name FROM payment_types WHERE id = p_payment_type_id AND is_active = 1 LIMIT 1;
    IF v_pay_name IS NULL THEN SET p_error='Invalid payment type.'; ROLLBACK; LEAVE sp_place_order; END IF;
    SELECT COALESCE(SUM(CASE WHEN p.has_offer=1 AND p.offer_price IS NOT NULL
        THEN p.offer_price * ci.quantity ELSE p.price * ci.quantity END),0.00) INTO v_subtotal
    FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = v_cart_id;
    IF p_coupon_code IS NOT NULL AND p_coupon_code != '' THEN
        CALL sp_validate_coupon(p_coupon_code, p_user_id, v_subtotal, v_coupon_id, v_discount, v_coupon_success, v_coupon_msg);
        IF v_coupon_success = 0 THEN SET p_error=v_coupon_msg; ROLLBACK; LEAVE sp_place_order; END IF;
    END IF;
    SET v_total = GREATEST(0.00, v_subtotal - v_discount) + v_delivery_fee;
    INSERT INTO orders (user_id, address_id, delivery_type_id, payment_type_id, coupon_id, coupon_code,
        subtotal, delivery_fee, discount_amount, total_price, status, notes,
        address_city, address_street, address_building, address_info, address_latitude, address_longitude,
        delivery_name, delivery_time, payment_name)
    VALUES (p_user_id, p_address_id, p_delivery_type_id, p_payment_type_id, v_coupon_id, p_coupon_code,
        v_subtotal, v_delivery_fee, v_discount, v_total, 'pending', p_notes,
        v_addr_city, v_addr_street, v_addr_building, v_addr_info, v_addr_lat, v_addr_lng,
        v_del_name, v_del_time, v_pay_name);
    SET p_order_id = LAST_INSERT_ID();
    INSERT INTO order_items (order_id, product_id, product_name_snapshot, product_price_snapshot,
        offer_price_snapshot, quantity, selected_options_json)
    SELECT p_order_id, ci.product_id, p.name, p.price,
           CASE WHEN p.has_offer=1 THEN p.offer_price ELSE NULL END,
           ci.quantity, ci.selected_options_json
    FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = v_cart_id;
    UPDATE products p JOIN cart_items ci ON ci.product_id = p.id AND ci.cart_id = v_cart_id
    SET p.stock_quantity = p.stock_quantity - ci.quantity;
    IF v_coupon_id IS NOT NULL THEN
        INSERT INTO coupon_usages (coupon_id, user_id, order_id) VALUES (v_coupon_id, p_user_id, p_order_id);
    END IF;
    DELETE FROM cart_items WHERE cart_id = v_cart_id;
    INSERT INTO notifications (user_id, type, title, body, data_json)
    VALUES (p_user_id, 'order_placed', 'Order Confirmed',
            CONCAT('Your order #', p_order_id, ' has been placed successfully.'),
            JSON_OBJECT('order_id', p_order_id));
    COMMIT;
END$$

-- Orders: get list for user
CREATE PROCEDURE sp_get_user_orders(IN p_user_id BIGINT UNSIGNED, IN p_page INT UNSIGNED, IN p_per_page INT UNSIGNED)
BEGIN
    DECLARE v_offset INT UNSIGNED DEFAULT 0;
    SET v_offset = (p_page - 1) * p_per_page;
    SELECT o.id, o.status, o.subtotal, o.delivery_fee, o.discount_amount, o.total_price,
           o.payment_name, o.delivery_name, o.coupon_code, o.created_at,
           COUNT(oi.id) AS total_items
    FROM orders o LEFT JOIN order_items oi ON oi.order_id = o.id
    WHERE o.user_id = p_user_id GROUP BY o.id ORDER BY o.created_at DESC
    LIMIT p_per_page OFFSET v_offset;
END$$

-- Orders: get single order detail
CREATE PROCEDURE sp_get_order_detail(IN p_order_id BIGINT UNSIGNED, IN p_user_id BIGINT UNSIGNED)
BEGIN
    SELECT o.id, o.status, o.subtotal, o.delivery_fee, o.discount_amount, o.total_price,
           o.notes, o.payment_name, o.delivery_name, o.delivery_time, o.coupon_code,
           o.address_city, o.address_street, o.address_building, o.address_info,
           o.address_latitude, o.address_longitude, o.created_at, o.updated_at
    FROM orders o WHERE o.id = p_order_id AND o.user_id = p_user_id LIMIT 1;
    SELECT oi.id, oi.product_id, oi.product_name_snapshot, oi.product_price_snapshot,
           oi.offer_price_snapshot, oi.quantity, oi.selected_options_json,
           COALESCE(oi.offer_price_snapshot, oi.product_price_snapshot) * oi.quantity AS line_total,
           p.main_image AS product_image, p.slug AS product_slug
    FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = p_order_id;
    SELECT status, note, created_at FROM order_status_history
    WHERE order_id = p_order_id ORDER BY created_at ASC;
END$$

-- Orders: cancel (pending only)
CREATE PROCEDURE sp_cancel_order(
    IN  p_order_id BIGINT UNSIGNED,
    IN  p_user_id  BIGINT UNSIGNED,
    OUT p_success  TINYINT,
    OUT p_message  VARCHAR(255)
)
sp_cancel_order: BEGIN
    DECLARE v_status VARCHAR(50);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; SET p_success=0; SET p_message='Cancellation failed.'; END;
    SET p_success = 0;
    SELECT status INTO v_status FROM orders WHERE id = p_order_id AND user_id = p_user_id LIMIT 1;
    IF v_status IS NULL THEN SET p_message='Order not found.'; LEAVE sp_cancel_order; END IF;
    IF v_status != 'pending' THEN SET p_message=CONCAT('Cannot cancel order with status: ',v_status); LEAVE sp_cancel_order; END IF;
    START TRANSACTION;
    UPDATE orders SET status = 'canceled' WHERE id = p_order_id AND user_id = p_user_id;
    UPDATE products p JOIN order_items oi ON oi.product_id = p.id AND oi.order_id = p_order_id
    SET p.stock_quantity = p.stock_quantity + oi.quantity;
    COMMIT;
    SET p_success=1; SET p_message='Order canceled successfully.';
END$$

-- Addresses: get all for user
CREATE PROCEDURE sp_get_user_addresses(IN p_user_id BIGINT UNSIGNED)
BEGIN
    SELECT id, latitude, longitude, city_name, street_name, building_number,
           additional_info, is_default, created_at
    FROM addresses WHERE user_id = p_user_id AND deleted_at IS NULL
    ORDER BY is_default DESC, created_at DESC;
END$$

-- Addresses: create
CREATE PROCEDURE sp_create_address(
    IN  p_user_id         BIGINT UNSIGNED,
    IN  p_latitude        DECIMAL(10,8),
    IN  p_longitude       DECIMAL(11,8),
    IN  p_city_name       VARCHAR(255),
    IN  p_street_name     VARCHAR(500),
    IN  p_building_number VARCHAR(100),
    IN  p_additional_info TEXT,
    IN  p_is_default      TINYINT,
    OUT p_address_id      BIGINT UNSIGNED
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; SET p_address_id=NULL; END;
    START TRANSACTION;
    IF p_is_default = 1 THEN
        UPDATE addresses SET is_default = 0 WHERE user_id = p_user_id AND deleted_at IS NULL;
    END IF;
    IF (SELECT COUNT(*) FROM addresses WHERE user_id = p_user_id AND deleted_at IS NULL) = 0 THEN
        SET p_is_default = 1;
    END IF;
    INSERT INTO addresses (user_id, latitude, longitude, city_name, street_name, building_number, additional_info, is_default)
    VALUES (p_user_id, p_latitude, p_longitude, p_city_name, p_street_name, p_building_number, p_additional_info, p_is_default);
    SET p_address_id = LAST_INSERT_ID();
    COMMIT;
END$$

-- Addresses: update
CREATE PROCEDURE sp_update_address(
    IN  p_address_id      BIGINT UNSIGNED,
    IN  p_user_id         BIGINT UNSIGNED,
    IN  p_latitude        DECIMAL(10,8),
    IN  p_longitude       DECIMAL(11,8),
    IN  p_city_name       VARCHAR(255),
    IN  p_street_name     VARCHAR(500),
    IN  p_building_number VARCHAR(100),
    IN  p_additional_info TEXT,
    IN  p_is_default      TINYINT,
    OUT p_success         TINYINT,
    OUT p_message         VARCHAR(255)
)
sp_update_address: BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; SET p_success=0; SET p_message='Update failed.'; END;
    SET p_success = 0;
    IF (SELECT COUNT(*) FROM addresses WHERE id=p_address_id AND user_id=p_user_id AND deleted_at IS NULL)=0 THEN
        SET p_message='Address not found.'; LEAVE sp_update_address; END IF;
    START TRANSACTION;
    IF p_is_default = 1 THEN UPDATE addresses SET is_default=0 WHERE user_id=p_user_id AND deleted_at IS NULL; END IF;
    UPDATE addresses SET latitude=p_latitude, longitude=p_longitude, city_name=p_city_name,
        street_name=p_street_name, building_number=p_building_number,
        additional_info=p_additional_info, is_default=p_is_default
    WHERE id=p_address_id AND user_id=p_user_id;
    COMMIT;
    SET p_success=1; SET p_message='Address updated.';
END$$

-- Addresses: soft delete
CREATE PROCEDURE sp_delete_address(
    IN  p_address_id BIGINT UNSIGNED,
    IN  p_user_id    BIGINT UNSIGNED,
    OUT p_success    TINYINT,
    OUT p_message    VARCHAR(255)
)
sp_delete_address: BEGIN
    DECLARE v_is_default TINYINT DEFAULT 0; DECLARE v_next_id BIGINT UNSIGNED;
    SET p_success = 0;
    SELECT is_default INTO v_is_default FROM addresses
    WHERE id=p_address_id AND user_id=p_user_id AND deleted_at IS NULL;
    IF v_is_default IS NULL THEN SET p_message='Address not found.'; LEAVE sp_delete_address; END IF;
    UPDATE addresses SET deleted_at=NOW() WHERE id=p_address_id AND user_id=p_user_id;
    IF v_is_default = 1 THEN
        SELECT id INTO v_next_id FROM addresses WHERE user_id=p_user_id AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1;
        IF v_next_id IS NOT NULL THEN UPDATE addresses SET is_default=1 WHERE id=v_next_id; END IF;
    END IF;
    SET p_success=1; SET p_message='Address deleted.';
END$$

-- Addresses: set default
CREATE PROCEDURE sp_set_default_address(
    IN  p_address_id BIGINT UNSIGNED,
    IN  p_user_id    BIGINT UNSIGNED,
    OUT p_success    TINYINT,
    OUT p_message    VARCHAR(255)
)
sp_set_default_address: BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; SET p_success=0; SET p_message='Failed to set default address.'; END;
    SET p_success = 0;
    IF (SELECT COUNT(*) FROM addresses WHERE id=p_address_id AND user_id=p_user_id AND deleted_at IS NULL)=0 THEN
        SET p_message='Address not found.'; LEAVE sp_set_default_address; END IF;
    START TRANSACTION;
    UPDATE addresses SET is_default=0 WHERE user_id=p_user_id AND deleted_at IS NULL;
    UPDATE addresses SET is_default=1 WHERE id=p_address_id AND user_id=p_user_id;
    COMMIT;
    SET p_success=1; SET p_message='Default address updated.';
END$$

-- Wishlist: get
CREATE PROCEDURE sp_get_wishlist(IN p_user_id BIGINT UNSIGNED, IN p_page INT UNSIGNED, IN p_per_page INT UNSIGNED)
BEGIN
    DECLARE v_offset INT UNSIGNED DEFAULT 0;
    SET v_offset = (p_page - 1) * p_per_page;
    SELECT w.id AS wishlist_id, w.created_at AS wishlisted_at,
           p.id, p.name, p.slug, p.price, p.has_offer, p.offer_price,
           p.main_image, p.stock_quantity, p.average_rating, p.is_active
    FROM wishlists w JOIN products p ON p.id = w.product_id
    WHERE w.user_id = p_user_id ORDER BY w.created_at DESC
    LIMIT p_per_page OFFSET v_offset;
END$$

-- Wishlist: toggle
CREATE PROCEDURE sp_toggle_wishlist(
    IN  p_user_id    BIGINT UNSIGNED,
    IN  p_product_id BIGINT UNSIGNED,
    OUT p_action     VARCHAR(20),
    OUT p_success    TINYINT,
    OUT p_message    VARCHAR(255)
)
sp_toggle_wishlist: BEGIN
    DECLARE v_exists BIGINT UNSIGNED;
    SET p_success = 0;
    IF (SELECT COUNT(*) FROM products WHERE id=p_product_id AND is_active=1 AND deleted_at IS NULL)=0 THEN
        SET p_message='Product not found.'; LEAVE sp_toggle_wishlist; END IF;
    SELECT id INTO v_exists FROM wishlists WHERE user_id=p_user_id AND product_id=p_product_id LIMIT 1;
    IF v_exists IS NOT NULL THEN
        DELETE FROM wishlists WHERE id = v_exists;
        SET p_action='removed'; SET p_message='Removed from wishlist.';
    ELSE
        INSERT INTO wishlists (user_id, product_id) VALUES (p_user_id, p_product_id);
        SET p_action='added'; SET p_message='Added to wishlist.';
    END IF;
    SET p_success=1;
END$$

-- Coupons: validate
CREATE PROCEDURE sp_validate_coupon(
    IN  p_code         VARCHAR(100),
    IN  p_user_id      BIGINT UNSIGNED,
    IN  p_order_total  DECIMAL(12,2),
    OUT p_coupon_id    BIGINT UNSIGNED,
    OUT p_discount     DECIMAL(10,2),
    OUT p_success      TINYINT,
    OUT p_message      VARCHAR(255)
)
sp_validate_coupon: BEGIN
    DECLARE v_type    VARCHAR(20); DECLARE v_value   DECIMAL(10,2);
    DECLARE v_min     DECIMAL(12,2); DECLARE v_cap    DECIMAL(10,2);
    DECLARE v_ulimit  INT UNSIGNED; DECLARE v_ucount  INT UNSIGNED;
    DECLARE v_per_usr INT UNSIGNED; DECLARE v_usr_use INT UNSIGNED;
    DECLARE v_starts  TIMESTAMP; DECLARE v_expires TIMESTAMP;
    DECLARE v_active  TINYINT;
    SET p_success=0; SET p_discount=0.00; SET p_coupon_id=NULL;
    SELECT id, discount_type, discount_value, min_order_amount, max_discount_cap,
           usage_limit, usage_count, per_user_limit, is_active, starts_at, expires_at
    INTO p_coupon_id, v_type, v_value, v_min, v_cap, v_ulimit, v_ucount, v_per_usr, v_active, v_starts, v_expires
    FROM coupons WHERE code = p_code LIMIT 1;
    IF p_coupon_id IS NULL  THEN SET p_message='Invalid coupon code.';             LEAVE sp_validate_coupon; END IF;
    IF v_active = 0         THEN SET p_message='Coupon is no longer active.';      LEAVE sp_validate_coupon; END IF;
    IF v_starts IS NOT NULL AND NOW() < v_starts THEN SET p_message='Coupon is not yet valid.'; LEAVE sp_validate_coupon; END IF;
    IF v_expires IS NOT NULL AND NOW() > v_expires THEN SET p_message='Coupon has expired.';    LEAVE sp_validate_coupon; END IF;
    IF p_order_total < v_min THEN SET p_message=CONCAT('Minimum order amount is ',v_min); LEAVE sp_validate_coupon; END IF;
    IF v_ulimit IS NOT NULL AND v_ucount >= v_ulimit THEN SET p_message='Coupon usage limit reached.'; LEAVE sp_validate_coupon; END IF;
    SELECT COUNT(*) INTO v_usr_use FROM coupon_usages WHERE coupon_id=p_coupon_id AND user_id=p_user_id;
    IF v_usr_use >= v_per_usr THEN SET p_message='You have already used this coupon.'; LEAVE sp_validate_coupon; END IF;
    IF v_type = 'percentage' THEN
        SET p_discount = ROUND(p_order_total * (v_value / 100), 2);
        IF v_cap IS NOT NULL AND p_discount > v_cap THEN SET p_discount = v_cap; END IF;
    ELSE SET p_discount = LEAST(v_value, p_order_total); END IF;
    SET p_success=1; SET p_message='Coupon applied successfully.';
END$$

-- Notifications: create
CREATE PROCEDURE sp_create_notification(
    IN p_user_id BIGINT UNSIGNED, IN p_type VARCHAR(100),
    IN p_title VARCHAR(500), IN p_body TEXT, IN p_data_json JSON)
BEGIN
    INSERT INTO notifications (user_id, type, title, body, data_json) VALUES (p_user_id, p_type, p_title, p_body, p_data_json);
END$$

-- Notifications: get paginated
CREATE PROCEDURE sp_get_notifications(IN p_user_id BIGINT UNSIGNED, IN p_page INT UNSIGNED, IN p_per_page INT UNSIGNED)
BEGIN
    DECLARE v_offset INT UNSIGNED DEFAULT 0;
    SET v_offset = (p_page - 1) * p_per_page;
    SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id=p_user_id AND is_read=0;
    SELECT id, type, title, body, data_json, is_read, read_at, created_at
    FROM notifications WHERE user_id=p_user_id
    ORDER BY created_at DESC LIMIT p_per_page OFFSET v_offset;
END$$

-- Notifications: mark all read
CREATE PROCEDURE sp_mark_notifications_read(IN p_user_id BIGINT UNSIGNED)
BEGIN
    UPDATE notifications SET is_read=1, read_at=NOW() WHERE user_id=p_user_id AND is_read=0;
END$$

-- Push tokens: register
CREATE PROCEDURE sp_register_push_token(IN p_user_id BIGINT UNSIGNED, IN p_token VARCHAR(512), IN p_platform ENUM('android','ios','web'))
BEGIN
    INSERT INTO push_tokens (user_id, token, platform) VALUES (p_user_id, p_token, p_platform)
    ON DUPLICATE KEY UPDATE user_id=p_user_id, platform=p_platform, updated_at=NOW();
END$$

-- Push tokens: remove
CREATE PROCEDURE sp_remove_push_token(IN p_token VARCHAR(512))
BEGIN DELETE FROM push_tokens WHERE token = p_token; END$$

-- App settings: get
CREATE PROCEDURE sp_get_setting(IN p_key VARCHAR(255))
BEGIN SELECT setting_key, value, type FROM app_settings WHERE setting_key=p_key LIMIT 1; END$$

-- App settings: set
CREATE PROCEDURE sp_set_setting(IN p_key VARCHAR(255), IN p_value TEXT)
BEGIN
    UPDATE app_settings SET value=p_value WHERE setting_key=p_key;
    IF ROW_COUNT()=0 THEN INSERT INTO app_settings (setting_key, value) VALUES (p_key, p_value); END IF;
END$$

-- Audit: log action
CREATE PROCEDURE sp_log_audit(
    IN p_user_id BIGINT UNSIGNED, IN p_action VARCHAR(100), IN p_entity_type VARCHAR(100),
    IN p_entity_id BIGINT UNSIGNED, IN p_ip_address VARCHAR(45), IN p_user_agent VARCHAR(500), IN p_meta_json JSON)
BEGIN
    INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, meta_json)
    VALUES (p_user_id, p_action, p_entity_type, p_entity_id, p_ip_address, p_user_agent, p_meta_json);
END$$

-- Inventory: update stock
CREATE PROCEDURE sp_update_stock(
    IN  p_product_id BIGINT UNSIGNED,
    IN  p_quantity   INT,
    OUT p_success    TINYINT,
    OUT p_message    VARCHAR(255)
)
sp_update_stock: BEGIN
    DECLARE v_current INT UNSIGNED DEFAULT 0;
    SET p_success = 0;
    SELECT stock_quantity INTO v_current FROM products WHERE id=p_product_id AND deleted_at IS NULL LIMIT 1;
    IF v_current IS NULL THEN SET p_message='Product not found.'; LEAVE sp_update_stock; END IF;
    IF (CAST(v_current AS SIGNED) + p_quantity) < 0 THEN
        SET p_message=CONCAT('Cannot deduct ',ABS(p_quantity),' units. Only ',v_current,' in stock.');
        LEAVE sp_update_stock; END IF;
    UPDATE products SET stock_quantity = CAST(stock_quantity AS SIGNED) + p_quantity WHERE id=p_product_id;
    SET p_success=1; SET p_message=CONCAT('Stock updated. New quantity: ', CAST(v_current AS SIGNED) + p_quantity);
END$$

-- Admin: get orders with filters
CREATE PROCEDURE sp_admin_get_orders(IN p_status VARCHAR(50), IN p_page INT UNSIGNED, IN p_per_page INT UNSIGNED)
BEGIN
    DECLARE v_offset INT UNSIGNED DEFAULT 0;
    SET v_offset = (p_page - 1) * p_per_page;
    SELECT o.id, o.status, o.subtotal, o.delivery_fee, o.discount_amount, o.total_price,
           o.payment_name, o.delivery_name, o.coupon_code, o.created_at,
           u.id AS user_id, u.fullname AS user_name, u.email AS user_email,
           COUNT(oi.id) AS total_items
    FROM orders o JOIN users u ON u.id=o.user_id LEFT JOIN order_items oi ON oi.order_id=o.id
    WHERE (p_status IS NULL OR p_status='' OR o.status=p_status)
    GROUP BY o.id ORDER BY o.created_at DESC
    LIMIT p_per_page OFFSET v_offset;
END$$

-- Admin: update order status
CREATE PROCEDURE sp_admin_update_order_status(
    IN  p_order_id BIGINT UNSIGNED,
    IN  p_status   ENUM('pending','preparing','delivering','finished','canceled','rejected'),
    IN  p_note     TEXT,
    OUT p_success  TINYINT,
    OUT p_message  VARCHAR(255)
)
sp_admin_update_order_status: BEGIN
    DECLARE v_user_id BIGINT UNSIGNED; DECLARE v_current_status VARCHAR(50);
    SET p_success = 0;
    SELECT user_id, status INTO v_user_id, v_current_status FROM orders WHERE id=p_order_id LIMIT 1;
    IF v_user_id IS NULL THEN SET p_message='Order not found.'; LEAVE sp_admin_update_order_status; END IF;
    IF v_current_status IN ('finished','canceled','rejected') THEN
        SET p_message=CONCAT('Cannot change status of a ',v_current_status,' order.'); LEAVE sp_admin_update_order_status; END IF;
    UPDATE orders SET status=p_status WHERE id=p_order_id;
    INSERT INTO order_status_history (order_id, status, note) VALUES (p_order_id, p_status, p_note);
    INSERT INTO notifications (user_id, type, title, body, data_json) VALUES
        (v_user_id, 'order_status', CONCAT('Order #',p_order_id,' Update'),
         CONCAT('Your order status changed to: ',p_status), JSON_OBJECT('order_id',p_order_id,'status',p_status));
    IF p_status = 'rejected' THEN
        UPDATE products p JOIN order_items oi ON oi.product_id=p.id AND oi.order_id=p_order_id
        SET p.stock_quantity = p.stock_quantity + oi.quantity;
    END IF;
    SET p_success=1; SET p_message=CONCAT('Order status updated to ',p_status);
END$$

-- Admin: dashboard statistics
CREATE PROCEDURE sp_admin_dashboard_stats()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL)                        AS total_users,
        (SELECT COUNT(*) FROM products WHERE is_active=1 AND deleted_at IS NULL)     AS total_products,
        (SELECT COUNT(*) FROM orders)                                                 AS total_orders,
        (SELECT COUNT(*) FROM orders WHERE status='pending')                          AS pending_orders,
        (SELECT COUNT(*) FROM orders WHERE status='preparing')                        AS preparing_orders,
        (SELECT COUNT(*) FROM orders WHERE status='delivering')                       AS delivering_orders,
        (SELECT COALESCE(SUM(total_price),0) FROM orders WHERE status='finished')     AS total_revenue,
        (SELECT COALESCE(SUM(total_price),0) FROM orders WHERE status='finished'
         AND created_at >= DATE_FORMAT(NOW(),'%Y-%m-01'))                             AS revenue_this_month,
        (SELECT COUNT(*) FROM orders WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')) AS orders_this_month,
        (SELECT COUNT(*) FROM users WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')
         AND deleted_at IS NULL)                                                      AS new_users_this_month,
        (SELECT COUNT(*) FROM products WHERE stock_quantity=0 AND is_active=1
         AND deleted_at IS NULL)                                                      AS out_of_stock_count;

    SELECT DATE_FORMAT(created_at,'%Y-%m') AS month, COUNT(*) AS order_count,
           COALESCE(SUM(total_price),0.00) AS revenue
    FROM orders WHERE status='finished' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY month ASC;

    SELECT p.id, p.name, p.slug, p.main_image, p.total_sales,
           SUM(oi.quantity * oi.product_price_snapshot) AS total_revenue
    FROM order_items oi JOIN products p ON p.id=oi.product_id
    JOIN orders o ON o.id=oi.order_id AND o.status='finished'
    GROUP BY p.id ORDER BY p.total_sales DESC LIMIT 10;

    SELECT c.id, c.name, COALESCE(SUM(p.total_sales),0) AS total_sales
    FROM categories c LEFT JOIN products p ON p.category_id=c.id AND p.deleted_at IS NULL
    WHERE c.is_active=1 GROUP BY c.id, c.name ORDER BY total_sales DESC LIMIT 5;

    SELECT id, name, slug, sku, stock_quantity, main_image
    FROM products WHERE stock_quantity <= 5 AND is_active=1 AND deleted_at IS NULL
    ORDER BY stock_quantity ASC LIMIT 20;
END$$

DELIMITER ;

-- ============================================================
-- SCHEDULED EVENTS
-- ============================================================
SET GLOBAL event_scheduler = ON;

CREATE EVENT IF NOT EXISTS evt_purge_expired_tokens
ON SCHEDULE EVERY 1 DAY STARTS CURRENT_TIMESTAMP
DO DELETE FROM user_tokens WHERE expires_at IS NOT NULL AND expires_at < NOW();

CREATE EVENT IF NOT EXISTS evt_purge_password_resets
ON SCHEDULE EVERY 1 HOUR STARTS CURRENT_TIMESTAMP
DO DELETE FROM password_resets WHERE expires_at < NOW();

CREATE EVENT IF NOT EXISTS evt_purge_failed_logins
ON SCHEDULE EVERY 1 HOUR STARTS CURRENT_TIMESTAMP
DO DELETE FROM failed_login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);

CREATE EVENT IF NOT EXISTS evt_purge_old_notifications
ON SCHEDULE EVERY 1 DAY STARTS CURRENT_TIMESTAMP
DO DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY) AND is_read=1;

CREATE EVENT IF NOT EXISTS evt_purge_old_audit_logs
ON SCHEDULE EVERY 1 WEEK STARTS CURRENT_TIMESTAMP
DO DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

CREATE EVENT IF NOT EXISTS evt_hard_delete_old_users
ON SCHEDULE EVERY 1 DAY STARTS CURRENT_TIMESTAMP
DO DELETE FROM users WHERE deleted_at IS NOT NULL AND deleted_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- ============================================================
-- SEED DATA
-- ============================================================

INSERT INTO categories (name, icon, image, is_active) VALUES
('Electronics',    'electronics.svg',  'electronics.jpg',  1),
('Clothing',       'clothing.svg',     'clothing.jpg',     1),
('Home & Kitchen', 'home_kitchen.svg', 'home_kitchen.jpg', 1),
('Sports',         'sports.svg',       'sports.jpg',       1),
('Books',          'books.svg',        'books.jpg',        1),
('Beauty',         'beauty.svg',       'beauty.jpg',       1),
('Toys',           'toys.svg',         'toys.jpg',         1),
('Automotive',     'automotive.svg',   'automotive.jpg',   1);

INSERT INTO brands (name, icon, image, is_active) VALUES
('Apple',   'apple.svg',   'apple.jpg',   1),
('Samsung', 'samsung.svg', 'samsung.jpg', 1),
('Nike',    'nike.svg',    'nike.jpg',    1),
('Adidas',  'adidas.svg',  'adidas.jpg',  1),
('Sony',    'sony.svg',    'sony.jpg',    1),
('LG',      'lg.svg',      'lg.jpg',      1),
('Xiaomi',  'xiaomi.svg',  'xiaomi.jpg',  1),
('IKEA',    'ikea.svg',    'ikea.jpg',    1);

INSERT INTO delivery_types (name, fee, estimated_delivery_time, is_active) VALUES
('Standard Delivery',  5.00,  '3-5 business days', 1),
('Express Delivery',  15.00,  '1-2 business days', 1),
('Same Day Delivery', 25.00,  'Today by 10 PM',    1),
('Free Shipping',      0.00,  '5-7 business days', 1);

INSERT INTO payment_types (name, is_active) VALUES
('Cash on Delivery', 1),
('Credit Card',      1),
('Debit Card',       1),
('Digital Wallet',   1);

INSERT INTO advertisements (title, image, redirect_type, redirect_value, is_active, sort_order) VALUES
('Summer Sale - Up to 50% Off', 'ads/summer_sale.jpg', 'url',      '/deals', 1, 1),
('New Electronics Arrivals',    'ads/electronics.jpg', 'category', '1',      1, 2),
('Nike Latest Collection',      'ads/nike.jpg',        'brand',    '3',      1, 3),
('Exclusive Members Deal',      'ads/members.jpg',     'none',     NULL,     1, 4);

INSERT INTO products (brand_id, category_id, name, slug, description, sku, barcode, price, has_offer, offer_price, stock_quantity, main_image, is_active) VALUES
(1,1,'Apple iPhone 15 Pro',       'apple-iphone-15-pro',       'Titanium frame, A17 Pro chip.',                  'SKU-IP15P', '8901234567890', 1299.00,1,  999.00,50, 'products/iphone15pro.jpg',    1),
(1,1,'Apple MacBook Air M3',      'apple-macbook-air-m3',      '15-inch MacBook Air with M3 chip.',              'SKU-MBAM3', '8901234567891', 1499.00,0,  NULL,  30, 'products/macbook_air_m3.jpg', 1),
(2,1,'Samsung Galaxy S24 Ultra',  'samsung-galaxy-s24-ultra',  'S Pen, 200MP camera.',                           'SKU-S24U',  '8901234567892', 1199.00,1,  899.00,40, 'products/s24ultra.jpg',       1),
(5,1,'Sony WH-1000XM5',           'sony-wh-1000xm5',           'Industry-leading noise cancelling headphones.',  'SKU-WH1K',  '8901234567893',  349.00,1,  249.00,100,'products/wh1000xm5.jpg',     1),
(6,1,'LG OLED C3 55"',            'lg-oled-c3-55',             '4K OLED TV, Dolby Vision, G-Sync.',              'SKU-LGC3',  '8901234567894',  999.00,0,  NULL,  20, 'products/lg_oled_c3.jpg',    1),
(7,1,'Xiaomi Redmi Note 13 Pro',  'xiaomi-redmi-note-13-pro',  '200MP camera, 5000mAh, 120Hz AMOLED.',           'SKU-XRN13', '8901234567895',  299.00,1,  199.00,80, 'products/redmi_note13.jpg',   1),
(3,2,'Nike Air Max 270',          'nike-air-max-270',          'Large Air unit for all-day comfort.',             'SKU-AM270', '8901234567896',  150.00,1,  110.00,200,'products/air_max_270.jpg',   1),
(4,2,'Adidas Ultraboost 23',      'adidas-ultraboost-23',      'Boost cushioning for high-performance running.',  'SKU-UB23',  '8901234567897',  180.00,0,  NULL,  150,'products/ultraboost23.jpg',  1),
(3,2,'Nike Dri-FIT T-Shirt',      'nike-dri-fit-t-shirt',      'Moisture-wicking training fabric.',               'SKU-NDFT',  '8901234567898',   35.00,1,   25.00,500,'products/nike_dri_fit.jpg',  1),
(8,3,'IKEA KALLAX Shelf Unit',    'ikea-kallax-shelf-unit',    'Versatile 4-compartment shelving unit.',          'SKU-KLX4',  '8901234567899',   59.00,0,  NULL,  75, 'products/kallax.jpg',         1);

INSERT INTO product_images (product_id, image_url, sort_order) VALUES
(1,'products/iphone15pro_2.jpg',1),(1,'products/iphone15pro_3.jpg',2),(1,'products/iphone15pro_4.jpg',3),
(3,'products/s24ultra_2.jpg',1),(3,'products/s24ultra_3.jpg',2),
(4,'products/wh1000xm5_2.jpg',1),
(7,'products/air_max_270_2.jpg',1),(7,'products/air_max_270_3.jpg',2);

INSERT INTO product_options (product_id, name, sort_order) VALUES
(1,'Color',1),(1,'Storage',2),(7,'Color',1),(7,'Size',2),(8,'Color',1),(8,'Size',2),(9,'Size',1);

INSERT INTO product_option_values (product_option_id, value, price_modifier, sort_order) VALUES
(1,'Natural Titanium',0.00,1),(1,'Blue Titanium',0.00,2),(1,'White Titanium',0.00,3),(1,'Black Titanium',0.00,4),
(2,'128GB',0.00,1),(2,'256GB',100.00,2),(2,'512GB',300.00,3),(2,'1TB',500.00,4),
(3,'Black/White',0.00,1),(3,'White/Blue',0.00,2),(3,'Red/Black',0.00,3),
(4,'EU 40',0.00,1),(4,'EU 41',0.00,2),(4,'EU 42',0.00,3),(4,'EU 43',0.00,4),(4,'EU 44',0.00,5),(4,'EU 45',0.00,6),
(5,'Core Black',0.00,1),(5,'Cloud White',0.00,2),
(6,'EU 40',0.00,1),(6,'EU 41',0.00,2),(6,'EU 42',0.00,3),(6,'EU 43',0.00,4),(6,'EU 44',0.00,5),
(7,'XS',0.00,1),(7,'S',0.00,2),(7,'M',0.00,3),(7,'L',0.00,4),(7,'XL',0.00,5),(7,'XXL',0.00,6);

INSERT INTO app_settings (setting_key, value, type, description) VALUES
('app_name',                  'Johnny Store', 'string',  'Application name'),
('app_version',               '1.0.0',        'string',  'Current API version'),
('max_failed_login_attempts', '5',            'integer', 'Max failed logins before lockout'),
('login_lockout_minutes',     '15',           'integer', 'Lockout duration in minutes'),
('token_expiry_days',         '30',           'integer', 'Auth token expiry in days'),
('password_reset_expiry_min', '60',           'integer', 'Password reset token TTL in minutes'),
('default_currency',          'USD',          'string',  'Default store currency'),
('free_shipping_threshold',   '100.00',       'string',  'Order total triggering free shipping'),
('max_cart_quantity',         '10',           'integer', 'Max units of single product per cart'),
('reviews_require_purchase',  'true',         'boolean', 'Only allow reviews from verified buyers'),
('maintenance_mode',          'false',        'boolean', 'Disable API when true');

INSERT INTO schema_migrations (version, description) VALUES
('1.0.0', 'Initial schema: all tables, views, triggers, procedures, events, seed data');

-- ============================================================
-- TEST USER — REMOVE BEFORE PRODUCTION
-- password = Test@1234  (bcrypt hash)
-- ============================================================
INSERT INTO users (fullname, email, password, gender, birthdate) VALUES
('Test User', 'test@johnny.dev', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1995-06-15');

-- ============================================================
-- PRODUCTION CHECKLIST
-- [ ] Remove test user (test@johnny.dev) before go-live
-- [ ] Restrict MySQL remote access; use strong DB password
-- [ ] Enable SSL/TLS on all MySQL connections
-- [ ] Set event_scheduler=ON in my.cnf (persists across restarts)
-- [ ] Enable slow query log: slow_query_log=1, long_query_time=1
-- [ ] Set innodb_buffer_pool_size to 70-80% of available RAM
-- [ ] Configure binary logging for point-in-time recovery
-- [ ] Schedule automated backups (mysqldump or Percona XtraBackup)
-- [ ] Run ANALYZE TABLE on all tables after initial data load
-- [ ] Load test sp_place_order with concurrent requests
-- [ ] Confirm FULLTEXT indexes are populated after bulk inserts
-- [ ] Verify ON DELETE RESTRICT on orders.user_id is intentional
-- ============================================================
-- ecommerce_db v1.0.0 — COMPLETE
-- Tables: 27 | Views: 11 | Triggers: 10 | Procedures: 40 | Events: 6
-- ============================================================
