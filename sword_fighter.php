<?php
// ============================================================
// CONNECTHUB - SWORD FIGHTER
// FULL WORKING IMAGE-BASED VERSION
// ============================================================
//
// Works with your extracted folder:
//
// C:\xampp\htdocs\connecthub\game-assets\sword\
//     connecthub_sword_fighter_assets\
//         sword_fighter_assets\
//             player
//             enemy
//             effects
//             backgrounds
//             ui
//
// Also supports:
//
// C:\xampp\htdocs\connecthub\game-assets\sword\
//     player
//     enemy
//     effects
//     backgrounds
//
// The game has procedural fallbacks so it will not become blank
// even if an image is missing.
//
// ============================================================

require "config.php";

login_required();

require "header.php";

?>

<style>

/* ============================================================
   PAGE
============================================================ */

.sf-page {

    width:100%;
    min-height:calc(100vh - 68px);

    padding:
        14px
        10px
        70px;

    display:flex;
    flex-direction:column;
    align-items:center;

    color:#fff;

}


/* ============================================================
   TOP BAR
============================================================ */

.sf-topbar {

    width:100%;
    max-width:1250px;

    display:flex;
    align-items:center;
    justify-content:space-between;

    gap:12px;

    margin-bottom:10px;

}


.sf-brand {

    display:flex;
    align-items:center;
    gap:10px;

}


.sf-brand-icon {

    width:48px;
    height:48px;

    display:grid;
    place-items:center;

    border:
        1px solid
        rgba(96,165,250,.22);

    border-radius:14px;

    background:
        linear-gradient(
            135deg,
            #172554,
            #2563eb,
            #4f46e5
        );

    box-shadow:
        0 0 28px
        rgba(37,99,235,.23);

    font-size:24px;

}


.sf-brand-text strong {

    display:block;

    color:#fff;

    font-size:20px;
    font-weight:1000;

}


.sf-brand-text small {

    display:block;

    margin-top:2px;

    color:#7186a6;

    font-size:7px;

    letter-spacing:1.3px;

}


.sf-actions {

    display:flex;
    gap:6px;

}


.sf-ui-btn {

    min-height:37px;

    padding:0 12px;

    border:
        1px solid
        rgba(96,165,250,.14);

    border-radius:10px;

    color:#dbeafe;

    background:
        rgba(3,12,29,.78);

    font-size:7px;
    font-weight:950;

    cursor:pointer;

    transition:.2s ease;

}


.sf-ui-btn:hover {

    transform:translateY(-2px);

    border-color:
        rgba(96,165,250,.30);

    background:
        rgba(37,99,235,.16);

}


.sf-ui-btn.restart {

    color:#fecaca;

}


/* ============================================================
   GAME FRAME
============================================================ */

.sf-frame {

    position:relative;

    width:100%;
    max-width:1250px;

    aspect-ratio:16 / 9;

    min-height:520px;

    overflow:hidden;

    border:
        1px solid
        rgba(96,165,250,.20);

    border-radius:22px;

    background:#020617;

    box-shadow:

        0 25px 75px
        rgba(0,0,0,.45),

        0 0 45px
        rgba(37,99,235,.07);

}


/* ============================================================
   CANVAS
============================================================ */

#sfCanvas {

    width:100%;
    height:100%;

    display:block;

    background:#020617;

}


/* ============================================================
   HUD
============================================================ */

.sf-hud {

    position:absolute;

    top:12px;
    left:12px;
    right:12px;

    z-index:20;

    display:flex;
    justify-content:space-between;

    pointer-events:none;

}


.sf-left-hud {

    display:flex;
    align-items:center;
    gap:8px;

}


.sf-avatar {

    width:44px;
    height:44px;

    display:grid;
    place-items:center;

    border:
        1px solid
        rgba(96,165,250,.20);

    border-radius:12px;

    background:
        rgba(2,6,23,.75);

    backdrop-filter:blur(8px);

    font-size:22px;

}


.sf-bars {

    width:260px;

}


.sf-name {

    color:#fff;

    font-size:9px;
    font-weight:950;

}


.sf-level {

    margin-top:2px;

    color:#7186a6;

    font-size:6px;

}


.sf-health {

    width:100%;
    height:9px;

    margin-top:4px;

    overflow:hidden;

    border-radius:999px;

    background:
        rgba(2,6,23,.85);

    border:
        1px solid
        rgba(255,255,255,.06);

}


.sf-health-fill {

    width:100%;
    height:100%;

    background:
        linear-gradient(
            90deg,
            #16a34a,
            #22c55e,
            #4ade80
        );

    transition:width .15s ease;

}


.sf-stamina {

    width:100%;
    height:5px;

    margin-top:3px;

    overflow:hidden;

    border-radius:999px;

    background:
        rgba(2,6,23,.85);

}


.sf-stamina-fill {

    width:100%;
    height:100%;

    background:
        linear-gradient(
            90deg,
            #0891b2,
            #22d3ee,
            #38bdf8
        );

    transition:width .12s ease;

}


/* ============================================================
   SCORE
============================================================ */

.sf-score {

    text-align:right;

}


.sf-score-label {

    color:#7186a6;

    font-size:6px;

    font-weight:950;

    letter-spacing:1.4px;

}


.sf-score-number {

    margin-top:2px;

    color:#fff;

    font-size:21px;

    font-weight:1000;

}


.sf-combo-text {

    margin-top:2px;

    color:#60a5fa;

    font-size:8px;

    font-weight:950;

}


/* ============================================================
   BOSS HUD
============================================================ */

.sf-boss {

    position:absolute;

    top:12px;
    left:50%;

    transform:translateX(-50%);

    width:min(380px,45%);

    opacity:0;

    z-index:22;

    pointer-events:none;

}


.sf-boss.visible {

    opacity:1;

}


.sf-boss-title {

    text-align:center;

    color:#fca5a5;

    font-size:7px;

    font-weight:1000;

    letter-spacing:2px;

}


.sf-boss-bar {

    width:100%;
    height:10px;

    margin-top:4px;

    overflow:hidden;

    border-radius:999px;

    background:#180708;

    border:
        1px solid
        rgba(248,113,113,.20);

}


.sf-boss-fill {

    width:100%;
    height:100%;

    background:
        linear-gradient(
            90deg,
            #991b1b,
            #ef4444,
            #f87171
        );

    transition:width .15s ease;

}


/* ============================================================
   LEVEL BAR
============================================================ */

.sf-level-bar {

    position:absolute;

    left:50%;
    bottom:12px;

    transform:translateX(-50%);

    z-index:18;

    min-width:185px;

    padding:
        6px
        11px;

    text-align:center;

    border:
        1px solid
        rgba(96,165,250,.12);

    border-radius:999px;

    color:#93c5fd;

    background:
        rgba(2,6,23,.62);

    backdrop-filter:blur(8px);

    font-size:6px;
    font-weight:950;

}


/* ============================================================
   COMBO
============================================================ */

.sf-combo-popup {

    position:absolute;

    left:50%;
    top:49%;

    z-index:25;

    transform:
        translate(
            -50%,
            -50%
        );

    pointer-events:none;

    opacity:0;

    color:#fff;

    font-size:42px;
    font-weight:1000;

    text-shadow:
        0 0 28px
        rgba(96,165,250,.60);

}


.sf-combo-popup.show {

    animation:
        comboPopup
        .65s
        ease-out
        forwards;

}


@keyframes comboPopup {

    0% {

        opacity:0;

        transform:
            translate(
                -50%,
                -50%
            )
            scale(.45);

    }

    20% {

        opacity:1;

    }

    100% {

        opacity:0;

        transform:
            translate(
                -50%,
                -90%
            )
            scale(1.18);

    }

}


/* ============================================================
   FLASH
============================================================ */

.sf-flash {

    position:absolute;

    inset:0;

    z-index:28;

    pointer-events:none;

    background:#ffffff;

    opacity:0;

}


.sf-flash.show {

    animation:
        flashEffect
        .15s
        ease-out
        forwards;

}


@keyframes flashEffect {

    0% {
        opacity:.30;
    }

    100% {
        opacity:0;
    }

}


/* ============================================================
   OVERLAY
============================================================ */

.sf-overlay {

    position:absolute;

    inset:0;

    z-index:50;

    display:none;

    align-items:center;

    justify-content:center;

    background:
        rgba(
            2,
            6,
            23,
            .72
        );

    backdrop-filter:
        blur(8px);

}


.sf-overlay.show {

    display:flex;

}


.sf-overlay-card {

    width:
        min(
            420px,
            calc(
                100% - 30px
            )
        );

    padding:28px;

    text-align:center;

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .18
        );

    border-radius:20px;

    background:
        linear-gradient(
            145deg,
            rgba(
                3,
                12,
                29,
                .98
            ),
            rgba(
                8,
                25,
                55,
                .96
            )
        );

    box-shadow:
        0 25px 70px
        rgba(
            0,
            0,
            0,
            .50
        );

}


.sf-overlay-icon {

    width:70px;
    height:70px;

    margin:
        0
        auto
        12px;

    display:grid;

    place-items:center;

    border-radius:20px;

    background:
        rgba(
            37,
            99,
            235,
            .15
        );

    font-size:30px;

}


.sf-overlay-card h2 {

    margin:
        0
        0
        6px;

    color:#fff;

    font-size:26px;

}


.sf-overlay-card p {

    margin:
        0
        0
        20px;

    color:#7186a6;

    font-size:8px;

    line-height:1.7;

}


.sf-overlay-button {

    min-height:42px;

    padding:
        0
        17px;

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .18
        );

    border-radius:10px;

    color:#fff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    font-size:8px;

    font-weight:950;

    cursor:pointer;

}


/* ============================================================
   MOBILE CONTROLS
============================================================ */

.sf-mobile {

    position:absolute;

    left:0;
    right:0;
    bottom:20px;

    z-index:24;

    display:none;

    justify-content:space-between;

    padding:
        0
        14px;

    pointer-events:none;

}


.sf-mobile-left,
.sf-mobile-right {

    display:flex;
    gap:7px;

}


.sf-mobile-btn {

    width:53px;
    height:53px;

    display:grid;
    place-items:center;

    border:
        1px solid
        rgba(
            147,
            197,
            253,
            .18
        );

    border-radius:15px;

    color:#fff;

    background:
        rgba(
            2,
            6,
            23,
            .72
        );

    backdrop-filter:blur(8px);

    font-size:17px;

    pointer-events:auto;

    user-select:none;

    -webkit-user-select:none;

    touch-action:none;

}


.sf-mobile-btn.attack {

    width:70px;
    height:70px;

    border-color:
        rgba(
            96,
            165,
            250,
            .28
        );

    background:
        linear-gradient(
            135deg,
            rgba(
                37,
                99,
                235,
                .92
            ),
            rgba(
                79,
                70,
                229,
                .92
            )
        );

    box-shadow:
        0
        0
        25px
        rgba(
            37,
            99,
            235,
            .18
        );

}


/* ============================================================
   INFO
============================================================ */

.sf-info {

    width:100%;
    max-width:1250px;

    margin-top:9px;

    display:grid;

    grid-template-columns:
        repeat(
            5,
            1fr
        );

    gap:6px;

}


.sf-info-card {

    padding:
        9px
        10px;

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .08
        );

    border-radius:11px;

    background:
        rgba(
            3,
            12,
            29,
            .70
        );

}


.sf-info-card span {

    display:block;

    color:#475569;

    font-size:5px;

}


.sf-info-card strong {

    display:block;

    margin-top:2px;

    color:#cbd5e1;

    font-size:7px;

}


/* ============================================================
   RESPONSIVE
============================================================ */

@media (
    max-width:900px
) {

    .sf-frame {

        aspect-ratio:
            4 / 5;

        min-height:
            570px;

    }


    .sf-mobile {

        display:flex;

    }


    .sf-info {

        grid-template-columns:
            repeat(
                2,
                1fr
            );

    }


    .sf-bars {

        width:210px;

    }


    .sf-boss {

        width:225px;

    }

}


@media (
    max-width:600px
) {

    .sf-page {

        padding:
            9px
            5px
            45px;

    }


    .sf-brand-text strong {

        font-size:16px;

    }


    .sf-brand-text small {

        font-size:5px;

    }


    .sf-brand-icon {

        width:40px;
        height:40px;

        font-size:20px;

    }


    .sf-ui-btn {

        min-height:33px;

        padding:
            0
            8px;

        font-size:6px;

    }


    .sf-frame {

        aspect-ratio:
            9 / 16;

        min-height:
            660px;

        border-radius:16px;

    }


    .sf-bars {

        width:155px;

    }


    .sf-avatar {

        width:37px;
        height:37px;

        font-size:18px;

    }


    .sf-name {

        font-size:7px;

    }


    .sf-score-number {

        font-size:17px;

    }


    .sf-boss {

        width:180px;

    }


    .sf-combo-popup {

        font-size:31px;

    }

}


</style>


<div class="sf-page">


    <!-- ========================================================
         GAME HEADER
    ========================================================= -->

    <div class="sf-topbar">


        <div class="sf-brand">


            <div class="sf-brand-icon">
                ⚔️
            </div>


            <div class="sf-brand-text">

                <strong>
                    Sword Fighter
                </strong>

                <small>
                    CONNECTHUB ORIGINAL • BATTLE ARENA
                </small>

            </div>


        </div>


        <div class="sf-actions">


            <button
                type="button"
                class="sf-ui-btn"
                id="sfPauseButton"
            >
                ⏸ PAUSE
            </button>


            <button
                type="button"
                class="
                    sf-ui-btn
                    restart
                "
                id="sfRestartButton"
            >
                ↻ RESTART
            </button>


        </div>


    </div>


    <!-- ========================================================
         GAME
    ========================================================= -->

    <div
        class="sf-frame"
        id="sfFrame"
    >


        <canvas
            id="sfCanvas"
        ></canvas>


        <!-- ====================================================
             HUD
        ===================================================== -->

        <div class="sf-hud">


            <div class="sf-left-hud">


                <div class="sf-avatar">
                    ⚔️
                </div>


                <div class="sf-bars">


                    <div class="sf-name">
                        CONNECTHUB HERO
                    </div>


                    <div
                        class="sf-level"
                        id="sfPlayerLevel"
                    >
                        LEVEL 01
                    </div>


                    <div class="sf-health">

                        <div
                            class="sf-health-fill"
                            id="sfHealth"
                        ></div>

                    </div>


                    <div class="sf-stamina">

                        <div
                            class="sf-stamina-fill"
                            id="sfStamina"
                        ></div>

                    </div>


                </div>


            </div>


            <div class="sf-score">


                <div class="sf-score-label">
                    SCORE
                </div>


                <div
                    class="sf-score-number"
                    id="sfScore"
                >
                    000000
                </div>


                <div
                    class="sf-combo-text"
                    id="sfCombo"
                >
                    COMBO x0
                </div>


            </div>


        </div>


        <!-- ====================================================
             BOSS
        ===================================================== -->

        <div
            class="sf-boss"
            id="sfBoss"
        >


            <div class="sf-boss-title">
                ☠ SHADOW BOSS ☠
            </div>


            <div class="sf-boss-bar">

                <div
                    class="sf-boss-fill"
                    id="sfBossFill"
                ></div>

            </div>


        </div>


        <!-- ====================================================
             LEVEL BAR
        ===================================================== -->

        <div
            class="sf-level-bar"
            id="sfLevelBar"
        >
            LEVEL 01 • ENEMIES 0/0
        </div>


        <!-- ====================================================
             COMBO POPUP
        ===================================================== -->

        <div
            class="sf-combo-popup"
            id="sfComboPopup"
        >
            COMBO x0
        </div>


        <!-- ====================================================
             FLASH
        ===================================================== -->

        <div
            class="sf-flash"
            id="sfFlash"
        ></div>


        <!-- ====================================================
             OVERLAY
        ===================================================== -->

        <div
            class="sf-overlay"
            id="sfOverlay"
        >


            <div class="sf-overlay-card">


                <div
                    class="sf-overlay-icon"
                    id="sfOverlayIcon"
                >
                    ⏸
                </div>


                <h2
                    id="sfOverlayTitle"
                >
                    Game Paused
                </h2>


                <p
                    id="sfOverlayText"
                >
                    The battle is paused.
                </p>


                <button
                    type="button"
                    class="sf-overlay-button"
                    id="sfOverlayButton"
                >
                    ▶ RESUME
                </button>


            </div>


        </div>


        <!-- ====================================================
             MOBILE CONTROLS
        ===================================================== -->

        <div class="sf-mobile">


            <div class="sf-mobile-left">


                <button
                    type="button"
                    class="sf-mobile-btn"
                    data-action="left"
                >
                    ◀
                </button>


                <button
                    type="button"
                    class="sf-mobile-btn"
                    data-action="right"
                >
                    ▶
                </button>


                <button
                    type="button"
                    class="sf-mobile-btn"
                    data-action="jump"
                >
                    ▲
                </button>


            </div>


            <div class="sf-mobile-right">


                <button
                    type="button"
                    class="sf-mobile-btn"
                    data-action="special"
                >
                    ✦
                </button>


                <button
                    type="button"
                    class="
                        sf-mobile-btn
                        attack
                    "
                    data-action="attack"
                >
                    ⚔️
                </button>


            </div>


        </div>


    </div>


    <!-- ========================================================
         INSTRUCTIONS
    ========================================================= -->

    <div class="sf-info">


        <div class="sf-info-card">

            <span>
                MOVE
            </span>

            <strong>
                A / D • ← / →
            </strong>

        </div>


        <div class="sf-info-card">

            <span>
                JUMP
            </span>

            <strong>
                W • ↑ • SPACE
            </strong>

        </div>


        <div class="sf-info-card">

            <span>
                ATTACK
            </span>

            <strong>
                J • CLICK
            </strong>

        </div>


        <div class="sf-info-card">

            <span>
                SPECIAL
            </span>

            <strong>
                K • ✦
            </strong>

        </div>


        <div class="sf-info-card">

            <span>
                PAUSE
            </span>

            <strong>
                P
            </strong>

        </div>


    </div>


</div>


<script>

/* ============================================================
   CONNECTHUB SWORD FIGHTER ENGINE
============================================================ */

(function () {

    "use strict";


    /* ========================================================
       DOM
    ======================================================== */

    const canvas =
        document.getElementById(
            "sfCanvas"
        );


    if (!canvas) {
        return;
    }


    const ctx =
        canvas.getContext(
            "2d"
        );


    if (!ctx) {
        return;
    }


    const healthBar =
        document.getElementById(
            "sfHealth"
        );


    const staminaBar =
        document.getElementById(
            "sfStamina"
        );


    const scoreElement =
        document.getElementById(
            "sfScore"
        );


    const comboElement =
        document.getElementById(
            "sfCombo"
        );


    const levelElement =
        document.getElementById(
            "sfPlayerLevel"
        );


    const bossElement =
        document.getElementById(
            "sfBoss"
        );


    const bossFill =
        document.getElementById(
            "sfBossFill"
        );


    const levelBar =
        document.getElementById(
            "sfLevelBar"
        );


    const comboPopup =
        document.getElementById(
            "sfComboPopup"
        );


    const flash =
        document.getElementById(
            "sfFlash"
        );


    const pauseButton =
        document.getElementById(
            "sfPauseButton"
        );


    const restartButton =
        document.getElementById(
            "sfRestartButton"
        );


    const overlay =
        document.getElementById(
            "sfOverlay"
        );


    const overlayIcon =
        document.getElementById(
            "sfOverlayIcon"
        );


    const overlayTitle =
        document.getElementById(
            "sfOverlayTitle"
        );


    const overlayText =
        document.getElementById(
            "sfOverlayText"
        );


    const overlayButton =
        document.getElementById(
            "sfOverlayButton"
        );


    /* ========================================================
       CANVAS STATE
    ======================================================== */

    let canvasWidth = 1200;

    let canvasHeight = 675;

    let groundY = 560;


    /* ========================================================
       GAME STATE
    ======================================================== */

    let gameTime = 0;

    let lastTime = 0;

    let cameraX = 0;

    let screenShake = 0;

    let paused = false;

    let gameOver = false;

    let victory = false;

    let level = 1;

    let score = 0;

    let combo = 0;

    let comboTimer = 0;

    let levelIntroTimer = 1.2;


    /* ========================================================
       INPUT
    ======================================================== */

    const keys = {};

    const touch = {

        left:false,

        right:false

    };


    document.addEventListener(
        "keydown",
        function (event) {

            const key =
                String(
                    event.key
                ).toLowerCase();


            keys[key] =
                true;


            if (
                key ===
                " "
            ) {

                event.preventDefault();

            }


            if (
                key ===
                "p"
            ) {

                togglePause();

            }

        }
    );


    document.addEventListener(
        "keyup",
        function (event) {

            const key =
                String(
                    event.key
                ).toLowerCase();


            keys[key] =
                false;

        }
    );


    /* ========================================================
       PLAYER
       MUST EXIST BEFORE resizeCanvas()
    ======================================================== */

    const player = {

        x:260,

        y:0,

        width:70,

        height:125,

        vx:0,

        vy:0,

        speed:340,

        jumpPower:720,

        health:100,

        maxHealth:100,

        stamina:100,

        maxStamina:100,

        facing:1,

        grounded:true,

        state:"idle",

        stateTime:0,

        attackTime:0,

        attackCooldown:0,

        attackStep:1,

        attackHit:false,

        specialTime:0,

        hurtTime:0,

        invulnerable:0,

        deathTime:0,

        dead:false

    };


    /* ========================================================
       RESIZE
    ======================================================== */

    function resizeCanvas() {

        const rect =
            canvas.getBoundingClientRect();


        const dpr =
            Math.min(
                window.devicePixelRatio ||
                1,
                2
            );


        canvasWidth =
            Math.max(
                640,
                Math.floor(
                    rect.width *
                    dpr
                )
            );


        canvasHeight =
            Math.max(
                420,
                Math.floor(
                    rect.height *
                    dpr
                )
            );


        canvas.width =
            canvasWidth;


        canvas.height =
            canvasHeight;


        groundY =
            canvasHeight *
            0.83;


        if (
            player
        ) {

            player.y =
                Math.min(
                    player.y,
                    groundY -
                    player.height
                );

        }

    }


    window.addEventListener(
        "resize",
        resizeCanvas
    );


    resizeCanvas();


    player.y =
        groundY -
        player.height;


    /* ========================================================
       ASSET ROOTS
    ======================================================== */

    const assetRoots = [

        "game-assets/sword/connecthub_sword_fighter_assets/sword_fighter_assets/",

        "game-assets/sword/"

    ];


    /* ========================================================
       IMAGE STORE
    ======================================================== */

    const images = {};

    const imageCanvases = {};

    const imageStates = {};


    /* ========================================================
       ASSET DEFINITIONS
    ======================================================== */

    const assets = {

        hero_idle:
            [
                "player",
                "hero_idle"
            ],

        hero_run:
            [
                "player",
                "hero_run"
            ],

        hero_jump:
            [
                "player",
                "hero_jump"
            ],

        hero_attack1:
            [
                "player",
                "hero_attack1"
            ],

        hero_attack2:
            [
                "player",
                "hero_attack2"
            ],

        hero_attack3:
            [
                "player",
                "hero_attack3"
            ],

        hero_special:
            [
                "player",
                "hero_special"
            ],

        hero_hurt:
            [
                "player",
                "hero_hurt"
            ],

        hero_block:
            [
                "player",
                "hero_block"
            ],

        hero_land:
            [
                "player",
                "hero_land"
            ],

        hero_dash:
            [
                "player",
                "hero_dash"
            ],

        hero_death:
            [
                "player",
                "hero_death"
            ],


        soldier_idle:
            [
                "enemy",
                "soldier_idle"
            ],

        soldier_run:
            [
                "enemy",
                "soldier_run"
            ],

        soldier_attack:
            [
                "enemy",
                "soldier_attack"
            ],

        soldier_hurt:
            [
                "enemy",
                "soldier_hurt"
            ],

        soldier_death:
            [
                "enemy",
                "soldier_death"
            ],


        boss_idle:
            [
                "enemy",
                "boss_idle"
            ],

        boss_attack:
            [
                "enemy",
                "boss_attack"
            ],

        boss_hurt:
            [
                "enemy",
                "boss_hurt"
            ],

        boss_death:
            [
                "enemy",
                "boss_death"
            ],


        slash1:
            [
                "effects",
                "slash1"
            ],

        slash2:
            [
                "effects",
                "slash2"
            ],

        slash3:
            [
                "effects",
                "slash3"
            ],

        slash4:
            [
                "effects",
                "slash4"
            ],

        slash5:
            [
                "effects",
                "slash5"
            ],

        special_wave:
            [
                "effects",
                "special_wave"
            ],

        special_burst:
            [
                "effects",
                "special_burst"
            ],

        dash_effect:
            [
                "effects",
                "dash_effect"
            ],

        impact:
            [
                "effects",
                "impact"
            ],

        critical_hit:
            [
                "effects",
                "critical_hit"
            ],

        sparks:
            [
                "effects",
                "sparks"
            ],

        fire:
            [
                "effects",
                "fire"
            ],

        ice:
            [
                "effects",
                "ice"
            ],

        dust:
            [
                "effects",
                "dust"
            ],

        blood:
            [
                "effects",
                "blood"
            ],

        energy:
            [
                "effects",
                "energy"
            ],

        leaves:
            [
                "effects",
                "leaves"
            ],

        light:
            [
                "effects",
                "light"
            ],

        smoke:
            [
                "effects",
                "smoke"
            ],

        magic:
            [
                "effects",
                "magic"
            ],


        night_background:
            [
                "backgrounds",
                "background_night"
            ],

        volcano_background:
            [
                "backgrounds",
                "background_volcano"
            ],

        forest_background:
            [
                "backgrounds",
                "background_forest"
            ]

    };


    /* ========================================================
       LOAD IMAGE
    ======================================================== */

    function loadAsset(
        key
    ) {

        const definition =
            assets[key];


        if (
            !definition
        ) {

            return;

        }


        if (
            imageStates[key] ===
            "loading"
        ) {

            return;

        }


        imageStates[key] =
            "loading";


        let rootIndex =
            0;


        function tryRoot() {

            if (
                rootIndex >=
                assetRoots.length
            ) {

                imageStates[key] =
                    "error";

                return;

            }


            const src =
                assetRoots[
                    rootIndex
                ] +
                definition[0] +
                "/" +
                definition[1] +
                ".png";


            rootIndex++;


            const img =
                new Image();


            img.onload =
                function () {

                    images[key] =
                        img;

                    imageStates[key] =
                        "ready";

                    prepareTransparentImage(
                        key
                    );

                };


            img.onerror =
                function () {

                    tryRoot();

                };


            img.src =
                src;

        }


        tryRoot();

    }


    Object.keys(
        assets
    ).forEach(
        function (key) {

            loadAsset(
                key
            );

        }
    );


    /* ========================================================
       REMOVE BLACK BACKGROUND
    ======================================================== */

    function prepareTransparentImage(
        key
    ) {

        const img =
            images[key];


        if (
            !img
        ) {

            return;

        }


        try {

            const off =
                document.createElement(
                    "canvas"
                );


            off.width =
                img.naturalWidth;


            off.height =
                img.naturalHeight;


            const offCtx =
                off.getContext(
                    "2d"
                );


            offCtx.drawImage(
                img,
                0,
                0
            );


            const imageData =
                offCtx.getImageData(
                    0,
                    0,
                    off.width,
                    off.height
                );


            const data =
                imageData.data;


            for (
                let i = 0;
                i < data.length;
                i += 4
            ) {

                const r =
                    data[i];

                const g =
                    data[i + 1];

                const b =
                    data[i + 2];


                /*
                 * Only remove very dark pixels.
                 * This keeps the blue/red artwork.
                 */

                if (
                    r < 18 &&
                    g < 18 &&
                    b < 18
                ) {

                    data[i + 3] =
                        0;

                }

            }


            offCtx.putImageData(
                imageData,
                0,
                0
            );


            imageCanvases[key] =
                off;

        } catch (
            error
        ) {

            imageCanvases[key] =
                null;

        }

    }


    /* ========================================================
       HELPERS
    ======================================================== */

    function clamp(
        value,
        min,
        max
    ) {

        return Math.max(
            min,
            Math.min(
                max,
                value
            )
        );

    }


    function random(
        min,
        max
    ) {

        return min +
            Math.random() *
            (
                max -
                min
            );

    }


    function distance(
        a,
        b
    ) {

        return Math.abs(
            a -
            b
        );

    }


    /* ========================================================
       EFFECTS
    ======================================================== */

    const effects = [];


    function addEffect(
        type,
        x,
        y,
        options = {}
    ) {

        effects.push({

            type:type,

            x:x,

            y:y,

            life:
                options.life ??
                .4,

            maxLife:
                options.life ??
                .4,

            size:
                options.size ??
                100,

            rotation:
                options.rotation ??
                0,

            flip:
                options.flip ??
                false

        });

    }


    function updateEffects(
        dt
    ) {

        for (
            let i =
                effects.length - 1;
            i >= 0;
            i--
        ) {

            effects[i].life -=
                dt;


            if (
                effects[i].life <=
                0
            ) {

                effects.splice(
                    i,
                    1
                );

            }

        }

    }


    function drawEffects() {

        for (
            const effect
            of effects
        ) {

            const source =
                imageCanvases[
                    effect.type
                ];


            const alpha =
                clamp(
                    effect.life /
                    effect.maxLife,
                    0,
                    1
                );


            ctx.save();


            ctx.globalAlpha =
                alpha;


            ctx.translate(
                effect.x -
                cameraX,
                effect.y
            );


            if (
                effect.flip
            ) {

                ctx.scale(
                    -1,
                    1
                );

            }


            ctx.rotate(
                effect.rotation
            );


            if (
                source
            ) {

                const ratio =
                    source.width /
                    source.height;


                const h =
                    effect.size;


                const w =
                    h *
                    ratio;


                ctx.drawImage(
                    source,
                    -w / 2,
                    -h / 2,
                    w,
                    h
                );

            } else {

                ctx.fillStyle =
                    "#60a5fa";

                ctx.beginPath();

                ctx.arc(
                    0,
                    0,
                    effect.size *
                    .16,
                    0,
                    Math.PI * 2
                );

                ctx.fill();

            }


            ctx.restore();

        }

    }


    /* ========================================================
       PARTICLES
    ======================================================== */

    const particles = [];


    function particleBurst(
        x,
        y,
        type,
        count = 12
    ) {

        for (
            let i = 0;
            i < count;
            i++
        ) {

            particles.push({

                x:x,

                y:y,

                vx:
                    random(
                        -180,
                        180
                    ),

                vy:
                    random(
                        -220,
                        40
                    ),

                gravity:
                    250,

                life:
                    random(
                        .30,
                        .75
                    ),

                maxLife:
                    random(
                        .30,
                        .75
                    ),

                size:
                    random(
                        3,
                        7
                    ),

                type:type,

                rotation:
                    random(
                        0,
                        Math.PI * 2
                    )

            });

        }

    }


    function updateParticles(
        dt
    ) {

        for (
            let i =
                particles.length - 1;
            i >= 0;
            i--
        ) {

            const p =
                particles[i];


            p.x +=
                p.vx *
                dt;


            p.y +=
                p.vy *
                dt;


            p.vy +=
                p.gravity *
                dt;


            p.life -=
                dt;


            if (
                p.life <=
                0
            ) {

                particles.splice(
                    i,
                    1
                );

            }

        }

    }


    function drawParticles() {

        for (
            const p
            of particles
        ) {

            const source =
                imageCanvases[
                    p.type
                ];


            const alpha =
                clamp(
                    p.life /
                    p.maxLife,
                    0,
                    1
                );


            ctx.save();


            ctx.globalAlpha =
                alpha;


            ctx.translate(
                p.x -
                cameraX,
                p.y
            );


            if (
                source
            ) {

                const size =
                    p.size *
                    13;


                ctx.drawImage(
                    source,
                    -size / 2,
                    -size / 2,
                    size,
                    size
                );

            } else {

                ctx.fillStyle =
                    "#60a5fa";


                ctx.fillRect(
                    -p.size / 2,
                    -p.size / 2,
                    p.size,
                    p.size
                );

            }


            ctx.restore();

        }

    }


    /* ========================================================
       DAMAGE NUMBERS
    ======================================================== */

    const damageNumbers = [];


    function showDamage(
        x,
        y,
        amount,
        color
    ) {

        damageNumbers.push({

            x:x,

            y:y,

            amount:
                Math.round(
                    amount
                ),

            color:
                color ||
                "#ffffff",

            life:.8,

            maxLife:.8,

            vy:-48

        });

    }


    function updateDamageNumbers(
        dt
    ) {

        for (
            let i =
                damageNumbers.length - 1;
            i >= 0;
            i--
        ) {

            const d =
                damageNumbers[i];


            d.y +=
                d.vy *
                dt;


            d.vy +=
                70 *
                dt;


            d.life -=
                dt;


            if (
                d.life <=
                0
            ) {

                damageNumbers.splice(
                    i,
                    1
                );

            }

        }

    }


    function drawDamageNumbers() {

        for (
            const d
            of damageNumbers
        ) {

            ctx.save();


            ctx.globalAlpha =
                clamp(
                    d.life /
                    d.maxLife,
                    0,
                    1
                );


            ctx.fillStyle =
                d.color;


            ctx.font =
                "900 " +
                Math.floor(
                    canvasWidth /
                    48
                ) +
                "px Arial";


            ctx.textAlign =
                "center";


            ctx.shadowBlur =
                13;


            ctx.shadowColor =
                d.color;


            ctx.fillText(
                "-" +
                d.amount,
                d.x -
                cameraX,
                d.y
            );


            ctx.restore();

        }

    }


    /* ========================================================
       ENEMY
    ======================================================== */

    const enemies = [];


    function createEnemy(
        x,
        boss
    ) {

        const hp =
            boss
                ? 450 +
                    level *
                    90
                : 70 +
                    level *
                    18;


        return {

            x:x,

            y:
                groundY -
                (
                    boss
                        ? 160
                        : 115
                ),

            width:
                boss
                    ? 90
                    : 67,

            height:
                boss
                    ? 160
                    : 115,

            vx:0,

            vy:0,

            speed:
                boss
                    ? 125
                    : 80 +
                        level *
                        6,

            health:hp,

            maxHealth:hp,

            damage:
                boss
                    ? 18 +
                        level
                    : 10 +
                        level,

            facing:-1,

            state:"idle",

            stateTime:0,

            attackTime:0,

            attackCooldown:
                random(
                    1.1,
                    2.2
                ),

            attackHit:false,

            hurtTime:0,

            deathTime:0,

            dead:false,

            boss:boss

        };

    }


    function spawnLevel() {

        enemies.length =
            0;


        const count =
            Math.min(
                2 +
                level,
                7
            );


        for (
            let i = 0;
            i < count;
            i++
        ) {

            enemies.push(
                createEnemy(
                    player.x +
                    500 +
                    i *
                    150 +
                    random(
                        0,
                        100
                    ),
                    false
                )
            );

        }


        if (
            level % 3 ===
            0
        ) {

            enemies.push(
                createEnemy(
                    player.x +
                    1000,
                    true
                )
            );

        }


        levelIntroTimer =
            1.2;


        updateUI();

    }


    function aliveEnemyCount() {

        return enemies.filter(
            function (
                enemy
            ) {

                return !enemy.dead;

            }
        ).length;

    }


    /* ========================================================
       PLAYER ACTIONS
    ======================================================== */

    function jump() {

        if (
            paused ||
            gameOver ||
            victory
        ) {

            return;

        }


        if (
            !player.grounded ||
            player.dead
        ) {

            return;

        }


        player.vy =
            -player.jumpPower;


        player.grounded =
            false;


        player.state =
            "jump";


        player.stateTime =
            0;


        particleBurst(
            player.x,
            groundY,
            "dust",
            8
        );

    }


    function attack() {

        if (
            paused ||
            gameOver ||
            victory ||
            player.dead
        ) {

            return;

        }


        if (
            player.attackCooldown >
            0
        ) {

            return;

        }


        if (
            player.specialTime >
            0
        ) {

            return;

        }


        player.attackTime =
            .42;


        player.attackCooldown =
            .20;


        player.attackStep =
            (
                player.attackStep %
                3
            ) +
            1;


        player.attackHit =
            false;


        player.state =
            "attack";


        player.stateTime =
            0;


        combo++;

        comboTimer =
            1.1;


        if (
            combo >=
            2
        ) {

            comboPopup.textContent =
                "COMBO x" +
                combo;


            comboPopup.classList.remove(
                "show"
            );


            void
                comboPopup
                .offsetWidth;


            comboPopup.classList.add(
                "show"
            );

        }

    }


    function specialAttack() {

        if (
            paused ||
            gameOver ||
            victory ||
            player.dead
        ) {

            return;

        }


        if (
            player.specialTime >
            0
        ) {

            return;

        }


        if (
            player.stamina <
            35
        ) {

            return;

        }


        player.stamina -=
            35;


        player.specialTime =
            .8;


        player.state =
            "special";


        player.stateTime =
            0;


        player.attackCooldown =
            .85;


        screenShake =
            .35;


        showFlash();


        addEffect(
            "special_wave",
            player.x +
            player.facing *
            120,
            player.y +
            player.height *
            .46,
            {
                life:.55,
                size:
                    player.height *
                    1.15,
                flip:
                    player.facing < 0
            }
        );


        particleBurst(
            player.x,
            player.y +
            55,
            "magic",
            24
        );


        for (
            const enemy
            of enemies
        ) {

            if (
                enemy.dead
            ) {

                continue;

            }


            if (
                distance(
                    player.x,
                    enemy.x
                ) <
                250
            ) {

                damageEnemy(
                    enemy,
                    52 +
                    combo *
                    6
                );

            }

        }

    }


    /* ========================================================
       ENEMY DAMAGE
    ======================================================== */

    function damageEnemy(
        enemy,
        amount
    ) {

        if (
            enemy.dead
        ) {

            return;

        }


        enemy.health -=
            amount;


        enemy.hurtTime =
            .30;


        enemy.state =
            "hurt";


        enemy.stateTime =
            0;


        enemy.vx =
            player.facing *
            270;


        particleBurst(
            enemy.x,
            enemy.y +
            enemy.height *
            .45,
            enemy.boss
                ? "energy"
                : "sparks",
            enemy.boss
                ? 20
                : 12
        );


        addEffect(
            "impact",
            enemy.x,
            enemy.y +
            enemy.height *
            .45,
            {
                life:.28,
                size:75
            }
        );


        showDamage(
            enemy.x,
            enemy.y,
            amount,
            enemy.boss
                ? "#fca5a5"
                : "#bfdbfe"
        );


        score +=
            enemy.boss
                ? 35
                : 15;


        screenShake =
            Math.max(
                screenShake,
                .12
            );


        if (
            enemy.health <=
            0
        ) {

            enemy.health =
                0;


            enemy.dead =
                true;


            enemy.state =
                "death";


            enemy.deathTime =
                .95;


            score +=
                enemy.boss
                    ? 800
                    : 120;


            particleBurst(
                enemy.x,
                enemy.y +
                enemy.height *
                .42,
                enemy.boss
                    ? "magic"
                    : "sparks",
                enemy.boss
                    ? 45
                    : 25
            );


            showDamage(
                enemy.x,
                enemy.y -
                20,
                enemy.boss
                    ? 500
                    : 100,
                "#facc15"
            );

        }

    }


    /* ========================================================
       PLAYER DAMAGE
    ======================================================== */

    function damagePlayer(
        amount
    ) {

        if (
            player.dead ||
            player.invulnerable >
            0
        ) {

            return;

        }


        player.health -=
            amount;


        player.hurtTime =
            .38;


        player.invulnerable =
            .80;


        player.state =
            "hurt";


        player.stateTime =
            0;


        player.vx =
            -player.facing *
            180;


        screenShake =
            .20;


        showFlash();


        particleBurst(
            player.x,
            player.y +
            50,
            "sparks",
            12
        );


        showDamage(
            player.x,
            player.y,
            amount,
            "#fca5a5"
        );


        combo =
            0;


        if (
            player.health <=
            0
        ) {

            player.health =
                0;


            player.dead =
                true;


            player.state =
                "death";


            player.stateTime =
                0;


            gameOver =
                true;


            showGameOver();

        }

    }


    /* ========================================================
       ENEMY UPDATE
    ======================================================== */

    function updateEnemies(
        dt
    ) {

        for (
            const enemy
            of enemies
        ) {

            enemy.stateTime +=
                dt;


            enemy.attackCooldown =
                Math.max(
                    0,
                    enemy.attackCooldown -
                    dt
                );


            enemy.attackTime =
                Math.max(
                    0,
                    enemy.attackTime -
                    dt
                );


            enemy.hurtTime =
                Math.max(
                    0,
                    enemy.hurtTime -
                    dt
                );


            if (
                enemy.dead
            ) {

                enemy.deathTime =
                    Math.max(
                        0,
                        enemy.deathTime -
                        dt
                );


                enemy.y +=
                    70 *
                    dt;


                continue;

            }


            const dx =
                player.x -
                enemy.x;


            const attackDistance =
                enemy.boss
                    ? 160
                    : 120;


            if (
                Math.abs(dx) >
                attackDistance
            ) {

                enemy.vx =
                    Math.sign(
                        dx
                    ) *
                    enemy.speed;


                enemy.facing =
                    dx >= 0
                        ? 1
                        : -1;


                enemy.state =
                    "run";

            } else {

                enemy.vx =
                    0;


                enemy.state =
                    "idle";


                if (
                    enemy.attackCooldown <=
                    0
                ) {

                    enemy.attackTime =
                        .55;


                    enemy.attackCooldown =
                        enemy.boss
                            ? 1.2
                            : random(
                                1.5,
                                2.35
                            );


                    enemy.attackHit =
                        false;


                    enemy.state =
                        "attack";


                    enemy.stateTime =
                        0;

                }


                if (
                    enemy.attackTime >
                    0
                ) {

                    const progress =
                        1 -
                        (
                            enemy.attackTime /
                            .55
                        );


                    if (
                        progress >
                        .38 &&
                        progress <
                        .80 &&
                        !enemy.attackHit
                    ) {

                        enemy.attackHit =
                            true;


                        if (
                            distance(
                                player.x,
                                enemy.x
                            ) <
                            attackDistance +
                            20
                        ) {

                            damagePlayer(
                                enemy.damage
                            );

                        }

                    }

                }

            }


            enemy.x +=
                enemy.vx *
                dt;


            enemy.x =
                Math.max(
                    50,
                    enemy.x
                );

        }

    }


    /* ========================================================
       HIT ENEMIES
    ======================================================== */

    function hitNearbyEnemies(
        range,
        amount
    ) {

        for (
            const enemy
            of enemies
        ) {

            if (
                enemy.dead
            ) {

                continue;

            }


            const dx =
                enemy.x -
                player.x;


            if (
                Math.abs(dx) <
                range &&
                (
                    player.facing *
                    dx >
                    -35
                )
            ) {

                damageEnemy(
                    enemy,
                    amount
                );

            }

        }


        particleBurst(
            player.x +
            player.facing *
            75,
            player.y +
            50,
            "sparks",
            12
        );

    }


    /* ========================================================
       PLAYER UPDATE
    ======================================================== */

    function updatePlayer(
        dt
    ) {

        if (
            player.dead
        ) {

            player.stateTime +=
                dt;

            return;

        }


        player.stateTime +=
            dt;


        player.attackCooldown =
            Math.max(
                0,
                player.attackCooldown -
                dt
            );


        player.attackTime =
            Math.max(
                0,
                player.attackTime -
                dt
            );


        player.specialTime =
            Math.max(
                0,
                player.specialTime -
                dt
            );


        player.hurtTime =
            Math.max(
                0,
                player.hurtTime -
                dt
            );


        player.invulnerable =
            Math.max(
                0,
                player.invulnerable -
                dt
            );


        /* ----------------------------------------------------
           JUMP
        ---------------------------------------------------- */

        if (
            keys["w"] ||
            keys["arrowup"] ||
            keys[" "]
        ) {

            jump();


            keys["w"] =
                false;


            keys["arrowup"] =
                false;


            keys[" "] =
                false;

        }


        /* ----------------------------------------------------
           ATTACK
        ---------------------------------------------------- */

        if (
            keys["j"]
        ) {

            attack();


            keys["j"] =
                false;

        }


        /* ----------------------------------------------------
           SPECIAL
        ---------------------------------------------------- */

        if (
            keys["k"]
        ) {

            specialAttack();


            keys["k"] =
                false;

        }


        /* ----------------------------------------------------
           MOVEMENT
        ---------------------------------------------------- */

        let moving =
            false;


        if (
            keys["a"] ||
            keys["arrowleft"] ||
            touch.left
        ) {

            player.vx =
                -player.speed;


            player.facing =
                -1;


            moving =
                true;

        }


        if (
            keys["d"] ||
            keys["arrowright"] ||
            touch.right
        ) {

            player.vx =
                player.speed;


            player.facing =
                1;


            moving =
                true;

        }


        if (
            !moving
        ) {

            player.vx *=
                .82;

        }


        /* ----------------------------------------------------
           GRAVITY
        ---------------------------------------------------- */

        player.vy +=
            1750 *
            dt;


        player.y +=
            player.vy *
            dt;


        if (
            player.y +
            player.height >=
            groundY
        ) {

            player.y =
                groundY -
                player.height;


            player.vy =
                0;


            player.grounded =
                true;

        } else {

            player.grounded =
                false;

        }


        /* ----------------------------------------------------
           SPECIAL SPEED
        ---------------------------------------------------- */

        if (
            player.specialTime >
            0
        ) {

            player.vx =
                player.facing *
                580;

        }


        player.x +=
            player.vx *
            dt;


        player.x =
            Math.max(
                50,
                player.x
            );


        /* ----------------------------------------------------
           ATTACK COLLISION
        ---------------------------------------------------- */

        if (
            player.attackTime >
            0
        ) {

            const progress =
                1 -
                (
                    player.attackTime /
                    .42
                );


            if (
                progress >
                .25 &&
                progress <
                .76 &&
                !player.attackHit
            ) {

                player.attackHit =
                    true;


                hitNearbyEnemies(
                    130,
                    20 +
                    player.attackStep *
                    10
                );


                addEffect(
                    "slash" +
                    player.attackStep,
                    player.x +
                    player.facing *
                    75,
                    player.y +
                    player.height *
                    .42,
                    {
                        life:.30,
                        size:100,
                        flip:
                            player.facing <
                            0
                    }
                );

            }

        }


        /* ----------------------------------------------------
           STATE
        ---------------------------------------------------- */

        if (
            player.hurtTime >
            0
        ) {

            player.state =
                "hurt";

        } else if (
            player.specialTime >
            0
        ) {

            player.state =
                "special";

        } else if (
            player.attackTime >
            0
        ) {

            player.state =
                "attack";

        } else if (
            !player.grounded
        ) {

            player.state =
                "jump";

        } else if (
            moving
        ) {

            player.state =
                "run";

        } else {

            player.state =
                "idle";

        }


        /* ----------------------------------------------------
           STAMINA
        ---------------------------------------------------- */

        player.stamina =
            Math.min(
                player.maxStamina,
                player.stamina +
                17 *
                dt
            );


        /* ----------------------------------------------------
           CAMERA
        ---------------------------------------------------- */

        const targetCamera =
            player.x -
            canvasWidth *
            .34;


        cameraX +=
            (
                targetCamera -
                cameraX
            ) *
            .075;


        cameraX =
            Math.max(
                0,
                cameraX
            );

    }


    /* ========================================================
       BACKGROUND
    ======================================================== */

    function drawBackground() {

        const sky =
            ctx.createLinearGradient(
                0,
                0,
                0,
                canvasHeight
            );


        sky.addColorStop(
            0,
            "#020617"
        );


        sky.addColorStop(
            .48,
            "#07152f"
        );


        sky.addColorStop(
            1,
            "#0f172a"
        );


        ctx.fillStyle =
            sky;


        ctx.fillRect(
            0,
            0,
            canvasWidth,
            canvasHeight
        );


        /* ----------------------------------------------------
           BACKGROUND IMAGE
        ---------------------------------------------------- */

        let backgroundKey =
            "night_background";


        if (
            level >=
            5
        ) {

            backgroundKey =
                "volcano_background";

        } else if (
            level >=
            3
        ) {

            backgroundKey =
                "forest_background";

        }


        const bg =
            imageCanvases[
                backgroundKey
            ];


        if (
            bg
        ) {

            const ratio =
                bg.width /
                bg.height;


            const canvasRatio =
                canvasWidth /
                canvasHeight;


            let drawWidth =
                canvasWidth;


            let drawHeight =
                drawWidth /
                ratio;


            if (
                drawHeight <
                canvasHeight
            ) {

                drawHeight =
                    canvasHeight;

                drawWidth =
                    drawHeight *
                    ratio;

            }


            const parallax =
                (
                    cameraX *
                    .055
                ) %
                160;


            const x =
                (
                    canvasWidth -
                    drawWidth
                ) /
                2 -
                parallax;


            const y =
                (
                    canvasHeight -
                    drawHeight
                ) /
                2;


            ctx.globalAlpha =
                .96;


            ctx.drawImage(
                bg,
                x,
                y,
                drawWidth,
                drawHeight
            );


            ctx.globalAlpha =
                1;

        }


        /* ----------------------------------------------------
           STARS
        ---------------------------------------------------- */

        for (
            let i = 0;
            i < 85;
            i++
        ) {

            let x =
                (
                    i *
                    171 -
                    cameraX *
                    .08
                ) %
                canvasWidth;


            if (
                x < 0
            ) {

                x +=
                    canvasWidth;

            }


            const y =
                (
                    i *
                    63
                ) %
                (
                    canvasHeight *
                    .47
                );


            const alpha =
                .17 +
                .23 *
                (
                    Math.sin(
                        gameTime *
                        2 +
                        i
                    ) +
                    1
                ) /
                2;


            ctx.fillStyle =
                "rgba(147,197,253," +
                alpha +
                ")";


            ctx.fillRect(
                x,
                y,
                1.5,
                1.5
            );

        }


        /* ----------------------------------------------------
           GROUND
        ---------------------------------------------------- */

        const ground =
            ctx.createLinearGradient(
                0,
                groundY,
                0,
                canvasHeight
            );


        ground.addColorStop(
            0,
            "rgba(4,14,28,.76)"
        );


        ground.addColorStop(
            1,
            "rgba(2,6,23,.98)"
        );


        ctx.fillStyle =
            ground;


        ctx.fillRect(
            0,
            groundY,
            canvasWidth,
            canvasHeight -
            groundY
        );


        ctx.strokeStyle =
            "rgba(96,165,250,.20)";


        ctx.lineWidth =
            2;


        ctx.beginPath();


        ctx.moveTo(
            0,
            groundY
        );


        ctx.lineTo(
            canvasWidth,
            groundY
        );


        ctx.stroke();


        /* ----------------------------------------------------
           GROUND LINES
        ---------------------------------------------------- */

        for (
            let i = 0;
            i < 24;
            i++
        ) {

            let x =
                (
                    i *
                    115 -
                    cameraX *
                    .85
                ) %
                (
                    canvasWidth +
                    120
                );


            if (
                x < 0
            ) {

                x +=
                    canvasWidth +
                    120;

            }


            ctx.strokeStyle =
                "rgba(37,99,235,.09)";


            ctx.beginPath();


            ctx.moveTo(
                x,
                groundY +
                22
            );


            ctx.lineTo(
                x +
                45,
                groundY +
                22
            );


            ctx.stroke();

        }

    }


    /* ========================================================
       PLAYER SPRITE
    ======================================================== */

    function getPlayerAssetKey() {

        if (
            player.dead
        ) {

            return "hero_death";

        }


        if (
            player.state ===
            "hurt"
        ) {

            return "hero_hurt";

        }


        if (
            player.state ===
            "special"
        ) {

            return "hero_special";

        }


        if (
            player.state ===
            "attack"
        ) {

            return (
                "hero_attack" +
                player.attackStep
            );

        }


        if (
            player.state ===
            "jump"
        ) {

            return "hero_jump";

        }


        if (
            player.state ===
            "run"
        ) {

            return "hero_run";

        }


        return "hero_idle";

    }


    function drawPlayer() {

        const key =
            getPlayerAssetKey();


        const source =
            imageCanvases[
                key
            ];


        const px =
            player.x -
            cameraX;


        const py =
            player.y +
            player.height;


        /* ----------------------------------------------------
           SHADOW
        ---------------------------------------------------- */

        ctx.save();


        ctx.globalAlpha =
            .28;


        ctx.fillStyle =
            "#000000";


        ctx.beginPath();


        ctx.ellipse(
            px,
            groundY +
            4,
            39,
            9,
            0,
            0,
            Math.PI *
            2
        );


        ctx.fill();


        ctx.restore();


        /* ----------------------------------------------------
           AURA
        ---------------------------------------------------- */

        if (
            player.specialTime >
            0
        ) {

            const radius =
                58 +
                Math.sin(
                    gameTime *
                    17
                ) *
                8;


            ctx.save();


            ctx.strokeStyle =
                "rgba(96,165,250,.50)";


            ctx.lineWidth =
                4;


            ctx.shadowBlur =
                25;


            ctx.shadowColor =
                "#3b82f6";


            ctx.beginPath();


            ctx.arc(
                px,
                player.y +
                player.height *
                .46,
                radius,
                0,
                Math.PI *
                2
            );


            ctx.stroke();


            ctx.restore();

        }


        /* ----------------------------------------------------
           DRAW ACTUAL IMAGE
        ---------------------------------------------------- */

        if (
            source
        ) {

            const ratio =
                source.width /
                source.height;


            let drawHeight =
                player.height *
                1.10;


            let drawWidth =
                drawHeight *
                ratio;


            const bob =
                player.state ===
                "idle"
                    ? Math.sin(
                        player.stateTime *
                        5
                    ) *
                    2
                    : player.state ===
                      "run"
                        ? Math.sin(
                            player.stateTime *
                            14
                        ) *
                        3
                        : 0;


            ctx.save();


            ctx.globalAlpha =
                (
                    player.invulnerable >
                    0
                )
                    ? (
                        Math.sin(
                            gameTime *
                            30
                        ) >
                        0
                            ? .40
                            : 1
                    )
                    : 1;


            if (
                player.facing <
                0
            ) {

                ctx.translate(
                    px,
                    py +
                    bob
                );


                ctx.scale(
                    -1,
                    1
                );


                ctx.drawImage(
                    source,
                    -drawWidth / 2,
                    -drawHeight,
                    drawWidth,
                    drawHeight
                );

            } else {

                ctx.drawImage(
                    source,
                    px -
                    drawWidth / 2,
                    py -
                    drawHeight +
                    bob,
                    drawWidth,
                    drawHeight
                );

            }


            ctx.restore();

        } else {

            drawFallbackHero(
                px,
                py
            );

        }

    }


    /* ========================================================
       FALLBACK HERO
    ======================================================== */

    function drawFallbackHero(
        x,
        y
    ) {

        ctx.save();


        ctx.translate(
            x,
            y
        );


        if (
            player.facing <
            0
        ) {

            ctx.scale(
                -1,
                1
            );

        }


        /* cape */

        ctx.fillStyle =
            "#1d4ed8";


        ctx.beginPath();


        ctx.moveTo(
            -18,
            -80
        );


        ctx.lineTo(
            -48,
            -18
        );


        ctx.lineTo(
            -8,
            -30
        );


        ctx.closePath();


        ctx.fill();


        /* body */

        const armor =
            ctx.createLinearGradient(
                -20,
                -85,
                20,
                -35
            );


        armor.addColorStop(
            0,
            "#60a5fa"
        );


        armor.addColorStop(
            1,
            "#172554"
        );


        ctx.fillStyle =
            armor;


        roundRect(
            ctx,
            -20,
            -92,
            40,
            50,
            9
        );


        ctx.fill();


        /* head */

        ctx.fillStyle =
            "#dbeafe";


        ctx.beginPath();


        ctx.arc(
            0,
            -107,
            18,
            0,
            Math.PI *
            2
        );


        ctx.fill();


        /* hair */

        ctx.fillStyle =
            "#020617";


        ctx.beginPath();


        ctx.moveTo(
            -18,
            -105
        );


        ctx.lineTo(
            -7,
            -132
        );


        ctx.lineTo(
            2,
            -115
        );


        ctx.lineTo(
            11,
            -135
        );


        ctx.lineTo(
            19,
            -110
        );


        ctx.closePath();


        ctx.fill();


        /* legs */

        ctx.strokeStyle =
            "#334155";


        ctx.lineWidth =
            11;


        ctx.lineCap =
            "round";


        ctx.beginPath();


        ctx.moveTo(
            -9,
            -43
        );


        ctx.lineTo(
            -14,
            -5
        );


        ctx.stroke();


        ctx.beginPath();


        ctx.moveTo(
            9,
            -43
        );


        ctx.lineTo(
            14,
            -5
        );


        ctx.stroke();


        /* sword */

        ctx.strokeStyle =
            "#e0f2fe";


        ctx.lineWidth =
            6;


        ctx.shadowBlur =
            12;


        ctx.shadowColor =
            "#60a5fa";


        ctx.beginPath();


        ctx.moveTo(
            24,
            -55
        );


        ctx.lineTo(
            104,
            -85
        );


        ctx.stroke();


        ctx.shadowBlur =
            0;


        ctx.restore();

    }


    /* ========================================================
       ENEMY SPRITE
    ======================================================== */

    function drawEnemy(
        enemy
    ) {

        const key =
            enemy.dead
                ? (
                    enemy.boss
                        ? "boss_death"
                        : "soldier_death"
                )
                : enemy.hurtTime >
                  0
                    ? (
                        enemy.boss
                            ? "boss_hurt"
                            : "soldier_hurt"
                    )
                    : enemy.state ===
                      "attack"
                        ? (
                            enemy.boss
                                ? "boss_attack"
                                : "soldier_attack"
                        )
                        : enemy.state ===
                          "run"
                            ? (
                                enemy.boss
                                    ? "boss_idle"
                                    : "soldier_run"
                            )
                            : (
                                enemy.boss
                                    ? "boss_idle"
                                    : "soldier_idle"
                            );


        const source =
            imageCanvases[
                key
            ];


        const x =
            enemy.x -
            cameraX;


        const y =
            enemy.y +
            enemy.height;


        /* ----------------------------------------------------
           shadow
        ---------------------------------------------------- */

        ctx.save();


        ctx.globalAlpha =
            .30;


        ctx.fillStyle =
            "#000";


        ctx.beginPath();


        ctx.ellipse(
            x,
            groundY +
            4,
            enemy.boss
                ? 52
                : 38,
            9,
            0,
            0,
            Math.PI *
            2
        );


        ctx.fill();


        ctx.restore();


        /* ----------------------------------------------------
           boss glow
        ---------------------------------------------------- */

        if (
            enemy.boss &&
            !enemy.dead
        ) {

            const glowRadius =
                62 +
                Math.sin(
                    gameTime *
                    5
                ) *
                5;


            ctx.save();


            ctx.strokeStyle =
                "rgba(239,68,68,.23)";


            ctx.lineWidth =
                4;


            ctx.shadowBlur =
                22;


            ctx.shadowColor =
                "#ef4444";


            ctx.beginPath();


            ctx.arc(
                x,
                enemy.y +
                enemy.height *
                .46,
                glowRadius,
                0,
                Math.PI *
                2
            );


            ctx.stroke();


            ctx.restore();

        }


        /* ----------------------------------------------------
           image
        ---------------------------------------------------- */

        if (
            source
        ) {

            const ratio =
                source.width /
                source.height;


            const drawHeight =
                enemy.height *
                1.06;


            const drawWidth =
                drawHeight *
                ratio;


            const bob =
                enemy.state ===
                "idle"
                    ? Math.sin(
                        enemy.stateTime *
                        5
                    ) *
                    2
                    : 0;


            ctx.save();


            if (
                enemy.facing <
                0
            ) {

                ctx.translate(
                    x,
                    y +
                    bob
                );


                ctx.scale(
                    -1,
                    1
                );


                ctx.drawImage(
                    source,
                    -drawWidth / 2,
                    -drawHeight,
                    drawWidth,
                    drawHeight
                );

            } else {

                ctx.drawImage(
                    source,
                    x -
                    drawWidth / 2,
                    y -
                    drawHeight +
                    bob,
                    drawWidth,
                    drawHeight
                );

            }


            ctx.restore();

        } else {

            drawFallbackEnemy(
                enemy,
                x,
                y
            );

        }


        /* ----------------------------------------------------
           health
        ---------------------------------------------------- */

        if (
            !enemy.dead
        ) {

            const health =
                clamp(
                    enemy.health /
                    enemy.maxHealth,
                    0,
                    1
                );


            const barWidth =
                enemy.boss
                    ? 100
                    : 62;


            const barHeight =
                enemy.boss
                    ? 8
                    : 5;


            ctx.fillStyle =
                "rgba(2,6,23,.82)";


            ctx.fillRect(
                x -
                barWidth /
                2,
                enemy.y -
                14,
                barWidth,
                barHeight
            );


            ctx.fillStyle =
                enemy.boss
                    ? "#ef4444"
                    : "#dc2626";


            ctx.fillRect(
                x -
                barWidth /
                2,
                enemy.y -
                14,
                barWidth *
                health,
                barHeight
            );

        }

    }


    /* ========================================================
       FALLBACK ENEMY
    ======================================================== */

    function drawFallbackEnemy(
        enemy,
        x,
        y
    ) {

        ctx.save();


        ctx.translate(
            x,
            y
        );


        if (
            enemy.facing <
            0
        ) {

            ctx.scale(
                -1,
                1
            );

        }


        const color =
            enemy.boss
                ? "#7f1d1d"
                : "#991b1b";


        ctx.fillStyle =
            color;


        roundRect(
            ctx,
            -(
                enemy.boss
                    ? 28
                    : 19
            ),
            -(
                enemy.boss
                    ? 112
                    : 82
            ),
            enemy.boss
                ? 56
                : 38,
            enemy.boss
                ? 66
                : 46,
            10
        );


        ctx.fill();


        ctx.fillStyle =
            "#1e293b";


        ctx.beginPath();


        ctx.arc(
            0,
            -(
                enemy.boss
                    ? 127
                    : 98
            ),
            enemy.boss
                ? 24
                : 17,
            0,
            Math.PI *
            2
        );


        ctx.fill();


        ctx.fillStyle =
            "#ef4444";


        ctx.shadowBlur =
            12;


        ctx.shadowColor =
            "#ef4444";


        ctx.fillRect(
            7,
            -(
                enemy.boss
                    ? 128
                    : 99
            ),
            6,
            3
        );


        ctx.shadowBlur =
            0;


        ctx.strokeStyle =
            "#0f172a";


        ctx.lineWidth =
            enemy.boss
                ? 14
                : 10;


        ctx.lineCap =
            "round";


        ctx.beginPath();


        ctx.moveTo(
            -10,
            -(
                enemy.boss
                    ? 45
                    : 36
            )
        );


        ctx.lineTo(
            -17,
            -5
        );


        ctx.stroke();


        ctx.beginPath();


        ctx.moveTo(
            10,
            -(
                enemy.boss
                    ? 45
                    : 36
            )
        );


        ctx.lineTo(
            17,
            -5
        );


        ctx.stroke();


        ctx.restore();

    }


    /* ========================================================
       ROUND RECT
    ======================================================== */

    function roundRect(
        context,
        x,
        y,
        width,
        height,
        radius
    ) {

        context.beginPath();


        context.moveTo(
            x + radius,
            y
        );


        context.lineTo(
            x +
            width -
            radius,
            y
        );


        context.quadraticCurveTo(
            x +
            width,
            y,
            x +
            width,
            y +
            radius
        );


        context.lineTo(
            x +
            width,
            y +
            height -
            radius
        );


        context.quadraticCurveTo(
            x +
            width,
            y +
            height,
            x +
            width -
            radius,
            y +
            height
        );


        context.lineTo(
            x +
            radius,
            y +
            height
        );


        context.quadraticCurveTo(
            x,
            y +
            height,
            x,
            y +
            height -
            radius
        );


        context.lineTo(
            x,
            y +
            radius
        );


        context.quadraticCurveTo(
            x,
            y,
            x +
            radius,
            y
        );


        context.closePath();

    }


    /* ========================================================
       UI
    ======================================================== */

    function updateUI() {

        const health =
            clamp(
                player.health /
                player.maxHealth *
                100,
                0,
                100
            );


        const stamina =
            clamp(
                player.stamina /
                player.maxStamina *
                100,
                0,
                100
            );


        healthBar.style.width =
            health +
            "%";


        staminaBar.style.width =
            stamina +
            "%";


        scoreElement.textContent =
            String(
                Math.floor(
                    score
                )
            ).padStart(
                6,
                "0"
            );


        comboElement.textContent =
            "COMBO x" +
            combo;


        levelElement.textContent =
            "LEVEL " +
            String(
                level
            ).padStart(
                2,
                "0"
            );


        levelBar.textContent =
            "LEVEL " +
            String(
                level
            ).padStart(
                2,
                "0"
            ) +
            " • ENEMIES " +
            aliveEnemyCount();


        const boss =
            enemies.find(
                function (
                    enemy
                ) {

                    return (
                        enemy.boss &&
                        !enemy.dead
                    );

                }
            );


        if (
            boss
        ) {

            bossElement.classList.add(
                "visible"
            );


            bossFill.style.width =
                clamp(
                    boss.health /
                    boss.maxHealth *
                    100,
                    0,
                    100
                ) +
                "%";

        } else {

            bossElement.classList.remove(
                "visible"
            );

        }

    }


    /* ========================================================
       FLASH
    ======================================================== */

    function showFlash() {

        flash.classList.remove(
            "show"
        );


        void
            flash.offsetWidth;


        flash.classList.add(
            "show"
        );

    }


    /* ========================================================
       LEVEL
    ======================================================== */

    function updateLevel(
        dt
    ) {

        levelIntroTimer =
            Math.max(
                0,
                levelIntroTimer -
                dt
            );


        if (
            enemies.length ===
            0 &&
            !victory
        ) {

            if (
                level >=
                6
            ) {

                victory =
                    true;


                showVictory();

            } else {

                level++;


                player.health =
                    clamp(
                        player.health +
                        20,
                        0,
                        player.maxHealth
                    );


                player.stamina =
                    player.maxStamina;


                particleBurst(
                    player.x,
                    player.y +
                    50,
                    "energy",
                    35
                );


                spawnLevel();

            }

        }

    }


    /* ========================================================
       MAIN UPDATE
    ======================================================== */

    function update(
        dt
    ) {

        if (
            paused ||
            gameOver ||
            victory
        ) {

            return;

        }


        gameTime +=
            dt;


        comboTimer =
            Math.max(
                0,
                comboTimer -
                dt
            );


        if (
            comboTimer <=
            0
        ) {

            combo =
                0;

        }


        updatePlayer(
            dt
        );


        updateEnemies(
            dt
        );


        updateParticles(
            dt
        );


        updateEffects(
            dt
        );


        updateDamageNumbers(
            dt
        );


        updateLevel(
            dt
        );


        screenShake =
            Math.max(
                0,
                screenShake -
                dt
            );


        for (
            let i =
                enemies.length - 1;
            i >= 0;
            i--
        ) {

            if (
                enemies[i].dead &&
                enemies[i].deathTime <=
                0
            ) {

                enemies.splice(
                    i,
                    1
                );

            }

        }


        updateUI();

    }


    /* ========================================================
       DRAW
    ======================================================== */

    function draw() {

        ctx.clearRect(
            0,
            0,
            canvasWidth,
            canvasHeight
        );


        ctx.save();


        if (
            screenShake >
            0
        ) {

            ctx.translate(
                random(
                    -7,
                    7
                ) *
                screenShake,

                random(
                    -7,
                    7
                ) *
                screenShake
            );

        }


        drawBackground();


        drawParticles();


        for (
            const enemy
            of enemies
        ) {

            drawEnemy(
                enemy
            );

        }


        drawPlayer();


        drawEffects();


        drawDamageNumbers();


        /* ----------------------------------------------------
           LEVEL INTRO
        ---------------------------------------------------- */

        if (
            levelIntroTimer >
            0
        ) {

            const alpha =
                clamp(
                    levelIntroTimer /
                    1.2,
                    0,
                    1
                );


            ctx.fillStyle =
                "rgba(2,6,23," +
                (
                    alpha *
                    .43
                ) +
                ")";


            ctx.fillRect(
                0,
                0,
                canvasWidth,
                canvasHeight
            );


            ctx.textAlign =
                "center";


            ctx.fillStyle =
                "rgba(191,219,254," +
                alpha +
                ")";


            ctx.font =
                "900 " +
                Math.floor(
                    canvasWidth /
                    20
                ) +
                "px Arial";


            ctx.fillText(
                "LEVEL " +
                String(
                    level
                ).padStart(
                    2,
                    "0"
                ),
                canvasWidth /
                2,
                canvasHeight *
                .44
            );


            ctx.fillStyle =
                "rgba(100,116,139," +
                alpha +
                ")";


            ctx.font =
                "700 " +
                Math.floor(
                    canvasWidth /
                    70
                ) +
                "px Arial";


            ctx.fillText(
                level %
                3 ===
                0
                    ? "☠ BOSS BATTLE ☠"
                    : "BATTLE READY",
                canvasWidth /
                2,
                canvasHeight *
                .50
            );

        }


        ctx.restore();

    }


    /* ========================================================
       GAME LOOP
    ======================================================== */

    function gameLoop(
        timestamp
    ) {

        if (
            !lastTime
        ) {

            lastTime =
                timestamp;

        }


        const dt =
            Math.min(
                (
                    timestamp -
                    lastTime
                ) /
                1000,
                .033
            );


        lastTime =
            timestamp;


        update(
            dt
        );


        draw();


        requestAnimationFrame(
            gameLoop
        );

    }


    /* ========================================================
       PAUSE
    ======================================================== */

    function togglePause() {

        if (
            gameOver ||
            victory
        ) {

            return;

        }


        paused =
            !paused;


        if (
            paused
        ) {

            overlay.classList.add(
                "show"
            );


            overlayIcon.textContent =
                "⏸";


            overlayTitle.textContent =
                "Game Paused";


            overlayText.textContent =
                "Your warrior is waiting for the battle to continue.";


            overlayButton.textContent =
                "▶ RESUME";


            pauseButton.textContent =
                "▶ RESUME";


        } else {

            overlay.classList.remove(
                "show"
            );


            pauseButton.textContent =
                "⏸ PAUSE";

        }

    }


    pauseButton.addEventListener(
        "click",
        togglePause
    );


    overlayButton.addEventListener(
        "click",
        function () {

            if (
                gameOver ||
                victory
            ) {

                restartGame();

            } else {

                togglePause();

            }

        }
    );


    /* ========================================================
       RESTART
    ======================================================== */

    function restartGame() {

        paused =
            false;

        gameOver =
            false;

        victory =
            false;

        level =
            1;

        score =
            0;

        combo =
            0;

        comboTimer =
            0;

        cameraX =
            0;

        screenShake =
            0;

        player.x =
            260;

        player.y =
            groundY -
            player.height;

        player.vx =
            0;

        player.vy =
            0;

        player.health =
            player.maxHealth;

        player.stamina =
            player.maxStamina;

        player.facing =
            1;

        player.grounded =
            true;

        player.state =
            "idle";

        player.stateTime =
            0;

        player.attackTime =
            0;

        player.attackCooldown =
            0;

        player.attackStep =
            1;

        player.attackHit =
            false;

        player.specialTime =
            0;

        player.hurtTime =
            0;

        player.invulnerable =
            0;

        player.dead =
            false;


        enemies.length =
            0;

        particles.length =
            0;

        effects.length =
            0;

        damageNumbers.length =
            0;


        overlay.classList.remove(
            "show"
        );


        pauseButton.textContent =
            "⏸ PAUSE";


        spawnLevel();


        updateUI();

    }


    restartButton.addEventListener(
        "click",
        restartGame
    );


    /* ========================================================
       GAME OVER
    ======================================================== */

    function showGameOver() {

        overlay.classList.add(
            "show"
        );


        overlayIcon.textContent =
            "💀";


        overlayTitle.textContent =
            "GAME OVER";


        overlayText.textContent =
            "Your warrior has fallen. Final score: " +
            Math.floor(
                score
            );


        overlayButton.textContent =
            "↻ PLAY AGAIN";

    }


    /* ========================================================
       VICTORY
    ======================================================== */

    function showVictory() {

        overlay.classList.add(
            "show"
        );


        overlayIcon.textContent =
            "🏆";


        overlayTitle.textContent =
            "VICTORY";


        overlayText.textContent =
            "You conquered the arena. Final score: " +
            Math.floor(
                score
            );


        overlayButton.textContent =
            "↻ PLAY AGAIN";


        particleBurst(
            player.x,
            player.y +
            50,
            "magic",
            70
        );


        particleBurst(
            player.x,
            player.y +
            50,
            "energy",
            50
        );

    }


    /* ========================================================
       CANVAS ATTACK
    ======================================================== */

    canvas.addEventListener(
        "pointerdown",
        function () {

            if (
                !paused &&
                !gameOver &&
                !victory
            ) {

                attack();

            }

        }
    );


    /* ========================================================
       MOBILE BUTTONS
    ======================================================== */

    document
        .querySelectorAll(
            ".sf-mobile-btn"
        )
        .forEach(
            function (
                button
            ) {

                const action =
                    button.getAttribute(
                        "data-action"
                    );


                function down(
                    event
                ) {

                    event.preventDefault();


                    if (
                        action ===
                        "left"
                    ) {

                        touch.left =
                            true;

                    }


                    if (
                        action ===
                        "right"
                    ) {

                        touch.right =
                            true;

                    }


                    if (
                        action ===
                        "jump"
                    ) {

                        jump();

                    }


                    if (
                        action ===
                        "attack"
                    ) {

                        attack();

                    }


                    if (
                        action ===
                        "special"
                    ) {

                        specialAttack();

                    }

                }


                function up(
                    event
                ) {

                    event.preventDefault();


                    if (
                        action ===
                        "left"
                    ) {

                        touch.left =
                            false;

                    }


                    if (
                        action ===
                        "right"
                    ) {

                        touch.right =
                            false;

                    }

                }


                button.addEventListener(
                    "pointerdown",
                    down
                );


                button.addEventListener(
                    "pointerup",
                    up
                );


                button.addEventListener(
                    "pointercancel",
                    up
                );


                button.addEventListener(
                    "pointerleave",
                    up
                );

            }
        );


    /* ========================================================
       INITIALIZE
    ======================================================== */

    spawnLevel();

    updateUI();

    requestAnimationFrame(
        gameLoop
    );


    /* ========================================================
       ASSET RETRY
       Useful if browser loads page faster than image files.
    ======================================================== */

    setInterval(
        function () {

            Object.keys(
                assets
            ).forEach(
                function (
                    key
                ) {

                    if (
                        imageStates[key] !==
                        "ready"
                    ) {

                        imageStates[key] =
                            null;

                        loadAsset(
                            key
                        );

                    }

                }
            );

        },
        2000
    );


})();

</script>


<?php

require "footer.php";

?>