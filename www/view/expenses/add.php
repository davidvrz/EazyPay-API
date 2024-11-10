<?php
// file: view/expenses/add.php
require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$currentuser = $view->getVariable("currentusername");
$group = $view->getVariable("group");
$errors = $view->getVariable("errors");

$view->setVariable("title", "Add Expense");

?>
<h1><?= i18n("Add Expense to Group:") . " " . htmlentities($group->getName()) ?></h1>

<?php if (isset($errors) && !empty($errors)): ?>
    <div class="errors">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlentities($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="index.php?controller=expenses&amp;action=add">
    <input type="hidden" name="group_id" value="<?= $group->getId() ?>" />

    <!-- Descripción del gasto -->
    <label for="description"><?= i18n("Description:") ?></label><br>
    <textarea name="description" id="description" required></textarea><br>
    <span><?= isset($errors['description']) ? htmlentities($errors['description']) : "" ?></span><br>

    <!-- Monto total del gasto -->
    <label for="totalAmount"><?= i18n("Total Amount:") ?></label><br>
    <input type="number" name="totalAmount" id="totalAmount" min="0" step="0.01" required /><br>
    <span><?= isset($errors['totalAmount']) ? htmlentities($errors['totalAmount']) : "" ?></span><br>
    
    <!-- Selección del pagador -->
    <label for="payer"><?= i18n("Payer:") ?></label><br>
    <select name="payer" id="payer" required>
        <option value=""><?= i18n("Select Payer") ?></option>
        <?php if ($group->getMembers()): ?>
            <?php foreach ($group->getMembers() as $user): ?>
                <option value="<?= htmlentities($user->getUsername()) ?>"><?= htmlentities($user->getUsername()) ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select><br>
    <span><?= isset($errors['payer']) ? htmlentities($errors['payer']) : "" ?></span><br>

    <h3><?= i18n("Participants") ?></h3>
    <!-- Mostrar los miembros del grupo -->
    <?php if ($group->getMembers()): ?>
        <?php foreach ($group->getMembers() as $user): ?>
            <label for="participant_<?= htmlentities($user->getUsername()) ?>"><?= htmlentities($user->getUsername()) ?>:</label>
            <input type="number" name="participants[<?= htmlentities($user->getUsername()) ?>]" id="participant_<?= htmlentities($user->getUsername()) ?>" min="0" step="0.01" />
            <span><?= isset($errors['participants'][$user->getUsername()]) ? htmlentities($errors['participants'][$user->getUsername()]) : "" ?></span><br>
        <?php endforeach; ?>
    <?php endif; ?>

    <input type="submit" name="submit" value="<?= i18n("Add Expense") ?>" />
</form>
