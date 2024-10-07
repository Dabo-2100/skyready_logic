<?php
// $statements = array_merge($statements, [
//     '
            // CREATE TRIGGER `after_insert_project_work_packages` 
            // AFTER INSERT ON project_work_packages FOR EACH ROW 
            // BEGIN 
            // INSERT INTO work_package_progress_tracker (work_packages_log_id, day_date, old_progress, new_progress) 
            // VALUES (NEW.log_id, NOW(), 0, 0); 
            
            // INSERT INTO work_package_status_tracker (work_package_log_id, new_status_id) 
            // VALUES (NEW.log_id, 1); 
            // END;
//     ',
// ]);
