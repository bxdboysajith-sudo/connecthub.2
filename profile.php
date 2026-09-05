<?php
// ============================================================
// CONNECTHUB - ADVANCED PROFILE PAGE
// ============================================================
// FEATURES
// ------------------------------------------------------------
// • Premium high-tech profile design
// • Animated profile hero
// • Glowing profile photo
// • Cover background
// • Online status
// • Posts / Followers / Following statistics
// • Generated username
// • Join date
// • Edit Profile
// • Messages
// • Share Profile
// • Copy Profile Link
// • Achievements
// • Activity overview
// • Responsive post gallery
// • Image / video support
// • Full-screen post viewer
// • Animated cards
// • Hover effects
// • Blue / indigo / cyan theme
// ============================================================


require "header.php";


/* ============================================================
   CURRENT USER
============================================================ */

$uid = (int)($_SESSION["user_id"] ?? 0);

if ($uid <= 0) {
    echo '
        <div style="padding:40px;text-align:center;">
            <h2>Session expired</h2>
            <a href="login.php">Login again</a>
        </div>
    ';
    require "footer.php";
    exit;
}


/* ============================================================
   GET USER
============================================================ */

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        email,
        profile_image,
        created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");


$user = null;


if ($stmt) {

    $stmt->bind_param(
        "i",
        $uid
    );

    $stmt->execute();

    $result =
        $stmt->get_result();

    $user =
        $result
            ? $result->fetch_assoc()
            : null;

    $stmt->close();

}


if (!$user) {

    echo '
        <div style="
            padding:50px;
            text-align:center;
            color:white;
        ">
            <h2>Profile not found</h2>
        </div>
    ';

    require "footer.php";

    exit;
}


/* ============================================================
   USER NAME
============================================================ */

$userName =
    trim(
        $user["name"] ?? "User"
    );

if ($userName === "") {
    $userName = "User";
}


/* ============================================================
   GENERATED USERNAME
============================================================ */

$handle =
    strtolower(
        preg_replace(
            "/[^a-zA-Z0-9]+/",
            "",
            $userName
        )
    );


if ($handle === "") {
    $handle = "connecthubuser";
}


$handle =
    "@" .
    $handle .
    $uid;


/* ============================================================
   PROFILE IMAGE
============================================================ */

$profileImage =
    trim(
        $user["profile_image"] ?? ""
    );


if ($profileImage !== "") {

    $profileImage =
        "uploads/" .
        basename(
            $profileImage
        );

}


/* ============================================================
   PROFILE INITIAL
============================================================ */

$initial =
    strtoupper(
        substr(
            $userName,
            0,
            1
        )
    );


if ($initial === "") {
    $initial = "C";
}


/* ============================================================
   JOIN DATE
============================================================ */

$joinDate = "ConnectHub Member";


if (!empty($user["created_at"])) {

    $timestamp =
        strtotime(
            $user["created_at"]
        );

    if ($timestamp !== false) {

        $joinDate =
            date(
                "M Y",
                $timestamp
            );

    }

}


/* ============================================================
   POST COUNT
============================================================ */

$postCount = 0;


$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM posts
    WHERE user_id = ?
");


if ($stmt) {

    $stmt->bind_param(
        "i",
        $uid
    );

    $stmt->execute();

    $result =
        $stmt->get_result();

    if ($result) {

        $row =
            $result->fetch_assoc();

        $postCount =
            (int)(
                $row["total"] ?? 0
            );

    }

    $stmt->close();

}


/* ============================================================
   FOLLOWING COUNT
============================================================ */

$followingCount = 0;


$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM follows
    WHERE follower_id = ?
");


if ($stmt) {

    $stmt->bind_param(
        "i",
        $uid
    );

    $stmt->execute();

    $result =
        $stmt->get_result();

    if ($result) {

        $row =
            $result->fetch_assoc();

        $followingCount =
            (int)(
                $row["total"] ?? 0
            );

    }

    $stmt->close();

}


/* ============================================================
   FOLLOWER COUNT
============================================================ */

$followerCount = 0;


$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM follows
    WHERE following_id = ?
");


if ($stmt) {

    $stmt->bind_param(
        "i",
        $uid
    );

    $stmt->execute();

    $result =
        $stmt->get_result();

    if ($result) {

        $row =
            $result->fetch_assoc();

        $followerCount =
            (int)(
                $row["total"] ?? 0
            );

    }

    $stmt->close();

}


/* ============================================================
   TOTAL LIKES ON USER POSTS
============================================================ */

$totalLikes = 0;


$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total
    FROM likes l
    INNER JOIN posts p
        ON p.id = l.post_id
    WHERE p.user_id = ?
");


if ($stmt) {

    $stmt->bind_param(
        "i",
        $uid
    );

    $stmt->execute();

    $result =
        $stmt->get_result();

    if ($result) {

        $row =
            $result->fetch_assoc();

        $totalLikes =
            (int)(
                $row["total"] ?? 0
            );

    }

    $stmt->close();

}


/* ============================================================
   TOTAL COMMENTS ON USER POSTS
============================================================ */

$totalComments = 0;


$stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total
    FROM comments c
    INNER JOIN posts p
        ON p.id = c.post_id
    WHERE p.user_id = ?
");


if ($stmt) {

    $stmt->bind_param(
        "i",
        $uid
    );

    $stmt->execute();

    $result =
        $stmt->get_result();

    if ($result) {

        $row =
            $result->fetch_assoc();

        $totalComments =
            (int)(
                $row["total"] ?? 0
            );

    }

    $stmt->close();

}


/* ============================================================
   GET POSTS
============================================================ */

$posts = [];


$stmt = $conn->prepare("
    SELECT
        id,
        image,
        caption,
        media_type,
        created_at
    FROM posts
    WHERE user_id = ?
    ORDER BY id DESC
");


if ($stmt) {

    $stmt->bind_param(
        "i",
        $uid
    );

    $stmt->execute();

    $result =
        $stmt->get_result();

    if ($result) {

        while (
            $row =
            $result->fetch_assoc()
        ) {

            $posts[] =
                $row;

        }

    }

    $stmt->close();

}


/* ============================================================
   PROFILE STRENGTH
============================================================ */

$profileStrength = 45;


if ($profileImage !== "") {
    $profileStrength += 25;
}


if ($userName !== "User") {
    $profileStrength += 15;
}


if ($postCount > 0) {
    $profileStrength += 15;
}


$profileStrength =
    min(
        100,
        $profileStrength
    );


/* ============================================================
   LEVEL
============================================================ */

$levelPoints =
    $postCount * 20 +
    $followerCount * 5 +
    $followingCount * 2 +
    $totalLikes * 2 +
    $totalComments;


$level =
    max(
        1,
        (int)(
            floor(
                $levelPoints / 100
            ) + 1
        )
    );


$nextLevelTarget =
    $level * 100;


$levelProgress =
    $levelPoints %
    100;


if ($levelProgress === 0 && $level > 1) {
    $levelProgress = 100;
}


/* ============================================================
   BADGES
============================================================ */

$badgeList = [];


if ($postCount >= 1) {

    $badgeList[] = [
        "icon" => "📸",
        "title" => "Creator",
        "text" => "Published your first post."
    ];

}


if ($followerCount >= 5) {

    $badgeList[] = [
        "icon" => "👥",
        "title" => "Popular",
        "text" => "Reached 5 followers."
    ];

}


if ($totalLikes >= 10) {

    $badgeList[] = [
        "icon" => "❤️",
        "title" => "Loved",
        "text" => "Received 10+ likes."
    ];

}


if ($totalComments >= 5) {

    $badgeList[] = [
        "icon" => "💬",
        "title" => "Conversation",
        "text" => "Received 5+ comments."
    ];

}


$badgeList[] = [
    "icon" => "🛡️",
    "title" => "Verified Profile",
    "text" => "ConnectHub member account."
];


$badgeList[] = [
    "icon" => "⚡",
    "title" => "Active",
    "text" => "Your profile is ready to connect."
];


/* ============================================================
   RECENT ACTIVITY
============================================================ */

$activities = [];


if ($postCount > 0) {

    $activities[] = [
        "icon" => "📸",
        "title" => "Created posts",
        "value" =>
            $postCount .
            " published"
    ];

}


$activities[] = [
    "icon" => "👥",
    "title" => "Following",
    "value" =>
        $followingCount .
        " people"
];


$activities[] = [
    "icon" => "✨",
    "title" => "Followers",
    "value" =>
        $followerCount .
        " people"
];


$activities[] = [
    "icon" => "❤️",
    "title" => "Post likes",
    "value" =>
        $totalLikes .
        " total"
];


$activities[] = [
    "icon" => "💬",
    "title" => "Comments",
    "value" =>
        $totalComments .
        " total"
];


/* ============================================================
   SAFE PROFILE LINK
============================================================ */

$profileUrl =
    "profile.php";


/* ============================================================
   BEGIN PAGE
============================================================ */

?>

<div class="advanced-profile-page">


    <!-- ========================================================
         PROFILE HERO
    ========================================================= -->

    <section class="profile-hero">


        <div class="profile-hero-bg"></div>


        <div class="profile-hero-grid"></div>


        <div class="profile-light-orb orb-a"></div>

        <div class="profile-light-orb orb-b"></div>

        <div class="profile-light-orb orb-c"></div>


        <!-- HERO CONTENT -->

        <div class="profile-hero-content">


            <!-- PROFILE IMAGE -->

            <div class="profile-avatar-wrap">


                <div class="profile-avatar-ring"></div>

                <div class="profile-avatar-ring ring-two"></div>


                <?php if (
                    $profileImage !== ""
                ): ?>

                    <img
                        src="<?= e($profileImage) ?>"
                        class="profile-main-avatar"
                        alt="Profile"
                        onerror="
                            this.style.display='none';
                            document.getElementById('profileFallback').style.display='flex';
                        "
                    >

                    <div
                        id="profileFallback"
                        class="profile-avatar-fallback"
                        style="display:none;"
                    >
                        <?= e($initial) ?>
                    </div>

                <?php else: ?>

                    <div
                        class="profile-avatar-fallback"
                    >
                        <?= e($initial) ?>
                    </div>

                <?php endif; ?>


                <div class="profile-online-dot"></div>


            </div>


            <!-- PROFILE DETAILS -->

            <div class="profile-identity">


                <div class="profile-status-pill">

                    <span></span>

                    ONLINE

                </div>


                <div class="profile-small-label">
                    CONNECTHUB IDENTITY
                </div>


                <h1>
                    <?= e($userName) ?>
                </h1>


                <div class="profile-handle">
                    <?= e($handle) ?>
                </div>


                <p class="profile-description">

                    Connecting, sharing and exploring
                    the ConnectHub world.

                </p>


                <div class="profile-meta-row">


                    <span>
                        📅 Member since
                        <?= e($joinDate) ?>
                    </span>


                    <span>
                        🛡️ Protected Profile
                    </span>


                    <span>
                        ⚡ Active User
                    </span>


                </div>


                <!-- ACTION BUTTONS -->

                <div class="profile-action-row">


                    <a
                        href="edit_profile.php"
                        class="profile-primary-button"
                    >
                        ✏️ Edit Profile
                    </a>


                    <a
                        href="chat.php"
                        class="profile-secondary-button"
                    >
                        💬 Messages
                    </a>


                    <button
                        type="button"
                        class="profile-secondary-button"
                        id="shareProfileButton"
                    >
                        🔗 Share Profile
                    </button>


                    <button
                        type="button"
                        class="profile-secondary-button"
                        id="copyProfileButton"
                    >
                        📋 Copy Link
                    </button>


                </div>


            </div>


            <!-- LEVEL -->

            <div class="profile-level-card">


                <div class="level-top">


                    <span>
                        CONNECTHUB LEVEL
                    </span>


                    <strong>
                        LV <?= (int)$level ?>
                    </strong>


                </div>


                <div class="level-number">
                    <?= (int)$levelPoints ?>
                    XP
                </div>


                <div class="level-bar">

                    <span
                        style="
                            width:<?= (int)$levelProgress ?>%;
                        "
                    ></span>

                </div>


                <small>
                    <?= (int)$levelProgress ?>%
                    to next level
                </small>


            </div>


        </div>


    </section>


    <!-- ========================================================
         STATS
    ========================================================= -->

    <section class="profile-stat-grid">


        <div class="profile-stat-card">

            <div class="stat-glow"></div>

            <div class="stat-icon blue-icon">
                📸
            </div>

            <div>

                <span>
                    POSTS
                </span>

                <strong>
                    <?= (int)$postCount ?>
                </strong>

                <small>
                    Published
                </small>

            </div>

        </div>


        <div class="profile-stat-card">

            <div class="stat-glow"></div>

            <div class="stat-icon cyan-icon">
                👥
            </div>

            <div>

                <span>
                    FOLLOWERS
                </span>

                <strong>
                    <?= (int)$followerCount ?>
                </strong>

                <small>
                    Connected with you
                </small>

            </div>

        </div>


        <div class="profile-stat-card">

            <div class="stat-glow"></div>

            <div class="stat-icon violet-icon">
                ➤
            </div>

            <div>

                <span>
                    FOLLOWING
                </span>

                <strong>
                    <?= (int)$followingCount ?>
                </strong>

                <small>
                    People you follow
                </small>

            </div>

        </div>


        <div class="profile-stat-card">

            <div class="stat-glow"></div>

            <div class="stat-icon pink-icon">
                ❤️
            </div>

            <div>

                <span>
                    LIKES
                </span>

                <strong>
                    <?= (int)$totalLikes ?>
                </strong>

                <small>
                    On your posts
                </small>

            </div>

        </div>


    </section>


    <!-- ========================================================
         PROFILE BODY
    ========================================================= -->

    <section class="profile-content-grid">


        <!-- ====================================================
             LEFT
        ===================================================== -->

        <div class="profile-left-column">


            <!-- PROFILE OVERVIEW -->

            <div class="profile-panel overview-panel">


                <div class="panel-heading">


                    <div class="panel-heading-icon">
                        C
                    </div>


                    <div>

                        <span>
                            PROFILE OVERVIEW
                        </span>

                        <h2>
                            Your ConnectHub Identity
                        </h2>

                    </div>


                </div>


                <div class="overview-grid">


                    <div class="overview-item">

                        <span>
                            Display Name
                        </span>

                        <strong>
                            <?= e($userName) ?>
                        </strong>

                    </div>


                    <div class="overview-item">

                        <span>
                            Username
                        </span>

                        <strong>
                            <?= e($handle) ?>
                        </strong>

                    </div>


                    <div class="overview-item">

                        <span>
                            Membership
                        </span>

                        <strong>
                            ConnectHub Member
                        </strong>

                    </div>


                    <div class="overview-item">

                        <span>
                            Account Status
                        </span>

                        <strong class="active-text">
                            ● Active
                        </strong>

                    </div>


                </div>


            </div>


            <!-- ACHIEVEMENTS -->

            <div class="profile-panel">


                <div class="panel-heading">


                    <div class="panel-heading-icon achievement-icon">
                        🏆
                    </div>


                    <div>

                        <span>
                            ACHIEVEMENTS
                        </span>

                        <h2>
                            Your Badges
                        </h2>

                    </div>


                </div>


                <div class="badge-grid">


                    <?php foreach (
                        $badgeList
                        as $badge
                    ): ?>


                        <div class="achievement-card">


                            <div class="achievement-symbol">

                                <?= e(
                                    $badge["icon"]
                                ) ?>

                            </div>


                            <div>

                                <strong>
                                    <?= e(
                                        $badge["title"]
                                    ) ?>
                                </strong>


                                <small>
                                    <?= e(
                                        $badge["text"]
                                    ) ?>
                                </small>

                            </div>


                        </div>


                    <?php endforeach; ?>


                </div>


            </div>


            <!-- ACTIVITY -->

            <div class="profile-panel">


                <div class="panel-heading">


                    <div class="panel-heading-icon activity-icon">
                        ⚡
                    </div>


                    <div>

                        <span>
                            PROFILE ACTIVITY
                        </span>

                        <h2>
                            Your ConnectHub Stats
                        </h2>

                    </div>


                </div>


                <div class="activity-list">


                    <?php foreach (
                        $activities
                        as $activity
                    ): ?>


                        <div class="activity-row">


                            <div class="activity-icon-box">

                                <?= e(
                                    $activity["icon"]
                                ) ?>

                            </div>


                            <div class="activity-copy">

                                <strong>
                                    <?= e(
                                        $activity["title"]
                                    ) ?>
                                </strong>

                                <small>
                                    <?= e(
                                        $activity["value"]
                                    ) ?>
                                </small>

                            </div>


                            <div class="activity-arrow">
                                →
                            </div>


                        </div>


                    <?php endforeach; ?>


                </div>


            </div>


            <!-- PROFILE STRENGTH -->

            <div class="profile-panel strength-panel">


                <div class="strength-heading">


                    <div>

                        <span>
                            PROFILE STRENGTH
                        </span>

                        <h2>
                            Looking Good
                        </h2>

                    </div>


                    <strong>
                        <?= (int)$profileStrength ?>%
                    </strong>


                </div>


                <div class="strength-bar">


                    <span
                        style="
                            width:<?= (int)$profileStrength ?>%;
                        "
                    ></span>


                </div>


                <p>
                    Keep posting and connecting with people
                    to make your ConnectHub profile stronger.
                </p>


            </div>


        </div>


        <!-- ====================================================
             RIGHT
        ===================================================== -->

        <div class="profile-right-column">


            <!-- POST SUMMARY -->

            <div class="profile-panel posts-overview-panel">


                <div class="panel-heading">


                    <div class="panel-heading-icon post-icon">
                        📸
                    </div>


                    <div>

                        <span>
                            CONTENT
                        </span>

                        <h2>
                            My Posts
                        </h2>

                    </div>


                    <div class="panel-count">
                        <?= (int)$postCount ?>
                    </div>


                </div>


                <?php if (
                    count($posts) > 0
                ): ?>


                    <div class="profile-post-grid">


                        <?php foreach (
                            $posts
                            as $index =>
                            $post
                        ): ?>


                            <?php

                            $postImage =
                                trim(
                                    $post["image"] ?? ""
                                );


                            if (
                                $postImage !== ""
                            ) {

                                $postImage =
                                    "uploads/" .
                                    basename(
                                        $postImage
                                    );

                            }


                            $mediaType =
                                strtolower(
                                    trim(
                                        $post["media_type"]
                                        ?? "image"
                                    )
                                );


                            if (
                                $mediaType !== "video"
                            ) {

                                $mediaType =
                                    "image";

                            }

                            ?>


                            <article
                                class="profile-post-tile"
                                data-post-index="<?= (int)$index ?>"
                                data-image="<?= e($postImage) ?>"
                                data-caption="<?= e($post["caption"] ?? "") ?>"
                                data-type="<?= e($mediaType) ?>"
                            >


                                <?php if (
                                    $postImage !== ""
                                ): ?>


                                    <?php if (
                                        $mediaType === "video"
                                    ): ?>

                                        <video
                                            muted
                                            playsinline
                                            preload="metadata"
                                        >

                                            <source
                                                src="<?= e($postImage) ?>"
                                            >

                                        </video>


                                        <div class="post-video-badge">
                                            ▶ VIDEO
                                        </div>


                                    <?php else: ?>


                                        <img
                                            src="<?= e($postImage) ?>"
                                            alt="Post"
                                            loading="lazy"
                                        >


                                    <?php endif; ?>


                                <?php else: ?>


                                    <div class="text-post-tile">

                                        <div>
                                            C
                                        </div>

                                        <span>
                                            Text Post
                                        </span>

                                    </div>


                                <?php endif; ?>


                                <div class="post-tile-overlay">


                                    <span>
                                        <?= !empty($post["caption"])
                                            ? "💬"
                                            : "C"
                                        ?>
                                    </span>


                                </div>


                            </article>


                        <?php endforeach; ?>


                    </div>


                <?php else: ?>


                    <div class="profile-empty-posts">


                        <div class="empty-post-icon">
                            📸
                        </div>


                        <h3>
                            No Posts Yet
                        </h3>


                        <p>
                            Share your first photo or video
                            and start building your ConnectHub profile.
                        </p>


                        <a
                            href="index.php"
                            class="create-first-post"
                        >
                            ➕ Create First Post
                        </a>


                    </div>


                <?php endif; ?>


            </div>


            <!-- QUICK ACTIONS -->

            <div class="profile-panel quick-actions-panel">


                <div class="panel-heading">


                    <div class="panel-heading-icon quick-icon">
                        ⚡
                    </div>


                    <div>

                        <span>
                            QUICK ACTIONS
                        </span>

                        <h2>
                            Manage Profile
                        </h2>

                    </div>

                </div>


                <div class="quick-action-grid">


                    <a
                        href="edit_profile.php"
                        class="quick-action"
                    >

                        <div>
                            ✏️
                        </div>

                        <span>
                            Edit Profile
                        </span>

                        <small>
                            Update information
                        </small>

                    </a>


                    <a
                        href="users.php"
                        class="quick-action"
                    >

                        <div>
                            👥
                        </div>

                        <span>
                            Find People
                        </span>

                        <small>
                            Discover users
                        </small>

                    </a>


                    <a
                        href="chat.php"
                        class="quick-action"
                    >

                        <div>
                            💬
                        </div>

                        <span>
                            Messages
                        </span>

                        <small>
                            Talk with people
                        </small>

                    </a>


                    <a
                        href="index.php"
                        class="quick-action"
                    >

                        <div>
                            📤
                        </div>

                        <span>
                            Create Post
                        </span>

                        <small>
                            Share something
                        </small>

                    </a>


                </div>


            </div>


            <!-- PROFILE CODE -->

            <div class="profile-panel identity-code-panel">


                <div class="identity-code">


                    <div class="code-symbol">
                        C
                    </div>


                    <div>

                        <span>
                            CONNECTHUB MEMBER
                        </span>

                        <strong>
                            #<?= str_pad(
                                (string)$uid,
                                5,
                                "0",
                                STR_PAD_LEFT
                            ) ?>
                        </strong>

                    </div>


                </div>


                <div class="identity-line"></div>


                <div class="identity-footer">

                    <span>
                        <?= e($handle) ?>
                    </span>

                    <span>
                        VERIFIED
                    </span>

                </div>


            </div>


        </div>


    </section>


</div>


<!-- ============================================================
     FULL SCREEN POST VIEWER
============================================================ -->

<div
    id="profilePostModal"
    class="profile-post-modal"
    aria-hidden="true"
>


    <div
        class="profile-modal-backdrop"
        id="profileModalBackdrop"
    ></div>


    <div class="profile-modal-content">


        <button
            type="button"
            class="profile-modal-close"
            id="profileModalClose"
            aria-label="Close"
        >
            ×
        </button>


        <div
            id="profileModalMedia"
            class="profile-modal-media"
        ></div>


        <div class="profile-modal-caption">


            <div class="modal-author">


                <div class="modal-author-avatar">

                    <?php if (
                        $profileImage !== ""
                    ): ?>

                        <img
                            src="<?= e($profileImage) ?>"
                            alt="Profile"
                        >

                    <?php else: ?>

                        <?= e($initial) ?>

                    <?php endif; ?>

                </div>


                <div>

                    <strong>
                        <?= e($userName) ?>
                    </strong>

                    <small>
                        <?= e($handle) ?>
                    </small>

                </div>


            </div>


            <div
                id="profileModalText"
                class="modal-post-text"
            ></div>


        </div>


    </div>


</div>


<style>

/* ============================================================
   ADVANCED PROFILE PAGE
============================================================ */

.advanced-profile-page {

    width: 100%;

    max-width: 1220px;

    margin: 0 auto;

    padding:
        24px
        20px
        80px;

}


/* ============================================================
   HERO
============================================================ */

.profile-hero {

    position: relative;

    min-height: 390px;

    overflow: hidden;

    border-radius: 30px;

    border:
        1px solid
        rgba(
            147,
            197,
            253,
            .20
        );

    background:
        linear-gradient(
            135deg,
            rgba(
                2,
                6,
                23,
                .93
            ),
            rgba(
                15,
                23,
                42,
                .90
            ),
            rgba(
                30,
                41,
                99,
                .90
            )
        );

    box-shadow:
        0 25px 80px
        rgba(
            0,
            0,
            0,
            .38
        );

    isolation: isolate;

}


/* ============================================================
   HERO BACKGROUND IMAGE
============================================================ */

.profile-hero-bg {

    position:
        absolute;

    inset:
        0;

    z-index:
        -5;

    background:
        url("uploads/profile-bg.jpg")
        center/
        cover
        no-repeat;

    opacity:
        .22;

    filter:
        saturate(
            1.3
        )
        contrast(
            1.05
        );

    animation:
        profileBackgroundFloat
        14s
        ease-in-out
        infinite
        alternate;

}


@keyframes profileBackgroundFloat {

    0% {

        transform:
            scale(
                1
            );

    }

    100% {

        transform:
            scale(
                1.08
            );

    }

}


/* ============================================================
   HERO GRID
============================================================ */

.profile-hero-grid {

    position:
        absolute;

    inset:
        0;

    z-index:
        -4;

    opacity:
        .18;

    background-image:

        linear-gradient(
            rgba(
                96,
                165,
                250,
                .13
            ) 1px,
            transparent 1px
        ),

        linear-gradient(
            90deg,
            rgba(
                96,
                165,
                250,
                .13
            ) 1px,
            transparent 1px
        );

    background-size:
        40px
        40px;

    mask-image:
        linear-gradient(
            to bottom,
            black,
            transparent
        );

}


/* ============================================================
   HERO ORBS
============================================================ */

.profile-light-orb {

    position:
        absolute;

    border-radius:
        50%;

    pointer-events:
        none;

    filter:
        blur(
            14px
        );

    z-index:
        -2;

}


.orb-a {

    width:
        360px;

    height:
        360px;

    top:
        -170px;

    right:
        5%;

    background:
        rgba(
            37,
            99,
            235,
            .22
        );

    animation:
        profileOrbA
        8s
        ease-in-out
        infinite
        alternate;

}


.orb-b {

    width:
        300px;

    height:
        300px;

    bottom:
        -170px;

    left:
        25%;

    background:
        rgba(
            124,
            58,
            237,
            .19
        );

    animation:
        profileOrbB
        10s
        ease-in-out
        infinite
        alternate;

}


.orb-c {

    width:
        210px;

    height:
        210px;

    left:
        -100px;

    top:
        45%;

    background:
        rgba(
            34,
            211,
            238,
            .13
        );

    animation:
        profileOrbC
        9s
        ease-in-out
        infinite
        alternate;

}


@keyframes profileOrbA {

    to {

        transform:
            translate(
                -100px,
                90px
            )
            scale(
                1.15
            );

    }

}


@keyframes profileOrbB {

    to {

        transform:
            translate(
                100px,
                -80px
            )
            scale(
                1.17
            );

    }

}


@keyframes profileOrbC {

    to {

        transform:
            translate(
                90px,
                -30px
            );

    }

}


/* ============================================================
   HERO CONTENT
============================================================ */

.profile-hero-content {

    position:
        relative;

    min-height:
        390px;

    padding:
        46px 44px;

    display:
        grid;

    grid-template-columns:
        190px
        minmax(0,1fr)
        190px;

    align-items:
        center;

    gap:
        34px;

}


/* ============================================================
   PROFILE AVATAR
============================================================ */

.profile-avatar-wrap {

    position:
        relative;

    width:
        170px;

    height:
        170px;

    display:
        grid;

    place-items:
        center;

    margin:
        0 auto;

}


.profile-avatar-ring {

    position:
        absolute;

    inset:
        -9px;

    border:
        2px solid
        rgba(
            96,
            165,
            250,
            .70
        );

    border-radius:
        50%;

    box-shadow:
        0 0 25px
        rgba(
            59,
            130,
            246,
            .30
        );

    animation:
        avatarRingPulse
        2.7s
        ease-in-out
        infinite;

}


.profile-avatar-ring.ring-two {

    inset:
        -18px;

    border:
        1px solid
        rgba(
            124,
            58,
            237,
            .35
        );

    animation:
        avatarRingPulseTwo
        3.4s
        ease-in-out
        infinite;

}


@keyframes avatarRingPulse {

    0%,
    100% {

        transform:
            scale(
                1
            );

        opacity:
            .65;

    }

    50% {

        transform:
            scale(
                1.07
            );

        opacity:
            1;

    }

}


@keyframes avatarRingPulseTwo {

    0%,
    100% {

        transform:
            scale(
                .96
            );

        opacity:
            .25;

    }

    50% {

        transform:
            scale(
                1.08
            );

        opacity:
            .85;

    }

}


.profile-main-avatar,
.profile-avatar-fallback {

    position:
        relative;

    width:
        150px;

    height:
        150px;

    border:
        4px solid
        rgba(
            255,
            255,
            255,
            .80
        );

    border-radius:
        50%;

    object-fit:
        cover;

    box-shadow:
        0 0 35px
        rgba(
            59,
            130,
            246,
            .32
        );

    z-index:
        2;

}


.profile-avatar-fallback {

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
            #4f46e5,
            #7c3aed
        );

    font-size:
        52px;

    font-weight:
        950;

}


/* ============================================================
   ONLINE DOT
============================================================ */

.profile-online-dot {

    position:
        absolute;

    right:
        4px;

    bottom:
        8px;

    width:
        22px;

    height:
        22px;

    border:
        4px solid
        #081125;

    border-radius:
        50%;

    background:
        #22c55e;

    z-index:
        5;

    box-shadow:
        0 0 20px
        rgba(
            34,
            197,
            94,
            .85
        );

    animation:
        profileOnlinePulse
        1.7s
        ease-in-out
        infinite;

}


@keyframes profileOnlinePulse {

    50% {

        transform:
            scale(
                1.18
            );

        box-shadow:
            0 0 28px
            rgba(
                34,
                197,
                94,
                1
            );

    }

}


/* ============================================================
   IDENTITY
============================================================ */

.profile-identity {

    min-width:
        0;

}


.profile-status-pill {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    margin-bottom:
        10px;

    padding:
        6px
        10px;

    border:
        1px solid
        rgba(
            34,
            197,
            94,
            .22
        );

    border-radius:
        999px;

    color:
        #bbf7d0;

    background:
        rgba(
            22,
            101,
            52,
            .13
        );

    font-size:
        8px;

    font-weight:
        950;

    letter-spacing:
        1px;

}


.profile-status-pill span {

    width:
        6px;

    height:
        6px;

    border-radius:
        50%;

    background:
        #22c55e;

    box-shadow:
        0 0 11px
        rgba(
            34,
            197,
            94,
            .95
        );

}


.profile-small-label {

    color:
        #60a5fa;

    font-size:
        8px;

    font-weight:
        950;

    letter-spacing:
        2px;

}


.profile-identity h1 {

    margin:
        5px 0 0;

    color:
        #ffffff;

    font-size:
        clamp(
            34px,
            5vw,
            58px
        );

    line-height:
        1;

    letter-spacing:
        -1.5px;

}


.profile-handle {

    margin-top:
        8px;

    color:
        #a5b4fc;

    font-size:
        13px;

    font-weight:
        800;

}


.profile-description {

    max-width:
        630px;

    margin:
        14px 0 13px;

    color:
        #b7c2d4;

    font-size:
        12px;

    line-height:
        1.7;

}


.profile-meta-row {

    display:
        flex;

    flex-wrap:
        wrap;

    gap:
        8px;

}


.profile-meta-row span {

    padding:
        7px
        9px;

    border:
        1px solid
        rgba(
            148,
            163,
            184,
            .11
        );

    border-radius:
        9px;

    color:
        #94a3b8;

    background:
        rgba(
            15,
            23,
            42,
            .38
        );

    font-size:
        8px;

    font-weight:
        800;

}


/* ============================================================
   BUTTONS
============================================================ */

.profile-action-row {

    display:
        flex;

    flex-wrap:
        wrap;

    gap:
        8px;

    margin-top:
        20px;

}


.profile-primary-button,
.profile-secondary-button {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    min-height:
        38px;

    padding:
        9px 13px;

    border-radius:
        10px;

    font-size:
        9px;

    font-weight:
        950;

    text-decoration:
        none;

    cursor:
        pointer;

}


.profile-primary-button {

    color:
        white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    border:
        1px solid
        rgba(
            147,
            197,
            253,
            .25
        );

    box-shadow:
        0 10px 25px
        rgba(
            37,
            99,
            235,
            .24
        );

}


.profile-secondary-button {

    color:
        #cbd5e1;

    background:
        rgba(
            15,
            23,
            42,
            .48
        );

    border:
        1px solid
        rgba(
            148,
            163,
            184,
            .13
        );

}


.profile-primary-button:hover,
.profile-secondary-button:hover {

    transform:
        translateY(
            -2px
        );

}


/* ============================================================
   LEVEL CARD
============================================================ */

.profile-level-card {

    padding:
        17px;

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .16
        );

    border-radius:
        18px;

    background:
        rgba(
            2,
            6,
            23,
            .46
        );

    box-shadow:
        inset
        0 1px 0
        rgba(
            255,
            255,
            255,
            .04
        );

}


.level-top {

    display:
        flex;

    justify-content:
        space-between;

    gap:
        10px;

}


.level-top span {

    color:
        #64748b;

    font-size:
        7px;

    font-weight:
        950;

    letter-spacing:
        1.4px;

}


.level-top strong {

    color:
        #93c5fd;

    font-size:
        10px;

}


.level-number {

    margin:
        13px 0 10px;

    color:
        #ffffff;

    font-size:
        25px;

    font-weight:
        950;

}


.level-bar {

    width:
        100%;

    height:
        6px;

    overflow:
        hidden;

    border-radius:
        999px;

    background:
        rgba(
            255,
            255,
            255,
            .08
        );

}


.level-bar span {

    display:
        block;

    height:
        100%;

    border-radius:
        inherit;

    background:
        linear-gradient(
            90deg,
            #2563eb,
            #6366f1,
            #22d3ee
        );

    box-shadow:
        0 0 14px
        rgba(
            59,
            130,
            246,
            .55
        );

}


.profile-level-card small {

    display:
        block;

    margin-top:
        7px;

    color:
        #64748b;

    font-size:
        7px;

}


/* ============================================================
   STATS
============================================================ */

.profile-stat-grid {

    display:
        grid;

    grid-template-columns:
        repeat(
            4,
            1fr
        );

    gap:
        11px;

    margin:
        14px 0;

}


.profile-stat-card {

    position:
        relative;

    min-height:
        97px;

    display:
        flex;

    align-items:
        center;

    gap:
        11px;

    padding:
        15px;

    overflow:
        hidden;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .12
        );

    border-radius:
        17px;

    background:
        rgba(
            255,
            255,
            255,
            .09
        );

    backdrop-filter:
        blur(
            12px
        );

    -webkit-backdrop-filter:
        blur(
            12px
        );

    box-shadow:
        0 10px 30px
        rgba(
            0,
            0,
            0,
            .12
        );

    transition:
        .25s
        ease;

}


.profile-stat-card:hover {

    transform:
        translateY(
            -4px
        );

    border-color:
        rgba(
            96,
            165,
            250,
            .30
        );

}


.stat-glow {

    position:
        absolute;

    width:
        90px;

    height:
        90px;

    border-radius:
        50%;

    right:
        -45px;

    top:
        -45px;

    background:
        rgba(
            59,
            130,
            246,
            .16
        );

    filter:
        blur(
            9px
        );

}


.stat-icon {

    position:
        relative;

    width:
        43px;

    height:
        43px;

    flex:
        0 0 43px;

    display:
        grid;

    place-items:
        center;

    border-radius:
        12px;

    font-size:
        18px;

}


.blue-icon {

    background:
        rgba(
            37,
            99,
            235,
            .18
        );

}


.cyan-icon {

    background:
        rgba(
            6,
            182,
            212,
            .17
        );

}


.violet-icon {

    background:
        rgba(
            124,
            58,
            237,
            .17
        );

}


.pink-icon {

    background:
        rgba(
            236,
            72,
            153,
            .16
        );

}


.profile-stat-card span {

    display:
        block;

    color:
        #64748b;

    font-size:
        7px;

    font-weight:
        950;

    letter-spacing:
        1.2px;

}


.profile-stat-card strong {

    display:
        block;

    margin-top:
        4px;

    color:
        #f8fafc;

    font-size:
        22px;

}


.profile-stat-card small {

    display:
        block;

    margin-top:
        3px;

    color:
        #64748b;

    font-size:
        7px;

}


/* ============================================================
   BODY GRID
============================================================ */

.profile-content-grid {

    display:
        grid;

    grid-template-columns:
        minmax(
            0,
            1fr
        )
        minmax(
            0,
            1.1fr
        );

    gap:
        14px;

    align-items:
        start;

}


.profile-left-column,
.profile-right-column {

    min-width:
        0;

    display:
        flex;

    flex-direction:
        column;

    gap:
        14px;

}


/* ============================================================
   PANELS
============================================================ */

.profile-panel {

    position:
        relative;

    overflow:
        hidden;

    padding:
        18px;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .13
        );

    border-radius:
        20px;

    background:
        rgba(
            255,
            255,
            255,
            .09
        );

    backdrop-filter:
        blur(
            13px
        );

    -webkit-backdrop-filter:
        blur(
            13px
        );

    box-shadow:
        0 14px 40px
        rgba(
            0,
            0,
            0,
            .13
        );

}


.profile-panel::after {

    content:
        "";

    position:
        absolute;

    left:
        -40%;

    top:
        0;

    width:
        35%;

    height:
        1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(
                96,
                165,
                250,
                .9
            ),
            transparent
        );

    animation:
        panelScan
        5.5s
        ease-in-out
        infinite;

}


@keyframes panelScan {

    0% {

        left:
            -40%;

        opacity:
            0;

    }

    20% {

        opacity:
            1;

    }

    80% {

        opacity:
            1;

    }

    100% {

        left:
            115%;

        opacity:
            0;

    }

}


/* ============================================================
   PANEL HEADING
============================================================ */

.panel-heading {

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    margin-bottom:
        15px;

}


.panel-heading-icon {

    width:
        41px;

    height:
        41px;

    flex:
        0 0 41px;

    display:
        grid;

    place-items:
        center;

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .18
        );

    border-radius:
        11px;

    color:
        #bfdbfe;

    background:
        linear-gradient(
            135deg,
            rgba(
                37,
                99,
                235,
                .20
            ),
            rgba(
                79,
                70,
                229,
                .12
            )
        );

    font-size:
        17px;

}


.panel-heading-icon.achievement-icon {

    background:
        rgba(
            245,
            158,
            11,
            .12
        );

}


.panel-heading-icon.activity-icon {

    background:
        rgba(
            34,
            211,
            238,
            .12
        );

}


.panel-heading-icon.post-icon {

    background:
        rgba(
            59,
            130,
            246,
            .14
        );

}


.panel-heading-icon.quick-icon {

    background:
        rgba(
            124,
            58,
            237,
            .14
        );

}


.panel-heading > div:not(.panel-heading-icon) {

    min-width:
        0;

}


.panel-heading span {

    display:
        block;

    color:
        #60a5fa;

    font-size:
        7px;

    font-weight:
        950;

    letter-spacing:
        1.5px;

}


.panel-heading h2 {

    margin:
        4px 0 0;

    color:
        #f8fafc;

    font-size:
        17px;

}


.panel-count {

    margin-left:
        auto;

    padding:
        6px 9px;

    color:
        #bfdbfe;

    background:
        rgba(
            37,
            99,
            235,
            .12
        );

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .14
        );

    border-radius:
        999px;

    font-size:
        9px;

    font-weight:
        950;

}


/* ============================================================
   OVERVIEW
============================================================ */

.overview-grid {

    display:
        grid;

    grid-template-columns:
        1fr
        1fr;

    gap:
        9px;

}


.overview-item {

    padding:
        12px;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .08
        );

    border-radius:
        13px;

    background:
        rgba(
            15,
            23,
            42,
            .35
        );

}


.overview-item span {

    display:
        block;

    color:
        #64748b;

    font-size:
        7px;

    font-weight:
        850;

}


.overview-item strong {

    display:
        block;

    margin-top:
        5px;

    color:
        #e2e8f0;

    font-size:
        10px;

    word-break:
        break-word;

}


.active-text {

    color:
        #4ade80 !important;

}


/* ============================================================
   BADGES
============================================================ */

.badge-grid {

    display:
        grid;

    grid-template-columns:
        1fr
        1fr;

    gap:
        9px;

}


.achievement-card {

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

    padding:
        11px;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .08
        );

    border-radius:
        13px;

    background:
        rgba(
            15,
            23,
            42,
            .32
        );

    transition:
        .22s
        ease;

}


.achievement-card:hover {

    transform:
        translateY(
            -2px
        );

    border-color:
        rgba(
            96,
            165,
            250,
            .22
        );

}


.achievement-symbol {

    width:
        37px;

    height:
        37px;

    flex:
        0 0 37px;

    display:
        grid;

    place-items:
        center;

    border-radius:
        10px;

    background:
        rgba(
            255,
            255,
            255,
            .06
        );

    font-size:
        17px;

}


.achievement-card strong {

    display:
        block;

    color:
        #e2e8f0;

    font-size:
        9px;

}


.achievement-card small {

    display:
        block;

    margin-top:
        3px;

    color:
        #64748b;

    font-size:
        7px;

    line-height:
        1.45;

}


/* ============================================================
   ACTIVITY
============================================================ */

.activity-list {

    display:
        flex;

    flex-direction:
        column;

    gap:
        7px;

}


.activity-row {

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

    padding:
        10px;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .07
        );

    border-radius:
        12px;

    background:
        rgba(
            15,
            23,
            42,
            .28
        );

}


.activity-icon-box {

    width:
        35px;

    height:
        35px;

    display:
        grid;

    place-items:
        center;

    flex:
        0 0 35px;

    border-radius:
        9px;

    background:
        rgba(
            59,
            130,
            246,
            .10
        );

}


.activity-copy {

    min-width:
        0;

    flex:
        1;

}


.activity-copy strong {

    display:
        block;

    color:
        #e2e8f0;

    font-size:
        9px;

}


.activity-copy small {

    display:
        block;

    margin-top:
        2px;

    color:
        #64748b;

    font-size:
        7px;

}


.activity-arrow {

    color:
        #60a5fa;

    font-size:
        14px;

}


/* ============================================================
   PROFILE STRENGTH
============================================================ */

.strength-heading {

    display:
        flex;

    align-items:
        flex-end;

    justify-content:
        space-between;

    gap:
        10px;

}


.strength-heading span {

    color:
        #60a5fa;

    font-size:
        7px;

    font-weight:
        950;

    letter-spacing:
        1.5px;

}


.strength-heading h2 {

    margin:
        5px 0 0;

    color:
        #f8fafc;

    font-size:
        18px;

}


.strength-heading > strong {

    color:
        #60a5fa;

    font-size:
        25px;

}


.strength-bar {

    width:
        100%;

    height:
        8px;

    margin:
        14px 0 9px;

    overflow:
        hidden;

    border-radius:
        999px;

    background:
        rgba(
            255,
            255,
            255,
            .07
        );

}


.strength-bar span {

    display:
        block;

    height:
        100%;

    border-radius:
        inherit;

    background:
        linear-gradient(
            90deg,
            #2563eb,
            #6366f1,
            #22d3ee
        );

    box-shadow:
        0 0 18px
        rgba(
            59,
            130,
            246,
            .46
        );

}


.strength-panel p {

    margin:
        0;

    color:
        #64748b;

    font-size:
        8px;

    line-height:
        1.6;

}


/* ============================================================
   POST GRID
============================================================ */

.profile-post-grid {

    display:
        grid;

    grid-template-columns:
        repeat(
            3,
            1fr
        );

    gap:
        7px;

}


.profile-post-tile {

    position:
        relative;

    aspect-ratio:
        1 / 1;

    overflow:
        hidden;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .08
        );

    border-radius:
        12px;

    background:
        rgba(
            2,
            6,
            23,
            .55
        );

    cursor:
        pointer;

}


.profile-post-tile img,
.profile-post-tile video {

    width:
        100%;

    height:
        100%;

    display:
        block;

    object-fit:
        cover;

    transition:
        transform .45s
        ease,
        filter .45s
        ease;

}


.profile-post-tile:hover img,
.profile-post-tile:hover video {

    transform:
        scale(
            1.07
        );

    filter:
        brightness(
            .72
        );

}


.post-tile-overlay {

    position:
        absolute;

    inset:
        0;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    color:
        white;

    font-size:
        18px;

    opacity:
        0;

    background:
        rgba(
            2,
            6,
            23,
            .25
        );

    transition:
        .25s
        ease;

}


.profile-post-tile:hover
.post-tile-overlay {

    opacity:
        1;

}


.post-video-badge {

    position:
        absolute;

    left:
        7px;

    bottom:
        7px;

    padding:
        4px
        6px;

    border-radius:
        999px;

    color:
        white;

    background:
        rgba(
            2,
            6,
            23,
            .72
        );

    font-size:
        6px;

    font-weight:
        950;

}


.text-post-tile {

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

    gap:
        8px;

    color:
        #94a3b8;

    background:
        linear-gradient(
            135deg,
            rgba(
                37,
                99,
                235,
                .12
            ),
            rgba(
                124,
                58,
                237,
                .12
            )
        );

}


.text-post-tile div {

    width:
        43px;

    height:
        43px;

    display:
        grid;

    place-items:
        center;

    border-radius:
        13px;

    color:
        white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    font-size:
        17px;

    font-weight:
        950;

}


.text-post-tile span {

    font-size:
        7px;

    font-weight:
        850;

}


/* ============================================================
   EMPTY POSTS
============================================================ */

.profile-empty-posts {

    padding:
        55px
        20px;

    text-align:
        center;

    border:
        1px dashed
        rgba(
            148,
            163,
            184,
            .17
        );

    border-radius:
        16px;

    background:
        rgba(
            2,
            6,
            23,
            .18
        );

}


.empty-post-icon {

    font-size:
        48px;

    margin-bottom:
        10px;

}


.profile-empty-posts h3 {

    margin:
        0 0 7px;

    color:
        #e2e8f0;

    font-size:
        18px;

}


.profile-empty-posts p {

    max-width:
        430px;

    margin:
        0 auto 18px;

    color:
        #64748b;

    font-size:
        9px;

    line-height:
        1.7;

}


.create-first-post {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        10px
        14px;

    border-radius:
        10px;

    color:
        white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    text-decoration:
        none;

    font-size:
        9px;

    font-weight:
        900;

}


/* ============================================================
   QUICK ACTIONS
============================================================ */

.quick-action-grid {

    display:
        grid;

    grid-template-columns:
        1fr
        1fr;

    gap:
        8px;

}


.quick-action {

    padding:
        12px;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .08
        );

    border-radius:
        13px;

    color:
        inherit;

    text-decoration:
        none;

    background:
        rgba(
            15,
            23,
            42,
            .30
        );

    transition:
        .23s
        ease;

}


.quick-action:hover {

    transform:
        translateY(
            -3px
        );

    border-color:
        rgba(
            96,
            165,
            250,
            .24
        );

    background:
        rgba(
            37,
            99,
            235,
            .09
        );

}


.quick-action > div {

    width:
        37px;

    height:
        37px;

    display:
        grid;

    place-items:
        center;

    border-radius:
        10px;

    background:
        rgba(
            59,
            130,
            246,
            .11
        );

    font-size:
        16px;

}


.quick-action > span {

    display:
        block;

    margin-top:
        8px;

    color:
        #e2e8f0;

    font-size:
        9px;

    font-weight:
        900;

}


.quick-action > small {

    display:
        block;

    margin-top:
        3px;

    color:
        #64748b;

    font-size:
        7px;

}


/* ============================================================
   IDENTITY CODE
============================================================ */

.identity-code-panel {

    padding:
        17px;

    background:
        linear-gradient(
            135deg,
            rgba(
                15,
                23,
                42,
                .62
            ),
            rgba(
                30,
                41,
                99,
                .45
            )
        );

}


.identity-code {

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

}


.code-symbol {

    width:
        48px;

    height:
        48px;

    display:
        grid;

    place-items:
        center;

    border:
        1px solid
        rgba(
            147,
            197,
            253,
            .24
        );

    border-radius:
        14px;

    color:
        white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5,
            #7c3aed
        );

    font-size:
        18px;

    font-weight:
        950;

}


.identity-code span {

    display:
        block;

    color:
        #64748b;

    font-size:
        7px;

    font-weight:
        950;

    letter-spacing:
        1.4px;

}


.identity-code strong {

    display:
        block;

    margin-top:
        4px;

    color:
        #bfdbfe;

    font-size:
        14px;

    letter-spacing:
        2px;

}


.identity-line {

    height:
        1px;

    margin:
        13px 0;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(
                96,
                165,
                250,
                .25
            ),
            transparent
        );

}


.identity-footer {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        10px;

}


.identity-footer span {

    color:
        #64748b;

    font-size:
        7px;

    font-weight:
        850;

}


.identity-footer span:last-child {

    color:
        #4ade80;

}


/* ============================================================
   POST MODAL
============================================================ */

.profile-post-modal {

    position:
        fixed;

    inset:
        0;

    z-index:
        90000;

    display:
        none;

    align-items:
        center;

    justify-content:
        center;

    padding:
        20px;

}


.profile-post-modal.open {

    display:
        flex;

}


.profile-modal-backdrop {

    position:
        absolute;

    inset:
        0;

    background:
        rgba(
            2,
            6,
            23,
            .82
        );

    backdrop-filter:
        blur(
            12px
        );

    -webkit-backdrop-filter:
        blur(
            12px
        );

}


.profile-modal-content {

    position:
        relative;

    z-index:
        2;

    width:
        min(
            900px,
            100%
        );

    max-height:
        92vh;

    overflow:
        hidden;

    border:
        1px solid
        rgba(
            147,
            197,
            253,
            .20
        );

    border-radius:
        20px;

    background:
        rgba(
            2,
            6,
            23,
            .93
        );

    box-shadow:
        0 35px 100px
        rgba(
            0,
            0,
            0,
            .55
        );

}


.profile-modal-close {

    position:
        absolute;

    right:
        12px;

    top:
        12px;

    z-index:
        10;

    width:
        38px;

    height:
        38px;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .12
        );

    border-radius:
        50%;

    color:
        white;

    background:
        rgba(
            2,
            6,
            23,
            .68
        );

    font-size:
        25px;

    line-height:
        1;

    cursor:
        pointer;

}


.profile-modal-media {

    min-height:
        350px;

    max-height:
        67vh;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        #020617;

}


.profile-modal-media img,
.profile-modal-media video {

    max-width:
        100%;

    max-height:
        67vh;

    display:
        block;

    object-fit:
        contain;

}


.modal-post-text {

    margin-top:
        12px;

    color:
        #cbd5e1;

    font-size:
        10px;

    line-height:
        1.6;

    white-space:
        pre-wrap;

}


.profile-modal-caption {

    padding:
        15px 17px 18px;

}


.modal-author {

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

}


.modal-author-avatar {

    width:
        38px;

    height:
        38px;

    display:
        grid;

    place-items:
        center;

    overflow:
        hidden;

    border:
        2px solid
        rgba(
            96,
            165,
            250,
            .35
        );

    border-radius:
        50%;

    color:
        white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    font-size:
        12px;

    font-weight:
        900;

}


.modal-author-avatar img {

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;

}


.modal-author strong {

    display:
        block;

    color:
        #f8fafc;

    font-size:
        10px;

}


.modal-author small {

    display:
        block;

    margin-top:
        2px;

    color:
        #64748b;

    font-size:
        7px;

}


/* ============================================================
   MOBILE
============================================================ */

@media (max-width:950px) {

    .profile-hero-content {

        grid-template-columns:
            150px
            minmax(0,1fr);

        padding:
            35px;

    }


    .profile-level-card {

        grid-column:
            1 / -1;

    }


}


@media (max-width:800px) {

    .advanced-profile-page {

        padding:
            14px
            10px
            60px;

    }


    .profile-hero {

        border-radius:
            23px;

    }


    .profile-hero-content {

        grid-template-columns:
            1fr;

        text-align:
            center;

        gap:
            20px;

        padding:
            34px 18px;

    }


    .profile-avatar-wrap {

        width:
            145px;

        height:
            145px;

    }


    .profile-main-avatar,
    .profile-avatar-fallback {

        width:
            125px;

        height:
            125px;

    }


    .profile-identity {

        display:
            flex;

        flex-direction:
            column;

        align-items:
            center;

    }


    .profile-meta-row {

        justify-content:
            center;

    }


    .profile-action-row {

        justify-content:
            center;

    }


    .profile-stat-grid {

        grid-template-columns:
            1fr
            1fr;

    }


    .profile-content-grid {

        grid-template-columns:
            1fr;

    }


}


@media (max-width:520px) {

    .profile-stat-grid {

        grid-template-columns:
            1fr;

    }


    .overview-grid,
    .badge-grid,
    .quick-action-grid {

        grid-template-columns:
            1fr;

    }


    .profile-post-grid {

        grid-template-columns:
            repeat(
                2,
                1fr
            );

    }


    .profile-identity h1 {

        font-size:
            34px;

    }


    .profile-action-row a,
    .profile-action-row button {

        width:
            100%;

    }


    .profile-meta-row {

        flex-direction:
            column;

        width:
            100%;

    }


    .profile-meta-row span {

        width:
            100%;

    }


    .profile-modal-media {

        min-height:
            250px;

    }

}

</style>


<script>

/* ============================================================
   PROFILE SHARE
============================================================ */

(function () {

    const shareButton =
        document.getElementById(
            "shareProfileButton"
        );


    const copyButton =
        document.getElementById(
            "copyProfileButton"
        );


    const profileUrl =
        window.location.origin +
        window.location.pathname;


    if (shareButton) {

        shareButton.addEventListener(
            "click",
            async function () {

                const shareData = {

                    title:
                        "ConnectHub Profile",

                    text:
                        "Check out <?= e($userName) ?> on ConnectHub.",

                    url:
                        profileUrl

                };


                try {

                    if (
                        navigator.share
                    ) {

                        await navigator.share(
                            shareData
                        );

                    } else {

                        await navigator.clipboard.writeText(
                            profileUrl
                        );


                        shareButton.textContent =
                            "✅ Link Copied";

                        setTimeout(
                            function () {

                                shareButton.textContent =
                                    "🔗 Share Profile";

                            },
                            1800
                        );

                    }

                } catch (error) {

                    // User cancelled share.

                }

            }
        );

    }


    if (copyButton) {

        copyButton.addEventListener(
            "click",
            async function () {

                try {

                    await navigator.clipboard.writeText(
                        profileUrl
                    );


                    copyButton.textContent =
                        "✅ Copied";


                    setTimeout(
                        function () {

                            copyButton.textContent =
                                "📋 Copy Link";

                        },
                        1600
                    );

                } catch (error) {

                    const textArea =
                        document.createElement(
                            "textarea"
                        );


                    textArea.value =
                        profileUrl;


                    document.body.appendChild(
                        textArea
                    );


                    textArea.select();

                    document.execCommand(
                        "copy"
                    );


                    textArea.remove();


                    copyButton.textContent =
                        "✅ Copied";


                    setTimeout(
                        function () {

                            copyButton.textContent =
                                "📋 Copy Link";

                        },
                        1600
                    );

                }

            }
        );

    }

})();


/* ============================================================
   POST VIEWER
============================================================ */

(function () {

    const modal =
        document.getElementById(
            "profilePostModal"
        );


    const modalMedia =
        document.getElementById(
            "profileModalMedia"
        );


    const modalText =
        document.getElementById(
            "profileModalText"
        );


    const closeButton =
        document.getElementById(
            "profileModalClose"
        );


    const backdrop =
        document.getElementById(
            "profileModalBackdrop"
        );


    const postTiles =
        document.querySelectorAll(
            ".profile-post-tile"
        );


    function closeModal() {

        if (!modal) {
            return;
        }


        modal.classList.remove(
            "open"
        );


        modal.setAttribute(
            "aria-hidden",
            "true"
        );


        modalMedia.innerHTML =
            "";


        modalText.textContent =
            "";


        document.body.style.overflow =
            "";

    }


    function openModal(
        tile
    ) {

        if (!modal) {
            return;
        }


        const image =
            tile.getAttribute(
                "data-image"
            ) || "";


        const caption =
            tile.getAttribute(
                "data-caption"
            ) || "";


        const type =
            tile.getAttribute(
                "data-type"
            ) || "image";


        modalMedia.innerHTML =
            "";


        if (image !== "") {


            if (
                type ===
                "video"
            ) {

                const video =
                    document.createElement(
                        "video"
                    );


                video.controls =
                    true;

                video.autoplay =
                    true;

                video.playsInline =
                    true;

                video.src =
                    image;


                modalMedia.appendChild(
                    video
                );


            } else {

                const img =
                    document.createElement(
                        "img"
                    );


                img.src =
                    image;


                img.alt =
                    "Post";


                modalMedia.appendChild(
                    img
                );

            }

        } else {

            modalMedia.innerHTML =
                `
                <div style="
                    color:#94a3b8;
                    text-align:center;
                    font-size:60px;
                ">
                    C
                </div>
                `;

        }


        modalText.textContent =
            caption;


        modal.classList.add(
            "open"
        );


        modal.setAttribute(
            "aria-hidden",
            "false"
        );


        document.body.style.overflow =
            "hidden";

    }


    postTiles.forEach(
        function (tile) {

            tile.addEventListener(
                "click",
                function () {

                    openModal(
                        tile
                    );

                }
            );

        }
    );


    if (closeButton) {

        closeButton.addEventListener(
            "click",
            closeModal
        );

    }


    if (backdrop) {

        backdrop.addEventListener(
            "click",
            closeModal
        );

    }


    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key ===
                "Escape"
            ) {

                closeModal();

            }

        }
    );

})();


/* ============================================================
   PROFILE CARD PARALLAX
============================================================ */

(function () {

    const hero =
        document.querySelector(
            ".profile-hero"
        );


    if (!hero) {
        return;
    }


    const finePointer =
        window.matchMedia(
            "(pointer:fine)"
        ).matches;


    if (!finePointer) {
        return;
    }


    hero.addEventListener(
        "mousemove",
        function (event) {

            const rect =
                hero.getBoundingClientRect();


            const x =
                (
                    event.clientX -
                    rect.left
                ) /
                rect.width;


            const y =
                (
                    event.clientY -
                    rect.top
                ) /
                rect.height;


            const rotateY =
                (
                    x -
                    .5
                ) *
                2;


            const rotateX =
                (
                    .5 -
                    y
                ) *
                1.5;


            hero.style.transform =
                `
                perspective(1200px)
                rotateX(${rotateX}deg)
                rotateY(${rotateY}deg)
                `;
            

        }
    );


    hero.addEventListener(
        "mouseleave",
        function () {

            hero.style.transform =
                "";

        }
    );

})();

</script>


<?php
require "footer.php";
?>