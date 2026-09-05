<?php
// =====================================================
// CONNECTHUB - PRODUCT DETAIL
// =====================================================

require "config.php";
login_required();


// =====================================================
// PRODUCT ID
// =====================================================

$id = intval(
    $_GET["id"] ?? 0
);


if ($id <= 0) {
    header("Location: shop.php?err=" . urlencode("Product not found."));
    exit;
}


// =====================================================
// GET PRODUCT
// =====================================================

$stmt = $conn->prepare("
    SELECT *
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$product =
    $stmt->get_result()->fetch_assoc();

$stmt->close();


if (!$product) {
    header("Location: shop.php?err=" . urlencode("Product not found."));
    exit;
}


// =====================================================
// IMAGE PATH
// =====================================================

$image = product_image_url($product["image"] ?? "");


// =====================================================
// ADD TO CART
// =====================================================

$message = "";
$error = "";


if (isset($_POST["add"])) {

    $quantity = intval(
        $_POST["quantity"] ?? 1
    );


    if ($quantity < 1) {

        $quantity = 1;
    }


    $stock =
        intval(
            $product["stock"]
        );


    if ($stock <= 0) {

        $error =
            "This product is currently out of stock.";

    } elseif ($quantity > $stock) {

        $error =
            "Only " .
            $stock .
            " item(s) are available.";

    } else {


        // ---------------------------------------------
        // CHECK USER LOGIN
        // ---------------------------------------------

        $userId =
            intval(
                $_SESSION["user_id"] ?? 0
            );


        if ($userId <= 0) {

            $error =
                "Please login before adding products to cart.";

        } else {


            // -----------------------------------------
            // GET USER CART
            // -----------------------------------------

            $stmt = $conn->prepare("
                SELECT id
                FROM cart
                WHERE user_id = ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "i",
                $userId
            );

            $stmt->execute();

            $cart =
                $stmt->get_result()->fetch_assoc();

            $stmt->close();


            // -----------------------------------------
            // CREATE CART
            // -----------------------------------------

            if ($cart) {

                $cartId =
                    intval(
                        $cart["id"]
                    );

            } else {

                $stmt = $conn->prepare("
                    INSERT INTO cart
                    (user_id)
                    VALUES (?)
                ");

                $stmt->bind_param(
                    "i",
                    $userId
                );

                $stmt->execute();

                $cartId =
                    $conn->insert_id;

                $stmt->close();
            }


            // -----------------------------------------
            // CHECK EXISTING CART ITEM
            // -----------------------------------------

            $stmt = $conn->prepare("
                SELECT
                    id,
                    quantity
                FROM cart_items
                WHERE cart_id = ?
                AND product_id = ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "ii",
                $cartId,
                $id
            );

            $stmt->execute();

            $existingItem =
                $stmt->get_result()->fetch_assoc();

            $stmt->close();


            if ($existingItem) {

                $newQuantity =
                    intval(
                        $existingItem["quantity"]
                    ) + $quantity;


                if ($newQuantity > $stock) {

                    $error =
                        "You already have " .
                        $existingItem["quantity"] .
                        " in your cart. Only " .
                        $stock .
                        " available.";

                } else {

                    $stmt = $conn->prepare("
                        UPDATE cart_items
                        SET quantity = ?
                        WHERE id = ?
                    ");

                    $stmt->bind_param(
                        "ii",
                        $newQuantity,
                        $existingItem["id"]
                    );

                    $stmt->execute();

                    $stmt->close();


                    $message =
                        "Product quantity updated in cart! 🛒";
                }

            } else {

                // -------------------------------------
                // ADD NEW ITEM
                // -------------------------------------

                $stmt = $conn->prepare("
                    INSERT INTO cart_items
                    (
                        cart_id,
                        product_id,
                        quantity
                    )
                    VALUES (?, ?, ?)
                ");

                $stmt->bind_param(
                    "iii",
                    $cartId,
                    $id,
                    $quantity
                );

                if ($stmt->execute()) {

                    $message =
                        "Product added to cart successfully! 🛒";

                } else {

                    $error =
                        "Unable to add product to cart.";
                }

                $stmt->close();
            }
        }
    }
}


// =====================================================
// RELATED PRODUCTS
// SAME CATEGORY
// =====================================================

$relatedProducts = [];

$category =
    trim(
        $product["category"] ?? ""
    );


if ($category !== "") {

    $stmt = $conn->prepare("
        SELECT *
        FROM products
        WHERE category = ?
        AND id != ?
        ORDER BY id DESC
        LIMIT 8
    ");

    $stmt->bind_param(
        "si",
        $category,
        $id
    );

    $stmt->execute();

    $relatedResult =
        $stmt->get_result();


    while (
        $related =
        $relatedResult->fetch_assoc()
    ) {

        $relatedProducts[] =
            $related;
    }

    $stmt->close();
}

require "header.php";
?>



<!-- =====================================================
     PRODUCT PAGE
===================================================== -->

<div class="product-page">


    <!-- =================================================
         SUCCESS
    ================================================== -->

    <?php if ($message !== ""): ?>

        <div class="product-success">

            <span>
                ✅
            </span>

            <span>
                <?= e($message) ?>
            </span>

            <a href="cart.php">
                View Cart →
            </a>

        </div>

    <?php endif; ?>



    <!-- =================================================
         ERROR
    ================================================== -->

    <?php if ($error !== ""): ?>

        <div class="product-error">

            ❌

            <?= e($error) ?>

        </div>

    <?php endif; ?>



    <!-- =================================================
         BREADCRUMB
    ================================================== -->

    <div class="breadcrumb">

        <a href="shop.php">
            🛒 Shop
        </a>

        <span>
            →
        </span>

        <span>
            <?= e($product["name"]) ?>
        </span>

    </div>



    <!-- =================================================
         PRODUCT DETAILS
    ================================================== -->

    <div class="product-detail">


        <!-- =============================================
             PRODUCT IMAGE
        ============================================== -->

        <div class="product-detail-image-box">

            <?php if ($image !== ""): ?>

                <img
                    src="<?= e($image) ?>"
                    alt="<?= e($product["name"]) ?>"
                    class="product-detail-image"
                >

            <?php else: ?>

                <div class="product-detail-no-image">
                    🛒
                </div>

            <?php endif; ?>

        </div>



        <!-- =============================================
             PRODUCT INFORMATION
        ============================================== -->

        <div class="product-information">


            <!-- CATEGORY -->

            <a
                href="shop.php?search=<?= urlencode($product["category"]) ?>"
                class="detail-category"
            >
                <?= e($product["category"]) ?>
            </a>



            <!-- NAME -->

            <h1>

                <?= e($product["name"]) ?>

            </h1>



            <!-- BRAND -->

            <?php if (!empty($product["brand"])): ?>

                <div class="detail-brand">

                    <span>
                        Brand:
                    </span>

                    <strong>
                        <?= e($product["brand"]) ?>
                    </strong>

                </div>

            <?php endif; ?>



            <!-- RATING -->

            <?php if (
                isset($product["rating"]) &&
                $product["rating"] !== ""
            ): ?>

                <div class="detail-rating">

                    ⭐

                    <strong>
                        <?= e($product["rating"]) ?>
                    </strong>

                </div>

            <?php endif; ?>



            <!-- PRICE -->

            <div class="detail-price">

                ₹<?= number_format(
                    (float)$product["price"],
                    2
                ) ?>

            </div>



            <!-- DESCRIPTION -->

            <?php if (
                !empty($product["description"])
            ): ?>

                <div class="detail-description">

                    <h3>
                        Product Description
                    </h3>

                    <p>

                        <?= nl2br(
                            e(
                                $product["description"]
                            )
                        ) ?>

                    </p>

                </div>

            <?php endif; ?>



            <!-- STOCK -->

            <div class="detail-stock">

                <strong>
                    Stock:
                </strong>


                <?php if (
                    intval($product["stock"]) > 0
                ): ?>

                    <span class="stock-available">

                        ✓
                        <?= e(
                            $product["stock"]
                        ) ?>

                        available

                    </span>

                <?php else: ?>

                    <span class="stock-out">

                        ✕ Out of Stock

                    </span>

                <?php endif; ?>

            </div>



            <!-- =========================================
                 ADD TO CART
            ========================================== -->

            <?php if (
                intval($product["stock"]) > 0
            ): ?>


                <form
                    method="POST"
                    class="add-cart-form"
                >


                    <label
                        for="quantity"
                    >
                        Quantity
                    </label>


                    <div class="quantity-row">


                        <input
                            type="number"
                            id="quantity"
                            name="quantity"
                            value="1"
                            min="1"
                            max="<?= e(
                                $product["stock"]
                            ) ?>"
                            required
                        >


                        <button
                            type="submit"
                            name="add"
                            class="add-cart-button"
                        >

                            🛒 Add to Cart

                        </button>


                    </div>


                </form>


            <?php else: ?>


                <button
                    class="out-of-stock-button"
                    disabled
                >

                    Out of Stock

                </button>


            <?php endif; ?>



            <!-- =========================================
                 NAVIGATION
            ========================================== -->

            <div class="product-navigation">

                <a
                    href="cart.php"
                    class="cart-view-button"
                >
                    🛍 View Cart
                </a>


                <a
                    href="shop.php"
                    class="back-shop-button"
                >
                    ← Back to Shop
                </a>

            </div>


        </div>


    </div>



    <!-- =================================================
         RELATED PRODUCTS
    ================================================== -->

    <?php if (
        count($relatedProducts) > 0
    ): ?>


        <section class="related-section">


            <!-- SECTION HEADER -->

            <div class="related-header">

                <div>

                    <h2>
                        🔗 Related Products
                    </h2>

                    <p>
                        More products from
                        <strong>
                            <?= e($category) ?>
                        </strong>
                        category
                    </p>

                </div>


                <a
                    href="shop.php?search=<?= urlencode($category) ?>"
                    class="see-all"
                >
                    View All →
                </a>

            </div>



            <!-- RELATED PRODUCT GRID -->

            <div class="related-products">


                <?php foreach (
                    $relatedProducts
                    as $related
                ): ?>


                    <?php

                    $relatedImage =
                        trim(
                            $related["image"] ?? ""
                        );


                    if (
                        $relatedImage !== ""
                    ) {

                        if (
                            strpos(
                                $relatedImage,
                                "products/"
                            ) !== 0 &&
                            strpos(
                                $relatedImage,
                                "/"
                            ) !== 0 &&
                            strpos(
                                $relatedImage,
                                "http://"
                            ) !== 0 &&
                            strpos(
                                $relatedImage,
                                "https://"
                            ) !== 0
                        ) {

                            $relatedImage =
                                "products/" .
                                $relatedImage;
                        }
                    }

                    ?>


                    <div class="related-card">


                        <!-- IMAGE -->

                        <a
                            href="product.php?id=<?= (int)$related["id"] ?>"
                        >

                            <?php if (
                                $relatedImage !== ""
                            ): ?>

                                <img
                                    src="<?= e(
                                        $relatedImage
                                    ) ?>"
                                    alt="<?= e(
                                        $related["name"]
                                    ) ?>"
                                    class="related-image"
                                >

                            <?php else: ?>

                                <div class="related-no-image">
                                    🛒
                                </div>

                            <?php endif; ?>

                        </a>



                        <!-- CATEGORY -->

                        <small>

                            <?= e(
                                $related["category"]
                            ) ?>

                        </small>



                        <!-- NAME -->

                        <h3>

                            <a
                                href="product.php?id=<?= (int)$related["id"] ?>"
                            >

                                <?= e(
                                    $related["name"]
                                ) ?>

                            </a>

                        </h3>



                        <!-- BRAND -->

                        <?php if (
                            !empty(
                                $related["brand"]
                            )
                        ): ?>

                            <p class="related-brand">

                                <?= e(
                                    $related["brand"]
                                ) ?>

                            </p>

                        <?php endif; ?>



                        <!-- RATING -->

                        <?php if (
                            isset(
                                $related["rating"]
                            ) &&
                            $related["rating"] !== ""
                        ): ?>

                            <div class="related-rating">

                                ⭐
                                <?= e(
                                    $related["rating"]
                                ) ?>

                            </div>

                        <?php endif; ?>



                        <!-- PRICE -->

                        <div class="related-price">

                            ₹<?= number_format(
                                (float)$related["price"],
                                2
                            ) ?>

                        </div>



                        <!-- VIEW -->

                        <a
                            href="product.php?id=<?= (int)$related["id"] ?>"
                            class="related-button"
                        >

                            View Product →

                        </a>


                    </div>


                <?php endforeach; ?>


            </div>


        </section>


    <?php else: ?>


        <!-- =================================================
             NO RELATED PRODUCTS
        ================================================== -->

        <section class="related-section">


            <div class="related-header">

                <div>

                    <h2>
                        🔗 Related Products
                    </h2>

                    <p>
                        No other products are currently
                        available in this category.
                    </p>

                </div>

            </div>


        </section>


    <?php endif; ?>


</div>



<style>

/* =====================================================
   PRODUCT PAGE
===================================================== */

.product-page {

    max-width: 1200px;

    margin: 0 auto;

    padding: 25px 20px 60px;

}


/* =====================================================
   MESSAGES
===================================================== */

.product-success {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 15px 18px;

    margin-bottom: 20px;

    background: #dcfce7;

    color: #166534;

    border: 1px solid #86efac;

    border-radius: 12px;

    font-weight: bold;

}


.product-success a {

    margin-left: auto;

    color: #166534;

    text-decoration: underline;

}


.product-error {

    padding: 15px 18px;

    margin-bottom: 20px;

    background: #fee2e2;

    color: #991b1b;

    border: 1px solid #fca5a5;

    border-radius: 12px;

    font-weight: bold;

}


/* =====================================================
   BREADCRUMB
===================================================== */

.breadcrumb {

    display: flex;

    align-items: center;

    gap: 9px;

    margin-bottom: 20px;

    color: #667085;

    font-size: 14px;

}


.breadcrumb a {

    color: #4f46e5;

    text-decoration: none;

    font-weight: bold;

}


.breadcrumb a:hover {

    text-decoration: underline;

}


/* =====================================================
   PRODUCT DETAIL
===================================================== */

.product-detail {

    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        minmax(0, 1fr);

    gap: 45px;

    background: white;

    border: 1px solid #e5e7eb;

    border-radius: 24px;

    padding: 25px;

    box-shadow:
        0 10px 30px
        rgba(15,23,42,.08);

}


/* =====================================================
   IMAGE
===================================================== */

.product-detail-image-box {

    width: 100%;

    min-height: 520px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f8fafc;

    border-radius: 20px;

    overflow: hidden;

}


.product-detail-image {

    width: 100%;

    height: 520px;

    object-fit: contain;

    padding: 25px;

    display: block;

}


.product-detail-no-image {

    font-size: 100px;

    display: flex;

    align-items: center;

    justify-content: center;

}


/* =====================================================
   PRODUCT INFORMATION
===================================================== */

.product-information {

    padding:
        15px 10px;

}


.detail-category {

    display: inline-block;

    padding: 6px 10px;

    background: #eef2ff;

    color: #4338ca;

    border-radius: 8px;

    font-size: 13px;

    font-weight: bold;

    text-decoration: none;

}


.product-information h1 {

    margin:
        15px 0 12px;

    color: #111827;

    font-size: 35px;

    line-height: 1.2;

}


.detail-brand {

    color: #667085;

    margin-bottom: 10px;

}


.detail-brand strong {

    color: #111827;

}


.detail-rating {

    margin:
        10px 0;

    color: #b45309;

}


.detail-price {

    margin:
        18px 0;

    color: #111827;

    font-size: 34px;

    font-weight: 900;

}


/* =====================================================
   DESCRIPTION
===================================================== */

.detail-description {

    padding:
        18px 0;

    border-top:
        1px solid #e5e7eb;

    border-bottom:
        1px solid #e5e7eb;

}


.detail-description h3 {

    margin-top: 0;

    color: #111827;

}


.detail-description p {

    color: #667085;

    line-height: 1.8;

}


/* =====================================================
   STOCK
===================================================== */

.detail-stock {

    margin:
        18px 0;

    color: #374151;

}


.stock-available {

    color: #15803d;

    font-weight: bold;

    margin-left: 7px;

}


.stock-out {

    color: #dc2626;

    font-weight: bold;

    margin-left: 7px;

}


/* =====================================================
   ADD CART
===================================================== */

.add-cart-form {

    margin-top:
        22px;

}


.add-cart-form label {

    display: block;

    margin-bottom: 8px;

    color: #374151;

    font-weight: bold;

}


.quantity-row {

    display: flex;

    gap: 10px;

}


.quantity-row input {

    width: 90px;

    padding: 13px;

    border: 1px solid #d1d5db;

    border-radius: 10px;

    text-align: center;

    font-size: 16px;

}


.add-cart-button {

    flex: 1;

    border: none;

    border-radius: 10px;

    background: #4f46e5;

    color: white;

    padding: 13px 20px;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;

}


.add-cart-button:hover {

    background: #4338ca;

}


.out-of-stock-button {

    width: 100%;

    padding: 14px;

    border: none;

    border-radius: 10px;

    background: #9ca3af;

    color: white;

    font-weight: bold;

    cursor: not-allowed;

}


/* =====================================================
   NAVIGATION
===================================================== */

.product-navigation {

    display: flex;

    gap: 10px;

    margin-top: 20px;

}


.cart-view-button,
.back-shop-button {

    flex: 1;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 12px;

    border-radius: 10px;

    text-decoration: none;

    font-weight: bold;

}


.cart-view-button {

    background: #111827;

    color: white;

}


.back-shop-button {

    background: #f3f4f6;

    color: #374151;

}


.cart-view-button:hover {

    background: #000;

}


.back-shop-button:hover {

    background: #e5e7eb;

}


/* =====================================================
   RELATED PRODUCTS SECTION
===================================================== */

.related-section {

    margin-top: 35px;

    background: white;

    border:
        1px solid #e5e7eb;

    border-radius:
        22px;

    padding:
        25px;

    box-shadow:
        0 10px 30px
        rgba(15,23,42,.07);

}


.related-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    margin-bottom: 20px;

}


.related-header h2 {

    margin: 0;

    color: #111827;

}


.related-header p {

    margin:
        7px 0 0;

    color: #667085;

}


.see-all {

    color: #4f46e5;

    text-decoration: none;

    font-weight: bold;

    white-space: nowrap;

}


.see-all:hover {

    text-decoration: underline;

}


/* =====================================================
   RELATED GRID
===================================================== */

.related-products {

    display: grid;

    grid-template-columns:
        repeat(
            4,
            minmax(0, 1fr)
        );

    gap: 18px;

}


/* =====================================================
   RELATED CARD
===================================================== */

.related-card {

    border:
        1px solid #e5e7eb;

    border-radius:
        16px;

    padding:
        12px;

    background:
        white;

    transition:
        transform .2s ease,
        box-shadow .2s ease;

}


.related-card:hover {

    transform:
        translateY(-3px);

    box-shadow:
        0 10px 25px
        rgba(15,23,42,.10);

}


/* =====================================================
   RELATED IMAGE
===================================================== */

.related-image {

    width:
        100%;

    height:
        190px;

    object-fit:
        contain;

    background:
        #f8fafc;

    border-radius:
        12px;

    display:
        block;

}


.related-no-image {

    width:
        100%;

    height:
        190px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        #f8fafc;

    border-radius:
        12px;

    font-size:
        55px;

}


/* =====================================================
   RELATED CONTENT
===================================================== */

.related-card > small {

    display:
        inline-block;

    margin-top:
        12px;

    color:
        #4338ca;

    font-weight:
        bold;

}


.related-card h3 {

    margin:
        7px 0;

    font-size:
        16px;

}


.related-card h3 a {

    color:
        #111827;

    text-decoration:
        none;

}


.related-card h3 a:hover {

    color:
        #4f46e5;

}


.related-brand {

    margin:
        4px 0;

    color:
        #667085;

    font-size:
        13px;

}


.related-rating {

    margin:
        7px 0;

    color:
        #b45309;

    font-size:
        13px;

}


.related-price {

    margin:
        10px 0;

    color:
        #111827;

    font-size:
        19px;

    font-weight:
        800;

}


.related-button {

    display:
        block;

    text-align:
        center;

    padding:
        10px;

    background:
        #4f46e5;

    color:
        white;

    border-radius:
        9px;

    text-decoration:
        none;

    font-size:
        13px;

    font-weight:
        bold;

}


.related-button:hover {

    background:
        #4338ca;

}


/* =====================================================
   PRODUCT NOT FOUND
===================================================== */

.product-not-found {

    max-width:
        600px;

    margin:
        70px auto;

    padding:
        50px 20px;

    text-align:
        center;

    background:
        white;

    border:
        1px solid #e5e7eb;

    border-radius:
        20px;

}


.product-not-found div {

    font-size:
        70px;

}


.product-not-found h2 {

    color:
        #111827;

}


.product-not-found a {

    display:
        inline-block;

    margin-top:
        15px;

    padding:
        12px 20px;

    background:
        #4f46e5;

    color:
        white;

    text-decoration:
        none;

    border-radius:
        10px;

    font-weight:
        bold;

}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 900px) {

    .product-detail {

        grid-template-columns:
            1fr;

    }


    .related-products {

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );

    }

}


@media (max-width: 600px) {

    .product-page {

        padding:
            15px 10px 40px;

    }


    .product-detail {

        padding:
            15px;

        gap:
            20px;

    }


    .product-detail-image-box {

        min-height:
            350px;

    }


    .product-detail-image {

        height:
            350px;

    }


    .product-information h1 {

        font-size:
            27px;

    }


    .detail-price {

        font-size:
            28px;

    }


    .quantity-row {

        flex-direction:
            column;

    }


    .quantity-row input {

        width:
            100%;

    }


    .product-navigation {

        flex-direction:
            column;

    }


    .related-section {

        padding:
            15px;

    }


    .related-header {

        align-items:
            flex-start;

        flex-direction:
            column;

    }


    .related-products {

        grid-template-columns:
            1fr;

    }


    .related-image,
    .related-no-image {

        height:
            230px;

    }


    .product-success {

        align-items:
            flex-start;

        flex-wrap:
            wrap;

    }


    .product-success a {

        width:
            100%;

        margin-left:
            0;

    }

}

</style>



<?php
require "footer.php";
?>