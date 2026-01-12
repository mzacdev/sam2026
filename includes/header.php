<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="<?php echo defined('SITE_DESCRIPTION') ? SITE_DESCRIPTION : SITE_FULL_NAME . ' (' . SITE_NAME . ')'; ?>">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME . ' (' . SITE_FULL_NAME . ')' : SITE_NAME . ' - ' . SITE_FULL_NAME; ?></title>
    
    <!-- Favicons and Icons -->
    <link rel="icon" type="image/x-icon" href="<?php echo logo(LOGO_FAVICON); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo logo('favicon-16x16.png'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo logo('favicon-32x32.png'); ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo logo('favicon-96x96.png'); ?>">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" sizes="57x57" href="<?php echo logo('apple-icon-57x57.png'); ?>">
    <link rel="apple-touch-icon" sizes="60x60" href="<?php echo logo('apple-icon-60x60.png'); ?>">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo logo('apple-icon-72x72.png'); ?>">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo logo('apple-icon-76x76.png'); ?>">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo logo('apple-icon-114x114.png'); ?>">
    <link rel="apple-touch-icon" sizes="120x120" href="<?php echo logo('apple-icon-120x120.png'); ?>">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo logo('apple-icon-144x144.png'); ?>">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo logo('apple-icon-152x152.png'); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo logo('apple-icon-180x180.png'); ?>">
    <link rel="apple-touch-icon" href="<?php echo logo('apple-icon.png'); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo logo('apple-icon-precomposed.png'); ?>">
    
    <!-- Android Icons -->
    <link rel="icon" type="image/png" sizes="36x36" href="<?php echo logo('android-icon-36x36.png'); ?>">
    <link rel="icon" type="image/png" sizes="48x48" href="<?php echo logo('android-icon-48x48.png'); ?>">
    <link rel="icon" type="image/png" sizes="72x72" href="<?php echo logo('android-icon-72x72.png'); ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo logo('android-icon-96x96.png'); ?>">
    <link rel="icon" type="image/png" sizes="144x144" href="<?php echo logo('android-icon-144x144.png'); ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo logo('android-icon-192x192.png'); ?>">
    
    <!-- Microsoft Tiles -->
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?php echo logo('ms-icon-144x144.png'); ?>">
    <meta name="msapplication-config" content="<?php echo logo('browserconfig.xml'); ?>">
    
    <!-- Web App Manifest -->
    <link rel="manifest" href="<?php echo logo('manifest.json'); ?>">
    <meta name="theme-color" content="#ffffff">
    
    <!-- CoreUI CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.3.0/dist/css/coreui.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/custom.css'); ?>">
    
    <!-- Inline style for background image with PHP path -->
    <style>
        body::before {
            background-image: url('<?php echo asset('img/background/sam2026.png'); ?>') !important;
            background-size: contain !important; /* Show full image without cropping */
            background-position: center center !important; /* Centered */
            opacity: 0.2 !important; /* Visibility level */
        }
        
        /* Hide loading overlay by default - will be shown by JS if needed */
        #loadingOverlay {
            display: none !important;
        }
    </style>
</head>
<body>

