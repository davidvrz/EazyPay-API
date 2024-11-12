document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("splitMode").addEventListener("change", toggleSplitMode);
    document.getElementById("totalAmount").addEventListener("input", updateParticipantAmounts);

    const addCheckboxEvents = () => {
        document.querySelectorAll("input[name^='include']").forEach(checkbox => {
            const username = checkbox.id.split("_")[1];
            checkbox.addEventListener("change", () => toggleParticipant(username));
        });
    };

    addCheckboxEvents();

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
        updateParticipantAmounts();
    }

    window.validateAmounts = function validateAmounts() {
        const totalAmount = parseFloat(document.getElementById("totalAmount").value) || 0;
        const selectedParticipants = getSelectedParticipants();
        let sum = 0;
    
        selectedParticipants.forEach(participant => {
            sum += parseFloat(participant.value) || 0;
        });
    
        // Redondear los valores a dos decimales antes de compararlos
        const roundedTotalAmount = Math.round(totalAmount * 100) / 100;
        const roundedSum = Math.round(sum * 100) / 100;
        console.log("ROUNDED TOTAL: "+roundedTotalAmount);
        console.log("ROUNDED SUM: "+roundedSum);
    
        // Comprobamos si la diferencia es mayor que 0.01
        if (Math.abs(roundedSum - roundedTotalAmount).toFixed(2) > 0.01) {
            alert("The total amount does not match the sum of participant amounts.");
            return false; // Detener el envío si la validación falla
        }
        return true; // Permitir el envío si la validación es correcta
    };
});
