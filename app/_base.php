<?php

// ============================================================================
// PHP Setups
// ============================================================================

date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

// ============================================================================
// General Page Functions
// ============================================================================

// Truncate Date
function truncateDate($dateString)
{
    // Validate the date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
        temp('info', 'Invalid Date');
        redirect('');
    }

    // Extract the year and month
    $year = substr($dateString, 0, 4);
    $month = substr($dateString, 5, 2);

    // Return the truncated date
    return $year . '-' . $month;
}

// Calculate Quantity Percentages
function quantityPercentages($records)
{
    $array = null;
    $total = 0;
    $count = count($records);

    foreach ($records as $r) {
        $total += $r->quantity;
    }

    for ($i = 0; $i < $count; $i++) {
        $array[$i] = intval((($records[$i]->quantity) / $total) * 100);
    }

    for ($i = 0; $i < $count; $i++) {
        if ($i == 0) {
            $array[$i] = $array[$i] . "%";
        } else {
            $array[$i] = ($array[$i] + $array[$i - 1]) . "%";
        }
    }

    return $array;
}


// Calculate Earned Percentages
function earnedPercentages($records)
{
    $array = null;
    $total = 0;
    $count = count($records);

    foreach ($records as $r) {
        $total += $r->total;
    }

    for ($i = 0; $i < $count; $i++) {
        $array[$i] = intval((($records[$i]->total) / $total) * 100);
    }

    for ($i = 0; $i < $count; $i++) {
        if ($i == 0) {
            $array[$i] = $array[$i] . "%";
        } else {
            $array[$i] = ($array[$i] + $array[$i - 1]) . "%";
        }
    }

    return $array;
}


// Is GET request?
function is_get()
{
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

// Is POST request?
function is_post()
{
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

// Obtain GET parameter
function get($key, $value = null)
{
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain POST parameter
function post($key, $value = null)
{
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain REQUEST (GET and POST) parameter
function req($key, $value = null)
{
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Redirect to URL
function redirect($url = null)
{
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

// Set or get temporary session variable
function temp($key, $value = null)
{
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    } else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

// Set or get temporary error session variable
function error($key, $value = null)
{
    if ($value !== null) {
        $_SESSION["error_$key"] = $value;
    } else {
        $value = $_SESSION["error_$key"] ?? null;
        unset($_SESSION["error_$key"]);
        return $value;
    }
}


// Obtain uploaded file --> cast to object
function get_file($key, $index = null)
{
    // Check if we are dealing with multiple files (array) or a single file
    if (is_null($index)) {
        // For single file uploads
        $f = $_FILES[$key] ?? null;

        if ($f && $f['error'] == 0) {
            return (object)$f;
        }

        return null;
    } else {
        // For multiple file uploads
        $f = [
            'name' => $_FILES[$key]['name'][$index] ?? null,
            'type' => $_FILES[$key]['type'][$index] ?? null,
            'tmp_name' => $_FILES[$key]['tmp_name'][$index] ?? null,
            'error' => $_FILES[$key]['error'][$index] ?? null,
            'size' => $_FILES[$key]['size'][$index] ?? null,
        ];

        if ($f['error'] == 0) {
            return (object)$f;
        }

        return null;
    }
}


// Crop, resize and save photo
function save_photo($f, $folder, $width = 200, $height = 200)
{
    $photo = uniqid() . '.jpg';

    require_once 'lib/SimpleImage.php';
    $img = new SimpleImage();
    $img->fromFile($f->tmp_name)
        ->thumbnail($width, $height)
        ->toFile("$folder/$photo", 'image/jpeg');

    return $photo;
}

// Is money?
function is_money($value)
{
    return preg_match('/^\-?\d+(\.\d{1,2})?$/', $value);
}

// Is email?
function is_email($value)
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

// Return local root path
function root($path = '')
{
    return "$_SERVER[DOCUMENT_ROOT]/$path";
}

// Return base url (host + port)
function base($path = '')
{
    return "http://$_SERVER[SERVER_NAME]:$_SERVER[SERVER_PORT]/$path";
}

// ============================================================================
// HTML Helpers
// ============================================================================

// Placeholder for TODO
function TODO()
{
    echo '<span>TODO</span>';
}

// Encode HTML special characters
function encode($value)
{
    return htmlentities($value);
}

// Generate <input type='date'>
function html_date($key, $min = '', $max = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='date' id='$key' name='$key' value='$value'
                 min='$min' max='$max' $attr>";
}

// Generate <input type='time'>
function html_time($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='time' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='hidden'>
function html_hidden($key, $attr = '')
{
    $value ??= encode($GLOBALS[$key] ?? '');
    echo "<input type='hidden' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='text'>
function html_text($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr>";
}

function html_textDefined($key, $attr = '', $value = '', $placeholder = '')
{
    echo "<input type='text' id='$key' name='$key' value='$value' $attr placeholder='$placeholder'>";
}

// Generate <input type='password'>
function html_password($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='password' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='number'>
function html_number($key, $min = '', $max = '', $step = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='number' id='$key' name='$key' value='$value'
                min='$min' max='$max' step='$step' $attr>";
}

// Generate <input type='search'>
function html_search($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='search' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='search'>
function html_search_record($key, $value, $attr = '')
{
    // $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='search' id='$key' name='$key' value='$value' $attr>";
}

// Generate <textarea>
function html_textarea($key, $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<textarea id='$key' name='$key' $attr>$value</textarea>";
}

// Generate SINGLE <input type='checkbox'>
function html_checkbox($key, $label = '', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    $status = $value == 1 ? 'checked' : '';
    echo "<label><input type='checkbox' id='$key' name='$key' value='1' $status $attr>$label</label>";
}

// Generate <input type='radio'> list
function html_radios($key, $items, $br = false)
{
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate <select>
function html_select($key, $items, $default = '- Select One -', $attr = '', $attr2 = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $attr2='$id' $state>$text</option>";
    }
    echo '</select>';
}

function html_selectCard($key, $items, $default = '- Select One -', $attr = '')
{
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

// Generate <input type='file'>
function html_file($key, $accept = '', $attr = '')
{
    echo "<input type='file' id='$key' name='$key' accept='$accept' $attr>";
}

// Generate table headers <th>
function table_headers($fields, $sort, $dir, $href = '')
{
    foreach ($fields as $k => $v) {
        $d = 'asc'; // Default direction
        $c = '';    // Default class

        if ($k == $sort) {
            $d = $dir == 'asc' ? 'desc' : 'asc';
            $c = $dir;
        }

        echo "<th><a href='?sort=$k&dir=$d&$href' class='$c'>$v</a></th>";
    }
}

function table_headers_2($fields, $sort, $dir, $href = '')
{
    foreach ($fields as $k => $v) {
        $label = $v['label'];
        $sortable = $v['sortable'];  // Check if the field is sortable

        if ($sortable) {
            $d = 'asc'; // Default direction
            $c = '';    // Default class

            if ($k == $sort) {
                $d = $dir == 'asc' ? 'desc' : 'asc';
                $c = $dir;
            }

            echo "<th><a href='?sort=$k&dir=$d&$href' class='$c'>$label</a></th>";
        } else {
            // If not sortable, just display the label without a link
            echo "<th>$label</th>";
        }
    }
}

function html_button($key, $text, $attr = '')
{
    echo "<button id='$key' name='$key' $attr>$text</button>";
}

function html_h1($h1_id, $text)
{
    echo "<h1 id='$h1_id'>$text</h1>";
}

function html_h2($h2_id, $text)
{
    echo "<h2 id='$h2_id'>$text</h2>";
}

function html_blank_label()
{
    echo "<label id='blank'></label>";
}

// ============================================================================
// Error Handlings
// ============================================================================

// Global error array
$_err = [];

// Generate <span class='err'>
function err($key)
{
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err'>$_err[$key]</span>";
    } else {
        echo '<span></span>';
    }
}

// ============================================================================
// Security
// ============================================================================

// Global user object
$_user = $_SESSION['user'] ?? null;

// Login user
function login($user, $url = '/member/home/home.php')
{
    $_SESSION['user'] = $user;
    redirect($url);
}

//Login admin 
function adminLogin($user, $url = '/admin/adminDashboard.php')
{
    $_SESSION['user'] = $user;
    redirect($url);
}

// Logout user
function logout($url = '/authentication/portal.php')
{
    temp('info', "Logged out");
    unset($_SESSION['user']);
    redirect($url);
}

// Authorization
function auth($path = '', ...$roles)
{
    global $_user;
    if ($_user) {
        if ($roles) {
            if (in_array($_user->role, $roles)) {
                return; // OK
            }
        } else {
            return; // OK
        }
    }
    temp('info', 'Unauthorized Access');
    redirect(base($path));
}

// Strong password
function is_strong_password($password)
{
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,20}$/', $password);
}


// ============================================================================
// Email Functions
// ============================================================================

// Demo Accounts:
// --------------
// AACS3173@gmail.com           npsg gzfd pnio aylm
// BAIT2173.email@gmail.com     ytwo bbon lrvw wclr
// liaw.casual@gmail.com        wtpa kjxr dfcb xkhg
// liawcv1@gmail.com            obyj shnv prpa kzvj

// Initialize and return mail object
function get_mail()
{
    require_once 'lib/PHPMailer.php';
    require_once 'lib/SMTP.php';

    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = 'smtp.gmail.com';
    $m->Port = 587;
    $m->Username = 'AACS3173@gmail.com';
    $m->Password = 'npsg gzfd pnio aylm';
    $m->CharSet = 'utf-8';
    $m->setFrom($m->Username, 'Fine Camp');

    return $m;
}

// ============================================================================
// Shopping Cart
// ============================================================================

// Get shopping cart
function get_cart()
{
    return $_SESSION['cart'] ?? [];
}

// Set shopping cart
function set_cart($cart = [])
{
    $_SESSION['cart'] = $cart;
}

// Update shopping cart
function update_cart($cart_item_id, $product_variation_option_id)
{
    $cart = get_cart();

    if (is_exists($product_variation_option_id, 'product_variation_option', 'product_variation_option_id')) {
        $_db = new PDO('mysql:dbname=finecamp_db', 'root', '', [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        ]);

        $stm = $_db->prepare('
            UPDATE cart_item 
            SET product_variation_option_id = ?
            WHERE cart_item_id = ?
        ');

        $stm->execute([$product_variation_option_id, $cart_item_id]);

        $cart[$cart_item_id] = $product_variation_option_id;
        ksort($cart);
    } else {
        unset($cart[$product_variation_option_id]);
    }

    set_cart($cart);
}

// ============================================================================
// Database Setups and Functions
// ============================================================================

// Global PDO object
$_db = new PDO('mysql:dbname=finecamp_db', 'root', '', [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

// Is unique?
function is_unique($value, $table, $field)
{
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

// Is exists?
function is_exists($value, $table, $field)
{
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}

// ============================================================================
// Global Constants and Variables
// ============================================================================

$_paymentMethod = array(
    "Mastercard" => "Mastercard",
    "Visa" => "Visa"
);


$_state = array(
    "Perlis",
    "Kedah",
    "Penang",
    "Perak",
    "Selangor",
    "Negeri Sembilan",
    "Melaka",
    "Johor",
    "Kelantan",
    "Terengganu",
    "Pahang",
    "Sarawak",
    "Sabah",
    "Federal Territory of Kuala Lumpur",
    "Federal Territory of Putrajaya",
    "Federal Territory of Labuan"
);
$_programs = $_db->query('SELECT * FROM user')->fetchAll(PDO::FETCH_COLUMN);
$_shippingDetail = $_db->query('SELECT * FROM shipping_detail')->fetchAll(PDO::FETCH_COLUMN);

$_orderStatus = array(
    "created" => "created",
    "packaged" => "packaging",
    "shipping" => "shipping",
    "delivered" => "delivery",
    "completed" => "received"
);

// Range 1-10
// TODO