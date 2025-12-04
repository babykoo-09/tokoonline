
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include '../koneksi.php';

if (!isset($_GET['id'])) {
    die("ID Pesanan tidak ditemukan.");
}

$order_id = $_GET['id'];

// Ambil data pesanan
$order_query = "SELECT * FROM orders WHERE order_id = ?";
$stmt = $koneksi->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

    // Ambil item pesanan
$items_query = "SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
$stmt = $koneksi->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$order_items = [];
while($item = $items_result->fetch_assoc()) {
    $topping_names = [];
    if (!empty($item['topping_id'])) {
        $topping_ids = explode(',', $item['topping_id']);
        $topping_placeholders = implode(',', array_fill(0, count($topping_ids), '?'));
        $topping_types = str_repeat('i', count($topping_ids));
        $topping_query = "SELECT name FROM toppings WHERE topping_id IN ($topping_placeholders)";
        $topping_stmt = $koneksi->prepare($topping_query);
        $topping_stmt->bind_param($topping_types, ...$topping_ids);
        $topping_stmt->execute();
        $topping_result = $topping_stmt->get_result();
        while($topping_row = $topping_result->fetch_assoc()) {
            $topping_names[] = $topping_row['name'];
        }
        $topping_stmt->close();
    }
    $item['topping_names'] = $topping_names;
    $order_items[] = $item;
}
$stmt->close();
$koneksi->close();?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Pesanan #<?php echo $order_id; ?></title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="receipt-body">
    <div class="receipt-header">
        <h1>The Premium Bubble</h1>
        <p>Struk Pesanan</p>
    </div>

    <div class="order-info">
        <p>No. Pesanan: #<?php echo htmlspecialchars($order['order_id']); ?></p>
        <p>Tanggal: <?php echo date("d/m/Y H:i", strtotime($order['order_date'])); ?></p>
        <p>Pelanggan: <?php echo htmlspecialchars($order['customer_name']); ?></p>
    </div>

    <div class="receipt-content">
        <hr>
        <table>
            <?php 
            $subtotal = 0;
            foreach ($order_items as $item): 
                $item_price = $item['item_price']; // Assuming item_price is stored now
                $subtotal += $item_price;
            ?>
            <tr>
                <td class="item-col">
                    <?php echo htmlspecialchars($item['product_name']); ?> (x<?php echo $item['quantity']; ?>)
                    <br>
                    <small>
                        Gula: <?php echo $item['sugar_level']; ?>%, 
                        Es: <?php echo $item['ice_level']; ?>,
                        Suhu: <?php echo $item['suhu']; ?>
                        <?php if(!empty($item['topping_names'])): ?>
                            <br>+ <?php echo htmlspecialchars(implode(', ', $item['topping_names'])); ?>
                        <?php endif; ?>
                    </small>
                </td>
                <td class="price-col">Rp <?php echo number_format($item_price, 0, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <hr>
    </div>

    <div class="receipt-total-section">
        <table>
            <tr>
                <td>Subtotal</td>
                <td class="price-col">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
            </tr>
            <tr>
                <td><strong>Total</strong></td>
                <td class="price-col"><strong>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></strong></td>
            </tr>
        </table>
    </div>

    <div class="receipt-footer">
        <p>Terima kasih telah memesan!</p>
    </div>

    <div class="receipt-no-print">
        <button onclick="window.print()">Cetak Struk</button>
    </div>

</body>
</html>
