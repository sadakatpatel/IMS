<?php
// Users List View
?>

<div class="users-list">
    <?php if (isset($users) && count($users) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['username']; ?></td>
                            <td><?php echo $user['full_name']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><span class="badge bg-<?php echo $user['role'] === 'Admin' ? 'danger' : 'info'; ?>"><?php echo $user['role']; ?></span></td>
                            <td><span class="badge bg-<?php echo $user['status'] === 'Active' ? 'success' : 'secondary'; ?>"><?php echo $user['status']; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No users found.</div>
    <?php endif; ?>
</div>