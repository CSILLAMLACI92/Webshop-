<?php

function ensure_orders_schema($conn, &$error) {
    $error = null;
    $ordersSql = "
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            total INT NOT NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'created',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX (user_id),
            CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $itemsSql = "
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            unit_price INT NOT NULL,
            quantity INT NOT NULL,
            INDEX (order_id),
            CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    if (!$conn->query($ordersSql)) {
        $error = "Orders table create failed";
        return false;
    }
    if (!$conn->query($itemsSql)) {
        $error = "Order items table create failed";
        return false;
    }
    return true;
}

