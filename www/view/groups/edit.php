<?php
//file: view/groups/edit.php

require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$group = $view->getVariable("group");
$errors = $view->getVariable("errors");

$view->setVariable("title", "Edit Group");

?><h1><?= i18n("Modify group") ?></h1>
<form action="index.php?controller=groups&amp;action=edit" method="POST">
	<?= i18n("Title") ?>: <input type="text" name="title"
	value="<?= isset($_POST["title"])?$_POST["title"]:$group->getTitle() ?>">
	<?= isset($errors["title"])?i18n($errors["title"]):"" ?><br>

	<?= i18n("Contents") ?>: <br>
	<textarea name="content" rows="4" cols="50"><?=
	isset($_POST["content"])?
	htmlentities($_POST["content"]):
	htmlentities($group->getContent())
	?></textarea>
	<?= isset($errors["content"])?i18n($errors["content"]):"" ?><br>

	<input type="hidden" name="id" value="<?= $group->getId() ?>">
	<input type="submit" name="submit" value="<?= i18n("Modify group") ?>">
</form>
