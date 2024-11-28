<?php
$endpoints += [
    '/api/warehouses'                   => 'warehouses_index',
    '/api/warehouses/store'             => 'warehouses_store',
    '/api/warehouses/users'             => 'warehoses_users',
    '/api/warehouses/\d+'               => 'warehouses_show',
    '/api/warehouses/locations/\d+'     => 'locations_index',
    '/api/warehouses/locations/store'   => 'locations_store',
    '/api/warehouses/items/\d+'         => 'items_index',
    '/api/warehouses/items/store'       => 'items_store',
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
                    $el['locations'] = getRows("warehouse_locations", "warehouse_id = {$el['warehouse_id']}  ORDER BY location_name ASC");
                    $el['admins'] = getRows("warehouses_users", "warehouse_id = {$el['warehouse_id']} and is_admin = 1");
                    $el['users'] = getRows("warehouses_users", "warehouse_id = {$el['warehouse_id']} and is_admin = 0");
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

function locations_index($id)
{
    $warehouse_id = explode("/api/warehouses/locations/", $id[0])[1];
    global $method, $response, $pdo;
    if ($method === "GET") {
        $operator_info = checkAuth();
        $response['data'] = ['locations' => getRows("warehouse_locations", "warehouse_id = {$warehouse_id} ORDER BY location_name ASC")];
        $response['err'] = false;
        $response['msg'] = "All Locations are ready to view";
    } else {
        echo 'Method Not Allowed';
    }
    echo json_encode($response, true);
}

function locations_store()
{
    global $method, $response, $POST_data;
    if ($method === "POST") {
        $operator_info = checkAuth();
        try {
            $location_id = insert_data("warehouse_locations", ["location_name", "warehouse_id"], [$POST_data['location_name'], $POST_data['warehouse_id']]);
            $response['data'] = ['locations' => getRows("warehouse_locations", "warehouse_id = {$POST_data['warehouse_id']}  ORDER BY location_name ASC")];
            $response['err'] = false;
            $response['msg'] = 'New Location added successfully !';
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

function items_store()
{
    global $method, $response, $POST_data;
    if ($method === "POST") {
        $operator_info = checkAuth();
        try {
            insert_data(
                "warehouse_items",
                ["unit_id", "category_id", "item_name", "item_sn", "item_pn", "item_nsn"],
                [
                    $POST_data['unit_id'],
                    $POST_data['category_id'],
                    $POST_data['item_name'],
                    $POST_data['item_sn'],
                    $POST_data['item_pn'],
                    $POST_data['item_nsn']
                ]
            );
            // $response['data'] = ['locations' => getRows("warehouse_locations", "warehouse_id = {$POST_data['warehouse_id']}  ORDER BY location_name ASC")];
            $response['err'] = false;
            $response['msg'] = 'New Item added successfully !';
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


function items_index($id)
{
    $page_id = explode("/api/warehouses/items/", $id[0])[1] * 25;
    global $method, $response;
    if ($method === "GET") {
        $operator_info = checkAuth();
        try {
            $items = getRows("warehouse_items", "1=1 LIMIT {$page_id} , 25");
            $data = array_map(function ($el) {
                $el['category_name'] = getOneField("item_categories", "category_name", "category_id = {$el['category_id']}");
                $el['unit_name'] = getOneField("warehouse_units", "unit_name", "unit_id = {$el['unit_id']}");
                return $el;
            }, $items);
            $response['data'] = [
                'items' => $data,
                'row_count' => getOneField("warehouse_items", "COUNT(*)", "1=1")
            ];
            $response['err'] = false;
        } catch (\Throwable $th) {
            $response['msg'] = $th;
            exit();
        }
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}
