<?php
// file: view/groups/movements.php
require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$group = $view->getVariable("group");
$suggestedMovements = $view->getVariable("suggestedMovements");

$view->setVariable("title", "Suggested Movements");
?>

<link rel="stylesheet" href="../../assets/styles/groups/movements.css" type="text/css">

<div class="main">
    <h1 class="movements-title"><?= i18n("Suggested Movements for Group") . ": " . htmlentities($group->getName()) ?></h1>
    
    <?php if (!empty($suggestedMovements)): ?>
        <ul class="movements-list">
            <?php foreach ($suggestedMovements as $movement): ?>
                <li class="movement-item">
                    <span class="payer"><?= htmlentities($movement["from"]->getUsername()) ?></span> 
                    <span class="movement-action"><?= i18n("should pay") ?></span> 
                    <span class="receiver"><?= htmlentities($movement["to"]->getUsername()) ?></span> 
                    <span class="amount"><?= htmlentities(number_format($movement["amount"], 2)) ?> €</span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="no-movements"><?= i18n("All balances are settled, no movements needed.") ?></p>
    <?php endif; ?>
    
    <div class="back-button">
        <a href="index.php?controller=groups&amp;action=view&amp;id=<?= $group->getId() ?>">
            <?= i18n("Back to Group") ?>
        </a>
    </div>
</div>
