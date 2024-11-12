<?php
// file: view/users/register.php

require_once(__DIR__ . "/../../config/ViewManager.php");
$view = ViewManager::getInstance();
$errors = $view->getVariable("errors");
$user = $view->getVariable("user");
$view->setVariable("title", "Register");
?>

<link rel="stylesheet" type="text/css" href="../../assets/styles/users/auth.css">

<div class="register-section">
    <div class="register-form-container">
        <div class="form-icon">
            <img src="../../assets/images/isotype.png" alt="icon">
        </div>
        <form class="register-form" method="POST" action="index.php?controller=users&amp;action=register">
            <div class="form-group">
                <label for="username"><?= i18n("Username") ?></label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user ? $user->getUsername() : "") ?>" required>
                <span class="error-message"><?= isset($errors["username"]) ? i18n($errors["username"]) : "" ?></span>
            </div>

            <div class="form-group">
                <label for="email"><?= i18n("Email") ?></label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user ? $user->getEmail() : "") ?>" required>
                <span class="error-message"><?= isset($errors["email"]) ? i18n($errors["email"]) : "" ?></span>
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
                <button type="submit" class="btn-register"><?= i18n("Register") ?></button>
            </div>
        </form>

        <p class="alternative-action"><?= i18n("Already registered?") ?> <a href="index.php?controller=users&amp;action=login"><?= i18n("Log in here!") ?></a></p>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        var passwordInput = document.getElementById("password");
        passwordInput.type = passwordInput.type === "password" ? "text" : "password";
    }
</script>
