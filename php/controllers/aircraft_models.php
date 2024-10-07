<?php
// Routes
$endpoints += [
    '/api/aircraftmodels'        => 'aircraftmodels_index',
    '/api/aircraftmodels/\d+'    => 'aircraftmodels_show',
    '/api/aircraftmodels/store'  => 'aircraftmodels_store',
    '/api/aircraftmodels/delete' => 'aircraftmodels_delete',
];

function aircraftmodels_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Model Are Ready To View';
        $response['data'] =  getRows("aircraft_models", "is_active = 1");
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function aircraftmodels_show($id)
{
    $model_id = explode("/api/aircraftmodels/", $id[0])[1];
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $aircraft_info = getRows("app_aircraft", "model_id=" . htmlspecialchars($model_id));
        if (isset($aircraft_info[0])) {
            $response['err'] = false;
            $response['msg'] = 'Aircraft Data is Ready To View';
            $response['data'] =  $aircraft_info;
        } else {
            $response['msg'] = 'Model id is wrong !';
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function aircraftmodels_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $model_name = htmlspecialchars($POST_data["model_name"]);
            $model_id = insert_data("aircraft_models", ["model_name"], [$model_name]);
            if (is_null($model_id) == false) {
                $response['err'] = false;
                $response['msg'] = "New Model Added Successfully";
                $response['data'] = getRows("aircraft_models", "is_active = 1");
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

function aircraftmodels_delete()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $model_id = htmlspecialchars($POST_data["model_id"]);
            $model_id = delete_data("aircraft_models", "model_id = $model_id");
            if (is_null($model_id) == false) {
                $response['err'] = false;
                $response['msg'] = "Model Deleted Successfully";
                $response['data'] = getRows("aircraft_models", "is_active = 1");
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
