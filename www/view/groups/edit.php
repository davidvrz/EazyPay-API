<?php
//file: view/groups/edit.php

require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$group = $view->getVariable("group");
$errors = $view->getVariable("errors");
$currentusername = $view->getVariable("currentusername");

$view->setVariable("name", "Edit Group");

?>

<link rel="stylesheet" href="../../assets/styles/groups/add-edit.css" type="text/css">

<div class="main">
    <div class="top-icon">
        <img src="../../assets/images/isotype.png" alt="Groups Icon">
    </div>
    
    <h1 class="main-title"><?= i18n("Modify group") ?></h1>
    
    <form action="index.php?controller=groups&amp;action=edit" method="POST" id="group-form">
        <!-- Group Name -->
        <?= i18n("Name") ?>: <input type="text" name="name" value="<?= isset($_POST["name"]) ? $_POST["name"] : $group->getName() ?>">
        <div class="error-message">
            <?= isset($errors["name"]) ? i18n($errors["name"]) : "" ?><br>
        </div>

        <!-- Group Description -->
        <?= i18n("Description") ?>: <br>
        <textarea name="description" rows="4" cols="50"><?= isset($_POST["description"]) ? htmlentities($_POST["description"]) : htmlentities($group->getDescription()) ?></textarea>
        <div class="error-message">
            <?= isset($errors["description"]) ? i18n($errors["description"]) : "" ?><br>
        </div>

        <!-- Participants Section -->
        <div id="members-container">
            <label for="members"><?= i18n("Participants") ?>:</label>

            <?php
            $members = $group->getMembers();
            $adminAdded = false; // Flag to check if admin is already added

            foreach ($members as $index => $member):
                // Add the admin only once
                if (!$adminAdded && $member->getUsername() === $currentusername):
                    ?>
                    <div class="member-input" id="creator-participant">
                        <input type="text" name="members[]" value="<?= htmlentities($member->getUsername()) ?>" readonly />
                    </div>
                    <?php
                    $adminAdded = true; // Mark that the admin has been added
                elseif ($member->getUsername() !== $currentusername):
                    // Display other members (excluding the admin)
                    ?>
                    <div class="member-input">
                        <input type="text" name="members[]" value="<?= htmlentities($member->getUsername()) ?>" />
                        <button type="button" class="remove-participant" onclick="removeParticipant(this)"><?= i18n("Remove") ?></button>
                    </div>
                    <?php
                endif;
            endforeach;
            ?>
        </div>

        <button type="button" id="add-participant"><?= i18n('Add Participant') ?></button><br>
        
        <div class="error-message">
            <?= isset($errors["members"]) ? i18n($errors["members"]) : "" ?><br>
        </div>

        <input type="hidden" name="id" value="<?= $group->getId() ?>">
        <input type="submit" name="submit" value="<?= i18n("Modify group") ?>">
    </form>
</div>

<!-- JavaScript to add and remove participants -->
<script>
    document.getElementById('add-participant').addEventListener('click', function() {
        // Create a new container for the participant input and remove button
        var memberDiv = document.createElement('div');
        memberDiv.className = 'member-input';
        
        var newInput = document.createElement('input');
        newInput.type = 'text';
        newInput.name = 'members[]';
        newInput.placeholder = '<?= i18n('Enter participant') ?>';

        var removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'remove-participant';
        removeButton.textContent = "<?= i18n("Remove") ?>";
        removeButton.onclick = function() { removeParticipant(removeButton); };

        memberDiv.appendChild(newInput);
        memberDiv.appendChild(removeButton);

        document.getElementById('members-container').appendChild(memberDiv);
    });

    function removeParticipant(button) {
        // Remove the input container and the remove button
        button.parentNode.remove();
    }
</script>



