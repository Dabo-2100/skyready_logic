<?php

function checkAuth()
{
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headerParts = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
        if (count($headerParts) == 2 && $headerParts[0] == 'Bearer') {
            $accessToken = $headerParts[1];
            $token_data = json_decode(checkToken($accessToken), true);
            if (isset($token_data)) {
                return $token_data;
            } else {
                echo "Error : 401 | Unauthorized False Token";
                http_response_code(401);
                exit();
            }
        } else {
            echo "Error : 400 | Bad Request No Token";
            http_response_code(400);
            exit();
        }
    } else {
        echo "Error : 400 | Bad Request No Token";
        http_response_code(400);
        exit();
    }
}

function getOneField($table_name, $required_field, $condition)
{
    global $pdo;
    $final = false;
    $pdoL = $pdo;
    try {
        $sql = "SELECT " . $required_field . " AS Final FROM " . $table_name . " WHERE " . $condition;
        $statement = $pdoL->prepare($sql);
        $statement->execute();
        if ($statement->rowCount() > 0) {
            while ($el = $statement->fetch(PDO::FETCH_ASSOC)) {
                $final  = $el['Final'];
            }
        }
    } catch (\Throwable $th) {
        // throw $th;
    } finally {
        $pdoL = null;
    }
    return $final;
}

function getRows($table_name, $condition)
{
    global $pdo;
    $final = [];
    try {
        $sql = "SELECT * FROM $table_name WHERE " . $condition;
        $statement = $pdo->prepare($sql);
        $statement->execute();
        $final = [];
        if ($statement->rowCount() > 0) {
            while ($el = $statement->fetch(PDO::FETCH_ASSOC)) {
                array_push($final, $el);
            }
        }
    } catch (\Throwable $th) {
        //throw $th;
    }
    return $final;
}

function get_heriarcy($table_name, $field_name, $field_value)
{
    $res = null;
    global $pdo, $response;
    $sql = "WITH RECURSIVE package_hierarchy AS (
            SELECT wp.* FROM $table_name wp WHERE wp.$field_name = $field_value
            UNION ALL SELECT wp.* FROM $table_name wp INNER JOIN package_hierarchy ph ON wp.parent_id = ph.$field_name) 
            SELECT * FROM package_hierarchy ORDER BY $field_name ";
    $statement = $pdo->prepare($sql);
    try {
        $statement->execute();
        $final = [];
        if ($statement->rowCount() > 0) {
            while ($el = $statement->fetch(PDO::FETCH_ASSOC)) {
                array_push($final, $el);
            }
        }
        $res = array_reverse($final);
    } catch (\Throwable $th) {
        $response['msg'] = $th;
        echo json_encode($response, true);
        exit();
    }
    return $res;
}

function insert_data($table_name = false, $Fields = false, $Values = false)
{
    $res = null;
    global $method, $POST_data, $pdo, $response;
    if ($method == "POST") {
        if ($table_name == false) {
            $table_name = @$POST_data["table_name"];
        }
        if ($Fields == false) {
            $Fields = @$POST_data["Fields"];
        }
        if ($Values == false) {

            $Values = @$POST_data["Values"];
        }
        $FieldsStr = "";
        $ValuesStr = "";
        foreach ($Fields as $index => $value) {
            $FieldsStr .= "$value";
            if (count($Fields) - 1 != $index) {
                $FieldsStr .= ",";
            }
        }
        foreach ($Values as $index => $value) {
            $ValuesStr .= "'$value'";
            if (count($Values) - 1 != $index) {
                $ValuesStr .= ",";
            }
        }

        $sql = "INSERT INTO $table_name ($FieldsStr) VALUES ($ValuesStr)";
        try {
            $statement = $pdo->prepare($sql);
            $statement->execute();
            $res = $pdo->lastInsertId();
        } catch (\Throwable $th) {
            $response['msg'] = $th;
            echo json_encode($response, true);
            exit();
        }
    } else {
        echo 'Method Not Allowed';
        exit();
    }
    return $res;
}

function update_data($table_name = false, $condition = false, $updateData = false)
{
    $res = null;
    global $method, $POST_data, $pdo, $response;
    if ($method === "POST") {
        if ($table_name == false) {
            $table_name = htmlspecialchars(strtolower(@$POST_data["table_name"]));
        }
        if ($condition == false) {
            $condition = htmlspecialchars(strtolower(@$POST_data["condition"]));
        }
        if ($updateData == false) {
            $updateData = @$POST_data["data"];
        }
        $sql = "UPDATE $table_name SET ";
        $updates = array();
        foreach ($updateData as $column => $value) {
            if (is_null($value) == 1) {
                $value = NULL;
            }
            $updates[] = "$column = '$value'";
        }
        $sql .= implode(", ", $updates);
        $sql .= " WHERE $condition";
        $statement = $pdo->prepare($sql);
        try {
            $statement->execute();
            $res = 1;
        } catch (\Throwable $th) {
            $response['msg'] = $th;
            echo json_encode($response, true);
            exit();
        }
    } else {
        echo 'Method Not Allowed';
        exit();
    }
    return $res;
}

function delete_data($table_name = false, $condition = false)
{
    $res = null;
    global $method, $POST_data, $pdo, $response;
    if ($method === "POST") {
        if ($table_name == false) {
            $table_name = htmlspecialchars(strtolower(@$POST_data["table_name"]));
        }
        if ($condition == false) {
            $condition = htmlspecialchars(strtolower(@$POST_data["condition"]));
        }
        $sql = "DELETE FROM $table_name WHERE $condition";
        $statement = $pdo->prepare($sql);
        try {
            $statement->execute();
            $response['err'] = false;
            $response['msg'] = "Data Deleted Successfuly !";
            $res = 1;
        } catch (\Throwable $th) {
            $response['msg'] = $th;
            echo json_encode($response, true);
            exit();
        }
    } else {
        echo 'Method Not Allowed';
    }
    return $res;
}
