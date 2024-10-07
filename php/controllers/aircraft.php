<?php
// Routes
$endpoints += [
    '/api/aircraft'         => 'aircraft_index',
    '/api/aircraft/\d+'    => 'aircraft_show',
    '/api/aircraft/store'  => 'aircraft_store',
    '/api/aircraft/update'  => 'aircraft_update',
];

function aircraft_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Aircraft Are Ready To View';
        $response['data'] =  array_map(function ($el) {
            $el['status_name'] = getOneField("aircraft_status", "status_name", "status_id = " . $el['status_id']);
            $el['usage_name'] = getOneField("aircraft_usags", "usage_name", "usage_id = " . $el['usage_id']);
            $el['manufacturer_name'] = getOneField("aircraft_manufacturers", "manufacturer_name", "manufacturer_id = " . $el['manufacturer_id']);
            $el['model_name'] = getOneField("aircraft_models", "model_name", "model_id = " . $el['model_id']);
            return $el;
        }, getRows("app_aircraft", "is_active = 1"));
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function aircraft_show($id)
{
    $aircraft_id = explode("/api/aircraft/", $id[0])[1];
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $aircraft_info = getRows("app_aircraft", "aircraft_id=" . htmlspecialchars($aircraft_id));
        if (isset($aircraft_info[0])) {
            $response['err'] = false;
            $response['msg'] = 'Aircraft Data is Ready To View';
            $response['data'] =  array_map(function ($el) {
                return [
                    'aircraft_id'               => $el['aircraft_id'],
                    'aircraft_serial_no'        => $el['aircraft_serial_no'],
                    'aircraft_register_no'      => $el['aircraft_register_no'],
                    'aircraft_manufacture_date' => $el['aircraft_manufacture_date'],
                    'aircraft_flight_hours'     => $el['aircraft_flight_hours'],
                    'status_id'                 => $el['status_id'],
                    'status_name'               => getOneField("aircraft_status", "status_name", "status_id = " . $el['status_id']),
                    'usage_id'                  => $el['usage_id'],
                    'usage_name'                => getOneField("aircraft_usags", "usage_name", "usage_id = " . $el['usage_id']),
                    'manufacturer_id'           => $el['manufacturer_id'],
                    'manufacturer_name'         => getOneField("aircraft_manufacturers", "manufacturer_name", "manufacturer_id = " . $el['manufacturer_id']),
                    'model_id'                  => $el['model_id'],
                    'model_name'                => getOneField("aircraft_models", "model_name", "model_id = " . $el['model_id']),
                ];
            }, $aircraft_info);
        } else {
            $response['msg'] = 'Aircraft id is wrong !';
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function aircraft_store()
{
    // Who can use this function 
    // Super_User , Specialty Admin
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $aircraft_serial_no = htmlspecialchars($POST_data["aircraft_serial_no"]);
            $aircraft_register_no = htmlspecialchars($POST_data["aircraft_register_no"]);
            $model_id = htmlspecialchars($POST_data["model_id"]);
            $manufacturer_id = htmlspecialchars($POST_data["manufacturer_id"]);
            $status_id = htmlspecialchars($POST_data["status_id"]);
            $usage_id = htmlspecialchars($POST_data["usage_id"]);
            $aircraft_manufacture_date = htmlspecialchars($POST_data["aircraft_manufacture_date"]);
            $aircraft_flight_hours = htmlspecialchars($POST_data["aircraft_flight_hours"]);

            $aircraft_id = insert_data(
                "app_aircraft",
                ["aircraft_serial_no", "aircraft_register_no", "model_id", "manufacturer_id", "status_id", "usage_id", "aircraft_manufacture_date", "aircraft_flight_hours"],
                [$aircraft_serial_no,  $aircraft_register_no,  $model_id,  $manufacturer_id,  $status_id,  $usage_id,  $aircraft_manufacture_date,  $aircraft_flight_hours]
            );

            if (isset($aircraft_id)) {
                $response['err'] = false;
                $response['msg'] = "New Aircraft Added Successfully";
                $response['data'] = [
                    'aircraft_id' => $aircraft_id
                ];
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

function aircraft_update()
{
    global $method, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $aircraft_update = update_data();
            if (isset($aircraft_update)) {
                $response['err'] = false;
                $response['msg'] = "Aircraft Updated Successfully";
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
