<?php
// Routes
$endpoints += [
    '/api/project/status'        => 'projectStatus_index',
    '/api/project/status/store'  => 'projectStatus_store',
    '/api/project/status/delete' => 'projectStatus_delete',
];

function projectStatus_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Status Are Ready To View';
        $response['data'] =  getRows("project_status", "is_active = 1");
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function projectStatus_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $data = [
                "status_name"           => htmlspecialchars($POST_data["status_name"]),
                "status_color_code"     => isset($POST_data['status_color_code']) ? htmlspecialchars($POST_data['status_color_code']) : null,
            ];
            $status_id = insert_data("project_status", array_keys($data), array_values($data));
            if (is_null($status_id) == false) {
                $response['err'] = false;
                $response['msg'] = "New Status Added Successfully";
                $response['data'] = getRows("project_status", "is_active = 1");
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

function projectStatus_delete()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $status_id = htmlspecialchars($POST_data["status_id"]);
            $status_id = delete_data("project_status", "status_id = $status_id");
            if (is_null($status_id) == false) {
                $response['err'] = false;
                $response['msg'] = "Status Deleted Successfully";
                $response['data'] = getRows("project_status", "is_active = 1");
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
