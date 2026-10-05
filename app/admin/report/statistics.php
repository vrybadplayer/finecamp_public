<?php
require '../../_base.php';
// ----------------------------------------------------------------------------

// SELECT ALL ORDERS and COUNT
// Tabulated Format
// Display Top 5
// Display Bottom 5 (trigger)
// Filter by Date
// Data Pie Chart

auth('authentication/loginAdmin.php', 'no role');

if (is_get()) {
    $stm = $_db->prepare('SELECT oi.name, oi.variation_name, SUM(oivo.quantity) AS quantity, oivo.quantity*oivo.unit_price AS total
    FROM order_item_variation_option oivo
    JOIN order_item oi ON oivo.order_item_id = oi.order_item_id
    JOIN orders o ON oi.order_id = o.order_id
    GROUP BY oi.variation_name
    ORDER BY quantity DESC;
    ');
    $stm->execute();
    $records = $stm->fetchAll();
    //calculations
}

if (is_post()) {
    $date = req('date');
    //date = req()
    //select where date = ?

    //calculations
}


$_title = 'Statistics';
$_css_file = '../../css/admin/statistics.css';
include '../../_head.php';



?>

<span id="top">
    <div id="topLeft">
        <h2>Top Selling</h2>
    </div>
    <div id="topRight">
        <div>
            <h2>Filter by</h2>
        </div>
        <div>
            <!-- use html_date()  [refer wis11 demo1] -->
            <input type="date" id="date" name='date'>
        </div>
        <div>
            <button data-post>Go</button>
        </div>
    </div>
</span>


<!-- Table -->

<table class="table">
    <tr>
        <th>Item</th>
        <th>Variation</th>
        <th>Quantity</th>
        <th>Total Earned</th>
    </tr>

    <?php //foreach($records AS $r): 
    ?>
    <!--
    <td><?php $r->name ?></td>
    <td><?php $r->variation_name ?></td>
    <td><?php $r->quantity ?></td>
    <td><?php $r->total ?></td>
    -->
    <?php //endforeach} 
    ?>

</table>

<br><br>

<style>
    <?php ?>div#quantityChart {
        width: 250px;
        height: 250px;
        border-radius: 50%;
        border: black 3px solid;
        background: conic-gradient(#b327b3 0% 20%,
                #87b82d 20% 40%,
                #ca0248 40% 60%,
                #8286de 60% 80%,
                #690e0a 80% 100%);
    }

    div#earnedChart {
        width: 250px;
        height: 250px;
        border-radius: 50%;
        border: black 3px solid;
        background: conic-gradient(#b327b3 0% 20%,
                #87b82d 20% 40%,
                #ca0248 40% 60%,
                #8286de 60% 80%,
                #690e0a 80% 100%);
    }
</style>

<!-- Pie Chart -->
<div id="stats">
    <!-- Top 5 by Quantity -->
    <span id="quantitySector">

        <div id="quantityChart">

        </div>
        <div id="quantityList">
            <h3><u>Quantity</u></h3>
            <ol>
                <?php foreach ($records as $r): ?>
                    <li style="color: #b327b3"><b>Purple:</b> <?php $r->variation_name ?></li>
                    <li style="color: #87b82d"><b>Green:</b> <?php $r->variation_name ?></li>
                    <li style="color: #ca0248"><b>Red:</b> <?php $r->variation_name ?></li>
                    <li style="color: #8286de"><b>Blue:</b> <?php $r->variation_name ?></li>
                    <li style="color: #690e0a"><b>Brown:</b> <?php $r->variation_name ?></li>
                <?php endforeach ?>
            </ol>
        </div>
    </span>

    <!-- Top 5 by Earned -->
    <span id="earnedSector">
        <div id="earnedChart">

        </div>
        <div id="earnedList">
            <h3><u>Total Earned</u></h3>

            <ol>
                <?php foreach ($records as $r): ?>
                    <li style="color: #b327b3"><b>Purple:</b> <?php $r->variation_name ?></li>
                    <li style="color: #87b82d"><b>Green:</b> <?php $r->variation_name ?></li>
                    <li style="color: #ca0248"><b>Red:</b> <?php $r->variation_name ?></li>
                    <li style="color: #8286de"><b>Blue:</b> <?php $r->variation_name ?></li>
                    <li style="color: #690e0a"><b>Brown:</b> <?php $r->variation_name ?></li>
                <?php endforeach ?>
            </ol>
        </div>
    </span>

</div>

<?php
include '../../_foot.php';
