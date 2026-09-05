<?php
// =====================================================
// CONNECTHUB - BANK PAYMENT
// =====================================================

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once "config.php";

login_required();

$uid = (int)($_SESSION["user_id"] ?? 0);

if ($uid <= 0) {
    header("Location: login.php");
    exit;
}


// =====================================================
// VARIABLES
// =====================================================

$message = "";
$error = "";

$total = 0;

$account = null;

$cartProducts = [];


// =====================================================
// GET USER CART
// =====================================================

$stmt = $conn->prepare("
    SELECT id
    FROM cart
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $uid
);

$stmt->execute();

$cart = $stmt
    ->get_result()
    ->fetch_assoc();

$stmt->close();


if (!$cart) {

    $error = "Your cart is empty.";

} else {

    $cart_id = (int)$cart["id"];


    // =================================================
    // GET CART PRODUCTS
    // =================================================

    $stmt = $conn->prepare("
        SELECT
            ci.id,
            ci.product_id,
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


    while ($item = $items->fetch_assoc()) {

        $productId = (int)$item["product_id"];

        $quantity = (int)$item["quantity"];

        $price = (float)$item["price"];

        $stock = (int)$item["stock"];


        // ---------------------------------------------
        // CHECK QUANTITY
        // ---------------------------------------------

        if ($quantity <= 0) {

            $error =
                "Invalid quantity for " .
                $item["name"];

            break;
        }


        // ---------------------------------------------
        // CHECK STOCK
        // ---------------------------------------------

        if ($stock < $quantity) {

            $error =
                $item["name"] .
                " does not have enough stock. " .
                "Available: " .
                $stock .
                ", requested: " .
                $quantity;

            break;
        }


        // ---------------------------------------------
        // CALCULATE TOTAL
        // ---------------------------------------------

        $subtotal =
            $price * $quantity;

        $total += $subtotal;


        $cartProducts[] = $item;
    }

    $stmt->close();


    if (
        count($cartProducts) === 0 &&
        $error === ""
    ) {

        $error = "Your cart is empty.";
    }
}


// =====================================================
// GET BANK ACCOUNT
// =====================================================

if ($error === "") {

    $stmt = $conn->prepare("
        SELECT
            id,
            user_id,
            account_number,
            balance,
            pin_hash
        FROM bank_accounts
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->bind_param(
        "i",
        $uid
    );

    $stmt->execute();

    $account =
        $stmt
        ->get_result()
        ->fetch_assoc();

    $stmt->close();


    if (!$account) {

        $error =
            "Banking account not found.";
    }
}


// =====================================================
// PAYMENT
// =====================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["pay_now"]) &&
    $error === ""
) {

    $transactionPin =
        trim(
            $_POST["transaction_pin"] ?? ""
        );


    // =================================================
    // CHECK PIN FORMAT
    // =================================================

    if (
        !preg_match(
            "/^\d{4}$/",
            $transactionPin
        )
    ) {

        $error =
            "Please enter your 4-digit Banking PIN.";

    }


    // =================================================
    // CHECK BANKING PIN
    // =================================================

    elseif (
        empty($account["pin_hash"])
    ) {

        $error =
            "Banking PIN is not set. Please create your PIN in Banking.";

    }

    elseif (
        !password_verify(
            $transactionPin,
            $account["pin_hash"]
        )
    ) {

        $error =
            "❌ Incorrect Banking PIN. Payment cancelled.";

    }


    // =================================================
    // CHECK BALANCE
    // =================================================

    elseif (
        (float)$account["balance"] < $total
    ) {

        $error =
            "❌ Insufficient bank balance.";
    }


    // =================================================
    // PROCESS PAYMENT
    // =================================================

    else {

        $conn->begin_transaction();


        try {

            // =========================================
            // GET CART AGAIN WITH LOCK
            // =========================================

            $stmt = $conn->prepare("
                SELECT id
                FROM cart
                WHERE user_id = ?
                LIMIT 1
                FOR UPDATE
            ");

            $stmt->bind_param(
                "i",
                $uid
            );

            $stmt->execute();

            $lockedCart =
                $stmt
                ->get_result()
                ->fetch_assoc();

            $stmt->close();


            if (!$lockedCart) {

                throw new Exception(
                    "Cart not found."
                );
            }


            $cart_id =
                (int)$lockedCart["id"];


            // =========================================
            // GET CART ITEMS + PRODUCT STOCK
            // =========================================

            $stmt = $conn->prepare("
                SELECT
                    ci.product_id,
                    ci.quantity,
                    p.name,
                    p.price,
                    p.stock
                FROM cart_items ci

                INNER JOIN products p
                    ON p.id = ci.product_id

                WHERE ci.cart_id = ?

                FOR UPDATE
            ");

            $stmt->bind_param(
                "i",
                $cart_id
            );

            $stmt->execute();

            $lockedItems =
                $stmt->get_result();


            $paymentProducts = [];

            $finalTotal = 0;


            while (
                $item =
                $lockedItems->fetch_assoc()
            ) {

                $productId =
                    (int)$item["product_id"];

                $quantity =
                    (int)$item["quantity"];

                $price =
                    (float)$item["price"];

                $stock =
                    (int)$item["stock"];


                if ($quantity <= 0) {

                    throw new Exception(
                        "Invalid quantity for " .
                        $item["name"]
                    );
                }


                if ($stock < $quantity) {

                    throw new Exception(
                        $item["name"] .
                        " does not have enough stock."
                    );
                }


                $finalTotal +=
                    $price * $quantity;


                $paymentProducts[] =
                    $item;
            }


            $stmt->close();


            if (
                count($paymentProducts) === 0
            ) {

                throw new Exception(
                    "Your cart is empty."
                );
            }


            // =========================================
            // GET AND LOCK BANK ACCOUNT
            // =========================================

            $accountId =
                (int)$account["id"];


            $stmt = $conn->prepare("
                SELECT
                    id,
                    balance,
                    pin_hash
                FROM bank_accounts
                WHERE id = ?
                AND user_id = ?
                FOR UPDATE
            ");

            $stmt->bind_param(
                "ii",
                $accountId,
                $uid
            );

            $stmt->execute();

            $lockedAccount =
                $stmt
                ->get_result()
                ->fetch_assoc();

            $stmt->close();


            if (!$lockedAccount) {

                throw new Exception(
                    "Bank account not found."
                );
            }


            // =========================================
            // VERIFY PIN AGAIN
            // =========================================

            if (
                empty(
                    $lockedAccount["pin_hash"]
                )
            ) {

                throw new Exception(
                    "Banking PIN is not set."
                );
            }


            if (
                !password_verify(
                    $transactionPin,
                    $lockedAccount["pin_hash"]
                )
            ) {

                throw new Exception(
                    "Incorrect Banking PIN."
                );
            }


            // =========================================
            // CHECK FINAL BALANCE
            // =========================================

            $currentBalance =
                (float)$lockedAccount["balance"];


            if (
                $currentBalance < $finalTotal
            ) {

                throw new Exception(
                    "Insufficient bank balance."
                );
            }


            // =========================================
            // CREATE ORDER
            //
            // IMPORTANT:
            // We only use user_id because your
            // orders table does NOT have total_amount.
            // =========================================

            $stmt = $conn->prepare("
                INSERT INTO orders
                (
                    user_id
                )
                VALUES (?)
            ");

            $stmt->bind_param(
                "i",
                $uid
            );

            $stmt->execute();

            $orderId =
                $stmt->insert_id;

            $stmt->close();


            if (!$orderId) {

                throw new Exception(
                    "Unable to create order."
                );
            }


            // =========================================
            // ORDER ITEMS + REDUCE STOCK
            // =========================================

            foreach (
                $paymentProducts
                as $item
            ) {

                $productId =
                    (int)$item["product_id"];

                $quantity =
                    (int)$item["quantity"];

                $price =
                    (float)$item["price"];


                // -------------------------------------
                // INSERT ORDER ITEM
                // -------------------------------------

                $stmt = $conn->prepare("
                    INSERT INTO order_items
                    (
                        order_id,
                        product_id,
                        quantity,
                        price
                    )
                    VALUES (?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "iiid",
                    $orderId,
                    $productId,
                    $quantity,
                    $price
                );

                $stmt->execute();

                $stmt->close();


                // -------------------------------------
                // DECREASE PRODUCT STOCK
                // -------------------------------------

                $stmt = $conn->prepare("
                    UPDATE products

                    SET stock =
                        stock - ?

                    WHERE id = ?

                    AND stock >= ?
                ");

                $stmt->bind_param(
                    "iii",
                    $quantity,
                    $productId,
                    $quantity
                );

                $stmt->execute();


                if (
                    $stmt->affected_rows !== 1
                ) {

                    $stmt->close();

                    throw new Exception(
                        "Unable to update stock for " .
                        $item["name"]
                    );
                }


                $stmt->close();
            }


            // =========================================
            // DEDUCT MONEY FROM BANK ACCOUNT
            // =========================================

            $stmt = $conn->prepare("
                UPDATE bank_accounts

                SET balance =
                    balance - ?

                WHERE id = ?

                AND user_id = ?

                AND balance >= ?
            ");

            $stmt->bind_param(
                "diid",
                $finalTotal,
                $accountId,
                $uid,
                $finalTotal
            );

            $stmt->execute();


            if (
                $stmt->affected_rows !== 1
            ) {

                $stmt->close();

                throw new Exception(
                    "Payment failed. Balance was not deducted."
                );
            }


            $stmt->close();


            // =========================================
            // CREATE BANK TRANSACTION
            // =========================================

            $type =
                "purchase";

            $reference =
                "ORDER_" .
                $orderId;


            $stmt = $conn->prepare("
                INSERT INTO transactions
                (
                    account_id,
                    type,
                    amount,
                    reference
                )
                VALUES (?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "isds",
                $accountId,
                $type,
                $finalTotal,
                $reference
            );

            $stmt->execute();

            $stmt->close();


            // =========================================
            // CLEAR CART
            // =========================================

            $stmt = $conn->prepare("
                DELETE FROM cart_items
                WHERE cart_id = ?
            ");

            $stmt->bind_param(
                "i",
                $cart_id
            );

            $stmt->execute();

            $stmt->close();


            // =========================================
            // COMMIT EVERYTHING
            // =========================================

            $conn->commit();


            // =========================================
            // SUCCESS
            // =========================================

            $message =
                "Payment successful! " .
                "Order #" .
                $orderId .
                " | Amount ₹" .
                number_format(
                    $finalTotal,
                    2
                );


            // Update displayed balance

            $account["balance"] =
                $currentBalance -
                $finalTotal;


            // Cart is now empty

            $total = 0;


            $cartProducts = [];


        } catch (Throwable $e) {

            // -----------------------------------------
            // ROLLBACK
            // -----------------------------------------

            $conn->rollback();


            $error =
                $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Banking Payment - ConnectHub
</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    min-height: 100vh;

    font-family:
        Arial,
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #eef2ff,
            #f8fafc
        );

    display: flex;

    justify-content: center;

    align-items: center;

    padding: 20px;
}


.payment-container {

    width: 100%;

    max-width: 520px;

}


.payment-card {

    background: white;

    border-radius: 24px;

    padding: 35px;

    box-shadow:
        0 15px 45px
        rgba(0,0,0,0.12);

    text-align: center;
}


.icon {

    font-size: 60px;

    margin-bottom: 10px;
}


h1 {

    margin: 5px 0 25px;

    color: #111827;
}


.success {

    background: #dcfce7;

    color: #166534;

    padding: 15px;

    border-radius: 12px;

    font-weight: bold;

    margin-bottom: 20px;
}


.error {

    background: #fee2e2;

    color: #b91c1c;

    padding: 15px;

    border-radius: 12px;

    font-weight: bold;

    margin-bottom: 20px;
}


.amount-box {

    background:
        linear-gradient(
            135deg,
            #111827,
            #312e81
        );

    color: white;

    border-radius: 18px;

    padding: 25px;

    margin-bottom: 20px;
}


.amount-label {

    font-size: 14px;

    opacity: .8;

    margin-bottom: 8px;
}


.amount {

    font-size: 36px;

    font-weight: bold;
}


.balance {

    background: #f8fafc;

    border-radius: 12px;

    padding: 15px;

    margin-bottom: 20px;

    color: #475467;
}


.balance strong {

    color: #111827;
}


.pin-input {

    width: 100%;

    padding: 16px;

    border: 2px solid #d0d5dd;

    border-radius: 12px;

    font-size: 20px;

    text-align: center;

    letter-spacing: 8px;

    outline: none;

    margin-bottom: 15px;
}


.pin-input:focus {

    border-color: #4f46e5;

    box-shadow:
        0 0 0 3px
        rgba(79,70,229,.12);
}


.pay-button {

    width: 100%;

    padding: 15px;

    border: none;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            #4f46e5,
            #7c3aed
        );

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    transition: .2s;
}


.pay-button:hover {

    transform: translateY(-1px);

    box-shadow:
        0 8px 20px
        rgba(79,70,229,.25);
}


.cancel {

    display: inline-block;

    margin-top: 20px;

    color: #667085;

    text-decoration: none;
}


.cancel:hover {

    color: #111827;
}


.success-button {

    display: inline-block;

    margin-top: 15px;

    padding: 13px 22px;

    background: #4f46e5;

    color: white;

    text-decoration: none;

    border-radius: 10px;

    font-weight: bold;
}


.shop-button {

    display: inline-block;

    margin-top: 15px;

    padding: 13px 22px;

    background: #10b981;

    color: white;

    text-decoration: none;

    border-radius: 10px;

    font-weight: bold;
}


@media (max-width: 600px) {

    .payment-card {

        padding: 25px 18px;
    }

    .amount {

        font-size: 30px;
    }

}

</style>

</head>


<body>


<div class="payment-container">

<div class="payment-card">


<?php if ($message !== ""): ?>

    <!-- ==========================================
         PAYMENT SUCCESS
    =========================================== -->

    <div class="icon">
        ✅
    </div>


    <h1>
        Payment Successful
    </h1>


    <div class="success">

        <?= e($message) ?>

    </div>


    <p>
        Your payment was completed successfully.
    </p>


    <a
        href="shop.php"
        class="shop-button"
    >
        🛍 Continue Shopping
    </a>


<?php else: ?>


    <!-- ==========================================
         PAYMENT ERROR
    =========================================== -->

    <?php if ($error !== ""): ?>

        <div class="icon">
            ❌
        </div>


        <h1>
            Payment Failed
        </h1>


        <div class="error">

            <?= e($error) ?>

        </div>


        <a
            href="cart.php"
            class="success-button"
        >
            🛒 Back to Cart
        </a>


    <?php else: ?>


        <!-- ======================================
             BANKING PAYMENT
        ======================================= -->

        <div class="icon">
            💳
        </div>


        <h1>
            Banking Payment
        </h1>


        <?php if (
            !empty($account["pin_hash"])
        ): ?>


            <!-- ==================================
                 PRODUCT AMOUNT
            =================================== -->

            <div class="amount-box">

                <div class="amount-label">
                    Product Amount
                </div>


                <div class="amount">

                    ₹<?= number_format(
                        $total,
                        2
                    ) ?>

                </div>

            </div>


            <!-- ==================================
                 AVAILABLE BALANCE
            =================================== -->

            <div class="balance">

                Available Balance:

                <strong>

                    ₹<?= number_format(
                        $account["balance"],
                        2
                    ) ?>

                </strong>

            </div>


            <!-- ==================================
                 PIN FORM
            =================================== -->

            <form
                method="POST"
                autocomplete="off"
            >

                <input
                    class="pin-input"
                    type="password"
                    name="transaction_pin"
                    maxlength="4"
                    minlength="4"
                    inputmode="numeric"
                    pattern="[0-9]{4}"
                    placeholder="Enter 4-digit Banking PIN"
                    autocomplete="new-password"
                    required
                    autofocus
                >


                <button
                    type="submit"
                    name="pay_now"
                    class="pay-button"
                >

                    🔐 Pay ₹<?= number_format(
                        $total,
                        2
                    ) ?>

                </button>

            </form>


            <a
                href="cart.php"
                class="cancel"
            >
                ← Back to Cart
            </a>


        <?php else: ?>


            <!-- ==================================
                 PIN NOT SET
            =================================== -->

            <div class="icon">
                🔐
            </div>


            <h1>
                Banking PIN Not Set
            </h1>


            <p>
                You need to create your Banking PIN
                before making a purchase.
            </p>


            <a
                href="bank.php"
                class="success-button"
            >
                🔐 Create Banking PIN
            </a>


            <br>


            <a
                href="cart.php"
                class="cancel"
            >
                ← Back to Cart
            </a>


        <?php endif; ?>


    <?php endif; ?>


<?php endif; ?>


</div>

</div>


</body>

</html>