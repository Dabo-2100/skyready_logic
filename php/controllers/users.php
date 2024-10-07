<?php
// Controller Routes
$endpoints += [
    '/api/users'        => 'users_index',
    '/api/users/\d+'    => 'users_show',
    '/api/users/store'  => 'users_store',
];

function users_index()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Users Are Ready To View';
        $response['data'] =  array_map(function ($user) {
            return [
                'user_id'    => $user['user_id'],
                'user_name'  => $user['user_name'],
                'user_email' => $user['user_email'],
                'is_active'  => $user['is_active'],
                'user_roles' => count(getRows("app_user_authority", "user_id=" . $user['user_id']))
            ];
        }, getRows("app_users", "1=1"));
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function users_show($id)
{
    $user_id = explode("/api/users/", $id[0])[1];
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $user_info = getRows("app_users", "user_id=" . htmlspecialchars($user_id));
        if (isset($user_info[0])) {
            $response['err'] = false;
            $response['msg'] = 'User Data is Ready To View';
            $response['data'] =  array_map(function ($user) {
                $user_roles = getRows("app_user_authority", "user_id=" . $user['user_id']);
                return [
                    'user_id'    => $user['user_id'],
                    'user_name'  => $user['user_name'],
                    'user_email' => $user['user_email'],
                    'is_active'  => $user['is_active'],
                    'user_roles' => array_map(function ($role) {
                        if ($role['is_active'] == 1) {
                            return [
                                'log_id' => $role['log_id'],
                                'role_id' => $role['role_id'],
                                'role_name' => getOneField("app_roles", "role_name", "role_id =" . $role['role_id']),
                                'app_id' => $role['app_id'],
                                'app_name' => getOneField("app_apps", "app_name", "app_id =" . $role['app_id']),
                                'app_icon' => getOneField("app_apps", "app_icon", "app_id =" . $role['app_id']),
                                'app_order' => getOneField("app_apps", "app_order", "app_id =" . $role['app_id']),
                            ];
                        }
                    }, $user_roles),
                ];
            }, $user_info);
        } else {
            $response['msg'] = 'User id is wrong !';
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function users_store()
{
    // Who can use this function 
    // Super_User , Specialty Admin
    global $method, $POST_data, $response;
    if ($method === "POST") {
        // $operator_info = checkAuth();
        // if ($operator_info['is_super'] == 1) {
        $user_name = htmlspecialchars(@$POST_data["user_name"]);
        $user_email = htmlspecialchars(strtolower(@$POST_data["user_email"]));
        $specialty_id = htmlspecialchars(@$POST_data["specialty_id"]);
        $is_super = htmlspecialchars(@$POST_data["is_super"]);
        $user_password = password_hash('user', PASSWORD_DEFAULT);
        $user_vcode = rand(1000, 9999);
        try {
            $user_id = insert_data("app_users", ["user_name", "user_email", "user_password", "user_vcode", "specialty_id", "is_super"], [$user_name, $user_email, $user_password, $user_vcode, $specialty_id, $is_super]);
            $user_token = createToken(['user_id' => $user_id, 'user_email' => $user_email, 'is_super' => $is_super]);
            try {
                $update_token = update_data("app_users", "user_id = $user_id", ['user_token' => $user_token]);
                if ($update_token == 1) {
                    $response['err'] = false;
                    $response['msg'] = "User added Successfuly Defalut password is : 'user' !";
                    $response['data'] = ['user_id' => $user_id, 'user_token' => $user_token];
                }
            } catch (\Throwable $err) {
                $response['msg'] = $err;
            }
        } catch (\Throwable $err) {
            $response['msg'] = $err;
        }
        echo json_encode($response, true);
        // } 
        // else {
        //     echo "Error : 401 | No Authority";
        //     http_response_code(401);
        //     exit();
        // }
    } else {
        echo 'Method Not Allowed';
    }
}
