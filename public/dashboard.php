<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/config/Helper.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/Supplier.php';
require_once __DIR__ . '/../app/models/PurchaseOrder.php';
require_once __DIR__ . '/../app/models/Sale.php';

$auth = new AuthController();
$auth->checkSessionTimeout();
$auth->requireLogin();

// Get dashboard statistics
$productModel = new Product();
$supplierModel = new Supplier();
$purchaseModel = new PurchaseOrder();
$saleModel = new Sale();

$totalProducts = $productModel->getTotalCount();
$totalSuppliers = $supplierModel->getTotalCount();
$totalOrders = $purchaseModel->getTotalCount();
$pendingOrders = $purchaseModel->getPendingCount();
$totalSales = $saleModel->getTotalCount();
$totalSalesAmount = $saleModel->getTotalSales();
$lowStockProducts = $productModel->getLowStock();
$recentSales = $saleModel->getRecentSales(5);

$pageTitle = 'Dashboard';
?>

<?php include __DIR__ . '/../views/layouts/header.php'; ?>

<div class="page-header mb-4">
    <h1 class="h2"><i class="fas fa-chart-line"></i> Dashboard</h1>
    <small class="text-muted">Welcome back, <?php echo $_SESSION['user_name']; ?></small>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-left-primary h-100 shadow">
            <div class="card-body">
                <div class="text-primary text-uppercase small font-weight-bold mb-1">
                    <i class="fas fa-box"></i> Total Products
                </div>
                <h3 class="mb-0"><?php echo $totalProducts; ?></h3>
            </div>
            <a href="<?php echo APP_URL; ?>/public/products.php" class="card-footer bg-light small text-muted">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-left-success h-100 shadow">
            <div class="card-body">
                <div class="text-success text-uppercase small font-weight-bold mb-1">
                    <i class="fas fa-industry"></i> Total Suppliers
                </div>
                <h3 class="mb-0"><?php echo $totalSuppliers; ?></h3>
            </div>
            <a href="<?php echo APP_URL; ?>/public/suppliers.php" class="card-footer bg-light small text-muted">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-left-warning h-100 shadow">
            <div class="card-body">
                <div class="text-warning text-uppercase small font-weight-bold mb-1">
                    <i class="fas fa-receipt"></i> Total Orders
                </div>
                <h3 class="mb-0"><?php echo $totalOrders; ?></h3>
                <small class="text-danger"><?php echo $pendingOrders; ?> Pending</small>
            </div>
            <a href="<?php echo APP_URL; ?>/public/purchase_orders.php" class="card-footer bg-light small text-muted">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card border-left-info h-100 shadow">
            <div class="card-body">
                <div class="text-info text-uppercase small font-weight-bold mb-1">
                    <i class="fas fa-dollar-sign"></i> Total Sales
                </div>
                <h3 class="mb-0"><?php echo Helper::formatCurrency($totalSalesAmount); ?></h3>
                <small class="text-muted"><?php echo $totalSales; ?> transactions</small>
            </div>
            <a href="<?php echo APP_URL; ?>/public/sales.php" class="card-footer bg-light small text-muted">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Low Stock Alerts -->
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Low Stock Alerts</h5>
            </div>
            <div class="card-body">
                <?php if (count($lowStockProducts) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Reorder Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($lowStockProducts, 0, 5) as $product): ?>
                                    <tr>
                                        <td>
                                            <small><?php echo $product['name']; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger"><?php echo $product['quantity']; ?></span>
                                        </td>
                                        <td>
                                            <small><?php echo $product['reorder_level']; ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($lowStockProducts) > 5): ?>
                        <a href="<?php echo APP_URL; ?>/public/products.php?filter=low_stock" class="btn btn-sm btn-outline-danger">
                            View All
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> All products have sufficient stock
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-history"></i> Recent Sales</h5>
            </div>
            <div class="card-body">
                <?php if (count($recentSales) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSales as $sale): ?>
                                    <tr>
                                        <td>
                                            <small><?php echo $sale['invoice_number']; ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo $sale['customer_name']; ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo Helper::formatCurrency($sale['net_amount']); ?></strong>
                                        </td>
                                        <td>
                                            <small><?php echo Helper::formatDate($sale['sale_date']); ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="<?php echo APP_URL; ?>/public/sales.php" class="btn btn-sm btn-outline-success">
                        View All Sales
                    </a>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> No sales recorded yet
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
