<?php
// file: view/expenses/edit.php
require_once(__DIR__."/../../config/ViewManager.php");
$view = ViewManager::getInstance();

$currentuser = $view->getVariable("currentusername");
$group = $view->getVariable("group");
$expense = $view->getVariable("expense"); // El gasto a editar
$errors = $view->getVariable("errors");

$view->setVariable("title", "Edit Expense");

?>
<h1><?= i18n("Edit Expense for Group:") . " " . htmlentities($group->getName()) ?></h1>

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

    <!-- Descripción del gasto -->
    <label for="description"><?= i18n("Description:") ?></label><br>
    <textarea name="description" id="description" required><?= htmlentities($expense->getDescription()) ?></textarea><br>
    <span><?= isset($errors['description']) ? htmlentities($errors['description']) : "" ?></span><br>

    <!-- Monto total del gasto -->
    <label for="totalAmount"><?= i18n("Total Amount:") ?></label><br>
    <input type="number" name="totalAmount" id="totalAmount" min="0" step="0.01" value="<?= htmlentities($expense->getTotalAmount()) ?>" required oninput="updateParticipantAmounts()" /><br>
    <span><?= isset($errors['totalAmount']) ? htmlentities($errors['totalAmount']) : "" ?></span><br>
    
    <!-- Selección del pagador -->
    <label for="payer"><?= i18n("Payer:") ?></label><br>
    <select name="payer" id="payer" required>
        <option value=""><?= i18n("Select Payer") ?></option>
        <?php if ($group->getMembers()): ?>
            <?php foreach ($group->getMembers() as $user): ?>
                <option value="<?= htmlentities($user->getUsername()) ?>" <?= ($user->getUsername() === $expense->getPayer()->getUsername()) ? "selected" : "" ?>>
                    <?= htmlentities($user->getUsername()) ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select><br>
    <span><?= isset($errors['payer']) ? htmlentities($errors['payer']) : "" ?></span><br>

    <h3><?= i18n("Participants") ?></h3>
    <!-- Mostrar los miembros del grupo -->
    <?php if ($group->getMembers()): ?>
        <?php foreach ($group->getMembers() as $user): ?>
            <div>
                <input type="checkbox" name="include[<?= htmlentities($user->getUsername()) ?>]" 
                       id="include_<?= htmlentities($user->getUsername()) ?>" 
                       <?= isset($expense->getParticipants()[$user->getUsername()]) ? 'checked' : '' ?>
                       onchange="toggleParticipant(<?= htmlentities(json_encode($user->getUsername())) ?>)" />
                <label for="participant_<?= htmlentities($user->getUsername()) ?>"><?= htmlentities($user->getUsername()) ?>:</label>
                <input type="number" name="participants[<?= htmlentities($user->getUsername()) ?>]" 
                       id="participant_<?= htmlentities($user->getUsername()) ?>" 
                       min="0" step="0.01" 
                       value="<?= isset($expense->getParticipants()[$user->getUsername()]) ? htmlentities($expense->getParticipants()[$user->getUsername()]) : '0.00' ?>"
                       <?= isset($expense->getParticipants()[$user->getUsername()]) ? '' : 'readonly' ?> />
                <span><?= isset($errors['participants'][$user->getUsername()]) ? htmlentities($errors['participants'][$user->getUsername()]) : "" ?></span><br>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <input type="submit" name="submit" value="<?= i18n("Save Changes") ?>" />
</form>

<script>
    function getSelectedParticipants() {
        return Array.from(document.querySelectorAll("input[name^='participants']")).filter(participant => 
            document.getElementById("include_" + participant.name.split("[")[1].split("]")[0]).checked
        );
    }

    function updateParticipantAmounts() {
        const totalAmount = parseFloat(document.getElementById("totalAmount").value) || 0;
        const selectedParticipants = getSelectedParticipants();
        
        // Dividir el monto total equitativamente entre los participantes seleccionados
        selectedParticipants.forEach(participant => {
            participant.value = (totalAmount / selectedParticipants.length).toFixed(2);
            participant.setAttribute("readonly", "readonly");
        });
    }

    function toggleParticipant(username) {
        const participantInput = document.getElementById("participant_" + username);
        const isChecked = document.getElementById("include_" + username).checked;

        if (!isChecked) {
            participantInput.value = "0.00";
            participantInput.setAttribute("readonly", "readonly");
        } else {
            participantInput.removeAttribute("readonly");
        }
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
