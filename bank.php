<?php
// ============================================================
// CONNECTHUB - ADVANCED BANKING PAGE
// ============================================================
// COMPLETE REPLACEMENT FOR bank.php
//
// FEATURES
// ------------------------------------------------------------
// • Premium blue / cyan banking interface
// • Large professional PIN lock screen
// • Banking PIN creation
// • PIN verification
// • Automatic relocking when returning from another page
// • Account overview
// • Account number display
// • Balance visibility toggle
// • Copy account number
// • Banking status indicator
// • Security status bar
// • Quick statistics
// • Send money
// • Recipient preview
// • Game earnings
// • Game earnings deposit
// • Deposit / Withdraw
// • Quick amount buttons
// • Transaction history
// • Dark readable transaction bars
// • Transaction badges
// • Empty-state panels
// • Success / error notifications
// • Responsive design
// • Animated blue glow
// • Animated scan lines
// • No dull white history bars
// ============================================================


require "config.php";

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
// VARIABLES
// ============================================================

$successMessage = "";

$errorMessage = "";


// ============================================================
// BANKING PAGE RELOCK
// ------------------------------------------------------------
// If user comes from another ConnectHub page, banking is locked.
// POST requests are ignored so forms do not accidentally lock.
// ============================================================

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    $currentPath = basename(
        parse_url(
            $_SERVER["REQUEST_URI"] ?? "",
            PHP_URL_PATH
        )
    );


    $refererPath = "";

    $referer =
        $_SERVER["HTTP_REFERER"] ?? "";


    if (
        $referer !== ""
    ) {

        $parsedReferer =
            parse_url(
                $referer,
                PHP_URL_PATH
            );


        if (
            $parsedReferer
        ) {

            $refererPath =
                basename(
                    $parsedReferer
                );

        }

    }


    $bankingPages = [

        "bank.php",
        "bank_payment.php",
        "bank_enter.php",
        "transfer.php"

    ];


    if (
        $currentPath === "bank.php" &&
        !in_array(
            $refererPath,
            $bankingPages,
            true
        ) &&
        !isset($_GET["unlock"])
    ) {

        unset(
            $_SESSION["bank_unlocked"],
            $_SESSION["bank_unlock_token"],
            $_SESSION["bank_page_verified"]
        );

    }

}


// ============================================================
// GET BANK ACCOUNT
// ============================================================

function bankGetAccount(
    $conn,
    int $uid
) {

    $stmt = $conn->prepare("
        SELECT
            id,
            user_id,
            account_number,
            balance,
            pin_hash
        FROM bank_accounts
        WHERE user_id = ?
        LIMIT 1
    ");


    if (!$stmt) {

        return null;

    }


    $stmt->bind_param(
        "i",
        $uid
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    $account =
        $result
            ? $result->fetch_assoc()
            : null;


    $stmt->close();


    return $account;

}


// ============================================================
// GET GAME EARNINGS
// ============================================================

function bankGetGameEarnings(
    $conn,
    int $uid
): float {

    $total = 0.00;


    $stmt = $conn->prepare("
        SELECT
            COALESCE(
                SUM(
                    CAST(
                        amount AS DECIMAL(15,2)
                    )
                ),
                0
            ) AS total
        FROM game_earnings
        WHERE user_id = ?
        AND LOWER(
            TRIM(status)
        ) = 'available'
    ");


    if (!$stmt) {

        return 0.00;

    }


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


        if ($row) {

            $total =
                (float)(
                    $row["total"] ?? 0
                );

        }

    }


    $stmt->close();


    return $total;

}


// ============================================================
// PROFILE IMAGE PATH
// ============================================================

function bankProfilePath(
    $value
): string {

    $value =
        trim(
            (string)$value
        );


    if ($value === "") {

        return "";

    }


    $value =
        str_replace(
            "\\",
            "/",
            $value
        );


    $value =
        ltrim(
            $value,
            "/"
        );


    if (
        strpos(
            $value,
            "uploads/"
        ) === 0
    ) {

        return $value;

    }


    if (
        strpos(
            $value,
            "/"
        ) === false
    ) {

        return "uploads/" .
            basename(
                $value
            );

    }


    return $value;

}


// ============================================================
// USER INITIAL
// ============================================================

function bankUserInitial(
    $name
): string {

    $name =
        trim(
            (string)$name
        );


    if (
        $name === ""
    ) {

        return "U";

    }


    return strtoupper(
        mb_substr(
            $name,
            0,
            1
        )
    );

}


// ============================================================
// INITIAL ACCOUNT
// ============================================================

$account =
    bankGetAccount(
        $conn,
        $uid
    );


if (!$account) {

    die(
        "Bank account not found. Please create a bank account first."
    );

}


$accountId =
    (int)$account["id"];


// ============================================================
// INITIAL GAME EARNINGS
// ============================================================

$gameEarnings =
    bankGetGameEarnings(
        $conn,
        $uid
    );


// ============================================================
// CHECK BANK UNLOCK
// ============================================================

$bankUnlocked =
    (
        isset(
            $_SESSION["bank_unlocked"]
        ) &&
        $_SESSION["bank_unlocked"] === true
    );


// ============================================================
// CREATE / SET PIN
// ============================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["set_pin"])
) {

    $newPin =
        trim(
            (string)(
                $_POST["new_pin"] ?? ""
            )
        );


    $confirmPin =
        trim(
            (string)(
                $_POST["confirm_pin"] ?? ""
            )
        );


    if (
        !preg_match(
            '/^\d{4}$/',
            $newPin
        )
    ) {

        $errorMessage =
            "Banking PIN must contain exactly 4 digits.";

    } elseif (
        $newPin !== $confirmPin
    ) {

        $errorMessage =
            "The PIN confirmation does not match.";

    } else {

        $pinHash =
            password_hash(
                $newPin,
                PASSWORD_DEFAULT
            );


        $stmt =
            $conn->prepare("
                UPDATE bank_accounts
                SET pin_hash = ?
                WHERE id = ?
                AND user_id = ?
            ");


        if (!$stmt) {

            $errorMessage =
                "Unable to prepare PIN creation.";

        } else {

            $stmt->bind_param(
                "sii",
                $pinHash,
                $accountId,
                $uid
            );


            if (
                $stmt->execute()
            ) {

                $stmt->close();


                unset(
                    $_SESSION["bank_unlocked"],
                    $_SESSION["bank_unlock_token"],
                    $_SESSION["bank_page_verified"]
                );


                header(
                    "Location: bank.php"
                );

                exit;

            }


            $errorMessage =
                "Unable to create Banking PIN.";


            $stmt->close();

        }

    }

}


// ============================================================
// VERIFY PIN
// ============================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["verify_bank_pin"])
) {

    $pin =
        trim(
            (string)(
                $_POST["bank_pin"] ?? ""
            )
        );


    $account =
        bankGetAccount(
            $conn,
            $uid
        );


    if (
        $account &&
        !empty(
            $account["pin_hash"]
        ) &&
        preg_match(
            '/^\d{4}$/',
            $pin
        ) &&
        password_verify(
            $pin,
            $account["pin_hash"]
        )
    ) {

        $_SESSION["bank_unlocked"] =
            true;


        $_SESSION["bank_page_verified"] =
            true;


        $_SESSION["bank_unlock_token"] =
            bin2hex(
                random_bytes(
                    32
                )
            );


        header(
            "Location: bank.php?unlock=1"
        );

        exit;

    } else {

        $errorMessage =
            "Incorrect Banking PIN. Please try again.";

    }

}


// ============================================================
// CHECK AGAIN
// ============================================================

$bankUnlocked =
    (
        isset(
            $_SESSION["bank_unlocked"]
        ) &&
        $_SESSION["bank_unlocked"] === true
    );


// ============================================================
// REFRESH ACCOUNT
// ============================================================

$account =
    bankGetAccount(
        $conn,
        $uid
    );


if (!$account) {

    die(
        "Bank account not found."
    );

}


$accountId =
    (int)$account["id"];


// ============================================================
// SEND MONEY
// ============================================================

if (
    $bankUnlocked &&
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["send_money"])
) {

    $receiverId =
        (int)(
            $_POST["receiver_id"] ?? 0
        );


    $amount =
        (float)(
            $_POST["send_amount"] ?? 0
        );


    $pin =
        trim(
            (string)(
                $_POST["send_pin"] ?? ""
            )
        );


    if (
        $receiverId <= 0
    ) {

        $errorMessage =
            "Please select a recipient.";

    } elseif (
        $receiverId === $uid
    ) {

        $errorMessage =
            "You cannot send money to yourself.";

    } elseif (
        $amount <= 0
    ) {

        $errorMessage =
            "Please enter a valid amount.";

    } elseif (
        $amount >
        99999999
    ) {

        $errorMessage =
            "The entered amount is too large.";

    } elseif (
        empty(
            $account["pin_hash"]
        ) ||
        !password_verify(
            $pin,
            $account["pin_hash"]
        )
    ) {

        $errorMessage =
            "Incorrect Banking PIN. Money was not sent.";

    } else {

        $conn->begin_transaction();


        try {

            // ------------------------------------------------
            // GET RECEIVER ACCOUNT
            // ------------------------------------------------

            $stmt =
                $conn->prepare("
                    SELECT
                        id,
                        user_id,
                        account_number,
                        balance
                    FROM bank_accounts
                    WHERE user_id = ?
                    LIMIT 1
                ");


            if (!$stmt) {

                throw new Exception(
                    "Unable to find recipient account."
                );

            }


            $stmt->bind_param(
                "i",
                $receiverId
            );


            $stmt->execute();


            $result =
                $stmt->get_result();


            $receiver =
                $result
                    ? $result->fetch_assoc()
                    : null;


            $stmt->close();


            if (!$receiver) {

                throw new Exception(
                    "Selected user does not have a Banking account."
                );

            }


            $receiverAccountId =
                (int)$receiver["id"];


            if (
                $receiverAccountId ===
                $accountId
            ) {

                throw new Exception(
                    "Invalid recipient account."
                );

            }


            // ------------------------------------------------
            // LOCK BOTH ACCOUNTS IN DETERMINISTIC ORDER
            // ------------------------------------------------

            $firstAccountId =
                min(
                    $accountId,
                    $receiverAccountId
                );


            $secondAccountId =
                max(
                    $accountId,
                    $receiverAccountId
                );


            $stmt =
                $conn->prepare("
                    SELECT
                        id,
                        user_id,
                        account_number,
                        balance
                    FROM bank_accounts
                    WHERE id IN (?, ?)
                    FOR UPDATE
                ");


            if (!$stmt) {

                throw new Exception(
                    "Unable to secure bank accounts."
                );

            }


            $stmt->bind_param(
                "ii",
                $firstAccountId,
                $secondAccountId
            );


            $stmt->execute();


            $result =
                $stmt->get_result();


            $lockedAccounts = [];


            while (
                $row =
                $result->fetch_assoc()
            ) {

                $lockedAccounts[
                    (int)$row["id"]
                ] =
                    $row;

            }


            $stmt->close();


            if (
                !isset(
                    $lockedAccounts[$accountId]
                ) ||
                !isset(
                    $lockedAccounts[
                        $receiverAccountId
                    ]
                )
            ) {

                throw new Exception(
                    "Unable to lock bank accounts."
                );

            }


            $sender =
                $lockedAccounts[
                    $accountId
                ];


            $receiverLocked =
                $lockedAccounts[
                    $receiverAccountId
                ];


            $senderBalance =
                (float)$sender["balance"];


            $receiverBalance =
                (float)$receiverLocked["balance"];


            // ------------------------------------------------
            // BALANCE CHECK
            // ------------------------------------------------

            if (
                $amount >
                $senderBalance
            ) {

                throw new Exception(
                    "Insufficient bank balance."
                );

            }


            // ------------------------------------------------
            // RECEIVER NAME
            // ------------------------------------------------

            $stmt =
                $conn->prepare("
                    SELECT
                        name
                    FROM users
                    WHERE id = ?
                    LIMIT 1
                ");


            if (!$stmt) {

                throw new Exception(
                    "Unable to load recipient details."
                );

            }


            $stmt->bind_param(
                "i",
                $receiverId
            );


            $stmt->execute();


            $result =
                $stmt->get_result();


            $receiverUser =
                $result
                    ? $result->fetch_assoc()
                    : null;


            $stmt->close();


            $receiverName =
                trim(
                    (string)(
                        $receiverUser["name"]
                        ??
                        "ConnectHub User"
                    )
                );


            if (
                $receiverName === ""
            ) {

                $receiverName =
                    "ConnectHub User";

            }


            // ------------------------------------------------
            // NEW BALANCES
            // ------------------------------------------------

            $newSenderBalance =
                round(
                    $senderBalance -
                    $amount,
                    2
                );


            $newReceiverBalance =
                round(
                    $receiverBalance +
                    $amount,
                    2
                );


            // ------------------------------------------------
            // UPDATE SENDER
            // ------------------------------------------------

            $stmt =
                $conn->prepare("
                    UPDATE bank_accounts
                    SET balance = ?
                    WHERE id = ?
                    AND user_id = ?
                ");


            if (!$stmt) {

                throw new Exception(
                    "Unable to update sender account."
                );

            }


            $stmt->bind_param(
                "dii",
                $newSenderBalance,
                $accountId,
                $uid
            );


            if (
                !$stmt->execute()
            ) {

                $stmt->close();


                throw new Exception(
                    "Unable to update sender balance."
                );

            }


            $stmt->close();


            // ------------------------------------------------
            // UPDATE RECEIVER
            // ------------------------------------------------

            $stmt =
                $conn->prepare("
                    UPDATE bank_accounts
                    SET balance = ?
                    WHERE id = ?
                    AND user_id = ?
                ");


            if (!$stmt) {

                throw new Exception(
                    "Unable to update receiver account."
                );

            }


            $stmt->bind_param(
                "dii",
                $newReceiverBalance,
                $receiverAccountId,
                $receiverId
            );


            if (
                !$stmt->execute()
            ) {

                $stmt->close();


                throw new Exception(
                    "Unable to update receiver balance."
                );

            }


            $stmt->close();


            // ------------------------------------------------
            // SENDER TRANSACTION
            // ------------------------------------------------

            $type =
                "TRANSFER";


            $reference =
                "Money sent to " .
                $receiverName .
                " (User ID: " .
                $receiverId .
                ")";


            $stmt =
                $conn->prepare("
                    INSERT INTO transactions
                    (
                        account_id,
                        type,
                        amount,
                        reference
                    )
                    VALUES (?, ?, ?, ?)
                ");


            if (!$stmt) {

                throw new Exception(
                    "Unable to create sender transaction."
                );

            }


            $stmt->bind_param(
                "isds",
                $accountId,
                $type,
                $amount,
                $reference
            );


            if (
                !$stmt->execute()
            ) {

                $stmt->close();


                throw new Exception(
                    "Unable to save sender transaction."
                );

            }


            $stmt->close();


            // ------------------------------------------------
            // RECEIVER TRANSACTION
            // ------------------------------------------------

            $receiverReference =
                "Money received from ConnectHub User #" .
                $uid;


            $stmt =
                $conn->prepare("
                    INSERT INTO transactions
                    (
                        account_id,
                        type,
                        amount,
                        reference
                    )
                    VALUES (?, ?, ?, ?)
                ");


            if (!$stmt) {

                throw new Exception(
                    "Unable to create receiver transaction."
                );

            }


            $stmt->bind_param(
                "isds",
                $receiverAccountId,
                $type,
                $amount,
                $receiverReference
            );


            if (
                !$stmt->execute()
            ) {

                $stmt->close();


                throw new Exception(
                    "Unable to save receiver transaction."
                );

            }


            $stmt->close();


            // ------------------------------------------------
            // COMMIT
            // ------------------------------------------------

            $conn->commit();


            header(
                "Location: bank.php?unlock=1&success=" .
                urlencode(
                    "₹" .
                    number_format(
                        $amount,
                        2
                    ) .
                    " sent successfully to " .
                    $receiverName .
                    "."
                )
            );


            exit;

        } catch (
            Throwable $e
        ) {

            $conn->rollback();


            $errorMessage =
                $e->getMessage();

        }

    }

}


// ============================================================
// GAME EARNINGS DEPOSIT
// ============================================================

if (
    $bankUnlocked &&
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset(
        $_POST["withdraw_game_earnings"]
    )
) {

    $amount =
        (float)(
            $_POST["game_amount"] ?? 0
        );


    $pin =
        trim(
            (string)(
                $_POST["game_pin"] ?? ""
            )
        );


    $gameEarnings =
        bankGetGameEarnings(
            $conn,
            $uid
        );


    if (
        empty(
            $account["pin_hash"]
        ) ||
        !password_verify(
            $pin,
            $account["pin_hash"]
        )
    ) {

        $errorMessage =
            "Incorrect Banking PIN. Game earnings transfer cancelled.";

    } elseif (
        $amount <= 0
    ) {

        $errorMessage =
            "Please enter a valid amount.";

    } elseif (
        $amount >
        $gameEarnings + 0.00001
    ) {

        $errorMessage =
            "Insufficient Game Earnings.";

    } else {

        $conn->begin_transaction();


        try {

            // ------------------------------------------------
            // LOCK BANK
            // ------------------------------------------------

            $stmt =
                $conn->prepare("
                    SELECT
                        balance
                    FROM bank_accounts
                    WHERE id = ?
                    AND user_id = ?
                    FOR UPDATE
                ");


            if (!$stmt) {

                throw new Exception(
                    "Unable to lock bank account."
                );

            }


            $stmt->bind_param(
                "ii",
                $accountId,
                $uid
            );


            $stmt->execute();


            $result =
                $stmt->get_result();


            $lockedAccount =
                $result
                    ? $result->fetch_assoc()
                    : null;


            $stmt->close();


            if (!$lockedAccount) {

                throw new Exception(
                    "Bank account not found."
                );

            }


            // ------------------------------------------------
            // LOCK AVAILABLE EARNINGS
            // ------------------------------------------------

            $stmt =
                $conn->prepare("
                    SELECT
                        id,
                        amount
                    FROM game_earnings
                    WHERE user_id = ?
                    AND LOWER(
                        TRIM(status)
                    ) = 'available'
                    ORDER BY id ASC
                    FOR UPDATE
                ");


            if (!$stmt) {

                throw new Exception(
                    "Unable to lock game earnings."
                );

            }


            $stmt->bind_param(
                "i",
                $uid
            );


            $stmt->execute();


            $result =
                $stmt->get_result();


            $remaining =
                round(
                    $amount,
                    2
                );


            $records = [];


            while (
                $earning =
                $result->fetch_assoc()
            ) {

                if (
                    $remaining <=
                    0.00001
                ) {

                    break;

                }


                $earningId =
                    (int)$earning["id"];


                $earningAmount =
                    round(
                        (float)$earning["amount"],
                        2
                    );


                if (
                    $earningAmount <=
                    $remaining +
                    0.00001
                ) {

                    $records[] = [

                        "id" =>
                            $earningId,

                        "used" =>
                            $earningAmount

                    ];


                    $remaining =
                        round(
                            $remaining -
                            $earningAmount,
                            2
                        );

                } else {

                    $records[] = [

                        "id" =>
                            $earningId,

                        "used" =>
                            $remaining

                    ];


                    $remaining =
                        0;

                }

            }


            $stmt->close();


            if (
                $remaining >
                0.00001
            ) {

                throw new Exception(
                    "Game Earnings balance changed. Please try again."
                );

            }


            // ------------------------------------------------
            // UPDATE EARNINGS
            // ------------------------------------------------

            foreach (
                $records
                as $record
            ) {

                $earningId =
                    (int)$record["id"];


                $used =
                    (float)$record["used"];


                $stmt =
                    $conn->prepare("
                        SELECT
                            amount
                        FROM game_earnings
                        WHERE id = ?
                        AND user_id = ?
                        AND LOWER(
                            TRIM(status)
                        ) = 'available'
                        FOR UPDATE
                    ");


                if (!$stmt) {

                    throw new Exception(
                        "Unable to verify game earning."
                    );

                }


                $stmt->bind_param(
                    "ii",
                    $earningId,
                    $uid
                );


                $stmt->execute();


                $result =
                    $stmt->get_result();


                $current =
                    $result
                        ? $result->fetch_assoc()
                        : null;


                $stmt->close();


                if (!$current) {

                    throw new Exception(
                        "Game earning record not found."
                    );

                }


                $currentAmount =
                    round(
                        (float)$current["amount"],
                        2
                    );


                if (
                    abs(
                        $currentAmount -
                        $used
                    ) < 0.00001
                ) {

                    $stmt =
                        $conn->prepare("
                            UPDATE game_earnings
                            SET
                                amount = 0,
                                status = 'withdrawn'
                            WHERE id = ?
                            AND user_id = ?
                            AND LOWER(
                                TRIM(status)
                            ) = 'available'
                        ");


                    if (!$stmt) {

                        throw new Exception(
                            "Unable to update game earning."
                        );

                    }


                    $stmt->bind_param(
                        "ii",
                        $earningId,
                        $uid
                    );


                    if (
                        !$stmt->execute()
                    ) {

                        $stmt->close();


                        throw new Exception(
                            "Unable to update game earning."
                        );

                    }


                    $stmt->close();

                } else {

                    $newAmount =
                        round(
                            $currentAmount -
                            $used,
                            2
                        );


                    $stmt =
                        $conn->prepare("
                            UPDATE game_earnings
                            SET amount = ?
                            WHERE id = ?
                            AND user_id = ?
                            AND LOWER(
                                TRIM(status)
                            ) = 'available'
                        ");


                    if (!$stmt) {

                        throw new Exception(
                            "Unable to update partial game earning."
                        );

                    }


                    $stmt->bind_param(
                        "dii",
                        $newAmount,
                        $earningId,
                        $uid
                    );


                    if (
                        !$stmt->execute()
                    ) {

                        $stmt->close();


                        throw new Exception(
                            "Unable to update game earnings."
                        );

                    }


                    $stmt->close();

                }

            }


            // ------------------------------------------------
            // ADD TO BANK
            // ------------------------------------------------

            $currentBalance =
                round(
                    (float)$lockedAccount["balance"],
                    2
                );


            $newBalance =
                round(
                    $currentBalance +
                    $amount,
                    2
                );


            $stmt =
                $conn->prepare("
                    UPDATE bank_accounts
                    SET balance = ?
                    WHERE id = ?
                    AND user_id = ?
                ");


            if (!$stmt) {

                throw new Exception(
                    "Unable to update bank balance."
                );

            }


            $stmt->bind_param(
                "dii",
                $newBalance,
                $accountId,
                $uid
            );


            if (
                !$stmt->execute()
            ) {

                $stmt->close();


                throw new Exception(
                    "Unable to update bank balance."
                );

            }


            $stmt->close();


            // ------------------------------------------------
            // TRANSACTION HISTORY
            // ------------------------------------------------

            $type =
                "DEPOSIT";


            $reference =
                "Game Earnings - ConnectHub Games";


            $stmt =
                $conn->prepare("
                    INSERT INTO transactions
                    (
                        account_id,
                        type,
                        amount,
                        reference
                    )
                    VALUES (?, ?, ?, ?)
                ");


            if (!$stmt) {

                throw new Exception(
                    "Unable to create transaction history."
                );

            }


            $stmt->bind_param(
                "isds",
                $accountId,
                $type,
                $amount,
                $reference
            );


            if (
                !$stmt->execute()
            ) {

                $stmt->close();


                throw new Exception(
                    "Unable to save transaction history."
                );

            }


            $stmt->close();


            $conn->commit();


            header(
                "Location: bank.php?unlock=1&success=" .
                urlencode(
                    "₹" .
                    number_format(
                        $amount,
                        2
                    ) .
                    " Game Earnings deposited successfully."
                )
            );


            exit;

        } catch (
            Throwable $e
        ) {

            $conn->rollback();


            $errorMessage =
                $e->getMessage();

        }

    }

}


// ============================================================
// NORMAL DEPOSIT / WITHDRAW
// ============================================================

if (
    $bankUnlocked &&
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    in_array(
        $_POST["action"],
        [
            "deposit",
            "withdraw"
        ],
        true
    )
) {

    $action =
        $_POST["action"];


    $amount =
        (float)(
            $_POST["amount"] ?? 0
        );


    $pin =
        trim(
            (string)(
                $_POST["transaction_pin"] ?? ""
            )
        );


    if (
        empty(
            $account["pin_hash"]
        ) ||
        !password_verify(
            $pin,
            $account["pin_hash"]
        )
    ) {

        $errorMessage =
            "Incorrect Banking PIN. Transaction cancelled.";

    } elseif (
        $amount <= 0
    ) {

        $errorMessage =
            "Please enter a valid amount.";

    } else {

        $conn->begin_transaction();


        try {

            // ------------------------------------------------
            // LOCK ACCOUNT
            // ------------------------------------------------

            $stmt =
                $conn->prepare("
                    SELECT
                        balance
                    FROM bank_accounts
                    WHERE id = ?
                    AND user_id = ?
                    FOR UPDATE
                ");


            if (!$stmt) {

                throw new Exception(
                    "Unable to lock account."
                );

            }


            $stmt->bind_param(
                "ii",
                $accountId,
                $uid
            );


            $stmt->execute();


            $result =
                $stmt->get_result();


            $lockedAccount =
                $result
                    ? $result->fetch_assoc()
                    : null;


            $stmt->close();


            if (!$lockedAccount) {

                throw new Exception(
                    "Bank account not found."
                );

            }


            $currentBalance =
                round(
                    (float)$lockedAccount["balance"],
                    2
                );


            // ------------------------------------------------
            // DEPOSIT
            // ------------------------------------------------

            if (
                $action ===
                "deposit"
            ) {

                $newBalance =
                    round(
                        $currentBalance +
                        $amount,
                        2
                    );


                $type =
                    "DEPOSIT";


                $reference =
                    "Cash Deposit by ConnectHub user #" .
                    $uid;

            } else {

                // ------------------------------------------------
                // WITHDRAW
                // ------------------------------------------------

                if (
                    $amount >
                    $currentBalance
                ) {

                    throw new Exception(
                        "Insufficient bank balance."
                    );

                }


                $newBalance =
                    round(
                        $currentBalance -
                        $amount,
                        2
                    );


                $type =
                    "WITHDRAW";


                $reference =
                    "Cash Withdrawal by ConnectHub user #" .
                    $uid;

            }


            // ------------------------------------------------
            // UPDATE ACCOUNT
            // ------------------------------------------------

            $stmt =
                $conn->prepare("
                    UPDATE bank_accounts
                    SET balance = ?
                    WHERE id = ?
                    AND user_id = ?
                ");


            if (!$stmt) {

                throw new Exception(
                    "Unable to update bank account."
                );

            }


            $stmt->bind_param(
                "dii",
                $newBalance,
                $accountId,
                $uid
            );


            if (
                !$stmt->execute()
            ) {

                $stmt->close();


                throw new Exception(
                    "Unable to update account balance."
                );

            }


            $stmt->close();


            // ------------------------------------------------
            // TRANSACTION RECORD
            // ------------------------------------------------

            $stmt =
                $conn->prepare("
                    INSERT INTO transactions
                    (
                        account_id,
                        type,
                        amount,
                        reference
                    )
                    VALUES (?, ?, ?, ?)
                ");


            if (!$stmt) {

                throw new Exception(
                    "Unable to create transaction."
                );

            }


            $stmt->bind_param(
                "isds",
                $accountId,
                $type,
                $amount,
                $reference
            );


            if (
                !$stmt->execute()
            ) {

                $stmt->close();


                throw new Exception(
                    "Unable to save transaction."
                );

            }


            $stmt->close();


            // ------------------------------------------------
            // COMMIT
            // ------------------------------------------------

            $conn->commit();


            header(
                "Location: bank.php?unlock=1&success=" .
                urlencode(
                    "₹" .
                    number_format(
                        $amount,
                        2
                    ) .
                    " " .
                    ucfirst(
                        $action
                    ) .
                    " successful."
                )
            );


            exit;

        } catch (
            Throwable $e
        ) {

            $conn->rollback();


            $errorMessage =
                $e->getMessage();

        }

    }

}


// ============================================================
// SUCCESS MESSAGE
// ============================================================

if (
    isset(
        $_GET["success"]
    )
) {

    $successMessage =
        trim(
            (string)(
                $_GET["success"]
            )
        );

}


// ============================================================
// REFRESH DATA
// ============================================================

$account =
    bankGetAccount(
        $conn,
        $uid
    );


if (!$account) {

    die(
        "Bank account not found."
    );

}


$accountId =
    (int)$account["id"];


$gameEarnings =
    bankGetGameEarnings(
        $conn,
        $uid
    );


// ============================================================
// GET USERS
// ============================================================

$bankUsers = [];


if (
    $bankUnlocked
) {

    $stmt =
        $conn->prepare("
            SELECT
                u.id,
                u.name,
                u.profile_image,
                b.account_number
            FROM users u
            INNER JOIN bank_accounts b
                ON b.user_id = u.id
            WHERE u.id != ?
            ORDER BY u.name ASC
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

            while (
                $row =
                $result->fetch_assoc()
            ) {

                $bankUsers[] =
                    $row;

            }

        }


        $stmt->close();

    }

}


// ============================================================
// TRANSACTION HISTORY
// ============================================================

$transactions = [];


if (
    $bankUnlocked
) {

    $stmt =
        $conn->prepare("
            SELECT
                id,
                type,
                amount,
                reference,
                created_at
            FROM transactions
            WHERE account_id = ?
            ORDER BY id DESC
            LIMIT 100
        ");


    if ($stmt) {

        $stmt->bind_param(
            "i",
            $accountId
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        if ($result) {

            while (
                $row =
                $result->fetch_assoc()
            ) {

                $transactions[] =
                    $row;

            }

        }


        $stmt->close();

    }

}


// ============================================================
// TRANSACTION COUNT
// ============================================================

$transactionCount =
    count(
        $transactions
    );


// ============================================================
// BANK USER DISPLAY
// ============================================================

$currentUserInitial =
    bankUserInitial(
        $_SESSION["name"] ?? "User"
    );


// ============================================================
// LOAD HEADER
// ============================================================

require "header.php";

?>

<style>

/* ============================================================
   CONNECTHUB ADVANCED BANKING
============================================================ */

.bank-shell {

    width: 100%;

    max-width: 1180px;

    margin: 0 auto;

    padding:
        25px
        22px
        80px;

}


/* ============================================================
   ALERTS
============================================================ */

.bank-notice {

    position: relative;

    display: flex;

    align-items: center;

    gap: 13px;

    margin-bottom: 16px;

    padding:
        13px
        16px;

    overflow: hidden;

    border-radius: 14px;

    backdrop-filter:
        blur(12px);

}


.bank-notice::after {

    content: "";

    position: absolute;

    left: -30%;

    bottom: 0;

    width: 25%;

    height: 1px;

    animation:
        noticeScan
        4s
        linear
        infinite;

}


@keyframes noticeScan {

    from {
        left: -30%;
    }

    to {
        left: 120%;
    }

}


.bank-notice-icon {

    width: 38px;

    height: 38px;

    flex: 0 0 38px;

    display: grid;

    place-items: center;

    border-radius: 11px;

    font-size: 17px;

}


.bank-notice strong {

    display: block;

    font-size: 10px;

}


.bank-notice p {

    margin:
        3px
        0
        0;

    font-size: 8px;

    line-height: 1.5;

}


.bank-notice.success {

    color:
        #dcfce7;

    border:
        1px
        solid
        rgba(
            34,
            197,
            94,
            .20
        );

    background:
        rgba(
            20,
            83,
            45,
            .40
        );

    box-shadow:
        0
        10px
        28px
        rgba(
            0,
            0,
            0,
            .15
        );

}


.bank-notice.success
.bank-notice-icon {

    background:
        rgba(
            34,
            197,
            94,
            .15
        );

}


.bank-notice.success::after {

    background:
        linear-gradient(
            90deg,
            transparent,
            #4ade80,
            transparent
        );

}


.bank-notice.error {

    color:
        #fee2e2;

    border:
        1px
        solid
        rgba(
            239,
            68,
            68,
            .20
        );

    background:
        rgba(
            127,
            29,
            29,
            .40
        );

}


.bank-notice.error
.bank-notice-icon {

    background:
        rgba(
            239,
            68,
            68,
            .15
        );

}


.bank-notice.error::after {

    background:
        linear-gradient(
            90deg,
            transparent,
            #f87171,
            transparent
        );

}


/* ============================================================
   SECURITY LOCK SCREEN
============================================================ */

.bank-security-stage {

    position: relative;

    min-height:
        calc(
            100vh -
            155px
        );

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        45px
        10px;

    overflow:
        hidden;

}


.bank-security-orb {

    position: absolute;

    border-radius: 50%;

    pointer-events: none;

    filter:
        blur(4px);

}


.bank-security-orb.one {

    width:
        370px;

    height:
        370px;

    top:
        -130px;

    right:
        -100px;

    background:
        rgba(
            37,
            99,
            235,
            .17
        );

    animation:
        securityOrbOne
        9s
        ease-in-out
        infinite
        alternate;

}


.bank-security-orb.two {

    width:
        310px;

    height:
        310px;

    bottom:
        -120px;

    left:
        -80px;

    background:
        rgba(
            124,
            58,
            237,
            .14
        );

    animation:
        securityOrbTwo
        11s
        ease-in-out
        infinite
        alternate;

}


.bank-security-orb.three {

    width:
        170px;

    height:
        170px;

    top:
        35%;

    left:
        28%;

    background:
        rgba(
            34,
            211,
            238,
            .07
        );

    animation:
        securityOrbThree
        7s
        ease-in-out
        infinite
        alternate;

}


@keyframes securityOrbOne {

    to {

        transform:
            translate(
                -90px,
                110px
            )
            scale(
                1.16
            );

    }

}


@keyframes securityOrbTwo {

    to {

        transform:
            translate(
                100px,
                -80px
            )
            scale(
                1.12
            );

    }

}


@keyframes securityOrbThree {

    to {

        transform:
            translate(
                55px,
                -40px
            )
            scale(
                1.25
            );

    }

}


/* ============================================================
   SECURITY CARD
============================================================ */

.bank-security-card {

    position:
        relative;

    z-index:
        5;

    width:
        min(
            550px,
            100%
        );

    padding:
        36px
        34px;

    text-align:
        center;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .19
        );

    border-radius:
        28px;

    background:
        linear-gradient(
            145deg,
            rgba(
                3,
                12,
                29,
                .96
            ),
            rgba(
                8,
                24,
                52,
                .92
            )
        );

    box-shadow:
        0
        30px
        90px
        rgba(
            0,
            0,
            0,
            .37
        ),
        0
        0
        55px
        rgba(
            37,
            99,
            235,
            .07
        );

    backdrop-filter:
        blur(
            20px
        );

}


.bank-security-card::before {

    content: "";

    position:
        absolute;

    inset:
        0;

    pointer-events:
        none;

    background:
        linear-gradient(
            120deg,
            transparent 0%,
            rgba(
                96,
                165,
                250,
                .05
            ) 35%,
            transparent 55%
        );

    animation:
        securitySweep
        6s
        linear
        infinite;

}


@keyframes securitySweep {

    from {

        transform:
            translateX(
                -110%
            );

    }

    to {

        transform:
            translateX(
                110%
            );

    }

}


/* ============================================================
   SECURITY LOGO
============================================================ */

.bank-security-logo {

    position:
        relative;

    width:
        94px;

    height:
        94px;

    margin:
        0
        auto
        18px;

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
            .28
        );

    border-radius:
        28px;

    background:
        linear-gradient(
            135deg,
            #1d4ed8,
            #2563eb,
            #4f46e5
        );

    box-shadow:
        0
        0
        40px
        rgba(
            37,
            99,
            235,
            .30
        );

    animation:
        bankLockFloat
        3s
        ease-in-out
        infinite;

}


.bank-security-logo::before {

    content: "";

    position:
        absolute;

    inset:
        -9px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .12
        );

    border-radius:
        34px;

    animation:
        bankLockRing
        4s
        linear
        infinite;

}


.bank-security-logo span {

    font-size:
        43px;

}


@keyframes bankLockFloat {

    50% {

        transform:
            translateY(
                -5px
            );

        box-shadow:
            0
            0
            55px
            rgba(
                37,
                99,
                235,
                .40
            );

    }

}


@keyframes bankLockRing {

    to {

        transform:
            rotate(
                360deg
            );

    }

}


/* ============================================================
   SECURITY BADGE
============================================================ */

.bank-security-badge {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        6px;

    padding:
        7px
        10px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .15
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
        1.6px;

}


.bank-security-badge span {

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
            .9
        );

    animation:
        lockDot
        1.5s
        ease-in-out
        infinite;

}


@keyframes lockDot {

    50% {

        transform:
            scale(
                1.4
            );

    }

}


/* ============================================================
   SECURITY TITLE
============================================================ */

.bank-security-card h1 {

    margin:
        14px
        0
        8px;

    color:
        #f8fafc;

    font-size:
        29px;

    font-weight:
        950;

}


.bank-security-card > p {

    max-width:
        430px;

    margin:
        0
        auto
        24px;

    color:
        #64748b;

    font-size:
        10px;

    line-height:
        1.7;

}


/* ============================================================
   SECURITY BARS
============================================================ */

.security-feature-grid {

    display:
        grid;

    grid-template-columns:
        repeat(
            3,
            1fr
        );

    gap:
        8px;

    margin-bottom:
        22px;

}


.security-feature {

    padding:
        11px
        8px;

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
        12px;

    background:
        rgba(
            15,
            23,
            42,
            .65
        );

}


.security-feature-icon {

    font-size:
        20px;

}


.security-feature strong {

    display:
        block;

    margin-top:
        5px;

    color:
        #e2e8f0;

    font-size:
        8px;

}


.security-feature small {

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
   PIN LABEL
============================================================ */

.security-input-label {

    display:
        block;

    margin:
        0
        0
        7px;

    text-align:
        left;

    color:
        #cbd5e1;

    font-size:
        9px;

    font-weight:
        900;

}


/* ============================================================
   BIG PIN BAR
============================================================ */

.big-pin-input {

    width:
        100%;

    height:
        70px;

    padding:
        10px
        20px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .18
        ) !important;

    border-radius:
        16px;

    outline:
        none !important;

    color:
        #ffffff !important;

    background:
        linear-gradient(
            135deg,
            rgba(
                15,
                23,
                42,
                .90
            ),
            rgba(
                13,
                33,
                66,
                .82
            )
        ) !important;

    text-align:
        center;

    font-size:
        28px;

    font-weight:
        900;

    letter-spacing:
        12px;

    box-shadow:
        inset
        0
        0
        18px
        rgba(
            37,
            99,
            235,
            .06
        );

}


.big-pin-input::placeholder {

    color:
        #334155;

    letter-spacing:
        8px;

}


.big-pin-input:focus {

    border-color:
        rgba(
            96,
            165,
            250,
            .48
        ) !important;

    box-shadow:
        0
        0
        0
        4px
        rgba(
            37,
            99,
            235,
            .10
        ),
        inset
        0
        0
        20px
        rgba(
            37,
            99,
            235,
            .08
        );

}


/* ============================================================
   SECURITY BUTTON
============================================================ */

.security-submit {

    width:
        100%;

    height:
        52px;

    margin-top:
        12px;

    border:
        1px
        solid
        rgba(
            147,
            197,
            253,
            .20
        );

    border-radius:
        13px;

    color:
        #ffffff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5,
            #7c3aed
        );

    font-size:
        11px;

    font-weight:
        950;

    cursor:
        pointer;

    box-shadow:
        0
        11px
        28px
        rgba(
            37,
            99,
            235,
            .22
        );

    transition:
        .20s
        ease;

}


.security-submit:hover {

    transform:
        translateY(
            -2px
        );

    box-shadow:
        0
        15px
        34px
        rgba(
            37,
            99,
            235,
            .32
        );

}


.security-footer {

    margin-top:
        18px;

    color:
        #475569;

    font-size:
        7px;

}


/* ============================================================
   DASHBOARD
============================================================ */

.bank-dashboard {

    width:
        100%;

}


/* ============================================================
   HERO
============================================================ */

.bank-hero {

    position:
        relative;

    overflow:
        hidden;

    margin-bottom:
        13px;

    padding:
        23px
        24px;

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
        21px;

    color:
        #ffffff;

    background:
        linear-gradient(
            135deg,
            #030b1d,
            #081a38,
            #172b6c
        );

    box-shadow:
        0
        20px
        50px
        rgba(
            0,
            0,
            0,
            .22
        );

}


.bank-hero::before {

    content:
        "";

    position:
        absolute;

    width:
        340px;

    height:
        340px;

    top:
        -190px;

    right:
        -120px;

    border-radius:
        50%;

    background:
        rgba(
            59,
            130,
            246,
            .16
        );

    filter:
        blur(
            10px
        );

    animation:
        heroOrb
        8s
        ease-in-out
        infinite
        alternate;

}


.bank-hero::after {

    content:
        "";

    position:
        absolute;

    left:
        -20%;

    bottom:
        0;

    width:
        28%;

    height:
        1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            #38bdf8,
            #818cf8,
            transparent
        );

    animation:
        bankHeroLine
        6s
        linear
        infinite;

}


@keyframes heroOrb {

    to {

        transform:
            translate(
                -70px,
                80px
            )
            scale(
                1.18
            );

    }

}


@keyframes bankHeroLine {

    from {
        left:
            -25%;
    }

    to {
        left:
            120%;
    }

}


/* ============================================================
   HERO TOP
============================================================ */

.bank-hero-top {

    position:
        relative;

    z-index:
        2;

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        flex-start;

    gap:
        18px;

}


.bank-hero-label {

    display:
        inline-flex;

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
            147,
            197,
            253,
            .13
        );

    border-radius:
        999px;

    color:
        #93c5fd;

    background:
        rgba(
            59,
            130,
            246,
            .08
        );

    font-size:
        7px;

    font-weight:
        950;

    letter-spacing:
        1.5px;

}


.bank-hero-label span {

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
        8px
        #22c55e;

}


.bank-hero h1 {

    margin:
        7px
        0
        4px;

    font-size:
        29px;

    font-weight:
        950;

}


.bank-hero-description {

    margin:
        0;

    color:
        #7f93ad;

    font-size:
        9px;

    line-height:
        1.6;

}


.bank-active-badge {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        6px;

    padding:
        8px
        10px;

    border:
        1px
        solid
        rgba(
            34,
            197,
            94,
            .16
        );

    border-radius:
        999px;

    color:
        #bbf7d0;

    background:
        rgba(
            22,
            101,
            52,
            .12
        );

    font-size:
        7px;

    font-weight:
        900;

}


.bank-active-badge::before {

    content:
        "";

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
        activeBankDot
        1.5s
        ease-in-out
        infinite;

}


@keyframes activeBankDot {

    50% {

        transform:
            scale(
                1.5
            );

    }

}


/* ============================================================
   ACCOUNT DISPLAY
============================================================ */

.bank-account-display {

    position:
        relative;

    z-index:
        2;

    display:
        grid;

    grid-template-columns:
        1fr
        1fr;

    gap:
        18px;

    margin-top:
        27px;

    padding:
        18px;

    border:
        1px
        solid
        rgba(
            147,
            197,
            253,
            .10
        );

    border-radius:
        17px;

    background:
        rgba(
            2,
            6,
            23,
            .25
        );

}


.account-info-label {

    display:
        block;

    color:
        #64748b;

    font-size:
        7px;

    font-weight:
        900;

    letter-spacing:
        1.3px;

}


.account-number-row {

    display:
        flex;

    align-items:
        center;

    gap:
        8px;

    margin-top:
        7px;

}


.bank-account-number {

    color:
        #ffffff;

    font-size:
        19px;

    font-weight:
        900;

    letter-spacing:
        3px;

}


.copy-account-button {

    width:
        31px;

    height:
        31px;

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
        9px;

    color:
        #93c5fd;

    background:
        rgba(
            37,
            99,
            235,
            .09
        );

    cursor:
        pointer;

}


.copy-account-button:hover {

    color:
        #ffffff;

    background:
        rgba(
            37,
            99,
            235,
            .18
        );

}


.balance-box {

    text-align:
        right;

}


.balance-box > span {

    display:
        block;

    color:
        #64748b;

    font-size:
        7px;

    font-weight:
        900;

    letter-spacing:
        1.2px;

}


.balance-value-row {

    display:
        flex;

    align-items:
        center;

    justify-content:
        flex-end;

    gap:
        10px;

    margin-top:
        4px;

}


.bank-balance-value {

    color:
        #ffffff;

    font-size:
        30px;

    font-weight:
        950;

}


.balance-toggle {

    width:
        32px;

    height:
        32px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .15
        );

    border-radius:
        9px;

    color:
        #93c5fd;

    background:
        rgba(
            37,
            99,
            235,
            .08
        );

    cursor:
        pointer;

}


/* ============================================================
   ACCOUNT MINI BARS
============================================================ */

.account-mini-bars {

    position:
        relative;

    z-index:
        2;

    display:
        grid;

    grid-template-columns:
        repeat(
            4,
            1fr
        );

    gap:
        7px;

    margin-top:
        10px;

}


.account-mini-bar {

    padding:
        9px
        11px;

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
        11px;

    background:
        rgba(
            2,
            6,
            23,
            .26
        );

}


.account-mini-bar span {

    display:
        block;

    color:
        #64748b;

    font-size:
        6px;

    font-weight:
        800;

}


.account-mini-bar strong {

    display:
        block;

    margin-top:
        3px;

    color:
        #dbeafe;

    font-size:
        8px;

}


/* ============================================================
   STAT GRID
============================================================ */

.bank-stat-grid {

    display:
        grid;

    grid-template-columns:
        repeat(
            4,
            1fr
        );

    gap:
        9px;

    margin-bottom:
        13px;

}


.bank-stat {

    position:
        relative;

    overflow:
        hidden;

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    padding:
        12px;

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
        13px;

    background:
        rgba(
            3,
            12,
            29,
            .70
        );

    box-shadow:
        0
        9px
        22px
        rgba(
            0,
            0,
            0,
            .13
        );

    backdrop-filter:
        blur(
            10px
        );

}


.bank-stat::before {

    content:
        "";

    position:
        absolute;

    width:
        90px;

    height:
        90px;

    top:
        -55px;

    right:
        -40px;

    border-radius:
        50%;

    background:
        rgba(
            59,
            130,
            246,
            .08
        );

}


.bank-stat-icon {

    width:
        40px;

    height:
        40px;

    flex:
        0
        0
        40px;

    display:
        grid;

    place-items:
        center;

    border-radius:
        11px;

    font-size:
        17px;

}


.bank-stat-icon.blue {

    background:
        rgba(
            37,
            99,
            235,
            .16
        );

}


.bank-stat-icon.cyan {

    background:
        rgba(
            6,
            182,
            212,
            .13
        );

}


.bank-stat-icon.purple {

    background:
        rgba(
            124,
            58,
            237,
            .14
        );

}


.bank-stat-icon.green {

    background:
        rgba(
            34,
            197,
            94,
            .13
        );

}


.bank-stat span {

    display:
        block;

    color:
        #64748b;

    font-size:
        7px;

}


.bank-stat strong {

    display:
        block;

    margin-top:
        3px;

    color:
        #e2e8f0;

    font-size:
        11px;

}


/* ============================================================
   PANEL GRID
============================================================ */

.bank-main-grid {

    display:
        grid;

    grid-template-columns:
        1.08fr
        .92fr;

    gap:
        12px;

    align-items:
        start;

}


/* ============================================================
   BANK PANEL
============================================================ */

.bank-panel {

    position:
        relative;

    overflow:
        hidden;

    margin-bottom:
        12px;

    padding:
        18px;

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
        17px;

    background:
        linear-gradient(
            145deg,
            rgba(
                3,
                12,
                29,
                .92
            ),
            rgba(
                7,
                19,
                40,
                .83
            )
        );

    box-shadow:
        0
        12px
        32px
        rgba(
            0,
            0,
            0,
            .17
        );

    backdrop-filter:
        blur(
            11px
        );

}


.bank-panel::before {

    content:
        "";

    position:
        absolute;

    width:
        120px;

    height:
        120px;

    top:
        -80px;

    right:
        -65px;

    border-radius:
        50%;

    background:
        rgba(
            37,
            99,
            235,
            .07
        );

    pointer-events:
        none;

}


/* ============================================================
   PANEL HEADER
============================================================ */

.bank-panel-header {

    position:
        relative;

    z-index:
        2;

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    margin-bottom:
        15px;

}


.bank-panel-icon {

    width:
        45px;

    height:
        45px;

    flex:
        0
        0
        45px;

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
        12px;

    font-size:
        20px;

}


.panel-icon-blue {

    background:
        rgba(
            37,
            99,
            235,
            .14
        );

}


.panel-icon-cyan {

    background:
        rgba(
            6,
            182,
            212,
            .12
        );

}


.panel-icon-purple {

    background:
        rgba(
            124,
            58,
            237,
            .13
        );

}


.panel-icon-green {

    background:
        rgba(
            34,
            197,
            94,
            .12
        );

}


.panel-header-text {

    min-width:
        0;

}


.panel-eyebrow {

    color:
        #60a5fa;

    font-size:
        6px;

    font-weight:
        950;

    letter-spacing:
        1.4px;

}


.bank-panel-header h2 {

    margin:
        3px
        0
        3px;

    color:
        #f8fafc;

    font-size:
        17px;

    font-weight:
        950;

}


.bank-panel-header p {

    margin:
        0;

    color:
        #64748b;

    font-size:
        8px;

    line-height:
        1.5;

}


/* ============================================================
   FORM
============================================================ */

.bank-form {

    position:
        relative;

    z-index:
        2;

    display:
        flex;

    flex-direction:
        column;

    gap:
        9px;

}


.bank-form-group {

    display:
        flex;

    flex-direction:
        column;

    gap:
        5px;

}


.bank-form-group label {

    color:
        #cbd5e1;

    font-size:
        8px;

    font-weight:
        850;

}


.bank-form input,
.bank-form select {

    width:
        100%;

    min-height:
        43px;

    padding:
        10px
        12px;

    border:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .11
        );

    border-radius:
        10px;

    outline:
        none;

    color:
        #e2e8f0;

    background:
        rgba(
            15,
            23,
            42,
            .86
        ) !important;

    font-size:
        9px;

}


.bank-form input::placeholder {

    color:
        #475569;

}


.bank-form input:focus,
.bank-form select:focus {

    border-color:
        rgba(
            96,
            165,
            250,
            .38
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
            .09
        );

}


.bank-form option {

    color:
        #e2e8f0;

    background:
        #0f172a;

}


/* ============================================================
   AMOUNT
============================================================ */

.amount-wrap {

    position:
        relative;

}


.amount-symbol {

    position:
        absolute;

    left:
        13px;

    top:
        50%;

    transform:
        translateY(
            -50%
        );

    z-index:
        2;

    color:
        #60a5fa;

    font-size:
        11px;

    font-weight:
        900;

}


.amount-wrap input {

    padding-left:
        29px;

}


/* ============================================================
   QUICK AMOUNTS
============================================================ */

.quick-amounts {

    display:
        grid;

    grid-template-columns:
        repeat(
            4,
            1fr
        );

    gap:
        6px;

}


.quick-amount {

    min-height:
        31px;

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
        8px;

    color:
        #93c5fd;

    background:
        rgba(
            37,
            99,
            235,
            .07
        );

    font-size:
        7px;

    font-weight:
        850;

    cursor:
        pointer;

}


.quick-amount:hover {

    color:
        #ffffff;

    background:
        rgba(
            37,
            99,
            235,
            .16
        );

}


/* ============================================================
   PRIMARY ACTION
============================================================ */

.bank-primary-button {

    min-height:
        46px;

    margin-top:
        3px;

    border:
        1px
        solid
        rgba(
            147,
            197,
            253,
            .18
        );

    border-radius:
        11px;

    color:
        #ffffff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    font-size:
        9px;

    font-weight:
        950;

    cursor:
        pointer;

    box-shadow:
        0
        9px
        24px
        rgba(
            37,
            99,
            235,
            .18
        );

    transition:
        .20s
        ease;

}


.bank-primary-button:hover {

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


/* ============================================================
   RECIPIENT PREVIEW
============================================================ */

.recipient-preview {

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

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
        11px;

    background:
        rgba(
            2,
            6,
            23,
            .35
        );

}


.recipient-preview-photo {

    width:
        34px;

    height:
        34px;

    flex:
        0
        0
        34px;

    overflow:
        hidden;

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
        10px;

    color:
        #ffffff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    font-size:
        10px;

    font-weight:
        950;

}


.recipient-preview-photo img {

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;

}


.recipient-preview-text {

    min-width:
        0;

}


.recipient-preview-text span {

    display:
        block;

    color:
        #475569;

    font-size:
        6px;

}


.recipient-preview-text strong {

    display:
        block;

    margin-top:
        2px;

    overflow:
        hidden;

    color:
        #dbeafe;

    font-size:
        9px;

    white-space:
        nowrap;

    text-overflow:
        ellipsis;

}


/* ============================================================
   GAME EARNINGS
============================================================ */

.earnings-card {

    border-color:
        rgba(
            34,
            197,
            94,
            .12
        );

}


.earnings-available {

    padding:
        13px;

    margin-bottom:
        13px;

    border:
        1px
        solid
        rgba(
            34,
            197,
            94,
            .14
        );

    border-radius:
        12px;

    background:
        linear-gradient(
            135deg,
            rgba(
                20,
                83,
                45,
                .28
            ),
            rgba(
                5,
                46,
                22,
                .24
            )
        );

}


.earnings-available span {

    display:
        block;

    color:
        #86efac;

    font-size:
        6px;

    font-weight:
        950;

    letter-spacing:
        1.1px;

}


.earnings-amount-row {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    margin-top:
        4px;

}


.earnings-amount {

    color:
        #dcfce7;

    font-size:
        27px;

    font-weight:
        950;

}


.earnings-status {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        5px;

    padding:
        6px
        8px;

    border:
        1px
        solid
        rgba(
            34,
            197,
            94,
            .13
        );

    border-radius:
        999px;

    color:
        #86efac;

    background:
        rgba(
            34,
            197,
            94,
            .07
        );

    font-size:
        6px;

    font-weight:
        900;

}


.earnings-status::before {

    content:
        "";

    width:
        5px;

    height:
        5px;

    border-radius:
        50%;

    background:
        #22c55e;

}


/* ============================================================
   DEPOSIT / WITHDRAW BUTTONS
============================================================ */

.money-actions {

    display:
        grid;

    grid-template-columns:
        1fr
        1fr;

    gap:
        7px;

    margin-top:
        2px;

}


.money-action {

    min-height:
        43px;

    border:
        1px
        solid
        transparent;

    border-radius:
        10px;

    font-size:
        8px;

    font-weight:
        950;

    cursor:
        pointer;

}


.money-action.deposit {

    color:
        #bbf7d0;

    border-color:
        rgba(
            34,
            197,
            94,
            .12
        );

    background:
        rgba(
            34,
            197,
            94,
            .09
        );

}


.money-action.deposit:hover {

    background:
        rgba(
            34,
            197,
            94,
            .16
        );

}


.money-action.withdraw {

    color:
        #fecaca;

    border-color:
        rgba(
            239,
            68,
            68,
            .12
        );

    background:
        rgba(
            239,
            68,
            68,
            .08
        );

}


.money-action.withdraw:hover {

    background:
        rgba(
            239,
            68,
            68,
            .15
        );

}


/* ============================================================
   SECURITY STRIP
============================================================ */

.bank-security-strip {

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
        12px;

}


.security-strip-item {

    display:
        flex;

    align-items:
        center;

    gap:
        7px;

    padding:
        9px
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
        10px;

    background:
        rgba(
            3,
            12,
            29,
            .68
        );

}


.security-strip-item span {

    font-size:
        15px;

}


.security-strip-item strong {

    display:
        block;

    color:
        #cbd5e1;

    font-size:
        7px;

}


.security-strip-item small {

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
   TRANSACTION HISTORY
============================================================ */

.history-panel {

    width:
        100%;

}


.history-topbar {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        10px;

    margin-bottom:
        12px;

}


.history-count {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    min-width:
        31px;

    height:
        27px;

    padding:
        0
        8px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .12
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
            .08
        );

    font-size:
        7px;

    font-weight:
        900;

}


.transaction-list {

    display:
        flex;

    flex-direction:
        column;

    gap:
        6px;

    max-height:
        500px;

    overflow-y:
        auto;

    padding-right:
        3px;

}


.transaction-list::-webkit-scrollbar {

    width:
        5px;

}


.transaction-list::-webkit-scrollbar-thumb {

    border-radius:
        999px;

    background:
        linear-gradient(
            180deg,
            #2563eb,
            #6366f1
        );

}


/* ============================================================
   TRANSACTION BAR
============================================================ */

.transaction-row {

    position:
        relative;

    display:
        grid;

    grid-template-columns:
        44px
        1fr
        auto;

    align-items:
        center;

    gap:
        9px;

    min-height:
        63px;

    padding:
        8px
        10px;

    overflow:
        hidden;

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
        12px;

    background:
        linear-gradient(
            100deg,
            rgba(
                4,
                14,
                31,
                .90
            ),
            rgba(
                7,
                24,
                48,
                .78
            )
        );

    transition:
        .20s
        ease;

}


.transaction-row::before {

    content:
        "";

    position:
        absolute;

    left:
        -30%;

    top:
        0;

    width:
        25%;

    height:
        1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            #60a5fa,
            transparent
        );

    opacity:
        0;

}


.transaction-row:hover {

    transform:
        translateX(
            2px
        );

    border-color:
        rgba(
            96,
            165,
            250,
            .18
        );

    background:
        linear-gradient(
            100deg,
            rgba(
                8,
                25,
                51,
                .93
            ),
            rgba(
                10,
                31,
                62,
                .86
            )
        );

}


.transaction-row:hover::before {

    opacity:
        1;

    animation:
        transactionScan
        3s
        linear
        infinite;

}


@keyframes transactionScan {

    from {
        left:
            -25%;
    }

    to {
        left:
            115%;
    }

}


/* ============================================================
   TRANSACTION ICON
============================================================ */

.transaction-icon {

    width:
        40px;

    height:
        40px;

    display:
        grid;

    place-items:
        center;

    border-radius:
        11px;

    font-size:
        16px;

}


.transaction-icon.deposit {

    background:
        rgba(
            34,
            197,
            94,
            .11
        );

}


.transaction-icon.withdraw {

    background:
        rgba(
            239,
            68,
            68,
            .11
        );

}


.transaction-icon.transfer {

    background:
        rgba(
            59,
            130,
            246,
            .11
        );

}


/* ============================================================
   TRANSACTION INFORMATION
============================================================ */

.transaction-main {

    min-width:
        0;

}


.transaction-title {

    display:
        flex;

    align-items:
        center;

    gap:
        6px;

}


.transaction-title strong {

    color:
        #e2e8f0;

    font-size:
        9px;

    font-weight:
        900;

}


.transaction-reference {

    margin-top:
        3px;

    overflow:
        hidden;

    color:
        #64748b;

    font-size:
        7px;

    white-space:
        nowrap;

    text-overflow:
        ellipsis;

}


.transaction-date {

    margin-top:
        3px;

    color:
        #334155;

    font-size:
        6px;

}


/* ============================================================
   TRANSACTION AMOUNT
============================================================ */

.transaction-amount-box {

    text-align:
        right;

}


.transaction-amount {

    display:
        block;

    color:
        #ffffff;

    font-size:
        12px;

    font-weight:
        950;

}


.transaction-type-badge {

    display:
        inline-flex;

    margin-top:
        4px;

    padding:
        4px
        6px;

    border-radius:
        999px;

    font-size:
        5px;

    font-weight:
        950;

    letter-spacing:
        .5px;

}


.transaction-type-badge.deposit {

    color:
        #86efac;

    background:
        rgba(
            34,
            197,
            94,
            .10
        );

}


.transaction-type-badge.withdraw {

    color:
        #fca5a5;

    background:
        rgba(
            239,
            68,
            68,
            .10
        );

}


.transaction-type-badge.transfer {

    color:
        #93c5fd;

    background:
        rgba(
            59,
            130,
            246,
            .10
        );

}


/* ============================================================
   EMPTY HISTORY
============================================================ */

.empty-history {

    padding:
        55px
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
            .13
        );

    border-radius:
        14px;

    background:
        rgba(
            2,
            6,
            23,
            .28
        );

}


.empty-history-icon {

    font-size:
        42px;

    opacity:
        .8;

}


.empty-history h3 {

    margin:
        10px
        0
        5px;

    color:
        #dbeafe;

    font-size:
        14px;

}


.empty-history p {

    margin:
        0;

    color:
        #475569;

    font-size:
        8px;

}


/* ============================================================
   BANK EXIT
============================================================ */

.bank-exit {

    padding:
        14px;

    text-align:
        center;

    border:
        1px
        solid
        rgba(
            248,
            113,
            113,
            .09
        );

    border-radius:
        14px;

    background:
        rgba(
            127,
            29,
            29,
            .06
        );

}


.bank-exit-button {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        7px;

    min-height:
        42px;

    padding:
        0
        17px;

    border:
        1px
        solid
        rgba(
            248,
            113,
            113,
            .14
        );

    border-radius:
        10px;

    color:
        #fecaca;

    background:
        rgba(
            127,
            29,
            29,
            .14
        );

    text-decoration:
        none;

    font-size:
        8px;

    font-weight:
        950;

}


.bank-exit-button:hover {

    color:
        #ffffff;

    background:
        rgba(
            220,
            38,
            38,
            .20
        );

}


.bank-exit p {

    margin:
        7px
        0
        0;

    color:
        #475569;

    font-size:
        6px;

}


/* ============================================================
   MOBILE
============================================================ */

@media (
    max-width: 900px
) {

    .bank-main-grid {

        grid-template-columns:
            1fr;

    }


    .bank-stat-grid {

        grid-template-columns:
            repeat(
                2,
                1fr
            );

    }


    .account-mini-bars {

        grid-template-columns:
            repeat(
                2,
                1fr
            );

    }

}


@media (
    max-width: 700px
) {

    .bank-shell {

        padding:
            16px
            10px
            50px;

    }


    .bank-account-display {

        grid-template-columns:
            1fr;

    }


    .balance-box {

        text-align:
            left;

    }


    .balance-value-row {

        justify-content:
            flex-start;

    }


    .bank-balance-value {

        font-size:
            27px;

    }


    .bank-hero-top {

        flex-direction:
            column;

    }


    .security-feature-grid {

        grid-template-columns:
            1fr;

    }


    .bank-security-card {

        padding:
            29px
            20px;

    }


    .bank-security-stage {

        padding:
            30px
            6px;

    }


    .bank-security-logo {

        width:
            82px;

        height:
            82px;

    }


    .bank-security-logo span {

        font-size:
            36px;

    }


    .bank-security-card h1 {

        font-size:
            24px;

    }


    .bank-security-strip {

        grid-template-columns:
            1fr;

    }

}


@media (
    max-width: 500px
) {

    .bank-stat-grid {

        grid-template-columns:
            1fr;

    }


    .account-mini-bars {

        grid-template-columns:
            1fr;

    }


    .quick-amounts {

        grid-template-columns:
            1fr
            1fr;

    }


    .money-actions {

        grid-template-columns:
            1fr;

    }


    .bank-hero {

        padding:
            18px;

    }


    .bank-hero h1 {

        font-size:
            25px;

    }


    .bank-account-number {

        font-size:
            15px;

        letter-spacing:
            2px;

    }


    .transaction-row {

        grid-template-columns:
            39px
            1fr;

    }


    .transaction-amount-box {

        grid-column:
            2;

        text-align:
            left;

    }


    .transaction-amount {

        margin-top:
            1px;

    }


    .big-pin-input {

        height:
            62px;

        font-size:
            23px;

        letter-spacing:
            8px;

    }

}

</style>


<?php if (
    $successMessage !== ""
): ?>

<div
    class="
        bank-shell
        bank-notice
        success
    "
>

    <div
        class="bank-notice-icon"
    >
        ✅
    </div>

    <div>

        <strong>
            Banking Action Successful
        </strong>

        <p>
            <?= e(
                $successMessage
            ) ?>
        </p>

    </div>

</div>

<?php endif; ?>


<?php if (
    $errorMessage !== ""
): ?>

<div
    class="
        bank-shell
        bank-notice
        error
    "
>

    <div
        class="bank-notice-icon"
    >
        ⚠️
    </div>

    <div>

        <strong>
            Banking Action Failed
        </strong>

        <p>
            <?= e(
                $errorMessage
            ) ?>
        </p>

    </div>

</div>

<?php endif; ?>


<?php if (
    empty(
        $account["pin_hash"]
    )
): ?>


<!-- ============================================================
     CREATE PIN
============================================================ -->

<div class="bank-shell">

    <div class="bank-security-stage">

        <div
            class="
                bank-security-orb
                one
            "
        ></div>

        <div
            class="
                bank-security-orb
                two
            "
        ></div>

        <div
            class="
                bank-security-orb
                three
            "
        ></div>


        <div
            class="
                bank-security-card
            "
        >


            <div
                class="
                    bank-security-logo
                "
            >

                <span>
                    🔐
                </span>

            </div>


            <div
                class="
                    bank-security-badge
                "
            >

                <span></span>

                CONNECTHUB BANKING SECURITY

            </div>


            <h1>
                Create Your Banking PIN
            </h1>


            <p>
                Your 4-digit Banking PIN protects
                your balance, transfers and
                transaction history.
            </p>


            <div
                class="
                    security-feature-grid
            ">


                <div
                    class="
                        security-feature
                    "
                >

                    <div
                        class="
                            security-feature-icon
                        "
                    >
                        🛡️
                    </div>

                    <strong>
                        Protected
                    </strong>

                    <small>
                        Account security
                    </small>

                </div>


                <div
                    class="
                        security-feature
                    "
                >

                    <div
                        class="
                            security-feature-icon
                        "
                    >
                        💳
                    </div>

                    <strong>
                        Banking
                    </strong>

                    <small>
                        Secure transactions
                    </small>

                </div>


                <div
                    class="
                        security-feature
                    "
                >

                    <div
                        class="
                            security-feature-icon
                        "
                    >
                        🔒
                    </div>

                    <strong>
                        Private
                    </strong>

                    <small>
                        PIN protected
                    </small>

                </div>


            </div>


            <form
                method="POST"
                class="bank-form"
                autocomplete="off"
            >


                <div
                    class="
                        bank-form-group
                    "
                >

                    <label
                        class="
                            security-input-label
                        "
                    >
                        ENTER 4-DIGIT PIN
                    </label>


                    <input
                        type="password"
                        name="new_pin"
                        class="
                            big-pin-input
                        "
                        maxlength="4"
                        minlength="4"
                        inputmode="numeric"
                        pattern="[0-9]{4}"
                        placeholder="••••"
                        autocomplete="new-password"
                        required
                    >

                </div>


                <div
                    class="
                        bank-form-group
                    "
                >

                    <label
                        class="
                            security-input-label
                        "
                    >
                        CONFIRM 4-DIGIT PIN
                    </label>


                    <input
                        type="password"
                        name="confirm_pin"
                        class="
                            big-pin-input
                        "
                        maxlength="4"
                        minlength="4"
                        inputmode="numeric"
                        pattern="[0-9]{4}"
                        placeholder="••••"
                        autocomplete="new-password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    name="set_pin"
                    class="
                        security-submit
                    "
                >
                    🔐 CREATE SECURE PIN
                </button>


            </form>


            <div
                class="
                    security-footer
                "
            >
                Never share your Banking PIN with anyone.
            </div>


        </div>

    </div>

</div>


<?php

require "footer.php";

exit;

endif;
?>


<?php if (!$bankUnlocked): ?>


<!-- ============================================================
     PIN LOCK
============================================================ -->

<div class="bank-shell">

    <div class="bank-security-stage">

        <div
            class="
                bank-security-orb
                one
            "
        ></div>

        <div
            class="
                bank-security-orb
                two
            "
        ></div>

        <div
            class="
                bank-security-orb
                three
            "
        ></div>


        <div
            class="
                bank-security-card
            "
        >


            <div
                class="
                    bank-security-logo
                "
            >

                <span>
                    🔒
                </span>

            </div>


            <div
                class="
                    bank-security-badge
                "
            >

                <span></span>

                CONNECTHUB SECURE BANKING

            </div>


            <h1>
                Banking is Locked
            </h1>


            <p>
                Enter your Banking PIN to open
                your secure ConnectHub account.
            </p>


            <div
                class="
                    security-feature-grid
                "
            >


                <div
                    class="
                        security-feature
                    "
                >

                    <div
                        class="
                            security-feature-icon
                        "
                    >
                        🔐
                    </div>

                    <strong>
                        PIN Protected
                    </strong>

                    <small>
                        Verification required
                    </small>

                </div>


                <div
                    class="
                        security-feature
                    "
                >

                    <div
                        class="
                            security-feature-icon
                        "
                    >
                        💰
                    </div>

                    <strong>
                        Balance Safe
                    </strong>

                    <small>
                        Account protected
                    </small>

                </div>


                <div
                    class="
                        security-feature
                    "
                >

                    <div
                        class="
                            security-feature-icon
                        "
                    >
                        🧾
                    </div>

                    <strong>
                        History Safe
                    </strong>

                    <small>
                        Activity protected
                    </small>

                </div>


            </div>


            <form
                method="POST"
                class="bank-form"
                autocomplete="off"
            >


                <div
                    class="
                        bank-form-group
                    "
                >

                    <label
                        class="
                            security-input-label
                        "
                    >
                        ENTER BANKING PIN
                    </label>


                    <input
                        type="password"
                        name="bank_pin"
                        class="
                            big-pin-input
                        "
                        maxlength="4"
                        minlength="4"
                        inputmode="numeric"
                        pattern="[0-9]{4}"
                        placeholder="••••"
                        autocomplete="off"
                        autofocus
                        required
                    >

                </div>


                <button
                    type="submit"
                    name="verify_bank_pin"
                    class="
                        security-submit
                    "
                >
                    🔓 UNLOCK MY BANKING
                </button>


            </form>


            <div
                class="
                    security-footer
                "
            >
                Your banking area will lock again when you leave Banking.
            </div>


        </div>

    </div>

</div>


<?php

require "footer.php";

exit;

endif;
?>


<!-- ============================================================
     UNLOCKED BANKING DASHBOARD
============================================================ -->

<div
    class="
        bank-shell
        bank-dashboard
    "
>


    <!-- ========================================================
         HERO
    ========================================================= -->

    <section
        class="
            bank-hero
        "
    >


        <div
            class="
                bank-hero-top
            "
        >


            <div>

                <div
                    class="
                        bank-hero-label
                    "
                >

                    <span></span>

                    CONNECTHUB DIGITAL BANK

                </div>


                <h1>
                    My Banking
                </h1>


                <p
                    class="
                        bank-hero-description
                    "
                >
                    Manage your balance, transfers,
                    game earnings and account activity.
                </p>

            </div>


            <div
                class="
                    bank-active-badge
                "
            >
                Banking Active
            </div>


        </div>


        <div
            class="
                bank-account-display
            "
        >


            <div>

                <span
                    class="
                        account-info-label
                    "
                >
                    ACCOUNT NUMBER
                </span>


                <div
                    class="
                        account-number-row
                    "
                >

                    <strong
                        class="
                            bank-account-number
                        "
                        id="accountNumber"
                    >
                        <?= e(
                            $account[
                                "account_number"
                            ]
                        ) ?>
                    </strong>


                    <button
                        type="button"
                        class="
                            copy-account-button
                        "
                        id="copyAccountButton"
                        title="Copy account number"
                    >
                        ⧉
                    </button>

                </div>

            </div>


            <div
                class="
                    balance-box
                "
            >

                <span>
                    AVAILABLE BALANCE
                </span>


                <div
                    class="
                        balance-value-row
                    "
                >

                    <strong
                        class="
                            bank-balance-value
                        "
                        id="balanceValue"
                        data-balance="<?= e(
                            number_format(
                                (float)$account["balance"],
                                2,
                                ".",
                                ""
                            )
                        ) ?>"
                    >

                        ₹<?= number_format(
                            (float)$account["balance"],
                            2
                        ) ?>

                    </strong>


                    <button
                        type="button"
                        class="
                            balance-toggle
                        "
                        id="balanceToggle"
                        title="Hide balance"
                    >
                        👁
                    </button>

                </div>

            </div>


        </div>


        <div
            class="
                account-mini-bars
            "
        >


            <div
                class="
                    account-mini-bar
                "
            >

                <span>
                    ACCOUNT STATUS
                </span>

                <strong>
                    ● Active
                </strong>

            </div>


            <div
                class="
                    account-mini-bar
                "
            >

                <span>
                    SECURITY
                </span>

                <strong>
                    🔐 PIN Protected
                </strong>

            </div>


            <div
                class="
                    account-mini-bar
                "
            >

                <span>
                    GAME WALLET
                </span>

                <strong>
                    ₹<?= number_format(
                        $gameEarnings,
                        2
                    ) ?>
                </strong>

            </div>


            <div
                class="
                    account-mini-bar
                "
            >

                <span>
                    TRANSACTIONS
                </span>

                <strong>
                    <?= $transactionCount ?>
                    Recent
                </strong>

            </div>


        </div>


    </section>


    <!-- ========================================================
         STATS
    ========================================================= -->

    <section
        class="
            bank-stat-grid
        "
    >


        <div
            class="
                bank-stat
            "
        >

            <div
                class="
                    bank-stat-icon
                    blue
                "
            >
                💰
            </div>

            <div>

                <span>
                    Current Balance
                </span>

                <strong>
                    ₹<?= number_format(
                        (float)$account["balance"],
                        2
                    ) ?>
                </strong>

            </div>

        </div>


        <div
            class="
                bank-stat
            "
        >

            <div
                class="
                    bank-stat-icon
                    green
                "
            >
                🎮
            </div>

            <div>

                <span>
                    Game Earnings
                </span>

                <strong>
                    ₹<?= number_format(
                        $gameEarnings,
                        2
                    ) ?>
                </strong>

            </div>

        </div>


        <div
            class="
                bank-stat
            "
        >

            <div
                class="
                    bank-stat-icon
                    cyan
                "
            >
                🔐
            </div>

            <div>

                <span>
                    Security
                </span>

                <strong>
                    PIN Protected
                </strong>

            </div>

        </div>


        <div
            class="
                bank-stat
            "
        >

            <div
                class="
                    bank-stat-icon
                    purple
                "
            >
                🧾
            </div>

            <div>

                <span>
                    Activity
                </span>

                <strong>
                    <?= $transactionCount ?>
                    Records
                </strong>

            </div>

        </div>


    </section>


    <!-- ========================================================
         SECURITY STRIP
    ========================================================= -->

    <section
        class="
            bank-security-strip
        "
    >


        <div
            class="
                security-strip-item
            "
        >

            <span>
                🛡️
            </span>

            <div>

                <strong>
                    Secure Access
                </strong>

                <small>
                    PIN verified
                </small>

            </div>

        </div>


        <div
            class="
                security-strip-item
            "
        >

            <span>
                ⚡
            </span>

            <div>

                <strong>
                    Fast Transfer
                </strong>

                <small>
                    ConnectHub users
                </small>

            </div>

        </div>


        <div
            class="
                security-strip-item
            "
        >

            <span>
                🔒
            </span>

            <div>

                <strong>
                    Protected Session
                </strong>

                <small>
                    Banking mode active
                </small>

            </div>

        </div>


    </section>


    <!-- ========================================================
         MAIN GRID
    ========================================================= -->

    <div
        class="
            bank-main-grid
        "
    >


        <!-- ====================================================
             SEND MONEY
        ===================================================== -->

        <section
            class="
                bank-panel
            "
        >


            <div
                class="
                    bank-panel-header
                "
            >

                <div
                    class="
                        bank-panel-icon
                        panel-icon-blue
                    "
                >
                    💸
                </div>


                <div
                    class="
                        panel-header-text
                    "
                >

                    <span
                        class="
                            panel-eyebrow
                        "
                    >
                        MONEY TRANSFER
                    </span>


                    <h2>
                        Send Money
                    </h2>


                    <p>
                        Transfer money securely to another
                        ConnectHub Banking user.
                    </p>

                </div>

            </div>


            <?php if (
                count($bankUsers) > 0
            ): ?>


            <form
                method="POST"
                class="
                    bank-form
                "
                autocomplete="off"
            >


                <div
                    class="
                        bank-form-group
                    "
                >

                    <label>
                        👤 Select Recipient
                    </label>


                    <select
                        name="receiver_id"
                        id="receiverSelect"
                        required
                    >

                        <option
                            value=""
                            data-image=""
                        >
                            Select ConnectHub user
                        </option>


                        <?php foreach (
                            $bankUsers
                            as $bankUser
                        ): ?>

                            <?php

                            $userImage =
                                bankProfilePath(
                                    $bankUser[
                                        "profile_image"
                                    ]
                                    ??
                                    ""
                                );


                            $userInitial =
                                bankUserInitial(
                                    $bankUser[
                                        "name"
                                    ]
                                    ??
                                    "User"
                                );

                            ?>


                            <option
                                value="<?= (int)$bankUser["id"] ?>"
                                data-image="<?= e(
                                    $userImage
                                ) ?>"
                                data-initial="<?= e(
                                    $userInitial
                                ) ?>"
                            >

                                <?= e(
                                    $bankUser["name"]
                                ) ?>

                                —
                                Account
                                <?= e(
                                    $bankUser[
                                        "account_number"
                                    ]
                                ) ?>

                            </option>


                        <?php endforeach; ?>


                    </select>

                </div>


                <div
                    class="
                        recipient-preview
                    "
                >

                    <div
                        class="
                            recipient-preview-photo
                        "
                        id="recipientPhoto"
                    >
                        👤
                    </div>


                    <div
                        class="
                            recipient-preview-text
                        "
                    >

                        <span>
                            RECIPIENT
                        </span>

                        <strong
                            id="recipientName"
                        >
                            Select a user above
                        </strong>

                    </div>

                </div>


                <div
                    class="
                        bank-form-group
                    "
                >

                    <label>
                        💰 Transfer Amount
                    </label>


                    <div
                        class="
                            amount-wrap
                        "
                    >

                        <span
                            class="
                                amount-symbol
                            "
                        >
                            ₹
                        </span>


                        <input
                            type="number"
                            name="send_amount"
                            id="sendAmount"
                            min="0.01"
                            step="0.01"
                            placeholder="0.00"
                            required
                        >

                    </div>

                </div>


                <div
                    class="
                        quick-amounts
                    "
                >

                    <button
                        type="button"
                        class="
                            quick-amount
                        "
                        data-amount="100"
                    >
                        ₹100
                    </button>


                    <button
                        type="button"
                        class="
                            quick-amount
                        "
                        data-amount="500"
                    >
                        ₹500
                    </button>


                    <button
                        type="button"
                        class="
                            quick-amount
                        "
                        data-amount="1000"
                    >
                        ₹1,000
                    </button>


                    <button
                        type="button"
                        class="
                            quick-amount
                        "
                        data-amount="5000"
                    >
                        ₹5,000
                    </button>

                </div>


                <div
                    class="
                        bank-form-group
                    "
                >

                    <label>
                        🔐 Banking PIN
                    </label>


                    <input
                        type="password"
                        name="send_pin"
                        maxlength="4"
                        minlength="4"
                        inputmode="numeric"
                        pattern="[0-9]{4}"
                        placeholder="Enter 4-digit PIN"
                        autocomplete="off"
                        required
                    >

                </div>


                <button
                    type="submit"
                    name="send_money"
                    class="
                        bank-primary-button
                    "
                >
                    💸 SEND MONEY SECURELY
                </button>


            </form>


            <?php else: ?>


            <div
                class="
                    empty-history
                "
            >

                <div
                    class="
                        empty-history-icon
                    "
                >
                    👥
                </div>


                <h3>
                    No Banking Recipients
                </h3>


                <p>
                    Other users need a ConnectHub Banking
                    account before you can send money.
                </p>

            </div>


            <?php endif; ?>


        </section>


        <!-- ====================================================
             GAME EARNINGS
        ===================================================== -->

        <section
            class="
                bank-panel
                earnings-card
            "
        >


            <div
                class="
                    bank-panel-header
                "
            >

                <div
                    class="
                        bank-panel-icon
                        panel-icon-green
                    "
                >
                    🎮
                </div>


                <div
                    class="
                        panel-header-text
                    "
                >

                    <span
                        class="
                            panel-eyebrow
                        "
                    >
                        CONNECTHUB GAMES
                    </span>


                    <h2>
                        Game Earnings
                    </h2>


                    <p>
                        Move your earned game money
                        into your Banking balance.
                    </p>

                </div>

            </div>


            <div
                class="
                    earnings-available
                "
            >

                <span>
                    AVAILABLE GAME BALANCE
                </span>


                <div
                    class="
                        earnings-amount-row
                    "
                >

                    <strong
                        class="
                            earnings-amount
                        "
                    >
                        ₹<?= number_format(
                            $gameEarnings,
                            2
                        ) ?>
                    </strong>


                    <span
                        class="
                            earnings-status
                        "
                    >
                        Available
                    </span>

                </div>

            </div>


            <?php if (
                $gameEarnings > 0
            ): ?>


                <form
                    method="POST"
                    class="
                        bank-form
                    "
                    autocomplete="off"
                >


                    <div
                        class="
                            bank-form-group
                        "
                    >

                        <label>
                            💰 Amount to Deposit
                        </label>


                        <div
                            class="
                                amount-wrap
                            "
                        >

                            <span
                                class="
                                    amount-symbol
                                "
                            >
                                ₹
                            </span>


                            <input
                                type="number"
                                name="game_amount"
                                min="0.01"
                                max="<?= e(
                                    number_format(
                                        $gameEarnings,
                                        2,
                                        ".",
                                        ""
                                    )
                                ) ?>"
                                step="0.01"
                                placeholder="0.00"
                                required
                            >

                        </div>

                    </div>


                    <div
                        class="
                            quick-amounts
                        "
                    >

                        <button
                            type="button"
                            class="
                                quick-amount
                            "
                            data-target="game_amount"
                            data-amount="100"
                        >
                            ₹100
                        </button>


                        <button
                            type="button"
                            class="
                                quick-amount
                            "
                            data-target="game_amount"
                            data-amount="500"
                        >
                            ₹500
                        </button>


                        <button
                            type="button"
                            class="
                                quick-amount
                            "
                            data-target="game_amount"
                            data-amount="1000"
                        >
                            ₹1,000
                        </button>


                        <button
                            type="button"
                            class="
                                quick-amount
                            "
                            data-target="game_amount"
                            data-amount="<?= e(
                                number_format(
                                    $gameEarnings,
                                    2,
                                    ".",
                                    ""
                                )
                            ) ?>"
                        >
                            MAX
                        </button>

                    </div>


                    <div
                        class="
                            bank-form-group
                        "
                    >

                        <label>
                            🔐 Banking PIN
                        </label>


                        <input
                            type="password"
                            name="game_pin"
                            maxlength="4"
                            minlength="4"
                            inputmode="numeric"
                            pattern="[0-9]{4}"
                            placeholder="Enter 4-digit PIN"
                            autocomplete="off"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        name="withdraw_game_earnings"
                        class="
                            bank-primary-button
                        "
                    >
                        🏦 DEPOSIT GAME EARNINGS
                    </button>


                </form>


            <?php else: ?>


                <div
                    class="
                        empty-history
                    "
                >

                    <div
                        class="
                            empty-history-icon
                        "
                    >
                        🎮
                    </div>


                    <h3>
                        No Game Earnings Yet
                    </h3>


                    <p>
                        Play ConnectHub games and your
                        available earnings will appear here.
                    </p>

                </div>


            <?php endif; ?>


        </section>


        <!-- ====================================================
             DEPOSIT / WITHDRAW
        ===================================================== -->

        <section
            class="
                bank-panel
            "
        >


            <div
                class="
                    bank-panel-header
                "
            >

                <div
                    class="
                        bank-panel-icon
                        panel-icon-cyan
                    "
                >
                    💰
                </div>


                <div
                    class="
                        panel-header-text
                    "
                >

                    <span
                        class="
                            panel-eyebrow
                        "
                    >
                        ACCOUNT MANAGEMENT
                    </span>


                    <h2>
                        Deposit / Withdraw
                    </h2>


                    <p>
                        Add money or withdraw money
                        from your simulated balance.
                    </p>

                </div>

            </div>


            <form
                method="POST"
                class="
                    bank-form
                "
                autocomplete="off"
            >


                <div
                    class="
                        bank-form-group
                    "
                >

                    <label>
                        💰 Amount
                    </label>


                    <div
                        class="
                            amount-wrap
                        "
                    >

                        <span
                            class="
                                amount-symbol
                            "
                        >
                            ₹
                        </span>


                        <input
                            type="number"
                            name="amount"
                            id="normalAmount"
                            min="0.01"
                            step="0.01"
                            placeholder="0.00"
                            required
                        >

                    </div>

                </div>


                <div
                    class="
                        quick-amounts
                    "
                >

                    <button
                        type="button"
                        class="
                            quick-amount
                        "
                        data-target="normalAmount"
                        data-amount="100"
                    >
                        ₹100
                    </button>


                    <button
                        type="button"
                        class="
                            quick-amount
                        "
                        data-target="normalAmount"
                        data-amount="500"
                    >
                        ₹500
                    </button>


                    <button
                        type="button"
                        class="
                            quick-amount
                        "
                        data-target="normalAmount"
                        data-amount="1000"
                    >
                        ₹1,000
                    </button>


                    <button
                        type="button"
                        class="
                            quick-amount
                        "
                        data-target="normalAmount"
                        data-amount="5000"
                    >
                        ₹5,000
                    </button>

                </div>


                <div
                    class="
                        bank-form-group
                    "
                >

                    <label>
                        🔐 Banking PIN
                    </label>


                    <input
                        type="password"
                        name="transaction_pin"
                        maxlength="4"
                        minlength="4"
                        inputmode="numeric"
                        pattern="[0-9]{4}"
                        placeholder="Enter 4-digit PIN"
                        autocomplete="off"
                        required
                    >

                </div>


                <div
                    class="
                        money-actions
                    "
                >

                    <button
                        type="submit"
                        name="action"
                        value="deposit"
                        class="
                            money-action
                            deposit
                        "
                    >
                        💰 DEPOSIT
                    </button>


                    <button
                        type="submit"
                        name="action"
                        value="withdraw"
                        class="
                            money-action
                            withdraw
                        "
                    >
                        💵 WITHDRAW
                    </button>

                </div>


            </form>


        </section>


        <!-- ====================================================
             ACCOUNT SECURITY
        ===================================================== -->

        <section
            class="
                bank-panel
            "
        >


            <div
                class="
                    bank-panel-header
                "
            >

                <div
                    class="
                        bank-panel-icon
                        panel-icon-purple
                    "
                >
                    🛡️
                </div>


                <div
                    class="
                        panel-header-text
                    "
                >

                    <span
                        class="
                            panel-eyebrow
                        "
                    >
                        SECURITY CENTER
                    </span>


                    <h2>
                        Banking Protection
                    </h2>


                    <p>
                        Your Banking session is currently protected.
                    </p>

                </div>

            </div>


            <div
                class="
                    security-feature-grid
                "
            >


                <div
                    class="
                        security-feature
                    "
                >

                    <div
                        class="
                            security-feature-icon
                        "
                    >
                        🔐
                    </div>

                    <strong>
                        PIN Protected
                    </strong>

                    <small>
                        Banking PIN enabled
                    </small>

                </div>


                <div
                    class="
                        security-feature
                    "
                >

                    <div
                        class="
                            security-feature-icon
                        "
                    >
                        🏦
                    </div>

                    <strong>
                        Active Account
                    </strong>

                    <small>
                        Account ready
                    </small>

                </div>


                <div
                    class="
                        security-feature
                    "
                >

                    <div
                        class="
                            security-feature-icon
                        "
                    >
                        🛡️
                    </div>

                    <strong>
                        Protected Session
                    </strong>

                    <small>
                        Banking unlocked
                    </small>

                </div>


            </div>


            <div
                class="
                    bank-notice
                    success
                "
                style="
                    margin-bottom:0;
                "
            >

                <div
                    class="
                        bank-notice-icon
                    "
                >
                    🟢
                </div>


                <div>

                    <strong>
                        Banking Session Active
                    </strong>

                    <p>
                        PIN verification has been completed
                        for this banking session.
                    </p>

                </div>

            </div>


        </section>


    </div>


    <!-- ========================================================
         TRANSACTION HISTORY
    ========================================================= -->

    <section
        class="
            bank-panel
            history-panel
        "
    >


        <div
            class="
                history-topbar
            "
        >


            <div
                class="
                    bank-panel-header
                "
                style="
                    margin-bottom:0;
                "
            >

                <div
                    class="
                        bank-panel-icon
                        panel-icon-blue
                    "
                >
                    📜
                </div>


                <div
                    class="
                        panel-header-text
                    "
                >

                    <span
                        class="
                            panel-eyebrow
                        "
                    >
                        ACCOUNT ACTIVITY
                    </span>


                    <h2>
                        Transaction History
                    </h2>


                    <p>
                        Your recent ConnectHub Banking activity.
                    </p>

                </div>

            </div>


            <div
                class="
                    history-count
                "
            >
                <?= $transactionCount ?>
            </div>


        </div>


        <?php if (
            count($transactions) > 0
        ): ?>


            <div
                class="
                    transaction-list
                "
            >


                <?php foreach (
                    $transactions
                    as $transaction
                ): ?>


                    <?php

                    $transactionType =
                        strtoupper(
                            trim(
                                (string)(
                                    $transaction[
                                        "type"
                                    ] ?? ""
                                )
                            )
                        );


                    if (
                        $transactionType ===
                        "WITHDRAW"
                    ) {

                        $transactionClass =
                            "withdraw";

                        $transactionIcon =
                            "↘";

                        $transactionLabel =
                            "WITHDRAW";

                    } elseif (
                        $transactionType ===
                        "TRANSFER"
                    ) {

                        $transactionClass =
                            "transfer";

                        $transactionIcon =
                            "↔";

                        $transactionLabel =
                            "TRANSFER";

                    } else {

                        $transactionClass =
                            "deposit";

                        $transactionIcon =
                            "↗";

                        $transactionLabel =
                            "DEPOSIT";

                    }


                    $transactionAmount =
                        number_format(
                            (float)(
                                $transaction[
                                    "amount"
                                ]
                                ??
                                0
                            ),
                            2
                        );


                    $transactionReference =
                        trim(
                            (string)(
                                $transaction[
                                    "reference"
                                ]
                                ??
                                ""
                            )
                        );


                    $transactionDate =
                        "";


                    if (
                        !empty(
                            $transaction[
                                "created_at"
                            ]
                        )
                    ) {

                        $timestamp =
                            strtotime(
                                $transaction[
                                    "created_at"
                                ]
                            );


                        if (
                            $timestamp
                        ) {

                            $transactionDate =
                                date(
                                    "d M Y • h:i A",
                                    $timestamp
                                );

                        } else {

                            $transactionDate =
                                $transaction[
                                    "created_at"
                                ];

                        }

                    }

                    ?>


                    <article
                        class="
                            transaction-row
                        "
                    >


                        <div
                            class="
                                transaction-icon
                                <?= e(
                                    $transactionClass
                                ) ?>
                        "
                        >

                            <?= e(
                                $transactionIcon
                            ) ?>

                        </div>


                        <div
                            class="
                                transaction-main
                            "
                        >

                            <div
                                class="
                                    transaction-title
                                "
                            >

                                <strong>
                                    <?= e(
                                        $transactionLabel
                                    ) ?>
                                </strong>

                            </div>


                            <div
                                class="
                                    transaction-reference
                                "
                            >

                                <?= e(
                                    $transactionReference
                                ) ?>

                            </div>


                            <div
                                class="
                                    transaction-date
                                "
                            >

                                <?= e(
                                    $transactionDate
                                ) ?>

                            </div>

                        </div>


                        <div
                            class="
                                transaction-amount-box
                            "
                        >

                            <strong
                                class="
                                    transaction-amount
                                "
                            >
                                ₹<?= e(
                                    $transactionAmount
                                ) ?>
                            </strong>


                            <span
                                class="
                                    transaction-type-badge
                                    <?= e(
                                        $transactionClass
                                    ) ?>
                            "
                            >
                                <?= e(
                                    $transactionLabel
                                ) ?>
                            </span>

                        </div>


                    </article>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <div
                class="
                    empty-history
                "
            >

                <div
                    class="
                        empty-history-icon
                    "
                >
                    📭
                </div>


                <h3>
                    No Transactions Yet
                </h3>


                <p>
                    Your banking activity will appear
                    here after your first transaction.
                </p>

            </div>


        <?php endif; ?>


    </section>


    <!-- ========================================================
         EXIT BANKING
    ========================================================= -->

    <div
        class="
            bank-exit
        "
    >


        <a
            href="bank_logout.php"
            class="
                bank-exit-button
            "
        >
            🔒 EXIT SECURE BANKING
        </a>


        <p>
            Leaving Banking will lock your banking session again.
        </p>


    </div>


</div>


<script>

/* ============================================================
   CONNECTHUB ADVANCED BANKING JAVASCRIPT
============================================================ */

(function () {

    "use strict";


    /* ========================================================
       BALANCE VISIBILITY
    ======================================================== */

    const balanceValue =
        document.getElementById(
            "balanceValue"
        );


    const balanceToggle =
        document.getElementById(
            "balanceToggle"
        );


    let balanceVisible =
        true;


    if (
        balanceValue &&
        balanceToggle
    ) {

        const realBalance =
            balanceValue.getAttribute(
                "data-balance"
            );


        balanceToggle.addEventListener(
            "click",
            function () {

                balanceVisible =
                    !balanceVisible;


                if (
                    balanceVisible
                ) {

                    balanceValue.textContent =
                        "₹" +
                        Number(
                            realBalance
                        ).toLocaleString(
                            "en-IN",
                            {
                                minimumFractionDigits:
                                    2,

                                maximumFractionDigits:
                                    2
                            }
                        );


                    balanceToggle.textContent =
                        "👁";

                    balanceToggle.title =
                        "Hide balance";

                } else {

                    balanceValue.textContent =
                        "₹ ••••••";


                    balanceToggle.textContent =
                        "🙈";

                    balanceToggle.title =
                        "Show balance";

                }

            }
        );

    }


    /* ========================================================
       COPY ACCOUNT NUMBER
    ======================================================== */

    const copyAccountButton =
        document.getElementById(
            "copyAccountButton"
        );


    const accountNumberElement =
        document.getElementById(
            "accountNumber"
        );


    if (
        copyAccountButton &&
        accountNumberElement
    ) {

        copyAccountButton.addEventListener(
            "click",
            async function () {

                const text =
                    accountNumberElement
                        .textContent
                        .trim();


                try {

                    await navigator
                        .clipboard
                        .writeText(
                            text
                        );

                } catch (
                    error
                ) {

                    const temp =
                        document.createElement(
                            "textarea"
                        );


                    temp.value =
                        text;


                    document.body.appendChild(
                        temp
                    );


                    temp.select();


                    document.execCommand(
                        "copy"
                    );


                    temp.remove();

                }


                const oldText =
                    copyAccountButton.textContent;


                copyAccountButton.textContent =
                    "✓";


                setTimeout(
                    function () {

                        copyAccountButton.textContent =
                            oldText;

                    },
                    1200
                );

            }
        );

    }


    /* ========================================================
       QUICK AMOUNT BUTTONS
    ======================================================== */

    document
        .querySelectorAll(
            ".quick-amount"
        )
        .forEach(
            function (button) {

                button.addEventListener(
                    "click",
                    function () {

                        let targetId =
                            button.getAttribute(
                                "data-target"
                            );


                        let targetInput = null;


                        if (
                            targetId
                        ) {

                            targetInput =
                                document.getElementById(
                                    targetId
                                );

                        } else {

                            targetInput =
                                document.querySelector(
                                    'input[name="send_amount"]'
                                );

                        }


                        if (
                            !targetInput
                        ) {

                            return;

                        }


                        const amount =
                            button.getAttribute(
                                "data-amount"
                            );


                        if (
                            amount
                        ) {

                            targetInput.value =
                                amount;


                            targetInput.dispatchEvent(
                                new Event(
                                    "input",
                                    {
                                        bubbles:
                                            true
                                    }
                                )
                            );


                            targetInput.focus();

                        }

                    }
                );

            }
        );


    /* ========================================================
       RECIPIENT PREVIEW
    ======================================================== */

    const receiverSelect =
        document.getElementById(
            "receiverSelect"
        );


    const recipientName =
        document.getElementById(
            "recipientName"
        );


    const recipientPhoto =
        document.getElementById(
            "recipientPhoto"
        );


    if (
        receiverSelect &&
        recipientName
    ) {

        receiverSelect.addEventListener(
            "change",
            function () {

                const selected =
                    receiverSelect
                        .options[
                            receiverSelect.selectedIndex
                        ];


                if (
                    !receiverSelect.value
                ) {

                    recipientName.textContent =
                        "Select a user above";


                    if (
                        recipientPhoto
                    ) {

                        recipientPhoto.innerHTML =
                            "👤";

                    }


                    return;

                }


                recipientName.textContent =
                    selected.textContent.trim();


                const image =
                    selected.getAttribute(
                        "data-image"
                    );


                const initial =
                    selected.getAttribute(
                        "data-initial"
                    )
                    ||
                    "U";


                if (
                    recipientPhoto
                ) {

                    recipientPhoto.innerHTML =
                        "";


                    if (
                        image
                    ) {

                        const img =
                            document.createElement(
                                "img"
                            );


                        img.src =
                            image;


                        img.alt =
                            "Recipient";


                        img.onerror =
                            function () {

                                recipientPhoto.textContent =
                                    initial;

                            };


                        recipientPhoto.appendChild(
                            img
                        );

                    } else {

                        recipientPhoto.textContent =
                            initial;

                    }

                }

            }
        );

    }


    /* ========================================================
       PIN INPUT
       ONLY ALLOW NUMBERS
    ======================================================== */

    document
        .querySelectorAll(
            'input[inputmode="numeric"]'
        )
        .forEach(
            function (input) {

                input.addEventListener(
                    "input",
                    function () {

                        input.value =
                            input.value.replace(
                                /[^0-9]/g,
                                ""
                            );

                    }
                );

            }
        );


    /* ========================================================
       BUTTON CLICK FEEDBACK
    ======================================================== */

    document.addEventListener(
        "pointerdown",
        function (event) {

            const button =
                event.target.closest(
                    "button"
                );


            if (
                !button
            ) {

                return;

            }


            button.style.transform =
                "scale(.98)";


            setTimeout(
                function () {

                    button.style.transform =
                        "";

                },
                100
            );

        },
        {
            passive:
                true
        }
    );


    /* ========================================================
       TRANSACTION ROW REVEAL
    ======================================================== */

    const rows =
        document.querySelectorAll(
            ".transaction-row"
        );


    rows.forEach(
        function (
            row,
            index
        ) {

            row.style.opacity =
                "0";

            row.style.transform =
                "translateY(7px)";


            setTimeout(
                function () {

                    row.style.transition =
                        "opacity .35s ease, transform .35s ease";


                    row.style.opacity =
                        "1";


                    row.style.transform =
                        "translateY(0)";

                },
                60 + (
                    index * 28
                )
            );

        }
    );


    /* ========================================================
       PREVENT DOUBLE CLICK SUBMIT
    ======================================================== */

    document.addEventListener(
        "submit",
        function (event) {

            const form =
                event.target;


            if (
                !form
            ) {

                return;

            }


            const buttons =
                form.querySelectorAll(
                    "button[type='submit']"
                );


            buttons.forEach(
                function (button) {

                    setTimeout(
                        function () {

                            button.disabled =
                                true;


                            const originalText =
                                button.textContent;


                            button.dataset.originalText =
                                originalText;


                            button.textContent =
                                "Processing...";

                        },
                        0
                    );

                }
            );

        },
        true
    );


})();

</script>


<?php

require "footer.php";

?>