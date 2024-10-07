<?php
// Routes
$endpoints += [
    '/api/packages'        => 'packages_index',
    '/api/packages/\d+'    => 'packages_show',
    '/api/packages/store'  => 'packages_store',
    '/api/packages/delete' => 'packages_delete',

    '/api/packages/types'        => 'packages_type_index',
    '/api/packages/types/\d+'    => 'packages_type_show',
    '/api/packages/types/store'  => 'packages_type_store',
    '/api/packages/types/delete' => 'packages_type_delete',
];


function packages_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Work Packages Are Ready To View';
        $response['data'] =  getRows("work_packages", "is_active = 1 ORDER BY package_name");
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function packages_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $package_name = @htmlspecialchars($POST_data["package_name"]);
            $is_folder = @htmlspecialchars($POST_data["is_folder"]);
            $package_duration = @htmlspecialchars($POST_data["package_duration"]);
            $package_issued_duration = @htmlspecialchars($POST_data["package_issued_duration"]);
            $package_type_id = @htmlspecialchars($POST_data["package_type_id"]);
            $package_desc = @htmlspecialchars($POST_data["package_desc"]);
            $package_version = @htmlspecialchars($POST_data["package_version"]);
            $package_release_date = @htmlspecialchars($POST_data["package_release_date"]);
            $model_id = @htmlspecialchars($POST_data["model_id"]);
            $fields = ["package_name", "package_duration", "package_issued_duration", "package_type_id", "package_desc", "package_version", "package_release_date", "is_folder"];
            $values = [$package_name, $package_duration, $package_issued_duration, $package_type_id, $package_desc, $package_version, $package_release_date, $is_folder];

            if (isset($POST_data["parent_id"])) {
                $parent_id = htmlspecialchars($POST_data["parent_id"]);
                array_push($fields, "parent_id");
                array_push($values, $parent_id);
            }
            if (isset($POST_data["model_id"])) {
                $model_id = htmlspecialchars($POST_data["model_id"]);
                array_push($fields, "model_id");
                array_push($values, $model_id);
            }
            $package_id = insert_data("work_packages", $fields, $values);

            if (is_null($package_id) == false) {
                $response['err'] = false;
                $response['msg'] = "New Work Package Added Successfully";
                $response['data'] = getRows("work_packages", "is_active = 1");
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

function packages_delete()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $package_id = htmlspecialchars($POST_data["package_id"]);
            $sons = get_heriarcy("work_packages", "package_id", $package_id);
            if (count($sons) == 1) {
                $removeIndex = delete_data("work_packages", "package_id = $package_id");
            } else {
                foreach ($sons as $index => $package) {
                    $package_id = $package['package_id'];
                    $removeIndex = delete_data("work_packages", "package_id = $package_id");
                }
            }
            $response['err'] = false;
            $response['msg'] = "Package Deleted Successfully";
            $response['data'] = getRows("work_packages", "is_active = 1");
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

function packages_show($id)
{
    $package_id = explode("/api/packages/", $id[0])[1];
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $package_info = getRows("work_packages", "package_id=" . htmlspecialchars($package_id)." ORDER BY package_name");
        if (isset($package_info[0])) {
            $package_info = $package_info[0];
            $response['err'] = false;
            $response['msg'] = 'Package Data is Ready To View';
            $package_info['applicability'] = array_map(function ($el) {
                $el['aircraft_serial_no'] = getOneField("app_aircraft", "aircraft_serial_no", "aircraft_id = {$el['aircraft_id']}");
                return $el;
            }, getRows(
                "work_package_applicability",
                "is_active = 1 AND package_id = {$package_id}"
            ));
            $package_info['parent_name'] = getOneField("work_packages", "package_name", "package_id = {$package_info['parent_id']}");
            $package_info['model_name'] = getOneField("aircraft_models", "model_name", "model_id = {$package_info['model_id']}");
            $response['data'] = [
                "tree" => get_heriarcy("work_packages", "package_id", "$package_id"),
                "info" => $package_info,
                "tasks" => array_map(
                    function ($el) {
                        $el['specialty_name'] = getOneField("app_specialties", "specialty_name", "specialty_id = {$el['specialty_id']}");
                        $el['task_type_name'] = getOneField("work_package_task_types", "type_name", "type_id = {$el['task_type_id']}");
                        return $el;
                    },
                    getRows("work_package_tasks", "package_id = {$package_id} AND is_active = 1 ORDER BY task_order")
                )
            ];
        } else {
            $response['msg'] = 'Package id is wrong !';
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}


function packages_type_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Manufacturers Are Ready To View';
        $response['data'] =  getRows("work_package_types", "is_active = 1");
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function packages_type_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $package_type_name = htmlspecialchars($POST_data["package_type_name"]);
            $package_type_id = insert_data("work_package_types", ["package_type_name"], [$package_type_name]);
            if (is_null($package_type_id) == false) {
                $response['err'] = false;
                $response['msg'] = "New Work Package Type Added Successfully";
                $response['data'] = getRows("work_package_types", "is_active = 1");
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

function packages_type_delete()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $package_type_id = htmlspecialchars($POST_data["package_type_id"]);
            $manufacturer_id = delete_data("work_package_types", "package_type_id = $package_type_id");
            if (is_null($manufacturer_id) == false) {
                $response['err'] = false;
                $response['msg'] = "Package Type Deleted Successfully";
                $response['data'] = getRows("work_package_types", "is_active = 1");
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

function packages_type_show($id)
{
    $package_type_id = explode("/api/packages/types/", $id[0])[1];
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $package_type_info = getRows("work_package_types", "package_type_id = " . htmlspecialchars($package_type_id));

        if (isset($package_type_info[0])) {
            $response['err'] = false;
            $response['msg'] = 'Packages Data is Ready To View';
            $response['data'] = getRows("work_packages", "package_type_id = $package_type_id and is_active = 1");
        } else {
            $response['msg'] = 'Package Type id is wrong !';
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}
