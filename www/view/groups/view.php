<?php
// file: view/groups/view.php
require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$group = $view->getVariable("group");
$currentuser = $view->getVariable("currentusername");
$errors = $view->getVariable("errors");
$membersWithBalance = $view->getVariable("membersWithBalance");

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

    <!-- Pestañas para Expenses y Balances -->
    <div class="tab-container">
        <button class="tab-button active" onclick="showTab('expenses')"><?= i18n("Expenses") ?></button>
        <button class="tab-button" onclick="showTab('balances')"><?= i18n("Balances") ?></button>
    </div>

    <!-- Contenido de la pestaña de Expenses -->
    <div id="expenses" class="tab-content active">
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

    <!-- Contenido de la pestaña de Balances -->
    <div id="balances" class="tab-content">
        <h2><?= i18n("Members' Balances") ?></h2>
        
        <?php if (!empty($group->getMembers())): ?>
            <ul>
                <?php foreach ($group->getMembers() as $member): ?>
                    <li>
                        <?= htmlentities($member['member']->getUsername()) ?>: 
                        <?= htmlentities(number_format($member['balance'], 2)) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><?= i18n("No balance information available.") ?></p>
        <?php endif; ?>
    </div>


<!-- JavaScript para controlar las pestañas -->
<script>
    function showTab(tabName) {
        // Ocultar todas las secciones de pestañas
        var contents = document.querySelectorAll('.tab-content');
        contents.forEach(function(content) {
            content.classList.remove('active');
        });

        // Remover la clase activa de todos los botones
        var buttons = document.querySelectorAll('.tab-button');
        buttons.forEach(function(button) {
            button.classList.remove('active');
        });

        // Mostrar la pestaña seleccionada y activar su botón
        document.getElementById(tabName).classList.add('active');
        document.querySelector(`button[onclick="showTab('${tabName}')"]`).classList.add('active');
    }
</script>

<style>
    .tab-container {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    .tab-button {
        padding: 10px 20px;
        cursor: pointer;
        border: none;
        background-color: #f1f1f1;
        transition: background-color 0.3s ease;
    }
    .tab-button.active {
        background-color: #ddd;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
</style>
