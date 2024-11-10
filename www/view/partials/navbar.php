<?php
// file: view/partials/navbar.php

$currentuser = $view->getVariable("currentusername");
?>

<link rel="stylesheet" type="text/css" href="../../assets/styles/partials/navbar.css">

<nav class="navbar">
    <img class="navbar-logo" src="../../assets/images/logo.png" alt="logo">
    <ul class="navbar-links">
        <li><a href="index.php?controller=groups&amp;action=index"><?= i18n("Home") ?></a></li>
        <?php if (isset($currentuser)): ?>
            <li><a href="index.php?controller=users&amp;action=logout"><?= i18n("Logout") ?></a></li>
            <li><a href="#"><?= i18n("Profile of") . " " . htmlentities($currentuser) ?></a></li>
        <?php else: ?>
            <li><a href="index.php?controller=users&amp;action=register"><?= i18n("Register") ?></a></li>
            <li><a href="index.php?controller=users&amp;action=login"><?= i18n("Login") ?></a></li>
        <?php endif; ?>
    </ul>
    <div class="navbar-toggle">
        <span></span>
        <span></span>
        <span></span>
    </div>
</nav>
