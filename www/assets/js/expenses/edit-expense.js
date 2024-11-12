document.addEventListener("DOMContentLoaded", function () {
    // Asignar los eventos de los elementos del DOM
    document.getElementById("splitMode").addEventListener("change", toggleSplitMode);
    document.getElementById("totalAmount").addEventListener("input", updateParticipantAmounts);

    // Añadir eventos a los checkboxes de los participantes
    const addCheckboxEvents = () => {
        document.querySelectorAll("input[name^='include']").forEach(checkbox => {
            const username = checkbox.id.split("_")[1];
            checkbox.addEventListener("change", () => toggleParticipant(username));
        });
    };

    addCheckboxEvents();

    // Función para obtener los participantes seleccionados
    function getSelectedParticipants() {
        return Array.from(document.querySelectorAll("input[name^='participants']")).filter(participant => 
            document.getElementById("include_" + participant.name.split("[")[1].split("]")[0]).checked
        );
    }

    // Función para actualizar los montos de los participantes si el modo de división es "igual"
    function updateParticipantAmounts() {
        const totalAmount = parseFloat(document.getElementById("totalAmount").value) || 0;
        const splitMode = document.getElementById("splitMode").value;
        const selectedParticipants = getSelectedParticipants();
        
        if (splitMode === "equal" && selectedParticipants.length > 0) {
            const splitAmount = totalAmount / selectedParticipants.length;
            selectedParticipants.forEach(participant => {
                participant.value = splitAmount.toFixed(2);
                participant.setAttribute("readonly", "readonly");
            });
        } else {
            selectedParticipants.forEach(participant => participant.removeAttribute("readonly"));
        }
    }

    // Función para manejar el cambio de modo de división
    function toggleSplitMode() {
        const splitMode = document.getElementById("splitMode").value;
        
        if (splitMode === "manual") {
            document.querySelectorAll("input[name^='participants']").forEach(participant => {
                participant.removeAttribute("readonly");
            });
        } else {
            updateParticipantAmounts();
        }
    }

    // Función para manejar el cambio de estado de los participantes (si están seleccionados o no)
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

    // Función para validar que la suma de los montos coincida con el monto total
    function validateAmounts() {
        const totalAmount = parseFloat(document.getElementById("totalAmount").value) || 0;
        const selectedParticipants = getSelectedParticipants();
        let sum = 0;

        selectedParticipants.forEach(participant => {
            sum += parseFloat(participant.value) || 0;
        });

        // Redondear ambos valores a 2 decimales para evitar problemas de precisión con decimales
        const roundedTotalAmount = Math.round(totalAmount * 100) / 100;
        const roundedSum = Math.round(sum * 100) / 100;

        if (Math.abs(roundedSum - roundedTotalAmount) > 0.01) {
            alert("The total amount does not match the sum of participant amounts.");
            return false;
        }
        return true;
    }

});
