<?php
// Routes
$endpoints += [
    '/api/aircraft/task/zones'          => 'task_zones_index',
    '/api/aircraft/task/zones/\d+'      => 'task_zones_show',
    '/api/aircraft/task/zones/store'    => 'task_zones_store',
    '/api/aircraft/task/zones/delete'   => 'task_zones_delete',
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
