<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

auth('index.php','Admin', 'Root');  //only admin and root can access this page

if (is_post()) {

    // (1) Check for duplicate user_id in the database
    $existing_ids = [];
    $result = $_db->query('SELECT user_id FROM user');
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $existing_ids[] = $row['user_id'];
    }

    $stm = $_db->prepare('UPDATE user SET status = "terminated" WHERE user_id = ?');
    $n = 0;

    // (2) Insert records from CSV file
    $f = fopen('user_data.csv', 'r');
    while ($data = fgetcsv($f)) {
        $user_id = $data[0];  
        if (in_array($user_id, $existing_ids)) {
            $n += $stm->execute([$user_id]);  // Pass user_id as a parameter
        }
    }
    fclose($f);

    temp('info', "$n record(s) status changed to terminated");
}

redirect('../adminDashboard.php');
