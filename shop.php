<?php
// ============================================================
// CONNECTHUB - PREMIUM CYBER SHOP & MARKETPLACE
// REAL PRODUCT IMAGES + INSTANT ADD TO CART + TECH PROMO AD
// POST HANDLING BEFORE HEADER.PHP (RULE 6)
// ============================================================

require "config.php";

login_required();

$uid = (int)($_SESSION["user_id"] ?? 0);

if ($uid <= 0) {
    header("Location: login.php");
    exit;
}

$message = trim((string)($_GET["msg"] ?? ""));
$error   = trim((string)($_GET["err"] ?? ""));

// ============================================================
// ADD TO CART ACTION — PROCESSED BEFORE HEADER.PHP
// ============================================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_to_cart"])) {

    $productId = (int)($_POST["product_id"] ?? 0);
    $quantity  = max(1, (int)($_POST["quantity"] ?? 1));

    if ($productId <= 0) {
        header("Location: shop.php?err=" . urlencode("Invalid product selected."));
        exit;
    }

    // Check product exists and stock
    $stmt = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $prod = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$prod) {
        header("Location: shop.php?err=" . urlencode("Product not found."));
        exit;
    }

    $stock = (int)($prod["stock"] ?? 0);
    if ($stock <= 0) {
        header("Location: shop.php?err=" . urlencode($prod["name"] . " is currently out of stock."));
        exit;
    }

    // Get or create cart
    $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $cart = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($cart) {
        $cartId = (int)$cart["id"];
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $cartId = (int)$conn->insert_id;
        $stmt->close();
    }

    if ($cartId <= 0) {
        header("Location: shop.php?err=" . urlencode("Could not access shopping cart."));
        exit;
    }

    // Check existing item in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? LIMIT 1");
    $stmt->bind_param("ii", $cartId, $productId);
    $stmt->execute();
    $existingItem = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existingItem) {
        $newQty = (int)$existingItem["quantity"] + $quantity;
        if ($newQty > $stock) {
            header("Location: shop.php?err=" . urlencode("Cannot add more than " . $stock . " units in stock."));
            exit;
        }

        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $newQty, $existingItem["id"]);
        $stmt->execute();
        $stmt->close();
    } else {
        if ($quantity > $stock) {
            $quantity = $stock;
        }
        $stmt = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $cartId, $productId, $quantity);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: shop.php?msg=" . urlencode("Added " . $prod["name"] . " to your cart! 🛍"));
    exit;
}

// ============================================================
// SEARCH & CATEGORY FILTERS
// ============================================================

$search   = trim((string)($_GET["search"] ?? ""));
$category = trim((string)($_GET["category"] ?? ""));

// Fetch distinct categories for quick pills
$allCategories = [];
$catRes = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
if ($catRes) {
    while ($crow = $catRes->fetch_assoc()) {
        $cname = trim($crow["category"]);
        if ($cname !== "") {
            $allCategories[] = $cname;
        }
    }
}

// Total items in cart counter
$cartCount = 0;
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(ci.quantity), 0) AS total_items
    FROM cart_items ci
    INNER JOIN cart c ON c.id = ci.cart_id
    WHERE c.user_id = ?
");
if ($stmt) {
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $cres = $stmt->get_result()->fetch_assoc();
    $cartCount = (int)($cres["total_items"] ?? 0);
    $stmt->close();
}

// Query Products
if ($search !== "" && $category !== "") {
    $searchLike = "%" . $search . "%";
    $stmt = $conn->prepare("
        SELECT * FROM products
        WHERE category = ?
        AND (name LIKE ? OR brand LIKE ? OR description LIKE ? OR attributes LIKE ?)
        ORDER BY id DESC
    ");
    $stmt->bind_param("sssss", $category, $searchLike, $searchLike, $searchLike, $searchLike);
    $stmt->execute();
    $products = $stmt->get_result();
} elseif ($category !== "") {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? ORDER BY id DESC");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $products = $stmt->get_result();
} elseif ($search !== "") {
    $searchLike = "%" . $search . "%";
    $stmt = $conn->prepare("
        SELECT * FROM products
        WHERE name LIKE ? OR category LIKE ? OR brand LIKE ? OR description LIKE ? OR attributes LIKE ?
        ORDER BY id DESC
    ");
    $stmt->bind_param("sssss", $searchLike, $searchLike, $searchLike, $searchLike, $searchLike);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $products = $conn->query("SELECT * FROM products ORDER BY id DESC");
}

// ============================================================
// HEADER LOADED AFTER ALL ACTION PROCESSING (RULE 6)
// ============================================================
require "header.php";
?>

<div class="cyber-shop-container">

    <!-- =====================================================
         TOP HERO BAR
    ====================================================== -->
    <header class="shop-top-hero">
        <div class="hero-left">
            <div class="hero-chip">CONNECTHUB MARKETPLACE</div>
            <h1>🛒 Cyber Commerce</h1>
            <p>Explore exclusive electronics, streetwear, luxury watches and digital goods.</p>
        </div>
        <div class="hero-right">
            <a href="cart.php" class="cyber-cart-btn">
                <span class="cart-icon">🛍</span>
                <span class="cart-text">My Cart</span>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
            <a href="bank.php" class="cyber-bank-link" title="Check your digital banking balance">
                🏦 CH-Bank Pay
            </a>
        </div>
    </header>

    <!-- =====================================================
         ALERTS
    ====================================================== -->
    <?php if ($message !== ""): ?>
        <div class="shop-alert success">
            <span class="alert-icon">✓</span>
            <div><?= e($message) ?></div>
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="shop-alert error">
            <span class="alert-icon">✕</span>
            <div><?= e($error) ?></div>
        </div>
    <?php endif; ?>

    <!-- =====================================================
         HIGH-TECH VISUAL AD / SPONSORED PROMO BANNER
    ====================================================== -->
    <section class="cyber-ad-banner">
        <div class="ad-particles"></div>
        <div class="ad-content">
            <div class="ad-tags">
                <span class="tag-flash">⚡ LIMITED TIME OFFER</span>
                <span class="tag-ch">CONNECTHUB PRIME</span>
            </div>
            <h2>FUTURISTIC GADGETS & AR APPAREL EXPO</h2>
            <p>Pay directly using your <strong>ConnectHub Banking PIN</strong> at checkout for an instant <strong>15% CASHBACK</strong> deposited straight into your bank balance. Every purchase also rewards you with <strong>Arcade Game Coins</strong>!</p>
            <div class="ad-highlights">
                <div class="hl-item">🔐 Bank PIN Protected</div>
                <div class="hl-item">⚡ Instant Dispatch</div>
                <div class="hl-item">🎮 Earn Game Coins</div>
                <div class="hl-item">🛡️ 100% Genuine Tech</div>
            </div>
        </div>
        <div class="ad-badge-side">
            <div class="discount-orb">
                <span class="orb-pct">40%</span>
                <span class="orb-sub">UP TO OFF</span>
            </div>
        </div>
    </section>

    <!-- =====================================================
         SEARCH & CATEGORY FILTER BAR
    ====================================================== -->
    <div class="shop-filter-bar">
        <form class="shop-search-form" method="GET" action="shop.php">
            <?php if ($category !== ""): ?>
                <input type="hidden" name="category" value="<?= e($category) ?>">
            <?php endif; ?>
            <div class="search-input-box">
                <span class="search-ico">🔍</span>
                <input
                    type="text"
                    name="search"
                    value="<?= e($search) ?>"
                    placeholder="Search smart devices, shoes, perfume, watches..."
                    autocomplete="off"
                >
                <?php if ($search !== ""): ?>
                    <a href="shop.php<?= $category !== "" ? "?category=" . urlencode($category) : "" ?>" class="search-clear-btn" title="Clear Search">✕</a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-submit-btn">Filter</button>
        </form>

        <div class="category-pills">
            <a href="shop.php<?= $search !== "" ? "?search=" . urlencode($search) : "" ?>" class="cat-pill <?= ($category === "") ? "active" : "" ?>">
                🌟 All Tech
            </a>
            <?php foreach ($allCategories as $cat): ?>
                <a href="shop.php?category=<?= urlencode($cat) ?><?= $search !== "" ? "&search=" . urlencode($search) : "" ?>" class="cat-pill <?= ($category === $cat) ? "active" : "" ?>">
                    <?= e(ucfirst(strtolower($cat))) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Active Filter Summary -->
    <?php if ($search !== "" || $category !== ""): ?>
        <div class="filter-results-info">
            <span>Showing results for: </span>
            <?php if ($category !== ""): ?>
                <strong class="filter-chip">Category: <?= e($category) ?></strong>
            <?php endif; ?>
            <?php if ($search !== ""): ?>
                <strong class="filter-chip">Search: "<?= e($search) ?>"</strong>
            <?php endif; ?>
            <a href="shop.php" class="reset-link">Clear all filters</a>
        </div>
    <?php endif; ?>

    <!-- =====================================================
         PRODUCTS GRID
    ====================================================== -->
    <div class="shop-products-grid">
        <?php if ($products && $products->num_rows > 0): ?>
            <?php while ($p = $products->fetch_assoc()): ?>
                <?php
                $resolvedImage = product_image_url($p["image"] ?? "");
                $stock = (int)($p["stock"] ?? 10);
                $isOutOfStock = ($stock <= 0);
                $price = (float)($p["price"] ?? 0);
                ?>
                <div class="product-card <?= $isOutOfStock ? "out-of-stock" : "" ?>">
                    
                    <!-- Card Top Header -->
                    <div class="card-head">
                        <span class="product-cat-tag">
                            <?= e($p["category"] ?? "Product") ?>
                        </span>
                        <?php if (isset($p["rating"]) && $p["rating"] !== ""): ?>
                            <span class="product-rating-badge">
                                ⭐ <?= e(number_format((float)$p["rating"], 1)) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Product Image Container -->
                    <a href="product.php?id=<?= (int)$p["id"] ?>" class="product-visual-wrapper">
                        <?php if ($resolvedImage !== ""): ?>
                            <img
                                src="<?= e($resolvedImage) ?>"
                                alt="<?= e($p["name"]) ?>"
                                class="product-img"
                                loading="lazy"
                                onerror="this.onerror=null; this.src='uploads/shop-bg.jpg';"
                            >
                        <?php else: ?>
                            <div class="no-image-placeholder">🛒</div>
                        <?php endif; ?>
                        <?php if ($isOutOfStock): ?>
                            <div class="stock-overlay">SOLD OUT</div>
                        <?php endif; ?>
                    </a>

                    <!-- Product Information -->
                    <div class="product-body">
                        <?php if (!empty($p["brand"])): ?>
                            <div class="product-brand-line">
                                <?= e(strtoupper($p["brand"])) ?>
                            </div>
                        <?php endif; ?>

                        <h3 class="product-title">
                            <a href="product.php?id=<?= (int)$p["id"] ?>">
                                <?= e($p["name"]) ?>
                            </a>
                        </h3>

                        <?php if (!empty($p["description"])): ?>
                            <p class="product-snippet">
                                <?= e(mb_strimwidth($p["description"], 0, 75, "...")) ?>
                            </p>
                        <?php endif; ?>

                        <div class="product-pricing-row">
                            <div class="price-stack">
                                <span class="price-currency">₹</span>
                                <span class="price-val"><?= number_format($price, 2) ?></span>
                            </div>
                            <div class="stock-indicator <?= $stock < 5 ? "low" : "ok" ?>">
                                <?= $isOutOfStock ? "● Out of Stock" : ($stock < 5 ? "● Low: " . $stock . " left" : "● In Stock") ?>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="product-card-actions">
                        <?php if (!$isOutOfStock): ?>
                            <form method="POST" action="shop.php" class="instant-add-form">
                                <input type="hidden" name="product_id" value="<?= (int)$p["id"] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" name="add_to_cart" class="btn-add-cart">
                                    <span>🛒</span> Add to Cart
                                </button>
                            </form>
                        <?php else: ?>
                            <button type="button" class="btn-add-cart disabled" disabled>
                                Sold Out
                            </button>
                        <?php endif; ?>
                        <a href="product.php?id=<?= (int)$p["id"] ?>" class="btn-view-details" title="View product details">
                            👁
                        </a>
                    </div>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="shop-empty-state">
                <div class="empty-icon">🛰️</div>
                <h3>No Products Found</h3>
                <p>We could not find any matching products in the ConnectHub inventory.</p>
                <a href="shop.php" class="btn-reset-shop">View All Products</a>
            </div>
        <?php endif; ?>
    </div>

</div>

<style>
/* ============================================================
   CYBER SHOP STYLING (RULE 2, 12, 13)
============================================================ */

.cyber-shop-container {
    max-width: 1240px;
    margin: 0 auto;
    padding: 24px 20px 80px;
    color: #f1f5f9;
}

/* Top Hero Bar */
.shop-top-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
    padding: 24px 28px;
    border-radius: 20px;
    background: rgba(15, 23, 42, 0.82);
    backdrop-filter: blur(14px);
    border: 1px solid rgba(99, 102, 241, 0.2);
    box-shadow: 0 15px 35px rgba(2, 6, 23, 0.4);
}

.hero-chip {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.5px;
    color: #818cf8;
    background: rgba(99, 102, 241, 0.14);
    border: 1px solid rgba(99, 102, 241, 0.25);
    margin-bottom: 6px;
}

.shop-top-hero h1 {
    margin: 4px 0;
    font-size: 28px;
    font-weight: 900;
    color: #ffffff;
    letter-spacing: -0.5px;
}

.shop-top-hero p {
    margin: 0;
    color: #94a3b8;
    font-size: 13px;
}

.hero-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.cyber-cart-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    border-radius: 12px;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #ffffff;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    box-shadow: 0 8px 22px rgba(79, 70, 229, 0.35);
    transition: transform .18s ease, filter .18s ease;
}

.cyber-cart-btn:hover {
    transform: translateY(-2px);
    filter: brightness(1.1);
}

.cart-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 6px;
    border-radius: 999px;
    background: #ef4444;
    color: #fff;
    font-size: 11px;
    font-weight: 900;
}

.cyber-bank-link {
    display: inline-flex;
    align-items: center;
    padding: 12px 18px;
    border-radius: 12px;
    background: rgba(37, 99, 235, 0.15);
    border: 1px solid rgba(59, 130, 246, 0.3);
    color: #60a5fa;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    transition: background .2s ease;
}

.cyber-bank-link:hover {
    background: rgba(37, 99, 235, 0.28);
    color: #93c5fd;
}

/* Alerts */
.shop-alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 14px;
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 600;
}

.shop-alert.success {
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.35);
    color: #34d399;
}

.shop-alert.error {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.35);
    color: #f87171;
}

/* Promotional High-Tech Ad Banner */
.cyber-ad-banner {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 25px;
    padding: 26px 32px;
    border-radius: 22px;
    margin-bottom: 28px;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(17, 24, 39, 0.95), rgba(30, 58, 138, 0.88), rgba(88, 28, 135, 0.75));
    border: 1px solid rgba(99, 102, 241, 0.35);
    box-shadow: 0 15px 40px rgba(2, 6, 23, 0.6), inset 0 0 30px rgba(99, 102, 241, 0.15);
}

.ad-tags {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
}

.tag-flash {
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 800;
    color: #fef08a;
    background: rgba(234, 179, 8, 0.2);
    border: 1px solid rgba(234, 179, 8, 0.35);
}

.tag-ch {
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 800;
    color: #a5b4fc;
    background: rgba(99, 102, 241, 0.2);
    border: 1px solid rgba(99, 102, 241, 0.35);
}

.ad-content h2 {
    margin: 6px 0;
    font-size: 22px;
    font-weight: 900;
    color: #ffffff;
    letter-spacing: 0.5px;
}

.ad-content p {
    margin: 0 0 14px;
    color: #cbd5e1;
    font-size: 13px;
    line-height: 1.5;
    max-width: 750px;
}

.ad-highlights {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.hl-item {
    font-size: 11px;
    font-weight: 700;
    color: #e2e8f0;
    background: rgba(255, 255, 255, 0.08);
    padding: 4px 12px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.12);
}

.discount-orb {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ef4444, #f59e0b);
    color: #fff;
    box-shadow: 0 0 35px rgba(239, 68, 68, 0.55);
    animation: pulseGlow 3s infinite alternate;
}

.orb-pct {
    font-size: 26px;
    font-weight: 900;
    line-height: 1;
}

.orb-sub {
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1px;
    margin-top: 2px;
}

@keyframes pulseGlow {
    0% { transform: scale(0.98); box-shadow: 0 0 20px rgba(239, 68, 68, 0.4); }
    100% { transform: scale(1.05); box-shadow: 0 0 40px rgba(245, 158, 11, 0.7); }
}

/* Filter Bar & Pills */
.shop-filter-bar {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 22px;
}

.shop-search-form {
    display: flex;
    gap: 10px;
}

.search-input-box {
    position: relative;
    flex: 1;
    display: flex;
    align-items: center;
}

.search-ico {
    position: absolute;
    left: 16px;
    font-size: 15px;
    color: #64748b;
}

.search-input-box input {
    width: 100%;
    padding: 14px 40px 14px 44px;
    border-radius: 14px;
    border: 1px solid rgba(99, 102, 241, 0.2);
    background: rgba(15, 23, 42, 0.85);
    color: #ffffff;
    font-size: 14px;
    outline: none;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.search-input-box input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 16px rgba(99, 102, 241, 0.25);
}

.search-clear-btn {
    position: absolute;
    right: 14px;
    color: #94a3b8;
    text-decoration: none;
    font-weight: bold;
    font-size: 14px;
}

.search-submit-btn {
    padding: 14px 24px;
    border-radius: 14px;
    border: none;
    background: #3b82f6;
    color: #fff;
    font-weight: 700;
    cursor: pointer;
    transition: background .2s ease;
}

.search-submit-btn:hover {
    background: #2563eb;
}

.category-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.cat-pill {
    padding: 7px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    color: #94a3b8;
    background: rgba(15, 23, 42, 0.7);
    border: 1px solid rgba(148, 163, 184, 0.15);
    text-decoration: none;
    transition: all .18s ease;
}

.cat-pill:hover {
    color: #ffffff;
    border-color: rgba(99, 102, 241, 0.4);
    background: rgba(30, 41, 59, 0.9);
}

.cat-pill.active {
    color: #ffffff;
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    border-color: #818cf8;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
}

.filter-results-info {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
    font-size: 13px;
    color: #94a3b8;
}

.filter-chip {
    padding: 3px 10px;
    border-radius: 6px;
    background: rgba(99, 102, 241, 0.18);
    color: #a5b4fc;
    font-weight: 600;
}

.reset-link {
    color: #f87171;
    text-decoration: underline;
    font-size: 12px;
}

/* Products Grid */
.shop-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 22px;
}

.product-card {
    display: flex;
    flex-direction: column;
    border-radius: 20px;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(99, 102, 241, 0.18);
    padding: 16px;
    box-shadow: 0 12px 30px rgba(2, 6, 23, 0.45);
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
}

.product-card:hover {
    transform: translateY(-5px);
    border-color: rgba(99, 102, 241, 0.45);
    box-shadow: 0 20px 45px rgba(2, 6, 23, 0.6), 0 0 25px rgba(79, 70, 229, 0.15);
}

.card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.product-cat-tag {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #818cf8;
    background: rgba(99, 102, 241, 0.12);
    padding: 3px 8px;
    border-radius: 6px;
}

.product-rating-badge {
    font-size: 11px;
    font-weight: 700;
    color: #fbbf24;
}

/* Product Visual Container */
.product-visual-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 190px;
    border-radius: 14px;
    background: rgba(2, 6, 23, 0.65);
    padding: 12px;
    margin-bottom: 14px;
    overflow: hidden;
    text-decoration: none;
}

.product-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform .25s ease;
}

.product-card:hover .product-img {
    transform: scale(1.06);
}

.no-image-placeholder {
    font-size: 48px;
    opacity: 0.5;
}

.stock-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.85);
    color: #f87171;
    font-weight: 900;
    font-size: 16px;
    letter-spacing: 1px;
}

.product-body {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-brand-line {
    font-size: 10px;
    font-weight: 800;
    color: #64748b;
    letter-spacing: 1px;
    margin-bottom: 4px;
}

.product-title {
    margin: 0 0 6px;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.3;
}

.product-title a {
    color: #f8fafc;
    text-decoration: none;
    transition: color .18s ease;
}

.product-title a:hover {
    color: #818cf8;
}

.product-snippet {
    font-size: 12px;
    color: #94a3b8;
    margin: 0 0 12px;
    line-height: 1.4;
}

.product-pricing-row {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-top: auto;
    padding-top: 10px;
    border-top: 1px solid rgba(148, 163, 184, 0.1);
    margin-bottom: 14px;
}

.price-stack {
    display: flex;
    align-items: baseline;
    gap: 2px;
}

.price-currency {
    font-size: 14px;
    font-weight: 800;
    color: #38bdf8;
}

.price-val {
    font-size: 20px;
    font-weight: 900;
    color: #ffffff;
}

.stock-indicator {
    font-size: 11px;
    font-weight: 700;
}

.stock-indicator.ok {
    color: #34d399;
}

.stock-indicator.low {
    color: #fbbf24;
}

/* Actions */
.product-card-actions {
    display: flex;
    gap: 8px;
}

.instant-add-form {
    flex: 1;
}

.btn-add-cart {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 11px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
    transition: transform .18s ease, filter .18s ease;
}

.btn-add-cart:hover {
    transform: translateY(-2px);
    filter: brightness(1.1);
}

.btn-add-cart.disabled {
    background: rgba(148, 163, 184, 0.2);
    color: #64748b;
    cursor: not-allowed;
    box-shadow: none;
}

.btn-view-details {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #cbd5e1;
    text-decoration: none;
    font-size: 15px;
    transition: background .2s ease, color .2s ease;
}

.btn-view-details:hover {
    background: rgba(255, 255, 255, 0.16);
    color: #ffffff;
}

/* Empty State */
.shop-empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    border-radius: 20px;
    background: rgba(15, 23, 42, 0.7);
    border: 1px solid rgba(99, 102, 241, 0.2);
}

.empty-icon {
    font-size: 50px;
    margin-bottom: 12px;
}

.shop-empty-state h3 {
    font-size: 22px;
    color: #fff;
    margin: 0 0 6px;
}

.shop-empty-state p {
    color: #94a3b8;
    margin: 0 0 20px;
}

.btn-reset-shop {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 12px;
    background: #4f46e5;
    color: #fff;
    text-decoration: none;
    font-weight: 700;
}

@media (max-width: 768px) {
    .shop-top-hero {
        flex-direction: column;
        align-items: flex-start;
    }
    .hero-right {
        width: 100%;
        justify-content: space-between;
    }
    .cyber-ad-banner {
        flex-direction: column;
    }
    .discount-orb {
        width: 80px;
        height: 80px;
    }
    .orb-pct {
        font-size: 22px;
    }
}
</style>

<?php require "footer.php"; ?>