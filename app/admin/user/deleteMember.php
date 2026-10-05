<?php
require '../../_base.php';
// ----------------------------------------------------------------------------

auth('index.php','Admin','Root');  //only admin and root can access this page

if(!is_post()) {
    temp('info', 'unexpected access to this page');
    redirect('index.php');
}

if (is_post()) {
    $id = req('id');
//change status , not direct delete the user
    $stm = $_db->prepare('UPDATE user SET status = "terminated" WHERE user_id = ?');
    $stm->execute([$id]);

    temp('info', "User ID:$id Terminated Successfully");
    redirect('index.php');
}



