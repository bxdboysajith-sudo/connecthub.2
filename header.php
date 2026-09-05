<?php

// ============================================================
// CONNECTHUB - GLOBAL HEADER
// FULL REBUILD
// ============================================================
//
// IMPORTANT
// ------------------------------------------------------------
// This version DOES NOT use your old JPG page backgrounds.
//
// Your background is now:
//     connecthub_global_banner.php
//
// Put that file here:
//     C:\xampp\htdocs\connecthub\connecthub_global_banner.php
//
// Every page using require "header.php"; will get the same
// animated HTML background.
//
// Sidebar remains fixed.
// Main content scrolls normally.
// ============================================================


require_once "config.php";

login_required();


// ============================================================
// CURRENT USER
// ============================================================

$uid = (int)(
    $_SESSION["user_id"] ?? 0
);

if ($uid <= 0) {

    header("Location: login.php");
    exit;

}


// ============================================================
// USER NAME
// ============================================================

$headerUserName = trim(
    (string)(
        $_SESSION["name"] ?? "User"
    )
);

if ($headerUserName === "") {

    $headerUserName = "User";

}


// ============================================================
// PROFILE IMAGE
// ============================================================

$headerUserImage = "";


// First try session.
if (
    !empty($_SESSION["profile_image"])
) {

    $headerUserImage =
        trim(
            (string)
            $_SESSION["profile_image"]
        );

}


// ============================================================
// OPTIONAL DATABASE PROFILE LOOKUP
// ============================================================
//
// Protected so an error here does not destroy the whole header.
// ============================================================

try {

    if (
        isset($conn) &&
        $conn instanceof mysqli
    ) {

        $userStmt = @(
            $conn->prepare(
                "
                SELECT
                    name,
                    profile_image
                FROM users
                WHERE id = ?
                LIMIT 1
                "
            )
        );

        if ($userStmt) {

            $userStmt->bind_param(
                "i",
                $uid
            );

            if (
                @$userStmt->execute()
            ) {

                $userResult =
                    @$userStmt->get_result();

                if ($userResult) {

                    $userRow =
                        $userResult->fetch_assoc();

                    if ($userRow) {

                        if (
                            !empty(
                                $userRow["name"]
                            )
                        ) {

                            $headerUserName =
                                trim(
                                    $userRow["name"]
                                );

                        }

                        if (
                            !empty(
                                $userRow["profile_image"]
                            )
                        ) {

                            $headerUserImage =
                                trim(
                                    $userRow["profile_image"]
                                );

                        }

                    }

                }

            }

            $userStmt->close();

        }

    }

} catch (Throwable $e) {

    // Session values continue to be used.

}


// ============================================================
// PROFILE IMAGE PATH
// ============================================================

if (
    $headerUserImage !== ""
) {

    if (
        preg_match(
            '/^(https?:)?\/\//i',
            $headerUserImage
        )
    ) {

        // External URL. Keep unchanged.

    } elseif (
        strpos(
            $headerUserImage,
            "uploads/"
        ) === 0
    ) {

        // Already correct.

    } elseif (
        strpos(
            $headerUserImage,
            "/uploads/"
        ) === 0
    ) {

        $headerUserImage =
            ltrim(
                $headerUserImage,
                "/"
            );

    } else {

        $headerUserImage =
            "uploads/" .
            basename(
                $headerUserImage
            );

    }

}


// ============================================================
// USER INITIAL
// ============================================================

$userInitial =
    strtoupper(
        substr(
            $headerUserName,
            0,
            1
        )
    );

if (
    $userInitial === ""
) {

    $userInitial = "U";

}


// ============================================================
// CURRENT PAGE
// ============================================================

$currentPage =
    basename(
        $_SERVER["PHP_SELF"] ??
        "index.php"
    );

$pageName =
    strtolower(
        pathinfo(
            $currentPage,
            PATHINFO_FILENAME
        )
    );


// ============================================================
// BANKING RELOCK
// ============================================================

$bankingPages = [

    "bank",
    "bank_payment",
    "bank_enter",
    "transfer",
    "deposit",
    "withdraw"

];

if (
    !in_array(
        $pageName,
        $bankingPages,
        true
    )
) {

    unset(
        $_SESSION["bank_unlocked"]
    );

    unset(
        $_SESSION["bank_page_verified"]
    );

}


// ============================================================
// PAGE TITLES
// ============================================================

$pageTitles = [

    "index" =>
        "Home",

    "users" =>
        "Find People",

    "shop" =>
        "Shop",

    "cart" =>
        "Cart",

    "bank" =>
        "Banking",

    "bank_payment" =>
        "Bank Payment",

    "bank_enter" =>
        "Bank Security",

    "transfer" =>
        "Transfer Money",

    "deposit" =>
        "Deposit",

    "withdraw" =>
        "Withdraw",

    "chat" =>
        "Messages",

    "games" =>
        "Games",

    "snake" =>
        "Snake Game",

    "car" =>
        "Car Racing",

    "shooter" =>
        "Space Shooter",

    "sword_fighter" =>
        "Sword Fighter",

    "ninja_runner" =>
        "Ninja Runner",

    "profile" =>
        "Profile",

    "edit_profile" =>
        "Edit Profile"

];


$pageTitle =
    $pageTitles[
        $pageName
    ] ??
    "ConnectHub";


// ============================================================
// PAGE SUBTITLE
// ============================================================

$pageSubtitles = [

    "index" =>
        "Your social world",

    "users" =>
        "Discover new people",

    "shop" =>
        "Explore products",

    "cart" =>
        "Review your shopping",

    "bank" =>
        "Secure digital banking",

    "bank_payment" =>
        "Protected payment",

    "bank_enter" =>
        "Bank security",

    "transfer" =>
        "Send money securely",

    "deposit" =>
        "Add funds",

    "withdraw" =>
        "Withdraw funds",

    "chat" =>
        "Stay connected",

    "games" =>
        "Play • compete • enjoy",

    "snake" =>
        "Classic arcade",

    "car" =>
        "Race to the finish",

    "shooter" =>
        "Defend the galaxy",

    "sword_fighter" =>
        "Fight your way forward",

    "ninja_runner" =>
        "Run • dodge • survive",

    "profile" =>
        "Your personal space",

    "edit_profile" =>
        "Update your profile"

];


$pageSubtitle =
    $pageSubtitles[
        $pageName
    ] ??
    "ConnectHub digital experience";


// ============================================================
// PAGE ICONS
// ============================================================

$pageIcons = [

    "index" =>
        "🏠",

    "users" =>
        "👥",

    "shop" =>
        "🛒",

    "cart" =>
        "🛍",

    "bank" =>
        "🏦",

    "bank_payment" =>
        "💳",

    "bank_enter" =>
        "🔐",

    "transfer" =>
        "💸",

    "deposit" =>
        "➕",

    "withdraw" =>
        "➖",

    "chat" =>
        "💬",

    "games" =>
        "🎮",

    "snake" =>
        "🐍",

    "car" =>
        "🏎",

    "shooter" =>
        "🚀",

    "sword_fighter" =>
        "⚔️",

    "ninja_runner" =>
        "🥷",

    "profile" =>
        "👤",

    "edit_profile" =>
        "✏️"

];


$pageIcon =
    $pageIcons[
        $pageName
    ] ??
    "✦";


// ============================================================
// NAV ACTIVE
// ============================================================

function navActive(
    string $filename,
    string $currentPage
): string {

    return
        $filename === $currentPage
            ? "active"
            : "";

}

?>

<!DOCTYPE html>

<html
    lang="en"
>

<head>

<meta
    charset="UTF-8"
>

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<meta
    name="theme-color"
    content="#050816"
>

<title>
    <?= e($pageTitle) ?> - ConnectHub
</title>


<link
    rel="stylesheet"
    href="style.css"
>


<style>

/* ============================================================
   CONNECTHUB COMPLETE HEADER THEME
============================================================ */


/* ============================================================
   RESET
============================================================ */

html,
body {

    margin:
        0;

    padding:
        0;

    width:
        100%;

    min-width:
        100%;

    min-height:
        100%;

}


*,
*::before,
*::after {

    box-sizing:
        border-box;

}


/* ============================================================
   BODY
============================================================ */

body {

    margin:
        0;

    padding:
        0;

    min-height:
        100vh;

    background:
        #050816;

    color:
        #e5eefc;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    overflow-x:
        hidden;

}


/* ============================================================
   REMOVE ALL OLD PAGE BACKGROUNDS
============================================================ */

.layout > main,
.layout > main.page-index,
.layout > main.page-users,
.layout > main.page-shop,
.layout > main.page-cart,
.layout > main.page-bank,
.layout > main.page-chat,
.layout > main.page-games,
.layout > main.page-profile,
.layout > main.page-snake,
.layout > main.page-car,
.layout > main.page-shooter,
.layout > main.page-sword_fighter,
.layout > main.page-ninja_runner {

    background-image:
        none !important;

    background-color:
        transparent !important;

    background-attachment:
        initial !important;

    background-repeat:
        initial !important;

    background-position:
        initial !important;

    background-size:
        initial !important;

}


/* ============================================================
   HTML BANNER ROOT
============================================================ */

#connecthub-global-banner {

    position:
        fixed !important;

    inset:
        0 !important;

    width:
        100vw !important;

    height:
        100vh !important;

    min-height:
        100vh !important;

    max-height:
        100vh !important;

    z-index:
        0 !important;

    overflow:
        hidden !important;

    pointer-events:
        none !important;

}


/* Any elements generated inside the banner
   cannot interfere with ConnectHub. */

#connecthub-global-banner *,
#connecthub-global-banner iframe {

    pointer-events:
        none !important;

}


/* ============================================================
   OPTIONAL SEPARATE HTML BANNER SUPPORT
============================================================ */

#connecthub-html-banner {

    position:
        fixed !important;

    inset:
        0 !important;

    width:
        100vw !important;

    height:
        100vh !important;

    z-index:
        0 !important;

    pointer-events:
        none !important;

}


#connecthub-html-banner-frame {

    width:
        100% !important;

    height:
        100% !important;

    border:
        0 !important;

    pointer-events:
        none !important;

}


/* ============================================================
   BACKGROUND DARK PROTECTION
============================================================ */

.ch-bg-layer {

    position:
        fixed;

    inset:
        0;

    width:
        100vw;

    height:
        100vh;

    z-index:
        1;

    pointer-events:
        none;

    background:

        linear-gradient(
            180deg,
            rgba(
                2,
                6,
                23,
                .04
            ),
            rgba(
                2,
                6,
                23,
                .10
            )
        );

}


/* ============================================================
   EXTRA GLOBAL GLOW
============================================================ */

.ch-bg-glow {

    position:
        fixed;

    inset:
        0;

    z-index:
        1;

    pointer-events:
        none;

    overflow:
        hidden;

}


.ch-bg-glow::before {

    content:
        "";

    position:
        absolute;

    width:
        48vw;

    height:
        48vw;

    left:
        -15vw;

    top:
        -15vw;

    max-width:
        760px;

    max-height:
        760px;

    border-radius:
        50%;

    background:
        radial-gradient(
            circle,
            rgba(
                124,
                58,
                237,
                .075
            ),
            transparent
            70%
        );

    filter:
        blur(
            25px
        );

    animation:
        chGlowA
        12s
        ease-in-out
        infinite
        alternate;

}


.ch-bg-glow::after {

    content:
        "";

    position:
        absolute;

    width:
        44vw;

    height:
        44vw;

    right:
        -13vw;

    bottom:
        -13vw;

    max-width:
        700px;

    max-height:
        700px;

    border-radius:
        50%;

    background:
        radial-gradient(
            circle,
            rgba(
                34,
                211,
                238,
                .06
            ),
            transparent
            70%
        );

    filter:
        blur(
            30px
        );

    animation:
        chGlowB
        14s
        ease-in-out
        infinite
        alternate;

}


@keyframes chGlowA {

    0% {

        transform:
            translate(
                0,
                0
            )
            scale(
                1
            );

    }

    100% {

        transform:
            translate(
                90px,
                70px
            )
            scale(
                1.12
            );

    }

}


@keyframes chGlowB {

    0% {

        transform:
            translate(
                0,
                0
            )
            scale(
                1
            );

    }

    100% {

        transform:
            translate(
                -90px,
                -70px
            )
            scale(
                1.14
            );

    }

}


/* ============================================================
   GLOBAL GRID
============================================================ */

.ch-grid {

    position:
        fixed;

    inset:
        0;

    z-index:
        2;

    pointer-events:
        none;

    opacity:
        .34;

    background-image:

        linear-gradient(
            rgba(
                255,
                255,
                255,
                .018
            )
            1px,
            transparent
            1px
        ),

        linear-gradient(
            90deg,
            rgba(
                255,
                255,
                255,
                .018
            )
            1px,
            transparent
            1px
        );

    background-size:
        50px
        50px;

    mask-image:
        radial-gradient(
            ellipse 75% 75%,
            black 25%,
            transparent 90%
        );

    -webkit-mask-image:
        radial-gradient(
            ellipse 75% 75%,
            black 25%,
            transparent 90%
        );

}


/* ============================================================
   SPARKLES
============================================================ */

.ch-sparkles {

    position:
        fixed;

    inset:
        0;

    z-index:
        2;

    pointer-events:
        none;

    overflow:
        hidden;

}


.ch-sparkle {

    position:
        absolute;

    display:
        block;

    border-radius:
        50%;

    background:
        rgba(
            255,
            255,
            255,
            .82
        );

    box-shadow:
        0
        0
        8px
        rgba(
            255,
            255,
            255,
            .60
        );

    animation:
        chSparkleMove
        3.3s
        ease-in-out
        infinite;

}


@keyframes chSparkleMove {

    0%,
    100% {

        opacity:
            .03;

        transform:
            scale(
                .25
            );

    }

    50% {

        opacity:
            .80;

        transform:
            scale(
                1
            );

    }

}


/* ============================================================
   PAGE BORDER
============================================================ */

.ch-page-border {

    position:
        fixed;

    inset:
        12px;

    z-index:
        2;

    pointer-events:
        none;

    border:
        1px
        solid
        rgba(
            255,
            255,
            255,
            .035
        );

    border-radius:
        22px;

}


/* ============================================================
   MAIN LAYOUT
============================================================ */

.layout {

    position:
        relative;

    z-index:
        10;

    width:
        100%;

    min-height:
        100vh;

}


/* ============================================================
   SIDEBAR - FIXED
============================================================ */

.layout > nav {

    position:
        fixed !important;

    left:
        0 !important;

    top:
        0 !important;

    bottom:
        0 !important;

    width:
        245px !important;

    height:
        100vh !important;

    min-height:
        100vh !important;

    max-height:
        100vh !important;

    margin:
        0 !important;

    z-index:
        10000 !important;

    display:
        flex !important;

    flex-direction:
        column !important;

    overflow-x:
        hidden !important;

    overflow-y:
        auto !important;

    background:

        linear-gradient(
            180deg,
            rgba(
                3,
                8,
                21,
                .985
            ),
            rgba(
                7,
                13,
                29,
                .975
            ),
            rgba(
                11,
                17,
                36,
                .985
            )
        ) !important;

    border-right:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .10
        ) !important;

    box-shadow:
        12px
        0
        45px
        rgba(
            0,
            0,
            0,
            .34
        ) !important;

    backdrop-filter:
        blur(
            20px
        );

    -webkit-backdrop-filter:
        blur(
            20px
        );

}


/* ============================================================
   SIDEBAR SCROLLBAR
============================================================ */

.layout > nav::-webkit-scrollbar {

    width:
        4px;

}


.layout > nav::-webkit-scrollbar-track {

    background:
        transparent;

}


.layout > nav::-webkit-scrollbar-thumb {

    border-radius:
        999px;

    background:
        rgba(
            96,
            165,
            250,
            .25
        );

}


/* ============================================================
   LOGO
============================================================ */

.ch-logo-area {

    position:
        relative;

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    padding:
        9px
        10px
        17px;

    margin:
        0
        0
        7px;

    border-bottom:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .07
        );

}


.ch-logo-box {

    position:
        relative;

    width:
        42px;

    height:
        42px;

    flex:
        0
        0
        42px;

    display:
        grid;

    place-items:
        center;

    color:
        #fff;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .28
        );

    border-radius:
        13px;

    background:

        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5,
            #7c3aed
        );

    box-shadow:

        0
        0
        25px
        rgba(
            59,
            130,
            246,
            .20
        );

    font-size:
        19px;

    font-weight:
        1000;

    animation:
        logoFloat
        2.7s
        ease-in-out
        infinite;

}


.ch-logo-box::after {

    content:
        "";

    position:
        absolute;

    inset:
        -4px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .10
        );

    border-radius:
        16px;

    animation:
        logoRing
        2.4s
        ease-in-out
        infinite;

}


@keyframes logoFloat {

    0%,
    100% {

        transform:
            translateY(
                0
            );

    }

    50% {

        transform:
            translateY(
                -3px
            )
            rotate(
                2deg
            );

    }

}


@keyframes logoRing {

    50% {

        transform:
            scale(
                1.10
            );

        opacity:
            .20;

    }

}


.ch-logo-text {

    min-width:
        0;

}


.ch-logo-text strong {

    display:
        block;

    color:
        #fff;

    font-size:
        20px;

    font-weight:
        1000;

    letter-spacing:
        -.7px;

}


.ch-logo-text strong span {

    color:
        #60a5fa;

}


.ch-logo-text small {

    display:
        block;

    margin-top:
        3px;

    color:
        #64748b;

    font-size:
        6px;

    font-weight:
        800;

    letter-spacing:
        1.25px;

}


/* ============================================================
   USER BOX
============================================================ */

.ch-user-box {

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

    margin:
        5px
        3px
        13px;

    padding:
        9px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .08
        );

    border-radius:
        14px;

    background:
        rgba(
            255,
            255,
            255,
            .022
        );

}


.ch-user-avatar {

    position:
        relative;

    width:
        39px;

    height:
        39px;

    flex:
        0
        0
        39px;

}


.ch-user-avatar img {

    width:
        39px;

    height:
        39px;

    display:
        block;

    object-fit:
        cover;

    border:
        2px
        solid
        rgba(
            96,
            165,
            250,
            .31
        );

    border-radius:
        12px;

}


.ch-user-fallback {

    width:
        39px;

    height:
        39px;

    display:
        grid;

    place-items:
        center;

    color:
        #fff;

    border-radius:
        12px;

    background:

        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    font-size:
        13px;

    font-weight:
        1000;

}


.ch-user-online {

    position:
        absolute;

    right:
        -1px;

    bottom:
        -1px;

    width:
        10px;

    height:
        10px;

    border:
        2px
        solid
        #071124;

    border-radius:
        50%;

    background:
        #22c55e;

    box-shadow:
        0
        0
        10px
        rgba(
            34,
            197,
            94,
            .60
        );

}


.ch-user-details {

    min-width:
        0;

}


.ch-user-details strong {

    display:
        block;

    overflow:
        hidden;

    color:
        #e2e8f0;

    font-size:
        9px;

    font-weight:
        900;

    white-space:
        nowrap;

    text-overflow:
        ellipsis;

}


.ch-user-details small {

    display:
        block;

    margin-top:
        3px;

    color:
        #64748b;

    font-size:
        6px;

}


/* ============================================================
   NAV SECTION
============================================================ */

.ch-nav-title {

    display:
        flex;

    align-items:
        center;

    gap:
        7px;

    margin:
        8px
        8px
        6px;

    color:
        #475569;

    font-size:
        6px;

    font-weight:
        950;

    letter-spacing:
        1.5px;

}


.ch-nav-title::after {

    content:
        "";

    flex:
        1;

    height:
        1px;

    background:

        linear-gradient(
            90deg,
            rgba(
                100,
                116,
                139,
                .19
            ),
            transparent
        );

}


/* ============================================================
   NAV LINKS
============================================================ */

.layout > nav a {

    position:
        relative;

    display:
        flex;

    align-items:
        center;

    width:
        100%;

    min-height:
        42px;

    margin:
        0
        0
        5px;

    padding:
        7px
        10px;

    gap:
        9px;

    overflow:
        hidden;

    color:
        #aebbd0;

    border:
        1px
        solid
        transparent;

    border-radius:
        11px;

    background:
        transparent;

    text-decoration:
        none;

    font-size:
        11px;

    font-weight:
        750;

    transition:
        background
        .20s
        ease,

        border-color
        .20s
        ease,

        color
        .20s
        ease,

        transform
        .20s
        ease;

}


.ch-nav-icon {

    width:
        27px;

    height:
        27px;

    flex:
        0
        0
        27px;

    display:
        grid;

    place-items:
        center;

    border-radius:
        8px;

    background:
        rgba(
            255,
            255,
            255,
            .035
        );

    transition:
        .20s
        ease;

}


.ch-nav-text {

    min-width:
        0;

    overflow:
        hidden;

    text-overflow:
        ellipsis;

    white-space:
        nowrap;

}


.layout > nav a:hover {

    color:
        #fff;

    transform:
        translateX(
            3px
        );

    background:

        linear-gradient(
            90deg,
            rgba(
                37,
                99,
                235,
                .15
            ),
            rgba(
                124,
                58,
                237,
                .06
            )
        );

    border-color:
        rgba(
            96,
            165,
            250,
            .09
        );

}


.layout > nav a:hover .ch-nav-icon {

    background:
        rgba(
            59,
            130,
            246,
            .16
        );

    transform:
        scale(
            1.08
        );

}


.layout > nav a.active {

    color:
        #fff;

    background:

        linear-gradient(
            90deg,
            rgba(
                37,
                99,
                235,
                .25
            ),
            rgba(
                124,
                58,
                237,
                .10
            )
        );

    border-color:
        rgba(
            96,
            165,
            250,
            .13
        );

    box-shadow:
        inset
        0
        0
        22px
        rgba(
            37,
            99,
            235,
            .035
        );

}


.layout > nav a.active::before {

    content:
        "";

    position:
        absolute;

    left:
        0;

    top:
        6px;

    bottom:
        6px;

    width:
        3px;

    border-radius:
        999px;

    background:

        linear-gradient(
            180deg,
            #60a5fa,
            #8b5cf6
        );

    box-shadow:
        0
        0
        12px
        rgba(
            96,
            165,
            250,
            .65
        );

}


.layout > nav a::after {

    content:
        "";

    position:
        absolute;

    top:
        0;

    left:
        -120%;

    width:
        70%;

    height:
        100%;

    background:

        linear-gradient(
            90deg,
            transparent,
            rgba(
                255,
                255,
                255,
                .07
            ),
            transparent
        );

    transform:
        skewX(
            -22deg
        );

    transition:
        left
        .55s
        ease;

    pointer-events:
        none;

}


.layout > nav a:hover::after {

    left:
        135%;

}


/* ============================================================
   LOGOUT
============================================================ */

.layout > nav a[href="logout.php"] {

    margin-top:
        auto;

    color:
        #fca5a5;

    background:
        rgba(
            127,
            29,
            29,
            .07
        );

    border-color:
        rgba(
            248,
            113,
            113,
            .06
        );

}


.layout > nav a[href="logout.php"]:hover {

    color:
        #fff;

    background:
        rgba(
            239,
            68,
            68,
            .18
        );

}


/* ============================================================
   MAIN PAGE
   ------------------------------------------------------------
   THIS IS THE IMPORTANT FIX.
============================================================ */

.layout > main {

    position:
        relative !important;

    z-index:
        5;

    margin-left:
        245px !important;

    width:
        calc(
            100% -
            245px
        ) !important;

    min-height:
        100vh !important;

    height:
        auto !important;

    max-height:
        none !important;

    overflow-x:
        hidden !important;

    overflow-y:
        visible !important;

    background:
        transparent !important;

}


/* ============================================================
   MAIN CONTENT NEVER GETS SHORT
============================================================ */

.layout > main::before,
.layout > main::after {

    content:
        none !important;

}


.layout > main > * {

    position:
        relative;

    z-index:
        5;

}


/* ============================================================
   TOP HEADER
============================================================ */

.layout > main > header {

    position:
        sticky !important;

    top:
        0 !important;

    z-index:
        9000 !important;

    width:
        100%;

    min-height:
        72px;

    padding:
        0
        20px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        15px;

    background:
        rgba(
            3,
            8,
            20,
            .66
        );

    border-bottom:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .10
        );

    backdrop-filter:
        blur(
            19px
        );

    -webkit-backdrop-filter:
        blur(
            19px
        );

    box-shadow:
        0
        8px
        30px
        rgba(
            0,
            0,
            0,
            .12
        );

    overflow:
        hidden;

}


/* ============================================================
   HEADER LIGHT
============================================================ */

.layout > main > header::before {

    content:
        "";

    position:
        absolute;

    top:
        0;

    left:
        -200px;

    width:
        190px;

    height:
        100%;

    background:

        linear-gradient(
            90deg,
            transparent,
            rgba(
                96,
                165,
                250,
                .12
            ),
            transparent
        );

    transform:
        skewX(
            -21deg
        );

    animation:
        headerSweep
        4.6s
        linear
        infinite;

    pointer-events:
        none;

}


@keyframes headerSweep {

    0% {

        left:
            -200px;

    }

    100% {

        left:
            120%;

    }

}


/* ============================================================
   HEADER BOTTOM LINE
============================================================ */

.layout > main > header::after {

    content:
        "";

    position:
        absolute;

    left:
        0;

    right:
        0;

    bottom:
        0;

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
                .46
            ),
            rgba(
                139,
                92,
                246,
                .40
            ),
            rgba(
                236,
                72,
                153,
                .22
            ),
            transparent
        );

    pointer-events:
        none;

}


/* ============================================================
   HEADER LEFT
============================================================ */

.ch-header-left {

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    min-width:
        0;

}


.ch-header-icon {

    width:
        39px;

    height:
        39px;

    flex:
        0
        0
        39px;

    display:
        grid;

    place-items:
        center;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .18
        );

    border-radius:
        12px;

    background:

        linear-gradient(
            135deg,
            rgba(
                37,
                99,
                235,
                .18
            ),
            rgba(
                124,
                58,
                237,
                .14
            )
        );

    color:
        #dbeafe;

    font-size:
        16px;

    animation:
        headerIconFloat
        3s
        ease-in-out
        infinite;

}


@keyframes headerIconFloat {

    50% {

        transform:
            translateY(
                -2px
            )
            scale(
                1.04
            );

    }

}


.ch-header-title {

    min-width:
        0;

}


.ch-header-title strong {

    display:
        block;

    color:
        #f8fafc;

    font-size:
        14px;

    font-weight:
        950;

    white-space:
        nowrap;

    overflow:
        hidden;

    text-overflow:
        ellipsis;

}


.ch-header-title small {

    display:
        block;

    margin-top:
        3px;

    color:
        #64748b;

    font-size:
        6px;

    font-weight:
        850;

    letter-spacing:
        1.1px;

    white-space:
        nowrap;

}


/* ============================================================
   HEADER CONNECTED BAR
============================================================ */

.ch-connected {

    display:
        flex;

    align-items:
        center;

    gap:
        6px;

    padding:
        6px
        9px;

    border:
        1px
        solid
        rgba(
            34,
            197,
            94,
            .12
        );

    border-radius:
        999px;

    color:
        #bbf7d0;

    background:
        rgba(
            20,
            83,
            45,
            .10
        );

    font-size:
        6px;

    font-weight:
        950;

}


.ch-connected-dot {

    width:
        6px;

    height:
        6px;

    border-radius:
        50%;

    background:
        #22c55e;

    box-shadow:
        0
        0
        10px
        rgba(
            34,
            197,
            94,
            .65
        );

    animation:
        connectedPulse
        1.8s
        ease-in-out
        infinite;

}


@keyframes connectedPulse {

    50% {

        opacity:
            .35;

        transform:
            scale(
                .7
            );

    }

}


/* ============================================================
   HEADER RIGHT
============================================================ */

.ch-header-right {

    display:
        flex;

    align-items:
        center;

    justify-content:
        flex-end;

    gap:
        7px;

}


/* ============================================================
   HEADER MINI BAR
============================================================ */

.ch-mini-status {

    display:
        flex;

    align-items:
        center;

    gap:
        5px;

    padding:
        6px
        8px;

    color:
        #94a3b8;

    border:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .08
        );

    border-radius:
        9px;

    background:
        rgba(
            15,
            23,
            42,
            .35
        );

    font-size:
        6px;

    font-weight:
        850;

}


.ch-mini-status b {

    color:
        #dbeafe;

}


/* ============================================================
   HEADER USER
============================================================ */

.ch-header-user {

    display:
        flex;

    align-items:
        center;

    gap:
        7px;

    padding:
        4px
        8px
        4px
        4px;

    border:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .09
        );

    border-radius:
        12px;

    background:
        rgba(
            15,
            23,
            42,
            .42
        );

}


.ch-header-user-avatar {

    width:
        34px;

    height:
        34px;

    display:
        block;

    object-fit:
        cover;

    border:
        2px
        solid
        rgba(
            96,
            165,
            250,
            .31
        );

    border-radius:
        10px;

}


.ch-header-user-fallback {

    display:
        grid;

    place-items:
        center;

    color:
        #fff;

    background:

        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    font-size:
        11px;

    font-weight:
        1000;

}


.ch-header-user-text {

    min-width:
        0;

}


.ch-header-user-text strong {

    display:
        block;

    max-width:
        130px;

    overflow:
        hidden;

    text-overflow:
        ellipsis;

    white-space:
        nowrap;

    color:
        #e2e8f0;

    font-size:
        8px;

    font-weight:
        900;

}


.ch-header-user-text small {

    display:
        block;

    margin-top:
        2px;

    color:
        #64748b;

    font-size:
        6px;

}


/* ============================================================
   HEADER PROFILE BUTTON
============================================================ */

.ch-header-profile {

    width:
        34px;

    height:
        34px;

    display:
        grid;

    place-items:
        center;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .10
        );

    border-radius:
        10px;

    color:
        #94a3b8;

    background:
        rgba(
            15,
            23,
            42,
            .42
        );

    text-decoration:
        none;

    transition:
        .20s
        ease;

}


.ch-header-profile:hover {

    color:
        #fff;

    border-color:
        rgba(
            96,
            165,
            250,
            .27
        );

    background:
        rgba(
            37,
            99,
            235,
            .17
        );

    transform:
        translateY(
            -2px
        );

}


/* ============================================================
   GAME PAGE FIX
============================================================ */

.layout > main.page-snake,
.layout > main.page-car,
.layout > main.page-shooter,
.layout > main.page-sword_fighter,
.layout > main.page-ninja_runner {

    min-height:
        100vh !important;

    background:
        transparent !important;

}


/* ============================================================
   GAME CHILDREN
============================================================ */

.layout > main.page-snake > *,
.layout > main.page-car > *,
.layout > main.page-shooter > *,
.layout > main.page-sword_fighter > *,
.layout > main.page-ninja_runner > * {

    position:
        relative;

    z-index:
        5;

}


/* ============================================================
   GENERIC GLASS CONTENT
============================================================ */

.layout > main .card,
.layout > main .post-card,
.layout > main .create-post,
.layout > main .product-card,
.layout > main .shop-card,
.layout > main .cart-card,
.layout > main .cart-item,
.layout > main .bank-card,
.layout > main .bank-panel,
.layout > main .account-card,
.layout > main .profile-card,
.layout > main .user-card,
.layout > main .people-card,
.layout > main .chat-card,
.layout > main .chat-window,
.layout > main .people-panel {

    position:
        relative;

    overflow:
        hidden;

}


/* ============================================================
   CARD LIGHT SWEEP
============================================================ */

.layout > main .card::before,
.layout > main .post-card::before,
.layout > main .create-post::before,
.layout > main .product-card::before,
.layout > main .bank-card::before,
.layout > main .bank-panel::before,
.layout > main .profile-card::before,
.layout > main .user-card::before,
.layout > main .people-card::before,
.layout > main .chat-card::before {

    content:
        "";

    position:
        absolute;

    left:
        -35%;

    top:
        0;

    width:
        30%;

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
                .72
            ),
            transparent
        );

    animation:
        cardSweep
        5.5s
        ease-in-out
        infinite;

    pointer-events:
        none;

}


@keyframes cardSweep {

    0% {

        left:
            -35%;

        opacity:
            0;

    }

    25% {

        opacity:
            .70;

    }

    75% {

        opacity:
            .65;

    }

    100% {

        left:
            110%;

        opacity:
            0;

    }

}


/* ============================================================
   C CURSOR
   ------------------------------------------------------------
   The C exists ONLY inside MAIN.
   It does not follow the mouse over sidebar.
============================================================ */

.ch-main-cursor {

    position:
        fixed;

    left:
        0;

    top:
        0;

    width:
        43px;

    height:
        43px;

    z-index:
        999999;

    display:
        none;

    align-items:
        center;

    justify-content:
        center;

    pointer-events:
        none;

    transform:
        translate(
            -50%,
            -50%
        );

}


/* ============================================================
   C OUTER
============================================================ */

.ch-main-cursor-inner {

    position:
        relative;

    width:
        43px;

    height:
        43px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border:
        2px
        solid
        rgba(
            96,
            165,
            250,
            .88
        );

    border-right-color:
        transparent;

    border-radius:
        50%;

    color:
        #dbeafe;

    background:
        rgba(
            4,
            12,
            29,
            .35
        );

    box-shadow:

        0
        0
        15px
        rgba(
            96,
            165,
            250,
            .42
        ),

        inset
        0
        0
        15px
        rgba(
            96,
            165,
            250,
            .08
        );

    font-size:
        16px;

    font-weight:
        1000;

    animation:
        cursorRotate
        2.6s
        linear
        infinite;

}


.ch-main-cursor-inner::before {

    content:
        "";

    position:
        absolute;

    inset:
        6px;

    border:
        1px
        solid
        rgba(
            139,
            92,
            246,
            .26
        );

    border-radius:
        50%;

}


.ch-main-cursor-inner::after {

    content:
        "";

    position:
        absolute;

    width:
        7px;

    height:
        7px;

    border-radius:
        50%;

    background:
        #60a5fa;

    box-shadow:
        0
        0
        14px
        #60a5fa;

}


@keyframes cursorRotate {

    to {

        transform:
            rotate(
                360deg
            );

    }

}


/* ============================================================
   C MOUSE MODE
============================================================ */

body.ch-cursor-active
.layout > main,
body.ch-cursor-active
.layout > main * {

    cursor:
        none !important;

}


/* ============================================================
   CLICK RIPPLE
============================================================ */

.ch-click-ripple {

    position:
        fixed;

    z-index:
        999998;

    width:
        10px;

    height:
        10px;

    border:
        2px
        solid
        rgba(
            96,
            165,
            250,
            .75
        );

    border-radius:
        50%;

    pointer-events:
        none;

    transform:
        translate(
            -50%,
            -50%
        );

    animation:
        clickRipple
        .60s
        ease-out
        forwards;

}


@keyframes clickRipple {

    0% {

        width:
            10px;

        height:
            10px;

        opacity:
            .90;

    }

    100% {

        width:
            120px;

        height:
            120px;

        opacity:
            0;

    }

}


/* ============================================================
   TOUCH RIPPLE
============================================================ */

.ch-touch-ripple {

    position:
        fixed;

    z-index:
        999997;

    width:
        12px;

    height:
        12px;

    border:
        2px
        solid
        rgba(
            96,
            165,
            250,
            .72
        );

    border-radius:
        50%;

    background:
        rgba(
            96,
            165,
            250,
            .04
        );

    pointer-events:
        none;

    transform:
        translate(
            -50%,
            -50%
        );

    animation:
        touchRipple
        .75s
        ease-out
        forwards;

}


@keyframes touchRipple {

    0% {

        width:
            12px;

        height:
            12px;

        opacity:
            .95;

    }

    100% {

        width:
            170px;

        height:
            170px;

        opacity:
            0;

    }

}


/* ============================================================
   NO LOADING OVERLAY
============================================================ */

.ch-no-loading {

    display:
        none !important;

}


/* ============================================================
   MOBILE
============================================================ */

@media (
    max-width: 800px
) {

    .layout > nav {

        position:
            fixed !important;

        left:
            0 !important;

        right:
            0 !important;

        top:
            0 !important;

        bottom:
            auto !important;

        width:
            100% !important;

        height:
            auto !important;

        min-height:
            70px !important;

        max-height:
            117px !important;

        display:
            block !important;

        overflow-x:
            auto !important;

        overflow-y:
            hidden !important;

        white-space:
            nowrap !important;

        -webkit-overflow-scrolling:
            touch;

    }


    .ch-logo-area {

        display:
            inline-flex;

        vertical-align:
            middle;

        padding:
            2px
            5px;

        margin:
            0
            4px
            3px
            0;

        border-bottom:
            0;

    }


    .ch-logo-box {

        width:
            35px;

        height:
            35px;

        flex:
            0
            0
            35px;

        font-size:
            15px;

    }


    .ch-logo-text strong {

        font-size:
            16px;

    }


    .ch-logo-text small {

        display:
            none;

    }


    .ch-user-box,
    .ch-nav-title {

        display:
            none;

    }


    .layout > nav a {

        display:
            inline-flex;

        vertical-align:
            middle;

        width:
            auto;

        min-height:
            36px;

        margin:
            2px;

        padding:
            5px
            8px;

        border-radius:
            9px;

        font-size:
            9px;

    }


    .ch-nav-icon {

        width:
            24px;

        height:
            24px;

        flex:
            0
            0
            24px;

        font-size:
            11px;

    }


    .layout > nav a.active::before {

        display:
            none;

    }


    .layout > nav a[href="logout.php"] {

        margin-top:
            2px;

    }


    .layout > main {

        margin-left:
            0 !important;

        width:
            100% !important;

        min-height:
            100vh !important;

    }


    .layout > main > header {

        margin-top:
            117px;

        min-height:
            62px;

        padding:
            0
            10px;

    }


    .ch-header-icon {

        width:
            34px;

        height:
            34px;

        flex:
            0
            0
            34px;

    }


    .ch-header-title strong {

        font-size:
            11px;

    }


    .ch-header-title small {

        font-size:
            5px;

    }


    .ch-connected,
    .ch-mini-status {

        display:
            none;

    }


    .ch-header-user {

        padding:
            2px;

        border:
            0;

        background:
            transparent;

    }


    .ch-header-user-text {

        display:
            none;

    }


    .ch-header-user-avatar {

        width:
            31px;

        height:
            31px;

    }


    .ch-header-profile {

        width:
            31px;

        height:
            31px;

    }


    .ch-page-border {

        inset:
            6px;

        border-radius:
            15px;

    }


    .ch-main-cursor {

        display:
            none !important;

    }


    body.ch-cursor-active
    .layout > main,
    body.ch-cursor-active
    .layout > main * {

        cursor:
            auto !important;

    }

}


/* ============================================================
   SMALL PHONES
============================================================ */

@media (
    max-width: 500px
) {

    .layout > nav {

        min-height:
            63px !important;

        max-height:
            103px !important;

    }


    .layout > nav a {

        min-height:
            33px;

        padding:
            5px
            7px;

        font-size:
            8px;

    }


    .ch-nav-icon {

        width:
            22px;

        height:
            22px;

        flex:
            0
            0
            22px;

    }


    .layout > main > header {

        margin-top:
            102px;

    }


    .ch-header-user-avatar {

        width:
            29px;

        height:
            29px;

    }


    .ch-header-profile {

        width:
            29px;

        height:
            29px;

    }

}


/* ============================================================
   REDUCED MOTION
============================================================ */

@media (
    prefers-reduced-motion: reduce
) {

    *,
    *::before,
    *::after {

        animation:
            none !important;

        transition:
            none !important;

    }

}

</style>

</head>


<body
    class="page-<?= e($pageName) ?>"
>


<!-- ============================================================
     YOUR CLAUDE HTML BANNER
============================================================ -->

<?php

$globalBannerFile =
    __DIR__ .
    DIRECTORY_SEPARATOR .
    "connecthub_global_banner.php";


if (
    is_file(
        $globalBannerFile
    )
) {

    echo
        '<div id="connecthub-global-banner">';

    require $globalBannerFile;

    echo
        '</div>';

}

?>


<!-- ============================================================
     OPTIONAL SEPARATE CLAUDE HTML FILE
     
     If instead your banner is:
     
     claude_banner.html
     
     use this block and remove/comment the PHP block above.
============================================================ -->

<!--

<div
    id="connecthub-html-banner"
>

    <iframe
        id="connecthub-html-banner-frame"
        src="claude_banner.html"
        scrolling="no"
        title="ConnectHub Animated Background"
    ></iframe>

</div>

-->


<!-- ============================================================
     BACKGROUND SUPPORT LAYERS
============================================================ -->

<div
    class="ch-bg-layer"
    aria-hidden="true"
></div>


<div
    class="ch-bg-glow"
    aria-hidden="true"
></div>


<div
    class="ch-grid"
    aria-hidden="true"
></div>


<div
    class="ch-sparkles"
    id="chSparkles"
    aria-hidden="true"
></div>


<div
    class="ch-page-border"
    aria-hidden="true"
></div>


<!-- ============================================================
     MAIN LAYOUT
============================================================ -->

<div
    class="layout"
>


<!-- ============================================================
     SIDEBAR
============================================================ -->

<nav>


    <!-- ========================================================
         LOGO
    ========================================================= -->

    <div
        class="ch-logo-area"
    >


        <div
            class="ch-logo-box"
        >
            C
        </div>


        <div
            class="ch-logo-text"
        >

            <strong>
                Connect<span>Hub</span>
            </strong>


            <small>
                CONNECT • SHARE • PLAY
            </small>

        </div>


    </div>


    <!-- ========================================================
         USER
    ========================================================= -->

    <div
        class="ch-user-box"
    >


        <div
            class="ch-user-avatar"
        >


            <?php if (
                $headerUserImage !== ""
            ): ?>


                <img
                    src="<?= e(
                        $headerUserImage
                    ) ?>"
                    alt="Profile"
                >


            <?php else: ?>


                <div
                    class="ch-user-fallback"
                >
                    <?= e(
                        $userInitial
                    ) ?>
                </div>


            <?php endif; ?>


            <span
                class="ch-user-online"
            ></span>


        </div>


        <div
            class="ch-user-details"
        >


            <strong>
                <?= e(
                    $headerUserName
                ) ?>
            </strong>


            <small>
                Online • ConnectHub
            </small>


        </div>


    </div>


    <!-- ========================================================
         MAIN
    ========================================================= -->

    <div
        class="ch-nav-title"
    >
        MAIN
    </div>


    <a
        href="index.php"
        class="<?= navActive(
            "index.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            🏠
        </span>

        <span class="ch-nav-text">
            Home
        </span>

    </a>


    <a
        href="users.php"
        class="<?= navActive(
            "users.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            👥
        </span>

        <span class="ch-nav-text">
            Find People
        </span>

    </a>


    <a
        href="chat.php"
        class="<?= navActive(
            "chat.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            💬
        </span>

        <span class="ch-nav-text">
            Messages
        </span>

    </a>


    <!-- ========================================================
         SHOPPING
    ========================================================= -->

    <div
        class="ch-nav-title"
    >
        SHOPPING
    </div>


    <a
        href="shop.php"
        class="<?= navActive(
            "shop.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            🛒
        </span>

        <span class="ch-nav-text">
            Shop
        </span>

    </a>


    <a
        href="cart.php"
        class="<?= navActive(
            "cart.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            🛍
        </span>

        <span class="ch-nav-text">
            Cart
        </span>

    </a>


    <!-- ========================================================
         MONEY
    ========================================================= -->

    <div
        class="ch-nav-title"
    >
        MONEY
    </div>


    <a
        href="bank.php"
        class="<?= navActive(
            "bank.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            🏦
        </span>

        <span class="ch-nav-text">
            Banking
        </span>

    </a>


    <a
        href="transfer.php"
        class="<?= navActive(
            "transfer.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            💸
        </span>

        <span class="ch-nav-text">
            Transfer Money
        </span>

    </a>


    <!-- ========================================================
         GAMING
    ========================================================= -->

    <div
        class="ch-nav-title"
    >
        GAMING
    </div>


    <a
        href="games.php"
        class="<?= navActive(
            "games.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            🎮
        </span>

        <span class="ch-nav-text">
            Games
        </span>

    </a>


    <a
        href="ninja_runner.php"
        class="<?= navActive(
            "ninja_runner.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            🥷
        </span>

        <span class="ch-nav-text">
            Ninja Runner
        </span>

    </a>


    <a
        href="sword_fighter.php"
        class="<?= navActive(
            "sword_fighter.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            ⚔️
        </span>

        <span class="ch-nav-text">
            Sword Fighter
        </span>

    </a>


    <a
        href="snake.php"
        class="<?= navActive(
            "snake.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            🐍
        </span>

        <span class="ch-nav-text">
            Snake
        </span>

    </a>


    <a
        href="car.php"
        class="<?= navActive(
            "car.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            🏎
        </span>

        <span class="ch-nav-text">
            Car Racing
        </span>

    </a>


    <a
        href="shooter.php"
        class="<?= navActive(
            "shooter.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            🚀
        </span>

        <span class="ch-nav-text">
            Space Shooter
        </span>

    </a>


    <!-- ========================================================
         ACCOUNT
    ========================================================= -->

    <div
        class="ch-nav-title"
    >
        ACCOUNT
    </div>


    <a
        href="profile.php"
        class="<?= navActive(
            "profile.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            👤
        </span>

        <span class="ch-nav-text">
            Profile
        </span>

    </a>


    <a
        href="edit_profile.php"
        class="<?= navActive(
            "edit_profile.php",
            $currentPage
        ) ?>"
    >

        <span class="ch-nav-icon">
            ✏️
        </span>

        <span class="ch-nav-text">
            Edit Profile
        </span>

    </a>


    <a
        href="logout.php"
    >

        <span class="ch-nav-icon">
            🚪
        </span>

        <span class="ch-nav-text">
            Logout
        </span>

    </a>


</nav>


<!-- ============================================================
     MAIN
============================================================ -->

<main
    class="page-<?= e(
        $pageName
    ) ?>"
>


    <!-- ========================================================
         HEADER
    ========================================================= -->

    <header>


        <!-- ====================================================
             LEFT
        ===================================================== -->

        <div
            class="ch-header-left"
        >


            <div
                class="ch-header-icon"
            >
                <?= e(
                    $pageIcon
                ) ?>
            </div>


            <div
                class="ch-header-title"
            >

                <strong>
                    <?= e(
                        $pageTitle
                    ) ?>
                </strong>


                <small>
                    <?= e(
                        $pageSubtitle
                    ) ?>
                </small>

            </div>


            <div
                class="ch-connected"
            >

                <span
                    class="ch-connected-dot"
                ></span>

                CONNECTED

            </div>


        </div>


        <!-- ====================================================
             RIGHT
        ===================================================== -->

        <div
            class="ch-header-right"
        >


            <div
                class="ch-mini-status"
            >

                <span>
                    USER ID
                </span>

                <b>
                    #<?= e(
                        (string)$uid
                    ) ?>
                </b>

            </div>


            <div
                class="ch-header-user"
            >


                <?php if (
                    $headerUserImage !== ""
                ): ?>


                    <img
                        class="ch-header-user-avatar"
                        src="<?= e(
                            $headerUserImage
                        ) ?>"
                        alt="Profile"
                    >


                <?php else: ?>


                    <div
                        class="
                            ch-header-user-avatar
                            ch-header-user-fallback
                        "
                    >
                        <?= e(
                            $userInitial
                        ) ?>
                    </div>


                <?php endif; ?>


                <div
                    class="ch-header-user-text"
                >

                    <strong>
                        <?= e(
                            $headerUserName
                        ) ?>
                    </strong>


                    <small>
                        Active now
                    </small>

                </div>


            </div>


            <a
                href="profile.php"
                class="ch-header-profile"
                title="Profile"
            >
                👤
            </a>


        </div>


    </header>


<!-- ============================================================
     PAGE CONTENT STARTS HERE

     IMPORTANT:
     Do NOT close </main> in this file.

     Your existing page content comes here.

     footer.php should close the main/layout.
============================================================ -->


<script>

/* ============================================================
   CONNECTHUB GLOBAL EFFECT ENGINE
============================================================ */

(function () {

    "use strict";


    /* ========================================================
       SPARKLES
    ======================================================== */

    const sparkles =
        document.getElementById(
            "chSparkles"
        );


    if (
        sparkles
    ) {

        const count =
            window.innerWidth <=
            700
                ? 45
                : 100;


        for (
            let i = 0;
            i < count;
            i++
        ) {

            const star =
                document.createElement(
                    "span"
                );


            star.className =
                "ch-sparkle";


            const size =
                Math.random() *
                2.2 +
                .7;


            star.style.width =
                size +
                "px";


            star.style.height =
                size +
                "px";


            star.style.left =
                (
                    Math.random() *
                    100
                ) +
                "%";


            star.style.top =
                (
                    Math.random() *
                    100
                ) +
                "%";


            star.style.animationDuration =
                (
                    Math.random() *
                    4 +
                    2
                ).toFixed(
                    2
                ) +
                "s";


            star.style.animationDelay =
                (
                    Math.random() *
                    5
                ).toFixed(
                    2
                ) +
                "s";


            sparkles.appendChild(
                star
            );

        }

    }


    /* ========================================================
       MAIN PAGE
    ======================================================== */

    const main =
        document.querySelector(
            ".layout > main"
        );


    const cursor =
        document.createElement(
            "div"
        );


    cursor.className =
        "ch-main-cursor";


    cursor.innerHTML =
        `
            <div class="ch-main-cursor-inner">
                C
            </div>
        `;


    document.body.appendChild(
        cursor
    );


    /* ========================================================
       CURSOR STATE
    ======================================================== */

    let mouseX =
        0;


    let mouseY =
        0;


    let cursorX =
        0;


    let cursorY =
        0;


    let cursorInsideMain =
        false;


    const hasFinePointer =
        window.matchMedia(
            "(pointer:fine)"
        );


    /* ========================================================
       CURSOR LOOP
    ======================================================== */

    function cursorAnimation() {

        cursorX +=
            (
                mouseX -
                cursorX
            ) *
            .22;


        cursorY +=
            (
                mouseY -
                cursorY
            ) *
            .22;


        if (
            cursorInsideMain &&
            hasFinePointer.matches
        ) {

            cursor.style.left =
                cursorX +
                "px";


            cursor.style.top =
                cursorY +
                "px";

        }


        requestAnimationFrame(
            cursorAnimation
        );

    }


    cursorAnimation();


    /* ========================================================
       ONLY MAIN GETS C CURSOR
    ======================================================== */

    if (
        main &&
        hasFinePointer.matches
    ) {


        main.addEventListener(
            "mouseenter",
            function (
                event
            ) {

                cursorInsideMain =
                    true;


                mouseX =
                    event.clientX;


                mouseY =
                    event.clientY;


                cursorX =
                    mouseX;


                cursorY =
                    mouseY;


                cursor.style.display =
                    "flex";


                document.body.classList.add(
                    "ch-cursor-active"
                );

            }
        );


        main.addEventListener(
            "mousemove",
            function (
                event
            ) {

                mouseX =
                    event.clientX;


                mouseY =
                    event.clientY;

            },
            {
                passive:true
            }
        );


        main.addEventListener(
            "mouseleave",
            function () {

                cursorInsideMain =
                    false;


                cursor.style.display =
                    "none";


                document.body.classList.remove(
                    "ch-cursor-active"
                );

            }
        );

    }


    /* ========================================================
       CLICK EFFECT
    ======================================================== */

    document.addEventListener(
        "click",
        function (
            event
        ) {

            if (
                !main
            ) {

                return;

            }


            const rect =
                main.getBoundingClientRect();


            const inside =
                event.clientX >=
                    rect.left &&
                event.clientX <=
                    rect.right &&
                event.clientY >=
                    rect.top &&
                event.clientY <=
                    rect.bottom;


            if (
                !inside
            ) {

                return;

            }


            const ripple =
                document.createElement(
                    "div"
                );


            ripple.className =
                "ch-click-ripple";


            ripple.style.left =
                event.clientX +
                "px";


            ripple.style.top =
                event.clientY +
                "px";


            document.body.appendChild(
                ripple
            );


            setTimeout(
                function () {

                    ripple.remove();

                },
                650
            );

        },
        {
            passive:true
        }
    );


    /* ========================================================
       TOUCH RIPPLE
    ======================================================== */

    document.addEventListener(
        "pointerdown",
        function (
            event
        ) {

            if (
                event.pointerType ===
                "mouse"
            ) {

                return;

            }


            const ripple =
                document.createElement(
                    "div"
                );


            ripple.className =
                "ch-touch-ripple";


            ripple.style.left =
                event.clientX +
                "px";


            ripple.style.top =
                event.clientY +
                "px";


            document.body.appendChild(
                ripple
            );


            setTimeout(
                function () {

                    ripple.remove();

                },
                800
            );

        },
        {
            passive:true
        }
    );


    /* ========================================================
       BUTTON / LINK SPARKS
    ======================================================== */

    document.addEventListener(
        "click",
        function (
            event
        ) {

            const item =
                event.target.closest(
                    "button, a"
                );


            if (
                !item
            ) {

                return;

            }


            for (
                let i =
                    0;
                i < 4;
                i++
            ) {

                const sparkle =
                    document.createElement(
                        "span"
                    );


                sparkle.style.position =
                    "fixed";


                sparkle.style.zIndex =
                    "999996";


                sparkle.style.pointerEvents =
                    "none";


                sparkle.style.width =
                    (
                        Math.random() *
                        3 +
                        1
                    ) +
                    "px";


                sparkle.style.height =
                    sparkle.style.width;


                sparkle.style.borderRadius =
                    "50%";


                sparkle.style.background =
                    "#bfdbfe";


                sparkle.style.boxShadow =
                    "0 0 8px #60a5fa";


                const rect =
                    item.getBoundingClientRect();


                sparkle.style.left =
                    (
                        rect.left +
                        rect.width /
                        2 +
                        (
                            Math.random() -
                            .5
                        ) *
                        rect.width
                    ) +
                    "px";


                sparkle.style.top =
                    (
                        rect.top +
                        rect.height /
                        2 +
                        (
                            Math.random() -
                            .5
                        ) *
                        rect.height
                    ) +
                    "px";


                sparkle.animate(

                    [

                        {

                            opacity:
                                0,

                            transform:
                                "scale(.25)"

                        },

                        {

                            opacity:
                                1,

                            transform:
                                "scale(1)"

                        },

                        {

                            opacity:
                                0,

                            transform:
                                "scale(.20) translateY(-20px)"

                        }

                    ],

                    {

                        duration:
                            550,

                        easing:
                            "ease-out",

                        fill:
                            "forwards"

                    }

                );


                document.body.appendChild(
                    sparkle
                );


                setTimeout(
                    function () {

                        sparkle.remove();

                    },
                    650
                );

            }

        },
        {
            passive:true
        }
    );


    /* ========================================================
       SCROLL POSITION
    ======================================================== */

    const scrollKey =
        "connecthub_scroll_" +
        window.location.pathname;


    function saveScroll() {

        try {

            sessionStorage.setItem(
                scrollKey,
                String(
                    window.scrollY ||
                    window.pageYOffset ||
                    0
                )
            );

        } catch (
            error
        ) {}

    }


    function restoreScroll() {

        let saved =
            null;


        try {

            saved =
                sessionStorage.getItem(
                    scrollKey
                );

        } catch (
            error
        ) {

            return;

        }


        if (
            saved ===
            null
        ) {

            return;

        }


        const position =
            parseInt(
                saved,
                10
            );


        if (
            Number.isNaN(
                position
            )
        ) {

            return;

        }


        if (
            position <=
            5
        ) {

            return;

        }


        window.scrollTo(
            0,
            position
        );


        requestAnimationFrame(
            function () {

                window.scrollTo(
                    0,
                    position
                );

            }
        );


        setTimeout(
            function () {

                window.scrollTo(
                    0,
                    position
                );

            },
            250
        );


        setTimeout(
            function () {

                try {

                    sessionStorage.removeItem(
                        scrollKey
                    );

                } catch (
                    error
                ) {}

            },
            500
        );

    }


    window.addEventListener(
        "load",
        function () {

            restoreScroll();

        },
        {
            once:true
        }
    );


    window.addEventListener(
        "pageshow",
        function () {

            restoreScroll();

        }
    );


    document.addEventListener(
        "submit",
        function () {

            saveScroll();

        },
        true
    );


    document.addEventListener(
        "click",
        function (
            event
        ) {

            const link =
                event.target.closest(
                    "a"
                );


            if (
                !link
            ) {

                return;

            }


            const href =
                link.getAttribute(
                    "href"
                );


            if (
                !href ||
                href === "#" ||
                href.startsWith(
                    "#"
                ) ||
                href.startsWith(
                    "javascript:"
                )
            ) {

                return;

            }


            if (
                link.target ===
                "_blank"
            ) {

                return;

            }


            if (
                link.hasAttribute(
                    "download"
                )
            ) {

                return;

            }


            saveScroll();

        },
        true
    );


    window.addEventListener(
        "beforeunload",
        function () {

            saveScroll();

        }
    );


    /* ========================================================
       GAME KEYBOARD PROTECTION
    ======================================================== */

    const isGamePage =
        document.body.classList.contains(
            "page-snake"
        ) ||
        document.body.classList.contains(
            "page-car"
        ) ||
        document.body.classList.contains(
            "page-shooter"
        ) ||
        document.body.classList.contains(
            "page-sword_fighter"
        ) ||
        document.body.classList.contains(
            "page-ninja_runner"
        );


    if (
        isGamePage
    ) {

        document.addEventListener(
            "keydown",
            function (
                event
            ) {

                const active =
                    document.activeElement;


                const tag =
                    active &&
                    active.tagName
                        ? active.tagName.toLowerCase()
                        : "";


                if (
                    tag === "input" ||
                    tag === "textarea" ||
                    tag === "select"
                ) {

                    return;

                }


                const key =
                    String(
                        event.key
                    ).toLowerCase();


                if (
                    key === " " ||
                    key === "arrowup" ||
                    key === "arrowdown"
                ) {

                    event.preventDefault();

                }

            }
        );

    }


    /* ========================================================
       BANNER POINTER PROTECTION
    ======================================================== */

    const banner =
        document.getElementById(
            "connecthub-global-banner"
        );


    if (
        banner
    ) {

        banner.style.pointerEvents =
            "none";

    }


})();

</script>


<!-- ============================================================
     FOOTER.PHP CLOSES:
     main
     layout
     body
     html
============================================================ -->