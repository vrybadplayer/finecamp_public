<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <!-- <link rel="shortcut icon" href=""> -->
    <link rel="shortcut icon" href="/images/base/logo.jpg" type="image/x-icon">
    <script src="/js/base/jquery-3.7.1.min.js"></script>
    <script src="/js/admin/app.js"></script>
    <link rel="stylesheet" href="/css/base/app.css">
    <link rel="stylesheet" href="<?= $_css_file ?>">
</head>

<body>
    <!-- Flash message -->
    <div id="info"><?= temp('info') ?></div>

    <div id="error-info"><?= nl2br(htmlspecialchars(error('error_message'))) ?></div>

    <header>

    </header>

    <nav>

    </nav>

    <main>