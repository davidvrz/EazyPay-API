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

<form method="POST" action="index.php?controller=expenses&amp;action=add" onsubmit="return validateAmounts()">
    <input type="hidden" name="group_id" value="<?= $group->getId() ?>" />

    <!-- Descripción del gasto -->
    <label for="description"><?= i18n("Description:") ?></label><br>
    <textarea name="description" id="description" required></textarea><br>
    <span><?= isset($errors['description']) ? htmlentities($errors['description']) : "" ?></span><br>

    <!-- Monto total del gasto -->
    <label for="totalAmount"><?= i18n("Total Amount:") ?></label><br>
    <input type="number" name="totalAmount" id="totalAmount" min="0" step="0.01" required oninput="updateParticipantAmounts()" /><br>
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

    <!-- Modo de reparto -->
    <label for="splitMode"><?= i18n("Split Mode:") ?></label><br>
    <select id="splitMode" onchange="toggleSplitMode()">
        <option value="equal"><?= i18n("Divide Equally") ?></option>
        <option value="manual"><?= i18n("Enter Manually") ?></option>
    </select><br>

    <h3><?= i18n("Participants") ?></h3>
    <!-- Mostrar los miembros del grupo -->
    <?php if ($group->getMembers()): ?>
        <?php foreach ($group->getMembers() as $user): ?>
            <div>
                <input type="checkbox" name="include[<?= htmlentities($user->getUsername()) ?>]" 
                       id="include_<?= htmlentities($user->getUsername()) ?>" 
                       checked onchange="toggleParticipant(<?= htmlentities(json_encode($user->getUsername())) ?>)" />
                <label for="participant_<?= htmlentities($user->getUsername()) ?>"><?= htmlentities($user->getUsername()) ?>:</label>
                <input type="number" name="participants[<?= htmlentities($user->getUsername()) ?>]" 
                       id="participant_<?= htmlentities($user->getUsername()) ?>" 
                       min="0" step="0.01" readonly />
                <span><?= isset($errors['participants'][$user->getUsername()]) ? htmlentities($errors['participants'][$user->getUsername()]) : "" ?></span><br>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <input type="submit" name="submit" value="<?= i18n("Add Expense") ?>" />
</form>

<script>
    function getSelectedParticipants() {
        return Array.from(document.querySelectorAll("input[name^='participants']")).filter(participant => 
            document.getElementById("include_" + participant.name.split("[")[1].split("]")[0]).checked
        );
    }

    function updateParticipantAmounts() {
        const totalAmount = parseFloat(document.getElementById("totalAmount").value) || 0;
        const splitMode = document.getElementById("splitMode").value;
        const selectedParticipants = getSelectedParticipants();
        
        if (splitMode === "equal") {
            const splitAmount = totalAmount / selectedParticipants.length;
            selectedParticipants.forEach(participant => {
                participant.value = splitAmount.toFixed(2);
                participant.setAttribute("readonly", "readonly");
            });
        } else {
            selectedParticipants.forEach(participant => participant.removeAttribute("readonly"));
        }
    }

    function toggleSplitMode() {
        const splitMode = document.getElementById("splitMode").value;
        
        if (splitMode === "manual") {
            document.querySelectorAll("input[name^='participants']").forEach(participant => {
                participant.value = "0.00";
                participant.removeAttribute("readonly");
            });
        } else {
            document.querySelectorAll("input[name^='participants']").forEach(participant => {
                participant.setAttribute("readonly", "readonly");
            });
            updateParticipantAmounts();
        }
    }

    function toggleParticipant(username) {
        const participantInput = document.getElementById("participant_" + username);
        const isChecked = document.getElementById("include_" + username).checked;
        const splitMode = document.getElementById("splitMode").value;

        if (!isChecked) {
            participantInput.value = "0.00";
            participantInput.setAttribute("readonly", "readonly");
        } else if (splitMode === "manual") {
            participantInput.removeAttribute("readonly");
        }
        updateParticipantAmounts(); // Actualiza para recalcular al activar/desactivar en modo "Dividir Equitativamente"
    }

    function validateAmounts() {
        const totalAmount = parseFloat(document.getElementById("totalAmount").value) || 0;
        const selectedParticipants = getSelectedParticipants();
        let sum = 0;

        selectedParticipants.forEach(participant => {
            sum += parseFloat(participant.value) || 0;
        });

        if (Math.abs(sum - totalAmount) > 0.01) {  // Permite una pequeña tolerancia de decimales
            alert("The total amount does not match the sum of participant amounts.");
            return false;
        }
        return true;
    }
</script>


