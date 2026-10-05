<?php


require '../../_base.php';

auth('index.php','Admin', 'Root');  //only admin and root can access this page



if (is_get()) {   //filter status and search record
    $status_filter = req('status');
    $search_query = req('search_input');
    $selected_status = $status_filter ?: '--Select Filter Status--';
    $selected_sort = req('sort') ?: '--Select Sorting Option--';
}


if (is_post()) {   //filter status and search record
    $status_filter = req('status');
    $search_query = req('search_input');
   
    $search_text = isset($search_query) ? $search_query : '';


    // Set the selected option in the filter select and sorting
    $selected_status = $status_filter ?: '--Select Filter Status--';
    $selected_sort = req('sort') ?: '--Select Sorting Option--';

    if ($status_filter != 'all') {
        $arr = $_db->query("SELECT * FROM user WHERE role = 'Member' AND status = '$status_filter' ")->fetchAll();

//===[search part]============================================================


        if (!empty($search_query)) {
            $arr = $_db->query("SELECT * FROM user WHERE role = 'Member' AND username LIKE '%$search_query%'")->fetchAll();
           // print_r($arr);
        }
    } else {
        $arr = $_db->query("SELECT * FROM user WHERE role = 'Member'")->fetchAll();
    }
    //var_dump($search_query); debug
}
$status_option = [
       'all' => 'all',
    'active' => 'active',
    'inactive' => 'inactive',
    'terminated' => 'terminated'
];

$sort_options = [
    'asc' => 'Ascending',
    'desc' => 'Descending'
];

// ----------------------------------------------------------------------------
$_title = 'Member List';
$_css_file = '/css/admin/admin.css';

//pagging part
$page = req('page', 1);
require_once '../../lib/SimplePager.php';

$query = 'SELECT * FROM user WHERE role = "Member"';
$params = [];

if ($status_filter && $status_filter != 'all') {
    $query .= ' AND status = :status';
    $params['status'] = $status_filter;
}

if (!empty($search_query)) {
    $query .= ' AND username LIKE :search_query';
    $params['search_query'] = '%' . $search_query . '%';
}
 else {
    //$status_filter = 'all'; //show all status 
    $arr = $_db->query("SELECT * FROM user WHERE role = 'Member'")->fetchAll();
}

if($selected_sort=='asc' || $selected_sort=='desc'){
$query .= ' ORDER BY username ' . $selected_sort;
}

$p = new SimplePager($query, $params, 10, $page);
$arr = $p->result;

include '../../_head.php';
?>
<?php html_h1('h1','Member List') ?>
<button data-get="/admin/adminDashboard.php" class="back">Back to Dashboard</button>
<!-- <p><strong>Total : <?= count($arr) ?> record(s) found</strong></p> -->
<p style="text-align: center;">
    <?= $p->count ?> of <?= $p->item_count ?> record(s) |
    Page <?= $p->page ?> of <?= $p->page_count ?>
</p><br>

<form action="" method="post">
    <label for="search_input">Search here : </label>
    <?= html_search_record('search_input', isset($search_text) ? $search_text : '') ?>
    <?= html_button('search', 'Search', "Search") ?>
    <label for="search_input">Filter by status : </label>
    <?= html_select('status', $status_option, $selected_status) ?> 

    <label for="sort">Sort by Member Name: </label>
    <?= html_select('sort', $sort_options, $selected_sort) ?>
    <?= html_button('btn', 'Filter') ?>
    <?= html_button('reset', 'Reset', 'class="btn"') ?>
    <br><br>
</form>
<label for="create_member">Create a new member here: </label>
<?= html_button('create', 'Create Member', 'onclick="window.location.href=\'insert.php\'"'); ?>

<button data-post="insertMemberByFile.php" class="back" data-confirm>Insert Member by file</button>
<button data-post="deleteMemberByFile.php" class="back" data-confirm>Delete Member by file</button>



<table class="tableList">

    <tr>
        <th>Id</th>
        <th>User Name</th>
        <th>Email</th>
        <th>Status</th>
        <th>Option</th>
    </tr>


    <?php foreach ($arr as $s) : ?> <!--looping data from database-->
        <tr>
            <td><?= $s->user_id ?></td>
            <td><?= $s->username ?></td>
            <!--password-->
            <td><?= $s->email ?></td>
            <?php if ($s->status == 'active') : ?>
                <td class="status" style="color:green"><?= $s->status ?></td>
            <?php elseif ($s->status == 'inactive') : ?>
                <td class="status" style="color:orange"><?= $s->status ?></td>
            <?php else : ?>
                <td class="status" style="color:red; text-stroke: 1px black;"><?= $s->status ?></td>
            <?php endif ?>



            <td>

                <button data-get="detail.php?id=<?= $s->user_id ?>" id="detail" class="back"></button>
                <button data-get="update.php?id=<?= $s->user_id ?>" id="update" class="back"></button>
                <button data-post="activeMember.php?id=<?= $s->user_id ?>" id="active" class="back" data-confirm></button>
                <button data-post="deleteMember.php?id=<?= $s->user_id ?>" id="delete" data-confirm class="back"></button>
               

            </td>
        </tr>
    <?php endforeach ?>
</table>
<br>
<div style="display:flex; justify-content: center;">
<?= $p->html("status=$status_filter&search_input=$search_query&sort=$selected_sort") ?> <!--able filter the record after change the page-->
</div>





<?php
include '../../_foot.php';
?>