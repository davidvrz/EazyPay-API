<?php
// file: view/groups/view.php
require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$group = $view->getVariable("group");
$currentuser = $view->getVariable("currentusername");
$errors = $view->getVariable("errors");

$view->setVariable("title", "View Group");

?>

<link rel="stylesheet" href="../../assets/styles/groups/view.css" type="text/css">

<div class="main">
    <div class="top-icon">
        <img src="../../assets/images/isotype.png" alt="Groups Icon">
    </div>

    <h1 class="main-title"><?= i18n("Group") . ": " . htmlentities($group->getName()) ?></h1>

    <em><?= sprintf(i18n("by %s"), $group->getAdmin()->getUsername()) ?></em>
    <p><?= htmlentities($group->getDescription()) ?></p>

    <div class="expense-container">
        <h2><?= i18n("Expenses") ?></h2>

        <?php if (!empty($group->getExpenses())): ?>
            <?php foreach ($group->getExpenses() as $expense): ?>
                <div class="expense">
                    <strong><?= htmlentities($expense->getDescription()); ?></strong>
                    <p>
                        <?= sprintf(i18n("%s paid..."), $expense->getPayer()->getUsername()) ?>
                        <?= htmlentities(number_format($expense->getTotalAmount(), 2)) ?>
                    </p>
                    <a href="index.php?controller=expenses&amp;action=view&amp;id=<?= $expense->getId() ?>">
                        <?= i18n("View Details") ?>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><?= i18n("No expenses recorded for this group.") ?></p>
        <?php endif; ?>

        <?php if (isset($currentuser)): ?>
            <div class="add-expense">
                <a href="index.php?controller=expenses&amp;action=add&amp;group_id=<?= $group->getId() ?>"><?= i18n("Add Expense") ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>