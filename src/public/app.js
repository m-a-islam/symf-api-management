document.addEventListener('DOMContentLoaded', () => {
    // We will re-use the API endpoints from our KeyController
    const API_BASE_URL = '/api';

    const addKeyForm = document.getElementById('add-key-form');
    const keyIdentifierInput = document.getElementById('key-identifier-input');

    // --- Hijack the "Add Key" Form Submission ---
    if (addKeyForm) {
        addKeyForm.addEventListener('submit', async (e) => {
            // Prevent the default browser action (a full page reload)
            e.preventDefault();

            const keyIdentifier = keyIdentifierInput.value.trim();
            if (!keyIdentifier) return;

            try {
                const response = await fetch(`${API_BASE_URL}/keys`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ keyIdentifier })
                });

                if (response.ok) {
                    // If successful, just reload the page to see the new key.
                    // A more advanced version would dynamically add a new row.
                    window.location.reload();
                } else {
                    const errorData = await response.json();
                    alert(`Error adding key: ${errorData.error || 'Unknown error'}`);
                }
            } catch (error) {
                console.error('Error adding key:', error);
                alert('An error occurred. Please check the console.');
            }
        });
    }

    // --- Add AJAX functionality to all action buttons (Delete, Toggle) ---
    document.querySelectorAll('.action-button').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault(); // Prevent form submission

            const form = e.target.closest('form');
            const url = form.action;
            const method = form.querySelector('input[name="_method"]')?.value || form.method;
            const isDelete = method.toUpperCase() === 'DELETE';

            if (isDelete && !confirm('Are you sure you want to delete this key?')) {
                return;
            }

            try {
                // We use our API endpoints here for consistency
                const apiURL = url.replace('/key/', '/api/keys/').replace('/delete', '').replace('/toggle-status', '');
                let apiMethod = 'POST';
                let body = null;

                if (isDelete) {
                    apiMethod = 'DELETE';
                } else { // It's a toggle
                    apiMethod = 'PATCH';
                    const currentStatus = e.target.dataset.status;
                    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
                    body = JSON.stringify({ status: newStatus });
                }

                const response = await fetch(apiURL, {
                    method: apiMethod,
                    headers: { 'Content-Type': 'application/json' },
                    body: body
                });

                if (response.ok) {
                    window.location.reload(); // Simple refresh to see the change
                } else {
                    alert('An error occurred.');
                }

            } catch (error) {
                console.error('Action failed:', error);
                alert('An error occurred. Please check the console.');
            }
        });
    });
});
