<?php
$statements = array_merge($statements, [
    'CREATE TABLE IF NOT EXISTS aircraft_manufacturers( 
        manufacturer_id         INT(20) AUTO_INCREMENT PRIMARY KEY,
        manufacturer_name       VARCHAR(255) NOT NULL,
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (manufacturer_name)
    )',

    'CREATE TABLE IF NOT EXISTS aircraft_models( 
        model_id                INT(20) AUTO_INCREMENT PRIMARY KEY,
        model_name              VARCHAR(255) NOT NULL,
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (model_name)
    )',

    'CREATE TABLE IF NOT EXISTS aircraft_status( 
        status_id               INT(20) AUTO_INCREMENT PRIMARY KEY,
        status_name             VARCHAR(255) NOT NULL,
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (status_name)
    )',

    'CREATE TABLE IF NOT EXISTS aircraft_usags( 
        usage_id                INT(20) AUTO_INCREMENT PRIMARY KEY,
        usage_name              VARCHAR(255) NOT NULL,
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (usage_name)
    )',

    'CREATE TABLE IF NOT EXISTS aircraft_zones( 
        zone_id                 INT(20) AUTO_INCREMENT PRIMARY KEY,
        zone_name               VARCHAR(255) NOT NULL,
        parent_id               INT(20) NULL, 
        FOREIGN KEY (parent_id) REFERENCES aircraft_zones(zone_id),
        model_id                INT(20), 
        FOREIGN KEY (model_id) REFERENCES aircraft_models(model_id),
        sta_value               DECIMAL(10,2) NULL,
        wl_value                DECIMAL(10,2) NULL,
        bl_value                DECIMAL(10,2) NULL,
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (model_id,zone_name)
    )',

    'CREATE TABLE IF NOT EXISTS designator_types( 
        type_id               INT(20) AUTO_INCREMENT PRIMARY KEY,
        type_name             VARCHAR(255) NOT NULL,
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (type_name)
    )',

    'CREATE TABLE IF NOT EXISTS aircraft_designators( 
        designator_id           INT(20) AUTO_INCREMENT PRIMARY KEY,
        designator_name         VARCHAR(255) NOT NULL,
        type_id                 INT(20), 
        model_id                INT(20), 
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (model_id, designator_name),
        FOREIGN KEY (type_id) REFERENCES designator_types(type_id),
        FOREIGN KEY (model_id) REFERENCES aircraft_models(model_id)
    )',

    'CREATE TABLE IF NOT EXISTS app_aircraft ( 
        aircraft_id                      INT(20) AUTO_INCREMENT PRIMARY KEY,
        aircraft_serial_no               VARCHAR(255) NOT NULL,
        aircraft_register_no             VARCHAR(255) NOT NULL,
        model_id                        INT(20), 
        FOREIGN KEY (model_id) REFERENCES aircraft_models(model_id),
        manufacturer_id                 INT(20), 
        FOREIGN KEY (manufacturer_id) REFERENCES aircraft_manufacturers(manufacturer_id),
        status_id                       INT(20), 
        FOREIGN KEY (status_id) REFERENCES aircraft_status(status_id),
        usage_id                        INT(20), 
        FOREIGN KEY (usage_id) REFERENCES aircraft_usags(usage_id),
        aircraft_manufacture_date       DATE NULL,
        aircraft_flight_hours           DECIMAL(10,2) NULL,
        is_active                       BOOLEAN DEFAULT TRUE,
        created_at                      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update                     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (model_id, aircraft_serial_no),
        UNIQUE (model_id, aircraft_register_no)
    )',
]);
