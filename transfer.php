<?php
// =====================================================
// CONNECTHUB - MONEY TRANSFER
// =====================================================

require "config.php";

login_required();

$uid = (int)($_SESSION["user_id"] ?? 0);

if ($uid <= 0) {
    header("Location: login.php");
    exit;
}


// =====================================================
// VARIABLES
// =====================================================

$error = "";
$message = "";

$selectedUser = null;


// =====================================================
// GET SELECTED USER
// transfer.php?user=7
// =====================================================

$selectedUserId = (int)(
    $_GET["user"]
    ?? $_POST["receiver_id"]
    ?? 0
);


// =====================================================
// SEND MONEY
// =====================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["send_money"])
) {

    $receiverId = (int)(
        $_POST["receiver_id"] ?? 0
    );

    $amount = (float)(
        $_POST["amount"] ?? 0
    );

    $transactionPin = trim(
        $_POST["transaction_pin"] ?? ""
    );


    // =================================================
    // BASIC VALIDATION
    // =================================================

    if ($receiverId <= 0) {

        $error =
            "❌ Please select a valid user.";

    } elseif ($receiverId === $uid) {

        $error =
            "❌ You cannot send money to yourself.";

    } elseif ($amount <= 0) {

        $error =
            "❌ Enter a valid amount.";

    } elseif (
        !preg_match(
            '/^[0-9]{4}$/',
            $transactionPin
        )
    ) {

        $error =
            "❌ Enter your 4-digit Banking PIN.";

    } else {


        // =============================================
        // GET SENDER BANK ACCOUNT
        // =============================================

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

        $stmt->bind_param(
            "i",
            $uid
        );

        $stmt->execute();

        $senderAccount =
            $stmt
            ->get_result()
            ->fetch_assoc();

        $stmt->close();


        // =============================================
        // CHECK SENDER ACCOUNT
        // =============================================

        if (!$senderAccount) {

            $error =
                "❌ Your Banking account was not found.";

        }


        // =============================================
        // CHECK BANKING PIN
        // =============================================

        elseif (
            empty($senderAccount["pin_hash"])
            || !password_verify(
                $transactionPin,
                $senderAccount["pin_hash"]
            )
        ) {

            $error =
                "❌ Incorrect Banking PIN.";

        }


        // =============================================
        // CHECK BALANCE
        // =============================================

        elseif (
            (float)$senderAccount["balance"]
            < $amount
        ) {

            $error =
                "❌ Insufficient balance.";

        } else {


            // =========================================
            // GET RECEIVER BANK ACCOUNT
            // =========================================

            $stmt = $conn->prepare("
                SELECT
                    id,
                    user_id,
                    account_number,
                    balance
                FROM bank_accounts
                WHERE user_id = ?
                LIMIT 1
            ");

            $stmt->bind_param(
                "i",
                $receiverId
            );

            $stmt->execute();

            $receiverAccount =
                $stmt
                ->get_result()
                ->fetch_assoc();

            $stmt->close();


            // =========================================
            // RECEIVER NOT FOUND
            // =========================================

            if (!$receiverAccount) {

                $error =
                    "❌ Receiver does not have a Banking account.";

            } else {


                // =====================================
                // START DATABASE TRANSACTION
                // =====================================

                $conn->begin_transaction();


                try {

                    // =================================
                    // ACCOUNT IDs
                    // =================================

                    $senderAccountId =
                        (int)$senderAccount["id"];

                    $receiverAccountId =
                        (int)$receiverAccount["id"];


                    // =================================
                    // GENERATE UNIQUE REFERENCE
                    // =================================

                    $reference =
                        "TRF_"
                        . date("YmdHis")
                        . "_"
                        . strtoupper(
                            bin2hex(
                                random_bytes(4)
                            )
                        );


                    // =================================
                    // LOCK SENDER ACCOUNT
                    // =================================

                    $stmt = $conn->prepare("
                        SELECT
                            balance
                        FROM bank_accounts
                        WHERE id = ?
                        AND user_id = ?
                        FOR UPDATE
                    ");

                    $stmt->bind_param(
                        "ii",
                        $senderAccountId,
                        $uid
                    );

                    $stmt->execute();

                    $lockedSender =
                        $stmt
                        ->get_result()
                        ->fetch_assoc();

                    $stmt->close();


                    if (!$lockedSender) {

                        throw new Exception(
                            "Sender account not found."
                        );
                    }


                    $currentBalance =
                        (float)$lockedSender["balance"];


                    // =================================
                    // CHECK BALANCE AGAIN
                    // =================================

                    if (
                        $amount > $currentBalance
                    ) {

                        throw new Exception(
                            "Insufficient balance."
                        );
                    }


                    // =================================
                    // LOCK RECEIVER ACCOUNT
                    // =================================

                    $stmt = $conn->prepare("
                        SELECT
                            balance
                        FROM bank_accounts
                        WHERE id = ?
                        FOR UPDATE
                    ");

                    $stmt->bind_param(
                        "i",
                        $receiverAccountId
                    );

                    $stmt->execute();

                    $lockedReceiver =
                        $stmt
                        ->get_result()
                        ->fetch_assoc();

                    $stmt->close();


                    if (!$lockedReceiver) {

                        throw new Exception(
                            "Receiver account not found."
                        );
                    }


                    // =================================
                    // DECREASE SENDER BALANCE
                    // =================================

                    $stmt = $conn->prepare("
                        UPDATE bank_accounts
                        SET balance = balance - ?
                        WHERE id = ?
                        AND balance >= ?
                    ");

                    $stmt->bind_param(
                        "did",
                        $amount,
                        $senderAccountId,
                        $amount
                    );

                    $stmt->execute();


                    if (
                        $stmt->affected_rows !== 1
                    ) {

                        $stmt->close();

                        throw new Exception(
                            "Unable to update sender balance."
                        );
                    }

                    $stmt->close();


                    // =================================
                    // INCREASE RECEIVER BALANCE
                    // =================================

                    $stmt = $conn->prepare("
                        UPDATE bank_accounts
                        SET balance = balance + ?
                        WHERE id = ?
                    ");

                    $stmt->bind_param(
                        "di",
                        $amount,
                        $receiverAccountId
                    );

                    $stmt->execute();


                    if (
                        $stmt->affected_rows !== 1
                    ) {

                        $stmt->close();

                        throw new Exception(
                            "Unable to update receiver balance."
                        );
                    }

                    $stmt->close();


                    // =================================
                    // SENDER TRANSACTION
                    // =================================

                    $senderType =
                        "TRANSFER_SENT";

                    $senderReference =
                        $reference
                        . " | Sent to user #"
                        . $receiverId;


                    $stmt = $conn->prepare("
                        INSERT INTO transactions
                        (
                            account_id,
                            type,
                            amount,
                            reference
                        )
                        VALUES (?, ?, ?, ?)
                    ");

                    $stmt->bind_param(
                        "isds",
                        $senderAccountId,
                        $senderType,
                        $amount,
                        $senderReference
                    );

                    if (!$stmt->execute()) {

                        $stmt->close();

                        throw new Exception(
                            "Unable to save sender transaction."
                        );
                    }

                    $stmt->close();


                    // =================================
                    // RECEIVER TRANSACTION
                    // =================================

                    $receiverType =
                        "TRANSFER_RECEIVED";

                    $receiverReference =
                        $reference
                        . " | Received from user #"
                        . $uid;


                    $stmt = $conn->prepare("
                        INSERT INTO transactions
                        (
                            account_id,
                            type,
                            amount,
                            reference
                        )
                        VALUES (?, ?, ?, ?)
                    ");

                    $stmt->bind_param(
                        "isds",
                        $receiverAccountId,
                        $receiverType,
                        $amount,
                        $receiverReference
                    );

                    if (!$stmt->execute()) {

                        $stmt->close();

                        throw new Exception(
                            "Unable to save receiver transaction."
                        );
                    }

                    $stmt->close();


                    // =================================
                    // COMMIT EVERYTHING
                    // =================================

                    $conn->commit();


                    $message =
                        "✅ ₹"
                        . number_format(
                            $amount,
                            2
                        )
                        . " sent successfully.";


                    // Keep receiver selected
                    $selectedUserId =
                        $receiverId;


                } catch (Throwable $e) {

                    // =================================
                    // ROLLBACK
                    // =================================

                    $conn->rollback();


                    $error =
                        "❌ Transfer failed. Please try again.";
                }
            }
        }
    }
}


// =====================================================
// GET SELECTED USER DETAILS
// =====================================================

if ($selectedUserId > 0) {

    $stmt = $conn->prepare("
        SELECT
            users.id,
            users.name,
            users.email,
            bank_accounts.account_number
        FROM users

        INNER JOIN bank_accounts
            ON bank_accounts.user_id = users.id

        WHERE users.id = ?
        AND users.id != ?

        LIMIT 1
    ");

    $stmt->bind_param(
        "ii",
        $selectedUserId,
        $uid
    );

    $stmt->execute();

    $selectedUser =
        $stmt
        ->get_result()
        ->fetch_assoc();

    $stmt->close();


    if (!$selectedUser) {

        if ($error === "") {

            $error =
                "❌ User Banking account not found.";
        }
    }
}

require "header.php";
?>

<!-- =====================================================
     TRANSFER PAGE
====================================================== -->

<div class="bank-transfer-page">

    <div class="bank-transfer-card">


        <!-- ICON -->

        <div class="bank-lock-icon">
            💸
        </div>


        <!-- TITLE -->

        <h2>
            Money Transfer
        </h2>


        <p>
            Send money securely to another
            ConnectHub user.
        </p>


        <!-- =============================================
             SUCCESS MESSAGE
        ============================================== -->

        <?php if ($message !== ""): ?>

            <div class="bank-success">
                <?= e($message) ?>
            </div>

        <?php endif; ?>


        <!-- =============================================
             ERROR MESSAGE
        ============================================== -->

        <?php if ($error !== ""): ?>

            <div class="bank-error">
                <?= e($error) ?>
            </div>

        <?php endif; ?>


        <!-- =============================================
             SELECTED USER
        ============================================== -->

        <?php if ($selectedUser): ?>


            <div class="receiver-card">


                <div class="receiver-icon">
                    👤
                </div>


                <div>

                    <strong>
                        <?= e(
                            $selectedUser["name"]
                        ) ?>
                    </strong>

                    <small>
                        Account:
                        <?= e(
                            $selectedUser[
                                "account_number"
                            ]
                        ) ?>
                    </small>

                </div>

            </div>



            <!-- =========================================
                 TRANSFER FORM
            ========================================== -->

            <form
                method="POST"
                class="transfer-form"
            >


                <input
                    type="hidden"
                    name="receiver_id"
                    value="<?= (int)$selectedUser["id"] ?>"
                >


                <!-- AMOUNT -->

                <label>
                    Amount
                </label>


                <input
                    type="number"
                    name="amount"
                    step="0.01"
                    min="1"
                    placeholder="Enter Amount"
                    required
                >


                <!-- PIN -->

                <label>
                    Banking PIN
                </label>


                <input
                    type="password"
                    name="transaction_pin"
                    placeholder="Enter 4-digit Banking PIN"
                    maxlength="4"
                    minlength="4"
                    inputmode="numeric"
                    pattern="[0-9]{4}"
                    autocomplete="off"
                    required
                >


                <!-- SEND -->

                <button
                    type="submit"
                    name="send_money"
                    class="bank-primary-button"
                >
                    💸 Send Money
                </button>


            </form>


        <?php else: ?>


            <!-- =========================================
                 NO USER SELECTED
            ========================================== -->

            <div class="transfer-empty">


                <div
                    style="
                        font-size:55px;
                    "
                >
                    👤
                </div>


                <h3>
                    Select a user
                </h3>


                <p>
                    Go back to Banking and search
                    for a ConnectHub user.
                </p>


                <a
                    href="bank.php"
                    class="bank-primary-button bank-link-button"
                >
                    🔎 Find User
                </a>


            </div>


        <?php endif; ?>


        <!-- =============================================
             BACK TO BANKING
        ============================================== -->

        <div
            style="
                margin-top:20px;
                text-align:center;
            "
        >

            <a
                href="bank.php"
                class="bank-back-link"
            >
                ← Back to Banking
            </a>

        </div>


    </div>

</div>



<style>

/* =====================================================
   TRANSFER PAGE
===================================================== */

.bank-transfer-page {

    width:100%;

    max-width:600px;

    margin:40px auto;

    padding:0 15px;

    box-sizing:border-box;

}


/* =====================================================
   CARD
===================================================== */

.bank-transfer-card {

    background:#ffffff;

    border-radius:22px;

    padding:30px;

    box-shadow:
        0 10px 35px
        rgba(16,24,40,.08);

    border:
        1px solid #eaecf0;

}


/* =====================================================
   ICON
===================================================== */

.bank-lock-icon {

    width:70px;

    height:70px;

    border-radius:50%;

    background:#eef2ff;

    display:flex;

    align-items:center;

    justify-content:center;

    font-size:34px;

    margin:0 auto 15px;

}


/* =====================================================
   TITLE
===================================================== */

.bank-transfer-card h2 {

    text-align:center;

    margin-bottom:8px;

    color:#111827;

}


.bank-transfer-card > p {

    text-align:center;

    color:#667085;

    line-height:1.6;

}


/* =====================================================
   SUCCESS
===================================================== */

.bank-success {

    background:#dcfce7;

    color:#166534;

    border:
        1px solid #86efac;

    padding:12px 15px;

    border-radius:10px;

    margin:15px 0;

    font-weight:600;

}


/* =====================================================
   ERROR
===================================================== */

.bank-error {

    background:#fee2e2;

    color:#b91c1c;

    border:
        1px solid #fca5a5;

    padding:12px 15px;

    border-radius:10px;

    margin:15px 0;

    font-weight:600;

}


/* =====================================================
   RECEIVER CARD
===================================================== */

.receiver-card {

    display:flex;

    align-items:center;

    gap:14px;

    padding:15px;

    background:#f8fafc;

    border:
        1px solid #e5e7eb;

    border-radius:14px;

    margin:20px 0;

}


.receiver-icon {

    width:48px;

    height:48px;

    min-width:48px;

    border-radius:50%;

    background:#6366f1;

    color:white;

    display:flex;

    align-items:center;

    justify-content:center;

    font-size:22px;

}


.receiver-card strong {

    display:block;

    font-size:17px;

    color:#111827;

}


.receiver-card small {

    display:block;

    color:#667085;

    margin-top:3px;

}


/* =====================================================
   FORM
===================================================== */

.transfer-form {

    display:flex;

    flex-direction:column;

    gap:10px;

}


.transfer-form label {

    font-weight:600;

    color:#344054;

    margin-top:8px;

}


.transfer-form input {

    width:100%;

    padding:13px;

    border:
        1px solid #d0d5dd;

    border-radius:10px;

    box-sizing:border-box;

    font-size:15px;

    outline:none;

}


.transfer-form input:focus {

    border-color:#6366f1;

    box-shadow:
        0 0 0 3px
        rgba(99,102,241,.12);

}


/* =====================================================
   BUTTON
===================================================== */

.bank-primary-button {

    display:inline-flex;

    align-items:center;

    justify-content:center;

    gap:6px;

    border:none;

    background:#4f46e5;

    color:white;

    padding:12px 18px;

    border-radius:10px;

    cursor:pointer;

    text-decoration:none;

    font-weight:600;

}


.bank-primary-button:hover {

    background:#4338ca;

}


.transfer-form
.bank-primary-button {

    width:100%;

    margin-top:15px;

    font-size:15px;

}


/* =====================================================
   EMPTY
===================================================== */

.transfer-empty {

    text-align:center;

    padding:30px 10px;

}


.transfer-empty h3 {

    color:#111827;

}


.transfer-empty p {

    color:#667085;

    line-height:1.6;

}


.bank-link-button {

    display:inline-flex;

    margin-top:10px;

}


/* =====================================================
   BACK LINK
===================================================== */

.bank-back-link {

    color:#4f46e5;

    text-decoration:none;

    font-weight:600;

}


.bank-back-link:hover {

    text-decoration:underline;

}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width:650px) {

    .bank-transfer-page {

        margin:20px auto;

        padding:
            0 10px;

    }


    .bank-transfer-card {

        padding:22px;

        border-radius:18px;

    }


    .receiver-card {

        align-items:flex-start;

    }

}

</style>


<?php

require "footer.php";

?>