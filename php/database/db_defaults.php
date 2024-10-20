<?php
$statements = array_merge($statements, [
    'INSERT IGNORE INTO app_roles (role_id,role_name) VALUES (1,"super"),(2,"admin"),(3,"user")',

    'INSERT IGNORE INTO app_apps 
        (app_id,app_name,app_icon,app_order) VALUES 
        (1,"Fleet Manager","fa-jet-fighter",1),
        (2,"Projects Manager","fa-calendar",2),
        (3,"Report Manager","fa-list-check",3),
        (4,"Form Manager","fa-book",4),
        (5,"Warehouse Manager","fa-house",5),
        (6,"Users Manager","fa-users",6)
    ',

    'INSERT IGNORE INTO app_specialties 
        (specialty_id,specialty_name) VALUES 
        (1,"Planning"),
        (2,"Airframe"),
        (3,"Structure"),
        (4,"Avionics")
    ',

    'INSERT IGNORE INTO project_status 
        (status_id,status_name) VALUES 
        (1,"Not Started"),
        (2,"In Progress"),
        (3,"On Hold"),
        (4,"Completed"),
        (5,"N/A"),
        (6,"Waiting For Spare parts"),
        (7,"Waiting For Installation"),
        (8,"Waiting For Manpower"),
        (9,"Waiting For Findings"),
        (10,"Mixed Task"),
        (11,"Waiting For Tools"),
        (12,"Waiting For Mail")
    ',
]);
