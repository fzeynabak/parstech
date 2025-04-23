<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__DIR__)));
}

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/functions.php';

// Check user permissions
if (!hasPermission('view_products')) {
    die('دسترسی غیر مجاز');
}

$database = new Database();
$db = $database->getConnection();

$products = [];
$stmt = $db->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>مدیریت کالاها و خدمات</h2>
        <a href="index.php?page=products/new" class="btn btn-primary">
            <i class="fas fa-plus"></i> افزودن کالای جدید
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>کد کالا</th>
                            <th>نام کالا</th>
                            <th>دسته‌بندی</th>
                            <th>قیمت</th>
                            <th>موجودی</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['code']); ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td><?php echo number_format($product['price']); ?> ریال</td>
                            <td><?php echo number_format($product['stock']); ?></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info edit-product" 
                                            data-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-product" 
                                            data-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Delete product
    $('.delete-product').click(function() {
        const productId = $(this).data('id');
        
        Swal.fire({
            title: 'آیا مطمئن هستید?',
            text: "این عملیات قابل بازگشت نیست!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'بله، حذف شود',
            cancelButtonText: 'انصراف'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/ajax/delete_product.php',
                    type: 'POST',
                    data: { id: productId },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'حذف شد!',
                                'کالای مورد نظر با موفقیت حذف شد.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'خطا!',
                                'مشکلی در حذف کالا پیش آمد.',
                                'error'
                            );
                        }
                    }
                });
            }
        });
    });

    // Edit product (redirect to edit page)
    $('.edit-product').click(function() {
        const productId = $(this).data('id');
        window.location.href = `index.php?page=products/edit&id=${productId}`;
    });
});
</script>