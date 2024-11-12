<?php
//file: view/groups/view.php
require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$currentuser = $view->getVariable("currentusername");
$expense = $view->getVariable("expense");
$group = $view->getVariable("group");

$view->setVariable("title", "View Group");
?>

<link rel="stylesheet" href="../../assets/styles/expenses/view.css" type="text/css">

<div class="main">
    <div class="top-icon">
        <img src="../../assets/images/isotype.png" alt="Groups Icon">
    </div>

    <h1 class="main-title"><?= i18n("Expense Details") ?></h1>

    <div class="expense-container">
        
        <?php if (isset($expense)): ?>
            <p><strong><?= i18n("Description:") ?></strong> <?= htmlentities($expense->getDescription()) ?></p>
            <p><strong><?= i18n("Total Amount:") ?></strong> <?= htmlentities(number_format($expense->getTotalAmount(), 2)) ?></p>
            <p><strong><?= i18n("Payer:") ?></strong> <?= htmlentities($expense->getPayer()->getUsername()) ?></p>

            <h3><?= i18n("Participants") ?></h3>
            <ul>
                <?php foreach ($expense->getParticipants() as $participant): ?>
                    <li>
                        <?= htmlentities($participant['user']->getUsername()) ?>: 
                        <?= htmlentities($participant['amount']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <?php if ($currentuser === $expense->getPayer()->getUsername() || $currentuser === $group->getAdmin()->getUsername()): ?>
                <!-- Mostrar solo si el usuario es el que creó el gasto o tiene permisos -->
                <a href="index.php?controller=expenses&amp;action=edit&amp;id=<?= htmlentities($expense->getId()) ?>" class="btn"><?= i18n("Edit Expense") ?></a>

                <!-- Botón de eliminar con confirmación -->
                <a href="index.php?controller=expenses&amp;action=delete&amp;id=<?= htmlentities($expense->getId()) ?>" class="btn" onclick="return confirm('<?= i18n("Are you sure you want to delete this expense?") ?>');"><?= i18n("Delete Expense") ?></a>
            <?php endif; ?>

        <?php else: ?>
            <p><?= i18n("Expense not found.") ?></p>
        <?php endif; ?>

        <div class="back-button-container">
            <a href="index.php?controller=groups&action=view&id=<?= htmlentities($expense->getGroup()->getId()) ?>" class="btn"><?= i18n("Back to Group") ?></a>
        </div>
    </div>
</div>
