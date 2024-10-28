<?php
//file: view/groups/add.php
require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$group = $view->getVariable("group");
$errors = $view->getVariable("errors");

$view->setVariable("title", "Edit Group");

?><h1><?= i18n("Create group")?></h1>
<form action="index.php?controller=groups&amp;action=add" method="POST">
	<?= i18n("Title") ?>: <input type="text" name="title"
	value="<?= $group->getTitle() ?>">
	<?= isset($errors["title"])?i18n($errors["title"]):"" ?><br>

	<?= i18n("Contents") ?>: <br>
	<textarea name="content" rows="4" cols="50"><?=
	htmlentities($group->getContent()) ?></textarea>
	<?= isset($errors["content"])?i18n($errors["content"]):"" ?><br>

	<input type="submit" name="submit" value="submit">
</form>
