```php
<?php
// ============================================================
// CONNECTHUB - HIGH TECH FIND PEOPLE
// BLUE / CYBER UI
// NO EMAIL DISPLAY
// FOLLOW + UNFOLLOW + MESSAGE
// ============================================================

require "config.php";

login_required();

$uid = (int)($_SESSION["user_id"] ?? 0);

if ($uid <= 0) {
    header("Location: login.php");
    exit;
}

$search = trim($_GET["search"] ?? "");


// ============================================================
// FOLLOW
// ============================================================

if (isset($_GET["follow"])) {

    $target = (int)($_GET["follow"] ?? 0);

    if ($target > 0 && $target !== $uid) {

        $stmt = $conn->prepare("
            INSERT IGNORE INTO follows
            (
                follower_id,
                following_id
            )
            VALUES (?, ?)
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $uid,
                $target
            );

            $stmt->execute();
            $stmt->close();
        }
    }

    header(
        "Location: users.php?search=" .
        urlencode($search)
    );

    exit;
}


// ============================================================
// UNFOLLOW
// ============================================================

if (isset($_GET["unfollow"])) {

    $target =
        (int)($_GET["unfollow"] ?? 0);

    if ($target > 0) {

        $stmt = $conn->prepare("
            DELETE FROM follows
            WHERE follower_id = ?
            AND following_id = ?
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $uid,
                $target
            );

            $stmt->execute();
            $stmt->close();
        }
    }

    header(
        "Location: users.php?search=" .
        urlencode($search)
    );

    exit;
}


// ============================================================
// GET USERS
// ============================================================

$users = null;

if ($search !== "") {

    $like =
        "%" . $search . "%";

    $stmt = $conn->prepare("
        SELECT
            u.id,
            u.name,
            u.profile_image,

            EXISTS(
                SELECT 1
                FROM follows f
                WHERE f.follower_id = ?
                AND f.following_id = u.id
            ) AS is_following

        FROM users u

        WHERE u.id != ?

        AND u.name LIKE ?

        ORDER BY u.name ASC
    ");

    if ($stmt) {

        $stmt->bind_param(
            "iis",
            $uid,
            $uid,
            $like
        );

        $stmt->execute();

        $users =
            $stmt->get_result();
    }

} else {

    $stmt = $conn->prepare("
        SELECT
            u.id,
            u.name,
            u.profile_image,

            EXISTS(
                SELECT 1
                FROM follows f
                WHERE f.follower_id = ?
                AND f.following_id = u.id
            ) AS is_following

        FROM users u

        WHERE u.id != ?

        ORDER BY u.name ASC
    ");

    if ($stmt) {

        $stmt->bind_param(
            "ii",
            $uid,
            $uid
        );

        $stmt->execute();

        $users =
            $stmt->get_result();
    }
}


// ============================================================
// COUNTS
// ============================================================

$followingCount = 0;
$followerCount = 0;

$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM follows
    WHERE follower_id = ?
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $uid
    );

    $stmt->execute();

    $followingCount =
        (int)$stmt
        ->get_result()
        ->fetch_row()[0];

    $stmt->close();
}


$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM follows
    WHERE following_id = ?
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $uid
    );

    $stmt->execute();

    $followerCount =
        (int)$stmt
        ->get_result()
        ->fetch_row()[0];

    $stmt->close();
}


// ============================================================
// HEADER
// ============================================================

require "header.php";

?>

<div class="tech-people-page">


    <!-- =====================================================
         HERO
    ====================================================== -->

    <section class="tech-hero">

        <div class="tech-grid-bg"></div>

        <div class="tech-glow glow-a"></div>
        <div class="tech-glow glow-b"></div>

        <div class="hero-content">

            <div class="hero-symbol">
                👥
            </div>

            <div>

                <div class="hero-mini">
                    CONNECTHUB // NETWORK
                </div>

                <h1>
                    Find People
                </h1>

                <p>
                    Discover users and build your
                    ConnectHub network.
                </p>

            </div>

        </div>


        <div class="network-stats">

            <div class="network-stat">

                <span>
                    FOLLOWING
                </span>

                <strong>
                    <?= $followingCount ?>
                </strong>

                <div class="stat-line"></div>

            </div>


            <div class="network-stat">

                <span>
                    FOLLOWERS
                </span>

                <strong>
                    <?= $followerCount ?>
                </strong>

                <div class="stat-line"></div>

            </div>


            <div class="network-stat">

                <span>
                    STATUS
                </span>

                <strong class="online-text">
                    ONLINE
                </strong>

                <div class="stat-line green-line"></div>

            </div>

        </div>

    </section>


    <!-- =====================================================
         SEARCH BAR
    ====================================================== -->

    <section class="tech-search-card">

        <div class="search-top">

            <div>

                <div class="tech-label">
                    USER DIRECTORY
                </div>

                <h2>
                    Search Network
                </h2>

            </div>

            <div class="scan-status">
                <span></span>
                SYSTEM READY
            </div>

        </div>


        <form
            method="GET"
            class="tech-search-form"
        >

            <div class="tech-input">

                <div class="search-icon">
                    🔍
                </div>

                <input
                    type="text"
                    name="search"
                    value="<?= e($search) ?>"
                    placeholder="Enter username..."
                    autocomplete="off"
                >

                <?php if ($search !== ""): ?>

                    <a
                        href="users.php"
                        class="clear-btn"
                    >
                        ×
                    </a>

                <?php endif; ?>

            </div>


            <button
                type="submit"
                class="tech-search-btn"
            >
                SEARCH
            </button>

        </form>

    </section>


    <!-- =====================================================
         SECTION BAR
    ====================================================== -->

    <div class="network-section-bar">

        <div>

            <span>
                NETWORK // MEMBERS
            </span>

            <h2>
                <?= $search !== ""
                    ? "Search Results"
                    : "Discover People"
                ?>
            </h2>

        </div>

        <div class="member-counter">
            <?= $users ? $users->num_rows : 0 ?>
            MEMBERS
        </div>

    </div>


    <!-- =====================================================
         USER GRID
    ====================================================== -->

    <section class="tech-user-grid">


        <?php if (
            $users &&
            $users->num_rows > 0
        ): ?>


            <?php while (
                $user =
                $users->fetch_assoc()
            ): ?>


                <?php

                $profileImage =
                    trim(
                        $user["profile_image"] ?? ""
                    );

                if (
                    $profileImage !== ""
                ) {

                    $profileImage =
                        "uploads/" .
                        basename(
                            $profileImage
                        );
                }


                $isFollowing =
                    (int)(
                        $user["is_following"] ?? 0
                    ) === 1;

                ?>


                <!-- =========================================
                     USER CARD
                ========================================== -->

                <article class="tech-user-card">


                    <!-- TOP BAR -->

                    <div class="user-card-topbar">

                        <div class="signal-box">

                            <span></span>
                            <span></span>
                            <span></span>

                        </div>

                        <div class="user-code">
                            NODE #<?= (int)$user["id"] ?>
                        </div>

                    </div>


                    <!-- COVER -->

                    <div class="user-cover">

                        <div class="cover-grid"></div>

                        <div class="cover-line line-one"></div>
                        <div class="cover-line line-two"></div>

                    </div>


                    <!-- CONTENT -->

                    <div class="user-card-content">


                        <!-- AVATAR -->

                        <div class="tech-avatar">

                            <div class="avatar-ring"></div>

                            <?php if (
                                $profileImage !== ""
                            ): ?>

                                <img
                                    src="<?= e($profileImage) ?>"
                                    alt="Profile"
                                >

                            <?php else: ?>

                                <div class="avatar-letter">
                                    <?= e(
                                        strtoupper(
                                            substr(
                                                $user["name"],
                                                0,
                                                1
                                            )
                                        )
                                    ) ?>
                                </div>

                            <?php endif; ?>

                            <div class="avatar-status"></div>

                        </div>


                        <!-- USER -->

                        <div class="tech-user-name">

                            <h3>
                                <?= e(
                                    $user["name"]
                                ) ?>
                            </h3>

                            <div class="user-status-text">

                                <?php if ($isFollowing): ?>

                                    <span class="green-dot"></span>
                                    CONNECTED

                                <?php else: ?>

                                    <span class="blue-dot"></span>
                                    AVAILABLE

                                <?php endif; ?>

                            </div>

                        </div>


                        <!-- DECORATIVE INFO BAR -->

                        <div class="connection-bar">

                            <div class="connection-fill"></div>

                        </div>


                        <!-- BUTTONS -->

                        <div class="tech-actions">

                            <?php if ($isFollowing): ?>

                                <a
                                    href="users.php?unfollow=<?= (int)$user["id"] ?>&search=<?= urlencode($search) ?>"
                                    class="tech-follow-btn following"
                                    onclick="
                                        return confirm(
                                            'Unfollow <?= e($user["name"]) ?>?'
                                        );
                                    "
                                >
                                    <span>
                                        ✓
                                    </span>

                                    FOLLOWING

                                </a>


                                <a
                                    href="chat.php?user=<?= (int)$user["id"] ?>"
                                    class="tech-message-btn"
                                >
                                    <span>
                                        💬
                                    </span>

                                    MESSAGE

                                </a>

                            <?php else: ?>

                                <a
                                    href="users.php?follow=<?= (int)$user["id"] ?>&search=<?= urlencode($search) ?>"
                                    class="tech-follow-btn"
                                >
                                    <span>
                                        +
                                    </span>

                                    CONNECT

                                </a>

                            <?php endif; ?>

                        </div>


                    </div>


                </article>


            <?php endwhile; ?>


        <?php else: ?>


            <!-- EMPTY -->

            <div class="tech-empty">

                <div class="empty-radar">

                    <div class="radar-circle circle-one"></div>
                    <div class="radar-circle circle-two"></div>
                    <div class="radar-dot"></div>

                </div>

                <div class="tech-label">
                    NETWORK SCAN
                </div>

                <h2>
                    No Users Found
                </h2>

                <p>
                    No matching network members were found.
                </p>

                <a
                    href="users.php"
                    class="return-network"
                >
                    ↻ RESET SEARCH
                </a>

            </div>


        <?php endif; ?>


    </section>

</div>


<style>

/* ============================================================
   ROOT PAGE
============================================================ */

.tech-people-page {

    width:
        100%;

    max-width:
        1180px;

    margin:
        0 auto;

    padding:
        22px 20px 80px;

    color:
        #dbeafe;

}


/* ============================================================
   HERO
============================================================ */

.tech-hero {

    position:
        relative;

    overflow:
        hidden;

    min-height:
        205px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        20px;

    padding:
        29px;

    margin-bottom:
        20px;

    border:
        1px solid
        rgba(59,130,246,.30);

    border-radius:
        24px;

    background:
        linear-gradient(
            135deg,
            #020617 0%,
            #071a35 42%,
            #0c2a5a 100%
        );

    box-shadow:
        0 25px 70px
        rgba(2,6,23,.35);

}


/* ============================================================
   GRID BACKGROUND
============================================================ */

.tech-grid-bg {

    position:
        absolute;

    inset:
        0;

    opacity:
        .25;

    background-image:
        linear-gradient(
            rgba(59,130,246,.15) 1px,
            transparent 1px
        ),
        linear-gradient(
            90deg,
            rgba(59,130,246,.15) 1px,
            transparent 1px
        );

    background-size:
        30px 30px;

}


/* ============================================================
   GLOWS
============================================================ */

.tech-glow {

    position:
        absolute;

    border-radius:
        50%;

    filter:
        blur(4px);

    pointer-events:
        none;

}


.glow-a {

    width:
        350px;

    height:
        350px;

    right:
        -130px;

    top:
        -180px;

    background:
        rgba(37,99,235,.24);

}


.glow-b {

    width:
        260px;

    height:
        260px;

    left:
        35%;

    bottom:
        -190px;

    background:
        rgba(6,182,212,.13);

}


/* ============================================================
   HERO CONTENT
============================================================ */

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
        18px;

}


.hero-symbol {

    width:
        78px;

    height:
        78px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border:
        1px solid
        rgba(96,165,250,.35);

    border-radius:
        22px;

    background:
        linear-gradient(
            135deg,
            rgba(30,64,175,.55),
            rgba(14,116,144,.28)
        );

    box-shadow:
        inset 0 0 25px
        rgba(59,130,246,.15),
        0 0 30px
        rgba(37,99,235,.13);

    font-size:
        37px;

}


.hero-mini {

    color:
        #60a5fa;

    font-size:
        8px;

    font-weight:
        900;

    letter-spacing:
        2px;

}


.tech-hero h1 {

    margin:
        6px 0 5px;

    color:
        #eff6ff;

    font-size:
        35px;

}


.tech-hero p {

    margin:
        0;

    color:
        #93c5fd;

    font-size:
        11px;

}


/* ============================================================
   STATS
============================================================ */

.network-stats {

    position:
        relative;

    z-index:
        5;

    display:
        flex;

    gap:
        9px;

}


.network-stat {

    min-width:
        102px;

    padding:
        12px;

    border:
        1px solid
        rgba(96,165,250,.18);

    border-radius:
        12px;

    background:
        rgba(15,23,42,.48);

    backdrop-filter:
        blur(8px);

}


.network-stat span {

    display:
        block;

    color:
        #64748b;

    font-size:
        7px;

    font-weight:
        900;

    letter-spacing:
        1px;

}


.network-stat strong {

    display:
        block;

    margin-top:
        4px;

    color:
        #e0f2fe;

    font-size:
        21px;

}


.online-text {

    color:
        #4ade80 !important;

    font-size:
        12px !important;

}


.stat-line {

    width:
        100%;

    height:
        2px;

    margin-top:
        7px;

    background:
        linear-gradient(
            90deg,
            #2563eb,
            transparent
        );

}


.green-line {

    background:
        linear-gradient(
            90deg,
            #22c55e,
            transparent
        );

}


/* ============================================================
   SEARCH
============================================================ */

.tech-search-card {

    padding:
        20px;

    margin-bottom:
        20px;

    border:
        1px solid
        rgba(59,130,246,.20);

    border-radius:
        19px;

    background:
        linear-gradient(
            135deg,
            rgba(2,6,23,.94),
            rgba(7,29,62,.92)
        );

    box-shadow:
        0 16px 35px
        rgba(2,6,23,.18);

}


.search-top {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        15px;

    margin-bottom:
        13px;

}


.tech-label {

    color:
        #60a5fa;

    font-size:
        7px;

    font-weight:
        900;

    letter-spacing:
        1.8px;

}


.search-top h2 {

    margin:
        4px 0 0;

    color:
        #eff6ff;

    font-size:
        19px;

}


.scan-status {

    display:
        flex;

    align-items:
        center;

    gap:
        6px;

    padding:
        7px 9px;

    border:
        1px solid
        rgba(34,197,94,.20);

    border-radius:
        8px;

    color:
        #86efac;

    background:
        rgba(22,101,52,.16);

    font-size:
        7px;

    font-weight:
        900;

}


.scan-status span {

    width:
        6px;

    height:
        6px;

    border-radius:
        50%;

    background:
        #22c55e;

    box-shadow:
        0 0 8px
        #22c55e;

}


.tech-search-form {

    display:
        flex;

    gap:
        8px;

}


.tech-input {

    display:
        flex;

    align-items:
        center;

    flex:
        1;

    min-height:
        46px;

    border:
        1px solid
        rgba(96,165,250,.22);

    border-radius:
        11px;

    background:
        rgba(2,6,23,.67);

}


.search-icon {

    padding:
        0 11px;

    color:
        #60a5fa;

}


.tech-input input {

    flex:
        1;

    height:
        44px;

    padding:
        0 8px 0 0;

    border:
        none !important;

    outline:
        none;

    color:
        #e0f2fe;

    background:
        transparent !important;

    font-size:
        11px;

}


.tech-input input::placeholder {

    color:
        #64748b;

}


.clear-btn {

    padding:
        0 11px;

    color:
        #94a3b8;

    text-decoration:
        none;

    font-size:
        18px;

}


.tech-search-btn {

    min-width:
        110px;

    border:
        1px solid
        rgba(96,165,250,.25);

    border-radius:
        11px;

    color:
        #eff6ff;

    background:
        linear-gradient(
            135deg,
            #1d4ed8,
            #2563eb
        );

    font-size:
        9px;

    font-weight:
        900;

    letter-spacing:
        1px;

    cursor:
        pointer;

}


/* ============================================================
   SECTION BAR
============================================================ */

.network-section-bar {

    display:
        flex;

    align-items:
        flex-end;

    justify-content:
        space-between;

    gap:
        15px;

    margin-bottom:
        12px;

    padding:
        0 2px;

}


.network-section-bar span {

    color:
        #2563eb;

    font-size:
        7px;

    font-weight:
        900;

    letter-spacing:
        1.8px;

}


.network-section-bar h2 {

    margin:
        5px 0 0;

    color:
        #0f172a;

    font-size:
        23px;

}


.member-counter {

    padding:
        7px 10px;

    border:
        1px solid
        rgba(37,99,235,.18);

    border-radius:
        8px;

    color:
        #1d4ed8;

    background:
        rgba(239,246,255,.87);

    font-size:
        7px;

    font-weight:
        900;

}


/* ============================================================
   GRID
============================================================ */

.tech-user-grid {

    display:
        grid;

    grid-template-columns:
        repeat(2,minmax(0,1fr));

    gap:
        15px;

}


/* ============================================================
   USER CARD
============================================================ */

.tech-user-card {

    position:
        relative;

    overflow:
        hidden;

    border:
        1px solid
        rgba(96,165,250,.20);

    border-radius:
        18px;

    background:
        linear-gradient(
            145deg,
            rgba(2,6,23,.97),
            rgba(7,25,52,.98)
        );

    box-shadow:
        0 16px 38px
        rgba(2,6,23,.16);

    transition:
        transform .22s ease,
        box-shadow .22s ease,
        border-color .22s ease;

}


.tech-user-card:hover {

    transform:
        translateY(-4px);

    border-color:
        rgba(59,130,246,.48);

    box-shadow:
        0 22px 50px
        rgba(15,23,42,.24),
        0 0 25px
        rgba(37,99,235,.10);

}


/* ============================================================
   TOPBAR
============================================================ */

.user-card-topbar {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    min-height:
        31px;

    padding:
        0 11px;

    border-bottom:
        1px solid
        rgba(96,165,250,.10);

    background:
        rgba(2,6,23,.72);

}


.signal-box {

    display:
        flex;

    gap:
        3px;

}


.signal-box span {

    width:
        4px;

    height:
        4px;

    border-radius:
        50%;

    background:
        #3b82f6;

    box-shadow:
        0 0 6px
        rgba(59,130,246,.8);

}


.signal-box span:nth-child(2) {

    background:
        #06b6d4;

}


.signal-box span:nth-child(3) {

    background:
        #22c55e;

}


.user-code {

    color:
        #475569;

    font-size:
        6px;

    font-weight:
        900;

    letter-spacing:
        1px;

}


/* ============================================================
   COVER
============================================================ */

.user-cover {

    position:
        relative;

    height:
        78px;

    overflow:
        hidden;

    background:
        linear-gradient(
            135deg,
            #071a35,
            #0b3b78,
            #0e7490
        );

}


.cover-grid {

    position:
        absolute;

    inset:
        0;

    opacity:
        .26;

    background-image:
        linear-gradient(
            rgba(125,211,252,.17) 1px,
            transparent 1px
        ),
        linear-gradient(
            90deg,
            rgba(125,211,252,.17) 1px,
            transparent 1px
        );

    background-size:
        18px 18px;

}


.cover-line {

    position:
        absolute;

    height:
        1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            #60a5fa,
            transparent
        );

    opacity:
        .45;

}


.line-one {

    left:
        -20%;

    right:
        30%;

    top:
        28px;

}


.line-two {

    left:
        35%;

    right:
        -20%;

    bottom:
        18px;

}


/* ============================================================
   CARD CONTENT
============================================================ */

.user-card-content {

    position:
        relative;

    padding:
        0 15px 15px;

}


/* ============================================================
   AVATAR
============================================================ */

.tech-avatar {

    position:
        relative;

    width:
        76px;

    height:
        76px;

    margin-top:
        -38px;

    border-radius:
        50%;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        #061226;

    border:
        4px solid
        #061226;

    box-shadow:
        0 8px 25px
        rgba(0,0,0,.40);

}


.tech-avatar img,
.avatar-letter {

    position:
        relative;

    z-index:
        3;

    width:
        66px;

    height:
        66px;

    border-radius:
        50%;

    object-fit:
        cover;

}


.avatar-letter {

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    color:
        white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #0891b2
        );

    font-size:
        27px;

    font-weight:
        900;

}


.avatar-ring {

    position:
        absolute;

    inset:
        -5px;

    border:
        1px solid
        rgba(59,130,246,.72);

    border-radius:
        50%;

    box-shadow:
        0 0 16px
        rgba(37,99,235,.34);

}


.avatar-status {

    position:
        absolute;

    right:
        2px;

    bottom:
        7px;

    z-index:
        5;

    width:
        11px;

    height:
        11px;

    border:
        2px solid
        #061226;

    border-radius:
        50%;

    background:
        #22c55e;

    box-shadow:
        0 0 8px
        rgba(34,197,94,.65);

}


/* ============================================================
   USER NAME
============================================================ */

.tech-user-name {

    margin-top:
        8px;

}


.tech-user-name h3 {

    margin:
        0;

    color:
        #eff6ff;

    font-size:
        18px;

}


.user-status-text {

    display:
        flex;

    align-items:
        center;

    gap:
        5px;

    margin-top:
        5px;

    color:
        #64748b;

    font-size:
        7px;

    font-weight:
        900;

    letter-spacing:
        .8px;

}


.green-dot,
.blue-dot {

    width:
        6px;

    height:
        6px;

    border-radius:
        50%;

}


.green-dot {

    background:
        #22c55e;

    box-shadow:
        0 0 8px
        rgba(34,197,94,.65);

}


.blue-dot {

    background:
        #3b82f6;

    box-shadow:
        0 0 8px
        rgba(59,130,246,.65);

}


/* ============================================================
   CONNECTION BAR
============================================================ */

.connection-bar {

    width:
        100%;

    height:
        3px;

    margin:
        12px 0;

    overflow:
        hidden;

    border-radius:
        10px;

    background:
        rgba(59,130,246,.10);

}


.connection-fill {

    width:
        72%;

    height:
        100%;

    background:
        linear-gradient(
            90deg,
            #2563eb,
            #06b6d4
        );

    box-shadow:
        0 0 9px
        rgba(37,99,235,.65);

}


/* ============================================================
   BUTTONS
============================================================ */

.tech-actions {

    display:
        flex;

    gap:
        7px;

}


.tech-follow-btn,
.tech-message-btn {

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        5px;

    min-height:
        39px;

    border-radius:
        9px;

    text-decoration:
        none;

    font-size:
        8px;

    font-weight:
        900;

    letter-spacing:
        .7px;

    transition:
        .18s ease;

}


.tech-follow-btn {

    flex:
        1;

    color:
        white;

    background:
        linear-gradient(
            135deg,
            #1d4ed8,
            #2563eb,
            #0891b2
        );

    box-shadow:
        0 7px 18px
        rgba(37,99,235,.18);

}


.tech-follow-btn:hover {

    transform:
        translateY(-1px);

    box-shadow:
        0 10px 22px
        rgba(37,99,235,.28);

}


.tech-follow-btn.following {

    color:
        #bfdbfe;

    background:
        rgba(30,64,175,.24);

    border:
        1px solid
        rgba(59,130,246,.25);

    box-shadow:
        none;

}


.tech-message-btn {

    flex:
        .8;

    color:
        #7dd3fc;

    border:
        1px solid
        rgba(56,189,248,.25);

    background:
        rgba(14,116,144,.15);

}


.tech-message-btn:hover {

    color:
        white;

    background:
        rgba(14,116,144,.28);

}


/* ============================================================
   EMPTY
============================================================ */

.tech-empty {

    grid-column:
        1 / -1;

    min-height:
        360px;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        center;

    padding:
        40px;

    text-align:
        center;

    border:
        1px solid
        rgba(59,130,246,.15);

    border-radius:
        20px;

    background:
        linear-gradient(
            145deg,
            rgba(2,6,23,.94),
            rgba(7,25,52,.96)
        );

}


.empty-radar {

    position:
        relative;

    width:
        95px;

    height:
        95px;

    margin-bottom:
        18px;

}


.radar-circle {

    position:
        absolute;

    inset:
        0;

    border:
        1px solid
        rgba(59,130,246,.35);

    border-radius:
        50%;

}


.circle-two {

    inset:
        17px;

    border-color:
        rgba(6,182,212,.30);

}


.radar-dot {

    position:
        absolute;

    left:
        50%;

    top:
        50%;

    width:
        10px;

    height:
        10px;

    transform:
        translate(-50%,-50%);

    border-radius:
        50%;

    background:
        #38bdf8;

    box-shadow:
        0 0 18px
        rgba(56,189,248,.80);

}


.tech-empty h2 {

    margin:
        7px 0;

    color:
        #eff6ff;

}


.tech-empty p {

    margin:
        0;

    color:
        #64748b;

    font-size:
        10px;

}


.return-network {

    margin-top:
        16px;

    padding:
        10px 15px;

    border:
        1px solid
        rgba(59,130,246,.30);

    border-radius:
        9px;

    color:
        #bfdbfe;

    background:
        rgba(37,99,235,.14);

    text-decoration:
        none;

    font-size:
        8px;

    font-weight:
        900;

}


/* ============================================================
   RESPONSIVE
============================================================ */

@media (max-width:900px) {

    .tech-user-grid {

        grid-template-columns:
            1fr;

    }


    .network-stats {

        flex-wrap:
            wrap;

    }

}


@media (max-width:700px) {

    .tech-people-page {

        padding:
            15px 10px 55px;

    }


    .tech-hero {

        flex-direction:
            column;

        align-items:
            flex-start;

        padding:
            22px;

    }


    .network-stats {

        width:
            100%;

    }


    .network-stat {

        flex:
            1;

        min-width:
            0;

    }


    .search-top {

        flex-direction:
            column;

        align-items:
            flex-start;

    }


    .tech-search-form {

        flex-direction:
            column;

    }


    .tech-search-btn {

        width:
            100%;

        min-height:
            44px;

    }


    .network-section-bar {

        align-items:
            flex-start;

        flex-direction:
            column;

    }

}

</style>

<?php require "footer.php"; ?>
```
