document.addEventListener('DOMContentLoaded', function () {
    loadUsers();

    document.getElementById('userForm').onsubmit = async function (e) {
      e.preventDefault();
      const name = document.getElementById('name').value;
      const email = document.getElementById('email').value;
      await fetch('/api/users', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ name, email })
      });
      loadUsers();
      this.reset();
    };
  });

  async function loadUsers() {
    const res = await fetch('/api/users');
    const users = await res.json();
    const table = document.getElementById('usersTable');
    table.innerHTML = users.map(u =>
      `<tr>
        <td>${u.name}</td>
        <td>${u.email}</td>
        <td><button onclick="deleteUser('${u._id}')">حذف</button></td>
      </tr>`).join('');
  }

  async function deleteUser(id) {
    await fetch('/api/users/' + id, { method: 'DELETE' });
    loadUsers();
  }
