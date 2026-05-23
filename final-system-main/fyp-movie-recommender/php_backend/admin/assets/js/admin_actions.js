/**
 * admin_actions.js
 * Shared administrative actions for MoodAI
 */

function toggleStatus(userId, currentStatus, redirectUrl = 'user.php') {
    const newStatus = currentStatus === 'active' ? 'banned' : 'active';
    const actionLabel = newStatus === 'banned' ? 'BAN' : 'RESTORE';
    const confirmMessage = `CRITICAL ACTION: Are you sure you want to ${actionLabel} this operative (ID: ${userId})?`;

    if (confirm(confirmMessage)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'user_action.php';
        
        const fields = {
            'user_id': userId,
            'action': 'toggle_status',
            'new_status': newStatus,
            'redirect': redirectUrl
        };

        for (const [name, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }
}

function deleteUser(userId) {
    if (confirm('EXTREME CAUTION: Are you sure you want to PERMANENTLY DELETE this operative? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'user_action.php';
        
        const fields = {
            'user_id': userId,
            'action': 'delete_user'
        };

        for (const [name, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }
}
