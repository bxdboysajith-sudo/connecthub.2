<?php
// ============================================================
// CONNECTHUB NINJA RUNNER
// COMPLETE REPLACEMENT
// ============================================================
//
// EXISTING IMAGE PACK SUPPORTED:
//
// game-assets/ninja/
// ├── player/
// │   ├── ninja_idle.png
// │   ├── ninja_run1.png
// │   ├── ninja_run2.png
// │   ├── ninja_run3.png
// │   ├── ninja_run4.png
// │   ├── ninja_jump.png
// │   ├── ninja_double_jump.png
// │   ├── ninja_slide.png
// │   ├── ninja_attack.png
// │   ├── ninja_hurt.png
// │   └── ninja_death.png
// │
// ├── powerups/
// │   ├── coin.png
// │   ├── star.png
// │   ├── boost.png
// │   ├── shield.png
// │   └── magnet.png
// │
// ├── obstacles/
// │   ├── spike_trap.png
// │   ├── wooden_crate.png
// │   ├── rock.png
// │   ├── barrier.png
// │   ├── cannon.png
// │   ├── hanging_spike.png
// │   ├── falling_log.png
// │   ├── fire_pit.png
// │   ├── tall_wall.png
// │   └── ninja_statue.png
// │
// ├── effects/
// │   ├── dash_trail.png
// │   ├── slash_effect.png
// │   ├── spin_attack.png
// │   ├── land_impact.png
// │   ├── jump_dust.png
// │   ├── coin_spark.png
// │   ├── star_burst.png
// │   ├── boost_glow.png
// │   ├── shield_flash.png
// │   ├── magnet_pulse.png
// │   ├── hurt_flash.png
// │   └── death_smoke.png
// │
// └── backgrounds/
//     ├── day_village.jpg
//     ├── night_rooftops.jpg
//     └── sunset_mountains.jpg
//
// ============================================================

require "config.php";

login_required();

require "header.php";

?>

<style>

/* ============================================================
   MAIN PAGE
============================================================ */

.nr-page {

    width:100%;

    min-height:
        calc(100vh - 68px);

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

.nr-topbar {

    width:100%;

    max-width:1350px;

    display:flex;

    align-items:center;

    justify-content:space-between;

    gap:15px;

    margin-bottom:10px;

}


.nr-brand {

    display:flex;

    align-items:center;

    gap:11px;

}


.nr-brand-icon {

    position:relative;

    width:50px;

    height:50px;

    display:grid;

    place-items:center;

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .30
        );

    border-radius:16px;

    background:
        linear-gradient(
            135deg,
            #111c44,
            #2563eb,
            #7c3aed
        );

    box-shadow:
        0 0 30px
        rgba(
            37,
            99,
            235,
            .28
        );

    font-size:25px;

    animation:
        nrLogoFloat
        2.8s
        ease-in-out
        infinite;

}


.nr-brand-icon::after {

    content:"";

    position:absolute;

    inset:-4px;

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .13
        );

    border-radius:19px;

    animation:
        nrLogoPulse
        2s
        ease-in-out
        infinite;

}


@keyframes nrLogoFloat {

    0%,
    100% {

        transform:
            translateY(0);

    }

    50% {

        transform:
            translateY(-4px)
            rotate(2deg);

    }

}


@keyframes nrLogoPulse {

    0%,
    100% {

        opacity:.85;

        transform:
            scale(1);

    }

    50% {

        opacity:.25;

        transform:
            scale(1.13);

    }

}


.nr-brand-text strong {

    display:block;

    color:#fff;

    font-size:21px;

    font-weight:1000;

}


.nr-brand-text small {

    display:block;

    margin-top:3px;

    color:#7186a6;

    font-size:7px;

    letter-spacing:1.4px;

}


.nr-top-actions {

    display:flex;

    flex-wrap:wrap;

    gap:7px;

}


.nr-button {

    min-height:38px;

    padding:
        0 13px;

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .16
        );

    border-radius:11px;

    color:#dbeafe;

    background:
        rgba(
            3,
            12,
            29,
            .83
        );

    font-size:7px;

    font-weight:950;

    cursor:pointer;

    transition:
        transform
        .18s
        ease,
        background
        .18s
        ease,
        border-color
        .18s
        ease;

}


.nr-button:hover {

    transform:
        translateY(-2px);

    border-color:
        rgba(
            96,
            165,
            250,
            .35
        );

    background:
        rgba(
            37,
            99,
            235,
            .18
        );

}


.nr-button.restart {

    color:#fecaca;

}


.nr-button.bank {

    color:#fde68a;

}


/* ============================================================
   GAME FRAME
============================================================ */

.nr-frame {

    position:relative;

    width:100%;

    max-width:1350px;

    aspect-ratio:
        16 / 9;

    min-height:550px;

    overflow:hidden;

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .23
        );

    border-radius:23px;

    background:#020617;

    box-shadow:

        0 30px 90px
        rgba(
            0,
            0,
            0,
            .50
        ),

        0 0 55px
        rgba(
            37,
            99,
            235,
            .10
        );

}


#nrCanvas {

    width:100%;

    height:100%;

    display:block;

    background:#020617;

    touch-action:none;

}


/* ============================================================
   HUD
============================================================ */

.nr-hud {

    position:absolute;

    top:12px;

    left:12px;

    right:12px;

    z-index:30;

    display:grid;

    grid-template-columns:
        1fr
        auto
        auto;

    align-items:start;

    gap:12px;

    pointer-events:none;

}


.nr-player-hud {

    display:flex;

    align-items:flex-start;

    gap:9px;

}


.nr-avatar {

    width:47px;

    height:47px;

    flex:0 0 47px;

    display:grid;

    place-items:center;

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .24
        );

    border-radius:13px;

    background:
        rgba(
            2,
            6,
            23,
            .80
        );

    backdrop-filter:
        blur(10px);

    box-shadow:
        0 8px 30px
        rgba(
            0,
            0,
            0,
            .18
        );

    font-size:22px;

}


/* ============================================================
   PLAYER INFORMATION
============================================================ */

.nr-player-info {

    width:300px;

}


.nr-player-name-line {

    display:flex;

    justify-content:space-between;

    align-items:center;

}


.nr-player-name {

    color:#fff;

    font-size:9px;

    font-weight:1000;

}


.nr-player-title {

    color:#60a5fa;

    font-size:5px;

    font-weight:950;

    letter-spacing:1px;

}


.nr-level-line {

    display:flex;

    justify-content:space-between;

    margin-top:3px;

}


.nr-level {

    color:#94a3b8;

    font-size:6px;

    font-weight:900;

}


.nr-health-number {

    color:#fca5a5;

    font-size:6px;

    font-weight:950;

}


/* ============================================================
   THREE HEARTS
============================================================ */

.nr-hearts {

    display:flex;

    gap:5px;

    margin-top:5px;

}


.nr-heart {

    width:57px;

    height:19px;

    display:flex;

    align-items:center;

    justify-content:center;

    gap:3px;

    border:
        1px solid
        rgba(
            239,
            68,
            68,
            .18
        );

    border-radius:7px;

    color:#ef4444;

    background:
        linear-gradient(
            90deg,
            rgba(
                127,
                29,
                29,
                .75
            ),
            rgba(
                185,
                28,
                28,
                .52
            )
        );

    box-shadow:
        0 0 10px
        rgba(
            239,
            68,
            68,
            .08
        );

    font-size:10px;

    transition:
        .18s
        ease;

}


.nr-heart.empty {

    filter:
        grayscale(1);

    opacity:.38;

    color:#475569;

    background:
        rgba(
            15,
            23,
            42,
            .70
        );

    box-shadow:none;

}


.nr-heart.break {

    animation:
        nrHeartBreak
        .38s
        ease;

}


@keyframes nrHeartBreak {

    0% {

        transform:
            scale(1);

    }

    35% {

        transform:
            scale(1.25);

    }

    100% {

        transform:
            scale(1);

    }

}


/* ============================================================
   BAR SYSTEM
============================================================ */

.nr-stat-label {

    display:flex;

    align-items:center;

    justify-content:space-between;

    margin-top:4px;

    color:#64748b;

    font-size:5px;

    font-weight:900;

    letter-spacing:.7px;

}


.nr-stat-value {

    color:#cbd5e1;

}


.nr-bar {

    width:100%;

    height:7px;

    margin-top:2px;

    overflow:hidden;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .06
        );

    border-radius:999px;

    background:
        rgba(
            2,
            6,
            23,
            .90
        );

}


.nr-fill {

    width:100%;

    height:100%;

    border-radius:999px;

    transition:
        width
        .12s
        ease;

}


/* HEALTH ENERGY XP */

.nr-energy-fill {

    background:
        linear-gradient(
            90deg,
            #0891b2,
            #22d3ee,
            #60a5fa
        );

    box-shadow:
        0 0 14px
        rgba(
            34,
            211,
            238,
            .30
        );

}


.nr-xp-fill {

    width:0%;

    background:
        linear-gradient(
            90deg,
            #7c3aed,
            #a855f7,
            #ec4899
        );

}


.nr-combo-fill {

    width:0%;

    background:
        linear-gradient(
            90deg,
            #f97316,
            #facc15,
            #fde68a
        );

}


/* ============================================================
   CENTER STATUS
============================================================ */

.nr-center-status {

    display:flex;

    flex-direction:column;

    align-items:center;

    gap:6px;

}


.nr-mission {

    min-width:220px;

    padding:
        7px
        13px;

    text-align:center;

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .15
        );

    border-radius:999px;

    color:#bfdbfe;

    background:
        rgba(
            2,
            6,
            23,
            .67
        );

    backdrop-filter:
        blur(10px);

    font-size:6px;

    font-weight:950;

}


.nr-status-row {

    display:flex;

    gap:5px;

}


.nr-chip {

    padding:
        5px 8px;

    border:
        1px solid
        rgba(
            148,
            163,
            184,
            .12
        );

    border-radius:999px;

    color:#64748b;

    background:
        rgba(
            2,
            6,
            23,
            .57
        );

    font-size:5px;

    font-weight:950;

}


.nr-chip.active {

    color:#67e8f9;

    border-color:
        rgba(
            34,
            211,
            238,
            .23
        );

    box-shadow:
        0 0 14px
        rgba(
            34,
            211,
            238,
            .08
        );

}


/* ============================================================
   SCORE
============================================================ */

.nr-score-box {

    min-width:145px;

    padding:
        5px 0;

    text-align:right;

}


.nr-score-label {

    color:#64748b;

    font-size:6px;

    font-weight:950;

    letter-spacing:1.4px;

}


.nr-score {

    margin-top:2px;

    color:#fff;

    font-size:25px;

    font-weight:1000;

    line-height:1;

    text-shadow:
        0 0 20px
        rgba(
            96,
            165,
            250,
            .28
        );

}


.nr-distance {

    margin-top:3px;

    color:#60a5fa;

    font-size:7px;

    font-weight:950;

}


.nr-score-pills {

    display:flex;

    justify-content:flex-end;

    gap:5px;

    margin-top:4px;

}


.nr-score-pill {

    padding:
        4px 7px;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .08
        );

    border-radius:999px;

    color:#cbd5e1;

    background:
        rgba(
            2,
            6,
            23,
            .55
        );

    font-size:5px;

    font-weight:950;

}


/* ============================================================
   EARNINGS PANEL
============================================================ */

.nr-earnings-panel {

    position:absolute;

    top:95px;

    right:12px;

    z-index:29;

    width:185px;

    padding:
        9px
        11px;

    border:
        1px solid
        rgba(
            250,
            204,
            21,
            .17
        );

    border-radius:12px;

    background:
        rgba(
            30,
            25,
            5,
            .62
        );

    backdrop-filter:
        blur(9px);

}


.nr-earnings-title {

    color:#a8a29e;

    font-size:5px;

    font-weight:950;

    letter-spacing:1px;

}


.nr-earnings-money {

    margin-top:2px;

    color:#fde68a;

    font-size:17px;

    font-weight:1000;

}


.nr-earnings-sub {

    display:flex;

    justify-content:space-between;

    margin-top:2px;

    color:#78716c;

    font-size:5px;

}


.nr-earnings-bar {

    width:100%;

    height:5px;

    margin-top:5px;

    overflow:hidden;

    border-radius:999px;

    background:
        rgba(
            24,
            20,
            5,
            .90
        );

}


.nr-earnings-fill {

    width:0%;

    height:100%;

    background:
        linear-gradient(
            90deg,
            #ca8a04,
            #facc15,
            #fde68a
        );

}


/* ============================================================
   BOOST PANEL
============================================================ */

.nr-boost-panel {

    position:absolute;

    bottom:58px;

    left:12px;

    z-index:29;

    width:205px;

    padding:
        8px
        10px;

    border:
        1px solid
        rgba(
            34,
            211,
            238,
            .16
        );

    border-radius:11px;

    background:
        rgba(
            3,
            12,
            29,
            .69
        );

    backdrop-filter:
        blur(8px);

    opacity:0;

    transform:
        translateY(8px);

    transition:
        .20s
        ease;

}


.nr-boost-panel.show {

    opacity:1;

    transform:
        translateY(0);

}


.nr-boost-head {

    display:flex;

    justify-content:space-between;

    color:#67e8f9;

    font-size:5px;

    font-weight:950;

}


.nr-boost-time {

    color:#cffafe;

}


.nr-boost-bar {

    width:100%;

    height:5px;

    margin-top:4px;

    overflow:hidden;

    border-radius:999px;

    background:
        rgba(
            2,
            6,
            23,
            .86
        );

}


.nr-boost-fill {

    width:0%;

    height:100%;

    background:
        linear-gradient(
            90deg,
            #06b6d4,
            #22d3ee,
            #67e8f9
        );

}


/* ============================================================
   HIT FLASH
============================================================ */

.nr-hit-flash {

    position:absolute;

    inset:0;

    z-index:44;

    pointer-events:none;

    opacity:0;

    background:
        radial-gradient(
            circle,
            rgba(
                239,
                68,
                68,
                .08
            ),
            rgba(
                239,
                68,
                68,
                .30
            )
        );

}


.nr-hit-flash.show {

    animation:
        nrHitFlash
        .28s
        ease-out
        forwards;

}


@keyframes nrHitFlash {

    0% {

        opacity:.85;

    }

    100% {

        opacity:0;

    }

}


/* ============================================================
   FLOATING COIN POPUP
============================================================ */

.nr-popup {

    position:absolute;

    z-index:42;

    pointer-events:none;

    opacity:0;

    color:#fde68a;

    font-size:15px;

    font-weight:1000;

    text-shadow:
        0 0 12px
        rgba(
            250,
            204,
            21,
            .35
        );

}


.nr-popup.show {

    animation:
        nrPopup
        .65s
        ease-out
        forwards;

}


@keyframes nrPopup {

    0% {

        opacity:1;

        transform:
            translateY(0)
            scale(.75);

    }

    100% {

        opacity:0;

        transform:
            translateY(-45px)
            scale(1.12);

    }

}


/* ============================================================
   PAUSE / GAME OVER OVERLAY
============================================================ */

.nr-overlay {

    position:absolute;

    inset:0;

    z-index:60;

    display:none;

    align-items:center;

    justify-content:center;

    background:
        rgba(
            2,
            6,
            23,
            .76
        );

    backdrop-filter:
        blur(10px);

}


.nr-overlay.show {

    display:flex;

}


.nr-overlay-card {

    width:
        min(
            500px,
            calc(
                100% -
                28px
            )
        );

    padding:
        29px;

    text-align:center;

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .22
        );

    border-radius:23px;

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
                .97
            )
        );

    box-shadow:
        0 30px 90px
        rgba(
            0,
            0,
            0,
            .55
        );

}


.nr-overlay-icon {

    width:78px;

    height:78px;

    margin:
        0 auto
        13px;

    display:grid;

    place-items:center;

    border-radius:23px;

    background:
        rgba(
            37,
            99,
            235,
            .15
        );

    font-size:35px;

}


.nr-overlay-card h2 {

    margin:
        0
        0
        7px;

    color:#fff;

    font-size:29px;

    font-weight:1000;

}


.nr-overlay-card p {

    margin:
        0
        0
        17px;

    color:#8ba0be;

    font-size:8px;

    line-height:1.8;

}


.nr-result-grid {

    display:grid;

    grid-template-columns:
        repeat(
            4,
            1fr
        );

    gap:6px;

    margin-bottom:14px;

}


.nr-result-card {

    padding:
        8px;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            .07
        );

    border-radius:10px;

    background:
        rgba(
            2,
            6,
            23,
            .50
        );

}


.nr-result-card span {

    display:block;

    color:#64748b;

    font-size:5px;

}


.nr-result-card strong {

    display:block;

    margin-top:3px;

    color:#e2e8f0;

    font-size:10px;

}


.nr-result-card.money strong {

    color:#fde68a;

}


.nr-overlay-money {

    margin-bottom:14px;

    padding:
        10px;

    border:
        1px solid
        rgba(
            250,
            204,
            21,
            .13
        );

    border-radius:12px;

    background:
        rgba(
            30,
            25,
            5,
            .42
        );

}


.nr-overlay-money span {

    display:block;

    color:#78716c;

    font-size:6px;

    font-weight:950;

}


.nr-overlay-money strong {

    display:block;

    margin-top:2px;

    color:#fde68a;

    font-size:23px;

}


.nr-overlay-buttons {

    display:flex;

    flex-wrap:wrap;

    justify-content:center;

    gap:7px;

}


.nr-overlay-button {

    min-height:42px;

    padding:
        0 15px;

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .17
        );

    border-radius:10px;

    color:#fff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    font-size:7px;

    font-weight:950;

    cursor:pointer;

}


.nr-overlay-button.secondary {

    color:#bfdbfe;

    background:
        rgba(
            30,
            41,
            59,
            .82
        );

}


.nr-overlay-status {

    min-height:16px;

    margin-top:8px;

    color:#67e8f9;

    font-size:6px;

    font-weight:900;

}


/* ============================================================
   MOBILE CONTROLS
============================================================ */

.nr-mobile-controls {

    position:absolute;

    left:0;

    right:0;

    bottom:14px;

    z-index:40;

    display:none;

    justify-content:space-between;

    padding:
        0 13px;

    pointer-events:none;

}


.nr-mobile-left,
.nr-mobile-right {

    display:flex;

    gap:7px;

}


.nr-mobile-button {

    width:57px;

    height:57px;

    display:grid;

    place-items:center;

    border:
        1px solid
        rgba(
            147,
            197,
            253,
            .19
        );

    border-radius:16px;

    color:#fff;

    background:
        rgba(
            2,
            6,
            23,
            .74
        );

    backdrop-filter:
        blur(8px);

    font-size:18px;

    font-weight:950;

    pointer-events:auto;

    user-select:none;

    -webkit-user-select:none;

    touch-action:none;

}


.nr-mobile-button:active {

    transform:
        scale(.94);

    background:
        rgba(
            37,
            99,
            235,
            .45
        );

}


.nr-mobile-button.attack {

    width:75px;

    height:75px;

    border-color:
        rgba(
            96,
            165,
            250,
            .32
        );

    background:
        linear-gradient(
            135deg,
            rgba(
                37,
                99,
                235,
                .95
            ),
            rgba(
                79,
                70,
                229,
                .95
            )
        );

    box-shadow:
        0 0 30px
        rgba(
            37,
            99,
            235,
            .20
        );

}


/* ============================================================
   INFO CARDS
============================================================ */

.nr-info {

    width:100%;

    max-width:1350px;

    margin-top:9px;

    display:grid;

    grid-template-columns:
        repeat(
            8,
            1fr
        );

    gap:6px;

}


.nr-info-card {

    padding:
        9px 10px;

    border:
        1px solid
        rgba(
            96,
            165,
            250,
            .09
        );

    border-radius:11px;

    background:
        rgba(
            3,
            12,
            29,
            .73
        );

}


.nr-info-card span {

    display:block;

    color:#475569;

    font-size:5px;

    font-weight:850;

}


.nr-info-card strong {

    display:block;

    margin-top:3px;

    color:#cbd5e1;

    font-size:7px;

}


.nr-info-card.money strong {

    color:#fde68a;

}


/* ============================================================
   RESPONSIVE
============================================================ */

@media(
    max-width:1100px
){

    .nr-info {

        grid-template-columns:
            repeat(
                4,
                1fr
            );

    }

}


@media(
    max-width:900px
){

    .nr-frame {

        aspect-ratio:
            4 / 5;

        min-height:600px;

    }


    .nr-hud {

        grid-template-columns:
            1fr
            auto;

    }


    .nr-center-status {

        position:absolute;

        top:66px;

        left:50%;

        transform:
            translateX(-50%);

    }


    .nr-mobile-controls {

        display:flex;

    }


    .nr-player-info {

        width:225px;

    }


    .nr-earnings-panel {

        top:115px;

        right:10px;

    }


    .nr-score-box {

        min-width:100px;

    }


    .nr-score {

        font-size:19px;

    }


    .nr-info {

        grid-template-columns:
            repeat(
                3,
                1fr
            );

    }

}


@media(
    max-width:600px
){

    .nr-page {

        padding:
            8px
            4px
            42px;

    }


    .nr-brand-text strong {

        font-size:16px;

    }


    .nr-brand-text small {

        font-size:5px;

    }


    .nr-brand-icon {

        width:41px;

        height:41px;

        font-size:20px;

    }


    .nr-button {

        min-height:33px;

        padding:
            0 8px;

        font-size:6px;

    }


    .nr-frame {

        aspect-ratio:
            9 / 16;

        min-height:680px;

        border-radius:16px;

    }


    .nr-player-info {

        width:150px;

    }


    .nr-avatar {

        width:37px;

        height:37px;

        flex-basis:37px;

        font-size:18px;

    }


    .nr-player-name {

        font-size:7px;

    }


    .nr-player-title {

        display:none;

    }


    .nr-heart {

        width:42px;

        height:16px;

        font-size:8px;

    }


    .nr-center-status {

        top:73px;

    }


    .nr-mission {

        min-width:160px;

        font-size:5px;

    }


    .nr-earnings-panel {

        top:116px;

        width:135px;

        right:7px;

    }


    .nr-score {

        font-size:16px;

    }


    .nr-info {

        grid-template-columns:
            1fr
            1fr;

    }


    .nr-result-grid {

        grid-template-columns:
            repeat(
                2,
                1fr
            );

    }

}

</style>


<div class="nr-page">


    <!-- ========================================================
         TOP BAR
    ========================================================= -->

    <div class="nr-topbar">


        <div class="nr-brand">


            <div class="nr-brand-icon">
                🥷
            </div>


            <div class="nr-brand-text">

                <strong>
                    Ninja Runner
                </strong>

                <small>
                    CONNECTHUB ORIGINAL • NINJA ADVENTURE
                </small>

            </div>


        </div>


        <div class="nr-top-actions">


            <button
                type="button"
                class="nr-button"
                id="nrPauseButton"
            >
                ⏸ PAUSE
            </button>


            <button
                type="button"
                class="
                    nr-button
                    restart
                "
                id="nrRestartButton"
            >
                ↻ RESTART
            </button>


            <button
                type="button"
                class="
                    nr-button
                    bank
                "
                id="nrTopBankButton"
            >
                🏦 BANKING
            </button>


        </div>


    </div>


    <!-- ========================================================
         GAME
    ========================================================= -->

    <div
        class="nr-frame"
        id="nrFrame"
    >


        <canvas
            id="nrCanvas"
        ></canvas>


        <!-- ====================================================
             HUD
        ===================================================== -->

        <div class="nr-hud">


            <!-- PLAYER -->

            <div class="nr-player-hud">


                <div class="nr-avatar">

                    🥷

                </div>


                <div class="nr-player-info">


                    <div class="nr-player-name-line">


                        <span class="nr-player-name">
                            CONNECTHUB NINJA
                        </span>


                        <span class="nr-player-title">
                            SHADOW RUNNER
                        </span>


                    </div>


                    <div class="nr-level-line">


                        <span
                            class="nr-level"
                            id="nrLevel"
                        >
                            LEVEL 01
                        </span>


                        <span
                            class="nr-health-number"
                            id="nrHealthText"
                        >
                            3 / 3 HEARTS
                        </span>


                    </div>


                    <!-- 3 HEALTH -->

                    <div class="nr-hearts">


                        <div
                            class="
                                nr-heart
                            "
                            id="nrHeart1"
                        >
                            ❤️
                        </div>


                        <div
                            class="
                                nr-heart
                            "
                            id="nrHeart2"
                        >
                            ❤️
                        </div>


                        <div
                            class="
                                nr-heart
                            "
                            id="nrHeart3"
                        >
                            ❤️
                        </div>


                    </div>


                    <!-- ENERGY -->

                    <div class="nr-stat-label">

                        <span>
                            ENERGY
                        </span>

                        <span
                            class="nr-stat-value"
                            id="nrEnergyText"
                        >
                            100%
                        </span>

                    </div>


                    <div class="nr-bar">

                        <div
                            class="
                                nr-fill
                                nr-energy-fill
                            "
                            id="nrEnergyFill"
                        ></div>

                    </div>


                    <!-- XP -->

                    <div class="nr-stat-label">

                        <span>
                            XP
                        </span>

                        <span
                            class="nr-stat-value"
                            id="nrXPText"
                        >
                            0 / 1000
                        </span>

                    </div>


                    <div class="nr-bar">

                        <div
                            class="
                                nr-fill
                                nr-xp-fill
                            "
                            id="nrXPFill"
                        ></div>

                    </div>


                    <!-- COMBO -->

                    <div class="nr-stat-label">

                        <span>
                            COMBO POWER
                        </span>

                        <span
                            class="nr-stat-value"
                            id="nrComboText"
                        >
                            x1
                        </span>

                    </div>


                    <div class="nr-bar">

                        <div
                            class="
                                nr-fill
                                nr-combo-fill
                            "
                            id="nrComboFill"
                        ></div>

                    </div>


                </div>


            </div>


            <!-- CENTER STATUS -->

            <div class="nr-center-status">


                <div
                    class="nr-mission"
                    id="nrMission"
                >
                    🥷 RUN • SURVIVE • COLLECT
                </div>


                <div class="nr-status-row">


                    <div
                        class="nr-chip"
                        id="nrShieldChip"
                    >
                        🛡 SHIELD OFF
                    </div>


                    <div
                        class="nr-chip"
                        id="nrMagnetChip"
                    >
                        🧲 MAGNET OFF
                    </div>


                    <div
                        class="nr-chip"
                        id="nrStarChip"
                    >
                        ⭐ STAR OFF
                    </div>


                </div>


            </div>


            <!-- SCORE -->

            <div class="nr-score-box">


                <div class="nr-score-label">
                    SCORE
                </div>


                <div
                    class="nr-score"
                    id="nrScore"
                >
                    000000
                </div>


                <div
                    class="nr-distance"
                    id="nrDistance"
                >
                    000 M
                </div>


                <div class="nr-score-pills">


                    <div
                        class="nr-score-pill"
                        id="nrCoins"
                    >
                        🪙 0
                    </div>


                    <div
                        class="nr-score-pill"
                        id="nrMultiplier"
                    >
                        ×1
                    </div>


                </div>


            </div>


        </div>


        <!-- ====================================================
             EARNINGS
        ===================================================== -->

        <div class="nr-earnings-panel">


            <div class="nr-earnings-title">
                CONNECTHUB GAME EARNINGS
            </div>


            <div
                class="nr-earnings-money"
                id="nrEarnings"
            >
                0 CREDITS
            </div>


            <div class="nr-earnings-sub">


                <span>
                    COINS
                </span>


                <span id="nrEarningCoinText">
                    0
                </span>


            </div>


            <div class="nr-earnings-bar">


                <div
                    class="nr-earnings-fill"
                    id="nrEarningsFill"
                ></div>


            </div>


        </div>


        <!-- ====================================================
             BOOST
        ===================================================== -->

        <div
            class="nr-boost-panel"
            id="nrBoostPanel"
        >


            <div class="nr-boost-head">


                <span>
                    ⚡ SPEED BOOST
                </span>


                <span
                    class="nr-boost-time"
                    id="nrBoostTime"
                >
                    0s
                </span>


            </div>


            <div class="nr-boost-bar">


                <div
                    class="nr-boost-fill"
                    id="nrBoostFill"
                ></div>


            </div>


        </div>


        <!-- ====================================================
             HIT FLASH
        ===================================================== -->

        <div
            class="nr-hit-flash"
            id="nrHitFlash"
        ></div>


        <!-- ====================================================
             POPUP
        ===================================================== -->

        <div
            class="nr-popup"
            id="nrPopup"
        >
            +1 CREDIT
        </div>


        <!-- ====================================================
             OVERLAY
        ===================================================== -->

        <div
            class="nr-overlay"
            id="nrOverlay"
        >


            <div class="nr-overlay-card">


                <div
                    class="nr-overlay-icon"
                    id="nrOverlayIcon"
                >
                    ⏸
                </div>


                <h2
                    id="nrOverlayTitle"
                >
                    GAME PAUSED
                </h2>


                <p
                    id="nrOverlayText"
                >
                    The ninja is waiting.
                </p>


                <!-- RESULTS -->

                <div class="nr-result-grid">


                    <div class="nr-result-card">


                        <span>
                            SCORE
                        </span>


                        <strong
                            id="nrResultScore"
                        >
                            0
                        </strong>


                    </div>


                    <div class="nr-result-card">


                        <span>
                            DISTANCE
                        </span>


                        <strong
                            id="nrResultDistance"
                        >
                            0 M
                        </strong>


                    </div>


                    <div class="nr-result-card">


                        <span>
                            COINS
                        </span>


                        <strong
                            id="nrResultCoins"
                        >
                            0
                        </strong>


                    </div>


                    <div
                        class="
                            nr-result-card
                            money
                        "
                    >


                        <span>
                            EARNINGS
                        </span>


                        <strong
                            id="nrResultCredits"
                        >
                            0
                        </strong>


                    </div>


                </div>


                <div class="nr-overlay-money">


                    <span>
                        GAME EARNINGS FROM THIS RUN
                    </span>


                    <strong
                        id="nrOverlayEarnings"
                    >
                        0 CREDITS
                    </strong>


                </div>


                <div class="nr-overlay-buttons">


                    <button
                        type="button"
                        class="nr-overlay-button"
                        id="nrOverlayButton"
                    >
                        ▶ RESUME
                    </button>


                    <button
                        type="button"
                        class="
                            nr-overlay-button
                            secondary
                        "
                        id="nrSaveButton"
                    >
                        💰 SAVE EARNINGS
                    </button>


                    <button
                        type="button"
                        class="
                            nr-overlay-button
                            secondary
                        "
                        id="nrDepositButton"
                    >
                        🏦 DEPOSIT
                    </button>


                    <button
                        type="button"
                        class="
                            nr-overlay-button
                            secondary
                        "
                        id="nrOverlayRestart"
                    >
                        ↻ RUN AGAIN
                    </button>


                </div>


                <div
                    class="nr-overlay-status"
                    id="nrOverlayStatus"
                ></div>


            </div>


        </div>


        <!-- ====================================================
             MOBILE
        ===================================================== -->

        <div class="nr-mobile-controls">


            <div class="nr-mobile-left">


                <button
                    type="button"
                    class="nr-mobile-button"
                    data-nr-action="jump"
                >
                    ▲
                </button>


                <button
                    type="button"
                    class="nr-mobile-button"
                    data-nr-action="slide"
                >
                    ▼
                </button>


            </div>


            <div class="nr-mobile-right">


                <button
                    type="button"
                    class="
                        nr-mobile-button
                        attack
                    "
                    data-nr-action="attack"
                >
                    ⚔️
                </button>


            </div>


        </div>


    </div>


    <!-- ========================================================
         INFO
    ========================================================= -->

    <div class="nr-info">


        <div class="nr-info-card">


            <span>
                HEALTH
            </span>


            <strong>
                ❤️ ❤️ ❤️
            </strong>


        </div>


        <div class="nr-info-card">


            <span>
                JUMP
            </span>


            <strong>
                W / ↑ / SPACE
            </strong>


        </div>


        <div class="nr-info-card">


            <span>
                SLIDE
            </span>


            <strong>
                S / ↓
            </strong>


        </div>


        <div class="nr-info-card">


            <span>
                ATTACK
            </span>


            <strong>
                J / CLICK
            </strong>


        </div>


        <div class="nr-info-card">


            <span>
                COMBO
            </span>


            <strong>
                HIT × MULTIPLIER
            </strong>


        </div>


        <div class="nr-info-card">


            <span>
                POWER
            </span>


            <strong>
                🛡 ⚡ 🧲 ⭐
            </strong>


        </div>


        <div class="
            nr-info-card
            money
        ">


            <span>
                GAME EARNINGS
            </span>


            <strong>
                COIN → CREDIT
            </strong>


        </div>


        <div class="nr-info-card">


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

(function () {

    "use strict";


    // ========================================================
    // CANVAS
    // ========================================================

    const canvas =
        document.getElementById(
            "nrCanvas"
        );


    const ctx =
        canvas.getContext(
            "2d"
        );


    if (
        !canvas ||
        !ctx
    ) {

        return;

    }


    // ========================================================
    // DOM
    // ========================================================

    const pauseButton =
        document.getElementById(
            "nrPauseButton"
        );


    const restartButton =
        document.getElementById(
            "nrRestartButton"
        );


    const topBankButton =
        document.getElementById(
            "nrTopBankButton"
        );


    const energyFill =
        document.getElementById(
            "nrEnergyFill"
        );


    const energyText =
        document.getElementById(
            "nrEnergyText"
        );


    const xpFill =
        document.getElementById(
            "nrXPFill"
        );


    const xpText =
        document.getElementById(
            "nrXPText"
        );


    const comboFill =
        document.getElementById(
            "nrComboFill"
        );


    const comboText =
        document.getElementById(
            "nrComboText"
        );


    const levelElement =
        document.getElementById(
            "nrLevel"
        );


    const healthText =
        document.getElementById(
            "nrHealthText"
        );


    const scoreElement =
        document.getElementById(
            "nrScore"
        );


    const distanceElement =
        document.getElementById(
            "nrDistance"
        );


    const coinsElement =
        document.getElementById(
            "nrCoins"
        );


    const multiplierElement =
        document.getElementById(
            "nrMultiplier"
        );


    const missionElement =
        document.getElementById(
            "nrMission"
        );


    const shieldChip =
        document.getElementById(
            "nrShieldChip"
        );


    const magnetChip =
        document.getElementById(
            "nrMagnetChip"
        );


    const starChip =
        document.getElementById(
            "nrStarChip"
        );


    const earningsElement =
        document.getElementById(
            "nrEarnings"
        );


    const earningsFill =
        document.getElementById(
            "nrEarningsFill"
        );


    const earningCoinText =
        document.getElementById(
            "nrEarningCoinText"
        );


    const boostPanel =
        document.getElementById(
            "nrBoostPanel"
        );


    const boostTime =
        document.getElementById(
            "nrBoostTime"
        );


    const boostFill =
        document.getElementById(
            "nrBoostFill"
        );


    const hitFlash =
        document.getElementById(
            "nrHitFlash"
        );


    const popup =
        document.getElementById(
            "nrPopup"
        );


    const overlay =
        document.getElementById(
            "nrOverlay"
        );


    const overlayIcon =
        document.getElementById(
            "nrOverlayIcon"
        );


    const overlayTitle =
        document.getElementById(
            "nrOverlayTitle"
        );


    const overlayText =
        document.getElementById(
            "nrOverlayText"
        );


    const overlayButton =
        document.getElementById(
            "nrOverlayButton"
        );


    const saveButton =
        document.getElementById(
            "nrSaveButton"
        );


    const depositButton =
        document.getElementById(
            "nrDepositButton"
        );


    const overlayRestart =
        document.getElementById(
            "nrOverlayRestart"
        );


    const overlayStatus =
        document.getElementById(
            "nrOverlayStatus"
        );


    const resultScore =
        document.getElementById(
            "nrResultScore"
        );


    const resultDistance =
        document.getElementById(
            "nrResultDistance"
        );


    const resultCoins =
        document.getElementById(
            "nrResultCoins"
        );


    const resultCredits =
        document.getElementById(
            "nrResultCredits"
        );


    const overlayEarnings =
        document.getElementById(
            "nrOverlayEarnings"
        );


    const heart1 =
        document.getElementById(
            "nrHeart1"
        );


    const heart2 =
        document.getElementById(
            "nrHeart2"
        );


    const heart3 =
        document.getElementById(
            "nrHeart3"
        );


    // ========================================================
    // CANVAS SIZE
    // ========================================================

    let W =
        1200;


    let H =
        675;


    let groundY =
        540;


    // ========================================================
    // GAME STATE
    // ========================================================

    let gameTime =
        0;


    let lastTime =
        0;


    let paused =
        false;


    let gameOver =
        false;


    let score =
        0;


    let coins =
        0;


    let distanceTravelled =
        0;


    let level =
        1;


    let xp =
        0;


    let speed =
        350;


    let baseSpeed =
        350;


    let health =
        3;


    const maxHealth =
        3;


    let energy =
        100;


    const maxEnergy =
        100;


    let combo =
        0;


    let comboTimer =
        0;


    const comboTimeout =
        2.6;


    let multiplier =
        1;


    let boostRemaining =
        0;


    const boostDuration =
        10;


    let magnetRemaining =
        0;


    const magnetDuration =
        10;


    let starRemaining =
        0;


    const starDuration =
        8;


    let shieldActive =
        false;


    let screenShake =
        0;


    let missionTimer =
        3;


    let obstacleTimer =
        .8;


    let coinTimer =
        1.1;


    let powerupTimer =
        6;


    let earningsSaved =
        false;


    /*
     * 1 coin = 1 virtual Game Credit.
     */

    const CREDIT_PER_COIN =
        1;


    /*
     * Total game credits saved
     * between sessions.
     */

    let totalGameCredits =
        Number(
            localStorage.getItem(
                "connecthub_ninja_game_credits"
            ) ||
            0
        );


    // ========================================================
    // PLAYER
    // ========================================================

    const player = {

        x:245,

        y:0,

        width:80,

        height:130,

        vy:0,

        jumpPower:760,

        grounded:true,

        sliding:false,

        attacking:false,

        attackTimer:0,

        attackCooldown:0,

        hurtTimer:0,

        invulnerable:0,

        dead:false,

        runFrame:0,

        runTimer:0,

        stateTimer:0

    };


    // ========================================================
    // RESIZE
    // ========================================================

    function resizeCanvas() {

        const rect =
            canvas.getBoundingClientRect();


        const dpr =
            Math.min(
                window.devicePixelRatio ||
                1,
                2
            );


        W =
            Math.max(
                640,
                Math.floor(
                    rect.width *
                    dpr
                )
            );


        H =
            Math.max(
                420,
                Math.floor(
                    rect.height *
                    dpr
                )
            );


        canvas.width =
            W;


        canvas.height =
            H;


        groundY =
            H *
            .80;


        player.y =
            Math.min(
                player.y,
                groundY -
                player.height
            );

    }


    window.addEventListener(
        "resize",
        resizeCanvas
    );


    resizeCanvas();


    player.y =
        groundY -
        player.height;


    // ========================================================
    // INPUT
    // ========================================================

    const keys = {

        jump:false,

        slide:false,

        attack:false

    };


    const mobile = {

        jump:false,

        slide:false

    };


    document.addEventListener(
        "keydown",
        function (
            event
        ) {

            const key =
                String(
                    event.key
                ).toLowerCase();


            if (
                key === "w" ||
                key === "arrowup" ||
                key === " "
            ) {

                event.preventDefault();

                keys.jump =
                    true;

            }


            if (
                key === "s" ||
                key === "arrowdown"
            ) {

                event.preventDefault();

                keys.slide =
                    true;

            }


            if (
                key === "j"
            ) {

                keys.attack =
                    true;

            }


            if (
                key === "p"
            ) {

                togglePause();

            }

        }
    );


    document.addEventListener(
        "keyup",
        function (
            event
        ) {

            const key =
                String(
                    event.key
                ).toLowerCase();


            if (
                key === "s" ||
                key === "arrowdown"
            ) {

                keys.slide =
                    false;

            }

        }
    );


    // ========================================================
    // IMAGE SYSTEM
    // ========================================================

    const ASSET_ROOT =
        "game-assets/ninja/";


    const assets = {};


    const assetFiles = {

        ninja_idle:
            "player/ninja_idle.png",

        ninja_run1:
            "player/ninja_run1.png",

        ninja_run2:
            "player/ninja_run2.png",

        ninja_run3:
            "player/ninja_run3.png",

        ninja_run4:
            "player/ninja_run4.png",

        ninja_jump:
            "player/ninja_jump.png",

        ninja_double_jump:
            "player/ninja_double_jump.png",

        ninja_slide:
            "player/ninja_slide.png",

        ninja_attack:
            "player/ninja_attack.png",

        ninja_hurt:
            "player/ninja_hurt.png",

        ninja_death:
            "player/ninja_death.png",


        coin:
            "powerups/coin.png",

        star:
            "powerups/star.png",

        boost:
            "powerups/boost.png",

        shield:
            "powerups/shield.png",

        magnet:
            "powerups/magnet.png",


        spike_trap:
            "obstacles/spike_trap.png",

        wooden_crate:
            "obstacles/wooden_crate.png",

        rock:
            "obstacles/rock.png",

        barrier:
            "obstacles/barrier.png",

        cannon:
            "obstacles/cannon.png",

        hanging_spike:
            "obstacles/hanging_spike.png",

        falling_log:
            "obstacles/falling_log.png",

        fire_pit:
            "obstacles/fire_pit.png",

        tall_wall:
            "obstacles/tall_wall.png",

        ninja_statue:
            "obstacles/ninja_statue.png",


        dash_trail:
            "effects/dash_trail.png",

        slash_effect:
            "effects/slash_effect.png",

        spin_attack:
            "effects/spin_attack.png",

        land_impact:
            "effects/land_impact.png",

        jump_dust:
            "effects/jump_dust.png",

        coin_spark:
            "effects/coin_spark.png",

        star_burst:
            "effects/star_burst.png",

        boost_glow:
            "effects/boost_glow.png",

        shield_flash:
            "effects/shield_flash.png",

        magnet_pulse:
            "effects/magnet_pulse.png",

        hurt_flash:
            "effects/hurt_flash.png",

        death_smoke:
            "effects/death_smoke.png",


        day_village:
            "backgrounds/day_village.jpg",

        night_rooftops:
            "backgrounds/night_rooftops.jpg",

        sunset_mountains:
            "backgrounds/sunset_mountains.jpg"

    };


    Object.keys(
        assetFiles
    ).forEach(
        function (
            key
        ) {

            const image =
                new Image();


            image.onload =
                function () {

                    assets[key] =
                        image;

                    console.log(
                        "Ninja asset loaded:",
                        key
                    );

                };


            image.onerror =
                function () {

                    console.warn(
                        "Ninja asset not found:",
                        ASSET_ROOT +
                        assetFiles[key]
                    );

                };


            image.src =
                ASSET_ROOT +
                assetFiles[key];

        }
    );


    function imageReady(
        image
    ) {

        return (
            image &&
            image.complete &&
            image.naturalWidth >
            0
        );

    }


    // ========================================================
    // PARTICLES
    // ========================================================

    const particles = [];


    function spawnParticles(
        x,
        y,
        type,
        amount
    ) {

        for (
            let i = 0;
            i < amount;
            i++
        ) {

            particles.push({

                x:x,

                y:y,

                vx:
                    random(
                        -170,
                        170
                    ),

                vy:
                    random(
                        -230,
                        30
                    ),

                gravity:
                    type === "dust"
                        ? 95
                        : 220,

                life:
                    random(
                        .25,
                        .75
                    ),

                maxLife:
                    .75,

                size:
                    random(
                        2,
                        7
                    ),

                type:type

            });

        }

    }


    function updateParticles(
        dt
    ) {

        for (
            let i =
                particles.length -
                1;
            i >= 0;
            i--
        ) {

            const particle =
                particles[i];


            particle.x +=
                particle.vx *
                dt;


            particle.y +=
                particle.vy *
                dt;


            particle.vy +=
                particle.gravity *
                dt;


            particle.life -=
                dt;


            if (
                particle.life <=
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
            const particle
            of particles
        ) {

            ctx.save();


            ctx.globalAlpha =
                Math.max(
                    0,
                    particle.life /
                    particle.maxLife
                );


            let color =
                "#94a3b8";


            if (
                particle.type ===
                "spark"
            ) {

                color =
                    "#bfdbfe";

            }


            if (
                particle.type ===
                "boost"
            ) {

                color =
                    "#67e8f9";

            }


            if (
                particle.type ===
                "coin"
            ) {

                color =
                    "#facc15";

            }


            if (
                particle.type ===
                "hit"
            ) {

                color =
                    "#f87171";

            }


            ctx.fillStyle =
                color;


            ctx.shadowBlur =
                14;


            ctx.shadowColor =
                color;


            ctx.beginPath();


            ctx.arc(
                particle.x,
                particle.y,
                particle.size,
                0,
                Math.PI *
                2
            );


            ctx.fill();


            ctx.restore();

        }

    }


    // ========================================================
    // EFFECTS
    // ========================================================

    const effects = [];


    function createEffect(
        type,
        x,
        y,
        size,
        duration
    ) {

        effects.push({

            type:type,

            x:x,

            y:y,

            size:size,

            duration:duration,

            life:duration

        });

    }


    function updateEffects(
        dt
    ) {

        for (
            let i =
                effects.length -
                1;
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

            const image =
                assets[
                    effect.type
                ];


            if (
                !imageReady(
                    image
                )
            ) {

                continue;

            }


            const ratio =
                image.naturalWidth /
                image.naturalHeight;


            const height =
                effect.size;


            const width =
                height *
                ratio;


            ctx.save();


            ctx.globalAlpha =
                Math.max(
                    0,
                    effect.life /
                    effect.duration
                );


            ctx.drawImage(

                image,

                effect.x -
                width /
                2,

                effect.y -
                height /
                2,

                width,

                height

            );


            ctx.restore();

        }

    }


    // ========================================================
    // OBSTACLES
    // ========================================================

    const obstacles = [];


    const obstacleDefinitions = {

        spike_trap:
            {
                width:68,
                height:46
            },

        wooden_crate:
            {
                width:64,
                height:64
            },

        rock:
            {
                width:70,
                height:54
            },

        barrier:
            {
                width:78,
                height:62
            },

        cannon:
            {
                width:84,
                height:68
            },

        hanging_spike:
            {
                width:68,
                height:120
            },

        falling_log:
            {
                width:98,
                height:66
            },

        fire_pit:
            {
                width:76,
                height:60
            },

        tall_wall:
            {
                width:82,
                height:125
            },

        ninja_statue:
            {
                width:70,
                height:112
            }

    };


    function chooseObstacleType() {

        const easyTypes = [

            "rock",

            "wooden_crate",

            "spike_trap"

        ];


        const advancedTypes = [

            "rock",

            "wooden_crate",

            "spike_trap",

            "barrier",

            "cannon",

            "hanging_spike",

            "falling_log",

            "fire_pit",

            "tall_wall",

            "ninja_statue"

        ];


        const types =
            level <= 2
                ? easyTypes
                : advancedTypes;


        return types[
            Math.floor(
                Math.random() *
                types.length
            )
        ];

    }


    function spawnObstacle() {

        const type =
            chooseObstacleType();


        const size =
            obstacleDefinitions[
                type
            ];


        obstacles.push({

            x:
                W +
                random(
                    80,
                    230
                ),

            y:
                groundY,

            width:
                size.width,

            height:
                size.height,

            type:type,

            destroyed:false,

            passed:false

        });

    }


    function updateObstacles(
        dt
    ) {

        for (
            let i =
                obstacles.length -
                1;
            i >= 0;
            i--
        ) {

            const obstacle =
                obstacles[i];


            obstacle.x -=
                speed *
                dt;


            if (
                obstacle.destroyed
            ) {

                continue;

            }


            if (
                !obstacle.passed &&
                obstacle.x <
                player.x -
                85
            ) {

                obstacle.passed =
                    true;


                score +=
                    20 *
                    multiplier;


                addCombo();

            }


            const playerHeight =
                player.sliding
                    ? player.height *
                      .52
                    : player.height;


            const playerTop =
                player.sliding
                    ? player.y +
                      player.height *
                      .46
                    : player.y;


            const playerBottom =
                player.y +
                playerHeight;


            const obstacleTop =
                obstacle.y -
                obstacle.height;


            const horizontal =
                Math.abs(
                    obstacle.x -
                    player.x
                ) <
                (
                    obstacle.width /
                    2 +
                    player.width /
                    2 -
                    16
                );


            const vertical =
                playerBottom >
                obstacleTop +
                8 &&
                playerTop <
                obstacle.y;


            if (
                horizontal &&
                vertical
            ) {

                if (
                    player.attacking ||
                    boostRemaining >
                    0
                ) {

                    obstacle.destroyed =
                        true;


                    score +=
                        100 *
                        multiplier;


                    xp +=
                        40;


                    spawnParticles(
                        obstacle.x,
                        obstacle.y -
                        obstacle.height /
                        2,
                        "spark",
                        18
                    );


                    createEffect(
                        "slash_effect",
                        obstacle.x,
                        obstacle.y -
                        obstacle.height /
                        2,
                        120,
                        .28
                    );


                    screenShake =
                        .16;


                    addCombo();

                } else {

                    hitPlayer(
                        obstacle.type
                    );

                }

            }


            if (
                obstacle.x <
                -220
            ) {

                obstacles.splice(
                    i,
                    1
                );

            }

        }

    }


    function drawObstacles() {

        for (
            const obstacle
            of obstacles
        ) {

            if (
                obstacle.destroyed
            ) {

                continue;

            }


            const image =
                assets[
                    obstacle.type
                ];


            if (
                imageReady(
                    image
                )
            ) {

                const ratio =
                    image.naturalWidth /
                    image.naturalHeight;


                const drawHeight =
                    obstacle.height;


                const drawWidth =
                    drawHeight *
                    ratio;


                ctx.drawImage(

                    image,

                    obstacle.x -
                    drawWidth /
                    2,

                    obstacle.y -
                    drawHeight,

                    drawWidth,

                    drawHeight

                );

            } else {

                /*
                 * Visual fallback.
                 */

                ctx.save();


                ctx.fillStyle =
                    "#475569";


                ctx.strokeStyle =
                    "#94a3b8";


                ctx.lineWidth =
                    2;


                ctx.fillRect(

                    obstacle.x -
                    obstacle.width /
                    2,

                    obstacle.y -
                    obstacle.height,

                    obstacle.width,

                    obstacle.height

                );


                ctx.strokeRect(

                    obstacle.x -
                    obstacle.width /
                    2,

                    obstacle.y -
                    obstacle.height,

                    obstacle.width,

                    obstacle.height

                );


                ctx.restore();

            }

        }

    }


    // ========================================================
    // COINS
    // ========================================================

    const collectibleCoins = [];


    function spawnCoinLine() {

        const count =
            Math.floor(
                random(
                    4,
                    8
                )
            );


        const baseY =
            groundY -
            random(
                95,
                195
            );


        for (
            let i =
                0;
            i <
                count;
            i++
        ) {

            collectibleCoins.push({

                x:
                    W +
                    80 +
                    i *
                    55,

                y:
                    baseY +
                    Math.sin(
                        i *
                        .85
                    ) *
                    18,

                rotation:
                    random(
                        0,
                        Math.PI *
                        2
                    ),

                collected:false

            });

        }

    }


    function collectCoin(
        coin
    ) {

        if (
            coin.collected
        ) {

            return;

        }


        coin.collected =
            true;


        coins +=
            1;


        const coinMultiplier =
            starRemaining >
            0
                ? 2
                : 1;


        score +=
            50 *
            multiplier *
            coinMultiplier;


        xp +=
            20 *
            coinMultiplier;


        energy =
            Math.min(
                maxEnergy,
                energy +
                4
            );


        addCombo();


        spawnParticles(
            coin.x,
            coin.y,
            "coin",
            8
        );


        createEffect(
            "coin_spark",
            coin.x,
            coin.y,
            75,
            .30
        );


        showCoinPopup(
            coin.x,
            coin.y,
            coinMultiplier
        );

    }


    function updateCoins(
        dt
    ) {

        for (
            let i =
                collectibleCoins.length -
                1;
            i >= 0;
            i--
        ) {

            const coin =
                collectibleCoins[i];


            coin.x -=
                speed *
                dt;


            coin.rotation +=
                dt *
                7;


            /*
             * Magnet.
             */

            if (
                magnetRemaining >
                0
            ) {

                const dx =
                    player.x -
                    coin.x;


                const dy =
                    (
                        player.y +
                        55
                    ) -
                    coin.y;


                const distance =
                    Math.sqrt(
                        dx * dx +
                        dy * dy
                    );


                if (
                    distance <
                    270
                ) {

                    coin.x +=
                        dx *
                        Math.min(
                            .11,
                            dt *
                            5
                        );


                    coin.y +=
                        dy *
                        Math.min(
                            .11,
                            dt *
                            5
                        );

                }

            }


            if (
                !coin.collected &&
                Math.abs(
                    coin.x -
                    player.x
                ) <
                50 &&
                Math.abs(
                    coin.y -
                    (
                        player.y +
                        55
                    )
                ) <
                90
            ) {

                collectCoin(
                    coin
                );

            }


            if (
                coin.collected ||
                coin.x <
                -80
            ) {

                collectibleCoins.splice(
                    i,
                    1
                );

            }

        }

    }


    function drawCoins() {

        for (
            const coin
            of collectibleCoins
        ) {

            const image =
                assets.coin;


            ctx.save();


            ctx.translate(
                coin.x,
                coin.y
            );


            const spinScale =
                .25 +
                Math.abs(
                    Math.cos(
                        coin.rotation
                    )
                ) *
                .75;


            if (
                imageReady(
                    image
                )
            ) {

                ctx.scale(
                    spinScale,
                    1
                );


                ctx.shadowBlur =
                    10;


                ctx.shadowColor =
                    "#facc15";


                ctx.drawImage(
                    image,
                    -24,
                    -24,
                    48,
                    48
                );

            } else {

                ctx.scale(
                    spinScale,
                    1
                );


                ctx.fillStyle =
                    "#facc15";


                ctx.beginPath();


                ctx.arc(
                    0,
                    0,
                    16,
                    0,
                    Math.PI *
                    2
                );


                ctx.fill();

            }


            ctx.restore();

        }

    }


    // ========================================================
    // POWER UPS
    // ========================================================

    const powerups = [];


    function choosePowerup() {

        const value =
            Math.random();


        if (
            value <
            .25
        ) {

            return "boost";

        }


        if (
            value <
            .50
        ) {

            return "shield";

        }


        if (
            value <
            .72
        ) {

            return "magnet";

        }


        return "star";

    }


    function spawnPowerup() {

        powerups.push({

            x:
                W +
                random(
                    180,
                    420
                ),

            y:
                groundY -
                random(
                    130,
                    210
                ),

            type:
                choosePowerup(),

            rotation:
                0

        });

    }


    function updatePowerups(
        dt
    ) {

        powerupTimer -=
            dt;


        if (
            powerupTimer <=
            0
        ) {

            spawnPowerup();


            powerupTimer =
                random(
                    7,
                    12
                );

        }


        /*
         * Timers.
         */

        if (
            boostRemaining >
            0
        ) {

            boostRemaining -=
                dt;

        }


        if (
            magnetRemaining >
            0
        ) {

            magnetRemaining -=
                dt;

        }


        if (
            starRemaining >
            0
        ) {

            starRemaining -=
                dt;

        }


        for (
            let i =
                powerups.length -
                1;
            i >= 0;
            i--
        ) {

            const power =
                powerups[i];


            power.x -=
                speed *
                dt;


            power.rotation +=
                dt *
                5;


            const collected =
                Math.abs(
                    power.x -
                    player.x
                ) <
                55 &&
                Math.abs(
                    power.y -
                    (
                        player.y +
                        55
                    )
                ) <
                90;


            if (
                collected
            ) {

                activatePowerup(
                    power.type
                );


                powerups.splice(
                    i,
                    1
                );


                continue;

            }


            if (
                power.x <
                -80
            ) {

                powerups.splice(
                    i,
                    1
                );

            }

        }

    }


    function activatePowerup(
        type
    ) {

        score +=
            150 *
            multiplier;


        xp +=
            50;


        if (
            type ===
            "boost"
        ) {

            boostRemaining =
                boostDuration;


            createEffect(
                "boost_glow",
                player.x,
                player.y +
                58,
                135,
                .70
            );


            spawnParticles(
                player.x,
                player.y +
                58,
                "boost",
                25
            );

        }


        if (
            type ===
            "shield"
        ) {

            shieldActive =
                true;


            createEffect(
                "shield_flash",
                player.x,
                player.y +
                55,
                135,
                .75
            );


            spawnParticles(
                player.x,
                player.y +
                55,
                "boost",
                18
            );

        }


        if (
            type ===
            "magnet"
        ) {

            magnetRemaining =
                magnetDuration;


            createEffect(
                "magnet_pulse",
                player.x,
                player.y +
                55,
                135,
                .75
            );

        }


        if (
            type ===
            "star"
        ) {

            starRemaining =
                starDuration;


            createEffect(
                "star_burst",
                player.x,
                player.y +
                55,
                120,
                .65
            );

        }


        missionTimer =
            2.1;

    }


    function drawPowerups() {

        for (
            const power
            of powerups
        ) {

            const image =
                assets[
                    power.type
                ];


            const floating =
                Math.sin(
                    gameTime *
                    5
                ) *
                5;


            ctx.save();


            ctx.translate(
                power.x,
                power.y +
                floating
            );


            if (
                imageReady(
                    image
                )
            ) {

                ctx.rotate(
                    Math.sin(
                        power.rotation
                    ) *
                    .08
                );


                ctx.shadowBlur =
                    18;


                if (
                    power.type ===
                    "boost"
                ) {

                    ctx.shadowColor =
                        "#22d3ee";

                }


                if (
                    power.type ===
                    "shield"
                ) {

                    ctx.shadowColor =
                        "#60a5fa";

                }


                if (
                    power.type ===
                    "magnet"
                ) {

                    ctx.shadowColor =
                        "#ef4444";

                }


                if (
                    power.type ===
                    "star"
                ) {

                    ctx.shadowColor =
                        "#facc15";

                }


                ctx.drawImage(
                    image,
                    -31,
                    -31,
                    62,
                    62
                );

            }


            ctx.restore();

        }

    }


    // ========================================================
    // JUMP
    // ========================================================

    function jump() {

        if (
            paused ||
            gameOver ||
            player.dead
        ) {

            return;

        }


        if (
            player.grounded
        ) {

            player.vy =
                -player.jumpPower;


            player.grounded =
                false;


            player.stateTimer =
                0;


            createEffect(
                "jump_dust",
                player.x,
                groundY,
                105,
                .34
            );


            spawnParticles(
                player.x,
                groundY,
                "dust",
                10
            );

        }

    }


    // ========================================================
    // SLIDE
    // ========================================================

    function updateSlide() {

        if (
            !player.grounded
        ) {

            player.sliding =
                false;


            return;

        }


        player.sliding =
            keys.slide ||
            mobile.slide;

    }


    // ========================================================
    // ATTACK
    // ========================================================

    function attack() {

        if (
            paused ||
            gameOver ||
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


        player.attacking =
            true;


        player.attackTimer =
            .34;


        player.attackCooldown =
            .20;


        player.stateTimer =
            0;


        createEffect(
            "slash_effect",
            player.x +
            58,
            player.y +
            54,
            115,
            .26
        );


        spawnParticles(
            player.x +
            55,
            player.y +
            52,
            "spark",
            10
        );

    }


    // ========================================================
    // DAMAGE
    // ========================================================

    function hitPlayer(
        obstacleType
    ) {

        if (
            player.dead
        ) {

            return;

        }


        if (
            player.invulnerable >
            0
        ) {

            return;

        }


        /*
         * Shield blocks one hit.
         */

        if (
            shieldActive
        ) {

            shieldActive =
                false;


            player.invulnerable =
                1;


            createEffect(
                "shield_flash",
                player.x,
                player.y +
                55,
                140,
                .60
            );


            spawnParticles(
                player.x,
                player.y +
                55,
                "boost",
                20
            );


            screenShake =
                .14;


            combo =
                0;


            multiplier =
                1;


            missionElement.textContent =
                "🛡 SHIELD BLOCKED THE HIT";


            missionTimer =
                2;


            return;

        }


        /*
         * ONE HEART LOST
         */

        health =
            Math.max(
                0,
                health -
                1
            );


        player.invulnerable =
            1.15;


        player.hurtTimer =
            .48;


        energy =
            Math.max(
                0,
                energy -
                30
            );


        combo =
            0;


        multiplier =
            1;


        screenShake =
            .28;


        /*
         * Heart animation.
         */

        animateHeartLoss(
            health
        );


        /*
         * Hit effects.
         */

        createEffect(
            "hurt_flash",
            player.x,
            player.y +
            55,
            125,
            .35
        );


        spawnParticles(
            player.x,
            player.y +
            55,
            "hit",
            18
        );


        hitFlash.classList.remove(
            "show"
        );


        void hitFlash.offsetWidth;


        hitFlash.classList.add(
            "show"
        );


        /*
         * Damage message.
         */

        if (
            obstacleType ===
            "rock"
        ) {

            missionElement.textContent =
                "💥 ROCK HIT • ONE HEART LOST";

        } else if (
            obstacleType ===
            "spike_trap"
        ) {

            missionElement.textContent =
                "⚠ SPIKE HIT • ONE HEART LOST";

        } else {

            missionElement.textContent =
                "⚠ OBSTACLE HIT • ONE HEART LOST";

        }


        missionTimer =
            2;


        /*
         * Game over.
         */

        if (
            health <=
            0
        ) {

            health =
                0;


            player.dead =
                true;


            gameOver =
                true;


            player.stateTimer =
                0;


            createEffect(
                "death_smoke",
                player.x,
                player.y +
                70,
                150,
                1.0
            );


            showGameOver();

        }

    }


    function animateHeartLoss(
        remainingHealth
    ) {

        const hearts = [

            heart1,
            heart2,
            heart3

        ];


        hearts.forEach(
            function (
                heart,
                index
            ) {

                if (
                    index + 1 >
                    remainingHealth
                ) {

                    heart.classList.add(
                        "empty"
                    );

                    heart.classList.remove(
                        "break"
                    );


                    void heart.offsetWidth;


                    heart.classList.add(
                        "break"
                    );

                } else {

                    heart.classList.remove(
                        "empty"
                    );

                }

            }
        );

    }


    // ========================================================
    // PLAYER UPDATE
    // ========================================================

    function updatePlayer(
        dt
    ) {

        if (
            player.dead
        ) {

            player.stateTimer +=
                dt;


            return;

        }


        player.stateTimer +=
            dt;


        player.attackCooldown =
            Math.max(
                0,
                player.attackCooldown -
                dt
            );


        player.attackTimer =
            Math.max(
                0,
                player.attackTimer -
                dt
            );


        player.hurtTimer =
            Math.max(
                0,
                player.hurtTimer -
                dt
            );


        player.invulnerable =
            Math.max(
                0,
                player.invulnerable -
                dt
            );


        if (
            keys.jump ||
            mobile.jump
        ) {

            jump();


            keys.jump =
                false;


            mobile.jump =
                false;

        }


        if (
            keys.attack
        ) {

            attack();


            keys.attack =
                false;

        }


        updateSlide();


        const oldGrounded =
            player.grounded;


        player.vy +=
            1800 *
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


            if (
                !oldGrounded
            ) {

                createEffect(
                    "land_impact",
                    player.x,
                    groundY,
                    105,
                    .30
                );


                spawnParticles(
                    player.x,
                    groundY,
                    "dust",
                    9
                );

            }

        } else {

            player.grounded =
                false;

        }


        player.attacking =
            player.attackTimer >
            0;


        player.runTimer +=
            dt;


        if (
            player.runTimer >=
            .09
        ) {

            player.runTimer =
                0;


            player.runFrame =
                (
                    player.runFrame +
                    1
                ) % 4;

        }

    }


    // ========================================================
    // COMBO
    // ========================================================

    function addCombo() {

        combo =
            Math.min(
                20,
                combo +
                1
            );


        comboTimer =
            comboTimeout;


        multiplier =
            Math.min(
                5,
                1 +
                Math.floor(
                    combo /
                    5
                )
            );

    }


    function updateCombo(
        dt
    ) {

        if (
            combo <=
            0
        ) {

            combo =
                0;


            multiplier =
                1;


            return;

        }


        comboTimer -=
            dt;


        if (
            comboTimer <=
            0
        ) {

            combo =
                0;


            multiplier =
                1;

        }

    }


    // ========================================================
    // XP / LEVEL
    // ========================================================

    function xpNeeded() {

        return (
            1000 +
            (
                level -
                1
            ) *
            400
        );

    }


    function updateLevel() {

        const required =
            xpNeeded();


        if (
            xp >=
            required
        ) {

            xp -=
                required;


            level +=
                1;


            score +=
                500;


            health =
                maxHealth;


            energy =
                maxEnergy;


            missionTimer =
                2.8;


            missionElement.textContent =
                "🎉 LEVEL " +
                level +
                " UNLOCKED • FULL HEALTH RESTORED";


            createEffect(
                "star_burst",
                W / 2,
                H / 2,
                180,
                .80
            );


            spawnParticles(
                W / 2,
                H / 2,
                "boost",
                35
            );

        }

    }


    // ========================================================
    // GAME UPDATE
    // ========================================================

    function update(
        dt
    ) {

        if (
            paused ||
            gameOver
        ) {

            return;

        }


        gameTime +=
            dt;


        missionTimer =
            Math.max(
                0,
                missionTimer -
                dt
            );


        screenShake =
            Math.max(
                0,
                screenShake -
                dt
            );


        comboTimer =
            Math.max(
                0,
                comboTimer
            );


        const normalSpeed =
            350 +
            Math.min(
                level *
                28,
                300
            );


        baseSpeed =
            normalSpeed;


        if (
            boostRemaining >
            0
        ) {

            speed =
                normalSpeed *
                1.58;

        } else {

            speed =
                normalSpeed;

        }


        updatePlayer(
            dt
        );


        updateSpawns(
            dt
        );


        updateObstacles(
            dt
        );


        updateCoins(
            dt
        );


        updatePowerups(
            dt
        );


        updateCombo(
            dt
        );


        updateLevel();


        updateParticles(
            dt
        );


        updateEffects(
            dt
        );


        /*
         * Slowly restore energy.
         */

        energy =
            Math.min(
                maxEnergy,
                energy +
                4 *
                dt
            );


        /*
         * Distance.
         */

        distanceTravelled +=
            speed *
            dt *
            .10;


        /*
         * Score.
         */

        score +=
            speed *
            dt *
            .035 *
            multiplier;


        updateUI();

    }


    function updateSpawns(
        dt
    ) {

        obstacleTimer -=
            dt;


        coinTimer -=
            dt;


        if (
            obstacleTimer <=
            0
        ) {

            spawnObstacle();


            obstacleTimer =
                Math.max(
                    .56,
                    random(
                        .75,
                        1.38
                    ) -
                    Math.min(
                        level *
                        .018,
                        .20
                    )
                );

        }


        if (
            coinTimer <=
            0
        ) {

            spawnCoinLine();


            coinTimer =
                random(
                    1.5,
                    2.7
                );

        }

    }


    // ========================================================
    // UI
    // ========================================================

    function updateUI() {

        /*
         * Health.
         */

        healthText.textContent =
            health +
            " / " +
            maxHealth +
            " HEARTS";


        updateHeartUI(
            heart1,
            health >=
            1
        );


        updateHeartUI(
            heart2,
            health >=
            2
        );


        updateHeartUI(
            heart3,
            health >=
            3
        );


        /*
         * Energy.
         */

        const energyPercent =
            clamp(
                energy /
                maxEnergy *
                100,
                0,
                100
            );


        energyFill.style.width =
            energyPercent +
            "%";


        energyText.textContent =
            Math.floor(
                energyPercent
            ) +
            "%";


        /*
         * XP.
         */

        const needed =
            xpNeeded();


        const xpPercent =
            clamp(
                xp /
                needed *
                100,
                0,
                100
            );


        xpFill.style.width =
            xpPercent +
            "%";


        xpText.textContent =
            Math.floor(
                xp
            ) +
            " / " +
            needed;


        /*
         * Combo.
         */

        const comboPercent =
            combo <=
            0
                ? 0
                : clamp(
                    comboTimer /
                    comboTimeout *
                    100,
                    0,
                    100
                );


        comboFill.style.width =
            comboPercent +
            "%";


        comboText.textContent =
            "x" +
            Math.max(
                1,
                combo
            );


        /*
         * Level.
         */

        levelElement.textContent =
            "LEVEL " +
            String(
                level
            ).padStart(
                2,
                "0"
            );


        /*
         * Score.
         */

        scoreElement.textContent =
            String(
                Math.floor(
                    score
                )
            ).padStart(
                6,
                "0"
            );


        /*
         * Distance.
         */

        distanceElement.textContent =
            Math.floor(
                distanceTravelled
            ) +
            " M";


        /*
         * Coins.
         */

        coinsElement.textContent =
            "🪙 " +
            coins;


        /*
         * Multiplier.
         */

        multiplierElement.textContent =
            "×" +
            multiplier;


        /*
         * Game Earnings.
         */

        const runCredits =
            coins *
            CREDIT_PER_COIN;


        earningsElement.textContent =
            formatCredits(
                runCredits
            );


        earningCoinText.textContent =
            coins;


        /*
         * Earnings visual.
         */

        const earningsProgress =
            clamp(
                (
                    coins %
                    100
                ) /
                100 *
                100,
                0,
                100
            );


        earningsFill.style.width =
            earningsProgress +
            "%";


        /*
         * Boost.
         */

        if (
            boostRemaining >
            0
        ) {

            boostPanel.classList.add(
                "show"
            );


            boostTime.textContent =
                Math.ceil(
                    boostRemaining
                ) +
                "s";


            boostFill.style.width =
                clamp(
                    boostRemaining /
                    boostDuration *
                    100,
                    0,
                    100
                ) +
                "%";

        } else {

            boostPanel.classList.remove(
                "show"
            );

        }


        /*
         * Power chips.
         */

        updateChip(
            shieldChip,
            shieldActive,
            "🛡 SHIELD ACTIVE",
            "🛡 SHIELD OFF"
        );


        updateChip(
            magnetChip,
            magnetRemaining >
            0,
            "🧲 MAGNET " +
            Math.ceil(
                magnetRemaining
            ) +
            "s",
            "🧲 MAGNET OFF"
        );


        updateChip(
            starChip,
            starRemaining >
            0,
            "⭐ STAR " +
            Math.ceil(
                starRemaining
            ) +
            "s",
            "⭐ STAR OFF"
        );


        /*
         * Mission.
         */

        if (
            missionTimer <=
            0
        ) {

            if (
                health ===
                1
            ) {

                missionElement.textContent =
                    "❤️ LAST HEART • BE CAREFUL";

            } else if (
                boostRemaining >
                0
            ) {

                missionElement.textContent =
                    "⚡ BOOST ACTIVE • BREAK OBSTACLES";

            } else if (
                magnetRemaining >
                0
            ) {

                missionElement.textContent =
                    "🧲 MAGNET ACTIVE • COLLECT COINS";

            } else if (
                starRemaining >
                0
            ) {

                missionElement.textContent =
                    "⭐ STAR BONUS • DOUBLE COIN VALUE";

            } else if (
                combo >=
                10
            ) {

                missionElement.textContent =
                    "🔥 COMBO x" +
                    combo +
                    " • MULTIPLIER ×" +
                    multiplier;

            } else {

                missionElement.textContent =
                    "🥷 RUN • SURVIVE • COLLECT";

            }

        }

    }


    function updateHeartUI(
        element,
        active
    ) {

        if (
            active
        ) {

            element.classList.remove(
                "empty"
            );

        } else {

            element.classList.add(
                "empty"
            );

        }

    }


    function updateChip(
        element,
        active,
        activeText,
        inactiveText
    ) {

        if (
            active
        ) {

            element.textContent =
                activeText;


            element.classList.add(
                "active"
            );

        } else {

            element.textContent =
                inactiveText;


            element.classList.remove(
                "active"
            );

        }

    }


    function formatCredits(
        value
    ) {

        return (
            Number(
                value
            ).toFixed(
                0
            ) +
            " CREDITS"
        );

    }


    // ========================================================
    // BACKGROUND
    // ========================================================

    function drawBackground() {

        /*
         * Base sky.
         */

        const gradient =
            ctx.createLinearGradient(
                0,
                0,
                0,
                H
            );


        if (
            level <
            4
        ) {

            gradient.addColorStop(
                0,
                "#0c2140"
            );


            gradient.addColorStop(
                .55,
                "#155e75"
            );


            gradient.addColorStop(
                1,
                "#0f172a"
            );

        } else if (
            level <
            8
        ) {

            gradient.addColorStop(
                0,
                "#09072a"
            );


            gradient.addColorStop(
                .55,
                "#312e81"
            );


            gradient.addColorStop(
                1,
                "#111827"
            );

        } else {

            gradient.addColorStop(
                0,
                "#2b0a16"
            );


            gradient.addColorStop(
                .55,
                "#7c2d12"
            );


            gradient.addColorStop(
                1,
                "#111827"
            );

        }


        ctx.fillStyle =
            gradient;


        ctx.fillRect(
            0,
            0,
            W,
            H
        );


        /*
         * Your actual background image.
         */

        let image;


        if (
            level >=
            8
        ) {

            image =
                assets.sunset_mountains;

        } else if (
            level >=
            4
        ) {

            image =
                assets.night_rooftops;

        } else {

            image =
                assets.day_village;

        }


        if (
            imageReady(
                image
            )
        ) {

            const ratio =
                image.naturalWidth /
                image.naturalHeight;


            let drawWidth =
                W;


            let drawHeight =
                W /
                ratio;


            if (
                drawHeight <
                H
            ) {

                drawHeight =
                    H;


                drawWidth =
                    H *
                    ratio;

            }


            const maxOffset =
                Math.max(
                    0,
                    drawWidth -
                    W
                );


            const offset =
                maxOffset >
                0
                    ? (
                        distanceTravelled *
                        .12
                    ) %
                    maxOffset
                    : 0;


            ctx.globalAlpha =
                .94;


            ctx.drawImage(

                image,

                (
                    W -
                    drawWidth
                ) /
                2 -
                offset,

                (
                    H -
                    drawHeight
                ) /
                2,

                drawWidth,

                drawHeight

            );


            ctx.globalAlpha =
                1;

        }


        /*
         * Atmospheric shine particles.
         */

        for (
            let i =
                0;
            i <
                85;
            i++
        ) {

            let x =
                (
                    i *
                    173 -
                    distanceTravelled *
                    .05
                ) %
                (
                    W +
                    50
                );


            if (
                x <
                0
            ) {

                x +=
                    W +
                    50;

            }


            const y =
                (
                    i *
                    67
                ) %
                (
                    H *
                    .48
                );


            const alpha =
                .08 +
                .16 *
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
                "rgba(191,219,254," +
                alpha +
                ")";


            ctx.fillRect(
                x,
                y,
                1.5,
                1.5
            );

        }


        /*
         * Moon or sun glow.
         */

        const cx =
            W *
            .80;


        const cy =
            H *
            .18;


        const glow =
            ctx.createRadialGradient(
                cx,
                cy,
                5,
                cx,
                cy,
                120
            );


        glow.addColorStop(
            0,
            "rgba(219,234,254,.28)"
        );


        glow.addColorStop(
            1,
            "rgba(219,234,254,0)"
        );


        ctx.fillStyle =
            glow;


        ctx.beginPath();


        ctx.arc(
            cx,
            cy,
            120,
            0,
            Math.PI *
            2
        );


        ctx.fill();


        ctx.fillStyle =
            level >=
            8
                ? "rgba(251,191,36,.68)"
                : "rgba(226,232,240,.84)";


        ctx.beginPath();


        ctx.arc(
            cx,
            cy,
            29,
            0,
            Math.PI *
            2
        );


        ctx.fill();


        /*
         * Ground.
         */

        const groundGradient =
            ctx.createLinearGradient(
                0,
                groundY,
                0,
                H
            );


        groundGradient.addColorStop(
            0,
            "rgba(3,12,24,.58)"
        );


        groundGradient.addColorStop(
            1,
            "rgba(2,6,23,.98)"
        );


        ctx.fillStyle =
            groundGradient;


        ctx.fillRect(
            0,
            groundY,
            W,
            H -
            groundY
        );


        /*
         * Ground line.
         */

        ctx.strokeStyle =
            "rgba(96,165,250,.27)";


        ctx.lineWidth =
            2;


        ctx.beginPath();


        ctx.moveTo(
            0,
            groundY
        );


        ctx.lineTo(
            W,
            groundY
        );


        ctx.stroke();


        /*
         * Floor lines.
         */

        for (
            let i =
                0;
            i <
                30;
            i++
        ) {

            let x =
                (
                    i *
                    105 -
                    distanceTravelled
                ) %
                (
                    W +
                    105
                );


            if (
                x <
                0
            ) {

                x +=
                    W +
                    105;

            }


            ctx.strokeStyle =
                "rgba(59,130,246,.10)";


            ctx.lineWidth =
                1;


            ctx.beginPath();


            ctx.moveTo(
                x,
                groundY +
                28
            );


            ctx.lineTo(
                x +
                48,
                groundY +
                28
            );


            ctx.stroke();

        }

    }


    // ========================================================
    // PLAYER DRAW
    // ========================================================

    function getPlayerImage() {

        if (
            player.dead
        ) {

            return assets.ninja_death;

        }


        if (
            player.hurtTimer >
            0
        ) {

            return assets.ninja_hurt;

        }


        if (
            player.attacking
        ) {

            return assets.ninja_attack;

        }


        if (
            player.sliding
        ) {

            return assets.ninja_slide;

        }


        if (
            !player.grounded
        ) {

            return assets.ninja_jump;

        }


        const frames = [

            assets.ninja_run1,

            assets.ninja_run2,

            assets.ninja_run3,

            assets.ninja_run4

        ];


        return (
            frames[
                player.runFrame
            ] ||
            assets.ninja_idle
        );

    }


    function drawPlayer() {

        const x =
            player.x;


        const y =
            player.y;


        /*
         * Shadow.
         */

        ctx.save();


        ctx.globalAlpha =
            .28;


        ctx.fillStyle =
            "#000";


        ctx.beginPath();


        ctx.ellipse(
            x,
            groundY +
            5,
            player.sliding
                ? 44
                : 36,
            8,
            0,
            0,
            Math.PI *
            2
        );


        ctx.fill();


        ctx.restore();


        /*
         * Shield.
         */

        if (
            shieldActive
        ) {

            const radius =
                62 +
                Math.sin(
                    gameTime *
                    8
                ) *
                5;


            ctx.save();


            ctx.strokeStyle =
                "rgba(96,165,250,.60)";


            ctx.lineWidth =
                4;


            ctx.shadowBlur =
                25;


            ctx.shadowColor =
                "#60a5fa";


            ctx.beginPath();


            ctx.arc(
                x,
                y +
                58,
                radius,
                0,
                Math.PI *
                2
            );


            ctx.stroke();


            ctx.restore();

        }


        /*
         * Speed trail.
         */

        if (
            boostRemaining >
            0
        ) {

            for (
                let i =
                    0;
                i <
                    5;
                i++
            ) {

                ctx.save();


                ctx.globalAlpha =
                    .17 -
                    i *
                    .024;


                ctx.fillStyle =
                    "#22d3ee";


                ctx.shadowBlur =
                    17;


                ctx.shadowColor =
                    "#22d3ee";


                ctx.fillRect(
                    x -
                    50 -
                    i *
                    15,
                    y +
                    38 +
                    i *
                    8,
                    55 -
                    i *
                    7,
                    5
                );


                ctx.restore();

            }

        }


        const image =
            getPlayerImage();


        /*
         * Real player sprite.
         */

        if (
            imageReady(
                image
            )
        ) {

            const ratio =
                image.naturalWidth /
                image.naturalHeight;


            let drawHeight =
                player.height;


            if (
                player.sliding
            ) {

                drawHeight *=
                    .67;

            }


            let drawWidth =
                drawHeight *
                ratio;


            let drawY =
                y +
                player.height -
                drawHeight;


            if (
                player.sliding
            ) {

                drawY +=
                    17;

            }


            if (
                player.invulnerable >
                0
            ) {

                ctx.globalAlpha =
                    Math.sin(
                        gameTime *
                        35
                    ) >
                    0
                        ? .40
                        : 1;

            }


            ctx.drawImage(

                image,

                x -
                drawWidth /
                2,

                drawY,

                drawWidth,

                drawHeight

            );


            ctx.globalAlpha =
                1;

        } else {

            drawFallbackNinja(
                x,
                y
            );

        }


        /*
         * Attack slash.
         */

        if (
            player.attacking
        ) {

            const progress =
                1 -
                (
                    player.attackTimer /
                    .34
                );


            ctx.save();


            ctx.globalAlpha =
                Math.sin(
                    progress *
                    Math.PI
                );


            ctx.strokeStyle =
                "#60a5fa";


            ctx.lineWidth =
                9;


            ctx.shadowBlur =
                24;


            ctx.shadowColor =
                "#3b82f6";


            ctx.beginPath();


            ctx.arc(
                x +
                35,
                y +
                53,
                68,
                -.9,
                -.9 +
                progress *
                3
            );


            ctx.stroke();


            ctx.restore();

        }

    }


    // ========================================================
    // FALLBACK PLAYER
    // ========================================================

    function drawFallbackNinja(
        x,
        y
    ) {

        ctx.save();


        ctx.translate(
            x,
            y
        );


        const bob =
            Math.sin(
                player.stateTimer *
                14
            ) *
            3;


        ctx.translate(
            0,
            bob
        );


        /*
         * Cape.
         */

        ctx.fillStyle =
            "#111827";


        ctx.beginPath();


        ctx.moveTo(
            -18,
            31
        );


        ctx.lineTo(
            -52,
            85
        );


        ctx.lineTo(
            -5,
            76
        );


        ctx.closePath();


        ctx.fill();


        /*
         * Body.
         */

        ctx.fillStyle =
            "#1e3a8a";


        roundRect(
            ctx,
            -20,
            31,
            40,
            53,
            8
        );


        ctx.fill();


        /*
         * Head.
         */

        ctx.fillStyle =
            "#020617";


        ctx.beginPath();


        ctx.arc(
            0,
            18,
            19,
            0,
            Math.PI *
            2
        );


        ctx.fill();


        /*
         * Eyes.
         */

        ctx.fillStyle =
            "#60a5fa";


        ctx.shadowBlur =
            12;


        ctx.shadowColor =
            "#60a5fa";


        ctx.fillRect(
            -11,
            16,
            6,
            3
        );


        ctx.fillRect(
            5,
            16,
            6,
            3
        );


        ctx.shadowBlur =
            0;


        /*
         * Legs.
         */

        ctx.strokeStyle =
            "#334155";


        ctx.lineWidth =
            11;


        ctx.lineCap =
            "round";


        const swing =
            Math.sin(
                player.stateTimer *
                14
            ) *
            8;


        ctx.beginPath();


        ctx.moveTo(
            -9,
            82
        );


        ctx.lineTo(
            -14 +
            swing,
            120
        );


        ctx.stroke();


        ctx.beginPath();


        ctx.moveTo(
            9,
            82
        );


        ctx.lineTo(
            14 -
            swing,
            120
        );


        ctx.stroke();


        /*
         * Sword.
         */

        ctx.strokeStyle =
            "#dbeafe";


        ctx.lineWidth =
            6;


        ctx.shadowBlur =
            15;


        ctx.shadowColor =
            "#60a5fa";


        ctx.beginPath();


        ctx.moveTo(
            30,
            55
        );


        ctx.lineTo(
            98,
            40
        );


        ctx.stroke();


        ctx.shadowBlur =
            0;


        ctx.restore();

    }


    // ========================================================
    // BACKGROUND + OBJECT DRAW ORDER
    // ========================================================

    function drawWorld() {

        ctx.clearRect(
            0,
            0,
            W,
            H
        );


        ctx.save();


        if (
            screenShake >
            0
        ) {

            ctx.translate(
                random(
                    -8,
                    8
                ) *
                screenShake,

                random(
                    -8,
                    8
                ) *
                screenShake
            );

        }


        drawBackground();


        drawCoins();


        drawPowerups();


        drawObstacles();


        drawParticles();


        drawPlayer();


        drawEffects();


        /*
         * Extra speed lines.
         */

        if (
            boostRemaining >
            0
        ) {

            for (
                let i =
                    0;
                i <
                    11;
                i++
            ) {

                const x =
                    random(
                        0,
                        W
                    );


                const y =
                    random(
                        80,
                        groundY -
                        50
                    );


                ctx.strokeStyle =
                    "rgba(103,232,249,.17)";


                ctx.lineWidth =
                    2;


                ctx.beginPath();


                ctx.moveTo(
                    x,
                    y
                );


                ctx.lineTo(
                    x -
                    55,
                    y
                );


                ctx.stroke();

            }

        }


        /*
         * Star power visual overlay.
         */

        if (
            starRemaining >
            0
        ) {

            ctx.save();


            ctx.globalAlpha =
                .055 +
                Math.sin(
                    gameTime *
                    8
                ) *
                .015;


            ctx.fillStyle =
                "#facc15";


            ctx.fillRect(
                0,
                0,
                W,
                H
            );


            ctx.restore();

        }


        ctx.restore();

    }


    // ========================================================
    // ROUND RECTANGLE
    // ========================================================

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
            x +
            radius,
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


    // ========================================================
    // RANDOM
    // ========================================================

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


    // ========================================================
    // CLAMP
    // ========================================================

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


    // ========================================================
    // COIN POPUP
    // ========================================================

    function showCoinPopup(
        x,
        y,
        multiplierValue
    ) {

        popup.textContent =
            "+" +
            (
                multiplierValue *
                CREDIT_PER_COIN
            ) +
            " CREDIT";


        popup.style.left =
            (
                x /
                W *
                100
            ) +
            "%";


        popup.style.top =
            (
                y /
                H *
                100
            ) +
            "%";


        popup.classList.remove(
            "show"
        );


        void popup.offsetWidth;


        popup.classList.add(
            "show"
        );

    }


    // ========================================================
    // PAUSE
    // ========================================================

    function togglePause() {

        if (
            gameOver
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
                "GAME PAUSED";


            overlayText.textContent =
                "Your ninja is safe. Resume whenever you are ready.";


            overlayButton.textContent =
                "▶ RESUME";


            saveButton.style.display =
                "none";


            depositButton.style.display =
                "none";


            overlayRestart.style.display =
                "none";


            overlayStatus.textContent =
                "";

        } else {

            overlay.classList.remove(
                "show"
            );

        }

    }


    pauseButton.addEventListener(
        "click",
        togglePause
    );


    // ========================================================
    // GAME OVER
    // ========================================================

    function showGameOver() {

        paused =
            true;


        overlay.classList.add(
            "show"
        );


        overlayIcon.textContent =
            "💀";


        overlayTitle.textContent =
            "RUN OVER";


        overlayText.textContent =
            "You lost all three hearts. Your collected coins generated virtual ConnectHub Game Credits.";


        overlayButton.style.display =
            "none";


        saveButton.style.display =
            "inline-flex";


        depositButton.style.display =
            "inline-flex";


        overlayRestart.style.display =
            "inline-flex";


        resultScore.textContent =
            Math.floor(
                score
            );


        resultDistance.textContent =
            Math.floor(
                distanceTravelled
            ) +
            " M";


        resultCoins.textContent =
            coins;


        const runCredits =
            coins *
            CREDIT_PER_COIN;


        resultCredits.textContent =
            runCredits +
            " C";


        overlayEarnings.textContent =
            formatCredits(
                runCredits
            );


        overlayStatus.textContent =
            "Game Earnings are ready to save.";


        earningsSaved =
            false;

    }


    // ========================================================
    // RESTART
    // ========================================================

    function restartGame() {

        window.location.reload();

    }


    restartButton.addEventListener(
        "click",
        restartGame
    );


    overlayRestart.addEventListener(
        "click",
        restartGame
    );


    // ========================================================
    // TOP BANKING
    // ========================================================

    topBankButton.addEventListener(
        "click",
        function () {

            /*
             * Keep current virtual Game Credits
             * available for your banking page.
             */

            localStorage.setItem(
                "connecthub_pending_game_credits",
                String(
                    coins *
                    CREDIT_PER_COIN
                )
            );


            window.location.href =
                "bank.php";

        }
    );


    // ========================================================
    // SAVE EARNINGS
    // ========================================================

    function saveGameEarnings() {

        const runCredits =
            coins *
            CREDIT_PER_COIN;


        if (
            runCredits <=
            0
        ) {

            overlayStatus.textContent =
                "No Game Credits were earned in this run.";


            return;

        }


        if (
            earningsSaved
        ) {

            overlayStatus.textContent =
                "✅ This run's Game Earnings are already saved.";


            return;

        }


        totalGameCredits +=
            runCredits;


        localStorage.setItem(
            "connecthub_ninja_game_credits",
            String(
                totalGameCredits
            )
        );


        localStorage.setItem(
            "connecthub_pending_game_credits",
            String(
                totalGameCredits
            )
        );


        earningsSaved =
            true;


        overlayStatus.textContent =
            "✅ " +
            runCredits +
            " Game Credits saved. Total wallet: " +
            totalGameCredits +
            " credits.";


        saveButton.textContent =
            "✅ SAVED";

    }


    saveButton.addEventListener(
        "click",
        saveGameEarnings
    );


    // ========================================================
    // DEPOSIT BUTTON
    // ========================================================

    depositButton.addEventListener(
        "click",
        function () {

            const runCredits =
                coins *
                CREDIT_PER_COIN;


            if (
                runCredits <=
                0
            ) {

                overlayStatus.textContent =
                    "No Game Credits available to deposit.";


                return;

            }


            /*
             * This stores the amount so bank.php
             * can read it later.
             *
             * It does NOT directly modify a real
             * financial account.
             */

            localStorage.setItem(
                "connecthub_pending_game_credits",
                String(
                    runCredits
                )
            );


            overlayStatus.textContent =
                "✅ " +
                runCredits +
                " virtual Game Credits prepared for ConnectHub Banking.";


            setTimeout(
                function () {

                    window.location.href =
                        "bank.php";

                },
                500
            );

        }
    );


    // ========================================================
    // RESUME BUTTON
    // ========================================================

    overlayButton.addEventListener(
        "click",
        function () {

            if (
                gameOver
            ) {

                restartGame();

                return;

            }


            togglePause();

        }
    );


    // ========================================================
    // CANVAS ATTACK
    // ========================================================

    canvas.addEventListener(
        "pointerdown",
        function (
            event
        ) {

            event.preventDefault();


            if (
                !paused &&
                !gameOver
            ) {

                attack();

            }

        }
    );


    // ========================================================
    // MOBILE BUTTONS
    // ========================================================

    document
        .querySelectorAll(
            "[data-nr-action]"
        )
        .forEach(
            function (
                button
            ) {

                const action =
                    button.getAttribute(
                        "data-nr-action"
                    );


                button.addEventListener(
                    "pointerdown",
                    function (
                        event
                    ) {

                        event.preventDefault();


                        if (
                            action ===
                            "jump"
                        ) {

                            mobile.jump =
                                true;

                        }


                        if (
                            action ===
                            "slide"
                        ) {

                            mobile.slide =
                                true;

                        }


                        if (
                            action ===
                            "attack"
                        ) {

                            attack();

                        }

                    }
                );


                button.addEventListener(
                    "pointerup",
                    function (
                        event
                    ) {

                        event.preventDefault();


                        if (
                            action ===
                            "slide"
                        ) {

                            mobile.slide =
                                false;

                        }

                    }
                );


                button.addEventListener(
                    "pointercancel",
                    function () {

                        mobile.slide =
                            false;

                    }
                );


                button.addEventListener(
                    "pointerleave",
                    function () {

                        mobile.slide =
                            false;

                    }
                );

            }
        );


    // ========================================================
    // INITIAL SPAWN
    // ========================================================

    spawnObstacle();


    spawnCoinLine();


    // ========================================================
    // INITIAL UI
    // ========================================================

    updateUI();


    // ========================================================
    // GAME LOOP
    // ========================================================

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


        drawWorld();


        requestAnimationFrame(
            gameLoop
        );

    }


    requestAnimationFrame(
        gameLoop
    );


    // ========================================================
    // TOUCH SCROLL PREVENTION
    // ========================================================

    document
        .querySelectorAll(
            ".nr-mobile-button"
        )
        .forEach(
            function (
                button
            ) {

                button.addEventListener(
                    "touchmove",
                    function (
                        event
                    ) {

                        event.preventDefault();

                    },
                    {
                        passive:false
                    }
                );

            }
        );


})();

</script>


<?php

require "footer.php";

?>