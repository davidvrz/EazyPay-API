<?php
//file: view/users/register.php

require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();
$errors = $view->getVariable("errors");
$user = $view->getVariable("user");
$view->setVariable("title", "Register");
?>
<h1><?= i18n("Register")?></h1>
<form action="index.php?controller=users&amp;action=register" method="POST">
	<?= i18n("Username")?>: <input type="text" name="username"
	value="<?= $user->getUsername() ?>">
	<?= isset($errors["username"])?i18n($errors["username"]):"" ?><br>

	<?= i18n("Password")?>: <input type="password" name="passwd"
	value="">
	<?= isset($errors["passwd"])?i18n($errors["passwd"]):"" ?><br>

	<?= i18n("Email")?>: <input type="text" name="email"
	value="<?= $user->getEmail() ?>">
	<?= isset($errors["email"])?i18n($errors["email"]):"" ?><br>

	<input type="submit" value="<?= i18n("Register")?>">
</form>

<p><?= i18n("Already registered?")?> <a href="index.php?controller=users&amp;action=login"><?= i18n("Log in here!")?></a></p>
<?php $view->moveToFragment("css");?>
<link rel="stylesheet" type="text/css" src="css/style2.css">
<?php $view->moveToDefaultFragment(); ?>