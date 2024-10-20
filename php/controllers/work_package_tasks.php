<?php
// Routes
$endpoints += [
    '/api/workpackage/tasks'            => 'workpackge_tasks_index',
    '/api/workpackage/tasks/\d+'        => 'workpackge_tasks_show',
    '/api/workpackage/tasks/store'      => 'workpackge_tasks_store',
    '/api/workpackage/tasks/delete'     => 'workpackge_tasks_delete',
];

function workpackge_tasks_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Tasks Are Ready To View';
        $response['data'] =  getRows("work_package_tasks", "is_active = 1");
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function workpackge_tasks_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $package_id = htmlspecialchars($POST_data["package_id"]);
            $task_name = htmlspecialchars($POST_data["task_name"]);
            $task_duration = htmlspecialchars($POST_data["task_duration"]);
            $specialty_id = htmlspecialchars($POST_data["specialty_id"]);
            $task_type_id = htmlspecialchars($POST_data["task_type_id"]);
            $fields = ["package_id", "task_name", "task_duration", "specialty_id", "task_type_id"];
            $values = [$package_id, $task_name, $task_duration, $specialty_id, $task_type_id];
            if (isset($POST_data["parent_id"])) {
                array_push($fields, "parent_id");
                array_push($values, $POST_data["parent_id"]);
            }

            if (isset($POST_data["task_order"])) {
                array_push($fields, "task_order");
                array_push($values, $POST_data["task_order"]);
            }
            $status_id = insert_data("work_package_tasks", $fields, $values);
            if (is_null($status_id) == false) {
                $response['task_id'] =  $status_id;
                $response['err'] = false;
                $response['msg'] = "New Task Added Successfully";
                $response['data'] = getRows("work_package_tasks", "package_id = {$package_id} AND is_active = 1");
            }
            echo json_encode($response, true);
        } else {
            echo "Error : 401 | No Authority";
            http_response_code(401);
            exit();
        }
    } else {
        echo 'Method Not Allowed';
    }
}


function workpackge_tasks_delete()
{
    global $method, $POST_data, $pdo, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info) {
            $task_id = $POST_data['task_id'];
            $package_id = getOneField("work_package_tasks", "package_id", "task_id = {$task_id}");
            $affected_projects = getRows("project_tasks", "task_id = {$task_id}");
            $affected_wps = array_map(function ($project) {
                global $POST_data;
                $package_id = getOneField("work_package_tasks", "package_id", "task_id = {$POST_data['task_id']}");
                $project_id = $project['project_id'];
                return getOneField("project_work_packages", "log_id", "work_package_id = {$package_id} AND project_id ={$project_id}");
            }, $affected_projects);
            $sql = "
                START TRANSACTION;
                    DELETE FROM task_comments WHERE log_id IN (SELECT log_id FROM `project_tasks` WHERE task_id = {$task_id}) AND parent_id IS NOT NULL;
                    DELETE FROM task_comments WHERE log_id IN (SELECT log_id FROM `project_tasks` WHERE task_id = {$task_id}) AND parent_id IS NULL;
                    DELETE FROM tasks_x_zones WHERE task_id = {$task_id};
                    DELETE FROM tasks_x_tags WHERE task_id = {$task_id};
                    DELETE FROM project_tasks WHERE task_id = {$task_id};
                    DELETE FROM work_package_tasks WHERE task_id = {$task_id};
                COMMIT;
            ";
            $statement = $pdo->prepare($sql);
            $statement->execute();

            foreach ($affected_wps as $index => $wp_id) {
                $sql = "UPDATE project_work_packages 
                SET work_package_progress = get_wp_progress($wp_id) 
                WHERE log_id = {$wp_id}";
                $statement = $pdo->prepare($sql);
                $statement->execute();
            }

            foreach ($affected_projects as $index => $project) {
                $project_id = $project['project_id'];
                $sql = "UPDATE app_projects 
                SET project_progress = get_project_progress($project_id) 
                WHERE project_id = {$project_id}";
                $statement = $pdo->prepare($sql);
                $statement->execute();
            }

            $response['err'] = false;
            $response['msg'] = "Task Deleted Successfully";
            echo json_encode($response, true);
        } else {
            echo "Error : 401 | No Authority";
            http_response_code(401);
            exit();
        }
    } else {
        echo 'Method Not Allowed';
    }
}


function workpackge_tasks_show($id)
{
    $task_id = explode("/workpackage/tasks/", $id[0])[1];
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $task_info = getRows("work_package_tasks", "task_id = " . htmlspecialchars($task_id));
        if (isset($task_info[0])) {
            $response['err'] = false;
            $response['msg'] = 'Task Data is Ready To View';
            $response['data'] = array_map(function ($task) {
                $tasks_x_zones = getRows("tasks_x_zones", "task_id=" . $task['task_id']);
                $tasks_x_designators = getRows("tasks_x_designators", "task_id=" . $task['task_id']);
                $task['selected_zones'] = array_map(function ($zone) {
                    if ($zone['is_active'] == 1) {
                        return [
                            'zone_id' => $zone['zone_id'],
                            'zone_name' => getOneField("aircraft_zones", "zone_name", "zone_id =" . $zone['zone_id']),
                        ];
                    }
                }, $tasks_x_zones);

                $task['selected_designators'] = array_map(function ($zone) {
                    if ($zone['is_active'] == 1) {
                        return [
                            'designator_id' => $zone['designator_id'],
                            'designator_name' => getOneField("aircraft_designators", "designator_name", "designator_id =" . $zone['designator_id']),
                        ];
                    }
                }, $tasks_x_designators);

                return $task;
            }, $task_info);
        } else {
            $response['msg'] = 'Task id is wrong !';
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}
