<?php
$endpoints += [
    '/api/users'                => 'users_index',
    '/api/users/\d+'            => 'users_show',
    '/api/users/store'          => 'users_store',
    '/api/users/register'       => 'users_register',
    '/api/users/resend'         => 'users_resend',
    '/api/users/reset'          => 'users_reset',
    '/api/roles'                => 'app_roles',
    '/api/users/update'         => 'users_update',
    '/api/users/token/update'   => 'token_update',
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
                    'specialty_id'  => $user['specialty_id'],
                    'is_super'  => $user['is_super'],
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
            // $response['msg'] = 'User id is wrong !';
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function users_store()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $default_password = $_ENV['DEFAULT_PASSWORD'];
        // $operator_info = checkAuth();
        // if ($operator_info['is_super'] == 1) {
        $user_email = htmlspecialchars(strtolower(@$POST_data["user_email"]));
        $user_name = htmlspecialchars(@$POST_data["user_name"]);
        $specialty_id = htmlspecialchars(@$POST_data["specialty_id"]);
        $is_super = htmlspecialchars(@$POST_data["is_super"]);
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        $user_vcode = rand(1000, 9999);
        try {
            $user_id = insert_data("app_users", ["user_name", "user_email", "user_password", "user_vcode", "specialty_id", "is_super"], [$user_name, $user_email, $hashed_password, $user_vcode, $specialty_id, $is_super]);
            $user_token = createToken($user_id, $is_super);
            try {
                $update_token = update_data("app_users", "user_id = $user_id", ['user_token' => $user_token]);
                if ($update_token == 1) {
                    $response['err'] = false;
                    $response['msg'] = "User added Successfuly Defalut password is : '{$default_password}' !";
                    $response['data'] = ['user_id' => $user_id, 'user_token' => $user_token];
                }
            } catch (\Throwable $err) {
                $response['msg'] = $err;
            }
        } catch (\Throwable $err) {
            $response['msg'] = $err;
        }
        // } else {
        //     $response['msg'] = "Only Super Users can register new users";
        // }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function users_update()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        // $default_password = $_ENV['DEFAULT_PASSWORD'];
        // $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        $user_id = htmlspecialchars(@$POST_data["user_id"]);
        $user_name = htmlspecialchars(@$POST_data["user_name"]);
        $specialty_id = htmlspecialchars(@$POST_data["specialty_id"]);
        $is_super = htmlspecialchars(@$POST_data["is_super"]);
        $user_roles = @$POST_data["user_roles"];
        try {
            $update_data = update_data(
                "app_users",
                "user_id = $user_id",
                [
                    'user_name' => $user_name,
                    'specialty_id' => $specialty_id,
                    'is_super' => $is_super
                ]
            );
            if ($update_data == 1) {
                $response['err'] = false;
                $response['msg'] = "User added Successfuly !";
                delete_data("app_user_authority", "user_id = {$user_id}");
                foreach ($user_roles as $index => $role) {
                    $app_id = $role['app_id'];
                    $role_id = $role['role_id'];
                    insert_data(
                        "app_user_authority",
                        ["user_id", "app_id", "role_id"],
                        [$user_id, $app_id, $role_id]
                    );
                }
            }
        } catch (\Throwable $err) {
            $response['msg'] = $err;
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function app_roles()
{
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['err'] = false;
        $response['msg'] = 'All Data Are Ready To View';
        $response['data'] =  [
            'app_roles' => getRows("app_roles", "1=1"),
            'app_apps' => getRows("app_apps", "1=1"),
            'app_specialties' => getRows("app_specialties", "1=1"),
        ];
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function users_register()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $default_password = $_ENV['DEFAULT_PASSWORD'];
        $operator_info = checkAuth();
        $user_email = htmlspecialchars(strtolower(@$POST_data["user_email"]));
        $user_name = htmlspecialchars(@$POST_data["user_name"]);
        $specialty_id = htmlspecialchars(@$POST_data["specialty_id"]);
        $is_super = htmlspecialchars(@$POST_data["is_super"]);
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        $user_vcode = rand(1000, 9999);
        $user_roles = @$POST_data["user_roles"];
        try {
            $user_id = insert_data("app_users", ["user_name", "user_email", "user_password", "user_vcode", "specialty_id", "is_super"], [$user_name, $user_email, $hashed_password, $user_vcode, $specialty_id, $is_super]);
            $user_token = createToken($user_id, $is_super);
            try {
                $update_token = update_data("app_users", "user_id = $user_id", ['user_token' => $user_token]);
                if ($update_token == 1) {
                    $response['err'] = false;
                    $response['msg'] = "User added Successfuly Defalut password is : '{$default_password}' !";
                    $response['data'] = ['user_id' => $user_id, 'user_token' => $user_token];
                    foreach ($user_roles as $index => $role) {
                        $app_id = $role['app_id'];
                        $role_id = $role['role_id'];
                        insert_data(
                            "app_user_authority",
                            ["user_id", "app_id", "role_id"],
                            [$user_id, $app_id, $role_id]
                        );
                    }
                }
            } catch (\Throwable $err) {
                $response['msg'] = $err;
            }
        } catch (\Throwable $err) {
            $response['msg'] = $err;
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function users_resend()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        $user_id = $operator_info['user_id'];
        $user_email = getOneField("app_users", "user_email", "user_id = {$user_id}");
        echo $user_email;
        $user_vcode = rand(1000, 9999);
        update_data("app_users", "user_id = {$user_id}", ["user_vcode" => $user_vcode]);
        try {
            sendMail($user_email, "Resend : Activation Code", "You Code is : {$user_vcode}");
        } catch (\Throwable $err) {
            $response['msg'] = $err;
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}
