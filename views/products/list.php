<?php
// Products List View
// Displays all products with search and filter options
?>

<div class="products-list">
    <?php if (isset($products) && count($products) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover" id="productsTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['name']; ?></td>
                            <td><code><?php echo $product['sku']; ?></code></td>
                            <td><?php echo $product['category_name']; ?></td>
                            <td><?php echo Helper::formatCurrency($product['selling_price']); ?></td>
                            <td><span class="badge bg-<?php echo $product['quantity'] <= $product['reorder_level'] ? 'danger' : 'success'; ?>"><?php echo $product['quantity']; ?></span></td>
                            <td><span class="badge bg-<?php echo $product['status'] === 'Active' ? 'success' : 'secondary'; ?>"><?php echo $product['status']; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No products found.</div>
    <?php endif; ?>
</div>