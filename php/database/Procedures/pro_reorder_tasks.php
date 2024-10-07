<?php
// $statements = array_merge($statements, [
//     'CREATE PROCEDURE reorder_tasks(
//         IN p_task_id INT,
//         IN p_package_id INT,
//         IN p_new_order INT,
//         IN p_old_order INT
//     )
//     BEGIN
//         DECLARE max_order INT;
    
//         -- Check if the task order has changed
//         IF p_new_order != p_old_order THEN
//             -- Get the maximum task order in the package
//             SELECT MAX(task_order) INTO max_order
//             FROM work_package_tasks
//             WHERE package_id = p_package_id;
    
//             -- Shift the task orders based on the new order
//             IF p_new_order < p_old_order THEN
//                 -- Move tasks between the new and old order down by 1
//                 UPDATE work_package_tasks
//                 SET task_order = task_order + 1
//                 WHERE task_order >= p_new_order 
//                   AND task_order < p_old_order 
//                   AND package_id = p_package_id;
//             ELSE
//                 -- Move tasks between the old and new order up by 1
//                 UPDATE work_package_tasks
//                 SET task_order = task_order - 1
//                 WHERE task_order > p_old_order 
//                   AND task_order <= p_new_order 
//                   AND package_id = p_package_id;
//             END IF;
    
//             -- Set the task order to the new value
//             UPDATE work_package_tasks
//             SET task_order = p_new_order
//             WHERE task_id = p_task_id;
//         END IF;
//     END'
// ]);
