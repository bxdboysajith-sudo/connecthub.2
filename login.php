```php
<?php
// ============================================================
// CONNECTHUB - LOGIN PAGE
// ADVANCED BLUE / CYBER AUTH DESIGN
// ============================================================

require "config.php";

$error = "";


// ============================================================
// LOGIN PROCESS
// ============================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email =
        trim(
            $_POST["email"] ?? ""
        );

    $password =
        $_POST["password"] ?? "";


    if ($email === "" || $password === "") {

        $error =
            "Please enter your email and password.";

    } else {

        $stmt =
            $conn->prepare("
                SELECT
                    id,
                    name,
                    email,
                    password
                FROM users
                WHERE email = ?
                LIMIT 1
            ");


        if (!$stmt) {

            $error =
                "Unable to process login right now.";

        } else {

            $stmt->bind_param(
                "s",
                $email
            );

            $stmt->execute();

            $user =
                $stmt
                    ->get_result()
                    ->fetch_assoc();

            $stmt->close();


            if (
                $user &&
                password_verify(
                    $password,
                    $user["password"]
                )
            ) {

                $_SESSION["user_id"] =
                    (int)$user["id"];

                $_SESSION["name"] =
                    $user["name"];


                header(
                    "Location: index.php"
                );

                exit;

            } else {

                $error =
                    "Invalid email or password.";

            }
        }
    }
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

<title>
    ConnectHub Login
</title>

<style>

/* ============================================================
   RESET
============================================================ */

* {
    box-sizing: border-box;
}

html,
body {
    width: 100%;
    min-height: 100%;
    margin: 0;
    padding: 0;
}


/* ============================================================
   BODY
============================================================ */

body {

    min-height: 100vh;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    color: white;

    overflow-x: hidden;

    background:
        linear-gradient(
            135deg,
            rgba(2,6,23,.55),
            rgba(3,37,76,.50)
        ),
        url("uploads/login-bg.jpg")
        center center / cover no-repeat fixed;

}


/* ============================================================
   TECH GRID
============================================================ */

body::before {

    content: "";

    position: fixed;

    inset: 0;

    pointer-events: none;

    opacity: .22;

    background-image:
        linear-gradient(
            rgba(96,165,250,.12) 1px,
            transparent 1px
        ),
        linear-gradient(
            90deg,
            rgba(96,165,250,.12) 1px,
            transparent 1px
        );

    background-size:
        36px 36px;

}


/* ============================================================
   GLOW EFFECTS
============================================================ */

.glow {

    position: fixed;

    border-radius: 50%;

    pointer-events: none;

    filter: blur(5px);

}

.glow-one {

    width: 350px;

    height: 350px;

    top: -130px;

    left: -100px;

    background:
        rgba(37,99,235,.20);

}

.glow-two {

    width: 320px;

    height: 320px;

    right: -100px;

    bottom: -120px;

    background:
        rgba(6,182,212,.16);

}


/* ============================================================
   PAGE
============================================================ */

.auth-page {

    position: relative;

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 30px 18px;

}


/* ============================================================
   MAIN AUTH CONTAINER
============================================================ */

.auth-layout {

    width: min(
        1050px,
        100%
    );

    display: grid;

    grid-template-columns:
        1fr 440px;

    overflow: hidden;

    border:
        1px solid
        rgba(147,197,253,.22);

    border-radius: 30px;

    background:
        rgba(2,6,23,.55);

    box-shadow:
        0 35px 100px
        rgba(0,0,0,.55);

    backdrop-filter:
        blur(18px);

    -webkit-backdrop-filter:
        blur(18px);

}


/* ============================================================
   LEFT SIDE
============================================================ */

.auth-showcase {

    position: relative;

    min-height: 620px;

    display: flex;

    flex-direction: column;

    justify-content: center;

    padding: 55px;

    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            rgba(15,23,42,.72),
            rgba(30,64,175,.28)
        );

}


.showcase-line {

    width: 65px;

    height: 4px;

    margin-bottom: 18px;

    border-radius: 10px;

    background:
        linear-gradient(
            90deg,
            #38bdf8,
            #2563eb
        );

}


.showcase-label {

    color:
        #60a5fa;

    font-size:
        9px;

    font-weight:
        900;

    letter-spacing:
        3px;

}


.auth-showcase h1 {

    max-width:
        500px;

    margin:
        12px 0;

    font-size:
        clamp(
            42px,
            5vw,
            68px
        );

    line-height:
        1.02;

}


.auth-showcase h1 span {

    color:
        #60a5fa;

}


.showcase-description {

    max-width:
        490px;

    color:
        #bfdbfe;

    line-height:
        1.8;

    font-size:
        14px;

}


.feature-grid {

    display:
        grid;

    grid-template-columns:
        repeat(2,1fr);

    gap:
        10px;

    max-width:
        450px;

    margin-top:
        26px;

}


.feature-box {

    padding:
        13px;

    border:
        1px solid
        rgba(147,197,253,.15);

    border-radius:
        13px;

    background:
        rgba(15,23,42,.34);

}


.feature-box strong {

    display:
        block;

    margin-bottom:
        4px;

    color:
        white;

    font-size:
        11px;

}


.feature-box span {

    color:
        #93c5fd;

    font-size:
        8px;

}


.showcase-bottom {

    margin-top:
        24px;

    color:
        rgba(255,255,255,.55);

    font-size:
        8px;

    letter-spacing:
        1px;

}


/* ============================================================
   RIGHT LOGIN CARD
============================================================ */

.auth-panel {

    display:
        flex;

    align-items:
        center;

    padding:
        42px 36px;

    background:
        rgba(248,250,252,.96);

    color:
        #0f172a;

}


.login-card {

    width:
        100%;

}


.logo {

    text-align:
        center;

    font-size:
        30px;

    font-weight:
        1000;

    letter-spacing:
        -.8px;

    color:
        #0f172a;

}


.logo span {

    color:
        #2563eb;

}


.login-label {

    margin-top:
        5px;

    text-align:
        center;

    color:
        #64748b;

    font-size:
        9px;

    font-weight:
        800;

    letter-spacing:
        2px;

}


.login-title {

    margin:
        26px 0 6px;

    font-size:
        29px;

    color:
        #0f172a;

}


.login-subtitle {

    margin:
        0 0 22px;

    color:
        #64748b;

    line-height:
        1.5;

    font-size:
        11px;

}


/* ============================================================
   ERROR
============================================================ */

.login-error {

    margin-bottom:
        16px;

    padding:
        11px 13px;

    border:
        1px solid
        #fecaca;

    border-radius:
        10px;

    color:
        #b91c1c;

    background:
        #fef2f2;

    font-size:
        10px;

    font-weight:
        700;

}


/* ============================================================
   FORM
============================================================ */

.form-group {

    margin-bottom:
        15px;

}


.form-group label {

    display:
        block;

    margin-bottom:
        7px;

    color:
        #334155;

    font-size:
        10px;

    font-weight:
        800;

}


.input-wrap {

    position:
        relative;

}


.input-icon {

    position:
        absolute;

    left:
        13px;

    top:
        50%;

    transform:
        translateY(-50%);

    font-size:
        15px;

}


.form-group input {

    width:
        100%;

    height:
        48px;

    padding:
        0 14px 0 40px;

    border:
        1px solid
        #dbe3ef;

    border-radius:
        12px;

    outline:
        none;

    color:
        #0f172a;

    background:
        #f8fafc;

    font-size:
        12px;

    transition:
        .18s ease;

}


.form-group input:focus {

    border-color:
        #60a5fa;

    background:
        white;

    box-shadow:
        0 0 0 4px
        rgba(59,130,246,.10);

}


.form-group input::placeholder {

    color:
        #94a3b8;

}


/* ============================================================
   LOGIN BUTTON
============================================================ */

.login-button {

    width:
        100%;

    height:
        49px;

    margin-top:
        7px;

    border:
        none;

    border-radius:
        12px;

    color:
        white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    font-size:
        13px;

    font-weight:
        900;

    cursor:
        pointer;

    box-shadow:
        0 10px 25px
        rgba(37,99,235,.25);

    transition:
        .18s ease;

}


.login-button:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 14px 30px
        rgba(37,99,235,.32);

}


.login-button:active {

    transform:
        translateY(0);

}


/* ============================================================
   DIVIDER
============================================================ */

.divider {

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    margin:
        22px 0;

}


.divider::before,
.divider::after {

    content:
        "";

    flex:
        1;

    height:
        1px;

    background:
        #e2e8f0;

}


.divider span {

    color:
        #94a3b8;

    font-size:
        8px;

    font-weight:
        800;

}


/* ============================================================
   REGISTER
============================================================ */

.register-text {

    text-align:
        center;

    color:
        #64748b;

    font-size:
        10px;

}


.register-link {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    width:
        100%;

    height:
        43px;

    margin-top:
        10px;

    border:
        1px solid
        #bfdbfe;

    border-radius:
        11px;

    color:
        #2563eb;

    background:
        #eff6ff;

    text-decoration:
        none;

    font-size:
        11px;

    font-weight:
        900;

    transition:
        .18s ease;

}


.register-link:hover {

    background:
        #dbeafe;

    border-color:
        #93c5fd;

}


/* ============================================================
   SECURITY NOTE
============================================================ */

.security-note {

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        5px;

    margin-top:
        20px;

    color:
        #94a3b8;

    font-size:
        7px;

}


/* ============================================================
   MOBILE
============================================================ */

@media(max-width:850px) {

    .auth-layout {

        grid-template-columns:
            1fr;

        max-width:
            520px;

    }

    .auth-showcase {

        min-height:
            300px;

        padding:
            35px;

    }

    .auth-showcase h1 {

        font-size:
            42px;

    }

    .feature-grid {

        display:
            none;

    }

}


@media(max-width:500px) {

    .auth-page {

        padding:
            12px;

    }

    .auth-layout {

        border-radius:
            22px;

    }

    .auth-showcase {

        min-height:
            230px;

        padding:
            27px;

    }

    .auth-showcase h1 {

        font-size:
            34px;

    }

    .showcase-description {

        font-size:
            11px;

    }

    .auth-panel {

        padding:
            30px 22px;

    }

}

</style>

</head>


<body>


<div class="glow glow-one"></div>
<div class="glow glow-two"></div>


<div class="auth-page">


    <div class="auth-layout">


        <!-- ==================================================
             SHOWCASE
        =================================================== -->

        <section class="auth-showcase">


            <div class="showcase-line"></div>


            <div class="showcase-label">
                CONNECTHUB DIGITAL PLATFORM
            </div>


            <h1>
                Your world.
                <br>
                <span>Connected.</span>
            </h1>


            <p class="showcase-description">

                Connect with people, share moments,
                shop products, manage your banking,
                chat privately and play games —
                all from one powerful platform.

            </p>


            <div class="feature-grid">


                <div class="feature-box">

                    <strong>
                        👥 SOCIAL
                    </strong>

                    <span>
                        Connect with your people
                    </span>

                </div>


                <div class="feature-box">

                    <strong>
                        💬 MESSAGES
                    </strong>

                    <span>
                        Private conversations
                    </span>

                </div>


                <div class="feature-box">

                    <strong>
                        🛒 SHOP
                    </strong>

                    <span>
                        Browse and purchase products
                    </span>

                </div>


                <div class="feature-box">

                    <strong>
                        🎮 GAMES
                    </strong>

                    <span>
                        Play, score and earn
                    </span>

                </div>


            </div>


            <div class="showcase-bottom">
                SECURE • CONNECTED • CONNECTHUB
            </div>


        </section>


        <!-- ==================================================
             LOGIN PANEL
        =================================================== -->

        <section class="auth-panel">


            <div class="login-card">


                <div class="logo">
                    Connect<span>Hub</span>
                </div>


                <div class="login-label">
                    WELCOME BACK
                </div>


                <h2 class="login-title">
                    Sign in
                </h2>


                <p class="login-subtitle">
                    Enter your ConnectHub account
                    details to continue.
                </p>


                <?php if (!empty($error)): ?>

                    <div class="login-error">

                        ⚠️
                        <?= e($error) ?>

                    </div>

                <?php endif; ?>


                <form
                    method="POST"
                    autocomplete="on"
                >


                    <div class="form-group">

                        <label>
                            Email Address
                        </label>

                        <div class="input-wrap">

                            <span class="input-icon">
                                📧
                            </span>

                            <input
                                name="email"
                                type="email"
                                placeholder="Enter your email"
                                autocomplete="email"
                                required
                                value="<?= e(
                                    $_POST["email"] ?? ""
                                ) ?>"
                            >

                        </div>

                    </div>


                    <div class="form-group">

                        <label>
                            Password
                        </label>

                        <div class="input-wrap">

                            <span class="input-icon">
                                🔐
                            </span>

                            <input
                                name="password"
                                type="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >

                        </div>

                    </div>


                    <button
                        type="submit"
                        class="login-button"
                    >
                        🔓 Login to ConnectHub
                    </button>


                </form>


                <div class="divider">

                    <span>
                        NEW TO CONNECTHUB?
                    </span>

                </div>


                <div class="register-text">

                    Don't have an account yet?

                </div>


                <a
                    href="register.php"
                    class="register-link"
                >
                    ✨ Create New Account
                </a>


                <div class="security-note">

                    🔒 Your account connection is protected

                </div>


            </div>


        </section>


    </div>

</div>


</body>

</html>
```
