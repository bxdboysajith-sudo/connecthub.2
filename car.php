<?php

/* =========================================================
   CONNECTHUB CAR GAME
   FULL ADVANCED VERSION
========================================================= */

/*
 * IMPORTANT:
 * Handle AJAX/game saving BEFORE header.php.
 * Otherwise header HTML can break JSON response.
 */

require "config.php";

$uid = (int)($_SESSION["user_id"] ?? 0);

if ($uid <= 0) {
    header("Location: login.php");
    exit;
}


/* =========================================================
   SAVE GAME EARNINGS
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["save_game"])
) {

    header("Content-Type: application/json; charset=utf-8");

    $score = (int)($_POST["score"] ?? 0);

    if ($score < 0) {

        echo json_encode([
            "success" => false,
            "message" => "Invalid score"
        ]);

        exit;
    }


    /*
     * Every 10 points = ₹1
     */

    $earning = floor($score / 10);


    if ($earning <= 0) {

        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(amount),0) AS total
            FROM game_earnings
            WHERE user_id = ?
            AND status = 'available'
        ");

        $stmt->bind_param("i", $uid);

        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        $available = (float)($row["total"] ?? 0);

        $stmt->close();


        echo json_encode([
            "success" => true,
            "earning" => 0,
            "available" => $available
        ]);

        exit;
    }


    try {

        $stmt = $conn->prepare("
            INSERT INTO game_earnings
            (user_id, amount, status)
            VALUES (?, ?, 'available')
        ");

        if (!$stmt) {
            throw new Exception($conn->error);
        }

        $stmt->bind_param(
            "id",
            $uid,
            $earning
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $stmt->close();


        /* GET AVAILABLE */

        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(amount),0) AS total
            FROM game_earnings
            WHERE user_id = ?
            AND status = 'available'
        ");

        if (!$stmt) {
            throw new Exception($conn->error);
        }

        $stmt->bind_param("i", $uid);

        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        $available =
            (float)($row["total"] ?? 0);

        $stmt->close();


        echo json_encode([
            "success" => true,
            "earning" => $earning,
            "available" => $available
        ]);

    } catch (Throwable $e) {

        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);
    }

    exit;
}


/* =========================================================
   GET AVAILABLE EARNINGS
========================================================= */

$availableEarnings = 0;

$stmt = $conn->prepare("
    SELECT COALESCE(SUM(amount),0) AS total
    FROM game_earnings
    WHERE user_id = ?
    AND status = 'available'
");

$stmt->bind_param("i", $uid);

$stmt->execute();

$row = $stmt->get_result()->fetch_assoc();

$availableEarnings =
    (float)($row["total"] ?? 0);

$stmt->close();


/* =========================================================
   HEADER
========================================================= */

require "header.php";

?>

<style>

/* =========================================================
   MAIN OUTER PAGE
========================================================= */

:root {

    /* CHANGE YOUR JPG NAME HERE */

    --outer-bg: url("uploads/car_background.jpg");

}


/* =========================================================
   PAGE BACKGROUND
========================================================= */

.car-page {

    position: relative;

    width: 100%;

    min-height:
        calc(100vh - 70px);

    padding:
        30px 25px 50px;

    box-sizing: border-box;

    overflow: hidden;

    background:

        linear-gradient(
            rgba(3,7,20,.45),
            rgba(3,7,20,.78)
        ),

        var(--outer-bg);

    background-size: cover;

    background-position: center;

    background-attachment: fixed;

}


/* =========================================================
   DARK CINEMATIC OVERLAY
========================================================= */

.car-page::before {

    content: "";

    position: absolute;

    inset: 0;

    pointer-events: none;

    background:

        radial-gradient(
            circle at 50% 20%,
            rgba(70,110,255,.25),
            transparent 35%
        ),

        radial-gradient(
            circle at 10% 80%,
            rgba(255,0,150,.18),
            transparent 30%
        ),

        radial-gradient(
            circle at 90% 75%,
            rgba(0,220,255,.18),
            transparent 30%
        );

}


/* =========================================================
   MOVING LIGHT EFFECT
========================================================= */

.car-page::after {

    content: "";

    position: absolute;

    inset: -50%;

    pointer-events: none;

    background:

        linear-gradient(
            115deg,
            transparent 45%,
            rgba(255,255,255,.035) 50%,
            transparent 55%
        );

    animation:
        lightSweep 8s linear infinite;

}


@keyframes lightSweep {

    0% {

        transform:
            translateX(-30%)
            translateY(-10%)
            rotate(8deg);

    }

    100% {

        transform:
            translateX(30%)
            translateY(10%)
            rotate(8deg);

    }

}


/* =========================================================
   CONTENT
========================================================= */

.car-content {

    position: relative;

    z-index: 5;

}


/* =========================================================
   TOP HEADER
========================================================= */

.car-header {

    width: 100%;

    max-width: 1250px;

    margin:
        0 auto 25px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

}


/* =========================================================
   TITLE
========================================================= */

.car-title {

    display: flex;

    align-items: center;

    gap: 14px;

    padding:
        10px 18px;

    border-radius: 18px;

    background:
        rgba(5,10,25,.62);

    border:
        1px solid rgba(255,255,255,.14);

    backdrop-filter:
        blur(16px);

    box-shadow:

        0 15px 45px
        rgba(0,0,0,.35),

        inset 0 0 25px
        rgba(100,130,255,.05);

}


.car-title-icon {

    width: 58px;

    height: 58px;

    border-radius: 17px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 29px;

    background:

        linear-gradient(
            135deg,
            #ff5b00,
            #ffb000
        );

    box-shadow:

        0 0 15px
        rgba(255,110,0,.55),

        0 10px 35px
        rgba(255,70,0,.3);

}


.car-title h1 {

    margin: 0;

    font-size: 27px;

    font-weight: 900;

    color: white;

    letter-spacing: -.5px;

    text-shadow:

        0 0 20px
        rgba(100,150,255,.4);

}


.car-title p {

    margin:
        4px 0 0;

    color:
        #b8c7e6;

    font-size: 13px;

}


/* =========================================================
   AVAILABLE MONEY
========================================================= */

.available-box {

    min-width: 170px;

    padding:
        12px 18px;

    border-radius: 16px;

    background:

        linear-gradient(
            135deg,
            rgba(10,20,40,.78),
            rgba(15,30,55,.65)
        );

    border:
        1px solid
        rgba(255,255,255,.16);

    backdrop-filter:
        blur(16px);

    text-align: right;

    box-shadow:

        0 15px 40px
        rgba(0,0,0,.35),

        0 0 25px
        rgba(0,255,160,.06);

}


.available-box span {

    display: block;

    color:
        #9eacc5;

    font-size: 10px;

    font-weight: 800;

    letter-spacing: 1px;

}


.available-box strong {

    display: block;

    margin-top: 3px;

    color:
        #36ff91;

    font-size: 21px;

    text-shadow:

        0 0 12px
        rgba(50,255,140,.45);

}


/* =========================================================
   DECORATIVE GRAPHIC SIDE ELEMENTS
========================================================= */

.graphics {

    position: absolute;

    z-index: 1;

    pointer-events: none;

}


/* LEFT PANEL */

.graphic-left {

    left: 2%;

    top: 30%;

    width: 250px;

    height: 330px;

    border-radius: 25px;

    background:

        linear-gradient(
            145deg,
            rgba(15,25,60,.55),
            rgba(5,10,25,.25)
        );

    border:
        1px solid
        rgba(90,140,255,.2);

    backdrop-filter:
        blur(8px);

    box-shadow:

        0 0 50px
        rgba(50,90,255,.1),

        inset 0 0 40px
        rgba(50,100,255,.05);

    transform:
        perspective(600px)
        rotateY(15deg);

}


/* RIGHT PANEL */

.graphic-right {

    right: 2%;

    top: 30%;

    width: 250px;

    height: 330px;

    border-radius: 25px;

    background:

        linear-gradient(
            215deg,
            rgba(15,25,60,.55),
            rgba(5,10,25,.25)
        );

    border:
        1px solid
        rgba(255,80,180,.18);

    backdrop-filter:
        blur(8px);

    box-shadow:

        0 0 50px
        rgba(255,40,180,.08),

        inset 0 0 40px
        rgba(255,40,180,.04);

    transform:
        perspective(600px)
        rotateY(-15deg);

}


/* =========================================================
   SIDE TEXT
========================================================= */

.side-info {

    padding: 30px;

    color: rgba(255,255,255,.8);

}


.side-info .small {

    font-size: 10px;

    letter-spacing: 2px;

    color: #7f9cff;

}


.side-info h2 {

    margin:
        8px 0;

    font-size: 23px;

    color: white;

}


.side-info p {

    font-size: 12px;

    line-height: 1.7;

    color: #93a4c5;

}


.side-line {

    height: 2px;

    width: 70px;

    margin-top: 18px;

    background:

        linear-gradient(
            90deg,
            #5577ff,
            transparent
        );

}


/* =========================================================
   GAME CONTAINER
========================================================= */

.car-game-container {

    position: relative;

    width: 100%;

    max-width: 1250px;

    margin: 0 auto;

    display: flex;

    justify-content: center;

    align-items: center;

}


/* =========================================================
   GAME OUTER GLOW
========================================================= */

.game-frame {

    position: relative;

    padding: 5px;

    border-radius: 31px;

    background:

        linear-gradient(
            135deg,
            rgba(100,130,255,.7),
            rgba(255,255,255,.05),
            rgba(255,60,170,.45)
        );

    box-shadow:

        0 0 30px
        rgba(70,100,255,.3),

        0 0 80px
        rgba(30,60,255,.14),

        0 30px 100px
        rgba(0,0,0,.55);

}


/* =========================================================
   GAME
========================================================= */

.game {

    position: relative;

    width: 430px;

    height: 780px;

    max-width: 100%;

    max-height:
        calc(100vh - 165px);

    min-height: 600px;

    overflow: hidden;

    border-radius: 27px;

    background:
        #111827;

    border:
        1px solid
        rgba(255,255,255,.2);

    box-shadow:

        inset 0 0 40px
        rgba(0,0,0,.8);

}


/* =========================================================
   ROAD
========================================================= */

.road {

    position: absolute;

    inset: 0;

    overflow: hidden;

    background:

        linear-gradient(
            90deg,

            #08732f 0%,
            #08732f 10%,

            #30343a 10%,
            #30343a 90%,

            #08732f 90%,
            #08732f 100%
        );

}


/* =========================================================
   ROAD LANE LINES
========================================================= */

.road::before {

    content: "";

    position: absolute;

    top: -100%;

    left: 36.66%;

    width: 5px;

    height: 300%;

    background:

        repeating-linear-gradient(
            to bottom,

            transparent 0,
            transparent 50px,

            rgba(255,255,255,.9)
            50px,
            rgba(255,255,255,.9)
            100px
        );

    animation:
        roadMove .45s linear infinite;

}


.road::after {

    content: "";

    position: absolute;

    top: -100%;

    left: 63.33%;

    width: 5px;

    height: 300%;

    background:

        repeating-linear-gradient(
            to bottom,

            transparent 0,
            transparent 50px,

            rgba(255,255,255,.9)
            50px,
            rgba(255,255,255,.9)
            100px
        );

    animation:
        roadMove .45s linear infinite;

}


@keyframes roadMove {

    from {

        transform:
            translateY(0);

    }

    to {

        transform:
            translateY(100px);

    }

}


/* =========================================================
   ROAD EDGES
========================================================= */

.road-edge-left {

    position: absolute;

    left: 10%;

    top: 0;

    width: 6px;

    height: 100%;

    background:

        repeating-linear-gradient(
            to bottom,

            #ffd400 0,
            #ffd400 35px,

            #222 35px,
            #222 70px
        );

}


.road-edge-right {

    position: absolute;

    right: 10%;

    top: 0;

    width: 6px;

    height: 100%;

    background:

        repeating-linear-gradient(
            to bottom,

            #ffd400 0,
            #ffd400 35px,

            #222 35px,
            #222 70px
        );

}


/* =========================================================
   GAME AREA
========================================================= */

#gameArea {

    position: absolute;

    inset: 0;

    z-index: 5;

    overflow: hidden;

}


/* =========================================================
   PLAYER
========================================================= */

#player {

    position: absolute;

    width: 82px;

    height: 155px;

    object-fit: contain;

    z-index: 20;

    pointer-events: none;

    user-select: none;

    filter:

        drop-shadow(
            0 8px 7px
            rgba(0,0,0,.7)
        )

        drop-shadow(
            0 0 10px
            rgba(255,100,30,.2)
        );

}


/* =========================================================
   NPC
========================================================= */

.npc {

    position: absolute;

    width: 78px;

    height: 150px;

    object-fit: contain;

    z-index: 15;

    pointer-events: none;

    user-select: none;

    filter:

        drop-shadow(
            0 8px 6px
            rgba(0,0,0,.6)
        );

}


/* =========================================================
   HUD
========================================================= */

.hud {

    position: absolute;

    top: 15px;

    left: 15px;

    right: 15px;

    z-index: 50;

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 8px;

}


.hud-box {

    padding:
        9px 11px;

    border-radius: 12px;

    background:
        rgba(5,10,20,.84);

    border:
        1px solid
        rgba(255,255,255,.12);

    backdrop-filter:
        blur(10px);

    box-shadow:

        0 5px 20px
        rgba(0,0,0,.25);

}


.hud-title {

    font-size: 10px;

    color:
        #b8c2d1;

    text-transform:
        uppercase;

    font-weight: bold;

}


.hud-value {

    font-size: 19px;

    font-weight: 900;

    margin-top: 2px;

    color: white;

}


.earning-value {

    color:
        #22c55e;

    text-shadow:

        0 0 10px
        rgba(34,197,94,.3);

}


/* =========================================================
   START SCREEN
========================================================= */

.start-screen {

    position: absolute;

    inset: 0;

    z-index: 100;

    background:

        radial-gradient(
            circle at center,
            rgba(40,70,140,.2),
            rgba(2,5,15,.97) 65%
        );

    display: flex;

    align-items: center;

    justify-content: center;

    text-align: center;

    padding: 25px;

}


.start-content {

    animation:
        startFloat 3s ease-in-out infinite;

}


@keyframes startFloat {

    0%,100% {

        transform:
            translateY(0);

    }

    50% {

        transform:
            translateY(-7px);

    }

}


.start-content h1 {

    font-size: 38px;

    margin-bottom: 10px;

    background:

        linear-gradient(
            90deg,
            #ff7a00,
            #ffb000,
            #ffffff
        );

    -webkit-background-clip: text;

    color: transparent;

    text-shadow:
        0 0 30px
        rgba(255,100,0,.25);

}


.start-content p {

    color:
        #cbd5e1;

    line-height: 1.6;

    margin-bottom: 25px;

}


.start-button {

    border: none;

    padding:
        15px 32px;

    border-radius: 14px;

    background:

        linear-gradient(
            135deg,
            #ff6500,
            #ff9d00
        );

    color: white;

    font-size: 17px;

    font-weight: 900;

    cursor: pointer;

    box-shadow:

        0 10px 30px
        rgba(255,100,0,.4);

    transition:
        .2s;

}


.start-button:hover {

    transform:
        scale(1.05);

    box-shadow:

        0 0 35px
        rgba(255,100,0,.55);

}


/* =========================================================
   GAME OVER
========================================================= */

.game-over {

    position: absolute;

    inset: 0;

    z-index: 200;

    background:

        radial-gradient(
            circle at center,
            rgba(80,0,20,.18),
            rgba(3,7,15,.96)
        );

    display: none;

    align-items: center;

    justify-content: center;

    text-align: center;

    padding: 25px;

}


.game-over-content {

    width: 100%;

    max-width: 320px;

}


.game-over h1 {

    font-size: 40px;

    margin-bottom: 15px;

    color:
        #ff4242;

    text-shadow:

        0 0 25px
        rgba(255,0,0,.4);

}


.result-card {

    background:

        rgba(255,255,255,.07);

    border:

        1px solid
        rgba(255,255,255,.1);

    border-radius: 18px;

    padding: 18px;

    margin-bottom: 18px;

    backdrop-filter:
        blur(12px);

}


.result-row {

    display: flex;

    justify-content: space-between;

    padding: 8px 0;

    color:
        #cbd5e1;

}


.result-row strong {

    color: white;

}


.result-earning {

    color:
        #22c55e !important;

    font-size: 22px;

}


.play-again {

    border: none;

    width: 100%;

    padding: 14px;

    border-radius: 13px;

    background:

        linear-gradient(
            135deg,
            #4f46e5,
            #9333ea
        );

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    margin-bottom: 9px;

}


.bank-button {

    display: block;

    width: 100%;

    padding: 13px;

    border-radius: 13px;

    background:

        linear-gradient(
            135deg,
            #16a34a,
            #22c55e
        );

    color: white;

    text-decoration: none;

    font-weight: bold;

}


/* =========================================================
   CONTROLS
========================================================= */

.controls {

    position: absolute;

    bottom: 18px;

    left: 15px;

    right: 15px;

    z-index: 80;

    display: flex;

    justify-content:
        space-between;

    align-items:
        center;

}


.control-button {

    width: 62px;

    height: 62px;

    border-radius: 50%;

    border:
        2px solid
        rgba(255,255,255,.2);

    background:
        rgba(4,8,18,.82);

    color: white;

    font-size: 27px;

    cursor: pointer;

    display: flex;

    align-items: center;

    justify-content: center;

    user-select: none;

    box-shadow:

        0 10px 25px
        rgba(0,0,0,.4);

}


.control-button:active {

    transform:
        scale(.9);

    background:
        #ff6500;

}


.nitro {

    width: 68px;

    height: 68px;

    background:

        linear-gradient(
            135deg,
            #2563eb,
            #06b6d4
        );

    font-size: 23px;

    box-shadow:

        0 0 25px
        rgba(0,180,255,.3);

}


/* =========================================================
   NITRO
========================================================= */

.nitro-bar {

    position: absolute;

    bottom: 95px;

    left: 50%;

    transform:
        translateX(-50%);

    width: 125px;

    height: 8px;

    border-radius: 20px;

    background:
        rgba(0,0,0,.5);

    overflow: hidden;

    z-index: 90;

}


.nitro-fill {

    width: 100%;

    height: 100%;

    background:

        linear-gradient(
            90deg,
            #06b6d4,
            #2563eb
        );

    box-shadow:

        0 0 10px
        rgba(0,190,255,.6);

}


/* =========================================================
   FLOATING PARTICLES
========================================================= */

.particle {

    position: absolute;

    width: 3px;

    height: 3px;

    border-radius: 50%;

    background:
        white;

    opacity: .6;

    box-shadow:

        0 0 8px
        rgba(120,180,255,.8);

    animation:
        particleFloat
        linear infinite;

}


@keyframes particleFloat {

    from {

        transform:
            translateY(110vh);

    }

    to {

        transform:
            translateY(-10vh);

    }

}


/* =========================================================
   BOTTOM GRAPHIC
========================================================= */

.bottom-tech {

    position: relative;

    z-index: 3;

    max-width: 850px;

    margin:
        28px auto 0;

    display: flex;

    justify-content:
        center;

    gap: 12px;

    flex-wrap: wrap;

}


.tech-box {

    min-width: 130px;

    padding:
        12px 18px;

    border-radius: 13px;

    background:
        rgba(5,10,25,.6);

    border:
        1px solid
        rgba(255,255,255,.1);

    backdrop-filter:
        blur(12px);

    text-align: center;

    color:
        #aebbd5;

    font-size: 11px;

}


.tech-box strong {

    display: block;

    color: white;

    font-size: 15px;

    margin-bottom: 3px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:1000px) {

    .graphics {

        display: none;

    }

}


@media(max-width:700px) {

    .car-page {

        padding:
            12px 8px 35px;

    }


    .car-header {

        align-items:
            flex-start;

        gap: 8px;

    }


    .car-title {

        padding:
            8px 10px;

    }


    .car-title-icon {

        width: 45px;

        height: 45px;

        font-size: 22px;

    }


    .car-title h1 {

        font-size: 19px;

    }


    .car-title p {

        font-size: 10px;

    }


    .available-box {

        min-width:
            125px;

        padding:
            8px 10px;

    }


    .available-box strong {

        font-size: 16px;

    }


    .game-frame {

        width: 100%;

        box-sizing:
            border-box;

    }


    .game {

        width: 100%;

        height:
            calc(100vh - 150px);

        min-height: 550px;

        max-height: none;

        border-radius: 24px;

    }


    .bottom-tech {

        display: none;

    }

}


@media(max-width:500px) {

    .car-title p {

        display: none;

    }


    .car-title h1 {

        font-size: 17px;

    }


    .available-box span {

        font-size: 8px;

    }

}


/* =========================================================
   REMOVE TEXT SELECTION
========================================================= */

button,
.game,
.game * {

    -webkit-tap-highlight-color:
        transparent;

}

</style>


<!-- =========================================================
     MAIN PAGE
========================================================= -->

<div class="car-page">

    <!-- SIDE GRAPHICS -->

    <div class="graphics graphic-left">

        <div class="side-info">

            <div class="small">
                CONNECTHUB SYSTEM
            </div>

            <h2>
                RACING<br>
                NETWORK
            </h2>

            <p>
                Drive through the
                ConnectHub network,
                avoid traffic and
                build your score.
            </p>

            <div class="side-line"></div>

        </div>

    </div>


    <div class="graphics graphic-right">

        <div class="side-info">

            <div class="small">
                PERFORMANCE
            </div>

            <h2>
                SPEED<br>
                PROTOCOL
            </h2>

            <p>
                Every successful
                run increases your
                reward balance.
            </p>

            <div class="side-line"></div>

        </div>

    </div>


    <!-- PARTICLES -->

    <div id="particles"></div>


    <div class="car-content">


        <!-- =================================================
             HEADER
        ================================================== -->

        <div class="car-header">

            <div class="car-title">

                <div class="car-title-icon">
                    🏎️
                </div>

                <div>

                    <h1>
                        ConnectHub Racing
                    </h1>

                    <p>
                        Avoid traffic • Earn rewards • Beat your score
                    </p>

                </div>

            </div>


            <div class="available-box">

                <span>
                    AVAILABLE EARNINGS
                </span>

                <strong id="topAvailable">
                    ₹<?= number_format($availableEarnings, 2) ?>
                </strong>

            </div>

        </div>


        <!-- =================================================
             GAME
        ================================================== -->

        <div class="car-game-container">

            <div class="game-frame">

                <div class="game" id="game">


                    <!-- ROAD -->

                    <div class="road">

                        <div class="road-edge-left"></div>

                        <div class="road-edge-right"></div>

                    </div>


                    <!-- GAME AREA -->

                    <div id="gameArea">

                        <img
                            id="player"
                            src="uploads/player_car.png"
                            alt="Player Car"
                        >

                    </div>


                    <!-- HUD -->

                    <div class="hud">

                        <div class="hud-box">

                            <div class="hud-title">
                                🏆 SCORE
                            </div>

                            <div
                                class="hud-value"
                                id="score"
                            >
                                0
                            </div>

                        </div>


                        <div class="hud-box">

                            <div class="hud-title">
                                💰 EARNINGS
                            </div>

                            <div
                                class="hud-value earning-value"
                                id="currentEarning"
                            >
                                ₹0
                            </div>

                        </div>


                        <div class="hud-box">

                            <div class="hud-title">
                                🏁 DISTANCE
                            </div>

                            <div
                                class="hud-value"
                                id="distance"
                            >
                                0.00 KM
                            </div>

                        </div>


                        <div class="hud-box">

                            <div class="hud-title">
                                🎮 AVAILABLE
                            </div>

                            <div
                                class="hud-value earning-value"
                                id="availableEarning"
                            >
                                ₹<?= number_format($availableEarnings, 2) ?>
                            </div>

                        </div>

                    </div>


                    <!-- NITRO -->

                    <div class="nitro-bar">

                        <div
                            class="nitro-fill"
                            id="nitroFill"
                        ></div>

                    </div>


                    <!-- CONTROLS -->

                    <div class="controls">

                        <button
                            class="control-button"
                            id="leftButton"
                        >
                            ◀
                        </button>


                        <button
                            class="control-button nitro"
                            id="nitroButton"
                        >
                            ⚡
                        </button>


                        <button
                            class="control-button"
                            id="rightButton"
                        >
                            ▶
                        </button>

                    </div>


                    <!-- START -->

                    <div
                        class="start-screen"
                        id="startScreen"
                    >

                        <div class="start-content">

                            <h1>
                                🏎️ CONNECT RACE
                            </h1>

                            <p>

                                Avoid the opponent cars
                                <br>

                                and survive as long as possible.

                                <br><br>

                                <b>
                                    Every 10 points = ₹1
                                </b>

                            </p>


                            <button
                                class="start-button"
                                id="startButton"
                            >
                                🏁 START RACE
                            </button>

                        </div>

                    </div>


                    <!-- GAME OVER -->

                    <div
                        class="game-over"
                        id="gameOver"
                    >

                        <div class="game-over-content">

                            <h1>
                                💥 CRASH!
                            </h1>


                            <div class="result-card">

                                <div class="result-row">

                                    <span>
                                        Score
                                    </span>

                                    <strong id="finalScore">
                                        0
                                    </strong>

                                </div>


                                <div class="result-row">

                                    <span>
                                        Distance
                                    </span>

                                    <strong id="finalDistance">
                                        0.00 KM
                                    </strong>

                                </div>


                                <div class="result-row">

                                    <span>
                                        Game Earnings
                                    </span>

                                    <strong
                                        class="result-earning"
                                        id="finalEarning"
                                    >
                                        ₹0
                                    </strong>

                                </div>


                                <div class="result-row">

                                    <span>
                                        Available
                                    </span>

                                    <strong
                                        class="result-earning"
                                        id="finalAvailable"
                                    >
                                        ₹<?= number_format($availableEarnings, 2) ?>
                                    </strong>

                                </div>

                            </div>


                            <button
                                class="play-again"
                                id="playAgain"
                            >
                                🔄 PLAY AGAIN
                            </button>


                            <a
                                href="bank.php"
                                class="bank-button"
                            >
                                🏦 GO TO BANKING
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             BOTTOM GRAPHICS
        ================================================== -->

        <div class="bottom-tech">

            <div class="tech-box">

                <strong>
                    ⚡ NITRO
                </strong>

                Turbo Boost

            </div>


            <div class="tech-box">

                <strong>
                    🏆 SCORE
                </strong>

                Build Your Record

            </div>


            <div class="tech-box">

                <strong>
                    💰 REWARDS
                </strong>

                10 Points = ₹1

            </div>


            <div class="tech-box">

                <strong>
                    🏁 RACING
                </strong>

                Endless Drive

            </div>

        </div>


    </div>

</div>


<script>

/* =========================================================
   ELEMENTS
========================================================= */

const gameArea =
    document.getElementById("gameArea");

const player =
    document.getElementById("player");

const scoreText =
    document.getElementById("score");

const earningText =
    document.getElementById("currentEarning");

const availableText =
    document.getElementById("availableEarning");

const topAvailable =
    document.getElementById("topAvailable");

const distanceText =
    document.getElementById("distance");

const startScreen =
    document.getElementById("startScreen");

const gameOver =
    document.getElementById("gameOver");

const finalScore =
    document.getElementById("finalScore");

const finalDistance =
    document.getElementById("finalDistance");

const finalEarning =
    document.getElementById("finalEarning");

const finalAvailable =
    document.getElementById("finalAvailable");

const nitroFill =
    document.getElementById("nitroFill");


/* =========================================================
   VARIABLES
========================================================= */

let lanes = [];

let playerLane = 1;

let playerX = 0;

let playerY = 0;

let running = false;

let score = 0;

let distance = 0;

let speed = 5;

let animationId = null;

let nitro = 100;

let usingNitro = false;

let npcs = [];

let spawnTimer = 0;


/* =========================================================
   LANES
========================================================= */

function setupLanes() {

    const width =
        gameArea.clientWidth;

    lanes = [

        width * .235,

        width * .50,

        width * .765

    ];

}


/* =========================================================
   PLAYER POSITION
========================================================= */

function positionPlayer() {

    setupLanes();

    playerX =
        lanes[playerLane]
        - player.offsetWidth / 2;

    playerY =
        gameArea.clientHeight
        - player.offsetHeight
        - 105;

    player.style.left =
        playerX + "px";

    player.style.top =
        playerY + "px";

}


/* =========================================================
   MOVE LEFT
========================================================= */

function moveLeft() {

    if (!running)
        return;

    if (playerLane > 0) {

        playerLane--;

        positionPlayer();

    }

}


/* =========================================================
   MOVE RIGHT
========================================================= */

function moveRight() {

    if (!running)
        return;

    if (playerLane < 2) {

        playerLane++;

        positionPlayer();

    }

}


/* =========================================================
   KEYBOARD
========================================================= */

document.addEventListener(
    "keydown",
    function(e) {

        if (e.key === "ArrowLeft") {

            e.preventDefault();

            moveLeft();

        }


        if (e.key === "ArrowRight") {

            e.preventDefault();

            moveRight();

        }


        if (
            e.code === "Space" ||
            e.key === "ArrowUp"
        ) {

            e.preventDefault();

            startNitro();

        }

    }
);


document.addEventListener(
    "keyup",
    function(e) {

        if (
            e.code === "Space" ||
            e.key === "ArrowUp"
        ) {

            stopNitro();

        }

    }
);


/* =========================================================
   BUTTONS
========================================================= */

document
    .getElementById("leftButton")
    .addEventListener(
        "click",
        moveLeft
    );


document
    .getElementById("rightButton")
    .addEventListener(
        "click",
        moveRight
    );


/* =========================================================
   NITRO
========================================================= */

document
    .getElementById("nitroButton")
    .addEventListener(
        "mousedown",
        startNitro
    );


document
    .getElementById("nitroButton")
    .addEventListener(
        "mouseup",
        stopNitro
    );


document
    .getElementById("nitroButton")
    .addEventListener(
        "mouseleave",
        stopNitro
    );


document
    .getElementById("nitroButton")
    .addEventListener(
        "touchstart",
        function(e) {

            e.preventDefault();

            startNitro();

        }
    );


document
    .getElementById("nitroButton")
    .addEventListener(
        "touchend",
        function(e) {

            e.preventDefault();

            stopNitro();

        }
    );


function startNitro() {

    if (
        running &&
        nitro > 0
    ) {

        usingNitro = true;

    }

}


function stopNitro() {

    usingNitro = false;

}


/* =========================================================
   CREATE NPC
========================================================= */

function createNPC() {

    if (!running)
        return;

    setupLanes();

    if (npcs.length >= 5)
        return;


    const npc =
        document.createElement("img");


    npc.src =
        "uploads/npc_car.png";


    npc.className =
        "npc";


    npc.draggable = false;


    const lane =
        Math.floor(
            Math.random() * 3
        );


    npc.style.left =
        (
            lanes[lane] - 39
        ) + "px";


    npc.style.top =
        "-180px";


    gameArea.appendChild(npc);


    npcs.push({

        element: npc,

        lane: lane,

        y: -180,

        speed:
            speed *
            (
                .75 +
                Math.random() * .45
            )

    });

}


/* =========================================================
   COLLISION
========================================================= */

function collision(a, b) {

    const r1 =
        a.getBoundingClientRect();

    const r2 =
        b.getBoundingClientRect();

    const padding = 14;

    return !(
        r1.right - padding <
        r2.left + padding ||

        r1.left + padding >
        r2.right - padding ||

        r1.bottom - padding <
        r2.top + padding ||

        r1.top + padding >
        r2.bottom - padding
    );

}


/* =========================================================
   UPDATE NPC
========================================================= */

function updateNPCs() {

    for (
        let i = npcs.length - 1;
        i >= 0;
        i--
    ) {

        const npc =
            npcs[i];


        npc.y +=
            npc.speed *
            (
                usingNitro
                ? .75
                : 1
            );


        npc.element.style.top =
            npc.y + "px";


        if (
            collision(
                player,
                npc.element
            )
        ) {

            endGame();

            return;

        }


        if (
            npc.y >
            gameArea.clientHeight + 200
        ) {

            npc.element.remove();

            npcs.splice(i, 1);

            score += 2;

        }

    }

}


/* =========================================================
   GAME LOOP
========================================================= */

function gameLoop() {

    if (!running)
        return;


    if (
        usingNitro &&
        nitro > 0
    ) {

        nitro -= .8;

        speed = 9;

    } else {

        usingNitro = false;


        if (nitro < 100) {

            nitro += .15;

        }


        speed =
            5 +
            Math.min(
                score / 150,
                3
            );

    }


    nitro =
        Math.max(
            0,
            Math.min(
                100,
                nitro
            )
        );


    nitroFill.style.width =
        nitro + "%";


    score += .08;


    distance +=
        speed * .002;


    const displayScore =
        Math.floor(score);


    const displayEarning =
        Math.floor(
            displayScore / 10
        );


    scoreText.innerText =
        displayScore;


    earningText.innerText =
        "₹" +
        displayEarning;


    distanceText.innerText =
        distance.toFixed(2) +
        " KM";


    spawnTimer++;


    const spawnRate =
        Math.max(
            32,
            75 -
            Math.floor(score / 20)
        );


    if (
        spawnTimer >=
        spawnRate
    ) {

        createNPC();

        spawnTimer = 0;

    }


    updateNPCs();


    if (running) {

        animationId =
            requestAnimationFrame(
                gameLoop
            );

    }

}


/* =========================================================
   START GAME
========================================================= */

function startGame() {

    cancelAnimationFrame(
        animationId
    );


    npcs.forEach(
        npc =>
            npc.element.remove()
    );


    npcs = [];


    score = 0;

    distance = 0;

    nitro = 100;

    playerLane = 1;

    spawnTimer = 0;

    running = true;


    setupLanes();

    positionPlayer();


    startScreen.style.display =
        "none";


    gameOver.style.display =
        "none";


    gameLoop();

}


/* =========================================================
   SAVE EARNINGS
========================================================= */

async function saveEarnings(
    finalScoreValue
) {

    const form =
        new FormData();


    form.append(
        "save_game",
        "1"
    );


    form.append(
        "score",
        Math.floor(
            finalScoreValue
        )
    );


    try {

        const response =
            await fetch(
                "car.php",
                {
                    method: "POST",

                    body: form,

                    credentials: "same-origin"
                }
            );


        const text =
            await response.text();


        let data;


        try {

            data =
                JSON.parse(text);

        } catch (jsonError) {

            console.error(
                "Server response:",
                text
            );

            finalEarning.innerText =
                "SAVE FAILED";

            return;

        }


        if (data.success) {

            const amount =
                Number(
                    data.available
                ).toFixed(2);


            availableText.innerText =
                "₹" + amount;


            topAvailable.innerText =
                "₹" + amount;


            finalAvailable.innerText =
                "₹" + amount;


            finalEarning.innerText =
                "₹" +
                Number(
                    data.earning
                ).toFixed(2);

        } else {

            finalEarning.innerText =
                "SAVE FAILED";

            console.error(
                data.message
            );

        }

    } catch(error) {

        console.error(error);

        finalEarning.innerText =
            "SAVE FAILED";

    }

}


/* =========================================================
   END GAME
========================================================= */

function endGame() {

    if (!running)
        return;


    running = false;


    cancelAnimationFrame(
        animationId
    );


    usingNitro = false;


    const finalScoreValue =
        Math.floor(score);


    const finalEarningValue =
        Math.floor(
            finalScoreValue / 10
        );


    finalScore.innerText =
        finalScoreValue;


    finalDistance.innerText =
        distance.toFixed(2) +
        " KM";


    finalEarning.innerText =
        "₹" +
        finalEarningValue;


    gameOver.style.display =
        "flex";


    saveEarnings(
        finalScoreValue
    );

}


/* =========================================================
   START BUTTON
========================================================= */

document
    .getElementById("startButton")
    .addEventListener(
        "click",
        startGame
    );


/* =========================================================
   PLAY AGAIN
========================================================= */

document
    .getElementById("playAgain")
    .addEventListener(
        "click",
        startGame
    );


/* =========================================================
   TOUCH SWIPE
========================================================= */

let touchStartX = 0;


gameArea.addEventListener(
    "touchstart",
    function(e) {

        touchStartX =
            e.touches[0].clientX;

    },
    {
        passive: true
    }
);


gameArea.addEventListener(
    "touchend",
    function(e) {

        const touchEndX =
            e.changedTouches[0].clientX;


        const difference =
            touchEndX -
            touchStartX;


        if (
            Math.abs(difference) >
            40
        ) {

            if (difference < 0) {

                moveLeft();

            } else {

                moveRight();

            }

        }

    },
    {
        passive: true
    }
);


/* =========================================================
   RESIZE
========================================================= */

window.addEventListener(
    "resize",
    function() {

        positionPlayer();

    }
);


/* =========================================================
   CREATE BACKGROUND PARTICLES
========================================================= */

const particleContainer =
    document.getElementById(
        "particles"
    );


for (
    let i = 0;
    i < 45;
    i++
) {

    const p =
        document.createElement(
            "div"
        );


    p.className =
        "particle";


    p.style.left =
        Math.random() * 100 +
        "%";


    p.style.animationDuration =
        (
            5 +
            Math.random() * 10
        ) +
        "s";


    p.style.animationDelay =
        (
            Math.random() * 8
        ) +
        "s";


    p.style.opacity =
        (
            .2 +
            Math.random() * .7
        );


    particleContainer.appendChild(p);

}


/* =========================================================
   INITIAL POSITION
========================================================= */

window.addEventListener(
    "load",
    function() {

        positionPlayer();

    }
);

</script>


<?php

require "footer.php";

?>