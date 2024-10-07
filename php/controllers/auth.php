<?php
// Routes
$endpoints += [
    '/api/auth/login'       => 'auth_login',
    '/api/auth/reset'       => 'auth_rest_password',
    '/api/auth/check'       => 'auth_check_token',
    '/api/auth/activate'    => 'auth_active_user',
    // '/api/auth/resendcode'  => 'resend_code',
];

function auth_login()
{
    global $method, $response, $POST_data;
    if ($method === "POST") {
        $user_email = htmlspecialchars(strtolower(@$POST_data["user_email"]));
        $user_password = htmlspecialchars(@$POST_data["user_password"]);
        $user_hashed_password = getOneField("app_users", "user_password", "user_email = '$user_email'");
        if (isset($user_hashed_password)) {
            if (password_verify($user_password, $user_hashed_password)) {
                $is_active = getOneField("app_users", "is_active", "user_email = '$user_email'");
                if ($is_active == 1) {
                    $user_info = getRows("app_users", "user_email= '$user_email'");
                    $response['err']  = false;
                    $response['msg']  = "User Login Successfully!";
                    $response['data'] = $response['data'] =  array_map(function ($user) {
                        return [
                            'user_id'    => $user['user_id'],
                            'user_name'  => $user['user_name'],
                            'user_token' => $user['user_token'],
                            'user_email' => $user['user_email'],
                            'is_active'  => $user['is_active'],
                            'user_roles' => getRows("app_user_authority", "user_id=" . $user['user_id'])
                        ];
                    }, $user_info);
                } else {
                    $response['err']  = false;
                    $response['msg']  = "User is not active";
                    $response['data'] = ['is_active' => 0];
                }
            } else {
                $response['msg'] = "Wrong Email or Password";
            }
        } else {
            $response['msg'] = "Wrong Email or Password";
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}

function auth_rest_password()
{
    global $method, $POST_data, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        $user_email = htmlspecialchars(strtolower(@$POST_data["user_email"]));
        $default = password_hash('user1234', PASSWORD_DEFAULT);
        if ($operator_info['is_super'] == 1) {
            update_data(
                "app_users",
                "user_email = '$user_email'",
                ['user_password' => $default]
            );
            sendMail("a_fattah_m@icloud.com", "Password Reset", "Your password have been reset Your Default Password is : user1234");
        } else {
            $response['msg'] = 'User have no authority';
            echo json_encode($response, true);
        }
    } else {
        echo 'Method Not Allowed';
    }
}

function auth_check_token()
{
    global $method, $response;
    if ($method === "POST") {
        $operator_info = checkAuth();
        $user_email = $operator_info['user_email'];
        $user_info = getRows("app_users", "user_email = '$user_email'");
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

function auth_active_user()
{
    global $method, $response, $POST_data;
    if ($method === "POST") {
        $operator_info = checkAuth();
        $user_email = $operator_info['user_email'];
        $user_vcode = $POST_data['user_vcode'];
        $user_actual_vcode = getOneField("app_users", "user_vcode", "user_email ='$user_email'");
        if ($user_vcode == $user_actual_vcode) {
            update_data(
                "app_users",
                "user_email = '$user_email'",
                ['is_active' => 1]
            );
        } else {
            $response['msg'] = 'The Verification code is invalid';
            echo json_encode($response, true);
        }
    } else {
        echo 'Method Not Allowed';
    }
}
