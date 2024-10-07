<?php
// Routes
$endpoints += [
    '/api/aircraftstatus'        => 'aircraftstatus_index',
    '/api/aircraftstatus/store'  => 'aircraftstatus_store',
    '/api/aircraftstatus/delete' => 'aircraftstatus_delete',
];

function aircraftstatus_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Status Are Ready To View';
        $response['data'] =  getRows("aircraft_status", "is_active = 1");
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function aircraftstatus_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $status_name = htmlspecialchars($POST_data["status_name"]);
            $status_id = insert_data("aircraft_status", ["status_name"], [$status_name]);
            if (is_null($status_id) == false) {
                $response['err'] = false;
                $response['msg'] = "New Status Added Successfully";
                $response['data'] = getRows("aircraft_status", "is_active = 1");
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

function aircraftstatus_delete()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $status_id = htmlspecialchars($POST_data["status_id"]);
            $status_id = delete_data("aircraft_status", "status_id = $status_id");
            if (is_null($status_id) == false) {
                $response['err'] = false;
                $response['msg'] = "Status Deleted Successfully";
                $response['data'] = getRows("aircraft_status", "is_active = 1");
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
