<?php
// file: view/groups/view.php
require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$group = $view->getVariable("group");
$currentuser = $view->getVariable("currentusername");

$view->setVariable("title", "View Group");
?>

<link rel="stylesheet" href="../../assets/styles/groups/view.css" type="text/css">
<script src="../../assets/js/groups/tabs.js"></script>

<div class="main">
    <div class="top-icon">
        <img src="../../assets/images/isotype.png" alt="Groups Icon">
    </div>

    <h1 class="main-title"><?= i18n("Group") . ": " . htmlentities($group->getName()) ?></h1>
    <em><?= sprintf(i18n("created by %s"), $group->getAdmin()->getUsername()) ?></em>
    <p><?= htmlentities($group->getDescription()) ?></p>

    <!-- Pestañas para Expenses y Balances -->
    <div class="tab-container">
        <button class="tab-button active" data-tab="expenses"><?= i18n("Expenses") ?></button>
        <button class="tab-button" data-tab="balances"><?= i18n("Balances") ?></button>
    </div>


    <!-- Contenido de la pestaña de Expenses -->
    <div id="expenses" class="tab-content active">
        <h2 class="tab-content-title"><?= i18n("Expenses") ?></h2>

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

    <!-- Contenido de la pestaña de Balances -->
    <div id="balances" class="tab-content">
        <h2 class="tab-content-title"><?= i18n("Members Balances") ?></h2>
        
        <?php if (!empty($group->getMembers())): ?>
            <ul>
                <?php foreach ($group->getMembers() as $member): ?>
                    <li>
                        <?= htmlentities($member['member']->getUsername()) ?>: 
                        <?= htmlentities(number_format($member['balance'], 2)) ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="suggested-movements">
                <a href="index.php?controller=groups&amp;action=suggestedMovements&amp;id=<?= $group->getId() ?>" class="suggested-movements-button">
                    <?= i18n("View Suggested Movements") ?>
                </a>
            </div>

        <?php else: ?>
            <p><?= i18n("No balance information available.") ?></p>
        <?php endif; ?>
    </div>
</div>
