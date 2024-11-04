<?php
//file: view/groups/add.php
require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$group = $view->getVariable("group");
$errors = $view->getVariable("errors");

$view->setVariable("title", "Edit Group");

?><h1><?= i18n("Create group")?></h1>
<form action="index.php?controller=groups&amp;action=add" method="POST">
	<?= i18n("Name") ?>: <input type="text" name="name"
	value="<?= $group->getName() ?>">
	<?= isset($errors["name"])?i18n($errors["name"]):"" ?><br>

	<?= i18n("Description") ?>: <br>
	<textarea name="description" rows="4" cols="50"><?=
	htmlentities($group->getDescription()) ?></textarea>
	<?= isset($errors["description"])?i18n($errors["description"]):"" ?><br>

	<input type="submit" name="submit" value="submit">
</form>
