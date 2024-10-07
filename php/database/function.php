<?php
$Functions = "
DELIMITER $$
CREATE FUNCTION `get_project_progress`(`project_id` INT) RETURNS float
    DETERMINISTIC
BEGIN
    DECLARE total_done_hrs FLOAT DEFAULT 0;  -- Declare the variable to hold the weighted progress sum
    DECLARE total_thrs FLOAT DEFAULT 0;      -- Declare the variable to hold the sum of task durations

    -- Calculate total done hours and total task hours for the specified project
    SELECT COALESCE(SUM(pt.task_progress * wpt.task_duration), 0), 
           COALESCE(SUM(wpt.task_duration), 0)
    INTO total_done_hrs, total_thrs
    FROM project_tasks pt
    JOIN work_package_tasks wpt ON pt.task_id = wpt.task_id
    WHERE pt.project_id = project_id
      AND pt.is_active = TRUE;

    -- Return the progress ratio, avoid division by zero
    RETURN IF(total_thrs = 0, 0, total_done_hrs / total_thrs);
END$$
DELIMITER ;

DELIMITER $$
CREATE FUNCTION `get_wp_progress`(`wp_log_id` INT) RETURNS float
    DETERMINISTIC
BEGIN
    DECLARE total_done_hrs FLOAT DEFAULT 0;  -- Declare the variable to hold the weighted progress sum
    DECLARE total_thrs FLOAT DEFAULT 0;      -- Declare the variable to hold the sum of task durations
    DECLARE package_id INT DEFAULT 0;
    DECLARE project_id INT DEFAULT 0;
    
    SELECT pwp.work_package_id ,pwp.project_id INTO package_id,project_id 
    FROM `project_work_packages` pwp 
    WHERE pwp.log_id = wp_log_id;
    
    -- Calculate total progress hours done and total task hours related to the package_id
    SELECT COALESCE(SUM(pt.task_progress * wpt.task_duration), 0), 
           COALESCE(SUM(wpt.task_duration), 0)
    INTO total_done_hrs, total_thrs
    FROM project_tasks pt
    JOIN work_package_tasks wpt ON pt.task_id = wpt.task_id
    WHERE 	wpt.package_id = package_id 
    AND 	pt.project_id = project_id
    AND 	wpt.is_active = TRUE;
    
    -- Return the progress ratio; if there are no durations, return 0 to avoid division by zero
    RETURN IF(total_thrs = 0, 0, total_done_hrs / total_thrs);
END$$
DELIMITER ;

";


$triggers = "
CREATE TRIGGER `after_insert_project` AFTER INSERT ON `app_projects`
 FOR EACH ROW BEGIN
    INSERT INTO project_progress_tracker (project_id, day_date, old_progress, new_progress) 
    VALUES (NEW.project_id, NOW(), 0, 0);
END

CREATE TRIGGER `after_insert_project_task` AFTER INSERT ON `project_tasks`
 FOR EACH ROW BEGIN
    DECLARE package_id INT;
    DECLARE log_id INT;
    -- Get the package_id of the inserted task from work_package_tasks
    SELECT wpt.package_id
    INTO package_id
    FROM work_package_tasks wpt
    WHERE wpt.task_id = NEW.task_id;
    -- Get the log_id 
    SELECT pwp.log_id
    INTO log_id
    FROM  `project_work_packages` pwp
    WHERE pwp.work_package_id = package_id AND pwp.project_id = NEW.project_id;

    -- Update the work_package_progress in project_work_packages using get_wp_progress function
    UPDATE project_work_packages pwp
    SET pwp.work_package_progress = get_wp_progress(log_id)
    WHERE pwp.log_id = log_id;
	 -- Update project_progress in app_projects using get_project_progress function
     UPDATE app_projects ap
     JOIN project_work_packages pwp ON pwp.project_id = ap.project_id
     SET ap.project_progress = get_project_progress(ap.project_id)
     WHERE pwp.work_package_id = package_id;
END

CREATE TRIGGER `after_insert_project_work_packages` AFTER INSERT ON `project_work_packages`
 FOR EACH ROW BEGIN 
    INSERT INTO work_package_progress_tracker (work_packages_log_id, day_date, old_progress, new_progress) 
    VALUES (NEW.log_id, NOW(), 0, 0); 

    INSERT INTO work_package_status_tracker (work_package_log_id, new_status_id) 
    VALUES (NEW.log_id, 1); 
END

CREATE TRIGGER `after_insert_task` AFTER INSERT ON `work_package_tasks`
 FOR EACH ROW BEGIN
    INSERT INTO project_tasks (task_id, project_id, status_id, task_start_at, task_end_at, task_progress, is_active)
    SELECT NEW.task_id, pwp.project_id, 1, NULL, NULL, 0, TRUE  -- Assuming '1' as the default status_id
    FROM project_work_packages pwp
    WHERE pwp.work_package_id = NEW.package_id;
    
    UPDATE work_packages
    SET package_duration = (
        SELECT COALESCE(SUM(task_duration), 0)
        FROM work_package_tasks
        WHERE package_id = NEW.package_id
    )
    WHERE package_id = NEW.package_id;
END

CREATE TRIGGER `after_update_project_task` AFTER UPDATE ON `project_tasks`
 FOR EACH ROW BEGIN
    DECLARE package_id INT;
    DECLARE log_id INT;
    -- Get the package_id of the inserted task from work_package_tasks
    SELECT wpt.package_id
    INTO package_id
    FROM work_package_tasks wpt
    WHERE wpt.task_id = NEW.task_id;
    -- Get the log_id 
    SELECT pwp.log_id
    INTO log_id
    FROM  `project_work_packages` pwp
    WHERE pwp.work_package_id = package_id AND pwp.project_id = NEW.project_id;

    -- Update the work_package_progress in project_work_packages using get_wp_progress function
    UPDATE project_work_packages pwp
    SET pwp.work_package_progress = get_wp_progress(log_id)
    WHERE pwp.log_id = log_id;
END

CREATE TRIGGER `after_update_task` AFTER UPDATE ON `work_package_tasks`
 FOR EACH ROW BEGIN
    -- Check if the task_duration was modified
    IF NEW.task_duration != OLD.task_duration THEN
        -- Update the package_duration in work_packages by summing task durations for the related package
        UPDATE work_packages wp
        SET wp.package_duration = (
            SELECT COALESCE(SUM(task_duration), 0)
            FROM work_package_tasks
            WHERE package_id = NEW.package_id
        )
        WHERE wp.package_id = NEW.package_id;

        -- Update the work_package_progress in project_work_packages using get_wp_progress function
        UPDATE project_work_packages
        SET work_package_progress = get_wp_progress(log_id)
        WHERE work_package_id = NEW.package_id;

        -- Update project_progress in app_projects using get_project_progress function
        UPDATE app_projects ap
        JOIN project_work_packages pwp ON pwp.project_id = ap.project_id
        SET ap.project_progress = get_project_progress(ap.project_id)
        WHERE pwp.work_package_id = NEW.package_id;
    END IF;
END

CREATE TRIGGER `before_insert_task` BEFORE INSERT ON `work_package_tasks`
 FOR EACH ROW BEGIN
    IF NEW.task_order IS NULL OR NEW.task_order = 0 THEN
        SET NEW.task_order = (
            SELECT IFNULL(MAX(task_order), 0) + 1 
            FROM work_package_tasks 
            WHERE package_id = NEW.package_id
        );
    END IF;
END

CREATE TRIGGER `before_update_project_task` BEFORE UPDATE ON `project_tasks`
 FOR EACH ROW BEGIN
    -- Check if the new task progress is 100
    IF NEW.task_progress = 100 AND NEW.is_active = 1 THEN
        SET NEW.status_id = 4;
    END IF;
END
";
