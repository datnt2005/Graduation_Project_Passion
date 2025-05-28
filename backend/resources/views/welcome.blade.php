<!DOCTYPE html>
<html>
<head>
    <title>Test User API via Fetch</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Test API: POST /api/users</h1>

    <form id="userForm">
        <label>Name:</label><br>
        <input type="text" name="name"><br><br>

        <label>Email:</label><br>
        <input type="email" name="email"><br><br>

        <label>Password:</label><br>
        <input type="password" name="password"><br><br>

        <label>Phone:</label><br>
        <input type="text" name="phone"><br><br>

        <label>Role:</label><br>
        <select name="role">
            <option value="">-- Select --</option>
            <option value="user">User</option>
            <option value="seller">Seller</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <label>Status:</label><br>
        <select name="status">
            <option value="">-- Select --</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="banned">Banned</option>
        </select><br><br>

        <label>Avatar:</label><br>
        <input type="file" name="avatar"><br><br>

        <button type="submit">Create User</button>
    </form>

    <div id="response" style="margin-top:20px;"></div>

    <hr>

    <h1>Test API: PUT /api/users/{id}</h1>

    <form id="editForm">
        <label>Select User:</label><br>
        <select name="id" id="userSelect">
            <option value="">Loading users...</option>
        </select><br><br>

        <label>Name:</label><br>
        <input type="text" name="name"><br><br>

        <label>Password:</label><br>
        <input type="password" name="password"><br><br>

        <label>Role:</label><br>
        <select name="role">
            <option value="">-- Select --</option>
            <option value="user">User</option>
            <option value="seller">Seller</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <label>Status:</label><br>
        <select name="status">
            <option value="">-- Select --</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="banned">Banned</option>
        </select><br><br>

        <label>Avatar:</label><br>
        <input type="file" name="avatar"><br><br>

        <button type="submit">Update User</button>
    </form>

    <div id="editResponse" style="margin-top:20px;"></div>

    <script>
        // Load all users to populate the <select>
        async function loadUsers() {
            const select = document.getElementById('userSelect');
            try {
                const res = await fetch('http://127.0.0.1:8000/api/users', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                select.innerHTML = '<option value="">-- Select User --</option>';
                data.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = `#${user.id} - ${user.name}`;
                    select.appendChild(option);
                });
            } catch (err) {
                select.innerHTML = '<option value="">Failed to load users</option>';
            }
        }

        loadUsers(); // Call on page load

        // CREATE
        document.getElementById('userForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const res = await fetch('http://127.0.0.1:8000/api/users', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                document.getElementById('response').innerText = JSON.stringify(data, null, 2);
                loadUsers(); // reload user list after adding
            } catch (err) {
                document.getElementById('response').innerText = 'Error: ' + err;
            }
        });

        // UPDATE
        document.getElementById('editForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const userId = formData.get('id');
            formData.delete('id');

            try {
                const res = await fetch(`http://127.0.0.1:8000/api/users/${userId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-HTTP-Method-Override': 'PUT'
                    }
                });
                const data = await res.json();
                document.getElementById('editResponse').innerText = JSON.stringify(data, null, 2);
            } catch (err) {
                document.getElementById('editResponse').innerText = 'Error: ' + err;
            }
        });
    </script>
</body>
</html>
