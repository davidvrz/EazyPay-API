<?php
// file: view/expenses/edit.php
require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$currentuser = $view->getVariable("currentusername");
$group = $view->getVariable("group");
$expense = $view->getVariable("expense");
$errors = $view->getVariable("errors");

$view->setVariable("title", "Edit Expense");
?>

<link rel="stylesheet" href="../../assets/styles/expenses/add-edit.css" type="text/css">

<div class="main">
    <div class="top-icon">
        <img src="../../assets/images/isotype.png" alt="Groups Icon">
    </div>

    <h1 class="main-title"><?= i18n("Edit Expense for Group:") . " " . htmlentities($group->getName()) ?></h1>

    <div class="expense-container">
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlentities($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?controller=expenses&amp;action=edit&amp;id=<?= htmlentities($expense->getId()) ?>" onsubmit="return validateAmounts()">
            <input type="hidden" name="group_id" value="<?= $group->getId() ?>" />
            <input type="hidden" name="id" value="<?= $expense->getId() ?>" />

            <label for="description"><?= i18n("Description:") ?></label>
            <textarea name="description" id="description" required><?= htmlentities($expense->getDescription()) ?></textarea>
            <div class="error-message">
                <span><?= isset($errors['description']) ? htmlentities($errors['description']) : "" ?></span>
            </div>

            <label for="totalAmount"><?= i18n("Total Amount:") ?></label>
            <input type="number" name="totalAmount" id="totalAmount" min="0" step="0.01" value="<?= htmlentities($expense->getTotalAmount()) ?>" required oninput="updateParticipantAmounts()" />
            <div class="error-message">
                <span><?= isset($errors['totalAmount']) ? htmlentities($errors['totalAmount']) : "" ?></span>
            </div>

            <label for="payer"><?= i18n("Payer:") ?></label>
            <select name="payer" id="payer" required>
                <?php foreach ($group->getMembers() as $member): ?>
                    <?php $user = $member['member'] ?>
                    <option value="<?= htmlentities($user->getUsername()) ?>" <?= ($user->getUsername() === $expense->getPayer()->getUsername()) ? "selected" : "" ?>>
                        <?= htmlentities($user->getUsername()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="error-message">
                <span><?= isset($errors['payer']) ? htmlentities($errors['payer']) : "" ?></span>
            </div>

            <label for="splitMode"><?= i18n("Split Mode:") ?></label>
            <select id="splitMode" onchange="toggleSplitMode()">
                <option value="equal"><?= i18n("Divide Equally") ?></option>
                <option value="manual" selected><?= i18n("Enter Manually") ?></option>
            </select>

            <h3><?= i18n("Participants") ?></h3>
            <?php 
            // Crear un array con los participantes actuales y sus importes para fácil acceso
            $participantsAmounts = [];
            foreach ($expense->getParticipants() as $participant) {
                $participantsAmounts[$participant['user']->getUsername()] = $participant['amount'];
            }
            ?>

            <?php foreach ($group->getMembers() as $member): ?>
                <?php 
                $user = $member['member'];
                $username = htmlentities($user->getUsername());
                // Obtener el importe actual del participante si está en el array
                $amount = isset($participantsAmounts[$username]) ? htmlentities($participantsAmounts[$username]) : '0.00';
                ?>
                <div>
                    <input type="checkbox" name="include[<?= $username ?>]" 
                           id="include_<?= $username ?>" 
                           <?= isset($participantsAmounts[$username]) ? 'checked' : '' ?>
                           onchange="toggleParticipant('<?= $username ?>')" />
                    <label for="participant_<?= $username ?>"><?= $username ?>:</label>
                    <input type="number" name="participants[<?= $username ?>]" 
                           id="participant_<?= $username ?>" 
                           min="0" step="0.01" 
                           value="<?= $amount ?>"
                           <?= isset($participantsAmounts[$username]) ? '' : 'readonly' ?> />
                    <div class="error-message">
                        <span><?= isset($errors['participants'][$username]) ? htmlentities($errors['participants'][$username]) : "" ?></span>
                    </div>
                </div>
            <?php endforeach; ?>

            <input type="submit" name="submit" value="<?= i18n("Save Changes") ?>" />
        </form>
    </div>
</div>