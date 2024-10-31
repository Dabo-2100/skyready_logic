<?php
// Routes
$endpoints += [
    '/api/aircraft/zones'        => 'zones_index',
    '/api/aircraft/zones/\d+'    => 'zones_show',
    '/api/aircraft/zones/store'  => 'zones_store',
    '/api/aircraft/zones/delete' => 'zones_delete',
];

function zones_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Zones Are Ready To View';
        $response['data'] =  getRows("aircraft_zones", "is_active = 1");
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function zones_show($id)
{
    $model_id = explode("/api/aircraft/zones/", $id[0])[1];
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $zones_info = getRows("aircraft_zones", "model_id=" . htmlspecialchars($model_id));
        if (isset($zones_info[0])) {
            $response['err'] = false;
            $response['msg'] = 'Zones Data is Ready To View';
            $response['data'] =  $zones_info;
        } else {
            $response['msg'] = 'Model id is wrong !';
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function zones_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $model_id = htmlspecialchars($POST_data["model_id"]);
            $zone_name = htmlspecialchars($POST_data["zone_name"]);
            $parent_id = isset($POST_data["parent_id"]) ? htmlspecialchars($POST_data["parent_id"]) : NULL;
            $sta_value = isset($POST_data["sta_value"]) ? htmlspecialchars($POST_data["sta_value"]) : '0';
            $wl_value = isset($POST_data["wl_value"]) ? htmlspecialchars($POST_data["wl_value"]) : '0';
            $bl_value = isset($POST_data["bl_value"]) ? htmlspecialchars($POST_data["bl_value"]) : '0';
            $fields = ["model_id", "zone_name"];
            $values = ["$model_id", "$zone_name"];
            if (is_null($parent_id) == false) {
                array_push($fields, "parent_id");
                array_push($values, $parent_id);
            }
            $zone_id = insert_data("aircraft_zones", $fields, $values);
            if (is_null($zone_id) == false) {
                $response['err'] = false;
                $response['msg'] = "New Zone Added Successfully";
                $response['data'] = getRows("aircraft_zones", "is_active = 1 AND model_id = {$model_id}");
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

function zones_delete()
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
