<?php
// Routes
$endpoints += [
    '/api/workpackage/tasks/types'                  => 'tasktypes_index',
    '/api/workpackage/tasks/types/specailty/\d+'    => 'tasktypes_show',
    '/api/workpackage/tasks/types/store'            => 'tasktypes_store',
    '/api/workpackage/tasks/types/delete'           => 'tasktypes_delete',
];

function tasktypes_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Status Are Ready To View';
        $response['data'] =  getRows("work_package_task_types", "is_active = 1");
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function tasktypes_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $type_name = htmlspecialchars($POST_data["type_name"]);
            $specialty_id = htmlspecialchars($POST_data["specialty_id"]);
            $task_type_id = insert_data("work_package_task_types", ["specialty_id", "type_name"], [$specialty_id, $type_name]);
            if (is_null($task_type_id) == false) {
                $response['err'] = false;
                $response['msg'] = "New Status Added Successfully";
                $response['data'] = getRows("work_package_task_types", "is_active = 1 AND specialty_id = {$specialty_id}");
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

function tasktypes_show($id)
{
    // i will show members and task types
    $specialty_id = explode("/tasks/types/specailty/", $id[0])[1];
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $Task_Types_info = getRows("work_package_task_types", "specialty_id =" . htmlspecialchars($specialty_id));
        if (count($Task_Types_info) != 0) {
            $response['err'] = false;
            $response['msg'] = 'All Task Types is Ready To View';
            $response['data'] =  $Task_Types_info;
        } else {
            $response['msg'] = 'Specailty id is wrong !';
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function tasktypes_delete()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $type_id = htmlspecialchars($POST_data["type_id"]);
            $specialty_id = getOneField("work_package_task_types", "specialty_id", "type_id = {$type_id}");

            $type_del = delete_data("work_package_task_types", "type_id = {$type_id}");
            if (is_null($type_del) == false) {
                $response['err'] = false;
                $response['msg'] = "Type Deleted Successfully";
                $response['data'] = getRows("work_package_task_types", "is_active = 1 AND specialty_id = {$specialty_id}");
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
