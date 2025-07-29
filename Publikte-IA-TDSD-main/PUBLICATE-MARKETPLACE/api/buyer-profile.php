

```php file="api/buyer-orders.php" type="code" project="api/buyer-orders"
[v0-no-op-code-block-prefix]<?php
// api/buyer-orders.php

// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database and object files
include_once '../config/database.php';
include_once '../objects/order.php';
include_once '../objects/order_item.php';
include_once '../objects/product.php';

// Instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$order = new Order($db);
$order_item = new OrderItem($db);
$product = new Product($db);

// Get buyer ID (assuming it's passed in the request)
$buyer_id = isset($_GET['buyer_id']) ? $_GET['buyer_id'] : die();

// Get order status (optional filter)
$status = isset($_GET['status']) ? $_GET['status'] : null;

// Query orders
$stmt = $order->readByBuyer($buyer_id, $status);
$num = $stmt->rowCount();

// Check if more than 0 record found
if ($num > 0) {

    // Orders array
    $orders_arr = array();
    $orders_arr["records"] = array();

    // Retrieve our table contents
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $order_item_arr = array(
            "id" => $id,
            "buyer_id" => $buyer_id,
            "order_date" => $order_date,
            "total_amount" => $total_amount,
            "status" => $status
        );

        // Get order items for the current order
        $order_item_stmt = $order_item->readByOrderId($id);
        $order_item_num = $order_item_stmt->rowCount();

        $order_item_arr["order_items"] = array();

        if ($order_item_num > 0) {
            while ($order_item_row = $order_item_stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($order_item_row);

                // Get product details
                $product->id = $product_id;
                $product->readOne();

                $product_item = array(
                    "product_id" => $product_id,
                    "product_name" => $product->name,
                    "quantity" => $quantity,
                    "price" => $price
                );

                array_push($order_item_arr["order_items"], $product_item);
            }
        }

        array_push($orders_arr["records"], $order_item_arr);
    }

    // Set response code - 200 OK
    http_response_code(200);

    // Show orders data in json format
    echo json_encode($orders_arr);
} else {

    // Set response code - 404 Not found
    http_response_code(404);

    // Tell the user no orders found
    echo json_encode(
        array("message" => "No orders found.")
    );
}
?>