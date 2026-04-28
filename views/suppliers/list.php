<?php
// Suppliers List View
?>

<div class="suppliers-list">
    <?php if (isset($suppliers) && count($suppliers) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td><?php echo $supplier['name']; ?></td>
                            <td><?php echo $supplier['contact_person']; ?></td>
                            <td><?php echo $supplier['email']; ?></td>
                            <td><?php echo $supplier['phone']; ?></td>
                            <td><span class="badge bg-<?php echo $supplier['status'] === 'Active' ? 'success' : 'secondary'; ?>"><?php echo $supplier['status']; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No suppliers found.</div>
    <?php endif; ?>
</div>