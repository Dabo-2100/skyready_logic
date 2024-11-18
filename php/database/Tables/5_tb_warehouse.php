<?php
$statements = array_merge($statements, [
    'CREATE TABLE IF NOT EXISTS app_warehouses( 
        warehouse_id            INT(20) AUTO_INCREMENT PRIMARY KEY,
        warehouse_name          VARCHAR(255) NOT NULL,      
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (warehouse_name)
    )',

    'CREATE TABLE IF NOT EXISTS warehouses_users( 
        log_id                  INT(20) AUTO_INCREMENT PRIMARY KEY,
        warehouse_id            INT,      
        FOREIGN KEY (warehouse_id) REFERENCES app_warehouses(warehouse_id),
        user_id                 INT, 
        FOREIGN KEY (user_id) REFERENCES app_users(user_id),
        is_admin                BOOLEAN DEFAULT FALSE,
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (warehouse_id,user_id)
    )',

    'CREATE TABLE IF NOT EXISTS warehouse_locations( 
        location_id             INT(20) AUTO_INCREMENT PRIMARY KEY,
        location_name           VARCHAR(255) NOT NULL,
        warehouse_id            INT,
        FOREIGN KEY (warehouse_id) REFERENCES app_warehouses(warehouse_id),
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (warehouse_id,location_name)
    )',

    'CREATE TABLE IF NOT EXISTS warehouse_units( 
        unit_id                         INT(20) AUTO_INCREMENT PRIMARY KEY,
        unit_name                       VARCHAR(255) NOT NULL,
        parent_id                       INT,
        FOREIGN KEY (parent_id) REFERENCES warehouse_units(unit_id),
        eq_value                        FLOAT DEFAULT 1,
        created_at                      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update                     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active                       BOOLEAN DEFAULT TRUE,
        UNIQUE (unit_name)
    )',

    'CREATE TABLE IF NOT EXISTS warehouse_items( 
        item_id                 INT(20) AUTO_INCREMENT PRIMARY KEY,
        unit_id                 INT,
        FOREIGN KEY (unit_id) REFERENCES warehouse_units(unit_id),
        item_name               VARCHAR(255) NOT NULL,
        item_img                VARCHAR(255) NOT NULL,
        item_sn                 VARCHAR(255) NOT NULL,
        item_pn                 VARCHAR(255) NOT NULL,
        item_nsn                VARCHAR(255) NOT NULL,
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (item_sn , item_pn , item_nsn )
    )',

    'CREATE TABLE IF NOT EXISTS special_fields( 
        field_id                INT(20) AUTO_INCREMENT PRIMARY KEY,
        field_name              VARCHAR(255) NOT NULL,
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (field_name)
    )',

    'CREATE TABLE IF NOT EXISTS special_x_items( 
        log_id                  INT(20) AUTO_INCREMENT PRIMARY KEY,
        field_id                INT,
        FOREIGN KEY (field_id) REFERENCES special_fields(field_id),
        item_id                 INT,
        FOREIGN KEY (item_id) REFERENCES warehouse_items(item_id),
        warehouse_id            INT,
        FOREIGN KEY (warehouse_id) REFERENCES app_warehouses(warehouse_id),
        field_value             VARCHAR(255) NOT NULL,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (warehouse_id,item_id,field_id)
    )',

    'CREATE TABLE IF NOT EXISTS item_tags( 
        tag_id                  INT(20) AUTO_INCREMENT PRIMARY KEY,
        tag_name                VARCHAR(255) NOT NULL,
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (tag_name)
    )',

    'CREATE TABLE IF NOT EXISTS tasgs_x_items( 
        log_id                  INT(20) AUTO_INCREMENT PRIMARY KEY,
        item_id                 INT,
        FOREIGN KEY (item_id) REFERENCES warehouse_items(item_id),
        warehouse_id            INT,
        FOREIGN KEY (warehouse_id) REFERENCES app_warehouses(warehouse_id),
        tag_id                  INT,
        FOREIGN KEY (tag_id) REFERENCES item_tags(tag_id),
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (warehouse_id,item_id,tag_id)
    )',

    'CREATE TABLE IF NOT EXISTS qty_status( 
        status_id               INT(20) AUTO_INCREMENT PRIMARY KEY,
        status_name             VARCHAR(255) NOT NULL,
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (status_name)
    )',

    'CREATE TABLE IF NOT EXISTS items_qty(
        qty_id                  INT(20) AUTO_INCREMENT PRIMARY KEY,
        item_amount             FLOAT,
        item_id                 INT,
        FOREIGN KEY (item_id) REFERENCES warehouse_items(item_id),
        unit_id                 INT,
        FOREIGN KEY (unit_id) REFERENCES warehouse_units(unit_id),
        warehouse_id            INT,
        FOREIGN KEY (warehouse_id) REFERENCES app_warehouses(warehouse_id),
        location_id             INT,
        FOREIGN KEY (location_id) REFERENCES warehouse_locations(location_id),
        aircraft_id              INT NULL,
        FOREIGN KEY (aircraft_id) REFERENCES app_aircraft(aircraft_id),
        status_id               INT NULL,
        FOREIGN KEY (status_id) REFERENCES qty_status(status_id),
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (aircraft_id, item_id, location_id)
    )',

    'CREATE TABLE IF NOT EXISTS warehouse_packlists ( 
        packlist_id             INT(20) AUTO_INCREMENT PRIMARY KEY,
        packlist_name           VARCHAR(255) NOT NULL,
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (packlist_name)
    )',

    'CREATE TABLE IF NOT EXISTS warehouse_notices ( 
        notice_id             INT(20) AUTO_INCREMENT PRIMARY KEY,
        notice_date           DATE ,
        warehouse_id          INT,
        FOREIGN KEY (warehouse_id) REFERENCES app_warehouses(warehouse_id),
        is_active             BOOLEAN DEFAULT TRUE,
        created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )',

    'CREATE TABLE IF NOT EXISTS record_types ( 
        type_id               INT(20) AUTO_INCREMENT PRIMARY KEY,
        type_name             VARCHAR(255) NOT NULL,
        is_active             BOOLEAN DEFAULT TRUE,
        created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (type_name)
    )',

    'CREATE TABLE IF NOT EXISTS items_qty_logs( 
        record_id                INT(20) AUTO_INCREMENT PRIMARY KEY,
        record_type_id           INT,
        FOREIGN KEY (record_type_id) REFERENCES warehouse_notices(notice_id),     
        notice_id                INT,
        FOREIGN KEY (notice_id) REFERENCES warehouse_notices(notice_id),
        packlist_id              INT,
        FOREIGN KEY (packlist_id) REFERENCES warehouse_packlists(packlist_id),
        item_id                  INT,
        FOREIGN KEY (item_id) REFERENCES warehouse_items(item_id),
        location_id              INT,
        FOREIGN KEY (location_id) REFERENCES warehose_locations(location_id),
        warehouse_id             INT,
        FOREIGN KEY (warehouse_id) REFERENCES app_warehouses(warehouse_id),
        before_balance           FLOAT(20) ,
        item_amount              FLOAT(20) ,
        after_balance            FLOAT(20) ,
        is_active                BOOLEAN DEFAULT TRUE,
        created_at               TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (record_type_id , notice_id,packlist_id,item_id,location_id,warehouse_id)
    )',

]);
