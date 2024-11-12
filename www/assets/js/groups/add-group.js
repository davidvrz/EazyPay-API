document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('add-participant').addEventListener('click', function() {
        const memberDiv = document.createElement('div');
        memberDiv.className = 'member-input';
        
        const newInput = document.createElement('input');
        newInput.type = 'text';
        newInput.name = 'members[]';
        newInput.placeholder = 'Enter participant';

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'remove-participant';
        removeButton.textContent = 'Remove';
        removeButton.onclick = function() { 
            removeParticipant(removeButton); 
        };

        memberDiv.appendChild(newInput);
        memberDiv.appendChild(removeButton);

        document.getElementById('members-container').appendChild(memberDiv);
    });

    function removeParticipant(button) {
        button.parentNode.remove();
    }
});
