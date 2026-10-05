<?php
include '../_base.php';

if ($_user != '') {
    if ($_user->role == 'Member') {
        redirect('/member/home/home.php');
    } else if ($_user->role == 'Admin' || $_user->role == 'Root') {
        redirect('/admin/adminDashboard.php');
    }
}

// ----------------------------------------------------------------------------
$token_id = req('token_id');

$stm = $_db->prepare('SELECT * FROM token WHERE token_id = ?');
$stm->execute([$token_id]);
$u = $stm->fetch();


$stm = $_db->prepare('UPDATE user SET status = ? WHERE user_id = ?');
$stm->execute(["active", $u->user_id]);

$stm = $_db->prepare('UPDATE user SET try = ? WHERE user_id = ?');
$stm->execute(['0', $u->user_id]);

$stm = $_db->prepare('DELETE FROM token WHERE token_id = ?');
$stm->execute([$token_id]);

temp('info', 'Email Unblocked');

redirect('/');

// ----------------------------------------------------------------------------