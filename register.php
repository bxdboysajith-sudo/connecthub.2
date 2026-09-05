<?php

// ============================================================
// CONNECTHUB - PREMIUM REGISTER PAGE
// COMPLETE REPLACEMENT
// ============================================================

require_once "config.php";

$error = "";
$success = "";


// ============================================================
// REGISTER PROCESS
// ============================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name =
        trim(
            (string)(
                $_POST["name"] ?? ""
            )
        );

    $email =
        trim(
            (string)(
                $_POST["email"] ?? ""
            )
        );

    $password =
        (string)(
            $_POST["password"] ?? ""
        );

    $confirmPassword =
        (string)(
            $_POST["confirm_password"] ?? ""
        );


    // ========================================================
    // VALIDATION
    // ========================================================

    if ($name === "") {

        $error =
            "Please enter your full name.";

    } elseif (strlen($name) < 2) {

        $error =
            "Your name must contain at least 2 characters.";

    } elseif (strlen($name) > 100) {

        $error =
            "Your name is too long.";

    } elseif ($email === "") {

        $error =
            "Please enter your email address.";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $error =
            "Password must contain at least 6 characters.";

    } elseif ($password !== $confirmPassword) {

        $error =
            "Passwords do not match.";

    }


    // ========================================================
    // DATABASE VALIDATION
    // ========================================================

    if ($error === "") {

        try {

            // --------------------------------------------------
            // CHECK EMAIL
            // --------------------------------------------------

            $checkStmt =
                $conn->prepare("
                    SELECT id
                    FROM users
                    WHERE email = ?
                    LIMIT 1
                ");

            if (!$checkStmt) {

                $error =
                    "Unable to verify your email address.";

            } else {

                $checkStmt->bind_param(
                    "s",
                    $email
                );

                $checkStmt->execute();

                $existingUser =
                    $checkStmt
                        ->get_result()
                        ->fetch_assoc();

                $checkStmt->close();


                if ($existingUser) {

                    $error =
                        "An account with this email already exists.";

                }

            }


            // --------------------------------------------------
            // CREATE USER
            // --------------------------------------------------

            if ($error === "") {

                $passwordHash =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                $userStmt =
                    $conn->prepare("
                        INSERT INTO users
                        (
                            name,
                            email,
                            password
                        )
                        VALUES (?, ?, ?)
                    ");


                if (!$userStmt) {

                    $error =
                        "Unable to create your account.";

                } else {

                    $userStmt->bind_param(
                        "sss",
                        $name,
                        $email,
                        $passwordHash
                    );


                    if (
                        $userStmt->execute()
                    ) {

                        $newUserId =
                            (int)(
                                $userStmt->insert_id
                            );

                        $userStmt->close();


                        // ====================================
                        // OPTIONAL BANK ACCOUNT
                        // ====================================

                        try {

                            $bankTable =
                                $conn->query(
                                    "SHOW TABLES LIKE 'bank_accounts'"
                                );


                            if (
                                $bankTable &&
                                $bankTable->num_rows > 0
                            ) {

                                $accountNumber =
                                    "CH" .
                                    str_pad(
                                        (string)$newUserId,
                                        8,
                                        "0",
                                        STR_PAD_LEFT
                                    );


                                // --------------------------------
                                // CHECK ACCOUNT NUMBER
                                // --------------------------------

                                $accountExists =
                                    false;


                                $accountCheck =
                                    $conn->prepare("
                                        SELECT id
                                        FROM bank_accounts
                                        WHERE account_number = ?
                                        LIMIT 1
                                    ");


                                if ($accountCheck) {

                                    $accountCheck->bind_param(
                                        "s",
                                        $accountNumber
                                    );

                                    $accountCheck->execute();

                                    $accountExists =
                                        $accountCheck
                                            ->get_result()
                                            ->num_rows > 0;

                                    $accountCheck->close();

                                }


                                // --------------------------------
                                // FALLBACK NUMBER
                                // --------------------------------

                                if ($accountExists) {

                                    try {

                                        $randomNumber =
                                            random_int(
                                                1000,
                                                9999
                                            );

                                    } catch (Throwable $e) {

                                        $randomNumber =
                                            mt_rand(
                                                1000,
                                                9999
                                            );

                                    }


                                    $accountNumber =
                                        "CH" .
                                        date("ymd") .
                                        $newUserId .
                                        $randomNumber;

                                }


                                // --------------------------------
                                // CREATE BANK ACCOUNT
                                // --------------------------------

                                $initialBalance =
                                    0.00;


                                $bankStmt =
                                    $conn->prepare("
                                        INSERT INTO bank_accounts
                                        (
                                            user_id,
                                            account_number,
                                            balance,
                                            pin_hash
                                        )
                                        VALUES (?, ?, ?, NULL)
                                    ");


                                if ($bankStmt) {

                                    $bankStmt->bind_param(
                                        "isd",
                                        $newUserId,
                                        $accountNumber,
                                        $initialBalance
                                    );

                                    $bankStmt->execute();

                                    $bankStmt->close();

                                }

                            }

                        } catch (Throwable $bankError) {

                            // Bank creation is optional.
                            // The main account remains valid.

                        }


                        $success =
                            "Your ConnectHub account has been created successfully.";

                    } else {

                        $error =
                            "Could not create your account. Please try again.";

                        $userStmt->close();

                    }

                }

            }

        } catch (Throwable $e) {

            $error =
                "Something went wrong while creating your account.";

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

<meta
    name="theme-color"
    content="#030712"
>

<title>
    Join ConnectHub
</title>


<style>

/* ============================================================
   RESET
============================================================ */

* {

    box-sizing:
        border-box;

    margin:
        0;

    padding:
        0;

}


html,
body {

    width:
        100%;

    min-height:
        100%;

}


/* ============================================================
   BODY
============================================================ */

body {

    min-height:
        100vh;

    overflow-x:
        hidden;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    color:
        #e5eefc;

    background:
        #020617;

}


/* ============================================================
   BACKGROUND
============================================================ */

.rc-bg {

    position:
        fixed;

    inset:
        0;

    z-index:
        0;

    overflow:
        hidden;

    background:

        radial-gradient(
            circle at 10% 10%,
            rgba(
                37,
                99,
                235,
                .17
            ),
            transparent 30%
        ),

        radial-gradient(
            circle at 90% 15%,
            rgba(
                124,
                58,
                237,
                .14
            ),
            transparent 30%
        ),

        radial-gradient(
            circle at 50% 100%,
            rgba(
                6,
                182,
                212,
                .10
            ),
            transparent 35%
        ),

        #020617;

}


.rc-grid {

    position:
        absolute;

    inset:
        0;

    opacity:
        .25;

    background-image:

        linear-gradient(
            rgba(
                96,
                165,
                250,
                .055
            ) 1px,
            transparent 1px
        ),

        linear-gradient(
            90deg,
            rgba(
                96,
                165,
                250,
                .055
            ) 1px,
            transparent 1px
        );

    background-size:
        45px
        45px;

    mask-image:
        radial-gradient(
            ellipse at center,
            black 20%,
            transparent 90%
        );

    -webkit-mask-image:
        radial-gradient(
            ellipse at center,
            black 20%,
            transparent 90%
        );

}


.rc-orb {

    position:
        absolute;

    border-radius:
        50%;

    filter:
        blur(5px);

    pointer-events:
        none;

}


.rc-orb-one {

    width:
        430px;

    height:
        430px;

    left:
        -190px;

    top:
        -180px;

    background:
        radial-gradient(
            circle,
            rgba(
                59,
                130,
                246,
                .18
            ),
            transparent 70%
        );

    animation:
        orbMoveOne
        12s
        ease-in-out
        infinite
        alternate;

}


.rc-orb-two {

    width:
        380px;

    height:
        380px;

    right:
        -170px;

    bottom:
        -160px;

    background:
        radial-gradient(
            circle,
            rgba(
                124,
                58,
                237,
                .17
            ),
            transparent 70%
        );

    animation:
        orbMoveTwo
        15s
        ease-in-out
        infinite
        alternate;

}


@keyframes orbMoveOne {

    from {

        transform:
            translate(
                0,
                0
            );

    }

    to {

        transform:
            translate(
                90px,
                80px
            );

    }

}


@keyframes orbMoveTwo {

    from {

        transform:
            translate(
                0,
                0
            );

    }

    to {

        transform:
            translate(
                -80px,
                -90px
            );

    }

}


/* ============================================================
   PARTICLES
============================================================ */

.rc-particles {

    position:
        absolute;

    inset:
        0;

    pointer-events:
        none;

}


.rc-particle {

    position:
        absolute;

    width:
        3px;

    height:
        3px;

    border-radius:
        50%;

    background:
        rgba(
            147,
            197,
            253,
            .75
        );

    box-shadow:
        0
        0
        9px
        rgba(
            96,
            165,
            250,
            .65
        );

    animation:
        particleFloat
        linear
        infinite;

}


@keyframes particleFloat {

    0% {

        transform:
            translateY(
                20px
            );

        opacity:
            0;

    }

    15% {

        opacity:
            .7;

    }

    50% {

        opacity:
            .35;

    }

    85% {

        opacity:
            .7;

    }

    100% {

        transform:
            translateY(
                -100vh
            );

        opacity:
            0;

    }

}


/* ============================================================
   PAGE
============================================================ */

.rc-page {

    position:
        relative;

    z-index:
        5;

    width:
        100%;

    min-height:
        100vh;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        35px
        20px;

}


/* ============================================================
   MAIN AUTH BOX
============================================================ */

.rc-container {

    position:
        relative;

    display:
        grid;

    grid-template-columns:
        minmax(
            0,
            1.05fr
        )
        minmax(
            420px,
            .95fr
        );

    width:
        min(
            1180px,
            100%
        );

    min-height:
        720px;

    overflow:
        hidden;

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
        30px;

    background:
        rgba(
            2,
            6,
            23,
            .67
        );

    box-shadow:

        0
        35px
        100px
        rgba(
            0,
            0,
            0,
            .54
        ),

        inset
        0
        1px
        0
        rgba(
            255,
            255,
            255,
            .06
        );

    backdrop-filter:
        blur(
            18px
        );

    -webkit-backdrop-filter:
        blur(
            18px
        );

}


/* ============================================================
   LEFT SIDE
============================================================ */

.rc-showcase {

    position:
        relative;

    display:
        flex;

    flex-direction:
        column;

    justify-content:
        center;

    padding:
        55px;

    overflow:
        hidden;

    border-right:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .08
        );

    background:

        linear-gradient(
            135deg,
            rgba(
                15,
                23,
                42,
                .68
            ),
            rgba(
                30,
                64,
                175,
                .17
            ),
            rgba(
                76,
                29,
                149,
                .12
            )
        );

}


/* ============================================================
   LOGO
============================================================ */

.rc-brand {

    display:
        flex;

    align-items:
        center;

    gap:
        11px;

    margin-bottom:
        38px;

}


.rc-brand-icon {

    width:
        50px;

    height:
        50px;

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
            .25
        );

    border-radius:
        15px;

    color:
        #fff;

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
        30px
        rgba(
            59,
            130,
            246,
            .28
        );

    font-size:
        22px;

    font-weight:
        1000;

    animation:
        logoPulse
        2.8s
        ease-in-out
        infinite;

}


@keyframes logoPulse {

    50% {

        transform:
            translateY(
                -3px
            )
            scale(
                1.04
            );

    }

}


.rc-brand-text strong {

    display:
        block;

    color:
        #fff;

    font-size:
        21px;

    font-weight:
        1000;

    letter-spacing:
        -.6px;

}


.rc-brand-text strong span {

    color:
        #60a5fa;

}


.rc-brand-text small {

    display:
        block;

    margin-top:
        4px;

    color:
        #64748b;

    font-size:
        6px;

    font-weight:
        900;

    letter-spacing:
        1.6px;

}


/* ============================================================
   SHOWCASE CONTENT
============================================================ */

.rc-eyebrow {

    color:
        #67e8f9;

    font-size:
        8px;

    font-weight:
        950;

    letter-spacing:
        2.3px;

}


.rc-showcase h1 {

    max-width:
        610px;

    margin:
        11px
        0
        15px;

    color:
        #f8fafc;

    font-size:
        clamp(
            43px,
            5vw,
            70px
        );

    line-height:
        1.02;

    letter-spacing:
        -2.8px;

    font-weight:
        1000;

}


.rc-showcase h1 span {

    color:
        #60a5fa;

}


.rc-showcase-description {

    max-width:
        520px;

    color:
        #9fb0c7;

    font-size:
        12px;

    line-height:
        1.75;

}


/* ============================================================
   FEATURE CARDS
============================================================ */

.rc-features {

    display:
        grid;

    grid-template-columns:
        repeat(
            2,
            minmax(
                0,
                1fr
            )
        );

    gap:
        9px;

    max-width:
        540px;

    margin-top:
        25px;

}


.rc-feature {

    padding:
        12px;

    border:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .10
        );

    border-radius:
        13px;

    background:
        rgba(
            15,
            23,
            42,
            .37
        );

    transition:
        .20s
        ease;

}


.rc-feature:hover {

    transform:
        translateY(
            -3px
        );

    border-color:
        rgba(
            96,
            165,
            250,
            .25
        );

    background:
        rgba(
            30,
            64,
            175,
            .12
        );

}


.rc-feature-icon {

    margin-bottom:
        7px;

    font-size:
        17px;

}


.rc-feature strong {

    display:
        block;

    color:
        #e2e8f0;

    font-size:
        9px;

    font-weight:
        900;

}


.rc-feature span {

    display:
        block;

    margin-top:
        3px;

    color:
        #64748b;

    font-size:
        7px;

    line-height:
        1.4;

}


/* ============================================================
   SHOWCASE FOOTER
============================================================ */

.rc-showcase-footer {

    margin-top:
        25px;

    color:
        #475569;

    font-size:
        7px;

    font-weight:
        850;

    letter-spacing:
        1.2px;

}


/* ============================================================
   DECORATION
============================================================ */

.rc-showcase-decoration {

    position:
        absolute;

    right:
        -80px;

    bottom:
        -90px;

    width:
        330px;

    height:
        330px;

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
        50%;

}


.rc-showcase-decoration::before {

    content:
        "";

    position:
        absolute;

    inset:
        45px;

    border:
        1px
        solid
        rgba(
            124,
            58,
            237,
            .10
        );

    border-radius:
        50%;

}


.rc-showcase-decoration::after {

    content:
        "";

    position:
        absolute;

    inset:
        90px;

    border:
        1px
        solid
        rgba(
            34,
            211,
            238,
            .10
        );

    border-radius:
        50%;

}


/* ============================================================
   REGISTER SIDE
============================================================ */

.rc-form-panel {

    display:
        flex;

    align-items:
        center;

    padding:
        45px
        40px;

    color:
        #0f172a;

    background:
        rgba(
            248,
            250,
            252,
            .985
        );

}


/* ============================================================
   FORM
============================================================ */

.rc-form {

    width:
        100%;

    max-width:
        500px;

    margin:
        auto;

}


.rc-form-heading {

    margin-bottom:
        20px;

}


.rc-form-heading .mini {

    color:
        #2563eb;

    font-size:
        8px;

    font-weight:
        950;

    letter-spacing:
        2px;

}


.rc-form-heading h2 {

    margin:
        5px
        0;

    color:
        #0f172a;

    font-size:
        30px;

    font-weight:
        1000;

    letter-spacing:
        -.8px;

}


.rc-form-heading p {

    color:
        #64748b;

    font-size:
        10px;

    line-height:
        1.55;

}


/* ============================================================
   ALERT
============================================================ */

.rc-alert {

    display:
        flex;

    align-items:
        flex-start;

    gap:
        8px;

    margin-bottom:
        14px;

    padding:
        11px
        12px;

    border-radius:
        11px;

    font-size:
        9px;

    line-height:
        1.5;

}


.rc-alert.error {

    color:
        #b91c1c;

    border:
        1px
        solid
        #fecaca;

    background:
        #fef2f2;

}


.rc-alert.success {

    color:
        #166534;

    border:
        1px
        solid
        #bbf7d0;

    background:
        #f0fdf4;

}


.rc-success-login {

    display:
        inline-flex;

    margin-top:
        8px;

    color:
        #166534;

    font-weight:
        900;

    text-decoration:
        none;

}


/* ============================================================
   FORM GROUP
============================================================ */

.rc-group {

    margin-bottom:
        12px;

}


.rc-group label {

    display:
        block;

    margin-bottom:
        6px;

    color:
        #334155;

    font-size:
        9px;

    font-weight:
        900;

}


.rc-input-wrap {

    position:
        relative;

}


.rc-input-icon {

    position:
        absolute;

    left:
        12px;

    top:
        50%;

    transform:
        translateY(
            -50%
        );

    color:
        #64748b;

    font-size:
        13px;

    pointer-events:
        none;

}


.rc-input {

    width:
        100%;

    height:
        47px;

    padding:
        0
        42px
        0
        38px;

    border:
        1px
        solid
        #dbe3ef;

    border-radius:
        11px;

    outline:
        none;

    color:
        #0f172a;

    background:
        #f8fafc;

    font:
        inherit;

    font-size:
        11px;

    transition:
        .20s
        ease;

}


.rc-input:focus {

    border-color:
        #60a5fa;

    background:
        #fff;

    box-shadow:
        0
        0
        0
        4px
        rgba(
            59,
            130,
            246,
            .09
        );

}


.rc-input::placeholder {

    color:
        #94a3b8;

}


.rc-password-button {

    position:
        absolute;

    right:
        8px;

    top:
        50%;

    transform:
        translateY(
            -50%
        );

    width:
        30px;

    height:
        30px;

    border:
        0;

    border-radius:
        8px;

    color:
        #64748b;

    background:
        transparent;

    cursor:
        pointer;

}


.rc-password-button:hover {

    color:
        #2563eb;

    background:
        #eff6ff;

}


/* ============================================================
   PASSWORD STRENGTH
============================================================ */

.rc-strength {

    display:
        flex;

    gap:
        4px;

    margin-top:
        7px;

}


.rc-strength-segment {

    flex:
        1;

    height:
        4px;

    border-radius:
        999px;

    background:
        #e2e8f0;

    transition:
        .20s
        ease;

}


.rc-strength-label {

    margin-top:
        5px;

    color:
        #94a3b8;

    font-size:
        7px;

}


/* ============================================================
   PASSWORD MATCH
============================================================ */

.rc-match {

    display:
        none;

    margin-top:
        5px;

    font-size:
        7px;

    font-weight:
        800;

}


.rc-match.show {

    display:
        block;

}


.rc-match.good {

    color:
        #16a34a;

}


.rc-match.bad {

    color:
        #dc2626;

}


/* ============================================================
   CHECKBOX
============================================================ */

.rc-check-row {

    display:
        flex;

    align-items:
        flex-start;

    gap:
        7px;

    margin:
        7px 0
        14px;

}


.rc-check-row input {

    width:
        15px;

    height:
        15px;

    margin-top:
        1px;

    accent-color:
        #2563eb;

}


.rc-check-row label {

    color:
        #64748b;

    font-size:
        8px;

    line-height:
        1.5;

}


.rc-check-row strong {

    color:
        #334155;

}


/* ============================================================
   REGISTER BUTTON
============================================================ */

.rc-register-button {

    position:
        relative;

    width:
        100%;

    height:
        50px;

    overflow:
        hidden;

    border:
        0;

    border-radius:
        12px;

    color:
        #fff;

    background:

        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5,
            #7c3aed
        );

    box-shadow:
        0
        12px
        28px
        rgba(
            37,
            99,
            235,
            .24
        );

    font-size:
        11px;

    font-weight:
        950;

    cursor:
        pointer;

    transition:
        .20s
        ease;

}


.rc-register-button:hover {

    transform:
        translateY(
            -2px
        );

    box-shadow:
        0
        17px
        36px
        rgba(
            37,
            99,
            235,
            .33
        );

}


.rc-register-button::before {

    content:
        "";

    position:
        absolute;

    left:
        -110%;

    top:
        0;

    width:
        55%;

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
                .22
            ),
            transparent
        );

    transform:
        skewX(
            -20deg
        );

    transition:
        left
        .65s
        ease;

}


.rc-register-button:hover::before {

    left:
        140%;

}


/* ============================================================
   DIVIDER
============================================================ */

.rc-divider {

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

    margin:
        18px 0;

}


.rc-divider::before,
.rc-divider::after {

    content:
        "";

    flex:
        1;

    height:
        1px;

    background:
        #e2e8f0;

}


.rc-divider span {

    color:
        #94a3b8;

    font-size:
        7px;

    font-weight:
        850;

    letter-spacing:
        1px;

}


/* ============================================================
   LOGIN BUTTON
============================================================ */

.rc-login {

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    width:
        100%;

    height:
        43px;

    border:
        1px
        solid
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
        10px;

    font-weight:
        900;

    transition:
        .20s
        ease;

}


.rc-login:hover {

    background:
        #dbeafe;

    border-color:
        #93c5fd;

    transform:
        translateY(
            -1px
        );

}


/* ============================================================
   SECURITY NOTE
============================================================ */

.rc-security {

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        5px;

    margin-top:
        13px;

    color:
        #94a3b8;

    font-size:
        7px;

}


/* ============================================================
   SMALL DEVICES
============================================================ */

@media (
    max-width: 950px
) {

    .rc-container {

        grid-template-columns:
            1fr;

        max-width:
            540px;

        min-height:
            auto;

    }


    .rc-showcase {

        min-height:
            300px;

        padding:
            35px;

        border-right:
            0;

        border-bottom:
            1px
            solid
            rgba(
                148,
                163,
                184,
                .08
            );

    }


    .rc-showcase h1 {

        font-size:
            44px;

    }


    .rc-showcase-description {

        font-size:
            10px;

    }


    .rc-form-panel {

        padding:
            35px;

    }

}


@media (
    max-width: 550px
) {

    .rc-page {

        padding:
            12px 8px;

    }


    .rc-container {

        border-radius:
            21px;

    }


    .rc-showcase {

        min-height:
            240px;

        padding:
            26px 20px;

    }


    .rc-brand {

        margin-bottom:
            23px;

    }


    .rc-brand-icon {

        width:
            40px;

        height:
            40px;

        font-size:
            17px;

    }


    .rc-brand-text strong {

        font-size:
            17px;

    }


    .rc-showcase h1 {

        margin-top:
            8px;

        font-size:
            35px;

    }


    .rc-showcase-description {

        font-size:
            9px;

    }


    .rc-features {

        display:
            none;

    }


    .rc-showcase-footer {

        margin-top:
            17px;

    }


    .rc-form-panel {

        padding:
            25px 18px;

    }


    .rc-form-heading h2 {

        font-size:
            25px;

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


<body>


<!-- ============================================================
     BACKGROUND
============================================================ -->

<div
    class="rc-bg"
>

    <div
        class="rc-grid"
    ></div>


    <div
        class="rc-orb rc-orb-one"
    ></div>


    <div
        class="rc-orb rc-orb-two"
    ></div>


    <div
        class="rc-particles"
        id="rcParticles"
    ></div>

</div>


<!-- ============================================================
     PAGE
============================================================ -->

<main
    class="rc-page"
>


    <div
        class="rc-container"
    >


        <!-- ====================================================
             SHOWCASE
        ===================================================== -->

        <section
            class="rc-showcase"
        >


            <div
                class="rc-brand"
            >

                <div
                    class="rc-brand-icon"
                >
                    C
                </div>


                <div
                    class="rc-brand-text"
                >

                    <strong>
                        Connect<span>Hub</span>
                    </strong>


                    <small>
                        CONNECT • SHARE • SHOP • BANK • PLAY
                    </small>

                </div>

            </div>


            <div
                class="rc-eyebrow"
            >
                BUILD YOUR CONNECTHUB IDENTITY
            </div>


            <h1>
                Your world.
                <br>
                <span>One account.</span>
            </h1>


            <p
                class="rc-showcase-description"
            >

                Create your ConnectHub account and enter
                your social, shopping, banking and gaming
                experience.

            </p>


            <!-- =================================================
                 FEATURES
            ================================================== -->

            <div
                class="rc-features"
            >


                <div
                    class="rc-feature"
                >

                    <div
                        class="rc-feature-icon"
                    >
                        👥
                    </div>

                    <strong>
                        Social Network
                    </strong>

                    <span>
                        Discover people and build connections.
                    </span>

                </div>


                <div
                    class="rc-feature"
                >

                    <div
                        class="rc-feature-icon"
                    >
                        💬
                    </div>

                    <strong>
                        Messages
                    </strong>

                    <span>
                        Stay connected with your friends.
                    </span>

                </div>


                <div
                    class="rc-feature"
                >

                    <div
                        class="rc-feature-icon"
                    >
                        🛒
                    </div>

                    <strong>
                        Shopping
                    </strong>

                    <span>
                        Explore products in the ConnectHub shop.
                    </span>

                </div>


                <div
                    class="rc-feature"
                >

                    <div
                        class="rc-feature-icon"
                    >
                        🎮
                    </div>

                    <strong>
                        Arcade
                    </strong>

                    <span>
                        Play ConnectHub games and compete.
                    </span>

                </div>


            </div>


            <div
                class="rc-showcase-footer"
            >

                SECURE • CONNECTED • CONNECTHUB

            </div>


            <div
                class="rc-showcase-decoration"
            ></div>


        </section>


        <!-- ====================================================
             FORM PANEL
        ===================================================== -->

        <section
            class="rc-form-panel"
        >


            <div
                class="rc-form"
            >


                <div
                    class="rc-form-heading"
                >

                    <div
                        class="mini"
                    >
                        CREATE ACCOUNT
                    </div>


                    <h2>
                        Join ConnectHub
                    </h2>


                    <p>
                        Create your account to get started.
                    </p>

                </div>


                <!-- ==================================================
                     ERROR
                =================================================== -->

                <?php if (
                    $error !== ""
                ): ?>

                    <div
                        class="rc-alert error"
                    >

                        <span>
                            ⚠️
                        </span>

                        <span>
                            <?= e($error) ?>
                        </span>

                    </div>

                <?php endif; ?>


                <!-- ==================================================
                     SUCCESS
                =================================================== -->

                <?php if (
                    $success !== ""
                ): ?>

                    <div
                        class="rc-alert success"
                    >

                        <span>
                            ✅
                        </span>

                        <div>

                            <div>
                                <?= e($success) ?>
                            </div>


                            <a
                                href="login.php"
                                class="rc-success-login"
                            >
                                Go to Login →
                            </a>

                        </div>

                    </div>

                <?php endif; ?>


                <?php if (
                    $success === ""
                ): ?>


                    <form
                        method="POST"
                        id="registerForm"
                        autocomplete="off"
                    >


                        <!-- ============================================
                             NAME
                        ============================================= -->

                        <div
                            class="rc-group"
                        >

                            <label
                                for="name"
                            >
                                Full Name
                            </label>


                            <div
                                class="rc-input-wrap"
                            >

                                <span
                                    class="rc-input-icon"
                                >
                                    👤
                                </span>


                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="rc-input"
                                    maxlength="100"
                                    autocomplete="name"
                                    placeholder="Enter your full name"
                                    value="<?= e(
                                        $_POST["name"] ?? ""
                                    ) ?>"
                                    required
                                >

                            </div>

                        </div>


                        <!-- ============================================
                             EMAIL
                        ============================================= -->

                        <div
                            class="rc-group"
                        >

                            <label
                                for="email"
                            >
                                Email Address
                            </label>


                            <div
                                class="rc-input-wrap"
                            >

                                <span
                                    class="rc-input-icon"
                                >
                                    📧
                                </span>


                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="rc-input"
                                    maxlength="150"
                                    autocomplete="email"
                                    placeholder="Enter your email"
                                    value="<?= e(
                                        $_POST["email"] ?? ""
                                    ) ?>"
                                    required
                                >

                            </div>

                        </div>


                        <!-- ============================================
                             PASSWORD
                        ============================================= -->

                        <div
                            class="rc-group"
                        >

                            <label
                                for="password"
                            >
                                Password
                            </label>


                            <div
                                class="rc-input-wrap"
                            >

                                <span
                                    class="rc-input-icon"
                                >
                                    🔐
                                </span>


                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="rc-input"
                                    autocomplete="new-password"
                                    minlength="6"
                                    placeholder="Create a password"
                                    required
                                >


                                <button
                                    type="button"
                                    class="rc-password-button"
                                    id="togglePassword"
                                    aria-label="Show password"
                                >
                                    👁
                                </button>

                            </div>


                            <div
                                class="rc-strength"
                            >

                                <span
                                    class="rc-strength-segment"
                                    id="strength1"
                                ></span>

                                <span
                                    class="rc-strength-segment"
                                    id="strength2"
                                ></span>

                                <span
                                    class="rc-strength-segment"
                                    id="strength3"
                                ></span>

                                <span
                                    class="rc-strength-segment"
                                    id="strength4"
                                ></span>

                                <span
                                    class="rc-strength-segment"
                                    id="strength5"
                                ></span>

                            </div>


                            <div
                                class="rc-strength-label"
                                id="strengthLabel"
                            >
                                Minimum 6 characters
                            </div>

                        </div>


                        <!-- ============================================
                             CONFIRM
                        ============================================= -->

                        <div
                            class="rc-group"
                        >

                            <label
                                for="confirmPassword"
                            >
                                Confirm Password
                            </label>


                            <div
                                class="rc-input-wrap"
                            >

                                <span
                                    class="rc-input-icon"
                                >
                                    ✅
                                </span>


                                <input
                                    type="password"
                                    id="confirmPassword"
                                    name="confirm_password"
                                    class="rc-input"
                                    autocomplete="new-password"
                                    minlength="6"
                                    placeholder="Confirm your password"
                                    required
                                >


                                <button
                                    type="button"
                                    class="rc-password-button"
                                    id="toggleConfirmPassword"
                                    aria-label="Show password"
                                >
                                    👁
                                </button>

                            </div>


                            <div
                                class="rc-match"
                                id="matchMessage"
                            ></div>

                        </div>


                        <!-- ============================================
                             AGREEMENT
                        ============================================= -->

                        <div
                            class="rc-check-row"
                        >

                            <input
                                type="checkbox"
                                id="agree"
                                required
                            >


                            <label
                                for="agree"
                            >

                                I agree to use ConnectHub responsibly
                                and keep my account credentials secure.

                                <strong>
                                    Your password should never be shared.
                                </strong>

                            </label>

                        </div>


                        <!-- ============================================
                             SUBMIT
                        ============================================= -->

                        <button
                            type="submit"
                            class="rc-register-button"
                            id="registerButton"
                        >

                            ✨ Create My ConnectHub Account

                        </button>


                    </form>


                <?php endif; ?>


                <!-- ==================================================
                     DIVIDER
                =================================================== -->

                <div
                    class="rc-divider"
                >

                    <span>
                        ALREADY A MEMBER?
                    </span>

                </div>


                <!-- ==================================================
                     LOGIN
                =================================================== -->

                <a
                    href="login.php"
                    class="rc-login"
                >

                    🔓 Login to ConnectHub

                </a>


                <div
                    class="rc-security"
                >

                    🔒 Password protected
                    •
                    Secure account creation

                </div>


            </div>


        </section>


    </div>


</main>


<script>

/* ============================================================
   PARTICLES
============================================================ */

(function () {

    const container =
        document.getElementById(
            "rcParticles"
        );


    if (
        !container
    ) {
        return;
    }


    const count =
        window.innerWidth <= 600
            ? 28
            : 55;


    for (
        let i = 0;
        i < count;
        i++
    ) {

        const particle =
            document.createElement(
                "span"
            );


        particle.className =
            "rc-particle";


        particle.style.left =
            (
                Math.random() *
                100
            ) +
            "%";


        particle.style.top =
            (
                30 +
                Math.random() *
                100
            ) +
            "%";


        particle.style.animationDuration =
            (
                7 +
                Math.random() *
                10
            ) +
            "s";


        particle.style.animationDelay =
            (
                Math.random() *
                8
            ) +
            "s";


        const size =
            (
                1 +
                Math.random() *
                2.5
            );


        particle.style.width =
            size +
            "px";


        particle.style.height =
            size +
            "px";


        container.appendChild(
            particle
        );

    }

})();


/* ============================================================
   PASSWORD VISIBILITY
============================================================ */

(function () {

    const password =
        document.getElementById(
            "password"
        );


    const confirmPassword =
        document.getElementById(
            "confirmPassword"
        );


    const togglePassword =
        document.getElementById(
            "togglePassword"
        );


    const toggleConfirm =
        document.getElementById(
            "toggleConfirmPassword"
        );


    function toggleField(
        input,
        button
    ) {

        if (
            !input ||
            !button
        ) {
            return;
        }


        button.addEventListener(
            "click",
            function () {

                const isPassword =
                    input.type ===
                    "password";


                input.type =
                    isPassword
                        ? "text"
                        : "password";


                button.textContent =
                    isPassword
                        ? "🙈"
                        : "👁";

            }
        );

    }


    toggleField(
        password,
        togglePassword
    );


    toggleField(
        confirmPassword,
        toggleConfirm
    );

})();


/* ============================================================
   PASSWORD STRENGTH
============================================================ */

(function () {

    const password =
        document.getElementById(
            "password"
        );


    const label =
        document.getElementById(
            "strengthLabel"
        );


    const segments = [

        document.getElementById(
            "strength1"
        ),

        document.getElementById(
            "strength2"
        ),

        document.getElementById(
            "strength3"
        ),

        document.getElementById(
            "strength4"
        ),

        document.getElementById(
            "strength5"
        )

    ];


    if (
        !password ||
        !label
    ) {
        return;
    }


    function updateStrength() {

        const value =
            password.value;


        let score = 0;


        if (
            value.length >= 6
        ) {
            score++;
        }


        if (
            value.length >= 10
        ) {
            score++;
        }


        if (
            /[A-Z]/.test(
                value
            )
        ) {
            score++;
        }


        if (
            /[0-9]/.test(
                value
            )
        ) {
            score++;
        }


        if (
            /[^A-Za-z0-9]/.test(
                value
            )
        ) {
            score++;
        }


        segments.forEach(
            function (
                segment,
                index
            ) {

                if (
                    !segment
                ) {
                    return;
                }


                segment.style.background =
                    index < score
                        ? (
                            score <= 2
                                ? "#ef4444"
                                : score === 3
                                    ? "#f59e0b"
                                    : "#22c55e"
                          )
                        : "#e2e8f0";

            }
        );


        if (
            value === ""
        ) {

            label.textContent =
                "Minimum 6 characters";

        } else if (
            score <= 2
        ) {

            label.textContent =
                "Weak password";

        } else if (
            score === 3
        ) {

            label.textContent =
                "Good password";

        } else if (
            score === 4
        ) {

            label.textContent =
                "Strong password";

        } else {

            label.textContent =
                "Very strong password";

        }

    }


    password.addEventListener(
        "input",
        updateStrength
    );


    updateStrength();

})();


/* ============================================================
   PASSWORD MATCH
============================================================ */

(function () {

    const password =
        document.getElementById(
            "password"
        );


    const confirm =
        document.getElementById(
            "confirmPassword"
        );


    const message =
        document.getElementById(
            "matchMessage"
        );


    if (
        !password ||
        !confirm ||
        !message
    ) {
        return;
    }


    function checkMatch() {

        if (
            confirm.value === ""
        ) {

            message.className =
                "rc-match";

            message.textContent =
                "";

            confirm.setCustomValidity(
                ""
            );

            return;

        }


        message.classList.add(
            "show"
        );


        if (
            password.value ===
            confirm.value
        ) {

            message.classList.add(
                "good"
            );

            message.classList.remove(
                "bad"
            );

            message.textContent =
                "✓ Passwords match.";

            confirm.setCustomValidity(
                ""
            );

        } else {

            message.classList.add(
                "bad"
            );

            message.classList.remove(
                "good"
            );

            message.textContent =
                "✕ Passwords do not match.";

            confirm.setCustomValidity(
                "Passwords do not match."
            );

        }

    }


    password.addEventListener(
        "input",
        checkMatch
    );


    confirm.addEventListener(
        "input",
        checkMatch
    );

})();


/* ============================================================
   SUBMIT ANIMATION
============================================================ */

(function () {

    const form =
        document.getElementById(
            "registerForm"
        );


    const button =
        document.getElementById(
            "registerButton"
        );


    if (
        !form ||
        !button
    ) {
        return;
    }


    form.addEventListener(
        "submit",
        function () {

            if (
                !form.checkValidity()
            ) {
                return;
            }


            button.disabled =
                true;


            button.style.opacity =
                ".75";


            button.textContent =
                "⏳ Creating your account...";

        }
    );

})();


/* ============================================================
   BUTTON CLICK RIPPLE
============================================================ */

document.addEventListener(
    "click",
    function (
        event
    ) {

        const target =
            event.target.closest(
                "button, a"
            );


        if (
            !target
        ) {
            return;
        }


        const rect =
            target.getBoundingClientRect();


        const ripple =
            document.createElement(
                "span"
            );


        ripple.style.position =
            "fixed";


        ripple.style.left =
            (
                rect.left +
                rect.width /
                2
            ) +
            "px";


        ripple.style.top =
            (
                rect.top +
                rect.height /
                2
            ) +
            "px";


        ripple.style.width =
            "8px";


        ripple.style.height =
            "8px";


        ripple.style.border =
            "2px solid rgba(96,165,250,.55)";


        ripple.style.borderRadius =
            "50%";


        ripple.style.pointerEvents =
            "none";


        ripple.style.zIndex =
            "999999";


        ripple.style.transform =
            "translate(-50%,-50%)";


        document.body.appendChild(
            ripple
        );


        ripple.animate(

            [

                {
                    opacity:
                        .9,

                    width:
                        "8px",

                    height:
                        "8px"

                },

                {
                    opacity:
                        0,

                    width:
                        "105px",

                    height:
                        "105px"

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


        setTimeout(
            function () {

                ripple.remove();

            },
            600
        );

    },
    {
        passive:
            true
    }
);

</script>


</body>

</html>