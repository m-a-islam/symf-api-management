document.addEventListener('DOMContentLoaded', () => {
    const API_BASE_URL = '/api';
    const addKeyForm = document.getElementById('add-key-form');
    const keyIdentifierInput = document.getElementById('key-identifier-input');
    const keysTable = document.getElementById('keys-table'); // <-- Select the TABLE, not the TBODY

    // The fetchKeys function remains the same, but we will make it more robust
    const fetchKeys = async () => {
        try {
            const response = await fetch(`${API_BASE_URL}/keys`);
            if (!response.ok) throw new Error('Failed to fetch keys');
            const keys = await response.json();

            const keysTableBody = keysTable.querySelector('tbody');
            if (!keysTableBody) return; // Defensive check

            keysTableBody.innerHTML = ''; // Clear the table before rendering
            keys.forEach(key => {
                const row = document.createElement('tr');
                row.dataset.keyId = key.id;
                row.innerHTML = `
                    <td>${key.id}</td>
                    <td><span data-field="keyIdentifier">${key.keyIdentifier}</span></td>
                    <td><span data-field="status">${key.status}</span></td>
                    <td>
                        <button class="btn-edit action-button">Edit</button>
                        <button class="btn-toggle action-button" data-status="${key.status}">Toggle</button>
                        <button class="btn-delete action-button">Delete</button>
                    </td>
                `;
                keysTableBody.appendChild(row);
            });
        } catch (error) {
            console.error('Error fetching keys:', error);
        }
    };

    // "Add Key" Form Submission (Remains the same)
    addKeyForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const keyIdentifier = keyIdentifierInput.value.trim();
        if (!keyIdentifier) return;
        try {
            const response = await fetch(`${API_BASE_URL}/keys`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ keyIdentifier })
            });
            if(response.ok) {
                keyIdentifierInput.value = '';
                fetchKeys();
            } else {
                alert('Failed to add key.');
            }
        } catch (error) {
            console.error('Error adding key:', error);
        }
    });

    // --- NEW: Single Event Listener on the TABLE element ---
    keysTable.addEventListener('click', async (e) => {
        const target = e.target;
        const row = target.closest('tr');
        if (!row || !target.classList.contains('action-button')) {
            // If the click was not on a row or not on an action button, do nothing
            return;
        }

        const id = row.dataset.keyId;

        // --- EDIT button clicked ---
        if (target.classList.contains('btn-edit')) {
            const identifierSpan = row.querySelector('span[data-field="keyIdentifier"]');
            const currentIdentifier = identifierSpan.textContent;
            identifierSpan.innerHTML = `<input type="text" value="${currentIdentifier}" />`;
            target.textContent = 'Save';
            target.classList.remove('btn-edit');
            target.classList.add('btn-save');
        }

        // --- SAVE button clicked ---
        else if (target.classList.contains('btn-save')) {
            const identifierInput = row.querySelector('input[type="text"]');
            const newIdentifier = identifierInput.value;
            const currentStatus = row.querySelector('span[data-field="status"]').textContent;
            try {
                const response = await fetch(`${API_BASE_URL}/keys/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ keyIdentifier: newIdentifier, status: currentStatus })
                });
                if(response.ok) fetchKeys();
                else alert('Failed to save key.');
            } catch (error) {
                console.error('Error saving key:', error);
            }
        }

        // --- DELETE, TOGGLE etc. (These can be added inside this same listener) ---
        else if (target.classList.contains('btn-delete')) {
            if (!confirm('Are you sure?')) return;
            try {
                const response = await fetch(`${API_BASE_URL}/keys/${id}`, { method: 'DELETE' });
                if(response.ok) fetchKeys();
                else alert('Failed to delete key.');
            } catch(error){ console.error('Delete failed', error); }
        }

        else if (target.classList.contains('btn-toggle')) {
            const currentStatus = target.dataset.status;
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            try {
                const response = await fetch(`${API_BASE_URL}/keys/${id}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ status: newStatus })
                });
                if(response.ok) fetchKeys();
                else alert('Failed to toggle status.');
            } catch(error){ console.error('Toggle failed', error); }
        }
    });


    // Initial fetch of keys when the page loads
    fetchKeys();
});
