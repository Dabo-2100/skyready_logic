<?php
// Routes
$endpoints += [
    '/api/manufacturers'        => 'manufacturers_index',
    '/api/manufacturers/store'  => 'manufacturers_store',
    '/api/manufacturers/delete' => 'manufacturers_delete',
];

function manufacturers_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Manufacturers Are Ready To View';
        $response['data'] =  getRows("aircraft_manufacturers", "is_active = 1");
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function manufacturers_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $manufacturer_name = htmlspecialchars($POST_data["manufacturer_name"]);
            $manufacturer_id = insert_data("aircraft_manufacturers", ["manufacturer_name"], [$manufacturer_name]);
            if (is_null($manufacturer_id) == false) {
                $response['err'] = false;
                $response['msg'] = "New Manufacturer Added Successfully";
                $response['data'] = getRows("aircraft_manufacturers", "is_active = 1");
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

function manufacturers_delete()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $manufacturer_id = htmlspecialchars($POST_data["manufacturer_id"]);
            $manufacturer_id = delete_data("aircraft_manufacturers", "manufacturer_id = $manufacturer_id");
            if (is_null($manufacturer_id) == false) {
                $response['err'] = false;
                $response['msg'] = "Manufacturer Deleted Successfully";
                $response['data'] = getRows("aircraft_manufacturers", "is_active = 1");
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
