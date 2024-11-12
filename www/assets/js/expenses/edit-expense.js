document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("totalAmount").addEventListener("input", updateParticipantAmounts);

    document.querySelectorAll("input[name^='include']").forEach(checkbox => {
        const username = checkbox.id.split("_")[1];
        checkbox.addEventListener("change", () => toggleParticipant(username));
    });
});

function getSelectedParticipants() {
    return Array.from(document.querySelectorAll("input[name^='participants']")).filter(participant => 
        document.getElementById("include_" + participant.name.split("[")[1].split("]")[0]).checked
    );
}

function updateParticipantAmounts() {
    const totalAmount = parseFloat(document.getElementById("totalAmount").value) || 0;
    const selectedParticipants = getSelectedParticipants();
    
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

    if (Math.abs(sum - totalAmount) > 0.01) {
        alert("The total amount does not match the sum of participant amounts.");
        return false;
    }
    return true;
}
