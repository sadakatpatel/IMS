<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/config/Helper.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/Supplier.php';
require_once __DIR__ . '/../app/models/PurchaseOrder.php';
require_once __DIR__ . '/../app/models/Sale.php';

$auth = new AuthController();
$auth->checkSessionTimeout();
$auth->requireLogin();

$productModel = new Product();
$supplierModel = new Supplier();
$poModel = new PurchaseOrder();
$saleModel = new Sale();

// Handle export requests
if (isset($_GET['export'])) {
    if ($_GET['export'] === 'products') {
        $products = $productModel->getAll();
        $headers = ['ID', 'Name', 'SKU', 'Category', 'Purchase Price', 'Selling Price', 'Quantity', 'Status'];
        $data = [];
        foreach ($products as $p) {
            $data[] = [$p['id'], $p['name'], $p['sku'], $p['category_name'], $p['purchase_price'], $p['selling_price'], $p['quantity'], $p['status']];
        }
        Helper::exportExcel('products_' . date('Y-m-d') . '.xls', $headers, $data);
    }

    if ($_GET['export'] === 'suppliers') {
        $suppliers = $supplierModel->getAll();
        $headers = ['ID', 'Name', 'Contact Person', 'Email', 'Phone', 'City', 'State', 'Status'];
        $data = [];
        foreach ($suppliers as $s) {
            $data[] = [$s['id'], $s['name'], $s['contact_person'], $s['email'], $s['phone'], $s['city'], $s['state'], $s['status']];
        }
        Helper::exportExcel('suppliers_' . date('Y-m-d') . '.xls', $headers, $data);
    }

    if ($_GET['export'] === 'orders') {
        $orders = $poModel->getAll();
        $headers = ['Order Number', 'Supplier', 'Order Date', 'Total Amount', 'Status'];
        $data = [];
        foreach ($orders as $o) {
            $data[] = [$o['order_number'], $o['supplier_name'], $o['order_date'], $o['total_amount'], $o['status']];
        }
        Helper::exportExcel('purchase_orders_' . date('Y-m-d') . '.xls', $headers, $data);
    }

    if ($_GET['export'] === 'sales') {
        $sales = $saleModel->getAll();
        $headers = ['Invoice Number', 'Customer', 'Sale Date', 'Total', 'Tax', 'Net Amount', 'Payment Status', 'Status'];
        $data = [];
        foreach ($sales as $s) {
            $data[] = [$s['invoice_number'], $s['customer_name'], $s['sale_date'], $s['total_amount'], $s['tax_amount'], $s['net_amount'], $s['payment_status'], $s['status']];
        }
        Helper::exportExcel('sales_' . date('Y-m-d') . '.xls', $headers, $data);
    }
}

// Get statistics for reports
$totalProducts = $productModel->getTotalCount();
$totalSuppliers = $supplierModel->getTotalCount();
$totalOrders = $poModel->getTotalCount();
$totalSales = $saleModel->getTotalSales();
$lowStockCount = count($productModel->getLowStock());

$pageTitle = 'Reports';
?>

<?php include __DIR__ . '/../views/layouts/header.php'; ?>

<div class="page-header mb-4">
    <h1 class="h2"><i class="fas fa-file-excel"></i> Reports & Exports</h1>
</div>

<!-- Report Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <h5 class="card-title"><i class="fas fa-box text-primary"></i> Products</h5>
                <h3 class="mb-3"><?php echo $totalProducts; ?></h3>
                <a href="?export=products" class="btn btn-sm btn-primary">
                    <i class="fas fa-download"></i> Export Excel
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <h5 class="card-title"><i class="fas fa-industry text-success"></i> Suppliers</h5>
                <h3 class="mb-3"><?php echo $totalSuppliers; ?></h3>
                <a href="?export=suppliers" class="btn btn-sm btn-success">
                    <i class="fas fa-download"></i> Export Excel
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <h5 class="card-title"><i class="fas fa-receipt text-warning"></i> Orders</h5>
                <h3 class="mb-3"><?php echo $totalOrders; ?></h3>
                <a href="?export=orders" class="btn btn-sm btn-warning">
                    <i class="fas fa-download"></i> Export Excel
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card shadow h-100">
            <div class="card-body text-center">
                <h5 class="card-title"><i class="fas fa-dollar-sign text-info"></i> Sales</h5>
                <h3 class="mb-3"><?php echo Helper::formatCurrency($totalSales); ?></h3>
                <a href="?export=sales" class="btn btn-sm btn-info">
                    <i class="fas fa-download"></i> Export Excel
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Report Sections -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Inventory Report</h5>
            </div>
            <div class="card-body">
                <p><strong>Total Products:</strong> <?php echo $totalProducts; ?></p>
                <p><strong>Low Stock Items:</strong> <span class="badge bg-danger"><?php echo $lowStockCount; ?></span></p>
                <p><strong>Total Inventory Value:</strong> <?php echo Helper::formatCurrency($productModel->getTotalInventoryValue()); ?></p>
                <hr>
                <a href="?export=products" class="btn btn-primary btn-sm">
                    <i class="fas fa-download"></i> Export Products
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Financial Report</h5>
            </div>
            <div class="card-body">
                <p><strong>Total Sales:</strong> <?php echo Helper::formatCurrency($totalSales); ?></p>
                <p><strong>Total Orders:</strong> <?php echo $totalOrders; ?></p>
                <p><strong>Suppliers:</strong> <?php echo $totalSuppliers; ?></p>
                <hr>
                <a href="?export=sales" class="btn btn-success btn-sm">
                    <i class="fas fa-download"></i> Export Sales Report
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Reports -->
<div class="card shadow">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="fas fa-filter"></i> Advanced Filters & Reports</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h6>Date Range Report</h6>
                <form action="" method="GET">
                    <div class="mb-2">
                        <label class="form-label">Start Date:</label>
                        <input type="date" class="form-control form-control-sm" name="start_date">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">End Date:</label>
                        <input type="date" class="form-control form-control-sm" name="end_date">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100">Generate Report</button>
                </form>
            </div>

            <div class="col-md-4">
                <h6>Stock Report</h6>
                <div class="list-group list-group-sm">
                    <a href="<?php echo APP_URL; ?>/public/products.php?filter=low_stock" class="list-group-item list-group-item-action">
                        <i class="fas fa-exclamation-triangle"></i> Low Stock Items
                    </a>
                    <a href="<?php echo APP_URL; ?>/public/products.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-box"></i> All Products
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <h6>Transaction History</h6>
                <div class="list-group list-group-sm">
                    <a href="<?php echo APP_URL; ?>/public/purchase_orders.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-receipt"></i> Purchase Orders
                    </a>
                    <a href="<?php echo APP_URL; ?>/public/sales.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-cart"></i> Sales Transactions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>
