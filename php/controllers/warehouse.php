<?php
$endpoints += [
    '/api/warehouses'           => 'warehouses_index',
    '/api/warehouses/store'     => 'warehouses_store',
    '/api/warehouses/users'     => 'warehoses_users',
    '/api/warehouses/\d+'       => 'warehouses_show',
];

function warehouses_index()
{
    global $method, $response, $pdo;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $user_id = $operator_info['user_id'];
        $sql = "SELECT * FROM app_warehouses aw JOIN warehouses_users wu ON aw.warehouse_id = wu.warehouse_id WHERE wu.user_id = {$user_id}";
        try {
            $statement = $pdo->prepare($sql);
            $statement->execute();
            if ($statement->rowCount() > 0) {
                $data = [];
                while ($el = $statement->fetch(PDO::FETCH_ASSOC)) {
                    array_push($data, $el);
                }
            }
            $response['err'] = false;
            $response['msg'] = 'All Warehouses Are Ready To View';
            $response['data'] = $data;
            echo json_encode($response, true);
        } catch (\Throwable $th) {
            $response['msg'] = $th;
            echo json_encode($response, true);
            exit();
        }
    } else {
        echo 'Method Not Allowed';
    }
}

function warehoses_users()
{
    global $method, $response, $pdo;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $sql = "SELECT * FROM app_users au 
        JOIN app_user_authority aua ON au.user_id = aua.user_id 
        WHERE aua.app_id = (SELECT app_id FROM app_apps WHERE app_name LIKE '%warehouse%')";
        try {
            $statement = $pdo->prepare($sql);
            $statement->execute();
            if ($statement->rowCount() > 0) {
                $data = [];
                while ($el = $statement->fetch(PDO::FETCH_ASSOC)) {
                    array_push($data, $el);
                }
            }
            $response['err'] = false;
            $response['msg'] = 'All Users Are Ready To View';
            $response['data'] = $data;
            echo json_encode($response, true);
        } catch (\Throwable $th) {
            $response['msg'] = $th;
            echo json_encode($response, true);
            exit();
        }
    } else {
        echo 'Method Not Allowed';
    }
}

function warehouses_store()
{
    global $method, $response, $POST_data;
    if ($method === "POST") {
        $operator_info = checkAuth();
        try {
            $warehouse_id = insert_data("app_warehouses", ["warehouse_name"], [$POST_data['warehouse_name']]);
            $warehouse_admins = $POST_data['warehouse_admins'];
            $warehouse_users = $POST_data['warehouse_users'];

            foreach ($warehouse_admins as $index => $user) {
                insert_data("warehouses_users", ["warehouse_id", "user_id", "is_admin"], [$warehouse_id, $user['value'], 1]);
            }
            foreach ($warehouse_users as $index => $user) {
                insert_data("warehouses_users", ["warehouse_id", "user_id"], [$warehouse_id, $user['value']]);
            }
            $response['err'] = false;
            $response['msg'] = 'New warehouse added successfully !';
            echo json_encode($response, true);
        } catch (\Throwable $th) {
            $response['msg'] = $th;
            echo json_encode($response, true);
            exit();
        }
    } else {
        echo 'Method Not Allowed';
    }
}



