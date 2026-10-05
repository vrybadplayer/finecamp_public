<!DOCTYPE html>
<html lang="en">
<?php
require '../_base.php';


auth('index.php', 'Admin', 'Root');

if (is_post() && !isset($_SESSION['noRecords'])) {
    // Filtered (Selected Date)
    $date = req('date');
    $truncatedDate = truncateDate($date);

    $stm = $_db->prepare('SELECT oi.name, oivo.name AS variation, SUM(oivo.quantity) 
    AS quantity, SUM(oivo.quantity)*oivo.unit_price AS total 
    FROM order_item_variation_option oivo 
    JOIN order_item oi ON oivo.order_item_id = oi.order_item_id 
    JOIN orders o ON oi.order_id = o.order_id 
    WHERE DATE_FORMAT(o.created_datetime, "%Y-%m") LIKE ? 
    GROUP BY oi.name, oivo.name 
    ORDER BY quantity DESC; 
    ');

    $stm->execute([$truncatedDate]);
    $records = $stm->fetchAll();
    $records = array_slice($records, 0, 5);
    $count = count($records);

    if (empty($records)) {
        temp('info', "The Month Has No Records");
        $_SESSION['noRecords'] = 'true';
        redirect();
    }
} else {
    // Default (Current Date)
    unset($_SESSION['noRecords']);
    $date = date('Y-m-d');
    $truncatedDate = truncateDate($date);

    $stm = $_db->prepare('SELECT oi.name, oivo.name AS variation, SUM(oivo.quantity) 
    AS quantity, SUM(oivo.quantity)*oivo.unit_price AS total 
     FROM order_item_variation_option oivo 
     JOIN order_item oi ON oivo.order_item_id = oi.order_item_id 
     JOIN orders o ON oi.order_id = o.order_id 
     WHERE DATE_FORMAT(o.created_datetime, "%Y-%m") LIKE ? 
     GROUP BY oi.name, oivo.name 
     ORDER BY quantity DESC; 
  ');

    $stm->execute([$truncatedDate]);
    $records = $stm->fetchAll();
    $records = array_slice($records, 0, 5);
    $count = count($records);
}

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/base/app.css">
    <link rel="stylesheet" href="/css/admin/app.css">
    <link rel="stylesheet" href="/css/admin/statistics.css">
    <title>Admin Dashboard</title>
    <style>
        body {
            background-color: white;
            display: flex;
            height: 100vh;
            margin: 0;
            /* font-family: Arial, sans-serif; */
        }

        .sidebar {
            width: 250px;
            background-color: #035391;
            padding: 20px;
            color: white;
            display: flex;
            flex-direction: column;
        }

        .nav-link {
            font-size: 18px;
            display: block;
            margin-bottom: 15px;
            text-decoration: none;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }

        .nav-link:hover {
            background-color: #0476D0;
            /* sub color */
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;

        }

        .header {
            background-color: #035391;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .nav-link:hover {
            background-color: white;
            color: #035391;
            border: #035391 1px solid;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
    </style>
</head>

<body>
    <div id="info"><?= temp('info') ?></div>
    <div class="sidebar">
        <h2 style="color: white;">Admin Dashboard</h2> <br><br>
        <a href="user/profile.php" class="nav-link">Admin Profile</a>
        <a href="user/index.php" class="nav-link">Member List</a>
        <?php if ($_user->role == 'Root'): ?>
            <a href="adminMemberList.php" class="nav-link">Admin List</a>
        <?php endif; ?>
        <a href="product/productManagement.php" class="nav-link">Product Management</a>
        <a href="product/categoryManagement.php" class="nav-link">Category Management</a>
        <a href="order/orderHistoryList.php" class="nav-link">Order List</a>
        <a href="../authentication/logout.php" class="nav-link">Logout</a>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>Welcome to Admin Dashboard ,<?= $_user->username ?></h1>
        </div>
        <!-- Main content goes here -->

        <span id="top">
            <div id="topLeft">
                <h2>Top Selling By Quantity</h2>
            </div>
            <form method="POST">
                <div id="topRight">
                    <div>
                        <h2>Filter by month</h2>
                    </div>
                    <div>
                        <?php html_date('date') ?>
                    </div>
                    <div>
                        <button data-post>Go</button>
                    </div>
                </div>
            </form>
        </span>


        <!-- Table -->

        <table class="table">
            <tr>
                <th>Item</th>
                <th>Variation</th>
                <th>Quantity</th>
                <th>Total Earned</th>
            </tr>

            <?php foreach ($records as $r):
            ?>
                <tr>
                    <td>
                        <p><?php echo $r->name ?></p>
                    </td>
                    <td>
                        <p><?php echo $r->variation ?></p>
                    </td>
                    <td>
                        <p><?php echo $r->quantity ?></p>
                    </td>
                    <td>
                        <p><?php echo $r->total ?></p>
                    </td>
                </tr>
            <?php endforeach ?>
        </table>

        <br><br>

        <style>
            /* Calculate the percentages of the quantity and total earned (_base.php)
        The values will be used for Embedded CSS */

            <?php

            $quantityArray = quantityPercentages($records);
            $earnedArray = earnedPercentages($records);


            echo "div#quantityChart {";
            echo "width: 250px;";
            echo "height: 250px;";
            echo "border-radius: 50%;";
            echo "border: black 3px solid;";
            if ($count > 0) {
                echo "background: conic-gradient(#b327b3 0% " . $quantityArray[0];
            }

            if ($count > 1) {
                echo ", #87b82d $quantityArray[0] $quantityArray[1]";
            } else {
                echo ");";
            }

            if ($count > 2) {
                echo ", #ca0248 $quantityArray[1] $quantityArray[2]";
            } else {
                echo ");";
            }

            if ($count > 3) {
                echo ", #8286de $quantityArray[2] $quantityArray[3]";
            } else {
                echo ");";
            }

            if ($count > 4) {
                echo ", #690e0a $quantityArray[3] $quantityArray[4]);";
            } else {
                echo ");";
            }
            echo "}";

            echo "div#quantityChart {";
            echo "width: 250px;";
            echo "height: 250px;";
            echo "border-radius: 50%;";
            echo "border: black 3px solid;";
            if ($count > 0) {
                echo "background: conic-gradient(#b327b3 0% " . $quantityArray[0];
            }

            if ($count > 1) {
                echo ", #87b82d $quantityArray[0] $quantityArray[1]";
            } else {
                echo ");";
            }

            if ($count > 2) {
                echo ", #ca0248 $quantityArray[1] $quantityArray[2]";
            } else {
                echo ");";
            }

            if ($count > 3) {
                echo ", #8286de $quantityArray[2] $quantityArray[3]";
            } else {
                echo ");";
            }

            if ($count > 4) {
                echo ", #690e0a $quantityArray[3] $quantityArray[4]);";
            } else {
                echo ");";
            }
            echo "}";


            echo "div#earnedChart {";
            echo "width: 250px;";
            echo "height: 250px;";
            echo "border-radius: 50%;";
            echo "border: black 3px solid;";
            if ($count > 0) {
                echo "background: conic-gradient(#b327b3 0% " . $earnedArray[0];
            }

            if ($count > 1) {
                echo ", #87b82d $earnedArray[0] $earnedArray[1]";
            } else {
                echo ");";
            }

            if ($count > 2) {
                echo ", #ca0248 $earnedArray[1] $earnedArray[2]";
            } else {
                echo ");";
            }

            if ($count > 3) {
                echo ", #8286de $earnedArray[2] $earnedArray[3]";
            } else {
                echo ");";
            }

            if ($count > 4) {
                echo ", #690e0a $earnedArray[3] $earnedArray[4]);";
            } else {
                echo ");";
            }
            echo "}";

            ?>
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
                        <?php if ($count > 0): ?><li style="color: #b327b3"><b>Purple:</b> <?php echo "&nbsp";
                                                                                            echo $records[0]->name;
                                                                                            echo " (";
                                                                                            echo $records[0]->variation;
                                                                                            echo ")"; ?></li><?php endif; ?>
                        <?php if ($count > 1): ?><li style="color: #87b82d"><b>Green:</b> <?php echo "&nbsp";
                                                                                            echo $records[1]->name;
                                                                                            echo " (";
                                                                                            echo $records[1]->variation;
                                                                                            echo ")"; ?></li><?php endif; ?>
                        <?php if ($count > 2): ?><li style="color: #ca0248"><b>Red:</b> <?php echo "&nbsp";
                                                                                        echo $records[2]->name;
                                                                                        echo " (";
                                                                                        echo $records[2]->variation;
                                                                                        echo ")"; ?></li><?php endif; ?>
                        <?php if ($count > 3): ?><li style="color: #8286de"><b>Blue:</b> <?php echo "&nbsp";
                                                                                            echo $records[3]->name;
                                                                                            echo " (";
                                                                                            echo $records[3]->variation;
                                                                                            echo ")"; ?></li><?php endif; ?>
                        <?php if ($count > 4): ?><li style="color: #690e0a"><b>Brown:</b> <?php echo "&nbsp";
                                                                                            echo $records[4]->name;
                                                                                            echo " (";
                                                                                            echo $records[4]->variation;
                                                                                            echo ")"; ?></li><?php endif; ?>
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
                        <?php if ($count > 0): ?><li style="color: #b327b3"><b>Purple:</b> <?php echo "&nbsp";
                                                                                            echo $records[0]->name;
                                                                                            echo " (";
                                                                                            echo $records[0]->variation;
                                                                                            echo ")"; ?></li><?php endif; ?>
                        <?php if ($count > 1): ?><li style="color: #87b82d"><b>Green:</b> <?php echo "&nbsp";
                                                                                            echo $records[1]->name;
                                                                                            echo " (";
                                                                                            echo $records[1]->variation;
                                                                                            echo ")"; ?></li><?php endif; ?>
                        <?php if ($count > 2): ?><li style="color: #ca0248"><b>Red:</b> <?php echo "&nbsp";
                                                                                        echo $records[2]->name;
                                                                                        echo " (";
                                                                                        echo $records[2]->variation;
                                                                                        echo ")"; ?></li><?php endif; ?>
                        <?php if ($count > 3): ?><li style="color: #8286de"><b>Blue:</b> <?php echo "&nbsp";
                                                                                            echo $records[3]->name;
                                                                                            echo " (";
                                                                                            echo $records[3]->variation;
                                                                                            echo ")"; ?></li><?php endif; ?>
                        <?php if ($count > 4): ?><li style="color: #690e0a"><b>Brown:</b> <?php echo "&nbsp";
                                                                                            echo $records[4]->name;
                                                                                            echo " (";
                                                                                            echo $records[4]->variation;
                                                                                            echo ")"; ?></li><?php endif; ?>
                    </ol>
                </div>
            </span>

        </div>
    </div>


</body>

</html>