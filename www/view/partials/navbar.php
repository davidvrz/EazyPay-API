<?php
// file: view/partials/navbar.php

$currentuser = $view->getVariable("currentusername");
?>
<link rel="stylesheet" type="text/css" href="../../assets/styles/navbar.css">

<nav class="navbar">
    <img class="navbar-logo" src="../../assets/images/logo.png" alt="logo">
    <ul class="navbar-links">
        <li><a href="index.php?controller=groups&amp;action=index">Home</a></li>
        <?php if (isset($currentuser)): ?>
            <li><a 	href="index.php?controller=users&amp;action=logout">Logout</a></li>
            <li><a href="#">Profile of <?= $currentuser ?></a></li>
        <?php else: ?>
            <li><a href="index.php?controller=users&amp;action=register">Register</a></li>
            <li><a href="index.php?controller=users&amp;action=login">Login</a></li>
        <?php endif ?>
    </ul>
    <div class="navbar-toggle">
        <span></span>
        <span></span>
        <span></span>
    </div>
</nav>
