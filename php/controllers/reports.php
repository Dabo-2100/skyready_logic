<?php
// Routes
$endpoints += [
    '/api/report/wp/details' => 'wp_report_1',
];

function wp_report_1()
{
    global $method, $response, $POST_data, $pdo;
    if ($method === "POST") {
        $operator_info = checkAuth();
        $package_children = $POST_data['package_children'];
        $data = [];
        foreach ($package_children as $index => $package_id) {
            $package_obj = [];
            $package_obj['package_id'] = $package_id;
            $package_obj['package_name'] = getOneField("work_packages", "package_name", "package_id = {$package_obj['package_id']}");
            $sql = "SELECT DISTINCT wpt.specialty_id , aps.specialty_name, wpt.package_id  
            FROM `project_tasks` pt
            JOIN work_package_tasks wpt ON wpt.task_id = pt.task_id
            JOIN app_specialties aps ON aps.specialty_id = wpt.specialty_id
            WHERE wpt.package_id = {$package_obj['package_id']}";
            $statement = $pdo->prepare($sql);
            $statement->execute();
            $final = [];
            if ($statement->rowCount() > 0) {
                while ($el = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $sql2 = "SELECT 
                    SUM(pt.task_progress/100 * wpt.task_duration) AS TotalDoneHrs, 
                    SUM(wpt.task_duration) AS Speciality_Duration, 
                    COUNT(*) AS tasks_No, 
                    (  SELECT COUNT(*) 
                        FROM project_tasks pt2 
                        JOIN work_package_tasks wpt2 ON wpt2.task_id = pt2.task_id
                        WHERE pt2.status_id != 4 AND wpt2.specialty_id = {$el['specialty_id']} AND wpt2.package_id = {$el['package_id']} 
                    ) AS not_done_count
                    FROM `project_tasks` pt 
                    JOIN work_package_tasks wpt ON wpt.task_id = pt.task_id 
                    JOIN app_specialties aps ON aps.specialty_id = wpt.specialty_id
                    WHERE wpt.package_id = {$el['package_id']} AND wpt.specialty_id = {$el['specialty_id']}";
                    $statement2 = $pdo->prepare($sql2);
                    $statement2->execute();
                    if ($statement2->rowCount() > 0) {
                        while ($el2 = $statement2->fetch(PDO::FETCH_ASSOC)) {
                            $el['Done_Hrs'] = number_format($el2['TotalDoneHrs'], 2);
                            $el['Speciality_Duration'] = number_format($el2['Speciality_Duration'], 2);
                            $el['tasks_no'] = number_format($el2['tasks_No'], 2);
                            $el['not_done_count'] = number_format($el2['not_done_count'], 2);
                        }
                    }
                    array_push($final, $el);
                }
            }
            $package_obj['specialites'] = $final;
            array_push($data, $package_obj);
        }
        // print_r($package_children);
        $response['err'] = false;
        $response['msg'] = 'All Types Are Ready To View';
        $response['data'] = $data;
        echo json_encode($response, true);
    } else {
        echo 'Method Not Allowed';
    }
}
