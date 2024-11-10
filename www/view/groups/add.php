<?php
//file: view/groups/add.php
require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$group = $view->getVariable("group");
$errors = $view->getVariable("errors");

$view->setVariable("title", "Add Group");

?><h1><?= i18n("Create group")?></h1>
<form action="index.php?controller=groups&amp;action=add" method="POST" id="group-form">
    <?= i18n("Name") ?>: <input type="text" name="name" value="<?= $group->getName() ?>">
    <?= isset($errors["name"]) ? i18n($errors["name"]) : "" ?><br>

    <?= i18n("Description") ?>: <br>
    <textarea name="description" rows="4" cols="50"><?= htmlentities($group->getDescription()) ?></textarea>
    <?= isset($errors["description"]) ? i18n($errors["description"]) : "" ?><br>

    <!-- Section for members -->
    <label for="members"><?= i18n("Participants") ?>:</label><br>

    <!-- Container for dynamic participant input fields -->
    <div id="members-container">
        <input type="text" name="members[]" placeholder="<?= i18n('Enter participant name') ?>" /><br>
    </div>
    
    <button type="button" id="add-participant"><?= i18n('Add Participant') ?></button><br>

    <?= isset($errors["members"]) ? i18n($errors["members"]) : "" ?><br>

    <input type="submit" name="submit" value="<?= i18n("Create Group") ?>">
</form>

<!-- JavaScript to add new members -->
<script>
    document.getElementById('add-participant').addEventListener('click', function() {
        // Create a new input element for participant
        var newInput = document.createElement('input');
        newInput.type = 'text';
        newInput.name = 'members[]';
        newInput.placeholder = '<?= i18n('Enter participant name') ?>';
        
        // Append the new input below the existing ones
        var container = document.getElementById('members-container');
        var br = document.createElement('br');
        container.appendChild(newInput);
        container.appendChild(br);
    });
</script>
