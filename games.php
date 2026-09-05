<?php
// ============================================================
// CONNECTHUB - ADVANCED GAMES HUB
// ============================================================
// FEATURES
// ------------------------------------------------------------
// • ConnectHub Original Games
// • Hill Climb Racing - Official Google Play link
// • Online Games section
// • Mobile Games section
// • Search games
// • Category filters
// • Featured game
// • Animated gaming interface
// • Premium blue / cyan gaming theme
// • Play Now buttons
// • Google Play buttons
// • Optional game cover images
// • Responsive mobile layout
// ============================================================

require "config.php";

login_required();


// ============================================================
// CONNECTHUB ORIGINAL GAMES
// ============================================================

$connectHubGames = [

    [
        "name" => "Snake Game",

        "description" =>
            "Control the snake, collect points and survive as long as possible.",

        "icon" => "🐍",

        "image" =>
            "uploads/games/snake.jpg",

        "category" => "Arcade",

        "rating" => "4.8",

        "players" => "1 Player",

        "url" => "snake.php",

        "type" => "internal"
    ],

    [
        "name" => "Car Racing",

        "description" =>
            "Race through challenging tracks, dodge obstacles and increase your speed.",

        "icon" => "🏎️",

        "image" =>
            "uploads/games/car-racing.jpg",

        "category" => "Racing",

        "rating" => "4.9",

        "players" => "1 Player",

        "url" => "car.php",

        "type" => "internal"
    ],

    [
        "name" => "Space Shooter",

        "description" =>
            "Destroy enemy waves, collect power-ups and defeat powerful bosses.",

        "icon" => "🚀",

        "image" =>
            "uploads/games/space-shooter.jpg",

        "category" => "Action",

        "rating" => "4.9",

        "players" => "1 Player",

        "url" => "shooter.php",

        "type" => "internal"
    ],

    [
        "name" => "Sword Fighter",

        "description" =>
            "Fight enemies using sword combos, special attacks and powerful abilities.",

        "icon" => "⚔️",

        "image" =>
            "uploads/games/sword-fighter.jpg",

        "category" => "Action",

        "rating" => "5.0",

        "players" => "1 Player",

        "url" => "sword_fighter.php",

        "type" => "internal"
    ],

    [
        "name" => "Ninja Runner",

        "description" =>
            "Run, jump and dodge obstacles while surviving as long as possible.",

        "icon" => "🥷",

        "image" =>
            "uploads/games/ninja-runner.jpg",

        "category" => "Runner",

        "rating" => "4.8",

        "players" => "1 Player",

        "url" => "ninja_runner.php",

        "type" => "internal"
    ]

];


// ============================================================
// ONLINE GAMES
// ============================================================

$onlineGames = [

    [
        "name" => "Arcade World",

        "description" =>
            "Discover browser-based arcade experiences.",

        "icon" => "🎯",

        "image" =>
            "uploads/games/arcade-world.jpg",

        "category" => "Arcade",

        "rating" => "4.7",

        "players" => "Online",

        "url" => "#",

        "type" => "online"
    ],

    [
        "name" => "Racing Arena",

        "description" =>
            "Explore fast browser racing games and competitive driving.",

        "icon" => "🏁",

        "image" =>
            "uploads/games/racing-arena.jpg",

        "category" => "Racing",

        "rating" => "4.8",

        "players" => "Online",

        "url" => "#",

        "type" => "online"
    ],

    [
        "name" => "Action Zone",

        "description" =>
            "Discover browser action games and exciting challenges.",

        "icon" => "🔥",

        "image" =>
            "uploads/games/action-zone.jpg",

        "category" => "Action",

        "rating" => "4.8",

        "players" => "Online",

        "url" => "#",

        "type" => "online"
    ],

    [
        "name" => "Puzzle Universe",

        "description" =>
            "Relax with browser puzzle and strategy experiences.",

        "icon" => "🧩",

        "image" =>
            "uploads/games/puzzle-universe.jpg",

        "category" => "Puzzle",

        "rating" => "4.6",

        "players" => "Online",

        "url" => "#",

        "type" => "online"
    ]

];


// ============================================================
// GOOGLE PLAY GAMES
// ============================================================

$playStoreGames = [

    [
        "name" => "Hill Climb Racing",

        "description" =>
            "Drive uphill through challenging terrain and test your driving skills.",

        "icon" => "🏎️",

        "image" =>
            "uploads/games/hill-climb-racing.jpg",

        "category" => "Racing",

        "rating" => "4.5",

        "players" => "Android",

        "url" =>
            "https://play.google.com/store/apps/details?id=com.fingersoft.hillclimb&pcampaignid=web_share",

        "type" => "playstore"
    ],

    [
        "name" => "Mobile Action Game",

        "description" =>
            "Discover action games available on Google Play.",

        "icon" => "🔥",

        "image" =>
            "uploads/games/mobile-action.jpg",

        "category" => "Action",

        "rating" => "4.5",

        "players" => "Android",

        "url" =>
            "https://play.google.com/",

        "type" => "playstore"
    ],

    [
        "name" => "Mobile Adventure",

        "description" =>
            "Explore adventure games available for Android devices.",

        "icon" => "🗺️",

        "image" =>
            "uploads/games/mobile-adventure.jpg",

        "category" => "Adventure",

        "rating" => "4.5",

        "players" => "Android",

        "url" =>
            "https://play.google.com/",

        "type" => "playstore"
    ]

];


// ============================================================
// HEADER
// ============================================================

require "header.php";

?>

<style>

/* ============================================================
   CONNECTHUB GAMES HUB
============================================================ */

.games-hub {

    width: 100%;

    max-width: 1200px;

    margin: 0 auto;

    padding:
        25px
        22px
        80px;

}


/* ============================================================
   HERO
============================================================ */

.games-hero {

    position: relative;

    min-height: 310px;

    margin-bottom: 16px;

    padding:
        38px;

    overflow: hidden;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .18
        );

    border-radius: 26px;

    background:

        radial-gradient(
            circle at 88% 20%,
            rgba(
                56,
                189,
                248,
                .22
            ),
            transparent 25%
        ),

        radial-gradient(
            circle at 15% 100%,
            rgba(
                124,
                58,
                237,
                .22
            ),
            transparent 30%
        ),

        linear-gradient(
            135deg,
            rgba(
                2,
                8,
                23,
                .97
            ),
            rgba(
                8,
                25,
                55,
                .95
            ),
            rgba(
                30,
                41,
                96,
                .92
            )
        );

    box-shadow:
        0
        25px
        65px
        rgba(
            0,
            0,
            0,
            .24
        );

}


/* ============================================================
   HERO ORBIT
============================================================ */

.games-hero::before {

    content: "";

    position: absolute;

    width: 350px;

    height: 350px;

    top: -190px;

    right: -100px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .17
        );

    border-radius: 50%;

    box-shadow:
        0
        0
        50px
        rgba(
            37,
            99,
            235,
            .10
        );

    animation:
        gameOrbit
        10s
        linear
        infinite;

}


@keyframes gameOrbit {

    to {

        transform:
            rotate(
                360deg
            );

    }

}


/* ============================================================
   HERO SCAN LINE
============================================================ */

.games-hero::after {

    content: "";

    position: absolute;

    left: -30%;

    bottom: 0;

    width: 28%;

    height: 1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            #38bdf8,
            #818cf8,
            transparent
        );

    animation:
        heroScan
        5s
        linear
        infinite;

}


@keyframes heroScan {

    from {
        left: -30%;
    }

    to {
        left: 120%;
    }

}


/* ============================================================
   HERO CONTENT
============================================================ */

.games-hero-content {

    position: relative;

    z-index: 3;

    max-width: 720px;

}


.games-eyebrow {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    padding:
        7px
        11px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .17
        );

    border-radius:
        999px;

    color:
        #93c5fd;

    background:
        rgba(
            37,
            99,
            235,
            .09
        );

    font-size:
        7px;

    font-weight:
        950;

    letter-spacing:
        1.5px;

}


.games-eyebrow::before {

    content: "";

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
        #22c55e;

    animation:
        onlineDot
        1.5s
        ease-in-out
        infinite;

}


@keyframes onlineDot {

    50% {

        transform:
            scale(
                1.5
            );

    }

}


/* ============================================================
   HERO TITLE
============================================================ */

.games-hero h1 {

    margin:
        14px
        0
        8px;

    color:
        #ffffff;

    font-size:
        clamp(
            35px,
            5vw,
            60px
        );

    line-height:
        1;

    font-weight:
        1000;

    letter-spacing:
        -2.5px;

}


.games-hero h1 span {

    color:
        #60a5fa;

    text-shadow:
        0
        0
        28px
        rgba(
            96,
            165,
            250,
            .32
        );

}


/* ============================================================
   HERO DESCRIPTION
============================================================ */

.games-hero p {

    max-width:
        650px;

    margin:
        0;

    color:
        #8096b0;

    font-size:
        10px;

    line-height:
        1.8;

}


/* ============================================================
   HERO BUTTONS
============================================================ */

.games-hero-actions {

    display:
        flex;

    flex-wrap:
        wrap;

    gap:
        7px;

    margin-top:
        22px;

}


.hero-button {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        6px;

    min-height:
        42px;

    padding:
        0
        15px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .16
        );

    border-radius:
        10px;

    color:
        #dbeafe;

    background:
        rgba(
            37,
            99,
            235,
            .08
        );

    text-decoration:
        none;

    font-size:
        7px;

    font-weight:
        900;

    transition:
        .20s
        ease;

}


.hero-button:hover {

    color:
        #ffffff;

    background:
        rgba(
            37,
            99,
            235,
            .18
        );

    transform:
        translateY(
            -2px
        );

}


.hero-button.primary {

    color:
        #ffffff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    box-shadow:
        0
        10px
        26px
        rgba(
            37,
            99,
            235,
            .22
        );

}


/* ============================================================
   CONTROLLER DECORATION
============================================================ */

.games-controller {

    position:
        absolute;

    right:
        50px;

    bottom:
        30px;

    z-index:
        2;

    font-size:
        110px;

    opacity:
        .09;

    transform:
        rotate(
            -12deg
        );

    animation:
        controllerFloat
        4s
        ease-in-out
        infinite;

}


@keyframes controllerFloat {

    50% {

        transform:
            rotate(
                -7deg
            )
            translateY(
                -9px
            );

    }

}


/* ============================================================
   FEATURED
============================================================ */

.featured-game {

    position:
        relative;

    overflow:
        hidden;

    margin-bottom:
        16px;

    padding:
        19px
        20px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .13
        );

    border-radius:
        19px;

    background:
        linear-gradient(
            135deg,
            rgba(
                3,
                12,
                29,
                .94
            ),
            rgba(
                30,
                64,
                175,
                .31
            ),
            rgba(
                76,
                29,
                149,
                .20
            )
        );

}


.featured-game::before {

    content:
        "";

    position:
        absolute;

    width:
        230px;

    height:
        230px;

    right:
        -110px;

    top:
        -100px;

    border-radius:
        50%;

    background:
        rgba(
            59,
            130,
            246,
            .12
        );

    filter:
        blur(
            8px
        );

}


.featured-content {

    position:
        relative;

    z-index:
        2;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        15px;

}


.featured-left {

    display:
        flex;

    align-items:
        center;

    gap:
        13px;

}


.featured-icon {

    width:
        70px;

    height:
        70px;

    flex:
        0
        0
        70px;

    display:
        grid;

    place-items:
        center;

    border:
        1px
        solid
        rgba(
            147,
            197,
            253,
            .19
        );

    border-radius:
        18px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    font-size:
        31px;

    box-shadow:
        0
        13px
        28px
        rgba(
            37,
            99,
            235,
            .20
        );

    animation:
        featuredFloat
        3s
        ease-in-out
        infinite;

}


@keyframes featuredFloat {

    50% {

        transform:
            translateY(
                -3px
            );

    }

}


.featured-content h2 {

    margin:
        5px
        0
        4px;

    color:
        #f8fafc;

    font-size:
        19px;

}


.featured-content p {

    margin:
        0;

    color:
        #64748b;

    font-size:
        8px;

    line-height:
        1.6;

}


.featured-play {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    min-height:
        42px;

    padding:
        0
        17px;

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
        10px;

    color:
        #ffffff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    text-decoration:
        none;

    font-size:
        8px;

    font-weight:
        950;

}


/* ============================================================
   TOOLBAR
============================================================ */

.games-toolbar {

    display:
        flex;

    align-items:
        center;

    gap:
        8px;

    margin-bottom:
        17px;

    padding:
        10px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .09
        );

    border-radius:
        15px;

    background:
        rgba(
            3,
            12,
            29,
            .77
        );

    backdrop-filter:
        blur(
            14px
        );

}


.game-search {

    flex:
        1;

    min-width:
        180px;

    height:
        41px;

    padding:
        0
        13px;

    border:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .10
        ) !important;

    border-radius:
        10px;

    outline:
        none;

    color:
        #e2e8f0 !important;

    background:
        rgba(
            15,
            23,
            42,
            .82
        ) !important;

    font-size:
        9px;

}


.game-search::placeholder {

    color:
        #475569;

}


.game-search:focus {

    border-color:
        rgba(
            96,
            165,
            250,
            .35
        ) !important;

    box-shadow:
        0
        0
        0
        3px
        rgba(
            59,
            130,
            246,
            .08
        );

}


/* ============================================================
   FILTERS
============================================================ */

.game-filters {

    display:
        flex;

    flex-wrap:
        wrap;

    gap:
        5px;

}


.game-filter {

    min-height:
        31px;

    padding:
        0
        10px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .07
        );

    border-radius:
        8px;

    color:
        #64748b;

    background:
        rgba(
            15,
            23,
            42,
            .60
        );

    font-size:
        7px;

    font-weight:
        850;

    cursor:
        pointer;

}


.game-filter:hover,
.game-filter.active {

    color:
        #ffffff;

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
            .15
        );

}


/* ============================================================
   SECTION
============================================================ */

.games-section {

    margin-bottom:
        20px;

}


.games-section-header {

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    margin-bottom:
        10px;

}


.games-section-icon {

    width:
        39px;

    height:
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
            .11
        );

    border-radius:
        11px;

    background:
        rgba(
            37,
            99,
            235,
            .12
        );

    font-size:
        17px;

}


.games-section-header strong {

    display:
        block;

    color:
        #ffffff;

    font-size:
        16px;

    font-weight:
        950;

}


.games-section-header small {

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
   GAME GRID
============================================================ */

.games-grid {

    display:
        grid;

    grid-template-columns:
        repeat(
            3,
            minmax(
                0,
                1fr
            )
        );

    gap:
        11px;

}


/* ============================================================
   GAME CARD
============================================================ */

.game-card {

    position:
        relative;

    display:
        flex;

    flex-direction:
        column;

    min-height:
        340px;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .09
        );

    border-radius:
        18px;

    background:
        linear-gradient(
            145deg,
            rgba(
                3,
                12,
                29,
                .94
            ),
            rgba(
                7,
                23,
                46,
                .86
            )
        );

    box-shadow:
        0
        12px
        30px
        rgba(
            0,
            0,
            0,
            .16
        );

    transition:
        transform
        .25s
        ease,
        border
        .25s
        ease,
        box-shadow
        .25s
        ease;

}


.game-card:hover {

    transform:
        translateY(
            -5px
        );

    border-color:
        rgba(
            96,
            165,
            250,
            .25
        );

    box-shadow:
        0
        20px
        45px
        rgba(
            0,
            0,
            0,
            .25
        ),
        0
        0
        25px
        rgba(
            37,
            99,
            235,
            .06
        );

}


/* ============================================================
   GAME IMAGE
============================================================ */

.game-cover {

    position:
        relative;

    width:
        100%;

    height:
        150px;

    overflow:
        hidden;

    background:
        linear-gradient(
            135deg,
            #0f172a,
            #172554,
            #312e81
        );

}


.game-cover::after {

    content:
        "";

    position:
        absolute;

    inset:
        0;

    background:
        linear-gradient(
            180deg,
            transparent 45%,
            rgba(
                2,
                6,
                23,
                .82
            )
        );

}


.game-cover img {

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;

    display:
        block;

    transition:
        transform
        .35s
        ease;

}


.game-card:hover
.game-cover img {

    transform:
        scale(
            1.06
        );

}


.game-cover-fallback {

    width:
        100%;

    height:
        100%;

    display:
        grid;

    place-items:
        center;

    font-size:
        57px;

    background:
        radial-gradient(
            circle at 50% 30%,
            rgba(
                59,
                130,
                246,
                .25
            ),
            transparent 55%
        ),

        linear-gradient(
            135deg,
            #0f172a,
            #172554,
            #312e81
        );

}


.game-cover-badge {

    position:
        absolute;

    z-index:
        3;

    top:
        10px;

    left:
        10px;

    display:
        inline-flex;

    padding:
        5px
        7px;

    border:
        1px
        solid
        rgba(
            255,
            255,
            255,
            .13
        );

    border-radius:
        999px;

    color:
        #dbeafe;

    background:
        rgba(
            2,
            6,
            23,
            .60
        );

    backdrop-filter:
        blur(
            8px
        );

    font-size:
        6px;

    font-weight:
        950;

}


/* ============================================================
   CARD BODY
============================================================ */

.game-card-body {

    display:
        flex;

    flex-direction:
        column;

    flex:
        1;

    padding:
        13px;

}


.game-card-body h3 {

    margin:
        0
        0
        5px;

    color:
        #f8fafc;

    font-size:
        17px;

    font-weight:
        950;

}


.game-card-body p {

    min-height:
        42px;

    margin:
        0;

    color:
        #64748b;

    font-size:
        8px;

    line-height:
        1.65;

}


/* ============================================================
   GAME META
============================================================ */

.game-meta {

    display:
        grid;

    grid-template-columns:
        repeat(
            3,
            1fr
        );

    gap:
        5px;

    margin-top:
        12px;

}


.game-meta-box {

    padding:
        7px
        6px;

    border:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .07
        );

    border-radius:
        8px;

    background:
        rgba(
            2,
            6,
            23,
            .34
        );

}


.game-meta-box span {

    display:
        block;

    color:
        #475569;

    font-size:
        5px;

}


.game-meta-box strong {

    display:
        block;

    margin-top:
        2px;

    color:
        #cbd5e1;

    font-size:
        7px;

}


.rating {

    color:
        #facc15 !important;

}


/* ============================================================
   CARD BUTTON
============================================================ */

.game-card-footer {

    margin-top:
        auto;

    padding-top:
        12px;

}


.game-card-button {

    width:
        100%;

    min-height:
        40px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .17
        );

    border-radius:
        10px;

    color:
        #ffffff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    text-decoration:
        none;

    font-size:
        8px;

    font-weight:
        950;

    cursor:
        pointer;

    transition:
        .20s
        ease;

}


.game-card-button:hover {

    transform:
        translateY(
            -1px
        );

    background:
        linear-gradient(
            135deg,
            #3b82f6,
            #6366f1
        );

}


.game-card-button.online {

    color:
        #67e8f9;

    background:
        rgba(
            6,
            182,
            212,
            .08
        );

    border-color:
        rgba(
            6,
            182,
            212,
            .14
        );

}


.game-card-button.online:hover {

    color:
        #ffffff;

    background:
        rgba(
            6,
            182,
            212,
            .16
        );

}


.game-card-button.playstore {

    color:
        #86efac;

    background:
        rgba(
            34,
            197,
            94,
            .08
        );

    border-color:
        rgba(
            34,
            197,
            94,
            .14
        );

}


.game-card-button.playstore:hover {

    color:
        #ffffff;

    background:
        rgba(
            34,
            197,
            94,
            .17
        );

}


/* ============================================================
   MOBILE DOWNLOAD STRIP
============================================================ */

.mobile-info-strip {

    display:
        grid;

    grid-template-columns:
        repeat(
            3,
            1fr
        );

    gap:
        7px;

    margin-bottom:
        16px;

}


.mobile-info {

    display:
        flex;

    align-items:
        center;

    gap:
        8px;

    padding:
        10px;

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
        11px;

    background:
        rgba(
            3,
            12,
            29,
            .70
        );

}


.mobile-info-icon {

    width:
        31px;

    height:
        31px;

    display:
        grid;

    place-items:
        center;

    border-radius:
        9px;

    background:
        rgba(
            37,
            99,
            235,
            .11
        );

    font-size:
        13px;

}


.mobile-info strong {

    display:
        block;

    color:
        #cbd5e1;

    font-size:
        7px;

}


.mobile-info small {

    display:
        block;

    margin-top:
        2px;

    color:
        #475569;

    font-size:
        5px;

}


/* ============================================================
   NO RESULTS
============================================================ */

.no-games {

    display:
        none;

    padding:
        50px
        20px;

    text-align:
        center;

    border:
        1px
        dashed
        rgba(
            96,
            165,
            250,
            .15
        );

    border-radius:
        16px;

    background:
        rgba(
            3,
            12,
            29,
            .60
        );

}


.no-games.show {

    display:
        block;

}


.no-games-icon {

    font-size:
        42px;

}


.no-games h3 {

    margin:
        9px
        0
        4px;

    color:
        #e2e8f0;

}


.no-games p {

    margin:
        0;

    color:
        #64748b;

    font-size:
        8px;

}


/* ============================================================
   MOBILE
============================================================ */

@media (
    max-width: 1000px
) {

    .games-grid {

        grid-template-columns:
            repeat(
                2,
                minmax(
                    0,
                    1fr
                )
            );

    }

}


@media (
    max-width: 750px
) {

    .games-hub {

        padding:
            18px
            10px
            60px;

    }


    .games-hero {

        min-height:
            285px;

        padding:
            28px
            20px;

    }


    .games-hero h1 {

        font-size:
            36px;

    }


    .games-controller {

        right:
            10px;

        bottom:
            8px;

        font-size:
            75px;

    }


    .games-toolbar {

        flex-direction:
            column;

        align-items:
            stretch;

    }


    .game-filters {

        flex-wrap:
            nowrap;

        overflow-x:
            auto;

        padding-bottom:
            2px;

    }


    .game-filter {

        flex:
            0
            0
            auto;

    }


    .games-grid {

        grid-template-columns:
            1fr;

    }


    .featured-content {

        flex-direction:
            column;

        align-items:
            flex-start;

    }


    .featured-play {

        width:
            100%;

    }


    .mobile-info-strip {

        grid-template-columns:
            1fr;

    }

}


@media (
    max-width: 480px
) {

    .games-hero {

        padding:
            24px
            16px;

    }


    .games-hero h1 {

        font-size:
            31px;

    }


    .games-hero-actions {

        flex-direction:
            column;

    }


    .hero-button {

        width:
            100%;

    }


    .featured-left {

        align-items:
            flex-start;

    }


    .featured-icon {

        width:
            61px;

        height:
            61px;

        flex-basis:
            61px;

        font-size:
            27px;

    }


    .game-cover {

        height:
            165px;

    }


    .game-meta {

        grid-template-columns:
            1fr
            1fr
            1fr;

    }

}

</style>


<div class="games-hub">


    <!-- ========================================================
         HERO
    ========================================================= -->

    <section
        class="games-hero"
    >


        <div
            class="games-controller"
        >
            🎮
        </div>


        <div
            class="games-hero-content"
        >


            <div
                class="games-eyebrow"
            >
                CONNECTHUB GAMING NETWORK
            </div>


            <h1>

                Play.
                <span>
                    Compete.
                </span>
                Discover.

            </h1>


            <p>

                Welcome to ConnectHub Games —
                one place for your original games,
                browser gaming experiences and
                official mobile game destinations.

            </p>


            <div
                class="games-hero-actions"
            >


                <a
                    href="#connecthub-games"
                    class="
                        hero-button
                        primary
                    "
                >
                    🎮 PLAY CONNECTHUB
                </a>


                <a
                    href="#online-games"
                    class="hero-button"
                >
                    🌐 ONLINE GAMES
                </a>


                <a
                    href="#mobile-games"
                    class="hero-button"
                >
                    📱 MOBILE GAMES
                </a>


            </div>


        </div>


    </section>


    <!-- ========================================================
         FEATURED GAME
    ========================================================= -->

    <section
        class="featured-game"
    >


        <div
            class="featured-content"
        >


            <div
                class="featured-left"
            >


                <div
                    class="featured-icon"
                >
                    🏎️
                </div>


                <div>

                    <span
                        class="games-eyebrow"
                    >
                        FEATURED MOBILE GAME
                    </span>


                    <h2>
                        Hill Climb Racing
                    </h2>


                    <p>
                        Open the official Google Play
                        listing and install the game on Android.
                    </p>

                </div>


            </div>


            <a
                href="https://play.google.com/store/apps/details?id=com.fingersoft.hillclimb&pcampaignid=web_share"
                class="featured-play"
                target="_blank"
                rel="noopener noreferrer"
            >
                ▶ OPEN GOOGLE PLAY
            </a>


        </div>


    </section>


    <!-- ========================================================
         SEARCH + FILTER
    ========================================================= -->

    <div
        class="games-toolbar"
    >


        <input
            type="search"
            id="gameSearch"
            class="game-search"
            placeholder="🔎 Search games..."
            autocomplete="off"
        >


        <div
            class="game-filters"
        >


            <button
                type="button"
                class="
                    game-filter
                    active
                "
                data-filter="all"
            >
                All
            </button>


            <button
                type="button"
                class="game-filter"
                data-filter="Arcade"
            >
                Arcade
            </button>


            <button
                type="button"
                class="game-filter"
                data-filter="Action"
            >
                Action
            </button>


            <button
                type="button"
                class="game-filter"
                data-filter="Racing"
            >
                Racing
            </button>


            <button
                type="button"
                class="game-filter"
                data-filter="Runner"
            >
                Runner
            </button>


            <button
                type="button"
                class="game-filter"
                data-filter="Puzzle"
            >
                Puzzle
            </button>


            <button
                type="button"
                class="game-filter"
                data-filter="Adventure"
            >
                Adventure
            </button>


        </div>


    </div>


    <!-- ========================================================
         CONNECTHUB ORIGINALS
    ========================================================= -->

    <section
        class="games-section"
        id="connecthub-games"
    >


        <div
            class="
                games-section-header
            "
        >


            <div
                class="
                    games-section-icon
                "
            >
                🎮
            </div>


            <div>

                <strong>
                    ConnectHub Originals
                </strong>

                <small>
                    Games created for your platform
                </small>

            </div>


        </div>


        <div
            class="games-grid"
        >


            <?php foreach (
                $connectHubGames
                as $game
            ): ?>


                <article
                    class="
                        game-card
                        searchable-game
                    "
                    data-name="<?= e(
                        strtolower(
                            $game["name"]
                        )
                    ) ?>"
                    data-category="<?= e(
                        $game["category"]
                    ) ?>"
                >


                    <div
                        class="
                            game-cover
                        "
                    >


                        <span
                            class="
                                game-cover-badge
                            "
                        >
                            CONNECTHUB
                        </span>


                        <?php

                        $gameImage =
                            trim(
                                $game["image"] ?? ""
                            );

                        ?>


                        <?php if (
                            $gameImage !== ""
                        ): ?>

                            <img
                                src="<?= e(
                                    $gameImage
                                ) ?>"
                                alt="<?= e(
                                    $game["name"]
                                ) ?>"
                                loading="lazy"
                                onerror="
                                    this.style.display='none';
                                    this.parentElement.querySelector('.game-cover-fallback').style.display='grid';
                                "
                            >


                            <div
                                class="
                                    game-cover-fallback
                                "
                                style="
                                    display:none;
                                "
                            >
                                <?= $game["icon"] ?>
                            </div>


                        <?php else: ?>

                            <div
                                class="
                                    game-cover-fallback
                                "
                            >
                                <?= $game["icon"] ?>
                            </div>

                        <?php endif; ?>


                    </div>


                    <div
                        class="
                            game-card-body
                        "
                    >


                        <h3>
                            <?= e(
                                $game["name"]
                            ) ?>
                        </h3>


                        <p>
                            <?= e(
                                $game["description"]
                            ) ?>
                        </p>


                        <div
                            class="
                                game-meta
                            "
                        >


                            <div
                                class="
                                    game-meta-box
                                "
                            >

                                <span>
                                    RATING
                                </span>

                                <strong
                                    class="rating"
                                >
                                    ⭐
                                    <?= e(
                                        $game["rating"]
                                    ) ?>
                                </strong>

                            </div>


                            <div
                                class="
                                    game-meta-box
                                "
                            >

                                <span>
                                    TYPE
                                </span>

                                <strong>
                                    HTML5
                                </strong>

                            </div>


                            <div
                                class="
                                    game-meta-box
                                "
                            >

                                <span>
                                    MODE
                                </span>

                                <strong>
                                    <?= e(
                                        $game["players"]
                                    ) ?>
                                </strong>

                            </div>


                        </div>


                        <div
                            class="
                                game-card-footer
                            "
                        >

                            <a
                                href="<?= e(
                                    $game["url"]
                                ) ?>"
                                class="
                                    game-card-button
                                "
                            >
                                ▶ PLAY NOW
                            </a>

                        </div>


                    </div>


                </article>


            <?php endforeach; ?>


        </div>


    </section>


    <!-- ========================================================
         ONLINE GAMES
    ========================================================= -->

    <section
        class="games-section"
        id="online-games"
    >


        <div
            class="
                games-section-header
            "
        >


            <div
                class="
                    games-section-icon
                "
            >
                🌐
            </div>


            <div>

                <strong>
                    Online Games
                </strong>

                <small>
                    Browser games and supported online platforms
                </small>

            </div>


        </div>


        <div
            class="games-grid"
        >


            <?php foreach (
                $onlineGames
                as $game
            ): ?>


                <article
                    class="
                        game-card
                        searchable-game
                    "
                    data-name="<?= e(
                        strtolower(
                            $game["name"]
                        )
                    ) ?>"
                    data-category="<?= e(
                        $game["category"]
                    ) ?>"
                >


                    <div
                        class="
                            game-cover
                        "
                    >


                        <span
                            class="
                                game-cover-badge
                            "
                        >
                            ONLINE
                        </span>


                        <?php

                        $gameImage =
                            trim(
                                $game["image"] ?? ""
                            );

                        ?>


                        <?php if (
                            $gameImage !== ""
                        ): ?>

                            <img
                                src="<?= e(
                                    $gameImage
                                ) ?>"
                                alt="<?= e(
                                    $game["name"]
                                ) ?>"
                                loading="lazy"
                                onerror="
                                    this.style.display='none';
                                    this.parentElement.querySelector('.game-cover-fallback').style.display='grid';
                                "
                            >


                            <div
                                class="
                                    game-cover-fallback
                                "
                                style="
                                    display:none;
                                "
                            >
                                <?= $game["icon"] ?>
                            </div>


                        <?php else: ?>

                            <div
                                class="
                                    game-cover-fallback
                                "
                            >
                                <?= $game["icon"] ?>
                            </div>

                        <?php endif; ?>


                    </div>


                    <div
                        class="
                            game-card-body
                        "
                    >


                        <h3>
                            <?= e(
                                $game["name"]
                            ) ?>
                        </h3>


                        <p>
                            <?= e(
                                $game["description"]
                            ) ?>
                        </p>


                        <div
                            class="
                                game-meta
                            "
                        >


                            <div
                                class="
                                    game-meta-box
                                "
                            >

                                <span>
                                    RATING
                                </span>

                                <strong
                                    class="rating"
                                >
                                    ⭐
                                    <?= e(
                                        $game["rating"]
                                    ) ?>
                                </strong>

                            </div>


                            <div
                                class="
                                    game-meta-box
                                "
                            >

                                <span>
                                    TYPE
                                </span>

                                <strong>
                                    WEB
                                </strong>

                            </div>


                            <div
                                class="
                                    game-meta-box
                                "
                            >

                                <span>
                                    ACCESS
                                </span>

                                <strong>
                                    ONLINE
                                </strong>

                            </div>


                        </div>


                        <div
                            class="
                                game-card-footer
                            "
                        >


                            <?php if (
                                $game["url"] !== "#"
                            ): ?>

                                <a
                                    href="<?= e(
                                        $game["url"]
                                    ) ?>"
                                    class="
                                        game-card-button
                                        online
                                    "
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    🌐 OPEN ONLINE
                                </a>

                            <?php else: ?>

                                <button
                                    type="button"
                                    class="
                                        game-card-button
                                        online
                                    "
                                    onclick="
                                        alert(
                                            'Add an approved online game URL to this card.'
                                        );
                                    "
                                >
                                    🌐 OPEN ONLINE
                                </button>

                            <?php endif; ?>


                        </div>


                    </div>


                </article>


            <?php endforeach; ?>


        </div>


    </section>


    <!-- ========================================================
         MOBILE GAMES
    ========================================================= -->

    <section
        class="games-section"
        id="mobile-games"
    >


        <div
            class="
                games-section-header
            "
        >


            <div
                class="
                    games-section-icon
                "
            >
                📱
            </div>


            <div>

                <strong>
                    Mobile Games
                </strong>

                <small>
                    Official Google Play destinations
                </small>

            </div>


        </div>


        <!-- ====================================================
             MOBILE INFO
        ===================================================== -->

        <div
            class="
                mobile-info-strip
            "
        >


            <div
                class="
                    mobile-info
                "
            >

                <div
                    class="
                        mobile-info-icon
                    "
                >
                    📱
                </div>

                <div>

                    <strong>
                        Android
                    </strong>

                    <small>
                        Mobile gaming
                    </small>

                </div>

            </div>


            <div
                class="
                    mobile-info
                "
            >

                <div
                    class="
                        mobile-info-icon
                    "
                >
                    ▶
                </div>

                <div>

                    <strong>
                        Official Store
                    </strong>

                    <small>
                        Google Play
                    </small>

                </div>

            </div>


            <div
                class="
                    mobile-info
                "
            >

                <div
                    class="
                        mobile-info-icon
                    "
                >
                    🔗
                </div>

                <div>

                    <strong>
                        Direct Link
                    </strong>

                    <small>
                        Open listing
                    </small>

                </div>

            </div>


        </div>


        <div
            class="games-grid"
        >


            <?php foreach (
                $playStoreGames
                as $game
            ): ?>


                <article
                    class="
                        game-card
                        searchable-game
                    "
                    data-name="<?= e(
                        strtolower(
                            $game["name"]
                        )
                    ) ?>"
                    data-category="<?= e(
                        $game["category"]
                    ) ?>"
                >


                    <div
                        class="
                            game-cover
                        "
                    >


                        <span
                            class="
                                game-cover-badge
                            "
                        >
                            GOOGLE PLAY
                        </span>


                        <?php

                        $gameImage =
                            trim(
                                $game["image"] ?? ""
                            );

                        ?>


                        <?php if (
                            $gameImage !== ""
                        ): ?>

                            <img
                                src="<?= e(
                                    $gameImage
                                ) ?>"
                                alt="<?= e(
                                    $game["name"]
                                ) ?>"
                                loading="lazy"
                                onerror="
                                    this.style.display='none';
                                    this.parentElement.querySelector('.game-cover-fallback').style.display='grid';
                                "
                            >


                            <div
                                class="
                                    game-cover-fallback
                                "
                                style="
                                    display:none;
                                "
                            >
                                <?= $game["icon"] ?>
                            </div>


                        <?php else: ?>

                            <div
                                class="
                                    game-cover-fallback
                                "
                            >
                                <?= $game["icon"] ?>
                            </div>

                        <?php endif; ?>


                    </div>


                    <div
                        class="
                            game-card-body
                        "
                    >


                        <h3>
                            <?= e(
                                $game["name"]
                            ) ?>
                        </h3>


                        <p>
                            <?= e(
                                $game["description"]
                            ) ?>
                        </p>


                        <div
                            class="
                                game-meta
                            "
                        >


                            <div
                                class="
                                    game-meta-box
                                "
                            >

                                <span>
                                    RATING
                                </span>

                                <strong
                                    class="rating"
                                >
                                    ⭐
                                    <?= e(
                                        $game["rating"]
                                    ) ?>
                                </strong>

                            </div>


                            <div
                                class="
                                    game-meta-box
                                "
                            >

                                <span>
                                    PLATFORM
                                </span>

                                <strong>
                                    Android
                                </strong>

                            </div>


                            <div
                                class="
                                    game-meta-box
                                "
                            >

                                <span>
                                    STORE
                                </span>

                                <strong>
                                    Play
                                </strong>

                            </div>


                        </div>


                        <div
                            class="
                                game-card-footer
                            "
                        >


                            <a
                                href="<?= e(
                                    $game["url"]
                                ) ?>"
                                class="
                                    game-card-button
                                    playstore
                                "
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                ▶ OPEN GOOGLE PLAY
                            </a>


                        </div>


                    </div>


                </article>


            <?php endforeach; ?>


        </div>


    </section>


    <!-- ========================================================
         NO RESULTS
    ========================================================= -->

    <div
        id="noGames"
        class="no-games"
    >

        <div
            class="no-games-icon"
        >
            🎮
        </div>


        <h3>
            No Games Found
        </h3>


        <p>
            Try another search term or category.
        </p>

    </div>


</div>


<script>

/* ============================================================
   CONNECTHUB GAMES SEARCH + FILTER
============================================================ */

(function () {

    "use strict";


    const search =
        document.getElementById(
            "gameSearch"
        );


    const filters =
        document.querySelectorAll(
            ".game-filter"
        );


    const cards =
        document.querySelectorAll(
            ".searchable-game"
        );


    const noGames =
        document.getElementById(
            "noGames"
        );


    let selectedCategory =
        "all";


    /* ========================================================
       UPDATE GAMES
    ======================================================== */

    function updateGames() {

        const query =
            search
                ? search.value
                    .trim()
                    .toLowerCase()
                : "";


        let visibleCount =
            0;


        cards.forEach(
            function (card) {

                const name =
                    (
                        card.getAttribute(
                            "data-name"
                        )
                        ||
                        ""
                    )
                    .toLowerCase();


                const category =
                    (
                        card.getAttribute(
                            "data-category"
                        )
                        ||
                        ""
                    )
                    .toLowerCase();


                const categoryMatch =
                    selectedCategory ===
                    "all"
                    ||
                    category ===
                    selectedCategory.toLowerCase();


                const searchMatch =
                    query === ""
                    ||
                    name.includes(
                        query
                    );


                if (
                    categoryMatch &&
                    searchMatch
                ) {

                    card.style.display =
                        "";

                    visibleCount++;

                } else {

                    card.style.display =
                        "none";

                }

            }
        );


        if (
            noGames
        ) {

            noGames.classList.toggle(
                "show",
                visibleCount === 0
            );

        }

    }


    /* ========================================================
       SEARCH
    ======================================================== */

    if (
        search
    ) {

        search.addEventListener(
            "input",
            updateGames
        );

    }


    /* ========================================================
       FILTERS
    ======================================================== */

    filters.forEach(
        function (filter) {

            filter.addEventListener(
                "click",
                function () {


                    filters.forEach(
                        function (item) {

                            item.classList.remove(
                                "active"
                            );

                        }
                    );


                    filter.classList.add(
                        "active"
                    );


                    selectedCategory =
                        filter.getAttribute(
                            "data-filter"
                        )
                        ||
                        "all";


                    updateGames();

                }
            );

        }
    );


    /* ========================================================
       CARD ANIMATION
    ======================================================== */

    cards.forEach(
        function (
            card,
            index
        ) {

            card.style.opacity =
                "0";


            card.style.transform =
                "translateY(12px)";


            setTimeout(
                function () {

                    card.style.transition =
                        "opacity .45s ease, transform .45s ease";


                    card.style.opacity =
                        "1";


                    card.style.transform =
                        "translateY(0)";

                },
                80 +
                (
                    index *
                    45
                )
            );

        }
    );


    /* ========================================================
       BUTTON CLICK EFFECT
    ======================================================== */

    document.addEventListener(
        "pointerdown",
        function (event) {

            const target =
                event.target.closest(
                    "button,a"
                );


            if (
                !target
            ) {

                return;

            }


            target.style.transform =
                "scale(.98)";


            setTimeout(
                function () {

                    target.style.transform =
                        "";

                },
                120
            );

        },
        {
            passive:
                true
        }
    );


})();

</script>


<?php

require "footer.php";

?>