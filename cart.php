```php
<?php
// ============================================================
// CONNECTHUB - ADVANCED SHOPPING CART
// REAL PRODUCT IMAGE DETECTION
// PROFESSIONAL CART UI
// ============================================================

require "config.php";

login_required();

$uid = (int)($_SESSION["user_id"] ?? 0);

if ($uid <= 0) {
    header("Location: login.php");
    exit;
}


// ============================================================
// REMOVE PRODUCT
// ============================================================

if (isset($_GET["remove"])) {

    $removeId = (int)($_GET["remove"] ?? 0);

    if ($removeId > 0) {

        $stmt = $conn->prepare("
            DELETE ci
            FROM cart_items ci
            INNER JOIN cart c
                ON c.id = ci.cart_id
            WHERE ci.id = ?
            AND c.user_id = ?
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $removeId,
                $uid
            );

            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: cart.php");
    exit;
}


// ============================================================
// GET CART
// ============================================================

$cart = null;

$stmt = $conn->prepare("
    SELECT id
    FROM cart
    WHERE user_id = ?
    LIMIT 1
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $uid
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $cart = $result
        ? $result->fetch_assoc()
        : null;

    $stmt->close();
}


// ============================================================
// GET CART ITEMS
// ============================================================

$items = null;

if ($cart) {

    $cartId = (int)$cart["id"];

    $stmt = $conn->prepare("
        SELECT
            ci.id,
            ci.quantity,
            p.id AS product_id,
            p.name,
            p.price,
            p.image,
            p.stock
        FROM cart_items ci
        INNER JOIN products p
            ON p.id = ci.product_id
        WHERE ci.cart_id = ?
        ORDER BY ci.id DESC
    ");

    if ($stmt) {

        $stmt->bind_param(
            "i",
            $cartId
        );

        $stmt->execute();

        $items = $stmt->get_result();

        $stmt->close();
    }
}


// ============================================================
// PRODUCT IMAGE FINDER
// ============================================================

function findProductImage($imageValue)
{
    return product_image_url($imageValue);
}


// ============================================================
// HEADER
// ============================================================

require "header.php";

?>

<div class="advanced-cart-page">


    <!-- =====================================================
         HERO
    ====================================================== -->

    <section class="cart-hero">

        <div class="hero-decoration hero-decoration-one"></div>
        <div class="hero-decoration hero-decoration-two"></div>

        <div class="hero-content">

            <div class="hero-icon">
                🛒
            </div>

            <div>

                <div class="hero-kicker">
                    CONNECTHUB SHOP
                </div>

                <h1>
                    My Shopping Cart
                </h1>

                <p>
                    Review your products and complete
                    your secure purchase.
                </p>

            </div>

        </div>

        <a
            href="shop.php"
            class="hero-button"
        >
            🛍 Continue Shopping
        </a>

    </section>


    <?php if (
        !$items ||
        $items->num_rows === 0
    ): ?>


        <!-- =================================================
             EMPTY CART
        ================================================== -->

        <section class="empty-cart">

            <div class="empty-art">

                <div class="empty-cart-main">
                    🛒
                </div>

                <span class="float-icon one">
                    🛍️
                </span>

                <span class="float-icon two">
                    ✨
                </span>

                <span class="float-icon three">
                    💙
                </span>

            </div>

            <div class="empty-kicker">
                CONNECTHUB SHOP
            </div>

            <h2>
                Your Cart is Empty
            </h2>

            <p>
                Discover products from the ConnectHub Shop
                and add your favorites here.
            </p>

            <a
                href="shop.php"
                class="start-shopping"
            >
                🛍️ Start Shopping
            </a>

        </section>


    <?php else: ?>


        <!-- =================================================
             CART HEADING
        ================================================== -->

        <div class="cart-heading">

            <div>

                <div class="section-kicker">
                    CART OVERVIEW
                </div>

                <h2>
                    Your Selected Products
                </h2>

                <p>
                    All product information and photos are shown below.
                </p>

            </div>

            <a
                href="shop.php"
                class="add-more-button"
            >
                + Add More Products
            </a>

        </div>


        <!-- =================================================
             CART LAYOUT
        ================================================== -->

        <div class="cart-layout">


            <!-- =================================================
                 PRODUCTS
            ================================================== -->

            <div class="cart-products">


                <?php

                $total = 0;
                $totalQuantity = 0;

                while (
                    $item =
                    $items->fetch_assoc()
                ):

                    $quantity =
                        max(
                            1,
                            (int)$item["quantity"]
                        );

                    $price =
                        (float)$item["price"];

                    $stock =
                        (int)$item["stock"];

                    $subtotal =
                        $price *
                        $quantity;

                    $total +=
                        $subtotal;

                    $totalQuantity +=
                        $quantity;


                    // Find real image
                    $realImage =
                        findProductImage(
                            $item["image"] ?? ""
                        );

                ?>


                    <!-- =========================================
                         PRODUCT CARD
                    ========================================== -->

                    <article class="cart-product">


                        <!-- PRODUCT PHOTO AREA -->

                        <div class="product-photo-box">


                            <?php if (
                                $realImage !== ""
                            ): ?>


                                <img
                                    src="<?= e($realImage) ?>"
                                    alt="<?= e($item["name"]) ?>"
                                    class="real-product-image"
                                >


                            <?php else: ?>


                                <div class="image-placeholder">

                                    <div>
                                        🛍️
                                    </div>

                                    <span>
                                        Product Image
                                    </span>

                                </div>


                            <?php endif; ?>


                            <div class="photo-tag">
                                CONNECTHUB
                            </div>


                            <div class="zoom-hint">
                                <?= $realImage !== ""
                                    ? "✓ IMAGE LOADED"
                                    : "IMAGE NOT FOUND"
                                ?>
                            </div>


                        </div>


                        <!-- PRODUCT DETAILS -->

                        <div class="product-details">


                            <div class="product-label">
                                CONNECTHUB PRODUCT
                            </div>


                            <div class="product-title-row">

                                <h3>
                                    <?= e(
                                        $item["name"]
                                    ) ?>
                                </h3>


                                <div class="unit-price">

                                    ₹<?= number_format(
                                        $price,
                                        2
                                    ) ?>

                                </div>

                            </div>


                            <div class="detail-grid">


                                <div class="detail-box">

                                    <span>
                                        QUANTITY
                                    </span>

                                    <strong>
                                        <?= $quantity ?>
                                    </strong>

                                </div>


                                <div class="detail-box">

                                    <span>
                                        STOCK
                                    </span>

                                    <strong>
                                        <?= $stock ?>
                                    </strong>

                                </div>


                                <div class="detail-box">

                                    <span>
                                        STATUS
                                    </span>

                                    <strong class="stock-ok">
                                        ● IN STOCK
                                    </strong>

                                </div>


                            </div>


                            <div class="subtotal-row">


                                <div>

                                    <span>
                                        ITEM SUBTOTAL
                                    </span>

                                    <strong>
                                        ₹<?= number_format(
                                            $subtotal,
                                            2
                                        ) ?>
                                    </strong>

                                </div>


                                <a
                                    href="cart.php?remove=<?= (int)$item["id"] ?>"
                                    class="remove-button"
                                    onclick="
                                        return confirm(
                                            'Remove this product from your cart?'
                                        );
                                    "
                                >
                                    🗑 Remove
                                </a>


                            </div>


                        </div>


                    </article>


                <?php endwhile; ?>


            </div>


            <!-- =================================================
                 ORDER SUMMARY
            ================================================== -->

            <aside class="order-summary">


                <div class="summary-header">

                    <div class="summary-icon">
                        💳
                    </div>

                    <div>

                        <div class="summary-kicker">
                            SECURE CHECKOUT
                        </div>

                        <h2>
                            Order Summary
                        </h2>

                    </div>

                </div>


                <div class="summary-line">

                    <span>
                        Total Products
                    </span>

                    <strong>
                        <?= $items->num_rows ?>
                    </strong>

                </div>


                <div class="summary-line">

                    <span>
                        Total Quantity
                    </span>

                    <strong>
                        <?= $totalQuantity ?>
                    </strong>

                </div>


                <div class="summary-line">

                    <span>
                        Delivery
                    </span>

                    <strong class="free">
                        FREE
                    </strong>

                </div>


                <div class="summary-divider"></div>


                <div class="total-label">
                    TOTAL AMOUNT
                </div>


                <div class="grand-total">
                    ₹<?= number_format(
                        $total,
                        2
                    ) ?>
                </div>


                <a
                    href="bank_payment.php"
                    class="pay-button"
                >
                    🔐 Pay with Banking
                </a>


                <div class="secure-payment-box">

                    <div class="secure-icon">
                        🛡️
                    </div>

                    <div>

                        <strong>
                            Secure Payment
                        </strong>

                        <p>
                            Your Banking PIN will be required
                            before the purchase is completed.
                        </p>

                    </div>

                </div>


                <div class="payment-features">

                    <div>
                        ✓ Secure Banking
                    </div>

                    <div>
                        ✓ PIN Protected
                    </div>

                    <div>
                        ✓ ConnectHub Checkout
                    </div>

                </div>


                <a
                    href="shop.php"
                    class="back-shop"
                >
                    ← Continue Shopping
                </a>


            </aside>


        </div>


    <?php endif; ?>

</div>


<style>

/* ============================================================
   PAGE
============================================================ */

.advanced-cart-page {

    width:
        100%;

    max-width:
        1180px;

    margin:
        0 auto;

    padding:
        24px 22px 80px;

}


/* ============================================================
   HERO
============================================================ */

.cart-hero {

    position:
        relative;

    overflow:
        hidden;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        20px;

    min-height:
        175px;

    padding:
        28px 30px;

    margin-bottom:
        25px;

    border-radius:
        25px;

    color:
        white;

    background:
        linear-gradient(
            135deg,
            #0f172a,
            #312e81,
            #4f46e5
        );

    box-shadow:
        0 22px 60px
        rgba(15,23,42,.24);

}


.hero-decoration {

    position:
        absolute;

    border-radius:
        50%;

    pointer-events:
        none;

}


.hero-decoration-one {

    width:
        330px;

    height:
        330px;

    right:
        -130px;

    top:
        -165px;

    background:
        rgba(129,140,248,.16);

}


.hero-decoration-two {

    width:
        220px;

    height:
        220px;

    left:
        48%;

    bottom:
        -170px;

    background:
        rgba(192,132,252,.10);

}


.hero-content {

    position:
        relative;

    z-index:
        5;

    display:
        flex;

    align-items:
        center;

    gap:
        16px;

}


.hero-icon {

    width:
        70px;

    height:
        70px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    flex:
        0 0 70px;

    border-radius:
        21px;

    border:
        1px solid
        rgba(255,255,255,.16);

    background:
        rgba(255,255,255,.10);

    font-size:
        35px;

}


.hero-kicker {

    color:
        #c7d2fe;

    font-size:
        8px;

    font-weight:
        900;

    letter-spacing:
        2px;

}


.cart-hero h1 {

    margin:
        6px 0 5px;

    font-size:
        34px;

}


.cart-hero p {

    margin:
        0;

    color:
        rgba(255,255,255,.67);

    font-size:
        11px;

}


.hero-button {

    position:
        relative;

    z-index:
        6;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        12px 15px;

    border-radius:
        11px;

    color:
        white;

    background:
        rgba(255,255,255,.10);

    border:
        1px solid
        rgba(255,255,255,.16);

    text-decoration:
        none;

    font-size:
        9px;

    font-weight:
        900;

    white-space:
        nowrap;

}


/* ============================================================
   CART HEADING
============================================================ */

.cart-heading {

    display:
        flex;

    align-items:
        flex-end;

    justify-content:
        space-between;

    gap:
        20px;

    margin-bottom:
        14px;

}


.section-kicker {

    color:
        #6366f1;

    font-size:
        8px;

    font-weight:
        900;

    letter-spacing:
        2px;

}


.cart-heading h2 {

    margin:
        5px 0 3px;

    color:
        #111827;

    font-size:
        24px;

}


.cart-heading p {

    margin:
        0;

    color:
        #64748b;

    font-size:
        10px;

}


.add-more-button {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        10px 13px;

    border-radius:
        10px;

    color:
        #4338ca;

    background:
        rgba(255,255,255,.83);

    border:
        1px solid
        rgba(255,255,255,.68);

    text-decoration:
        none;

    font-size:
        9px;

    font-weight:
        900;

}


/* ============================================================
   CART LAYOUT
============================================================ */

.cart-layout {

    display:
        grid;

    grid-template-columns:
        minmax(0,1fr)
        350px;

    gap:
        20px;

    align-items:
        start;

}


/* ============================================================
   CART PRODUCT
============================================================ */

.cart-product {

    display:
        flex;

    gap:
        18px;

    padding:
        14px;

    margin-bottom:
        13px;

    border-radius:
        20px;

    background:
        rgba(255,255,255,.92);

    border:
        1px solid
        rgba(255,255,255,.72);

    box-shadow:
        0 12px 30px
        rgba(15,23,42,.07);

    transition:
        transform .2s ease,
        box-shadow .2s ease;

}


.cart-product:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 18px 40px
        rgba(15,23,42,.11);

}


/* ============================================================
   PRODUCT PHOTO
============================================================ */

.product-photo-box {

    position:
        relative;

    width:
        190px;

    height:
        190px;

    flex:
        0 0 190px;

    overflow:
        hidden;

    border-radius:
        17px;

    background:
        linear-gradient(
            135deg,
            #f8fafc,
            #eef2ff
        );

    border:
        1px solid
        #e2e8f0;

}


.real-product-image {

    display:
        block;

    width:
        100%;

    height:
        100%;

    object-fit:
        contain;

    padding:
        8px;

}


.image-placeholder {

    width:
        100%;

    height:
        100%;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        center;

    color:
        #94a3b8;

    background:
        linear-gradient(
            135deg,
            #eef2ff,
            #f8fafc
        );

}


.image-placeholder div {

    font-size:
        50px;

}


.image-placeholder span {

    margin-top:
        5px;

    font-size:
        9px;

    font-weight:
        700;

}


.photo-tag {

    position:
        absolute;

    top:
        8px;

    left:
        8px;

    padding:
        5px 7px;

    border-radius:
        7px;

    color:
        white;

    background:
        rgba(15,23,42,.76);

    font-size:
        6px;

    font-weight:
        900;

    letter-spacing:
        .8px;

}


.zoom-hint {

    position:
        absolute;

    bottom:
        7px;

    left:
        7px;

    right:
        7px;

    padding:
        5px;

    border-radius:
        6px;

    color:
        white;

    background:
        rgba(15,23,42,.63);

    text-align:
        center;

    font-size:
        6px;

    font-weight:
        900;

}


/* ============================================================
   PRODUCT DETAILS
============================================================ */

.product-details {

    flex:
        1;

    min-width:
        0;

}


.product-label {

    color:
        #6366f1;

    font-size:
        7px;

    font-weight:
        900;

    letter-spacing:
        1px;

}


.product-title-row {

    display:
        flex;

    align-items:
        flex-start;

    justify-content:
        space-between;

    gap:
        12px;

}


.product-title-row h3 {

    margin:
        6px 0 0;

    color:
        #111827;

    font-size:
        20px;

    line-height:
        1.3;

}


.unit-price {

    margin-top:
        4px;

    color:
        #4f46e5;

    font-size:
        18px;

    font-weight:
        900;

    white-space:
        nowrap;

}


.detail-grid {

    display:
        grid;

    grid-template-columns:
        repeat(3,1fr);

    gap:
        7px;

    margin-top:
        17px;

}


.detail-box {

    padding:
        9px;

    border:
        1px solid
        #e5e7eb;

    border-radius:
        10px;

    background:
        #f8fafc;

}


.detail-box span {

    display:
        block;

    color:
        #94a3b8;

    font-size:
        7px;

    font-weight:
        800;

}


.detail-box strong {

    display:
        block;

    margin-top:
        3px;

    color:
        #334155;

    font-size:
        11px;

}


.stock-ok {

    color:
        #16a34a !important;

}


.subtotal-row {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        10px;

    margin-top:
        15px;

    padding-top:
        12px;

    border-top:
        1px solid
        #eef2f7;

}


.subtotal-row span {

    display:
        block;

    color:
        #94a3b8;

    font-size:
        7px;

    font-weight:
        900;

    letter-spacing:
        .7px;

}


.subtotal-row strong {

    display:
        block;

    margin-top:
        3px;

    color:
        #2563eb;

    font-size:
        20px;

}


.remove-button {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        7px 9px;

    border-radius:
        8px;

    color:
        #b91c1c;

    background:
        #fee2e2;

    text-decoration:
        none;

    font-size:
        8px;

    font-weight:
        900;

}


/* ============================================================
   SUMMARY
============================================================ */

.order-summary {

    position:
        sticky;

    top:
        86px;

    padding:
        23px;

    border-radius:
        22px;

    color:
        white;

    background:
        linear-gradient(
            145deg,
            #0f172a,
            #312e81,
            #4f46e5
        );

    box-shadow:
        0 22px 55px
        rgba(31,41,55,.28);

}


.summary-header {

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    margin-bottom:
        18px;

}


.summary-icon {

    width:
        46px;

    height:
        46px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        12px;

    background:
        rgba(255,255,255,.10);

    font-size:
        22px;

}


.summary-kicker {

    color:
        #c7d2fe;

    font-size:
        7px;

    font-weight:
        900;

    letter-spacing:
        1.5px;

}


.order-summary h2 {

    margin:
        4px 0 0;

    font-size:
        20px;

}


.summary-line {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    padding:
        8px 0;

}


.summary-line span {

    color:
        rgba(255,255,255,.61);

    font-size:
        9px;

}


.summary-line strong {

    font-size:
        10px;

}


.free {

    color:
        #4ade80 !important;

}


.summary-divider {

    height:
        1px;

    margin:
        9px 0 14px;

    background:
        rgba(255,255,255,.13);

}


.total-label {

    color:
        rgba(255,255,255,.57);

    font-size:
        8px;

    font-weight:
        800;

}


.grand-total {

    margin:
        4px 0 17px;

    font-size:
        32px;

    font-weight:
        900;

}


.pay-button {

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    width:
        100%;

    padding:
        13px;

    border-radius:
        11px;

    color:
        white;

    background:
        #22c55e;

    text-decoration:
        none;

    font-size:
        10px;

    font-weight:
        900;

    box-shadow:
        0 10px 22px
        rgba(34,197,94,.22);

}


.pay-button:hover {

    background:
        #16a34a;

}


.secure-payment-box {

    display:
        flex;

    gap:
        9px;

    margin-top:
        13px;

    padding:
        11px;

    border-radius:
        11px;

    border:
        1px solid
        rgba(255,255,255,.11);

    background:
        rgba(255,255,255,.06);

}


.secure-icon {

    width:
        32px;

    height:
        32px;

    flex:
        0 0 32px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        8px;

    background:
        rgba(255,255,255,.08);

}


.secure-payment-box strong {

    display:
        block;

    font-size:
        9px;

}


.secure-payment-box p {

    margin:
        3px 0 0;

    color:
        rgba(255,255,255,.53);

    font-size:
        7px;

    line-height:
        1.5;

}


.payment-features {

    display:
        grid;

    gap:
        5px;

    margin-top:
        12px;

}


.payment-features div {

    color:
        rgba(255,255,255,.58);

    font-size:
        7px;

}


.back-shop {

    display:
        block;

    margin-top:
        14px;

    color:
        rgba(255,255,255,.66);

    text-align:
        center;

    text-decoration:
        none;

    font-size:
        8px;

}


/* ============================================================
   EMPTY CART
============================================================ */

.empty-cart {

    padding:
        70px 22px;

    text-align:
        center;

    border-radius:
        25px;

    background:
        rgba(255,255,255,.87);

    border:
        1px solid
        rgba(255,255,255,.72);

    box-shadow:
        0 18px 45px
        rgba(15,23,42,.08);

}


.empty-art {

    position:
        relative;

    width:
        120px;

    height:
        120px;

    margin:
        0 auto 20px;

}


.empty-cart-main {

    width:
        98px;

    height:
        98px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    margin:
        auto;

    border-radius:
        28px;

    background:
        linear-gradient(
            135deg,
            #eef2ff,
            #dbeafe
        );

    font-size:
        47px;

}


.float-icon {

    position:
        absolute;

    font-size:
        17px;

}


.float-icon.one {

    top:
        8px;

    right:
        0;

}


.float-icon.two {

    left:
        0;

    bottom:
        13px;

}


.float-icon.three {

    right:
        7px;

    bottom:
        0;

}


.empty-kicker {

    color:
        #6366f1;

    font-size:
        8px;

    font-weight:
        900;

    letter-spacing:
        2px;

}


.empty-cart h2 {

    margin:
        6px 0;

    color:
        #111827;

    font-size:
        27px;

}


.empty-cart p {

    max-width:
        520px;

    margin:
        0 auto 23px;

    color:
        #64748b;

    font-size:
        11px;

    line-height:
        1.6;

}


.start-shopping {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        12px 18px;

    border-radius:
        11px;

    color:
        white;

    background:
        #4f46e5;

    text-decoration:
        none;

    font-size:
        10px;

    font-weight:
        900;

}


/* ============================================================
   RESPONSIVE
============================================================ */

@media (max-width:950px) {

    .cart-layout {

        grid-template-columns:
            1fr;

    }


    .order-summary {

        position:
            relative;

        top:
            auto;

    }

}


@media (max-width:700px) {

    .advanced-cart-page {

        padding:
            17px 10px 55px;

    }


    .cart-hero {

        flex-direction:
            column;

        align-items:
            flex-start;

    }


    .hero-button {

        width:
            100%;

    }


    .cart-heading {

        flex-direction:
            column;

        align-items:
            stretch;

    }


    .add-more-button {

        text-align:
            center;

    }


    .cart-product {

        flex-direction:
            column;

    }


    .product-photo-box {

        width:
            100%;

        height:
            270px;

        flex:
            none;

    }


    .detail-grid {

        grid-template-columns:
            1fr 1fr;

    }


    .subtotal-row {

        flex-direction:
            column;

        align-items:
            flex-start;

    }


    .remove-button {

        width:
            100%;

    }

}


@media (max-width:450px) {

    .hero-icon {

        width:
            60px;

        height:
            60px;

        flex-basis:
            60px;

    }


    .cart-hero h1 {

        font-size:
            28px;

    }


    .product-title-row {

        flex-direction:
            column;

    }


    .detail-grid {

        grid-template-columns:
            1fr;

    }

}

</style>


<?php require "footer.php"; ?>
```
