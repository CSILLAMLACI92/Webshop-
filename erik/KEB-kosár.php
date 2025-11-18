<?php
session_start();

// Inicializáljuk a kosarat, ha még nincs
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Termék hozzáadása
if (isset($_POST['add_to_cart'])) {
    $product = htmlspecialchars($_POST['product']);
    $price = (int)$_POST['price'];

    $_SESSION['cart'][] = [
        'product' => $product,
        'price' => $price
    ];
}

// Termék eltávolítása
if (isset($_GET['remove'])) {
    $index = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // újraindexelés
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Kosár</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 40px;
            max-width: 600px;
            margin: auto;
        }

        h2 {
            color: #333;
            border-bottom: 2px solid #ccc;
            padding-bottom: 5px;
        }

        form {
            margin-bottom: 15px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            background: #fff;
            margin-bottom: 10px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        a.remove {
            color: #dc3545;
            text-decoration: none;
            font-weight: bold;
            margin-left: 15px;
        }

        a.remove:hover {
            text-decoration: underline;
        }

        p.total {
            font-weight: bold;
            font-size: 1.2em;
            text-align: right;
        }
    </style>
</head>
<body>
    <h2>Termékek</h2>

    <form method="post">
        <input type="hidden" name="product" value="Gitár">
        <input type="hidden" name="price" value="45000">
        <button type="submit" name="add_to_cart">Gitár hozzáadása (45.000 Ft)</button>
    </form>

    <form method="post">
        <input type="hidden" name="product" value="Erősítő">
        <input type="hidden" name="price" value="60000">
        <button type="submit" name="add_to_cart">Erősítő hozzáadása (60.000 Ft)</button>
    </form>

    <h2>Kosár tartalma</h2>

    <?php if (!empty($_SESSION['cart'])): ?>
        <ul>
            <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                <li>
                    <?= htmlspecialchars($item['product']) ?> - <?= number_format($item['price'], 0, ',', ' ') ?> Ft
                    <a class="remove" href="?remove=<?= $index ?>">Eltávolítás</a>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="total">
            Összesen: 
            <?php
            $total = array_sum(array_column($_SESSION['cart'], 'price'));
            echo number_format($total, 0, ',', ' ') . ' Ft';
            ?>
        </p>
    <?php else: ?>
        <p>A kosár üres.</p>
    <?php endif; ?>
</body>
</html>
