<?php
session_start();

/*
 * When the user clicks ENTER, we mark the intro as completed
 * for this browser session and send them to login.php.
 */
if (
    isset($_GET["enter"]) &&
    $_GET["enter"] === "1"
) {
    $_SESSION["connecthub_intro_seen"] = true;

    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>ConnectHub</title>

<style>

* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: #020617;
    font-family: Arial, sans-serif;
}

.intro {
    position: fixed;
    inset: 0;
    overflow: hidden;
    background: #020617;
}

video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    background: #020617;
}

.overlay {
    position: absolute;
    inset: 0;
    z-index: 2;

    background:
        linear-gradient(
            180deg,
            rgba(0,0,0,.12),
            transparent 35%,
            rgba(2,6,23,.78)
        );
}

.brand {
    position: absolute;
    top: 25px;
    left: 28px;
    z-index: 5;

    display: flex;
    align-items: center;
    gap: 10px;

    color: white;
}

.logo {
    width: 44px;
    height: 44px;

    display: grid;
    place-items: center;

    border-radius: 14px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5,
            #7c3aed
        );

    font-size: 20px;
    font-weight: 1000;

    box-shadow:
        0 0 30px
        rgba(59,130,246,.40);
}

.brand strong {
    display: block;
    font-size: 20px;
}

.brand strong span {
    color: #60a5fa;
}

.brand small {
    display: block;
    margin-top: 3px;
    font-size: 6px;
    letter-spacing: 1.4px;
    color: rgba(255,255,255,.60);
}

.center {
    position: absolute;
    left: 50%;
    bottom: 8%;
    transform: translateX(-50%);
    z-index: 5;

    width: min(700px, calc(100% - 30px));

    text-align: center;

    color: white;
}

.welcome {
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 4px;
    color: rgba(255,255,255,.75);
}

.title {
    margin-top: 8px;
    font-size: clamp(42px, 8vw, 78px);
    font-weight: 1000;
    line-height: 1;
    letter-spacing: -3px;
    text-shadow:
        0 5px 35px rgba(0,0,0,.45);
}

.title span {
    color: #60a5fa;
}

.description {
    max-width: 530px;
    margin: 14px auto 0;

    color: rgba(255,255,255,.76);

    font-size: 10px;
    line-height: 1.7;
}

.enter {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-width: 240px;
    min-height: 58px;

    margin-top: 24px;

    padding: 0 25px;

    border: 1px solid
        rgba(255,255,255,.25);

    border-radius: 15px;

    color: white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5,
            #7c3aed
        );

    box-shadow:
        0 15px 50px
        rgba(37,99,235,.36);

    font-size: 11px;
    font-weight: 1000;
    letter-spacing: 1px;

    text-decoration: none;

    transition: .22s ease;

    cursor: pointer;

    overflow: hidden;

    position: relative;
}

.enter:hover {
    transform:
        translateY(-3px)
        scale(1.02);

    box-shadow:
        0 20px 65px
        rgba(37,99,235,.52);
}

.enter::before {
    content: "";

    position: absolute;
    top: 0;
    left: -130%;

    width: 65%;
    height: 100%;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(255,255,255,.25),
            transparent
        );

    transform: skewX(-20deg);

    transition: left .65s ease;
}

.enter:hover::before {
    left: 140%;
}

.arrow {
    margin-left: 8px;
    transition: transform .2s ease;
}

.enter:hover .arrow {
    transform: translateX(5px);
}

.status {
    position: absolute;
    right: 20px;
    bottom: 20px;

    z-index: 5;

    padding: 7px 10px;

    border-radius: 999px;

    background:
        rgba(0,0,0,.28);

    border:
        1px solid
        rgba(255,255,255,.12);

    color:
        rgba(255,255,255,.70);

    font-size: 6px;
    font-weight: 900;

    backdrop-filter: blur(10px);
}

.status-dot {
    display: inline-block;

    width: 6px;
    height: 6px;

    margin-right: 5px;

    border-radius: 50%;

    background: #22c55e;

    box-shadow:
        0 0 9px
        rgba(34,197,94,.8);
}

@media(max-width:600px) {

    .brand {
        top: 15px;
        left: 15px;
    }

    .logo {
        width: 38px;
        height: 38px;
        font-size: 17px;
    }

    .brand strong {
        font-size: 17px;
    }

    .brand small {
        font-size: 5px;
    }

    .welcome {
        font-size: 7px;
        letter-spacing: 2px;
    }

    .title {
        font-size: 43px;
    }

    .description {
        font-size: 8px;
        padding: 0 12px;
    }

    .enter {
        min-width: 225px;
        min-height: 54px;
    }

}

</style>

</head>


<body>


<div class="intro">


    <video
        id="introVideo"
        autoplay
        muted
        playsinline
        preload="auto"
    >

        <source
            src="intro/connecthub_intro.mp4"
            type="video/mp4"
        >

    </video>


    <div class="overlay"></div>


    <div class="brand">

        <div class="logo">
            C
        </div>

        <div>

            <strong>
                Connect<span>Hub</span>
            </strong>

            <small>
                CONNECT • SHARE • SHOP • BANK • PLAY
            </small>

        </div>

    </div>


    <div class="center">

        <div class="welcome">
            WELCOME TO
        </div>


        <div class="title">
            Connect<span>Hub</span>
        </div>


        <div class="description">

            Your social, shopping, banking and
            gaming experience — all in one place.

        </div>


        <a
            href="start.php?enter=1"
            class="enter"
        >

            ENTER CONNECTHUB

            <span class="arrow">
                →
            </span>

        </a>


    </div>


    <div class="status">

        <span class="status-dot"></span>

        CONNECTHUB INTRO

    </div>


</div>


<script>

/*
 * Attempt autoplay again.
 */

const video =
    document.getElementById(
        "introVideo"
    );

if (video) {

    video.play().catch(() => {

        /*
         * Browser blocked autoplay.
         * The user can still click ENTER.
         */

    });

}

</script>


</body>

</html>