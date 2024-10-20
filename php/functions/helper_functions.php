<?php
// Use PhpMailer
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require './vendor/phpmailer/phpmailer/src/Exception.php';
require './vendor/phpmailer/phpmailer/src/PHPMailer.php';
require './vendor/phpmailer/phpmailer/src/SMTP.php';
// // Use PhpSpreadsheet classes
// use PhpOffice\PhpSpreadsheet\IOFactory;
// use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Date;

function sendMail($sendTo, $subject, $msg)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.hostinger.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'verify@easetasks.com';                     //SMTP username
        $mail->Password   = '$c0ET]1BaEfg';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        //Recipients
        $mail->setFrom('verify@easetasks.com', 'IPACO SkyReady');
        $mail->addAddress($sendTo);     //Add a recipient
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $msg;
        $mail->AltBody = $msg;
        $mail->send();
    } catch (Exception $e) {
        echo $e;
    }
}

function getUserIP()
{
    // If you trust the proxy, check these headers
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        // Multiple IP addresses may be comma-separated, so take the first one
        $ip_array = explode(',', $ip);
        $ip = trim($ip_array[0]);
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
        // Default to REMOTE_ADDR
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    // Validate the IP address
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    } else {
        return 'Invalid IP';
    }
}


// function upload_items()
// {
//     global $method;
//     global $POST_data;
//     global $pdo;
//     global $response;

//     if ($method === "POST") {
//         if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
//             $headerParts = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
//             if (count($headerParts) == 2 && $headerParts[0] == 'Bearer') {
//                 $accessToken = $headerParts[1];
//                 $user_info = json_decode(checkToken($accessToken), true);
//                 if ($user_info) {
//                     // print_r($_FILES['file']);
//                     if (isset($_FILES["file"])) {
//                         $tmpFilePath = $_FILES['file']['tmp_name'];
//                         // Load the Excel file
//                         $objPHPExcel = IOFactory::load($tmpFilePath);
//                         // Get the active sheet
//                         $sheet = $objPHPExcel->getActiveSheet();
//                         // Get the highest row number
//                         $highestRow = $sheet->getHighestRow();
//                         $acceptedRows = [];
//                         $refusedRows = [];
//                         // Loop through each row
//                         for ($row = 1; $row <= $highestRow; $row++) {
//                             // Get cell value for each column in the current row
//                             $product_pn = $sheet->getCellByColumnAndRow(1, $row)->getValue();
//                             $product_usa_pn = $sheet->getCellByColumnAndRow(3, $row)->getValue();
//                             $excelRow = [
//                                 'cell_1' => $sheet->getCellByColumnAndRow(1, $row)->getValue(),
//                                 'cell_2' => $sheet->getCellByColumnAndRow(2, $row)->getValue(),
//                                 'cell_3' => $sheet->getCellByColumnAndRow(3, $row)->getValue(),
//                                 'cell_4' => $sheet->getCellByColumnAndRow(4, $row)->getValue()
//                             ];
//                             // Do something with the cell values
//                             try {
//                                 $sql = "
//                                     SELECT * FROM warehouse_products WHERE 
//                                     (product_pn = :product_pn AND warehouse_id = :warehouse_id AND is_active = 1) OR
//                                     (product_usa_pn = :product_usa_pn AND warehouse_id = :warehouse_id AND is_active = 1) 
//                                 ";
//                                 $statement = $pdo->prepare($sql);
//                                 $statement->bindParam(':product_pn', $product_pn);
//                                 $statement->bindParam(':product_usa_pn', $product_usa_pn);
//                                 $statement->bindParam(':warehouse_id', $_POST['warehouse_id']);
//                                 $statement->execute();
//                                 if ($statement->rowCount() > 0) {
//                                     array_push($refusedRows, $excelRow);
//                                 } else {
//                                     array_push($acceptedRows, $excelRow);
//                                 }
//                                 $response['data'] = [
//                                     'accepted_rows' => $acceptedRows,
//                                     'refused_rows' => $refusedRows
//                                 ];
//                                 $response['err'] = false;
//                                 $response['msg'] = 'All Sheet rows has been tested';
//                             } catch (Exception $e) {
//                                 $response['msg'] = "An error occurred: " . $e->getMessage();
//                             }
//                         }
//                     } else {
//                         $response['msg'] = "No File Uploaded";
//                     }
//                 } else {
//                     $response['msg'] = "Invaild user token !";
//                 }
//                 echo json_encode($response, true);
//             } else {
//                 http_response_code(400);
//                 echo "Error : 400 | Bad Request";
//             }
//         } else {
//             http_response_code(401); // Unauthorized
//             echo "Error : 401 | Unauthorized";
//         }
//     } else {
//         echo 'Method Not Allowed';
//     }
// }
