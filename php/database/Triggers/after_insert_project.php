<?php
// $statements = array_merge($statements, [
//     '
            // CREATE TRIGGER `after_insert_project` 
            // AFTER INSERT ON app_projects FOR EACH ROW 
            // BEGIN 
                // INSERT INTO project_progress_tracker (project_id, day_date, old_progress, new_progress) VALUES 
                // (NEW.project_id, NOW(), 0, 0); 
            // END;
//     ',
// ]);
