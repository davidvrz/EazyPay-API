<?php
//file: view/groups/index.php

require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$groups = $view->getVariable("groups");
$currentuser = $view->getVariable("currentusername");

$view->setVariable("title", "Groups");

?><h1><?=i18n("Groups")?></h1>

<table border="1">
	<tr>
		<th><?= i18n("Title")?></th><th><?= i18n("Admin")?></th><th><?= i18n("Description")?></th>
	</tr>

	<?php foreach ($groups as $group): ?>
		<tr>
			<td>
				<a href="index.php?controller=groups&amp;action=view&amp;id=<?= $group->getId() ?>"><?= htmlentities($group->getTitle()) ?></a>
			</td>
			<td>
				<?= $group->getTitle() ?>
			</td>
			<td>
				<?php
				//show actions ONLY for the admin of the group (if logged)


				if (isset($currentuser) && $currentuser == $group->getAdmin()->getUsername()): ?>

				<?php
				// 'Delete Button': show it as a link, but do POST in order to preserve
				// the good semantic of HTTP
				?>
				<form
				method="POST"
				action="index.php?controller=groups&amp;action=delete"
				id="delete_group_<?= $group->getId(); ?>"
				style="display: inline"
				>

				<input type="hidden" name="id" value="<?= $group->getId() ?>">

				<a href="#" 
				onclick="
				if (confirm('<?= i18n("are you sure?")?>')) {
					document.getElementById('delete_group_<?= $group->getId() ?>').submit()
				}"
				><?= i18n("Delete") ?></a>

			</form>

			&nbsp;

			<?php
			// 'Edit Button'
			?>
			<a href="index.php?controller=groups&amp;action=edit&amp;id=<?= $group->getId() ?>"><?= i18n("Edit") ?></a>

		<?php endif; ?>

	</td>
</tr>
<?php endforeach; ?>

</table>
<?php if (isset($currentuser)): ?>
	<a href="index.php?controller=groups&amp;action=add"><?= i18n("Create group") ?></a>
<?php endif; ?>
