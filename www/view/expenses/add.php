<?php
//file: view/groups/add.php
require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$currentuser = $view->getVariable("currentusername");
$group = $view->getVariable("group");
$errors = $view->getVariable("errors");

$view->setVariable("title", "Add Expense");

?>

<link rel="stylesheet" href="../../assets/styles/expenses/add-edit.css" type="text/css">
<script src="../../assets/js/expenses/add-expense.js"></script>

<div class="main">
    <div class="top-icon">
        <img src="../../assets/images/isotype.png" alt="Groups Icon">
    </div>

    <h1 class="main-title"><?= i18n("Add Expense to Group:") . " " . htmlentities($group->getName()) ?></h1>

    <div class="expense-container">
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="error-modal">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlentities($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?controller=expenses&amp;action=add" onsubmit="return validateAmounts()">
            <input type="hidden" name="group_id" value="<?= $group->getId() ?>" />

            <!-- Descripción del gasto -->
            <label for="description"><?= i18n("Description:") ?></label>
            <textarea name="description" id="description" required></textarea>
            <div class="error-message">
                <?= isset($errors['description']) ? htmlentities($errors['description']) : "" ?>
            </div>

            <!-- Monto total del gasto -->
            <label for="totalAmount"><?= i18n("Total Amount:") ?></label>
            <input type="number" name="totalAmount" id="totalAmount" min="0" step="0.01" required />
            <div class="error-message">
                <?= isset($errors['totalAmount']) ? htmlentities($errors['totalAmount']) : "" ?>
            </div>

            <!-- Selección del pagador -->
            <label for="payer"><?= i18n("Payer:") ?></label>
            <select name="payer" id="payer" required>
                <?php if ($group->getMembers()): ?>
                    <?php foreach ($group->getMembers() as $member): ?>
                        <?php $user = $member['member'] ?>
                        <option value="<?= htmlentities($user->getUsername()) ?>">
                            <?= htmlentities($user->getUsername()) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <div class="error-message">
                <?= isset($errors['payer']) ? htmlentities($errors['payer']) : "" ?>
            </div>

            <!-- Modo de reparto -->
            <label for="splitMode"><?= i18n("Split Mode:") ?></label>
            <select id="splitMode">
                <option value="equal"><?= i18n("Divide Equally") ?></option>
                <option value="manual"><?= i18n("Enter Manually") ?></option>
            </select>

            <h3><?= i18n("Participants") ?></h3>
            <?php if ($group->getMembers()): ?>
                <?php foreach ($group->getMembers() as $member): ?>
                    <?php 
                    $user = $member['member'];
                    $username = htmlentities($user->getUsername());
                    ?>
                    <div>
                        <input type="checkbox" name="include[<?= $username ?>]" 
                            id="include_<?= $username ?>" 
                            checked />
                        <label for="participant_<?= $username ?>"><?= $username ?>:</label>
                        <input type="number" name="participants[<?= $username ?>]" 
                            id="participant_<?= $username ?>" 
                            min="0" step="0.01" readonly />
                        <div class="error-message">
                            <span><?= isset($errors['participants'][$username]) ? htmlentities($errors['participants'][$username]) : "" ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <input type="submit" name="submit" value="<?= i18n("Add Expense") ?>" />
        </form>
    </div>
</div>
