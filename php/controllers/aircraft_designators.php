<?php
// Routes
$endpoints += [
    '/api/aircraft/designators'        => 'designators_index',
    '/api/aircraft/designators/\d+'    => 'designators_show',
    '/api/aircraft/designators/store'  => 'designators_store',
    '/api/aircraft/designators/delete' => 'designators_delete',
    '/api/aircraft/designators/search' => 'designators_search',
];

function designators_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Designators Are Ready To View';
        $designtros =  getRows("aircraft_designators", "is_active = 1");
        $response['data'] = array_map(function ($el) {
            $el['model_name'] = getOneField("aircraft_models", "model_name", "model_id = {$el['model_id']}");
            $el['type_name'] = getOneField("designator_types", "type_name", "type_id = {$el['type_id']}");
            return $el;
        }, $designtros);

        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function designators_show($id)
{
    $model_id = explode("/api/aircraft/designators/", $id[0])[1];
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $designtros = getRows("aircraft_designators", "is_active = 1 AND model_id = {$model_id}");
        $response['err'] = false;
        $response['msg'] = "New Designator Added Successfully";
        $response['data'] = array_map(function ($el) {
            $el['model_name'] = getOneField("aircraft_models", "model_name", "model_id = {$el['model_id']}");
            $el['type_name'] = getOneField("designator_types", "type_name", "type_id = {$el['type_id']}");
            return $el;
        }, $designtros);
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function designators_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $designator_name = htmlspecialchars($POST_data["designator_name"]);
            $model_id = htmlspecialchars($POST_data["model_id"]);
            $type_id = htmlspecialchars($POST_data["type_id"]);
            $designator_id = insert_data("aircraft_designators", ["designator_name", "model_id", "type_id"], [$designator_name, $model_id, $type_id]);
            if (is_null($designator_id) == false) {
                $designtros = getRows("aircraft_designators", "is_active = 1 AND model_id = {$model_id}");
                $response['err'] = false;
                $response['msg'] = "New Designator Added Successfully";
                $response['data'] = array_map(function ($el) {
                    $el['model_name'] = getOneField("aircraft_models", "model_name", "model_id = {$el['model_id']}");
                    $el['type_name'] = getOneField("designator_types", "type_name", "type_id = {$el['type_id']}");
                    return $el;
                }, $designtros);
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

function designators_delete()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $designator_id = htmlspecialchars($POST_data["designator_id"]);
            $model_id = getOneField("aircraft_designators", "model_id", "designator_id = {$designator_id}");
            $designator_id = delete_data("aircraft_designators", "designator_id = $designator_id");
            if (is_null($designator_id) == false) {
                $response['err'] = false;
                $response['msg'] = "Status Deleted Successfully";
                $response['data'] = getRows("aircraft_designators", "is_active = 1 AND model_id = {$model_id}");
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

function designators_search()
{
    global $method, $response, $POST_data;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $filter_by =  htmlspecialchars($POST_data["filter_by"]);
            $filter_val = htmlspecialchars($POST_data["filter_val"]);
            $response['err'] = false;
            $response['msg'] = 'All Status Are Ready To View';
            $response['data'] =  array_map(function ($el) {
                $el['model_name'] = getOneField("aircraft_models", "model_name", "model_id = {$el['model_id']}");
                $el['type_name'] = getOneField("designator_types", "type_name", "type_id = {$el['type_id']}");
                return $el;
            }, getRows(
                "aircraft_designators",
                "is_active = 1 AND {$filter_by} LIKE '%{$filter_val}%' "
            ));
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
