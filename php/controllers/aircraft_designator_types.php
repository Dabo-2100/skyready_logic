<?php
// Routes
$endpoints += [
    '/api/aircraft/designators/types'        => 'designatorTypes_index',
    '/api/aircraft/designators/types/store'  => 'designatorTypes_store',
    '/api/aircraft/designators/types/delete' => 'designatorTypes_delete',
];

function designatorTypes_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Types Are Ready To View';
        $response['data'] =  getRows("designator_types", "is_active = 1 ORDER BY type_name");
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function designatorTypes_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $type_name = htmlspecialchars($POST_data["type_name"]);
            $type_id = insert_data("designator_types", ["type_name"], [$type_name]);
            if (is_null($type_id) == false) {
                $response['err'] = false;
                $response['msg'] = "New Designator Type Added Successfully";
                $response['data'] = getRows("designator_types", "is_active = 1");
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

function designatorTypes_delete()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $type_id = htmlspecialchars($POST_data["type_id"]);
            $type_id = delete_data("designator_types", "type_id = $type_id");
            if (is_null($type_id) == false) {
                $response['err'] = false;
                $response['msg'] = "Designators Deleted Successfully";
                $response['data'] = getRows("designator_types", "is_active = 1");
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
