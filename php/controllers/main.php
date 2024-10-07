<?php
// Routes
$endpoints += [
    '/api/insert' => 'insertData',
    '/api/update' => 'updateData',
    '/api/get'    => 'getData',
    '/api/delete' => 'deleteData',

];

function insertData()
{
    global $response;
    if (is_null(insert_data()) == false) {
        $response['err'] = false;
        $response['msg'] = 'Record Inserted Successfully';
    } else {
        $response['msg'] = 'Record Not Inserted';
    }
    echo json_encode($response, true);
}

function updateData()
{
    global $response;
    if (is_null(update_data()) == false) {
        $response['err'] = false;
        $response['msg'] = 'Record Updated Successfully';
    } else {
        $response['msg'] = 'Record Not Updated';
    }
    echo json_encode($response, true);
}

function deleteData()
{
    global $response;
    if (is_null(delete_data()) == false) {
        $response['err'] = false;
        $response['msg'] = 'Deleted Updated Successfully';
    } else {
        $response['msg'] = 'Record Not Deleted';
    }
    echo json_encode($response, true);
}

function getData()
{
    global $pdo, $POST_data;
    $final = [];
    try {
        $sql = $POST_data['query'];
        $statement = $pdo->prepare($sql);
        $statement->execute();
        $final = [];
        if ($statement->rowCount() > 0) {
            while ($el = $statement->fetch(PDO::FETCH_ASSOC)) {
                array_push($final, $el);
            }
        }
    } catch (\Throwable $th) {
    }
    if (count($final) == 0) {
        $response['msg'] = 'There are no records';
    } else {
        $response['err'] = false;
        $response['msg'] = 'Records are ready to view';
        $response['data'] = $final;
    }
    echo json_encode($response, true);
}
