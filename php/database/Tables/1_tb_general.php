<?php
$statements = array_merge($statements, [
    'CREATE TABLE IF NOT EXISTS app_apps( 
        app_id            INT(20) AUTO_INCREMENT PRIMARY KEY,
        app_name          VARCHAR(255) NOT NULL,
        app_icon          VARCHAR(255) NULL,
        app_order         INT(20) NULL,
        is_active         BOOLEAN DEFAULT TRUE,
        created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (app_name)
    )',

    'CREATE TABLE IF NOT EXISTS app_roles( 
        role_id           INT(20) AUTO_INCREMENT PRIMARY KEY,
        role_name         VARCHAR(255) NOT NULL,
        is_active         BOOLEAN DEFAULT TRUE,
        created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (role_name)
    )',

    'CREATE TABLE IF NOT EXISTS app_specialties( 
        specialty_id       INT(20) AUTO_INCREMENT PRIMARY KEY,
        specialty_name     VARCHAR(255) NOT NULL,
        is_active          BOOLEAN DEFAULT TRUE,
        created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (specialty_name)
    )',

    'CREATE TABLE IF NOT EXISTS app_users( 
        user_id           INT(20) AUTO_INCREMENT PRIMARY KEY,
        user_email        VARCHAR(255) NOT NULL,
        user_name         VARCHAR(255) NOT NULL,
        user_password     VARCHAR(255) NOT NULL,
        user_token        VARCHAR(255) NOT NULL,
        user_vcode        VARCHAR(255) NOT NULL,
        specialty_id      INT,
        FOREIGN KEY (specialty_id) REFERENCES app_specialties(specialty_id),
        is_super          BOOLEAN DEFAULT FALSE,
        is_active         BOOLEAN DEFAULT FALSE,
        created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (user_email)
    )',

    'CREATE TABLE IF NOT EXISTS app_user_authority( 
        log_id            INT(20) AUTO_INCREMENT PRIMARY KEY,
        user_id           INT,
        FOREIGN KEY (user_id) REFERENCES app_users(user_id),
        app_id            INT,
        FOREIGN KEY (app_id) REFERENCES app_apps(app_id),
        role_id           INT,
        FOREIGN KEY (role_id) REFERENCES app_roles(role_id),
        is_active         BOOLEAN DEFAULT TRUE,
        created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )',
]);
