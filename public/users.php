<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/config/Helper.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/User.php';

$auth = new AuthController();
$auth->checkSessionTimeout();
$auth->requireAdmin();

$userModel = new User();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] === 'delete' && isset($_POST['id'])) {
        // Prevent deleting admin user
        if ($_POST['id'] == 1) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete admin user']);
            exit;
        }
        $result = $userModel->delete($_POST['id']);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = Helper::sanitize($_POST['username'] ?? '');
        $email = Helper::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = Helper::sanitize($_POST['full_name'] ?? '');
        $role = Helper::sanitize($_POST['role'] ?? 'Staff');

        if (empty($username) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }

        if ($userModel->usernameExists($username)) {
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            exit;
        }

        if ($userModel->emailExists($email)) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }

        $result = $userModel->create($username, $email, $password, $full_name, $role);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'full_name' => Helper::sanitize($_POST['full_name'] ?? ''),
            'email' => Helper::sanitize($_POST['email'] ?? ''),
            'role' => Helper::sanitize($_POST['role'] ?? 'Staff')
        ];

        $result = $userModel->update($_POST['id'], $data);
        echo json_encode($result);
        exit;
    }

    if ($_GET['action'] === 'get' && isset($_GET['id'])) {
        $user = $userModel->getById($_GET['id']);
        echo json_encode($user);
        exit;
    }
}

// Get all users
$users = $userModel->getAll(null);

$pageTitle = 'Users';
?>

<?php include __DIR__ . '/../views/layouts/header.php'; ?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h2"><i class="fas fa-users"></i> User Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?php echo $user['username']; ?></strong></td>
                            <td><?php echo $user['full_name']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $user['role'] === 'Admin' ? 'danger' : 'primary'; ?>">
                                    <?php echo $user['role']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $user['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                    <?php echo $user['status']; ?>
                                </span>
                            </td>
                            <td><?php echo Helper::formatDate($user['created_at']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info edit-user" 
                                        data-id="<?php echo $user['id']; ?>"
                                        data-bs-toggle="modal" data-bs-target="#editUserModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($user['id'] !== 1): ?>
                                    <button class="btn btn-sm btn-danger delete-user" 
                                            data-id="<?php echo $user['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-inbox text-muted"></i> No users found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select class="form-select" name="role" required>
                            <option value="Staff">Staff</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editUserId">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name" id="editFullName">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="editEmail">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" id="editRole">
                            <option value="Staff">Staff</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('<?php echo APP_URL; ?>/public/users.php?action=create', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User added successfully');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
});

document.querySelectorAll('.delete-user').forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this user?')) {
            const id = this.dataset.id;
            fetch('<?php echo APP_URL; ?>/public/users.php?action=delete', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User deleted successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    });
});

document.querySelectorAll('.edit-user').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        document.getElementById('editUserId').value = id;
        
        fetch('<?php echo APP_URL; ?>/public/users.php?action=get&id=' + id)
        .then(response => response.json())
        .then(user => {
            document.getElementById('editFullName').value = user.full_name || '';
            document.getElementById('editEmail').value = user.email || '';
            document.getElementById('editRole').value = user.role || 'Staff';
        });
    });
});

document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('id', document.getElementById('editUserId').value);
    
    fetch('<?php echo APP_URL; ?>/public/users.php?action=update', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User updated successfully');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
