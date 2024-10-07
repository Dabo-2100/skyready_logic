<?php
// Routes
$endpoints += [
    '/api/specialties'        => 'specialties_index',
    '/api/specialties/\d+'    => 'specialties_show',
    '/api/specialties/store'  => 'specialties_store',
    '/api/specialties/delete' => 'specialties_delete',
];

function specialties_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All specialties Are Ready To View';
        $response['data'] = array_map(function ($el) {
            $el['count'] = count(getRows("app_users", "specialty_id = {$el['specialty_id']}"));
            return $el;
        }, getRows("app_specialties", "is_active = 1"));
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function specialties_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $specialty_name = htmlspecialchars($POST_data["specialty_name"]);
            $specialty_id = insert_data("app_specialties", ["specialty_name"], [$specialty_name]);
            if (is_null($specialty_id) == false) {
                $response['err'] = false;
                $response['msg'] = "New Specialty Added Successfully";
                $response['data'] = getRows("app_specialties", "is_active = 1");
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

function specialties_show($id)
{
    // i will show members and task types
    $specialty_id = explode("/api/specialties/", $id[0])[1];
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $specialty_info = getRows("app_specialties", "specialty_id=" . htmlspecialchars($specialty_id));
        if (isset($specialty_info[0])) {
            $response['err'] = false;
            $response['msg'] = 'Aircraft Data is Ready To View';
            $response['data'] =  [
                'info' => $specialty_info,
                'members' => getRows("app_users", "specialty_id = {$specialty_id}"),
                'work_package_task_types' => getRows("work_package_task_types", "specialty_id = {$specialty_id}"),
            ];
        } else {
            $response['msg'] = 'Model id is wrong !';
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function specialties_delete()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $specialty_id = htmlspecialchars($POST_data["specialty_id"]);
            $specialty_id = delete_data("app_specialties", "specialty_id = $specialty_id");
            if (is_null($specialty_id) == false) {
                $response['err'] = false;
                $response['msg'] = "Status Deleted Successfully";
                $response['data'] = getRows("app_specialties", "is_active = 1");
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
