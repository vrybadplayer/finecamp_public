<?php
// Validate recipientName
if ($recipient_name == '' || strlen($recipient_name) > 100) {
    $_err['recipientName'] = 'Invalid Recipient Name (Must be between <b>1 to 100 characters</b>)';
}

// Validate recipientPhoneNo
if ($recipient_phone_no == '' || (strlen($recipient_phone_no) < 10 || strlen($recipient_phone_no) > 11)) {
    $_err['recipientPhoneNo'] = 'Invalid Recipient Phone Number (Must be between <b>10 to 11 characters</b>)';
} else if (preg_match('/^11-/', $recipient_phone_no)) {
    $digits = substr($recipient_phone_no, 3);

    if (!preg_match('/^\d{8}$/', $digits)) {
        $_err['recipientPhoneNo'] = 'Invalid Recipient Phone Number Format (Valid format: <b>11-########</b>)';
    }
} else if (preg_match('/^1\d{1}-/', $recipient_phone_no)) {
    $digits = substr($recipient_phone_no, 3);

    if (!preg_match('/^\d{7}$/', $digits)) {
        $_err['recipientPhoneNo'] = 'Invalid Recipient Phone Number Format (Valid format: <b>1#-#######</b>)';
    }
} else {
    $_err['recipientPhoneNo'] = 'Invalid Recipient Phone Number Format, please use one of the following formats:
        <br><span>(a) <b>11-########</b></span> 
        <br><span>(b) <b>1#-#######</b></span>';
}

// Validate addressLine
if ($address_line == '' || strlen($address_line) > 100) {
    $_err['addressLine'] = 'Invalid Address Line (Must be between <b>1 to 100 characters</b>)';
}

// Validate postcode
if (!preg_match('/^\d{5}$/', $postcode)) {
    $_err['postcode'] = 'Invalid Postcode (Must be <b>5 digits</b>)';
}

// Validate city
if ($city == '' || strlen($city) > 100) {
    $_err['city'] = 'Invalid City (Must be between <b>1 to 100 characters</b>)';
}

// Validate state
if (!ctype_digit($state) || ($state < 0 || $state > count($_state) - 1)) {
    $_err['state'] = 'Invalid State (Please select from the list above)';
}
?>