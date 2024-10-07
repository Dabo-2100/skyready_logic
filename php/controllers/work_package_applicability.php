<?php
// Routes
$endpoints += [
    '/api/workpackage/applicability'          => 'applicability_index',
    '/api/workpackage/applicability/related'  => 'applicability_related',
    '/api/workpackage/applicability/store'    => 'applicability_store',
    '/api/workpackage/applicability/delete'   => 'applicability_delete',
];

function applicability_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Applicabilities Are Ready To View';
        $response['data'] =  getRows("work_package_applicability", "is_active = 1");
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function applicability_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $package_id = htmlspecialchars($POST_data["package_id"]);
            $aircraft_id = htmlspecialchars($POST_data["aircraft_id"]);
            $status_id = insert_data("work_package_applicability", ["package_id", "aircraft_id"], [$package_id, $aircraft_id]);
            if (is_null($status_id) == false) {
                $response['err'] = false;
                $response['msg'] = "New Applicability Added Successfully";
                $response['data'] = getRows("work_package_applicability", "is_active = 1");
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

function applicability_delete()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        if ($operator_info['is_super'] == 1) {
            $log_id = htmlspecialchars($POST_data["log_id"]);
            $log_id = delete_data("work_package_applicability", "log_id = $log_id");
            if (is_null($log_id) == false) {
                $response['err'] = false;
                $response['msg'] = "Applicability Deleted Successfully";
                $response['data'] = getRows("work_package_applicability", "is_active = 1");
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

function applicability_related()
{
    global $method, $response, $POST_data;
    if ($method === "POST") {
        $operator_info = checkAuth();
        $filter_by =  htmlspecialchars($POST_data["filter_by"]);
        $filter_val = htmlspecialchars($POST_data["filter_val"]);
        $response['err'] = false;
        $response['msg'] = 'All Status Are Ready To View';
        $response['data'] =  array_map(function ($el) {
            $el['aircraft_serial_no'] = getOneField("app_aircraft", "aircraft_serial_no", "aircraft_id = {$el['aircraft_id']}");
            $el['package_name'] = getOneField("work_packages", "package_name", "package_id = {$el['package_id']}");
            $el['parent_id'] = getOneField("work_packages", "parent_id", "package_id = {$el['package_id']}");
            $el['parent_name'] = getOneField("work_packages", "package_name", "package_id = {$el['parent_id']}");
            return $el;
        }, getRows(
            "work_package_applicability",
            "is_active = 1 AND {$filter_by} = {$filter_val} ORDER BY package_id"
        ));
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}
