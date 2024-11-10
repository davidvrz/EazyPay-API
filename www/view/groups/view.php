<?php
//file: view/groups/view.php
require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$group = $view->getVariable("group");
$currentuser = $view->getVariable("currentusername");
$newexpense = $view->getVariable("expense");
$errors = $view->getVariable("errors");

$view->setVariable("title", "View Group");

?><h1><?= i18n("Group").": ".htmlentities($group->getName()) ?></h1>
<em><?= sprintf(i18n("by %s"),$group->getAdmin()->getUsername()) ?></em>
<p>
	<?= htmlentities($group->getDescription()) ?>
</p>

<h2><?= i18n("Expenses") ?></h2>

<?php foreach($group->getExpenses() as $expense): ?>
	<hr>
	<p><?= sprintf(i18n("%s paid..."),$expense->getPayer()->getUsername()) ?> </p>
	<p><?= $expense->getDescription(); ?></p>
<?php endforeach; ?>

<?php if (isset($currentuser) ): ?>
	<h3><?= i18n("Add a expense") ?></h3>
	<p>
        <a href="index.php?controller=expenses&amp;action=add&amp;group_id=<?= $group->getId() ?>"><?= i18n("Add Expense") ?></a>
    </p>

<?php endif ?>
