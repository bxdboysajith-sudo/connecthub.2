<?php

require_once "config.php";

login_required();

$uid = (int)$_SESSION["user_id"];

$message = "";
$error = "";


// =====================================================
// GET USER CART
// =====================================================

$stmt = $conn->prepare("
    SELECT id
    FROM cart
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $uid);
$stmt->execute();

$cart = $stmt->get_result()->fetch_assoc();

$stmt->close();


if (!$cart) {
    $error = "Your cart is empty.";
} else {

    $cart_id = (int)$cart["id"];


    // =================================================
    // CHECK CART ITEMS
    // =================================================

    $stmt = $conn->prepare("
        SELECT
            ci.quantity,
            p.name,
            p.price,
            p.stock
        FROM cart_items ci

        INNER JOIN products p
            ON p.id = ci.product_id

        WHERE ci.cart_id = ?
    ");

    $stmt->bind_param(
        "i",
        $cart_id
    );

    $stmt->execute();

    $items = $stmt->get_result();


    if ($items->num_rows === 0) {

        $error = "Your cart is empty.";

    } else {

        $total = 0;

        while ($item = $items->fetch_assoc()) {

            $quantity = (int)$item["quantity"];
            $stock = (int)$item["stock"];
            $price = (float)$item["price"];


            if ($quantity <= 0) {

                $error =
                    "Invalid quantity for " .
                    $item["name"];

                break;
            }


            if ($stock < $quantity) {

                $error =
                    $item["name"] .
                    " does not have enough stock. " .
                    "Available: " .
                    $stock;

                break;
            }


            $total +=
                $price * $quantity;
        }
    }

    $stmt->close();


    // =================================================
    // SEND USER TO BANKING PAYMENT
    // =================================================

    if ($error === "") {

        header(
            "Location: bank_payment.php"
        );

        exit;
    }
}

?>


<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Checkout - ConnectHub
</title>


<style>

* {
    box-sizing: border-box;
}


body {

    font-family:
        Arial,
        sans-serif;

    background:
        #f5f7fb;

    margin: 0;

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

}


.checkout-box {

    width: 90%;

    max-width: 600px;

    background: white;

    padding: 45px;

    border-radius: 20px;

    box-shadow:
        0 10px 35px
        rgba(0,0,0,0.12);

    text-align: center;

}


.icon {

    font-size: 60px;

    margin-bottom: 15px;

}


h1 {

    margin-bottom: 15px;

    color: #111827;

}


.error {

    color: #b91c1c;

    background: #fee2e2;

    padding: 15px;

    border-radius: 10px;

    font-size: 18px;

    font-weight: bold;

}


.button {

    display: inline-block;

    margin-top: 25px;

    padding: 13px 28px;

    background: #4f46e5;

    color: white;

    text-decoration: none;

    border-radius: 9px;

    font-weight: bold;

}


.button:hover {

    background: #3730a3;

}

</style>

</head>


<body>


<div class="checkout-box">


<?php if ($error !== ""): ?>

    <div class="icon">
        ❌
    </div>


    <h1>
        Checkout Failed
    </h1>


    <div class="error">

        <?= e($error) ?>

    </div>


    <a
        href="cart.php"
        class="button"
    >
        🛒 Back to Cart
    </a>

<?php endif; ?>


</div>


</body>

</html>