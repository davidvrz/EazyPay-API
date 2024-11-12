<?php
// file: view/users/login.php

require_once(__DIR__ . "/../../config/ViewManager.php");
$view = ViewManager::getInstance();
$view->setVariable("title", "Login");
$errors = $view->getVariable("errors");
?>

<link rel="stylesheet" type="text/css" href="../../assets/styles/users/auth.css">

<div class="register-section">
    <div class="register-form-container">
        <div class="form-icon">
            <img src="../../assets/images/isotype.png" alt="icon">
        </div>
        <form class="register-form" method="POST" action="index.php?controller=users&amp;action=login">
            <div class="form-group">
                <label for="email"><?= i18n("Username") ?></label>
                <input type="text" id="username" name="username" required>
                <span class="error-message"><?= isset($errors["username"]) ? i18n($errors["username"]) : "" ?></span>
            </div>

            <div class="form-group">
                <label for="password"><?= i18n("Password") ?></label>
                <input type="password" id="password" name="passwd" required>
                <span class="error-message"><?= isset($errors["passwd"]) ? i18n($errors["passwd"]) : "" ?></span>
            </div>

            <div class="form-group show-password">
                <input type="checkbox" id="show-password" onclick="togglePasswordVisibility()">
                <label for="show-password"><?= i18n("Show password") ?></label>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-back" onclick="window.location.href='?controller=user&amp;action=register'"><?= i18n("Back") ?></button>
                <button type="submit" class="btn-register"><?= i18n("Login") ?></button>
            </div>
        </form>

        <p class="alternative-action"><?= i18n("Not a user?") ?> <a href="index.php?controller=users&amp;action=register"><?= i18n("Register here!") ?></a></p>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        var passwordInput = document.getElementById("password");
        passwordInput.type = passwordInput.type === "password" ? "text" : "password";
    }
</script>
