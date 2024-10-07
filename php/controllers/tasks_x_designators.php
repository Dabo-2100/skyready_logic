<?php
// Routes
$endpoints += [
    '/api/aircraft/task/designators'          => 'task_designators_index',
    '/api/aircraft/task/designators/\d+'      => 'task_designators_show',
    '/api/aircraft/task/designators/store'    => 'task_designators_store',
    '/api/aircraft/task/designators/delete'   => 'task_designators_delete',
];

function task_designators_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All designators_x_Task Are Ready To View';
        $response['data'] =  getRows("tasks_x_designators", "is_active = 1");
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function task_designators_show($id)
{
    $task_id = explode("/aircraft/task/designators/", $id[0])[1];
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $designators_info = getRows("tasks_x_designators", "task_id = " . htmlspecialchars($task_id));
        if (isset($designators_info[0])) {
            $response['err'] = false;
            $response['msg'] = 'designators Data is Ready To View';
            $response['data'] =  $designators_info;
        } else {
            $response['msg'] = 'Task id is wrong !';
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function task_designators_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $task_id = htmlspecialchars($POST_data["task_id"]);
            $designator_id = htmlspecialchars($POST_data["designator_id"]);
            $fields = ["task_id", "designator_id"];
            $values = ["$task_id", "$designator_id"];
            $log_id = insert_data("tasks_x_designators", $fields, $values);
            if (is_null($log_id) == false) {
                $response['err'] = false;
                $response['msg'] = "New designator Added Successfully to The Task";
                $response['data'] = getRows("tasks_x_designators", "is_active = 1 AND task_id = {$task_id}");
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

function task_designators_delete()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $designator_id = htmlspecialchars($POST_data["designator_id"]);
            $designator_id = delete_data("aircraft_designators", "designator_id = $designator_id");
            if (is_null($designator_id) == false) {
                $response['err'] = false;
                $response['msg'] = "designator Deleted Successfully";
                $response['data'] = getRows("aircraft_designators", "is_active = 1");
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
