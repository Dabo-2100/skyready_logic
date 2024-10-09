<?php
$statements = array_merge($statements, [
    'CREATE TABLE IF NOT EXISTS work_package_types( 
        package_type_id         INT(20) AUTO_INCREMENT PRIMARY KEY,
        package_type_name       VARCHAR(255) NOT NULL,
        is_active               BOOLEAN DEFAULT TRUE,
        created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (package_type_name)
    )',

    'CREATE TABLE IF NOT EXISTS work_packages ( 
        package_id                      INT(20) AUTO_INCREMENT PRIMARY KEY,
        package_name                    VARCHAR(255) NOT NULL,
        package_desc                    VARCHAR(255) NULL,
        package_version                 VARCHAR(255) NULL,
        package_duration                FLOAT(20) NULL,
        package_issued_duration         FLOAT(20) NULL,
        package_release_date            DATE NULL,
        package_type_id                 INT NULL, 
        FOREIGN KEY (package_type_id) REFERENCES work_package_types(package_type_id),
        parent_id                       INT NULL, 
        FOREIGN KEY (parent_id) REFERENCES work_packages(package_id),
        model_id                        INT NULL, 
        FOREIGN KEY (model_id) REFERENCES aircraft_models(model_id),
        is_folder                       BOOLEAN DEFAULT FALSE,
        is_active                       BOOLEAN DEFAULT TRUE,
        created_at                      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update                     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (parent_id,package_name,package_type_id)
    )',

    'CREATE TABLE IF NOT EXISTS work_package_task_types ( 
        type_id             INT(20) AUTO_INCREMENT PRIMARY KEY,
        type_name           VARCHAR(255) NOT NULL,
        specialty_id        INT NULL, 
        FOREIGN KEY (specialty_id) REFERENCES app_specialties(specialty_id),
        is_active           BOOLEAN DEFAULT TRUE,
        created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (type_name,specialty_id)
    )',

    'CREATE TABLE IF NOT EXISTS work_package_tasks ( 
        task_id             INT(20) AUTO_INCREMENT PRIMARY KEY,
        package_id          INT NOT NULL,
        FOREIGN KEY (package_id) REFERENCES work_packages(package_id),
        task_name           VARCHAR(255) NOT NULL,
        task_weight         VARCHAR(255) NULL,
        task_order          INT(20),
        task_duration       FLOAT(20),
        specialty_id        INT NULL, 
        FOREIGN KEY (specialty_id) REFERENCES app_specialties(specialty_id),
        task_type_id        INT NULL,
        FOREIGN KEY (task_type_id) REFERENCES work_package_task_types(type_id),
        parent_id           INT NULL,
        FOREIGN KEY (parent_id) REFERENCES work_package_tasks(task_id),
        is_active           BOOLEAN DEFAULT TRUE,
        created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (package_id,task_name)
    )',

    'CREATE TABLE IF NOT EXISTS work_package_applicability( 
        log_id              INT(20) AUTO_INCREMENT PRIMARY KEY,
        package_id          INT NULL, 
        FOREIGN KEY (package_id) REFERENCES work_packages(package_id),
        aircraft_id         INT NULL, 
        FOREIGN KEY (aircraft_id) REFERENCES app_aircraft(aircraft_id),
        is_active           BOOLEAN DEFAULT TRUE,
        created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (package_id,aircraft_id)
    )',

    'CREATE TABLE IF NOT EXISTS tasks_x_zones( 
        log_id              INT(20) AUTO_INCREMENT PRIMARY KEY,
        task_id             INT NULL, 
        FOREIGN KEY (task_id) REFERENCES work_package_tasks(task_id),
        zone_id             INT NULL, 
        FOREIGN KEY (zone_id) REFERENCES aircraft_zones(zone_id),
        is_active           BOOLEAN DEFAULT TRUE,
        created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (task_id,zone_id)
    )',

    'CREATE TABLE IF NOT EXISTS tasks_x_designators( 
        log_id              INT(20) AUTO_INCREMENT PRIMARY KEY,
        task_id             INT NULL, 
        FOREIGN KEY (task_id) REFERENCES work_package_tasks(task_id),
        designator_id         INT NULL, 
        FOREIGN KEY (designator_id) REFERENCES aircraft_designators(designator_id),
        is_active           BOOLEAN DEFAULT TRUE,
        created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_update         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (task_id,designator_id)
    )',
]);
