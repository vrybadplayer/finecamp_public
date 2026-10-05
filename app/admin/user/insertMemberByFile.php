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

    //print_r($existing_ids);

    $stm = $_db->prepare('INSERT INTO user (user_id, username, password, email, phone_no, photo, role, status) VALUES (?, ?, SHA1(?), ?, ?, ?, ?, ?)');
    $n = 0;

    // (2) Insert records from CSV file
    // TODO
    $f = fopen('user_data.csv', 'r');
    while ($data = fgetcsv($f)) {
        if(!in_array($data[0], $existing_ids)) {
            $n+=$stm->execute($data);
        }

    }
    fclose($f);

    temp('info', "$n record(s) inserted");
}

redirect('../adminDashboard.php');