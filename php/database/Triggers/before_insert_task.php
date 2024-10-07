<?php
// $statements = array_merge($statements, [
//     '
// DELIMITER $$
// CREATE TRIGGER `before_insert_task` 
// BEFORE INSERT ON `work_package_tasks` 
// FOR EACH ROW 
// BEGIN
//     IF NEW.task_order IS NULL OR NEW.task_order = 0 THEN
//         SET NEW.task_order = (
//             SELECT IFNULL(MAX(task_order), 0) + 1 
//             FROM work_package_tasks 
//             WHERE package_id = NEW.package_id
//         );
//     END IF;
// END$$
// DELIMITER ;
//     ',
// ]);
