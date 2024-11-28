<?php
// Routes
$endpoints += [
    '/api/aircraft/task/zones'          => 'task_zones_index',
    '/api/aircraft/task/zones/\d+'      => 'task_zones_show',
    '/api/aircraft/task/zones/store'    => 'task_zones_store',
    '/api/aircraft/task/zones/delete'   => 'task_zones_delete',
    '/api/aircraft/task/vs/zones'       => 'tasks_vs_zones',
];

function task_zones_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Zones_x_Task Are Ready To View';
        $response['data'] =  getRows("tasks_x_zones", "is_active = 1");
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function task_zones_show($id)
{
    $task_id = explode("/aircraft/task/zones/", $id[0])[1];
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $zones_info = getRows("tasks_x_zones", "task_id = " . htmlspecialchars($task_id));
        if (isset($zones_info[0])) {
            $response['err'] = false;
            $response['msg'] = 'Zones Data is Ready To View';
            $response['data'] =  $zones_info;
        } else {
            $response['msg'] = 'Task id is wrong !';
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function task_zones_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $task_id = htmlspecialchars($POST_data["task_id"]);
            $zone_id = htmlspecialchars($POST_data["zone_id"]);
            $fields = ["task_id", "zone_id"];
            $values = ["$task_id", "$zone_id"];
            $log_id = insert_data("tasks_x_zones", $fields, $values);
            if (is_null($log_id) == false) {
                $response['err'] = false;
                $response['msg'] = "New Zone Added Successfully to The Task";
                $response['data'] = getRows("tasks_x_zones", "is_active = 1 AND task_id = {$task_id}");
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

function task_zones_delete()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $zone_id = htmlspecialchars($POST_data["zone_id"]);
            $zone_id = delete_data("aircraft_zones", "zone_id = $zone_id");
            if (is_null($zone_id) == false) {
                $response['err'] = false;
                $response['msg'] = "Zone Deleted Successfully";
                $response['data'] = getRows("aircraft_zones", "is_active = 1");
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

function tasks_vs_zones()
{
    global $method, $POST_data, $pdo, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        $zones_id = htmlspecialchars($POST_data["zones_id"]);
        $sql = "WITH RECURSIVE zone_hierarchy AS 
        (SELECT az.* FROM aircraft_zones az WHERE az.zone_id IN ({$zones_id})
        UNION ALL SELECT az.* FROM aircraft_zones az INNER JOIN zone_hierarchy ch ON az.parent_id = ch.zone_id) 
        -- SELECT DISTINCT zone_id FROM zone_hierarchy
        SELECT * FROM tasks_x_zones txz
        WHERE txz.zone_id IN (SELECT DISTINCT zone_id FROM zone_hierarchy)
        ";
        $statement = $pdo->prepare($sql);
        $statement->execute();
        $final = [];
        if ($statement->rowCount() > 0) {
            while ($el = $statement->fetch(PDO::FETCH_ASSOC)) {
                $returnObj = ['task_id' => $el['task_id']];
                $returnObj['task_name'] = getOneField("work_package_tasks", "task_name", "task_id = {$el['task_id']}");
                $returnObj['task_duration'] = getOneField("work_package_tasks", "task_duration", "task_id = {$el['task_id']}");
                $returnObj['specialty_id'] = getOneField("work_package_tasks", "specialty_id", "task_id = {$el['task_id']}");
                $returnObj['specialty_name'] = getOneField("app_specialties", "specialty_name", "specialty_id = {$returnObj['specialty_id']}");
                $returnObj['package_id'] = getOneField("work_package_tasks", "package_id", "task_id = {$el['task_id']}");
                $returnObj['package_name'] = getOneField("work_packages", "package_name", "package_id = {$returnObj['package_id']}");
                $returnObj['parent_name'] = getOneField("work_packages", "package_name", "package_id = {$returnObj['package_id']}");
                array_push($final, $returnObj);
            }
        }
        $response['data'] = $final;
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}
