<?php
//file: view/groups/view.php
require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$group = $view->getVariable("group");
$currentuser = $view->getVariable("currentusername");
$newcomment = $view->getVariable("comment");
$errors = $view->getVariable("errors");

$view->setVariable("title", "View Group");

?><h1><?= i18n("Group").": ".htmlentities($group->getTitle()) ?></h1>
<em><?= sprintf(i18n("by %s"),$group->getAuthor()->getUsername()) ?></em>
<p>
	<?= htmlentities($group->getContent()) ?>
</p>

<h2><?= i18n("Comments") ?></h2>

<?php foreach($group->getComments() as $comment): ?>
	<hr>
	<p><?= sprintf(i18n("%s commented..."),$comment->getAuthor()->getUsername()) ?> </p>
	<p><?= $comment->getContent(); ?></p>
<?php endforeach; ?>

<?php if (isset($currentuser) ): ?>
	<h3><?= i18n("Write a comment") ?></h3>

	<form method="POST" action="index.php?controller=comments&amp;action=add">
		<?= i18n("Comment")?>:<br>
		<?= isset($errors["content"])?i18n($errors["content"]):"" ?><br>
		<textarea type="text" name="content"><?=
		htmlentities($newcomment->getContent());
		?></textarea>
		<input type="hidden" name="id" value="<?= $group->getId() ?>" ><br>
		<input type="submit" name="submit" value="<?=i18n("do comment") ?>">
	</form>

<?php endif ?>
