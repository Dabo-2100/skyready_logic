<?php
// Routes
$endpoints += [
    '/api/aircraftusages'        => 'aircraftusages_index',
    '/api/aircraftusages/store'  => 'aircraftusages_store',
    '/api/aircraftusages/delete' => 'aircraftusages_delete',
];

function aircraftusages_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Model Are Ready To View';
        $response['data'] =  getRows("aircraft_usags", "is_active = 1");
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function aircraftusages_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $usage_name = htmlspecialchars($POST_data["usage_name"]);
            $usage_id = insert_data("aircraft_usags", ["usage_name"], [$usage_name]);
            if (is_null($usage_id) == false) {
                $response['err'] = false;
                $response['msg'] = "New Model Added Successfully";
                $response['data'] = getRows("aircraft_usags", "is_active = 1");
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

function aircraftusages_delete()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $usage_id = htmlspecialchars($POST_data["usage_id"]);
            $usage_id = delete_data("aircraft_usags", "usage_id = $usage_id");
            if (is_null($usage_id) == false) {
                $response['err'] = false;
                $response['msg'] = "Model Deleted Successfully";
                $response['data'] = getRows("aircraft_usags", "is_active = 1");
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
