```php
<?php
// ============================================================
// CONNECTHUB - SPACE SHOOTER
// SCREEN STYLE LIKE SNAKE GAME
// FULL PAGE BACKGROUND
// TITLE BAR + EARNINGS BARS
// SMALL GAME BAR INSIDE GAME
// PLAYER PNG + ENEMY PNG
// SCORE + EARNINGS + LIVES
// ============================================================

require "config.php";

login_required();

$uid = (int)($_SESSION["user_id"] ?? 0);

if ($uid <= 0) {
    header("Location: login.php");
    exit;
}


// ============================================================
// SAVE SCORE + EARNINGS
// ============================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["save_game"])
) {

    header("Content-Type: application/json");

    $score = (int)($_POST["score"] ?? 0);

    if ($score < 0) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid score."
        ]);
        exit;
    }

    $earning = floor($score / 10);

    try {

        $conn->begin_transaction();

        // ====================================================
        // GET SPACE SHOOTER GAME ID
        // ====================================================

        $gameId = 0;

        $stmt = $conn->prepare("
            SELECT id
            FROM games
            WHERE slug = 'space-shooter'
            LIMIT 1
        ");

        if ($stmt) {

            $stmt->execute();

            $result = $stmt->get_result();

            if (
                $result &&
                $result->num_rows > 0
            ) {

                $row = $result->fetch_assoc();

                $gameId = (int)$row["id"];
            }

            $stmt->close();
        }


        // Fallback by game name
        if ($gameId <= 0) {

            $stmt = $conn->prepare("
                SELECT id
                FROM games
                WHERE LOWER(name) = 'space shooter'
                LIMIT 1
            ");

            if ($stmt) {

                $stmt->execute();

                $result = $stmt->get_result();

                if (
                    $result &&
                    $result->num_rows > 0
                ) {

                    $row = $result->fetch_assoc();

                    $gameId =
                        (int)$row["id"];
                }

                $stmt->close();
            }
        }


        if ($gameId <= 0) {

            throw new Exception(
                "Space Shooter game was not found in the games table."
            );
        }


        // ====================================================
        // SAVE SCORE
        // ====================================================

        $stmt = $conn->prepare("
            INSERT INTO game_scores
            (
                user_id,
                game_id,
                score
            )
            VALUES (?, ?, ?)
        ");

        if (!$stmt) {

            throw new Exception(
                "Could not prepare score query."
            );
        }

        $stmt->bind_param(
            "iii",
            $uid,
            $gameId,
            $score
        );

        if (!$stmt->execute()) {

            throw new Exception(
                "Could not save score."
            );
        }

        $stmt->close();


        // ====================================================
        // SAVE GAME EARNINGS
        // ====================================================

        if ($earning > 0) {

            $gameName =
                "space_shooter";

            $status =
                "available";

            $stmt = $conn->prepare("
                INSERT INTO game_earnings
                (
                    user_id,
                    game,
                    amount,
                    status
                )
                VALUES (?, ?, ?, ?)
            ");

            if (!$stmt) {

                throw new Exception(
                    "Could not prepare earnings query."
                );
            }

            $stmt->bind_param(
                "isds",
                $uid,
                $gameName,
                $earning,
                $status
            );

            if (!$stmt->execute()) {

                throw new Exception(
                    "Could not save game earnings."
                );
            }

            $stmt->close();
        }


        // ====================================================
        // GET UPDATED AVAILABLE EARNINGS
        // ====================================================

        $available = 0.00;

        $stmt = $conn->prepare("
            SELECT
                COALESCE(
                    SUM(amount),
                    0
                ) AS total
            FROM game_earnings
            WHERE user_id = ?
            AND LOWER(TRIM(status)) = 'available'
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

                $available =
                    (float)(
                        $row["total"] ?? 0
                    );
            }

            $stmt->close();
        }


        $conn->commit();


        echo json_encode([
            "success" => true,
            "score" => $score,
            "earning" => $earning,
            "available" => $available,
            "message" =>
                "Score and earnings saved successfully."
        ]);

        exit;


    } catch (Throwable $e) {

        $conn->rollback();

        echo json_encode([
            "success" => false,
            "message" =>
                $e->getMessage()
        ]);

        exit;
    }
}


// ============================================================
// GET AVAILABLE GAME EARNINGS
// ============================================================

$availableEarnings = 0.00;

$stmt = $conn->prepare("
    SELECT
        COALESCE(
            SUM(amount),
            0
        ) AS total
    FROM game_earnings
    WHERE user_id = ?
    AND LOWER(TRIM(status)) = 'available'
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

        $availableEarnings =
            (float)(
                $row["total"] ?? 0
            );
    }

    $stmt->close();
}

?>

<?php require "header.php"; ?>


<!-- ============================================================
     FULL SPACE SHOOTER PAGE
============================================================ -->

<div class="space-page">


    <!-- ========================================================
         FULL PAGE BACKGROUND
    ========================================================= -->

    <div class="space-page-bg"></div>


    <!-- ========================================================
         PAGE CONTENT
    ========================================================= -->

    <div class="space-content">


        <!-- ====================================================
             TITLE BAR
        ===================================================== -->

        <div class="space-title-bar">

            <div class="title-left">

                <div class="title-icon">
                    🚀
                </div>

                <div>

                    <div class="title-small">
                        CONNECTHUB ARCADE
                    </div>

                    <h1>
                        Space Shooter
                    </h1>

                    <p>
                        Destroy enemy ships, survive the galaxy and earn money.
                    </p>

                </div>

            </div>


            <div class="title-score-box">

                <span>
                    SCORE
                </span>

                <strong id="topScore">
                    0
                </strong>

            </div>

        </div>


        <!-- ====================================================
             AVAILABLE EARNINGS BAR
        ===================================================== -->

        <div class="available-bar">

            <div class="available-left">

                <div class="money-icon">
                    💰
                </div>

                <div>

                    <strong>
                        Available Earnings
                    </strong>

                    <span>
                        Total game earnings
                    </span>

                </div>

            </div>


            <div class="available-right">

                <strong
                    id="availableTop"
                >
                    ₹<?= number_format(
                        $availableEarnings,
                        2
                    ) ?>
                </strong>

                <small>
                    Every 10 points = ₹1
                </small>

            </div>

        </div>


        <!-- ====================================================
             CURRENT GAME EARNING BAR
        ===================================================== -->

        <div class="current-bar">

            <div class="current-left">

                <div class="target-icon">
                    🎯
                </div>

                <div>

                    <strong>
                        Current Game Earnings
                    </strong>

                    <span>
                        Earnings from this Space Shooter round
                    </span>

                </div>

            </div>


            <strong
                id="currentEarning"
            >
                ₹0
            </strong>

        </div>


        <!-- ====================================================
             ACTUAL GAME
        ===================================================== -->

        <div
            class="game-box"
            id="gameBox"
        >


            <!-- FULL GAME IMAGE -->

            <div class="game-full-bg"></div>


            <!-- CANVAS -->

            <canvas
                id="spaceCanvas"
            ></canvas>


            <!-- =================================================
                 SMALL BAR INSIDE GAME
            ================================================== -->

            <div class="inside-game-bar">

                <div class="inside-bar-content">

                    <div class="inside-stat">

                        <span>
                            SCORE
                        </span>

                        <strong
                            id="score"
                        >
                            0
                        </strong>

                    </div>


                    <div class="inside-stat">

                        <span>
                            💰 EARN
                        </span>

                        <strong
                            id="gameEarning"
                            class="green"
                        >
                            ₹0
                        </strong>

                    </div>


                    <div class="inside-stat">

                        <span>
                            ❤️ LIVES
                        </span>

                        <strong
                            id="lives"
                        >
                            3
                        </strong>

                    </div>


                    <div class="inside-stat">

                        <span>
                            🎮 AVAILABLE
                        </span>

                        <strong
                            id="insideAvailable"
                            class="green"
                        >
                            ₹<?= number_format(
                                $availableEarnings,
                                2
                            ) ?>
                        </strong>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 START SCREEN
            ================================================== -->

            <div
                id="startScreen"
                class="game-overlay"
            >

                <div class="start-card">

                    <div class="rocket-big">
                        🚀
                    </div>

                    <div class="arcade-label">
                        CONNECTHUB ORIGINAL
                    </div>

                    <h2>
                        SPACE SHOOTER
                    </h2>

                    <p>
                        Enter the galaxy, destroy enemy ships,
                        survive as long as possible and earn rewards.
                    </p>


                    <div class="control-info">

                        <div>
                            🎯 Destroy enemies
                        </div>

                        <div>
                            ← → Move
                        </div>

                        <div>
                            SPACE Shoot
                        </div>

                        <div>
                            💰 10 Points = ₹1
                        </div>

                    </div>


                    <button
                        type="button"
                        id="startButton"
                    >
                        🚀 START MISSION
                    </button>

                </div>

            </div>


            <!-- =================================================
                 GAME OVER
            ================================================== -->

            <div
                id="gameOver"
                class="game-overlay hidden"
            >

                <div class="gameover-card">

                    <div class="skull">
                        💀
                    </div>

                    <h2>
                        GAME OVER
                    </h2>

                    <div class="gameover-label">
                        YOUR SCORE
                    </div>

                    <strong
                        id="finalScore"
                        class="big-score"
                    >
                        0
                    </strong>


                    <div class="final-earning-box">

                        💰 YOU EARNED

                        <strong
                            id="finalEarning"
                        >
                            ₹0
                        </strong>

                    </div>


                    <div class="final-available">

                        Available Game Earnings:

                        <strong
                            id="finalAvailable"
                        >
                            ₹<?= number_format(
                                $availableEarnings,
                                2
                            ) ?>
                        </strong>

                    </div>


                    <div
                        id="saveStatus"
                        class="save-status"
                    >
                        Saving earnings...
                    </div>


                    <button
                        type="button"
                        id="restartButton"
                        class="restart-button"
                    >
                        🔄 PLAY AGAIN
                    </button>


                    <a
                        href="bank.php?from=game"
                        class="bank-button"
                    >
                        🏦 GO TO BANKING
                    </a>

                </div>

            </div>


            <!-- =================================================
                 MOBILE CONTROLS
            ================================================== -->

            <div class="mobile-controls">

                <button
                    type="button"
                    id="leftButton"
                >
                    ◀
                </button>

                <button
                    type="button"
                    id="fireButton"
                >
                    🔫
                </button>

                <button
                    type="button"
                    id="rightButton"
                >
                    ▶
                </button>

            </div>


        </div>


        <!-- ====================================================
             BACK
        ===================================================== -->

        <div class="back-area">

            <a
                href="games.php"
                class="back-games-link"
            >
                ← Back to Games
            </a>

        </div>


    </div>

</div>


<style>

/* ============================================================
   PAGE
============================================================ */

.space-page {

    position: relative;

    width: 100%;

    min-height: calc(100vh - 70px);

    overflow: hidden;

    background: #020617;

}


/* ============================================================
   FULL PAGE BACKGROUND
============================================================ */

.space-page-bg {

    position: fixed;

    top: 0;

    right: 0;

    bottom: 0;

    left: 0;

    background-image:
        linear-gradient(
            rgba(2,6,23,.36),
            rgba(2,6,23,.52)
        ),
        url("uploads/space_full_bg.jpg");

    background-size: cover;

    background-position: center;

    background-repeat: no-repeat;

    z-index: 0;

    pointer-events: none;

}


/* ============================================================
   CONTENT
============================================================ */

.space-content {

    position: relative;

    z-index: 2;

    width: 100%;

    max-width: 1100px;

    margin: 0 auto;

    padding: 18px 25px 40px;

    box-sizing: border-box;

}


/* ============================================================
   TITLE BAR
============================================================ */

.space-title-bar {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 15px 18px;

    background:
        rgba(2,6,23,.90);

    border:
        1px solid
        rgba(255,255,255,.16);

    border-radius: 17px;

    box-shadow:
        0 12px 35px
        rgba(0,0,0,.28);

    backdrop-filter: blur(12px);

}


.title-left {

    display: flex;

    align-items: center;

    gap: 13px;

}


.title-icon {

    width: 48px;

    height: 48px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 13px;

    background:
        rgba(59,130,246,.16);

    font-size: 26px;

}


.title-small {

    color: #38bdf8;

    font-size: 8px;

    font-weight: 900;

    letter-spacing: 3px;

}


.space-title-bar h1 {

    margin: 2px 0 2px;

    color: white;

    font-size: 24px;

}


.space-title-bar p {

    margin: 0;

    color: #cbd5e1;

    font-size: 11px;

}


.title-score-box {

    min-width: 90px;

    padding: 9px 13px;

    text-align: center;

    border-radius: 12px;

    background:
        rgba(79,70,229,.18);

    border:
        1px solid
        rgba(129,140,248,.60);

}


.title-score-box span {

    display: block;

    color: #c7d2fe;

    font-size: 8px;

    font-weight: 800;

    letter-spacing: 1px;

}


.title-score-box strong {

    display: block;

    margin-top: 1px;

    color: #a5b4fc;

    font-size: 23px;

}


/* ============================================================
   AVAILABLE BAR
============================================================ */

.available-bar {

    margin-top: 11px;

    padding: 12px 17px;

    border-radius: 13px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    background:
        rgba(2,60,36,.92);

    border:
        1px solid
        rgba(34,197,94,.55);

    box-shadow:
        0 9px 22px
        rgba(0,0,0,.20);

}


.available-left {

    display: flex;

    align-items: center;

    gap: 10px;

}


.money-icon {

    font-size: 25px;

}


.available-left strong,
.available-left span {

    display: block;

}


.available-left strong {

    color: white;

    font-size: 13px;

}


.available-left span {

    margin-top: 2px;

    color: #a7f3d0;

    font-size: 9px;

}


.available-right {

    text-align: right;

}


.available-right strong {

    display: block;

    color: #4ade80;

    font-size: 20px;

}


.available-right small {

    color: #86efac;

    font-size: 8px;

}


/* ============================================================
   CURRENT EARNING
============================================================ */

.current-bar {

    margin-top: 9px;

    padding: 10px 17px;

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    background:
        rgba(91,45,0,.92);

    border:
        1px solid
        rgba(249,115,22,.55);

    box-shadow:
        0 8px 20px
        rgba(0,0,0,.20);

}


.current-left {

    display: flex;

    align-items: center;

    gap: 10px;

}


.target-icon {

    font-size: 23px;

}


.current-left strong,
.current-left span {

    display: block;

}


.current-left strong {

    color: white;

    font-size: 12px;

}


.current-left span {

    margin-top: 2px;

    color: #fed7aa;

    font-size: 8px;

}


#currentEarning {

    color: #fb923c;

    font-size: 21px;

    font-weight: 900;

}


/* ============================================================
   GAME BOX
============================================================ */

.game-box {

    position: relative;

    width: 100%;

    height: 650px;

    margin-top: 12px;

    overflow: hidden;

    border-radius: 20px;

    background: #020617;

    border:
        2px solid
        rgba(255,255,255,.12);

    box-shadow:
        0 20px 60px
        rgba(0,0,0,.42);

}


/* ============================================================
   GAME BACKGROUND INSIDE GAME
============================================================ */

.game-full-bg {

    position: absolute;

    inset: 0;

    background-image:
        linear-gradient(
            rgba(0,0,0,.05),
            rgba(0,0,0,.20)
        ),
        url("uploads/space_full_bg.jpg");

    background-size: cover;

    background-position: center;

    background-repeat: no-repeat;

    z-index: 1;

}


/* ============================================================
   CANVAS
============================================================ */

#spaceCanvas {

    position: absolute;

    inset: 0;

    width: 100%;

    height: 100%;

    display: block;

    z-index: 5;

}


/* ============================================================
   SMALL IMAGE BAR INSIDE THE GAME
============================================================ */

.inside-game-bar {

    position: absolute;

    top: 13px;

    left: 50%;

    transform: translateX(-50%);

    width: 570px;

    max-width: calc(100% - 25px);

    height: 62px;

    z-index: 35;

    border-radius: 14px;

    overflow: hidden;

    background-image:
        url("uploads/space_game_bar.jpg");

    background-size: cover;

    background-position: center;

    background-repeat: no-repeat;

    border:
        1px solid
        rgba(255,255,255,.28);

    box-shadow:
        0 8px 22px
        rgba(0,0,0,.55);

}


/* DARKEN BAR IMAGE SLIGHTLY */

.inside-game-bar::before {

    content: "";

    position: absolute;

    inset: 0;

    background:
        rgba(0,0,0,.28);

    z-index: 0;

}


/* ============================================================
   BAR CONTENT
============================================================ */

.inside-bar-content {

    position: relative;

    z-index: 2;

    width: 100%;

    height: 100%;

    padding: 5px;

    display: grid;

    grid-template-columns:
        repeat(4,1fr);

    gap: 5px;

}


.inside-stat {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    border-radius: 9px;

    background:
        rgba(2,6,23,.45);

    border:
        1px solid
        rgba(255,255,255,.08);

}


.inside-stat span {

    color:
        rgba(255,255,255,.78);

    font-size:
        7px;

    font-weight:
        900;

    letter-spacing:
        .7px;

}


.inside-stat strong {

    color:
        white;

    font-size:
        14px;

    font-weight:
        900;

}


.inside-stat .green {

    color:
        #4ade80;

}


/* ============================================================
   GAME OVER / START OVERLAY
============================================================ */

.game-overlay {

    position: absolute;

    inset: 0;

    z-index: 100;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 20px;

    background:
        rgba(1,5,15,.48);

    backdrop-filter:
        blur(4px);

}


.hidden {

    display:
        none !important;

}


/* ============================================================
   START CARD
============================================================ */

.start-card {

    width:
        min(600px,92%);

    padding:
        30px;

    text-align:
        center;

    border-radius:
        21px;

    background:
        rgba(2,6,23,.94);

    border:
        1px solid
        rgba(255,255,255,.17);

    color:
        white;

    box-shadow:
        0 25px 70px
        rgba(0,0,0,.58);

}


.rocket-big {

    font-size:
        48px;

}


.arcade-label {

    color:
        #38bdf8;

    font-size:
        9px;

    letter-spacing:
        3px;

    font-weight:
        900;

    margin-top:
        5px;

}


.start-card h2 {

    margin:
        5px 0 10px;

    font-size:
        38px;

    background:
        linear-gradient(
            90deg,
            #38bdf8,
            #818cf8,
            #c084fc
        );

    -webkit-background-clip:
        text;

    color:
        transparent;

}


.start-card p {

    margin:
        0 0 18px;

    color:
        #cbd5e1;

    line-height:
        1.6;

    font-size:
        13px;

}


.control-info {

    display:
        grid;

    grid-template-columns:
        repeat(2,1fr);

    gap:
        8px;

    margin-bottom:
        18px;

}


.control-info div {

    padding:
        10px;

    border-radius:
        9px;

    background:
        rgba(255,255,255,.06);

    color:
        #dbeafe;

    font-size:
        11px;

}


#startButton {

    width:
        100%;

    padding:
        14px;

    border:
        none;

    border-radius:
        11px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    color:
        white;

    font-weight:
        900;

    font-size:
        15px;

    cursor:
        pointer;

}


/* ============================================================
   GAME OVER CARD
============================================================ */

.gameover-card {

    width:
        min(430px,92%);

    padding:
        28px;

    border-radius:
        21px;

    background:
        rgba(2,6,23,.96);

    border:
        1px solid
        rgba(255,255,255,.15);

    text-align:
        center;

    color:
        white;

}


.skull {

    font-size:
        50px;

}


.gameover-card h2 {

    margin:
        3px 0 5px;

    font-size:
        32px;

}


.gameover-label {

    color:
        #94a3b8;

    font-size:
        9px;

    letter-spacing:
        2px;

}


.big-score {

    display:
        block;

    margin:
        4px 0 15px;

    color:
        #a5b4fc;

    font-size:
        46px;

}


.final-earning-box {

    padding:
        10px 15px;

    margin-bottom:
        10px;

    border:
        1px solid
        rgba(34,197,94,.45);

    border-radius:
        10px;

    color:
        #d1fae5;

}


.final-earning-box strong {

    color:
        #4ade80;

    font-size:
        20px;

    margin-left:
        5px;

}


.final-available {

    padding:
        9px;

    border-radius:
        9px;

    background:
        rgba(255,255,255,.06);

    color:
        #cbd5e1;

    font-size:
        11px;

}


.final-available strong {

    color:
        #4ade80;

}


.save-status {

    margin:
        10px 0;

    color:
        #86efac;

    font-size:
        11px;

}


.restart-button,
.bank-button {

    display:
        block;

    width:
        100%;

    padding:
        13px;

    border-radius:
        10px;

    margin-top:
        8px;

    text-align:
        center;

    font-weight:
        900;

    text-decoration:
        none;

}


.restart-button {

    border:
        none;

    cursor:
        pointer;

    background:
        linear-gradient(
            135deg,
            #6366f1,
            #8b5cf6
        );

    color:
        white;

}


.bank-button {

    background:
        #16a34a;

    color:
        white;

}


/* ============================================================
   MOBILE CONTROLS
============================================================ */

.mobile-controls {

    display:
        none;

    position:
        absolute;

    left:
        15px;

    right:
        15px;

    bottom:
        15px;

    z-index:
        60;

    justify-content:
        space-between;

}


.mobile-controls button {

    width:
        58px;

    height:
        58px;

    border:
        1px solid
        rgba(255,255,255,.20);

    border-radius:
        50%;

    background:
        rgba(2,6,23,.82);

    color:
        white;

    font-size:
        22px;

}


/* ============================================================
   BACK
============================================================ */

.back-area {

    text-align:
        center;

    margin-top:
        15px;

}


.back-games-link {

    color:
        white;

    text-decoration:
        none;

    padding:
        10px 18px;

    background:
        rgba(2,6,23,.78);

    border-radius:
        10px;

    border:
        1px solid
        rgba(255,255,255,.12);

    font-weight:
        700;

    font-size:
        12px;

}


/* ============================================================
   RESPONSIVE
============================================================ */

@media(max-width:800px) {

    .space-content {

        padding:
            12px 12px 30px;

    }


    .game-box {

        height:
            620px;

        min-height:
            620px;

    }


    .inside-game-bar {

        width:
            calc(100% - 22px);

        height:
            55px;

    }


    .inside-bar-content {

        gap:
            3px;

    }


    .inside-stat {

        flex-direction:
            column;

        gap:
            1px;

    }


    .inside-stat span {

        font-size:
            6px;

    }


    .inside-stat strong {

        font-size:
            12px;

    }

}


@media(max-width:600px) {

    .space-title-bar {

        padding:
            12px;

    }


    .title-icon {

        width:
            40px;

        height:
            40px;

        font-size:
            22px;

    }


    .space-title-bar h1 {

        font-size:
            20px;

    }


    .space-title-bar p {

        font-size:
            9px;

    }


    .title-score-box {

        min-width:
            65px;

    }


    .title-score-box strong {

        font-size:
            20px;

    }


    .available-bar,
    .current-bar {

        padding:
            10px 12px;

    }


    .available-right strong,
    #currentEarning {

        font-size:
            17px;

    }


    .available-left strong,
    .current-left strong {

        font-size:
            10px;

    }


    .game-box {

        height:
            calc(100vh - 300px);

        min-height:
            500px;

        border-radius:
            15px;

    }


    .inside-game-bar {

        top:
            9px;

        height:
            49px;

        width:
            calc(100% - 18px);

    }


    .inside-stat strong {

        font-size:
            10px;

    }


    .control-info {

        grid-template-columns:
            1fr;

    }


    .start-card {

        padding:
            22px;

    }


    .start-card h2 {

        font-size:
            29px;

    }


    .mobile-controls {

        display:
            flex;

    }

}

</style>


<script>

/* ============================================================
   ELEMENTS
============================================================ */

const canvas =
    document.getElementById("spaceCanvas");

const ctx =
    canvas.getContext("2d");


const startScreen =
    document.getElementById("startScreen");

const gameOver =
    document.getElementById("gameOver");


const startButton =
    document.getElementById("startButton");

const restartButton =
    document.getElementById("restartButton");


const scoreEl =
    document.getElementById("score");

const topScoreEl =
    document.getElementById("topScore");


const earningEl =
    document.getElementById("gameEarning");

const currentEarningEl =
    document.getElementById("currentEarning");


const livesEl =
    document.getElementById("lives");


const availableEl =
    document.getElementById("insideAvailable");

const availableTopEl =
    document.getElementById("availableTop");


const finalScoreEl =
    document.getElementById("finalScore");

const finalEarningEl =
    document.getElementById("finalEarning");

const finalAvailableEl =
    document.getElementById("finalAvailable");


const saveStatusEl =
    document.getElementById("saveStatus");


/* ============================================================
   IMAGE ASSETS
============================================================ */

const playerShip =
    new Image();

playerShip.src =
    "uploads/player_ship.png";


const enemyShip =
    new Image();

enemyShip.src =
    "uploads/enemy_ship.png";


/* ============================================================
   GAME VARIABLES
============================================================ */

let canvasWidth = 0;

let canvasHeight = 0;

let running = false;

let animationId = 0;

let score = 0;

let lives = 3;

let bullets = [];

let enemies = [];

let particles = [];

let stars = [];

let keys = {};

let spawnTimer = 0;

let spawnDelay = 850;

let fireCooldown = 0;


/* ============================================================
   PLAYER
============================================================ */

const player = {

    x: 0,

    y: 0,

    width: 88,

    height: 112,

    speed: 7

};


/* ============================================================
   RESIZE
============================================================ */

function resizeGame() {

    const rect =
        canvas.getBoundingClientRect();


    canvasWidth =
        rect.width;

    canvasHeight =
        rect.height;


    const dpr =
        window.devicePixelRatio || 1;


    canvas.width =
        Math.floor(
            canvasWidth * dpr
        );

    canvas.height =
        Math.floor(
            canvasHeight * dpr
        );


    ctx.setTransform(
        dpr,
        0,
        0,
        dpr,
        0,
        0
    );


    if (
        player.x === 0
    ) {

        player.x =
            canvasWidth / 2;

    }


    player.y =
        canvasHeight - 105;


    player.x =
        Math.max(
            player.width / 2,
            Math.min(
                canvasWidth -
                player.width / 2,
                player.x
            )
        );

}


window.addEventListener(
    "resize",
    resizeGame
);


/* ============================================================
   STARS
============================================================ */

function createStars() {

    stars = [];


    for (
        let i = 0;
        i < 100;
        i++
    ) {

        stars.push({

            x:
                Math.random() *
                canvasWidth,

            y:
                Math.random() *
                canvasHeight,

            size:
                Math.random() *
                2 +
                .5,

            speed:
                Math.random() *
                1.3 +
                .4

        });

    }

}


function updateStars() {

    ctx.fillStyle =
        "rgba(255,255,255,.70)";


    stars.forEach(
        star => {

            star.y +=
                star.speed;


            if (
                star.y >
                canvasHeight
            ) {

                star.y = 0;

                star.x =
                    Math.random() *
                    canvasWidth;

            }


            ctx.beginPath();

            ctx.arc(
                star.x,
                star.y,
                star.size,
                0,
                Math.PI * 2
            );

            ctx.fill();

        }
    );

}


/* ============================================================
   PLAYER
============================================================ */

function drawPlayer() {

    if (
        playerShip.complete &&
        playerShip.naturalWidth > 0
    ) {

        ctx.drawImage(

            playerShip,

            player.x -
            player.width / 2,

            player.y -
            player.height / 2,

            player.width,

            player.height

        );

    } else {

        ctx.fillStyle =
            "#38bdf8";


        ctx.beginPath();

        ctx.moveTo(
            player.x,
            player.y -
            player.height / 2
        );

        ctx.lineTo(
            player.x -
            player.width / 2,
            player.y +
            player.height / 2
        );

        ctx.lineTo(
            player.x +
            player.width / 2,
            player.y +
            player.height / 2
        );

        ctx.closePath();

        ctx.fill();

    }

}


/* ============================================================
   FIRE
============================================================ */

function fireBullet() {

    if (
        !running ||
        fireCooldown > 0
    ) {

        return;

    }


    bullets.push({

        x:
            player.x,

        y:
            player.y -
            player.height / 2,

        speed:
            13

    });


    fireCooldown =
        7;

}


function updateBullets() {

    for (
        let i =
            bullets.length - 1;

        i >= 0;

        i--
    ) {

        const bullet =
            bullets[i];


        bullet.y -=
            bullet.speed;


        ctx.save();

        ctx.fillStyle =
            "#67e8f9";

        ctx.shadowColor =
            "#22d3ee";

        ctx.shadowBlur =
            14;


        ctx.fillRect(
            bullet.x - 3,
            bullet.y,
            6,
            22
        );


        ctx.restore();


        if (
            bullet.y <
            -30
        ) {

            bullets.splice(
                i,
                1
            );

        }

    }

}


/* ============================================================
   ENEMY
============================================================ */

function createEnemy() {

    if (
        enemies.length >= 7
    ) {

        return;

    }


    enemies.push({

        x:
            45 +
            Math.random() *
            (
                canvasWidth -
                90
            ),

        y:
            -80,

        width:
            78,

        height:
            95,

        speed:
            2.2 +
            Math.random() *
            1.8

    });

}


function drawEnemy(
    enemy
) {

    if (
        enemyShip.complete &&
        enemyShip.naturalWidth > 0
    ) {

        ctx.drawImage(

            enemyShip,

            enemy.x -
            enemy.width / 2,

            enemy.y -
            enemy.height / 2,

            enemy.width,

            enemy.height

        );

    } else {

        ctx.fillStyle =
            "#ef4444";

        ctx.beginPath();

        ctx.arc(
            enemy.x,
            enemy.y,
            30,
            0,
            Math.PI * 2
        );

        ctx.fill();

    }

}


/* ============================================================
   COLLISION
============================================================ */

function collision(
    a,
    b
) {

    return !(
        a.x +
        a.width / 2 <
        b.x -
        b.width / 2 ||

        a.x -
        a.width / 2 >
        b.x +
        b.width / 2 ||

        a.y +
        a.height / 2 <
        b.y -
        b.height / 2 ||

        a.y -
        a.height / 2 >
        b.y +
        b.height / 2
    );

}


/* ============================================================
   EXPLOSIONS
============================================================ */

function createExplosion(
    x,
    y
) {

    for (
        let i = 0;
        i < 18;
        i++
    ) {

        particles.push({

            x,
            y,

            vx:
                (
                    Math.random() -
                    .5
                ) * 8,

            vy:
                (
                    Math.random() -
                    .5
                ) * 8,

            life:
                30 +
                Math.random() *
                20,

            size:
                2 +
                Math.random() *
                4

        });

    }

}


function updateParticles() {

    for (
        let i =
            particles.length - 1;

        i >= 0;

        i--
    ) {

        const p =
            particles[i];


        p.x +=
            p.vx;

        p.y +=
            p.vy;

        p.vx *=
            .97;

        p.vy *=
            .97;

        p.life--;


        ctx.globalAlpha =
            p.life / 50;


        ctx.fillStyle =
            "#fbbf24";


        ctx.fillRect(
            p.x,
            p.y,
            p.size,
            p.size
        );


        ctx.globalAlpha = 1;


        if (
            p.life <= 0
        ) {

            particles.splice(
                i,
                1
            );

        }

    }

}


/* ============================================================
   ENEMIES
============================================================ */

function updateEnemies() {

    for (
        let i =
            enemies.length - 1;

        i >= 0;

        i--
    ) {

        const enemy =
            enemies[i];


        enemy.y +=
            enemy.speed;


        drawEnemy(
            enemy
        );


        // Player collision

        if (
            collision(
                player,
                enemy
            )
        ) {

            createExplosion(
                enemy.x,
                enemy.y
            );


            enemies.splice(
                i,
                1
            );


            lives--;


            livesEl.textContent =
                "❤️ " +
                lives;


            if (
                lives <= 0
            ) {

                endGame();

            }


            continue;

        }


        // Bullet collision

        let destroyed = false;


        for (
            let b =
                bullets.length - 1;

            b >= 0;

            b--
        ) {

            const bullet =
                bullets[b];


            if (

                bullet.x >
                enemy.x -
                enemy.width / 2 &&

                bullet.x <
                enemy.x +
                enemy.width / 2 &&

                bullet.y >
                enemy.y -
                enemy.height / 2 &&

                bullet.y <
                enemy.y +
                enemy.height / 2

            ) {

                bullets.splice(
                    b,
                    1
                );


                enemies.splice(
                    i,
                    1
                );


                createExplosion(
                    enemy.x,
                    enemy.y
                );


                score +=
                    10;


                destroyed =
                    true;


                break;

            }

        }


        if (
            destroyed
        ) {

            continue;

        }


        if (
            enemy.y >
            canvasHeight +
            120
        ) {

            enemies.splice(
                i,
                1
            );

        }

    }

}


/* ============================================================
   PLAYER MOVEMENT
============================================================ */

function updatePlayer() {

    if (
        keys["ArrowLeft"] ||
        keys["a"] ||
        keys["A"]
    ) {

        player.x -=
            player.speed;

    }


    if (
        keys["ArrowRight"] ||
        keys["d"] ||
        keys["D"]
    ) {

        player.x +=
            player.speed;

    }


    player.x =
        Math.max(
            player.width / 2,
            Math.min(
                canvasWidth -
                player.width / 2,
                player.x
            )
        );

}


/* ============================================================
   GAME LOOP
============================================================ */

function gameLoop() {

    if (
        !running
    ) {

        return;

    }


    ctx.clearRect(
        0,
        0,
        canvasWidth,
        canvasHeight
    );


    updateStars();

    updatePlayer();

    updateBullets();

    updateEnemies();

    updateParticles();

    drawPlayer();


    if (
        fireCooldown > 0
    ) {

        fireCooldown--;

    }


    /*
     * Score slowly increases with time.
     */

    score +=
        .03;


    /*
     * Enemy spawning.
     */

    spawnTimer +=
        16;


    if (
        spawnTimer >=
        spawnDelay
    ) {

        createEnemy();

        spawnTimer =
            0;


        spawnDelay =
            Math.max(
                350,
                850 -
                Math.floor(
                    score * 2
                )
            );

    }


    const shownScore =
        Math.floor(
            score
        );


    const shownEarning =
        Math.floor(
            shownScore /
            10
        );


    scoreEl.textContent =
        shownScore;


    topScoreEl.textContent =
        shownScore;


    earningEl.textContent =
        "₹" +
        shownEarning;


    currentEarningEl.textContent =
        "₹" +
        shownEarning;


    gameEarningElUpdate(
        shownEarning
    );


    animationId =
        requestAnimationFrame(
            gameLoop
        );

}


/* ============================================================
   UPDATE GAME BAR EARNING
============================================================ */

function gameEarningElUpdate(
    amount
) {

    const el =
        document.getElementById(
            "gameEarning"
        );

    if (el) {

        el.textContent =
            "₹" +
            amount;

    }

}


/* ============================================================
   START GAME
============================================================ */

function startGame() {

    cancelAnimationFrame(
        animationId
    );


    resizeGame();

    createStars();


    score =
        0;

    lives =
        3;

    bullets =
        [];

    enemies =
        [];

    particles =
        [];

    spawnTimer =
        0;

    spawnDelay =
        850;

    fireCooldown =
        0;


    player.x =
        canvasWidth /
        2;


    player.y =
        canvasHeight -
        105;


    scoreEl.textContent =
        "0";


    topScoreEl.textContent =
        "0";


    earningEl.textContent =
        "₹0";


    currentEarningEl.textContent =
        "₹0";


    gameEarningElUpdate(0);


    livesEl.textContent =
        "❤️ 3";


    startScreen.classList.add(
        "hidden"
    );


    gameOver.classList.add(
        "hidden"
    );


    running =
        true;


    gameLoop();

}


/* ============================================================
   END GAME
============================================================ */

function endGame() {

    if (
        !running
    ) {

        return;

    }


    running =
        false;


    cancelAnimationFrame(
        animationId
    );


    const finalScore =
        Math.floor(
            score
        );


    const earned =
        Math.floor(
            finalScore /
            10
        );


    finalScoreEl.textContent =
        finalScore;


    finalEarningEl.textContent =
        "₹" +
        earned.toFixed(2);


    finalAvailableEl.textContent =
        availableTopEl.textContent;


    saveStatusEl.textContent =
        "⏳ Saving score...";


    gameOver.classList.remove(
        "hidden"
    );


    saveGame();

}


/* ============================================================
   SAVE GAME
============================================================ */

async function saveGame() {

    const formData =
        new FormData();


    formData.append(
        "save_game",
        "1"
    );


    formData.append(
        "score",
        String(
            Math.floor(
                score
            )
        )
    );


    try {

        const response =
            await fetch(
                "shooter.php",
                {
                    method:
                        "POST",

                    body:
                        formData
                }
            );


        const data =
            await response.json();


        if (
            data.success
        ) {

            const available =
                Number(
                    data.available ||
                    0
                );


            finalEarningEl.textContent =
                "₹" +
                Number(
                    data.earning ||
                    0
                ).toFixed(2);


            finalAvailableEl.textContent =
                "₹" +
                available.toFixed(2);


            availableTopEl.textContent =
                "₹" +
                available.toFixed(2);


            availableEl.textContent =
                "₹" +
                available.toFixed(2);


            saveStatusEl.textContent =
                "🏆 Score saved! Earnings added.";

        } else {

            saveStatusEl.textContent =
                "⚠️ " +
                (
                    data.message ||
                    "Could not save score."
                );

        }

    } catch (error) {

        console.error(
            error
        );


        saveStatusEl.textContent =
            "⚠️ Could not connect to server.";

    }

}


/* ============================================================
   KEYBOARD
============================================================ */

document.addEventListener(
    "keydown",
    function(event) {

        keys[event.key] =
            true;


        if (
            event.code === "Space"
        ) {

            event.preventDefault();

            fireBullet();

        }

    }
);


document.addEventListener(
    "keyup",
    function(event) {

        keys[event.key] =
            false;

    }
);


/* ============================================================
   START / RESTART
============================================================ */

startButton.addEventListener(
    "click",
    startGame
);


restartButton.addEventListener(
    "click",
    startGame
);


/* ============================================================
   MOBILE CONTROLS
============================================================ */

const leftButton =
    document.getElementById(
        "leftButton"
    );


const rightButton =
    document.getElementById(
        "rightButton"
    );


const fireButton =
    document.getElementById(
        "fireButton"
    );


leftButton.addEventListener(
    "pointerdown",
    function() {

        keys["ArrowLeft"] =
            true;

    }
);


leftButton.addEventListener(
    "pointerup",
    function() {

        keys["ArrowLeft"] =
            false;

    }
);


leftButton.addEventListener(
    "pointerleave",
    function() {

        keys["ArrowLeft"] =
            false;

    }
);


rightButton.addEventListener(
    "pointerdown",
    function() {

        keys["ArrowRight"] =
            true;

    }
);


rightButton.addEventListener(
    "pointerup",
    function() {

        keys["ArrowRight"] =
            false;

    }
);


rightButton.addEventListener(
    "pointerleave",
    function() {

        keys["ArrowRight"] =
            false;

    }
);


fireButton.addEventListener(
    "pointerdown",
    function() {

        fireBullet();

    }
);


/* ============================================================
   INITIALIZE
============================================================ */

window.addEventListener(
    "load",
    function() {

        resizeGame();

        createStars();

    }
);

</script>


<?php require "footer.php"; ?>
```

This gives you the exact structure you showed:

```text
ConnectHub sidebar
        │
        ▼
┌───────────────────────────────────────────────────────┐
│ 🚀 Space Shooter                          SCORE 0    │  ← title bar
├───────────────────────────────────────────────────────┤
│ 💰 Available Earnings                       ₹82.00  │  ← green bar
├───────────────────────────────────────────────────────┤
│ 🎯 Current Game Earnings                     ₹0      │  ← orange bar
├───────────────────────────────────────────────────────┤
│                                                       │
│       ┌─────────────────────────────────────┐         │
│       │ SCORE | EARN | ❤️ LIVES | AVAILABLE │         │
│       │       space_game_bar.jpg            │         │
│       └─────────────────────────────────────┘         │
│                                                       │
│                 space_full_bg.jpg                     │
│                                                       │
│                     👾 enemy_ship.png                 │
│                                                       │
│                                                       │
│                     🚀 player_ship.png                │
│                                                       │
│                                                       │
└───────────────────────────────────────────────────────┘
```

Make sure the filenames are **exactly**:

```text
space_full_bg.jpg
space_game_bar.jpg
player_ship.png
enemy_ship.png
```

The important part is that `space_game_bar.jpg` is **inside the gameplay area**, while `space_full_bg.jpg` fills the entire gameplay area behind it.
