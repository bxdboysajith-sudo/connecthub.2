```php
<?php
// ============================================================
// CONNECTHUB - SNAKE GAME
// START BUTTON + LARGE GAMEPLAY BAR
// GAME EARNINGS + BANKING
// ============================================================

require "config.php";

login_required();

$uid = (int)($_SESSION["user_id"] ?? 0);

if ($uid <= 0) {
    header("Location: login.php");
    exit;
}


// ============================================================
// IMAGE SETTINGS
// ============================================================

$fullBackground = "uploads/snake-bg.jpg";
$gameplayBar    = "uploads/snake-bar.jpg";


// ============================================================
// SAVE GAME EARNING
// ============================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["save_game_earning"])
) {

    header(
        "Content-Type: application/json; charset=UTF-8"
    );

    $score =
        (int)($_POST["score"] ?? 0);


    if ($score < 0) {

        echo json_encode([
            "success" => false,
            "message" => "Invalid score."
        ]);

        exit;
    }


    // Every 10 points = ₹1

    $amount =
        floor(
            $score / 10
        );


    if ($amount <= 0) {

        // Still return current total

        $stmt = $conn->prepare("
            SELECT
                COALESCE(
                    SUM(amount),
                    0
                ) AS total
            FROM game_earnings
            WHERE user_id = ?
            AND status = 'available'
        ");


        $stmt->bind_param(
            "i",
            $uid
        );


        $stmt->execute();


        $row =
            $stmt
                ->get_result()
                ->fetch_assoc();


        $stmt->close();


        echo json_encode([
            "success" => true,
            "amount" => 0,
            "available" =>
                (float)($row["total"] ?? 0),
            "message" =>
                "No earnings for this score."
        ]);

        exit;
    }


    try {

        // ====================================================
        // CHECK TABLE
        // ====================================================

        $check =
            $conn->query(
                "SHOW TABLES LIKE 'game_earnings'"
            );


        if (
            !$check ||
            $check->num_rows === 0
        ) {

            throw new Exception(
                "game_earnings table does not exist."
            );
        }


        // ====================================================
        // INSERT EARNING
        // ====================================================

        $status =
            "available";


        $stmt =
            $conn->prepare("
                INSERT INTO game_earnings
                (
                    user_id,
                    amount,
                    status
                )
                VALUES (?, ?, ?)
            ");


        if (!$stmt) {

            throw new Exception(
                "Database prepare failed: " .
                $conn->error
            );
        }


        $stmt->bind_param(
            "ids",
            $uid,
            $amount,
            $status
        );


        if (
            !$stmt->execute()
        ) {

            $error =
                $stmt->error;

            $stmt->close();


            throw new Exception(
                "Could not save earnings: " .
                $error
            );
        }


        $stmt->close();


        // ====================================================
        // GET UPDATED EARNINGS
        // ====================================================

        $stmt =
            $conn->prepare("
                SELECT
                    COALESCE(
                        SUM(amount),
                        0
                    ) AS total
                FROM game_earnings
                WHERE user_id = ?
                AND status = 'available'
            ");


        if (!$stmt) {

            throw new Exception(
                "Could not read game earnings."
            );
        }


        $stmt->bind_param(
            "i",
            $uid
        );


        $stmt->execute();


        $row =
            $stmt
                ->get_result()
                ->fetch_assoc();


        $stmt->close();


        $available =
            (float)(
                $row["total"] ?? 0
            );


        echo json_encode([
            "success" => true,
            "amount" => $amount,
            "available" => $available,
            "message" =>
                "Game earnings saved successfully."
        ]);

        exit;

    } catch (Throwable $e) {

        echo json_encode([
            "success" => false,
            "message" =>
                "Server error: " .
                $e->getMessage()
        ]);

        exit;
    }
}


// ============================================================
// CURRENT AVAILABLE GAME EARNINGS
// ============================================================

$gameEarnings = 0.00;


$stmt =
    $conn->prepare("
        SELECT
            COALESCE(
                SUM(amount),
                0
            ) AS total
        FROM game_earnings
        WHERE user_id = ?
        AND status = 'available'
    ");


if ($stmt) {

    $stmt->bind_param(
        "i",
        $uid
    );


    $stmt->execute();


    $row =
        $stmt
            ->get_result()
            ->fetch_assoc();


    $gameEarnings =
        (float)(
            $row["total"] ?? 0
        );


    $stmt->close();
}

?>


<?php require "header.php"; ?>


<!-- ============================================================
     FULL SNAKE PAGE
============================================================ -->

<div
    class="snake-page"
    style="
        background-image:
        url('<?= htmlspecialchars(
            $fullBackground,
            ENT_QUOTES,
            'UTF-8'
        ) ?>');
    "
>


    <!-- ========================================================
         BACKGROUND OVERLAY
    ========================================================= -->

    <div class="snake-background-overlay"></div>


    <div class="snake-content">


        <!-- ====================================================
             LARGE GAMEPLAY BAR
        ==================================================== -->

        <div class="snake-game-bar">

            <img
                src="<?= htmlspecialchars(
                    $gameplayBar,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                alt="ConnectHub Snake"
            >


            <div class="bar-dark"></div>


            <div class="bar-overlay">

                <div class="bar-title">
                    🐍 CONNECTHUB SNAKE
                </div>

                <div class="bar-subtitle">
                    EAT • SCORE • EARN
                </div>

            </div>

        </div>


        <!-- ====================================================
             GAME HEADER
        ==================================================== -->

        <div class="game-header">

            <div>

                <div class="game-title">
                    🐍 Snake Game
                </div>

                <div class="game-subtitle">
                    Eat the food, grow the snake and earn money.
                </div>

            </div>


            <div class="score-box">

                <span>
                    SCORE
                </span>

                <strong id="score">
                    0
                </strong>

            </div>

        </div>


        <!-- ====================================================
             AVAILABLE EARNINGS
        ==================================================== -->

        <div class="earnings-box">

            <div class="earning-left">

                <span class="money-icon">
                    💰
                </span>


                <div>

                    <strong>
                        Available Earnings
                    </strong>

                    <span
                        id="availableEarnings"
                        class="available-money"
                    >
                        ₹<?= number_format(
                            $gameEarnings,
                            2
                        ) ?>
                    </span>

                </div>

            </div>


            <div class="earning-rule">
                Every 10 points = ₹1
            </div>

        </div>


        <!-- ====================================================
             CURRENT EARNING
        ==================================================== -->

        <div class="current-earning-box">

            <strong>
                🎯 Current Game Earnings
            </strong>

            <span id="currentEarnings">
                ₹0
            </span>

        </div>


        <!-- ====================================================
             GAME AREA
        ==================================================== -->

        <div class="snake-game-card">


            <!-- GAME BACKGROUND -->

            <div class="game-background">

                <img
                    src="<?= htmlspecialchars(
                        $gameplayBar,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    alt=""
                >

            </div>


            <!-- CANVAS -->

            <canvas
                id="snakeCanvas"
                width="600"
                height="600"
            ></canvas>


            <!-- =================================================
                 START SCREEN
            ================================================== -->

            <div
                id="startScreen"
                class="start-screen"
            >

                <div class="start-card">

                    <div class="start-icon">
                        🐍
                    </div>

                    <div class="start-label">
                        CONNECTHUB ARCADE
                    </div>

                    <h2>
                        SNAKE GAME
                    </h2>

                    <p>
                        Eat the food, grow your snake,
                        increase your score and earn money.
                    </p>


                    <div class="start-rules">

                        <div>
                            🍎 Eat food = 10 points
                        </div>

                        <div>
                            💰 Every 10 points = ₹1
                        </div>

                        <div>
                            ⌨️ Use Arrow Keys or WASD
                        </div>

                        <div>
                            💥 Don't hit the wall or yourself
                        </div>

                    </div>


                    <button
                        type="button"
                        id="startGameButton"
                        class="start-game-button"
                    >
                        🐍 START GAME
                    </button>

                </div>

            </div>


            <!-- =================================================
                 GAME OVER
            ================================================== -->

            <div
                id="gameOverScreen"
                class="game-over-screen"
            >

                <div class="game-over-icon">
                    💀
                </div>

                <h2>
                    GAME OVER
                </h2>


                <div class="final-score-label">
                    YOUR SCORE
                </div>


                <div
                    id="finalScore"
                    class="final-score"
                >
                    0
                </div>


                <div class="earned-result">

                    <span>
                        💰
                    </span>

                    <span>
                        YOU EARNED
                    </span>

                    <strong id="earnedAmount">
                        ₹0
                    </strong>

                </div>


                <button
                    type="button"
                    id="playAgainButton"
                    class="play-again-button"
                >
                    🔄 PLAY AGAIN
                </button>


                <a
                    href="bank.php?from=game"
                    class="bank-button"
                >
                    🏦 GO TO BANKING
                </a>


                <div
                    id="saveStatus"
                    class="save-status"
                >
                </div>

            </div>

        </div>


        <!-- ====================================================
             CONTROLS
        ==================================================== -->

        <div class="controls">

            <button
                type="button"
                class="control-button up"
                data-direction="up"
            >
                ⬆️
            </button>


            <div class="control-row">

                <button
                    type="button"
                    class="control-button"
                    data-direction="left"
                >
                    ⬅️
                </button>


                <button
                    type="button"
                    class="control-button"
                    data-direction="down"
                >
                    ⬇️
                </button>


                <button
                    type="button"
                    class="control-button"
                    data-direction="right"
                >
                    ➡️
                </button>

            </div>

        </div>


        <!-- ====================================================
             HOW TO PLAY
        ==================================================== -->

        <div class="how-to-play">

            <h2>
                🎮 How to Play
            </h2>


            <div class="rules">

                <div>
                    ⌨️
                    <strong>Move</strong>
                    using Arrow Keys or WASD.
                </div>


                <div>
                    🍎
                    <strong>Eat the red food</strong>
                    to increase your score.
                </div>


                <div>
                    🐍
                    <strong>Grow your snake</strong>
                    every time you eat.
                </div>


                <div>
                    💰
                    <strong>Every 10 points = ₹1</strong>
                    game earnings.
                </div>


                <div>
                    🏦
                    Your earnings are automatically saved.
                </div>

            </div>

        </div>


    </div>

</div>


<style>

/* ==========================================================
   PAGE
========================================================== */

.snake-page {

    position: relative;

    min-height: 100vh;

    padding: 30px 20px 70px;

    box-sizing: border-box;

    background-size: cover;

    background-position: center;

    background-repeat: no-repeat;

    background-attachment: fixed;
}


/* ==========================================================
   OVERLAY
========================================================== */

.snake-background-overlay {

    position: fixed;

    inset: 0;

    z-index: 0;

    pointer-events: none;

    background:
        rgba(3,8,20,.60);
}


/* ==========================================================
   CONTENT
========================================================== */

.snake-content {

    position: relative;

    z-index: 2;

    width: 100%;

    max-width: 1000px;

    margin: auto;
}


/* ==========================================================
   GAMEPLAY BAR
========================================================== */

.snake-game-bar {

    position: relative;

    width: 100%;

    height: 230px;

    overflow: hidden;

    border-radius: 28px;

    margin-bottom: 20px;

    border:
        2px solid
        rgba(255,255,255,.25);

    box-shadow:
        0 20px 60px
        rgba(0,0,0,.55);
}


.snake-game-bar img {

    position: absolute;

    inset: 0;

    width: 100%;

    height: 100%;

    object-fit: cover;

    object-position: center;
}


.bar-dark {

    position: absolute;

    inset: 0;

    background:
        linear-gradient(
            90deg,
            rgba(0,0,0,.75),
            rgba(0,0,0,.15),
            rgba(0,0,0,.70)
        );

    z-index: 1;
}


.bar-overlay {

    position: absolute;

    inset: 0;

    z-index: 2;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    text-align: center;

    color: white;

    text-shadow:
        0 5px 15px
        rgba(0,0,0,.9);
}


.bar-title {

    font-size: 48px;

    font-weight: 1000;

    letter-spacing: 2px;
}


.bar-subtitle {

    margin-top: 12px;

    font-size: 17px;

    letter-spacing: 8px;

    font-weight: 800;
}


/* ==========================================================
   GAME HEADER
========================================================== */

.game-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 20px;

    margin-bottom: 12px;

    border-radius: 18px;

    color: white;

    background:
        rgba(8,18,35,.90);

    border:
        1px solid
        rgba(255,255,255,.18);

    backdrop-filter:
        blur(12px);
}


.game-title {

    font-size: 30px;

    font-weight: 900;
}


.game-subtitle {

    margin-top: 5px;

    color: #cbd5e1;

    font-size: 14px;
}


/* ==========================================================
   SCORE
========================================================== */

.score-box {

    min-width: 110px;

    padding: 12px;

    border-radius: 15px;

    text-align: center;

    background:
        rgba(79,70,229,.30);

    border:
        1px solid
        rgba(129,140,248,.55);
}


.score-box span {

    display: block;

    font-size: 12px;

    color: #cbd5e1;

    letter-spacing: 1px;
}


.score-box strong {

    display: block;

    margin-top: 2px;

    font-size: 32px;

    color: #a5b4fc;
}


/* ==========================================================
   EARNINGS
========================================================== */

.earnings-box {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 16px 20px;

    margin-bottom: 10px;

    border-radius: 15px;

    color: white;

    background:
        rgba(4,45,28,.90);

    border:
        1px solid
        rgba(52,211,153,.5);

    backdrop-filter:
        blur(10px);
}


.earning-left {

    display: flex;

    align-items: center;

    gap: 12px;
}


.money-icon {

    font-size: 28px;
}


.earning-left strong {

    display: block;

    font-size: 14px;
}


.available-money {

    display: block;

    margin-top: 2px;

    font-size: 22px;

    font-weight: 900;

    color: #34d399;
}


.earning-rule {

    font-size: 14px;

    color: #86efac;
}


/* ==========================================================
   CURRENT EARNINGS
========================================================== */

.current-earning-box {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 15px 20px;

    margin-bottom: 18px;

    border-radius: 15px;

    color: white;

    background:
        rgba(70,35,5,.90);

    border:
        1px solid
        rgba(251,146,60,.55);
}


.current-earning-box strong {

    font-size: 15px;
}


.current-earning-box span {

    font-size: 22px;

    font-weight: 900;

    color: #fb923c;
}


/* ==========================================================
   GAME
========================================================== */

.snake-game-card {

    position: relative;

    width: 600px;

    max-width: 100%;

    aspect-ratio: 1 / 1;

    margin: 0 auto;

    overflow: hidden;

    border-radius: 28px;

    border: 4px solid
        rgba(255,255,255,.35);

    background:
        #06101d;

    box-shadow:
        0 25px 70px
        rgba(0,0,0,.65);
}


.game-background {

    position: absolute;

    inset: 0;

    z-index: 1;
}


.game-background img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    object-position: center;

    display: block;
}


/* ==========================================================
   CANVAS
========================================================== */

#snakeCanvas {

    position: absolute;

    inset: 0;

    z-index: 5;

    width: 100%;

    height: 100%;

    display: block;

    background: transparent;
}


/* ==========================================================
   START SCREEN
========================================================== */

.start-screen {

    position: absolute;

    inset: 0;

    z-index: 80;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 20px;

    background:
        rgba(2,6,18,.82);

    backdrop-filter:
        blur(7px);
}


.start-card {

    width:
        min(440px,92%);

    padding: 28px;

    border-radius: 22px;

    text-align: center;

    color: white;

    background:
        rgba(4,10,25,.96);

    border:
        1px solid
        rgba(255,255,255,.18);

    box-shadow:
        0 25px 60px
        rgba(0,0,0,.55);
}


.start-icon {

    font-size: 60px;

    margin-bottom: 5px;
}


.start-label {

    color: #34d399;

    font-size: 9px;

    font-weight: 900;

    letter-spacing: 3px;
}


.start-card h2 {

    margin: 5px 0 8px;

    font-size: 34px;

    background:
        linear-gradient(
            90deg,
            #4ade80,
            #22c55e,
            #86efac
        );

    -webkit-background-clip:
        text;

    color:
        transparent;
}


.start-card p {

    margin: 0 0 17px;

    color:
        #cbd5e1;

    font-size:
        13px;

    line-height:
        1.6;
}


.start-rules {

    display:
        grid;

    grid-template-columns:
        1fr 1fr;

    gap:
        7px;

    margin-bottom:
        18px;
}


.start-rules div {

    padding:
        9px 6px;

    border-radius:
        9px;

    background:
        rgba(255,255,255,.06);

    color:
        #dbeafe;

    font-size:
        10px;
}


.start-game-button {

    width:
        100%;

    padding:
        15px;

    border:
        none;

    border-radius:
        12px;

    background:
        linear-gradient(
            135deg,
            #16a34a,
            #22c55e
        );

    color:
        white;

    font-size:
        16px;

    font-weight:
        900;

    cursor:
        pointer;

    box-shadow:
        0 10px 25px
        rgba(34,197,94,.28);
}


.start-game-button:hover {

    transform:
        translateY(-2px);
}


/* ==========================================================
   GAME OVER
========================================================== */

.game-over-screen {

    position: absolute;

    inset: 0;

    z-index: 90;

    display: none;

    box-sizing: border-box;

    padding: 45px 20px 20px;

    text-align: center;

    color: white;

    background:
        rgba(2,6,18,.94);

    backdrop-filter:
        blur(10px);
}


.game-over-icon {

    font-size: 65px;
}


.game-over-screen h2 {

    margin:
        5px 0 10px;

    font-size:
        35px;

    font-weight:
        900;
}


.final-score-label {

    color:
        #94a3b8;

    font-size:
        14px;
}


.final-score {

    margin:
        3px 0 18px;

    font-size:
        60px;

    font-weight:
        900;

    color:
        #a5b4fc;
}


.earned-result {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        9px;

    padding:
        13px 20px;

    margin-bottom:
        15px;

    border-radius:
        14px;

    background:
        rgba(16,185,129,.13);

    border:
        1px solid
        #34d399;
}


.earned-result strong {

    color:
        #34d399;

    font-size:
        23px;
}


.play-again-button {

    display:
        block;

    width:
        210px;

    margin:
        5px auto 10px;

    padding:
        14px;

    border:
        none;

    border-radius:
        13px;

    background:
        linear-gradient(
            135deg,
            #6366f1,
            #8b5cf6
        );

    color:
        white;

    font-size:
        15px;

    font-weight:
        900;

    cursor:
        pointer;
}


.bank-button {

    display:
        block;

    width:
        210px;

    margin:
        0 auto 10px;

    padding:
        12px;

    border-radius:
        11px;

    background:
        #16a34a;

    color:
        white;

    text-decoration:
        none;

    font-weight:
        800;

    font-size:
        13px;
}


.save-status {

    min-height:
        20px;

    color:
        #86efac;

    font-size:
        12px;
}


/* ==========================================================
   CONTROLS
========================================================== */

.controls {

    width:
        250px;

    margin:
        28px auto;

    text-align:
        center;
}


.control-row {

    display:
        flex;

    justify-content:
        center;

    gap:
        10px;
}


.control-button {

    width:
        65px;

    height:
        65px;

    border:
        1px solid
        rgba(255,255,255,.25);

    border-radius:
        16px;

    background:
        rgba(15,23,42,.95);

    color:
        white;

    font-size:
        25px;

    cursor:
        pointer;

    box-shadow:
        0 8px 20px
        rgba(0,0,0,.4);
}


.control-button:hover {

    background:
        rgba(79,70,229,.95);
}


.control-button:active {

    transform:
        scale(.90);
}


.up {

    margin-bottom:
        10px;
}


/* ==========================================================
   HOW TO PLAY
========================================================== */

.how-to-play {

    margin-top:
        25px;

    padding:
        25px;

    border-radius:
        20px;

    color:
        white;

    background:
        rgba(8,18,35,.90);

    border:
        1px solid
        rgba(255,255,255,.16);

    backdrop-filter:
        blur(12px);
}


.how-to-play h2 {

    margin-top:
        0;

    font-size:
        23px;
}


.rules {

    display:
        grid;

    gap:
        10px;
}


.rules div {

    padding:
        13px;

    border-radius:
        11px;

    background:
        rgba(255,255,255,.06);

    color:
        #dbeafe;
}


/* ==========================================================
   MOBILE
========================================================== */

@media(max-width:700px) {

    .snake-page {

        padding:
            15px 10px 45px;

        background-attachment:
            scroll;
    }


    .snake-game-bar {

        height:
            170px;

        border-radius:
            20px;
    }


    .bar-title {

        font-size:
            31px;
    }


    .bar-subtitle {

        font-size:
            11px;

        letter-spacing:
            4px;
    }


    .game-header {

        padding:
            15px;
    }


    .game-title {

        font-size:
            22px;
    }


    .game-subtitle {

        font-size:
            12px;
    }


    .score-box {

        min-width:
            75px;
    }


    .score-box strong {

        font-size:
            25px;
    }


    .earnings-box,
    .current-earning-box {

        flex-direction:
            column;

        align-items:
            flex-start;

        gap:
            8px;
    }


    .snake-game-card {

        width:
            100%;

        border-radius:
            20px;

        border-width:
            3px;
    }


    .start-card {

        padding:
            22px;
    }


    .start-card h2 {

        font-size:
            28px;
    }


    .start-rules {

        grid-template-columns:
            1fr;
    }


    .control-button {

        width:
            58px;

        height:
            58px;
    }

}


/* ==========================================================
   SMALL PHONE
========================================================== */

@media(max-width:400px) {

    .bar-title {

        font-size:
            25px;
    }


    .bar-subtitle {

        font-size:
            9px;

        letter-spacing:
            3px;
    }


    .game-title {

        font-size:
            20px;
    }

}

</style>


<script>

// ============================================================
// CONNECTHUB SNAKE
// START BUTTON VERSION
// ============================================================


// ============================================================
// CANVAS
// ============================================================

const canvas =
    document.getElementById(
        "snakeCanvas"
    );

const ctx =
    canvas.getContext(
        "2d"
    );


// ============================================================
// ELEMENTS
// ============================================================

const scoreElement =
    document.getElementById(
        "score"
    );


const currentEarningsElement =
    document.getElementById(
        "currentEarnings"
    );


const availableEarningsElement =
    document.getElementById(
        "availableEarnings"
    );


const startScreen =
    document.getElementById(
        "startScreen"
    );


const startGameButton =
    document.getElementById(
        "startGameButton"
    );


const gameOverScreen =
    document.getElementById(
        "gameOverScreen"
    );


const finalScoreElement =
    document.getElementById(
        "finalScore"
    );


const earnedAmountElement =
    document.getElementById(
        "earnedAmount"
    );


const saveStatusElement =
    document.getElementById(
        "saveStatus"
    );


const playAgainButton =
    document.getElementById(
        "playAgainButton"
    );


// ============================================================
// GAME SETTINGS
// ============================================================

const GRID_SIZE =
    20;


const TILE_SIZE =
    canvas.width /
    GRID_SIZE;


// ============================================================
// GAME VARIABLES
// ============================================================

let snake = [];


let food = {
    x: 10,
    y: 10
};


let direction = {
    x: 1,
    y: 0
};


let nextDirection = {
    x: 1,
    y: 0
};


let score = 0;


let gameRunning = false;


let gameLoop = null;


let saving = false;


// ============================================================
// PREPARE GAME
// Does NOT start the game.
// ============================================================

function prepareGame() {

    snake = [

        {
            x: 10,
            y: 10
        },

        {
            x: 9,
            y: 10
        },

        {
            x: 8,
            y: 10
        },

        {
            x: 7,
            y: 10
        }

    ];


    direction = {
        x: 1,
        y: 0
    };


    nextDirection = {
        x: 1,
        y: 0
    };


    score = 0;


    scoreElement.textContent =
        "0";


    currentEarningsElement.textContent =
        "₹0";


    createFood();


    drawGame();


    gameRunning = false;


    clearInterval(
        gameLoop
    );


    gameOverScreen.style.display =
        "none";


    startScreen.style.display =
        "flex";

}


// ============================================================
// ACTUALLY START GAME
// ============================================================

function startGame() {

    gameRunning = true;


    startScreen.style.display =
        "none";


    gameOverScreen.style.display =
        "none";


    score = 0;


    snake = [

        {
            x: 10,
            y: 10
        },

        {
            x: 9,
            y: 10
        },

        {
            x: 8,
            y: 10
        },

        {
            x: 7,
            y: 10
        }

    ];


    direction = {
        x: 1,
        y: 0
    };


    nextDirection = {
        x: 1,
        y: 0
    };


    scoreElement.textContent =
        "0";


    currentEarningsElement.textContent =
        "₹0";


    saveStatusElement.textContent =
        "";


    createFood();


    clearInterval(
        gameLoop
    );


    gameLoop =
        setInterval(
            updateGame,
            130
        );


    drawGame();

}


// ============================================================
// FOOD
// ============================================================

function createFood() {

    let valid = false;


    while (!valid) {

        food = {

            x:
                Math.floor(
                    Math.random() *
                    GRID_SIZE
                ),

            y:
                Math.floor(
                    Math.random() *
                    GRID_SIZE
                )

        };


        valid =
            !snake.some(
                part =>
                    part.x === food.x &&
                    part.y === food.y
            );

    }

}


// ============================================================
// UPDATE
// ============================================================

function updateGame() {

    if (!gameRunning) {

        return;

    }


    direction =
        nextDirection;


    const newHead = {

        x:
            snake[0].x +
            direction.x,

        y:
            snake[0].y +
            direction.y

    };


    // ========================================================
    // WALL
    // ========================================================

    if (

        newHead.x < 0 ||
        newHead.x >= GRID_SIZE ||
        newHead.y < 0 ||
        newHead.y >= GRID_SIZE

    ) {

        endGame();

        return;

    }


    // ========================================================
    // BODY
    // ========================================================

    for (
        let i = 0;
        i < snake.length;
        i++
    ) {

        if (

            newHead.x ===
                snake[i].x &&

            newHead.y ===
                snake[i].y

        ) {

            endGame();

            return;

        }

    }


    snake.unshift(
        newHead
    );


    // ========================================================
    // FOOD
    // ========================================================

    if (

        newHead.x ===
            food.x &&

        newHead.y ===
            food.y

    ) {

        score += 10;


        scoreElement.textContent =
            score;


        const earnings =
            Math.floor(
                score / 10
            );


        currentEarningsElement.textContent =
            "₹" +
            earnings;


        createFood();

    }

    else {

        snake.pop();

    }


    drawGame();

}


// ============================================================
// DRAW GAME
// ============================================================

function drawGame() {

    ctx.clearRect(
        0,
        0,
        canvas.width,
        canvas.height
    );


    drawGrid();

    drawFood();

    drawSnake();

}


// ============================================================
// GRID
// ============================================================

function drawGrid() {

    ctx.save();


    ctx.strokeStyle =
        "rgba(255,255,255,.08)";


    ctx.lineWidth =
        1;


    for (
        let i = 0;
        i <= GRID_SIZE;
        i++
    ) {

        const position =
            i * TILE_SIZE;


        ctx.beginPath();

        ctx.moveTo(
            position,
            0
        );

        ctx.lineTo(
            position,
            canvas.height
        );

        ctx.stroke();


        ctx.beginPath();

        ctx.moveTo(
            0,
            position
        );

        ctx.lineTo(
            canvas.width,
            position
        );

        ctx.stroke();

    }


    ctx.restore();

}


// ============================================================
// FOOD
// ============================================================

function drawFood() {

    const centerX =
        food.x *
            TILE_SIZE +
        TILE_SIZE / 2;


    const centerY =
        food.y *
            TILE_SIZE +
        TILE_SIZE / 2;


    ctx.save();


    ctx.shadowColor =
        "#ef4444";


    ctx.shadowBlur =
        20;


    ctx.fillStyle =
        "#ef4444";


    ctx.beginPath();


    ctx.arc(
        centerX,
        centerY,
        TILE_SIZE * .30,
        0,
        Math.PI * 2
    );


    ctx.fill();


    ctx.restore();


    // Highlight

    ctx.fillStyle =
        "#ffffff";


    ctx.beginPath();


    ctx.arc(
        centerX - 5,
        centerY - 5,
        3,
        0,
        Math.PI * 2
    );


    ctx.fill();


    // Leaf

    ctx.fillStyle =
        "#22c55e";


    ctx.beginPath();


    ctx.ellipse(
        centerX + 6,
        centerY - 13,
        5,
        3,
        -.5,
        0,
        Math.PI * 2
    );


    ctx.fill();

}


// ============================================================
// SNAKE
// ============================================================

function drawSnake() {

    for (
        let i =
            snake.length - 1;

        i >= 0;

        i--
    ) {

        const part =
            snake[i];


        const x =
            part.x *
            TILE_SIZE;


        const y =
            part.y *
            TILE_SIZE;


        if (i === 0) {

            drawSnakeHead(
                x,
                y
            );

        }

        else {

            drawSnakeBody(
                x,
                y
            );

        }

    }

}


// ============================================================
// BODY
// ============================================================

function drawSnakeBody(
    x,
    y
) {

    const padding = 4;


    ctx.save();


    ctx.shadowColor =
        "rgba(0,0,0,.7)";


    ctx.shadowBlur =
        10;


    const gradient =
        ctx.createLinearGradient(
            x,
            y,
            x + TILE_SIZE,
            y + TILE_SIZE
        );


    gradient.addColorStop(
        0,
        "#4ade80"
    );


    gradient.addColorStop(
        1,
        "#16a34a"
    );


    ctx.fillStyle =
        gradient;


    ctx.beginPath();


    ctx.roundRect(
        x + padding,
        y + padding,
        TILE_SIZE -
            padding * 2,
        TILE_SIZE -
            padding * 2,
        7
    );


    ctx.fill();


    ctx.restore();


    // Shine

    ctx.fillStyle =
        "rgba(255,255,255,.25)";


    ctx.beginPath();


    ctx.roundRect(
        x + 7,
        y + 6,
        TILE_SIZE - 14,
        4,
        2
    );


    ctx.fill();

}


// ============================================================
// HEAD
// ============================================================

function drawSnakeHead(
    x,
    y
) {

    const centerX =
        x +
        TILE_SIZE / 2;


    const centerY =
        y +
        TILE_SIZE / 2;


    ctx.save();


    ctx.shadowColor =
        "rgba(0,0,0,.8)";


    ctx.shadowBlur =
        15;


    const gradient =
        ctx.createLinearGradient(
            x,
            y,
            x + TILE_SIZE,
            y + TILE_SIZE
        );


    gradient.addColorStop(
        0,
        "#86efac"
    );


    gradient.addColorStop(
        .5,
        "#22c55e"
    );


    gradient.addColorStop(
        1,
        "#15803d"
    );


    ctx.fillStyle =
        gradient;


    ctx.beginPath();


    ctx.roundRect(
        x + 2,
        y + 2,
        TILE_SIZE - 4,
        TILE_SIZE - 4,
        9
    );


    ctx.fill();


    ctx.restore();


    // ========================================================
    // EYES
    // ========================================================

    let eye1X;
    let eye1Y;
    let eye2X;
    let eye2Y;


    if (
        direction.x === 1
    ) {

        eye1X =
            centerX + 5;

        eye2X =
            centerX + 5;

        eye1Y =
            centerY - 6;

        eye2Y =
            centerY + 6;

    }

    else if (
        direction.x === -1
    ) {

        eye1X =
            centerX - 5;

        eye2X =
            centerX - 5;

        eye1Y =
            centerY - 6;

        eye2Y =
            centerY + 6;

    }

    else if (
        direction.y === -1
    ) {

        eye1X =
            centerX - 6;

        eye2X =
            centerX + 6;

        eye1Y =
            centerY - 5;

        eye2Y =
            centerY - 5;

    }

    else {

        eye1X =
            centerX - 6;

        eye2X =
            centerX + 6;

        eye1Y =
            centerY + 5;

        eye2Y =
            centerY + 5;

    }


    ctx.fillStyle =
        "#ffffff";


    ctx.beginPath();

    ctx.arc(
        eye1X,
        eye1Y,
        4,
        0,
        Math.PI * 2
    );

    ctx.fill();


    ctx.beginPath();

    ctx.arc(
        eye2X,
        eye2Y,
        4,
        0,
        Math.PI * 2
    );

    ctx.fill();


    ctx.fillStyle =
        "#111827";


    ctx.beginPath();

    ctx.arc(
        eye1X,
        eye1Y,
        2,
        0,
        Math.PI * 2
    );

    ctx.fill();


    ctx.beginPath();

    ctx.arc(
        eye2X,
        eye2Y,
        2,
        0,
        Math.PI * 2
    );

    ctx.fill();


    // ========================================================
    // TONGUE
    // ========================================================

    if (
        direction.x === 1
    ) {

        drawTongue(
            centerX + 13,
            centerY
        );

    }

    else if (
        direction.x === -1
    ) {

        drawTongue(
            centerX - 13,
            centerY
        );

    }

    else if (
        direction.y === -1
    ) {

        drawTongue(
            centerX,
            centerY - 13
        );

    }

    else {

        drawTongue(
            centerX,
            centerY + 13
        );

    }

}


// ============================================================
// TONGUE
// ============================================================

function drawTongue(
    x,
    y
) {

    ctx.strokeStyle =
        "#ef4444";


    ctx.lineWidth =
        2;


    if (
        direction.x !== 0
    ) {

        ctx.beginPath();


        ctx.moveTo(
            x,
            y
        );


        ctx.lineTo(
            x + direction.x * 7,
            y
        );


        ctx.stroke();


        ctx.beginPath();


        ctx.moveTo(
            x +
                direction.x * 7,
            y
        );


        ctx.lineTo(
            x +
                direction.x * 11,
            y - 4
        );


        ctx.moveTo(
            x +
                direction.x * 7,
            y
        );


        ctx.lineTo(
            x +
                direction.x * 11,
            y + 4
        );


        ctx.stroke();

    }

    else {

        ctx.beginPath();


        ctx.moveTo(
            x,
            y
        );


        ctx.lineTo(
            x,
            y +
                direction.y * 7
        );


        ctx.stroke();


        ctx.beginPath();


        ctx.moveTo(
            x,
            y +
                direction.y * 7
        );


        ctx.lineTo(
            x - 4,
            y +
                direction.y * 11
        );


        ctx.moveTo(
            x,
            y +
                direction.y * 7
        );


        ctx.lineTo(
            x + 4,
            y +
                direction.y * 11
        );


        ctx.stroke();

    }

}


// ============================================================
// GAME OVER
// ============================================================

function endGame() {

    if (!gameRunning) {
        return;
    }


    gameRunning = false;


    clearInterval(
        gameLoop
    );


    finalScoreElement.textContent =
        score;


    const earned =
        Math.floor(
            score / 10
        );


    earnedAmountElement.textContent =
        "₹" +
        earned;


    gameOverScreen.style.display =
        "block";


    if (
        earned > 0
    ) {

        saveGameEarning();

    }

    else {

        saveStatusElement.textContent =
            "No earnings this round.";

    }

}


// ============================================================
// SAVE EARNING
// ============================================================

async function saveGameEarning() {

    if (saving) {
        return;
    }


    saving = true;


    saveStatusElement.textContent =
        "⏳ Saving Earnings...";


    try {

        const formData =
            new FormData();


        formData.append(
            "save_game_earning",
            "1"
        );


        formData.append(
            "score",
            score
        );


        const response =
            await fetch(
                "snake.php",
                {
                    method: "POST",
                    body: formData,
                    credentials: "same-origin"
                }
            );


        const text =
            await response.text();


        let data;


        try {

            data =
                JSON.parse(
                    text
                );

        } catch (error) {

            console.error(
                "Server response:",
                text
            );

            throw new Error(
                "Invalid server response."
            );

        }


        if (!data.success) {

            throw new Error(
                data.message ||
                "Could not save earnings."
            );

        }


        availableEarningsElement.textContent =
            "₹" +
            Number(
                data.available
            ).toFixed(2);


        saveStatusElement.textContent =
            "✅ Earnings saved successfully!";

    }

    catch (error) {

        console.error(
            error
        );


        saveStatusElement.textContent =
            "⚠️ " +
            error.message;

    }

    finally {

        saving = false;

    }

}


// ============================================================
// KEYBOARD
// ============================================================

document.addEventListener(
    "keydown",
    function(event) {

        if (!gameRunning) {

            return;

        }


        const key =
            event.key.toLowerCase();


        // UP

        if (

            (
                key === "arrowup" ||
                key === "w"
            ) &&

            direction.y !== 1

        ) {

            nextDirection = {
                x: 0,
                y: -1
            };


            event.preventDefault();

        }


        // DOWN

        else if (

            (
                key === "arrowdown" ||
                key === "s"
            ) &&

            direction.y !== -1

        ) {

            nextDirection = {
                x: 0,
                y: 1
            };


            event.preventDefault();

        }


        // LEFT

        else if (

            (
                key === "arrowleft" ||
                key === "a"
            ) &&

            direction.x !== 1

        ) {

            nextDirection = {
                x: -1,
                y: 0
            };


            event.preventDefault();

        }


        // RIGHT

        else if (

            (
                key === "arrowright" ||
                key === "d"
            ) &&

            direction.x !== -1

        ) {

            nextDirection = {
                x: 1,
                y: 0
            };


            event.preventDefault();

        }

    }
);


// ============================================================
// MOBILE CONTROLS
// ============================================================

document
    .querySelectorAll(
        ".control-button"
    )
    .forEach(
        button => {

            button.addEventListener(
                "click",
                function() {

                    if (!gameRunning) {
                        return;
                    }


                    const dir =
                        this.dataset.direction;


                    if (

                        dir === "up" &&
                        direction.y !== 1

                    ) {

                        nextDirection = {
                            x: 0,
                            y: -1
                        };

                    }


                    else if (

                        dir === "down" &&
                        direction.y !== -1

                    ) {

                        nextDirection = {
                            x: 0,
                            y: 1
                        };

                    }


                    else if (

                        dir === "left" &&
                        direction.x !== 1

                    ) {

                        nextDirection = {
                            x: -1,
                            y: 0
                        };

                    }


                    else if (

                        dir === "right" &&
                        direction.x !== -1

                    ) {

                        nextDirection = {
                            x: 1,
                            y: 0
                        };

                    }

                }
            );

        }
    );


// ============================================================
// START BUTTON
// ============================================================

startGameButton.addEventListener(
    "click",
    function() {

        startGame();

    }
);


// ============================================================
// PLAY AGAIN
// ============================================================

playAgainButton.addEventListener(
    "click",
    function() {

        startGame();

    }
);


// ============================================================
// IMPORTANT:
// DO NOT CALL startGame() HERE.
// PREPARE ONLY.
// ============================================================

prepareGame();

</script>


<?php require "footer.php"; ?>
```
