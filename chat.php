<?php
// ============================================================
// CONNECTHUB - ADVANCED MESSAGES / CHAT
// ============================================================
// FEATURES
// ------------------------------------------------------------
// • Advanced blue / cyan / indigo interface
// • Dynamic GIF library
// • Add unlimited future GIFs without changing this file
// • Supports GIF / JPG / JPEG / PNG / WEBP
// • Recursive GIF folder scanning
// • GIF search
// • Animated online / offline status
// • last_seen heartbeat
// • Conversation search
// • Message previews
// • Shared post messages
// • Image / video shared posts
// • Message copy button
// • Animated message bubbles
// • Date separators
// • Responsive mobile interface
// • Profile images
// • No email displayed
// ============================================================


// ============================================================
// CONFIG
// ============================================================

require "config.php";

login_required();


$uid = (int)($_SESSION["user_id"] ?? 0);

if ($uid <= 0) {

    header("Location: login.php");
    exit;

}


// ============================================================
// HELPER FUNCTIONS
// ============================================================

function safeProfilePath($value)
{
    $value = trim((string)$value);

    if ($value === "") {
        return "";
    }

    if (strpos($value, "uploads/") === 0) {
        return $value;
    }

    if (strpos($value, "/") !== false) {
        return ltrim($value, "/");
    }

    return "uploads/" . basename($value);
}


function safeMediaPath($value)
{
    $value = trim((string)$value);

    if ($value === "") {
        return "";
    }

    $value = str_replace("\\", "/", $value);

    $value = ltrim($value, "/");

    if (strpos($value, "uploads/") === 0) {
        return $value;
    }

    return "uploads/" . $value;
}


function getUserInitial($name)
{
    $name = trim((string)$name);

    if ($name === "") {
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


function findExistingChatId($conn, $uid, $other)
{
    $stmt = $conn->prepare("
        SELECT c.id
        FROM chats c
        INNER JOIN chat_members cm1
            ON cm1.chat_id = c.id
        INNER JOIN chat_members cm2
            ON cm2.chat_id = c.id
        WHERE cm1.user_id = ?
        AND cm2.user_id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param(
        "ii",
        $uid,
        $other
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $row = $result
        ? $result->fetch_assoc()
        : null;

    $stmt->close();

    return $row
        ? (int)$row["id"]
        : 0;
}


function createOrGetChat($conn, $uid, $other)
{
    $existing = findExistingChatId(
        $conn,
        $uid,
        $other
    );

    if ($existing > 0) {
        return $existing;
    }

    $result = $conn->query("
        INSERT INTO chats ()
        VALUES ()
    ");

    if ($result === false) {
        return 0;
    }

    $chatId = (int)$conn->insert_id;

    if ($chatId <= 0) {
        return 0;
    }


    $stmt = $conn->prepare("
        INSERT INTO chat_members
        (
            chat_id,
            user_id
        )
        VALUES (?, ?)
    ");

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param(
        "ii",
        $chatId,
        $uid
    );

    $stmt->execute();
    $stmt->close();


    $stmt = $conn->prepare("
        INSERT INTO chat_members
        (
            chat_id,
            user_id
        )
        VALUES (?, ?)
    ");

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param(
        "ii",
        $chatId,
        $other
    );

    $stmt->execute();
    $stmt->close();


    return $chatId;
}


function isUserOnline($lastSeen)
{
    if (empty($lastSeen)) {
        return false;
    }

    $timestamp = strtotime($lastSeen);

    if (!$timestamp) {
        return false;
    }

    return (
        time() -
        $timestamp
    ) <= 70;
}


function relativeLastSeen($lastSeen)
{
    if (empty($lastSeen)) {
        return "Offline";
    }

    $timestamp = strtotime($lastSeen);

    if (!$timestamp) {
        return "Offline";
    }

    $diff = time() - $timestamp;

    if ($diff <= 70) {
        return "Active now";
    }

    if ($diff < 3600) {

        $minutes = max(
            1,
            floor($diff / 60)
        );

        return "Active " .
            $minutes .
            " min ago";
    }

    if ($diff < 86400) {

        $hours = max(
            1,
            floor($diff / 3600)
        );

        return "Active " .
            $hours .
            " hr ago";
    }

    return "Offline";
}


function collectGifFiles($directory, $relativePrefix = "")
{
    $allowed = [
        "gif",
        "jpg",
        "jpeg",
        "png",
        "webp"
    ];

    $output = [];


    if (!is_dir($directory)) {
        return $output;
    }


    $items = @scandir($directory);

    if (!is_array($items)) {
        return $output;
    }


    foreach ($items as $item) {

        if (
            $item === "." ||
            $item === ".."
        ) {
            continue;
        }


        $fullPath =
            rtrim(
                $directory,
                DIRECTORY_SEPARATOR
            ) .
            DIRECTORY_SEPARATOR .
            $item;


        $relativePath =
            $relativePrefix === ""
                ? $item
                : $relativePrefix .
                  "/" .
                  $item;


        if (is_dir($fullPath)) {

            $nested =
                collectGifFiles(
                    $fullPath,
                    $relativePath
                );

            foreach (
                $nested
                as $nestedFile
            ) {
                $output[] =
                    $nestedFile;
            }

            continue;
        }


        if (!is_file($fullPath)) {
            continue;
        }


        $extension =
            strtolower(
                pathinfo(
                    $item,
                    PATHINFO_EXTENSION
                )
            );


        if (
            in_array(
                $extension,
                $allowed,
                true
            )
        ) {

            $output[] =
                $relativePath;

        }

    }


    return $output;
}


// ============================================================
// CHECK / CREATE last_seen COLUMN
// ============================================================

$hasLastSeen = false;


$columnStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'last_seen'
");


if ($columnStmt) {

    $columnStmt->execute();

    $columnResult =
        $columnStmt->get_result();

    $columnRow =
        $columnResult
            ? $columnResult->fetch_assoc()
            : null;

    $columnStmt->close();


    if (
        $columnRow &&
        (int)$columnRow["total"] > 0
    ) {

        $hasLastSeen = true;

    }

}


// ============================================================
// CREATE STATUS COLUMN IF MISSING
// ============================================================

if (!$hasLastSeen) {

    @$conn->query("
        ALTER TABLE users
        ADD COLUMN last_seen DATETIME NULL
    ");


    $hasLastSeen = true;

}


// ============================================================
// HEARTBEAT AJAX
// ============================================================

if (
    isset($_GET["heartbeat"]) &&
    $_GET["heartbeat"] === "1"
) {

    if ($hasLastSeen) {

        $stmt = $conn->prepare("
            UPDATE users
            SET last_seen = NOW()
            WHERE id = ?
        ");

        if ($stmt) {

            $stmt->bind_param(
                "i",
                $uid
            );

            $stmt->execute();

            $stmt->close();
        }

    }


    header(
        "Content-Type: application/json; charset=utf-8"
    );


    echo json_encode([
        "success" => true,
        "online" => true
    ]);


    exit;
}


// ============================================================
// STATUS AJAX
// ============================================================

if (
    isset($_GET["status"]) &&
    $_GET["status"] === "1"
) {

    header(
        "Content-Type: application/json; charset=utf-8"
    );


    $idsParameter =
        trim(
            (string)(
                $_GET["ids"] ?? ""
            )
        );


    $idArray = [];


    if ($idsParameter !== "") {

        foreach (
            explode(
                ",",
                $idsParameter
            )
            as $rawId
        ) {

            $id =
                (int)$rawId;

            if ($id > 0) {
                $idArray[$id] = $id;
            }

        }

    }


    if (count($idArray) === 0) {

        echo json_encode([]);

        exit;

    }


    $statusOutput = [];


    if ($hasLastSeen) {

        $safeIds =
            implode(
                ",",
                array_map(
                    "intval",
                    array_values(
                        $idArray
                    )
                )
            );


        $result =
            $conn->query("
                SELECT
                    id,
                    last_seen
                FROM users
                WHERE id IN ($safeIds)
            ");


        if ($result) {

            while (
                $row =
                $result->fetch_assoc()
            ) {

                $lastSeen =
                    $row["last_seen"] ?? null;


                $statusOutput[
                    (int)$row["id"]
                ] = [

                    "online" =>
                        isUserOnline(
                            $lastSeen
                        ),

                    "last_seen" =>
                        $lastSeen

                ];

            }

        }

    }


    echo json_encode(
        $statusOutput
    );


    exit;
}


// ============================================================
// UPDATE CURRENT USER STATUS
// ============================================================

if ($hasLastSeen) {

    $stmt = $conn->prepare("
        UPDATE users
        SET last_seen = NOW()
        WHERE id = ?
    ");

    if ($stmt) {

        $stmt->bind_param(
            "i",
            $uid
        );

        $stmt->execute();

        $stmt->close();

    }

}


// ============================================================
// SELECTED USER
// ============================================================

$selected =
    (int)(
        $_GET["user"]
        ??
        $_POST["other"]
        ??
        0
    );


// ============================================================
// SEND MESSAGE
// ============================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["send"])
) {

    $other =
        (int)(
            $_POST["other"] ?? 0
        );


    $message =
        trim(
            $_POST["message"] ?? ""
        );


    if (
        $other <= 0 ||
        $other === $uid
    ) {

        $error =
            "Invalid recipient.";

    } elseif (
        $message === ""
    ) {

        $error =
            "Please type a message.";

    } else {

        $chatId =
            createOrGetChat(
                $conn,
                $uid,
                $other
            );


        if (
            $chatId <= 0
        ) {

            $error =
                "Unable to open conversation.";

        } else {

            $filePath = "";

            $messageType = "text";


            $stmt = $conn->prepare("
                INSERT INTO messages
                (
                    chat_id,
                    sender_id,
                    message,
                    file_path,
                    message_type
                )
                VALUES (?, ?, ?, ?, ?)
            ");


            if ($stmt) {

                $stmt->bind_param(
                    "iisss",
                    $chatId,
                    $uid,
                    $message,
                    $filePath,
                    $messageType
                );


                if (
                    $stmt->execute()
                ) {

                    $stmt->close();


                    header(
                        "Location: chat.php?user=" .
                        $other
                    );

                    exit;
                }


                $stmt->close();

            }


            $error =
                "Unable to send message.";

        }

    }

}


// ============================================================
// SEND GIF / IMAGE
// ============================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["send_saved_gif"])
) {

    $other =
        (int)(
            $_POST["other"] ?? 0
        );


    $relativeGif =
        trim(
            str_replace(
                "\\",
                "/",
                (string)(
                    $_POST["gif_name"] ?? ""
                )
            )
        );


    $relativeGif =
        ltrim(
            $relativeGif,
            "/"
        );


    if (
        $other <= 0 ||
        $other === $uid ||
        $relativeGif === ""
    ) {

        header(
            "Location: chat.php"
        );

        exit;
    }


    // Prevent path traversal.

    if (
        strpos(
            $relativeGif,
            ".."
        ) !== false
    ) {

        header(
            "Location: chat.php?user=" .
            $other
        );

        exit;
    }


    $gifDirectory =
        __DIR__ .
        "/uploads/gifs/";


    $fullGifPath =
        $gifDirectory .
        str_replace(
            "/",
            DIRECTORY_SEPARATOR,
            $relativeGif
        );


    $extension =
        strtolower(
            pathinfo(
                $relativeGif,
                PATHINFO_EXTENSION
            )
        );


    $allowed =
        [
            "gif",
            "jpg",
            "jpeg",
            "png",
            "webp"
        ];


    if (
        !in_array(
            $extension,
            $allowed,
            true
        )
        ||
        !is_file(
            $fullGifPath
        )
    ) {

        header(
            "Location: chat.php?user=" .
            $other
        );

        exit;
    }


    $chatId =
        createOrGetChat(
            $conn,
            $uid,
            $other
        );


    if (
        $chatId <= 0
    ) {

        header(
            "Location: chat.php?user=" .
            $other
        );

        exit;
    }


    $filePath =
        "gifs/" .
        $relativeGif;


    $message =
        "🎬 Media";


    $messageType =
        "gif";


    $stmt =
        $conn->prepare("
            INSERT INTO messages
            (
                chat_id,
                sender_id,
                message,
                file_path,
                message_type
            )
            VALUES (?, ?, ?, ?, ?)
        ");


    if ($stmt) {

        $stmt->bind_param(
            "iisss",
            $chatId,
            $uid,
            $message,
            $filePath,
            $messageType
        );

        $stmt->execute();

        $stmt->close();

    }


    header(
        "Location: chat.php?user=" .
        $other
    );

    exit;
}


// ============================================================
// GET ALL PEOPLE YOU FOLLOW
// ============================================================

$people = [];


$peopleQuery =
    "
    SELECT
        u.id,
        u.name,
        u.profile_image
    " .
    (
        $hasLastSeen
            ? ", u.last_seen"
            : ""
    ) .
    "
    FROM users u

    INNER JOIN follows f
        ON f.following_id = u.id

    WHERE f.follower_id = ?
    AND u.id != ?

    ORDER BY u.name ASC
    ";


$stmt =
    $conn->prepare(
        $peopleQuery
    );


if ($stmt) {

    $stmt->bind_param(
        "ii",
        $uid,
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

            $personId =
                (int)$row["id"];


            $row["chat_id"] =
                findExistingChatId(
                    $conn,
                    $uid,
                    $personId
                );


            $row["last_message"] =
                "";


            $row["last_message_time"] =
                "";


            if (
                (int)$row["chat_id"] > 0
            ) {

                $previewStmt =
                    $conn->prepare("
                        SELECT
                            message,
                            message_type,
                            created_at
                        FROM messages
                        WHERE chat_id = ?
                        ORDER BY id DESC
                        LIMIT 1
                    ");


                if ($previewStmt) {

                    $previewChatId =
                        (int)$row["chat_id"];


                    $previewStmt->bind_param(
                        "i",
                        $previewChatId
                    );


                    $previewStmt->execute();


                    $previewResult =
                        $previewStmt
                            ->get_result();


                    if (
                        $previewResult
                    ) {

                        $preview =
                            $previewResult
                                ->fetch_assoc();


                        if ($preview) {

                            $previewText =
                                trim(
                                    (string)(
                                        $preview[
                                            "message"
                                        ] ?? ""
                                    )
                                );


                            $previewType =
                                $preview[
                                    "message_type"
                                ] ?? "";


                            if (
                                $previewType
                                ===
                                "gif"
                            ) {

                                $previewText =
                                    "🎬 Media";

                            } elseif (
                                $previewType
                                ===
                                "post"
                            ) {

                                $previewText =
                                    "📸 Shared post";

                            }


                            $row[
                                "last_message"
                            ] =
                                $previewText;


                            $row[
                                "last_message_time"
                            ] =
                                $preview[
                                    "created_at"
                                ] ?? "";

                        }

                    }


                    $previewStmt->close();

                }

            }


            $people[] =
                $row;

        }

    }


    $stmt->close();

}


// ============================================================
// SELECTED PERSON
// ============================================================

$person = null;


if (
    $selected > 0 &&
    $selected !== $uid
) {

    $selectedQuery =
        "
        SELECT
            id,
            name,
            profile_image
        " .
        (
            $hasLastSeen
                ? ", last_seen"
                : ""
        ) .
        "
        FROM users
        WHERE id = ?
        LIMIT 1
        ";


    $stmt =
        $conn->prepare(
            $selectedQuery
        );


    if ($stmt) {

        $stmt->bind_param(
            "i",
            $selected
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        if ($result) {

            $person =
                $result->fetch_assoc();

        }


        $stmt->close();

    }

}


// ============================================================
// CHAT ID
// ============================================================

$chatId = 0;


if (
    $person
) {

    $chatId =
        findExistingChatId(
            $conn,
            $uid,
            $selected
        );

}


// ============================================================
// LOAD ALL GIFS DYNAMICALLY
// ============================================================

$gifDirectory =
    __DIR__ .
    "/uploads/gifs/";


$savedGifs =
    collectGifFiles(
        $gifDirectory
    );


usort(
    $savedGifs,
    function (
        $a,
        $b
    ) {

        return strnatcasecmp(
            $a,
            $b
        );

    }
);


// ============================================================
// LOAD MESSAGES
// ============================================================

$messages = [];


if (
    $chatId > 0
) {

    $stmt =
        $conn->prepare("
            SELECT
                m.id,
                m.sender_id,
                m.message,
                m.file_path,
                m.message_type,
                m.created_at,
                u.name,
                u.profile_image
            FROM messages m

            INNER JOIN users u
                ON u.id = m.sender_id

            WHERE m.chat_id = ?

            ORDER BY m.id ASC
        ");


    if ($stmt) {

        $stmt->bind_param(
            "i",
            $chatId
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        if ($result) {

            while (
                $row =
                $result->fetch_assoc()
            ) {

                $messages[] =
                    $row;

            }

        }


        $stmt->close();

    }

}


// ============================================================
// SELECTED PERSON STATUS
// ============================================================

$personOnline = false;

$personStatus = "Offline";


if (
    $person &&
    $hasLastSeen
) {

    $personOnline =
        isUserOnline(
            $person["last_seen"] ??
            null
        );


    $personStatus =
        relativeLastSeen(
            $person["last_seen"] ??
            null
        );

}


$personImage =
    $person
        ? safeProfilePath(
            $person["profile_image"] ?? ""
        )
        : "";


$personInitial =
    $person
        ? getUserInitial(
            $person["name"] ?? "User"
        )
        : "U";


// ============================================================
// LOAD HEADER
// ============================================================

require "header.php";

?>

<div class="advanced-chat-page">

    <!-- ========================================================
         PAGE HERO
    ========================================================= -->

    <section class="chat-page-header">

        <div class="chat-hero-glow glow-a"></div>
        <div class="chat-hero-glow glow-b"></div>
        <div class="chat-hero-glow glow-c"></div>

        <div class="chat-hero-icon">
            💬
        </div>

        <div class="chat-hero-text">

            <div class="chat-eyebrow">
                CONNECTHUB COMMUNICATION
            </div>

            <h1>
                Messages
            </h1>

            <p>
                Connect, chat and share moments instantly.
            </p>

        </div>

        <div class="chat-live-badge">

            <span></span>

            LIVE

        </div>

    </section>


    <!-- ========================================================
         CHAT CONTAINER
    ========================================================= -->

    <section class="chat-container">


        <!-- ====================================================
             PEOPLE PANEL
        ===================================================== -->

        <aside class="people-panel">


            <div class="people-panel-header">

                <div>

                    <span>
                        CONNECTHUB
                    </span>

                    <h2>
                        Conversations
                    </h2>

                </div>

                <div class="people-count">
                    <?= count($people) ?>
                </div>

            </div>


            <!-- SEARCH -->

            <div class="conversation-search">

                <span>
                    ⌕
                </span>

                <input
                    type="text"
                    id="conversationSearch"
                    placeholder="Search people..."
                    autocomplete="off"
                >

            </div>


            <!-- PEOPLE -->

            <div
                class="people-list"
                id="peopleList"
            >

                <?php if (
                    count($people) > 0
                ): ?>

                    <?php foreach (
                        $people
                        as $p
                    ): ?>

                        <?php

                        $pId =
                            (int)$p["id"];


                        $pImage =
                            safeProfilePath(
                                $p[
                                    "profile_image"
                                ] ?? ""
                            );


                        $pInitial =
                            getUserInitial(
                                $p["name"] ??
                                "User"
                            );


                        $pOnline =
                            $hasLastSeen &&
                            isUserOnline(
                                $p["last_seen"]
                                ?? null
                            );


                        $pStatus =
                            $pOnline
                                ? "Active now"
                                : relativeLastSeen(
                                    $p[
                                        "last_seen"
                                    ] ?? null
                                );


                        $preview =
                            trim(
                                (string)(
                                    $p[
                                        "last_message"
                                    ] ?? ""
                                )
                            );


                        if (
                            $preview === ""
                        ) {

                            $preview =
                                "Start a conversation";

                        }


                        if (
                            function_exists(
                                "mb_strimwidth"
                            )
                        ) {

                            $preview =
                                mb_strimwidth(
                                    $preview,
                                    0,
                                    35,
                                    "..."
                                );

                        } else {

                            $preview =
                                substr(
                                    $preview,
                                    0,
                                    35
                                );

                        }


                        ?>

                        <a
                            href="chat.php?user=<?= $pId ?>"
                            class="
                                conversation-item
                                <?= $selected === $pId
                                    ? "conversation-active"
                                    : ""
                                ?>
                            "
                            data-user-id="<?= $pId ?>"
                            data-name="<?= e(
                                strtolower(
                                    $p["name"] ??
                                    ""
                                )
                            ) ?>"
                        >

                            <!-- AVATAR -->

                            <div class="conversation-avatar">

                                <?php if (
                                    $pImage !== ""
                                ): ?>

                                    <img
                                        src="<?= e(
                                            $pImage
                                        ) ?>"
                                        alt="Profile"
                                        loading="lazy"
                                    >

                                <?php else: ?>

                                    <div class="
                                        conversation-avatar-fallback
                                    ">
                                        <?= e(
                                            $pInitial
                                        ) ?>
                                    </div>

                                <?php endif; ?>


                                <span
                                    class="
                                        conversation-status
                                        <?= $pOnline
                                            ? "status-online"
                                            : "status-offline"
                                        ?>"
                                    data-status-for="<?= $pId ?>"
                                ></span>

                            </div>


                            <!-- INFORMATION -->

                            <div class="conversation-details">

                                <div class="
                                    conversation-name-row
                                ">

                                    <strong>
                                        <?= e(
                                            $p["name"]
                                        ) ?>
                                    </strong>

                                    <?php if (
                                        !empty(
                                            $p[
                                                "last_message_time"
                                            ]
                                        )
                                    ): ?>

                                        <time>
                                            <?= e(
                                                date(
                                                    "H:i",
                                                    strtotime(
                                                        $p[
                                                            "last_message_time"
                                                        ]
                                                    )
                                                )
                                            ) ?>
                                        </time>

                                    <?php endif; ?>

                                </div>


                                <div class="
                                    conversation-preview
                                ">

                                    <?= e(
                                        $preview
                                    ) ?>

                                </div>


                                <div
                                    class="
                                        conversation-status-text
                                        <?= $pOnline
                                            ? "text-online"
                                            : ""
                                        ?>"
                                    data-status-text-for="<?= $pId ?>"
                                >

                                    <?= e(
                                        $pStatus
                                    ) ?>

                                </div>

                            </div>


                            <div class="conversation-arrow">
                                ›
                            </div>

                        </a>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="no-conversations">

                        <div class="no-conversation-icon">
                            👥
                        </div>

                        <h3>
                            No Connections Yet
                        </h3>

                        <p>
                            Follow people from
                            Find People to start
                            chatting.
                        </p>

                        <a
                            href="users.php"
                            class="discover-button"
                        >
                            👥 Find People
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </aside>


        <!-- ====================================================
             CHAT WINDOW
        ===================================================== -->

        <main class="chat-window">


            <?php if (
                $person
            ): ?>


                <!-- =================================================
                     SELECTED USER HEADER
                ================================================== -->

                <div class="chat-window-header">


                    <div class="selected-person">


                        <div class="selected-avatar">

                            <?php if (
                                $personImage !== ""
                            ): ?>

                                <img
                                    src="<?= e(
                                        $personImage
                                    ) ?>"
                                    alt="Profile"
                                >

                            <?php else: ?>

                                <div
                                    class="
                                        selected-avatar-fallback
                                    "
                                >
                                    <?= e(
                                        $personInitial
                                    ) ?>
                                </div>

                            <?php endif; ?>


                            <span
                                id="selectedStatusDot"
                                class="
                                    selected-status-dot
                                    <?= $personOnline
                                        ? "status-online"
                                        : "status-offline"
                                    ?>"
                            ></span>

                        </div>


                        <div class="selected-person-info">

                            <div class="selected-name">
                                <?= e(
                                    $person["name"]
                                ) ?>
                            </div>

                            <div
                                id="selectedStatusText"
                                class="
                                    selected-status-text
                                    <?= $personOnline
                                        ? "online-text"
                                        : ""
                                    ?>"
                            >
                                <?= e(
                                    $personStatus
                                ) ?>
                            </div>

                        </div>

                    </div>


                    <div class="chat-header-actions">


                        <div class="secure-chat-indicator">

                            <span>
                                🔐
                            </span>

                            Secure

                        </div>


                        <button
                            type="button"
                            class="header-icon-button"
                            id="focusConversationSearch"
                            title="Search people"
                        >
                            ⌕
                        </button>

                    </div>

                </div>


                <!-- =================================================
                     MESSAGES
                ================================================== -->

                <div
                    class="message-area"
                    id="messageArea"
                >


                    <div class="chat-start-label">

                        <span>
                            CONNECTHUB CHAT
                        </span>

                    </div>


                    <?php if (
                        count($messages) > 0
                    ): ?>


                        <?php

                        $previousDate = "";

                        ?>


                        <?php foreach (
                            $messages
                            as $m
                        ): ?>


                            <?php

                            $isMine =
                                (
                                    (int)$m[
                                        "sender_id"
                                    ] ===
                                    $uid
                                );


                            $messageDate =
                                !empty(
                                    $m[
                                        "created_at"
                                    ]
                                )
                                    ? date(
                                        "Y-m-d",
                                        strtotime(
                                            $m[
                                                "created_at"
                                            ]
                                        )
                                    )
                                    : "";


                            ?>


                            <?php if (
                                $messageDate !==
                                $previousDate
                            ): ?>


                                <?php

                                $previousDate =
                                    $messageDate;


                                if (
                                    $messageDate ===
                                    date(
                                        "Y-m-d"
                                    )
                                ) {

                                    $displayDate =
                                        "Today";

                                } elseif (
                                    $messageDate ===
                                    date(
                                        "Y-m-d",
                                        strtotime(
                                            "-1 day"
                                        )
                                    )
                                ) {

                                    $displayDate =
                                        "Yesterday";

                                } else {

                                    $displayDate =
                                        date(
                                            "d M Y",
                                            strtotime(
                                                $messageDate
                                            )
                                        );

                                }

                                ?>


                                <div
                                    class="
                                        message-date-separator
                                    "
                                >

                                    <span>
                                        <?= e(
                                            $displayDate
                                        ) ?>
                                    </span>

                                </div>

                            <?php endif; ?>


                            <?php

                            $senderImage =
                                safeProfilePath(
                                    $m[
                                        "profile_image"
                                    ] ?? ""
                                );


                            $senderInitial =
                                getUserInitial(
                                    $m[
                                        "name"
                                    ] ?? "User"
                                );


                            $messageType =
                                strtolower(
                                    trim(
                                        $m[
                                            "message_type"
                                        ] ?? "text"
                                    )
                                );


                            ?>


                            <div
                                class="
                                    message-row
                                    <?= $isMine
                                        ? "message-mine"
                                        : "message-theirs"
                                    ?>"
                                data-message-id="<?= (int)$m["id"] ?>"
                            >


                                <?php if (
                                    !$isMine
                                ): ?>

                                    <div class="message-avatar">

                                        <?php if (
                                            $senderImage !== ""
                                        ): ?>

                                            <img
                                                src="<?= e(
                                                    $senderImage
                                                ) ?>"
                                                alt="Profile"
                                                loading="lazy"
                                            >

                                        <?php else: ?>

                                            <div class="
                                                message-avatar-fallback
                                            ">
                                                <?= e(
                                                    $senderInitial
                                                ) ?>
                                            </div>

                                        <?php endif; ?>

                                    </div>

                                <?php endif; ?>


                                <div
                                    class="
                                        message-content-wrap
                                    "
                                >


                                    <?php if (
                                        !$isMine
                                    ): ?>

                                        <div
                                            class="
                                                message-sender-name
                                            "
                                        >
                                            <?= e(
                                                $m[
                                                    "name"
                                                ]
                                            ) ?>
                                        </div>

                                    <?php endif; ?>


                                    <div class="message-bubble">


                                        <div
                                            class="
                                                message-light-line
                                            "
                                        ></div>


                                        <?php if (
                                            $messageType ===
                                            "gif"
                                        ): ?>


                                            <?php

                                            $messageMedia =
                                                safeMediaPath(
                                                    $m[
                                                        "file_path"
                                                    ] ?? ""
                                                );


                                            ?>


                                            <?php if (
                                                $messageMedia
                                                !==
                                                ""
                                            ): ?>

                                                <div
                                                    class="
                                                        message-media
                                                    "
                                                >

                                                    <img
                                                        src="<?= e(
                                                            $messageMedia
                                                        ) ?>"
                                                        alt="Shared media"
                                                        class="
                                                            message-gif
                                                        "
                                                        loading="lazy"
                                                        onerror="
                                                            this.style.display='none';
                                                            this.parentElement.classList.add('media-broken');
                                                        "
                                                    >

                                                    <div
                                                        class="
                                                            media-fallback
                                                        "
                                                    >
                                                        🎬
                                                    </div>

                                                </div>

                                            <?php else: ?>

                                                <div
                                                    class="
                                                        unavailable-media
                                                    "
                                                >
                                                    🎬 Media unavailable
                                                </div>

                                            <?php endif; ?>


                                        <?php elseif (
                                            $messageType ===
                                            "post"
                                        ): ?>


                                            <?php

                                            $sharedPostId =
                                                (int)(
                                                    $m[
                                                        "file_path"
                                                    ] ?? 0
                                                );


                                            $sharedPost =
                                                null;


                                            if (
                                                $sharedPostId
                                                >
                                                0
                                            ) {

                                                $postStmt =
                                                    $conn->prepare("
                                                        SELECT
                                                            p.id,
                                                            p.image,
                                                            p.caption,
                                                            p.media_type,
                                                            p.created_at,
                                                            u.name,
                                                            u.profile_image
                                                        FROM posts p
                                                        INNER JOIN users u
                                                            ON u.id = p.user_id
                                                        WHERE p.id = ?
                                                        LIMIT 1
                                                    ");


                                                if (
                                                    $postStmt
                                                ) {

                                                    $postStmt->bind_param(
                                                        "i",
                                                        $sharedPostId
                                                    );


                                                    $postStmt->execute();


                                                    $postResult =
                                                        $postStmt
                                                            ->get_result();


                                                    if (
                                                        $postResult
                                                    ) {

                                                        $sharedPost =
                                                            $postResult
                                                                ->fetch_assoc();

                                                    }


                                                    $postStmt->close();

                                                }

                                            }


                                            ?>


                                            <?php if (
                                                $sharedPost
                                            ): ?>


                                                <?php

                                                $sharedPostMedia =
                                                    safeMediaPath(
                                                        $sharedPost[
                                                            "image"
                                                        ] ?? ""
                                                    );


                                                $sharedPostType =
                                                    strtolower(
                                                        trim(
                                                            $sharedPost[
                                                                "media_type"
                                                            ] ?? "image"
                                                        )
                                                    );


                                                ?>


                                                <div
                                                    class="
                                                        shared-post-card
                                                    "
                                                >


                                                    <div
                                                        class="
                                                            shared-post-header
                                                        "
                                                    >

                                                        <div
                                                            class="
                                                                shared-post-avatar
                                                            "
                                                        >

                                                            <?php

                                                            $sharedOwnerImage =
                                                                safeProfilePath(
                                                                    $sharedPost[
                                                                        "profile_image"
                                                                    ] ?? ""
                                                                );

                                                            ?>


                                                            <?php if (
                                                                $sharedOwnerImage
                                                                !==
                                                                ""
                                                            ): ?>

                                                                <img
                                                                    src="<?= e(
                                                                        $sharedOwnerImage
                                                                    ) ?>"
                                                                    alt="Profile"
                                                                >

                                                            <?php else: ?>

                                                                <?= e(
                                                                    getUserInitial(
                                                                        $sharedPost[
                                                                            "name"
                                                                        ] ?? "U"
                                                                    )
                                                                ) ?>

                                                            <?php endif; ?>

                                                        </div>


                                                        <div>

                                                            <strong>
                                                                <?= e(
                                                                    $sharedPost[
                                                                        "name"
                                                                    ]
                                                                ) ?>
                                                            </strong>

                                                            <small>
                                                                Shared post
                                                            </small>

                                                        </div>

                                                    </div>


                                                    <?php if (
                                                        $sharedPostMedia
                                                        !==
                                                        ""
                                                    ): ?>


                                                        <?php if (
                                                            $sharedPostType
                                                            ===
                                                            "video"
                                                        ): ?>

                                                            <video
                                                                src="<?= e(
                                                                    $sharedPostMedia
                                                                ) ?>"
                                                                controls
                                                                playsinline
                                                                preload="metadata"
                                                                class="
                                                                    shared-post-media
                                                                "
                                                            ></video>

                                                        <?php else: ?>

                                                            <img
                                                                src="<?= e(
                                                                    $sharedPostMedia
                                                                ) ?>"
                                                                alt="Shared post"
                                                                class="
                                                                    shared-post-media
                                                                "
                                                                loading="lazy"
                                                            >

                                                        <?php endif; ?>


                                                    <?php endif; ?>


                                                    <?php if (
                                                        !empty(
                                                            $sharedPost[
                                                                "caption"
                                                            ]
                                                        )
                                                    ): ?>

                                                        <div
                                                            class="
                                                                shared-post-caption
                                                            "
                                                        >

                                                            <?= nl2br(
                                                                e(
                                                                    $sharedPost[
                                                                        "caption"
                                                                    ]
                                                                )
                                                            ) ?>

                                                        </div>

                                                    <?php endif; ?>


                                                    <div
                                                        class="
                                                            shared-post-footer
                                                        "
                                                    >

                                                        📸
                                                        Shared through ConnectHub

                                                    </div>


                                                </div>


                                            <?php else: ?>


                                                <div
                                                    class="
                                                        unavailable-media
                                                    "
                                                >
                                                    📸 Shared post unavailable
                                                </div>


                                            <?php endif; ?>


                                        <?php else: ?>


                                            <div class="message-text">

                                                <?= nl2br(
                                                    e(
                                                        $m[
                                                            "message"
                                                        ] ?? ""
                                                    )
                                                ) ?>

                                            </div>


                                        <?php endif; ?>


                                        <div class="message-meta">

                                            <span>

                                                <?= e(
                                                    date(
                                                        "H:i",
                                                        strtotime(
                                                            $m[
                                                                "created_at"
                                                            ]
                                                        )
                                                    )
                                                ) ?>

                                            </span>


                                            <?php if (
                                                $isMine
                                            ): ?>

                                                <span class="
                                                    sent-status
                                                ">
                                                    ✓ Sent
                                                </span>

                                            <?php endif; ?>

                                        </div>


                                    </div>


                                    <button
                                        type="button"
                                        class="
                                            message-copy-button
                                        "
                                        title="Copy message"
                                        data-copy="<?= e(
                                            strip_tags(
                                                $m[
                                                    "message"
                                                ] ?? ""
                                            )
                                        ) ?>"
                                    >
                                        ⧉
                                    </button>


                                </div>

                            </div>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <div class="empty-chat">

                            <div class="empty-orbit">

                                <div>
                                    💬
                                </div>

                            </div>


                            <div class="empty-chat-label">
                                NEW CONVERSATION
                            </div>


                            <h2>
                                Start chatting with
                                <?= e(
                                    $person["name"]
                                ) ?>
                            </h2>


                            <p>
                                Send a message, GIF or shared
                                post to start the conversation.
                            </p>


                            <div class="empty-pulse-row">

                                <span></span>
                                <span></span>
                                <span></span>

                            </div>

                        </div>


                    <?php endif; ?>


                    <div
                        id="messageBottom"
                    ></div>


                </div>


                <!-- =================================================
                     COMPOSER
                ================================================== -->

                <div class="message-composer">


                    <!-- =================================================
                         GIF POPUP
                    ================================================== -->

                    <div
                        id="gifPopup"
                        class="gif-popup"
                    >


                        <div class="gif-popup-header">


                            <div>

                                <span>
                                    CONNECTHUB MEDIA
                                </span>

                                <strong>
                                    GIF Library
                                </strong>

                            </div>


                            <button
                                type="button"
                                id="closeGif"
                                class="gif-close"
                            >
                                ×
                            </button>


                        </div>


                        <!-- GIF SEARCH -->

                        <div class="gif-search-box">

                            <span>
                                ⌕
                            </span>

                            <input
                                type="text"
                                id="gifSearch"
                                placeholder="Search GIFs..."
                                autocomplete="off"
                            >

                        </div>


                        <div
                            id="gifResultsCount"
                            class="gif-results-count"
                        >

                            <?= count(
                                $savedGifs
                            ) ?>
                            media files

                        </div>


                        <?php if (
                            count($savedGifs) > 0
                        ): ?>


                            <div
                                class="gif-grid"
                                id="gifGrid"
                            >


                                <?php foreach (
                                    $savedGifs
                                    as $gif
                                ): ?>


                                    <?php

                                    $gifUrl =
                                        "uploads/gifs/" .
                                        $gif;


                                    $gifSearchName =
                                        strtolower(
                                            basename(
                                                $gif
                                            )
                                        );

                                    ?>


                                    <form
                                        method="POST"
                                        class="
                                            gif-item-form
                                        "
                                        data-gif-name="<?= e(
                                            $gifSearchName
                                        ) ?>"
                                    >


                                        <input
                                            type="hidden"
                                            name="other"
                                            value="<?= $selected ?>"
                                        >


                                        <input
                                            type="hidden"
                                            name="gif_name"
                                            value="<?= e(
                                                $gif
                                            ) ?>"
                                        >


                                        <button
                                            type="submit"
                                            name="send_saved_gif"
                                            class="gif-item"
                                            title="<?= e(
                                                basename(
                                                    $gif
                                                )
                                            ) ?>"
                                        >

                                            <img
                                                src="<?= e(
                                                    $gifUrl
                                                ) ?>"
                                                alt="<?= e(
                                                    basename(
                                                        $gif
                                                    )
                                                ) ?>"
                                                loading="lazy"
                                                onerror="
                                                    this.style.display='none';
                                                    this.parentElement.classList.add('gif-error');
                                                "
                                            >

                                            <span class="
                                                gif-hover-label
                                            ">
                                                Send
                                            </span>

                                        </button>


                                    </form>


                                <?php endforeach; ?>


                            </div>


                            <div
                                id="gifNoSearchResults"
                                class="
                                    gif-no-search-results
                                "
                                style="display:none;"
                            >

                                <div>
                                    🔎
                                </div>

                                <strong>
                                    No matching media
                                </strong>

                                <small>
                                    Try another search.
                                </small>

                            </div>


                        <?php else: ?>


                            <div class="no-gifs">

                                <div>
                                    🎬
                                </div>

                                <h3>
                                    Your GIF Library is Empty
                                </h3>

                                <p>
                                    Add GIFs or images here:
                                </p>

                                <code>
                                    uploads/gifs/
                                </code>

                                <small>
                                    New files added later will
                                    appear automatically.
                                </small>

                            </div>


                        <?php endif; ?>


                    </div>


                    <!-- =================================================
                         COMPOSER
                    ================================================== -->

                    <form
                        method="POST"
                        class="message-form"
                        id="messageForm"
                        autocomplete="off"
                    >


                        <input
                            type="hidden"
                            name="other"
                            value="<?= $selected ?>"
                        >


                        <button
                            type="button"
                            class="composer-tool"
                            id="gifButton"
                            title="GIF library"
                        >
                            GIF
                        </button>


                        <div class="message-input-shell">


                            <textarea
                                name="message"
                                id="messageInput"
                                rows="1"
                                maxlength="5000"
                                placeholder="Write a message..."
                                required
                            ></textarea>


                            <div class="
                                input-scan-line
                            "></div>


                        </div>


                        <button
                            type="submit"
                            name="send"
                            class="send-message-button"
                            title="Send"
                        >

                            ➤

                        </button>


                    </form>


                    <div class="composer-footer">


                        <span>
                            🔐 Secure ConnectHub messaging
                        </span>


                        <span>
                            Enter to send · Shift + Enter for new line
                        </span>


                    </div>


                </div>


            <?php else: ?>


                <!-- =================================================
                     NO CHAT SELECTED
                ================================================== -->

                <div class="no-chat-selected">


                    <div class="no-chat-visual">


                        <div class="
                            no-chat-main-orb
                        ">
                            💬
                        </div>


                        <div class="
                            no-chat-ring
                            ring-one
                        "></div>


                        <div class="
                            no-chat-ring
                            ring-two
                        "></div>


                    </div>


                    <div
                        class="
                            no-chat-label
                        "
                    >
                        CONNECTHUB MESSAGES
                    </div>


                    <h2>
                        Choose someone to start a conversation
                    </h2>


                    <p>
                        Your conversations, shared posts and
                        media will appear here.
                    </p>


                    <div class="no-chat-features">


                        <div>
                            <span>
                                ⚡
                            </span>
                            Fast
                        </div>


                        <div>
                            <span>
                                🔐
                            </span>
                            Secure
                        </div>


                        <div>
                            <span>
                                🎬
                            </span>
                            Media
                        </div>


                        <div>
                            <span>
                                🟢
                            </span>
                            Live Status
                        </div>


                    </div>


                </div>


            <?php endif; ?>


        </main>


    </section>


</div>


<style>

/* ============================================================
   PAGE
============================================================ */

.advanced-chat-page {

    width: 100%;

    max-width: 1260px;

    margin: 0 auto;

    padding:
        20px
        16px
        70px;

}


/* ============================================================
   HERO
============================================================ */

.chat-page-header {

    position: relative;

    min-height: 110px;

    display: flex;

    align-items: center;

    gap: 15px;

    margin-bottom: 14px;

    padding:
        18px
        22px;

    overflow: hidden;

    border:
        1px solid
        rgba(96,165,250,.18);

    border-radius: 22px;

    background:
        linear-gradient(
            135deg,
            rgba(2,6,23,.96),
            rgba(15,23,42,.93),
            rgba(30,41,99,.92)
        );

    box-shadow:
        0 20px 60px
        rgba(0,0,0,.30);

}


.chat-hero-glow {

    position: absolute;

    border-radius: 50%;

    pointer-events: none;

    filter: blur(12px);

}


.glow-a {

    width: 280px;

    height: 280px;

    right: -100px;

    top: -135px;

    background:
        rgba(37,99,235,.20);

    animation:
        heroGlowA
        8s
        ease-in-out
        infinite
        alternate;

}


.glow-b {

    width: 210px;

    height: 210px;

    left: 35%;

    bottom: -145px;

    background:
        rgba(124,58,237,.17);

    animation:
        heroGlowB
        10s
        ease-in-out
        infinite
        alternate;

}


.glow-c {

    width: 120px;

    height: 120px;

    left: 10%;

    top: 25%;

    background:
        rgba(34,211,238,.09);

    animation:
        heroGlowC
        6s
        ease-in-out
        infinite
        alternate;

}


@keyframes heroGlowA {

    to {
        transform:
            translate(-80px,90px)
            scale(1.2);
    }

}


@keyframes heroGlowB {

    to {
        transform:
            translate(90px,-70px)
            scale(1.16);
    }

}


@keyframes heroGlowC {

    to {
        transform:
            translate(40px,-20px)
            scale(1.3);
    }

}


/* ============================================================
   HERO ICON
============================================================ */

.chat-hero-icon {

    position: relative;

    z-index: 2;

    width: 61px;

    height: 61px;

    flex: 0 0 61px;

    display: grid;

    place-items: center;

    border:
        1px solid
        rgba(147,197,253,.27);

    border-radius: 17px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5,
            #7c3aed
        );

    font-size: 28px;

    box-shadow:
        0 0 30px
        rgba(37,99,235,.30);

    animation:
        chatHeroPulse
        3s
        ease-in-out
        infinite;

}


@keyframes chatHeroPulse {

    50% {

        transform:
            translateY(-3px)
            scale(1.04);

        box-shadow:
            0 0 44px
            rgba(59,130,246,.46);

    }

}


/* ============================================================
   HERO TEXT
============================================================ */

.chat-hero-text {

    position: relative;

    z-index: 2;

    min-width: 0;

}


.chat-eyebrow {

    color:
        #60a5fa;

    font-size: 7px;

    font-weight: 950;

    letter-spacing: 2px;

}


.chat-hero-text h1 {

    margin:
        4px 0;

    color: white;

    font-size: 28px;

}


.chat-hero-text p {

    margin: 0;

    color:
        #94a3b8;

    font-size: 10px;

}


.chat-live-badge {

    position: relative;

    z-index: 2;

    margin-left: auto;

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding:
        8px
        11px;

    border:
        1px solid
        rgba(34,197,94,.18);

    border-radius: 999px;

    color:
        #bbf7d0;

    background:
        rgba(22,101,52,.12);

    font-size: 7px;

    font-weight: 950;

}


.chat-live-badge span {

    width: 6px;

    height: 6px;

    border-radius: 50%;

    background: #22c55e;

    box-shadow:
        0 0 12px
        rgba(34,197,94,1);

    animation:
        liveBlink
        1.5s
        ease-in-out
        infinite;

}


@keyframes liveBlink {

    50% {
        transform: scale(1.5);
        opacity: .55;
    }

}


/* ============================================================
   CHAT CONTAINER
============================================================ */

.chat-container {

    display: grid;

    grid-template-columns:
        330px
        minmax(0,1fr);

    min-height: 690px;

    overflow: hidden;

    border:
        1px solid
        rgba(96,165,250,.12);

    border-radius: 24px;

    background:
        rgba(2,6,23,.56);

    box-shadow:
        0 25px 80px
        rgba(0,0,0,.28);

    backdrop-filter:
        blur(14px);

    -webkit-backdrop-filter:
        blur(14px);

}


/* ============================================================
   PEOPLE PANEL
============================================================ */

.people-panel {

    min-width: 0;

    display: flex;

    flex-direction: column;

    overflow: hidden;

    border-right:
        1px solid
        rgba(148,163,184,.08);

    background:
        linear-gradient(
            180deg,
            rgba(3,10,26,.98),
            rgba(7,15,37,.95)
        );

}


.people-panel-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 10px;

    padding:
        18px
        16px
        13px;

}


.people-panel-header span {

    display: block;

    color:
        #60a5fa;

    font-size: 7px;

    font-weight: 950;

    letter-spacing: 1.6px;

}


.people-panel-header h2 {

    margin:
        4px
        0
        0;

    color:
        #f8fafc;

    font-size: 19px;

}


.people-count {

    width: 34px;

    height: 34px;

    display: grid;

    place-items: center;

    border:
        1px solid
        rgba(96,165,250,.15);

    border-radius: 10px;

    color:
        #bfdbfe;

    background:
        rgba(37,99,235,.12);

    font-size: 10px;

    font-weight: 950;

}


/* ============================================================
   SEARCH
============================================================ */

.conversation-search {

    margin:
        0
        13px
        11px;

    display: flex;

    align-items: center;

    gap: 7px;

    padding:
        0
        11px;

    border:
        1px solid
        rgba(148,163,184,.10);

    border-radius: 12px;

    background:
        rgba(15,23,42,.68);

}


.conversation-search > span {

    color:
        #64748b;

    font-size: 16px;

}


.conversation-search input {

    width: 100%;

    height: 40px;

    padding: 0;

    border: none !important;

    outline: none !important;

    background: transparent !important;

    box-shadow: none !important;

    color:
        #e2e8f0;

    font-size: 9px;

}


.conversation-search input::placeholder {

    color:
        #64748b;

}


/* ============================================================
   PEOPLE LIST
============================================================ */

.people-list {

    flex: 1;

    overflow-y: auto;

    padding:
        0
        8px
        12px;

}


.people-list::-webkit-scrollbar,
.gif-popup::-webkit-scrollbar,
.message-area::-webkit-scrollbar {

    width: 6px;

}


.people-list::-webkit-scrollbar-thumb,
.gif-popup::-webkit-scrollbar-thumb,
.message-area::-webkit-scrollbar-thumb {

    border-radius: 999px;

    background:
        linear-gradient(
            180deg,
            #2563eb,
            #6366f1
        );

}


/* ============================================================
   CONVERSATION
============================================================ */

.conversation-item {

    position: relative;

    display: flex;

    align-items: center;

    gap: 10px;

    width: 100%;

    min-height: 73px;

    margin: 4px 0;

    padding: 9px;

    overflow: hidden;

    border:
        1px solid
        transparent;

    border-radius: 14px;

    text-decoration: none;

    color: inherit;

    transition:
        .22s
        ease;

}


.conversation-item:hover {

    transform:
        translateX(3px);

    background:
        rgba(37,99,235,.08);

    border-color:
        rgba(96,165,250,.12);

}


.conversation-active {

    background:
        linear-gradient(
            90deg,
            rgba(37,99,235,.18),
            rgba(79,70,229,.08)
        );

    border-color:
        rgba(96,165,250,.19);

    box-shadow:
        inset 3px 0 0
        #3b82f6;

}


.conversation-active::after {

    content: "";

    position: absolute;

    width: 80px;

    height: 80px;

    right: -35px;

    top: -35px;

    border-radius: 50%;

    background:
        rgba(59,130,246,.11);

    filter: blur(10px);

}


/* ============================================================
   AVATAR
============================================================ */

.conversation-avatar {

    position: relative;

    width: 46px;

    height: 46px;

    flex: 0 0 46px;

}


.conversation-avatar img,
.conversation-avatar-fallback {

    width: 46px;

    height: 46px;

    border-radius: 14px;

}


.conversation-avatar img {

    display: block;

    object-fit: cover;

    border:
        2px solid
        rgba(96,165,250,.20);

}


.conversation-avatar-fallback {

    display: grid;

    place-items: center;

    color: white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    font-size: 14px;

    font-weight: 950;

}


.conversation-status {

    position: absolute;

    right: -2px;

    bottom: -2px;

    width: 13px;

    height: 13px;

    border:
        3px solid
        #061024;

    border-radius: 50%;

}


.status-online {

    background: #22c55e;

    box-shadow:
        0 0 12px
        rgba(34,197,94,.9);

    animation:
        statusPulse
        1.7s
        ease-in-out
        infinite;

}


.status-offline {

    background:
        #475569;

}


@keyframes statusPulse {

    50% {

        transform: scale(1.18);

        box-shadow:
            0 0 19px
            rgba(34,197,94,1);

    }

}


/* ============================================================
   DETAILS
============================================================ */

.conversation-details {

    min-width: 0;

    flex: 1;

}


.conversation-name-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 7px;

}


.conversation-name-row strong {

    overflow: hidden;

    color:
        #e2e8f0;

    font-size: 10px;

    white-space: nowrap;

    text-overflow: ellipsis;

}


.conversation-name-row time {

    color:
        #475569;

    font-size: 6px;

}


.conversation-preview {

    margin-top: 4px;

    overflow: hidden;

    color:
        #64748b;

    font-size: 7px;

    white-space: nowrap;

    text-overflow: ellipsis;

}


.conversation-status-text {

    margin-top: 4px;

    color:
        #475569;

    font-size: 6px;

}


.text-online {

    color:
        #4ade80;

}


.conversation-arrow {

    color:
        #334155;

    font-size: 18px;

    transition:
        .2s
        ease;

}


.conversation-item:hover
.conversation-arrow {

    color:
        #60a5fa;

    transform:
        translateX(3px);

}


/* ============================================================
   NO CONVERSATION
============================================================ */

.no-conversations {

    padding:
        50px
        18px;

    text-align: center;

}


.no-conversation-icon {

    font-size: 45px;

}


.no-conversations h3 {

    margin:
        11px 0 6px;

    color:
        #e2e8f0;

    font-size: 15px;

}


.no-conversations p {

    max-width: 230px;

    margin:
        0
        auto
        17px;

    color:
        #64748b;

    font-size: 8px;

    line-height: 1.7;

}


.discover-button {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding:
        9px
        13px;

    border-radius: 10px;

    color: white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    text-decoration: none;

    font-size: 8px;

    font-weight: 900;

}


/* ============================================================
   CHAT WINDOW
============================================================ */

.chat-window {

    position: relative;

    min-width: 0;

    min-height: 690px;

    display: flex;

    flex-direction: column;

    overflow: hidden;

    background:
        radial-gradient(
            circle at 82% 12%,
            rgba(37,99,235,.08),
            transparent 24%
        ),
        radial-gradient(
            circle at 20% 82%,
            rgba(124,58,237,.06),
            transparent 24%
        ),
        rgba(4,10,25,.82);

}


/* ============================================================
   CHAT HEADER
============================================================ */

.chat-window-header {

    flex: 0 0 76px;

    min-height: 76px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 10px;

    padding:
        12px
        17px;

    border-bottom:
        1px solid
        rgba(148,163,184,.08);

    background:
        rgba(2,6,23,.64);

    backdrop-filter:
        blur(13px);

}


.selected-person {

    display: flex;

    align-items: center;

    gap: 10px;

    min-width: 0;

}


.selected-avatar {

    position: relative;

    width: 48px;

    height: 48px;

    flex: 0 0 48px;

}


.selected-avatar img,
.selected-avatar-fallback {

    width: 48px;

    height: 48px;

    border-radius: 14px;

    border:
        2px solid
        rgba(96,165,250,.30);

}


.selected-avatar img {

    display: block;

    object-fit: cover;

}


.selected-avatar-fallback {

    display: grid;

    place-items: center;

    color: white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

    font-size: 15px;

    font-weight: 950;

}


.selected-status-dot {

    position: absolute;

    right: -2px;

    bottom: -2px;

    width: 14px;

    height: 14px;

    border:
        3px solid
        #061024;

    border-radius: 50%;

}


.selected-person-info {

    min-width: 0;

}


.selected-name {

    overflow: hidden;

    color:
        #f8fafc;

    font-size: 13px;

    font-weight: 950;

    white-space: nowrap;

    text-overflow: ellipsis;

}


.selected-status-text {

    margin-top: 4px;

    color:
        #64748b;

    font-size: 7px;

    font-weight: 850;

}


.online-text {

    color:
        #4ade80;

}


.chat-header-actions {

    display: flex;

    align-items: center;

    gap: 7px;

}


.secure-chat-indicator {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    padding:
        7px
        9px;

    border:
        1px solid
        rgba(96,165,250,.10);

    border-radius: 999px;

    color:
        #94a3b8;

    background:
        rgba(15,23,42,.50);

    font-size: 7px;

    font-weight: 900;

}


.header-icon-button {

    width: 33px;

    height: 33px;

    display: grid;

    place-items: center;

    border:
        1px solid
        rgba(148,163,184,.10);

    border-radius: 10px;

    color:
        #94a3b8;

    background:
        rgba(15,23,42,.55);

    cursor: pointer;

    font-size: 14px;

}


/* ============================================================
   MESSAGE AREA
============================================================ */

.message-area {

    flex: 1;

    min-height: 0;

    overflow-y: auto;

    padding:
        15px
        18px
        20px;

}


.chat-start-label {

    display: flex;

    justify-content: center;

    margin:
        0 0 15px;

}


.chat-start-label span {

    padding:
        5px
        9px;

    border:
        1px solid
        rgba(96,165,250,.08);

    border-radius: 999px;

    color:
        #475569;

    background:
        rgba(15,23,42,.48);

    font-size: 6px;

    font-weight: 900;

    letter-spacing: 1px;

}


/* ============================================================
   DATE
============================================================ */

.message-date-separator {

    display: flex;

    justify-content: center;

    margin:
        13px 0
        17px;

}


.message-date-separator span {

    padding:
        5px
        9px;

    border:
        1px solid
        rgba(148,163,184,.08);

    border-radius: 999px;

    color:
        #64748b;

    background:
        rgba(15,23,42,.48);

    font-size: 6px;

    font-weight: 850;

}


/* ============================================================
   MESSAGE ROW
============================================================ */

.message-row {

    display: flex;

    align-items: flex-end;

    gap: 7px;

    margin:
        7px 0;

    animation:
        messageAppear
        .28s
        ease-out;

}


@keyframes messageAppear {

    from {

        opacity: 0;

        transform:
            translateY(8px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}


.message-mine {

    justify-content: flex-end;

}


.message-theirs {

    justify-content: flex-start;

}


/* ============================================================
   MESSAGE AVATAR
============================================================ */

.message-avatar {

    width: 30px;

    height: 30px;

    flex: 0 0 30px;

}


.message-avatar img,
.message-avatar-fallback {

    width: 30px;

    height: 30px;

    border-radius: 10px;

}


.message-avatar img {

    display: block;

    object-fit: cover;

}


.message-avatar-fallback {

    display: grid;

    place-items: center;

    color: white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    font-size: 9px;

    font-weight: 950;

}


/* ============================================================
   MESSAGE CONTENT
============================================================ */

.message-content-wrap {

    position: relative;

    max-width:
        min(
            74%,
            590px
        );

}


.message-sender-name {

    margin:
        0
        0
        4px
        5px;

    color:
        #64748b;

    font-size: 6px;

    font-weight: 900;

}


/* ============================================================
   BUBBLE
============================================================ */

.message-bubble {

    position: relative;

    overflow: hidden;

    padding:
        10px
        12px
        7px;

    border:
        1px solid
        rgba(255,255,255,.07);

    border-radius: 15px;

    border-bottom-left-radius: 5px;

    background:
        linear-gradient(
            135deg,
            rgba(15,23,42,.91),
            rgba(30,41,59,.78)
        );

    box-shadow:
        0 8px 25px
        rgba(0,0,0,.15);

}


.message-mine
.message-bubble {

    border-color:
        rgba(96,165,250,.20);

    border-bottom-left-radius: 15px;

    border-bottom-right-radius: 5px;

    background:
        linear-gradient(
            135deg,
            rgba(37,99,235,.86),
            rgba(79,70,229,.86)
        );

}


/* ============================================================
   LIGHT SCAN
============================================================ */

.message-light-line {

    position: absolute;

    left: -35%;

    top: 0;

    width: 34%;

    height: 1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(147,197,253,.9),
            transparent
        );

    animation:
        messageScan
        4.5s
        linear
        infinite;

}


@keyframes messageScan {

    0% {
        left: -35%;
        opacity: 0;
    }

    25% {
        opacity: 1;
    }

    80% {
        opacity: 1;
    }

    100% {
        left: 110%;
        opacity: 0;
    }

}


/* ============================================================
   TEXT
============================================================ */

.message-text {

    color:
        #e2e8f0;

    font-size: 10px;

    line-height: 1.65;

    word-break: break-word;

}


.message-mine
.message-text {

    color: white;

}


/* ============================================================
   META
============================================================ */

.message-meta {

    display: flex;

    justify-content: flex-end;

    align-items: center;

    gap: 6px;

    margin-top: 5px;

}


.message-meta span {

    color:
        #64748b;

    font-size: 6px;

}


.message-mine
.message-meta span {

    color:
        rgba(255,255,255,.60);

}


.sent-status {

    font-weight: 850;

}


/* ============================================================
   COPY
============================================================ */

.message-copy-button {

    position: absolute;

    right: -31px;

    top: 50%;

    width: 25px;

    height: 25px;

    display: grid;

    place-items: center;

    opacity: 0;

    transform:
        translateY(-50%);

    border:
        1px solid
        rgba(148,163,184,.10);

    border-radius: 8px;

    color:
        #64748b;

    background:
        rgba(2,6,23,.82);

    cursor: pointer;

    transition: .2s ease;

}


.message-content-wrap:hover
.message-copy-button {

    opacity: 1;

}


.message-copy-button:hover {

    color:
        #bfdbfe;

}


/* ============================================================
   MEDIA
============================================================ */

.message-media {

    position: relative;

    overflow: hidden;

    min-width: 70px;

    min-height: 70px;

    border-radius: 11px;

    background:
        rgba(2,6,23,.25);

}


.message-gif {

    display: block;

    width: auto;

    max-width: 290px;

    max-height: 290px;

    object-fit: contain;

}


.media-fallback {

    display: none;

    align-items: center;

    justify-content: center;

    min-height: 90px;

    color:
        #94a3b8;

    font-size: 28px;

}


.message-media.media-broken
.media-fallback {

    display: flex;

}


.unavailable-media {

    padding:
        20px;

    border:
        1px dashed
        rgba(148,163,184,.12);

    border-radius: 11px;

    color:
        #64748b;

    font-size: 8px;

    text-align: center;

}


/* ============================================================
   SHARED POST
============================================================ */

.shared-post-card {

    width:
        min(
            340px,
            100%
        );

    overflow: hidden;

    border:
        1px solid
        rgba(96,165,250,.12);

    border-radius: 13px;

    background:
        rgba(2,6,23,.58);

}


.shared-post-header {

    display: flex;

    align-items: center;

    gap: 8px;

    padding: 9px;

    border-bottom:
        1px solid
        rgba(148,163,184,.07);

}


.shared-post-avatar {

    width: 29px;

    height: 29px;

    overflow: hidden;

    display: grid;

    place-items: center;

    border-radius: 9px;

    color: white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    font-size: 8px;

    font-weight: 950;

}


.shared-post-avatar img {

    width: 100%;

    height: 100%;

    display: block;

    object-fit: cover;

}


.shared-post-header strong {

    display: block;

    color:
        #e2e8f0;

    font-size: 8px;

}


.shared-post-header small {

    display: block;

    margin-top: 2px;

    color:
        #64748b;

    font-size: 6px;

}


.shared-post-media {

    width: 100%;

    max-height: 310px;

    display: block;

    object-fit: cover;

    background: #020617;

}


.shared-post-caption {

    padding:
        9px;

    color:
        #cbd5e1;

    font-size: 8px;

    line-height: 1.55;

}


.shared-post-footer {

    padding:
        7px
        9px;

    color:
        #475569;

    font-size: 6px;

    border-top:
        1px solid
        rgba(148,163,184,.06);

}


/* ============================================================
   EMPTY CHAT
============================================================ */

.empty-chat {

    min-height: 470px;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    text-align: center;

    padding: 40px;

}


.empty-orbit {

    position: relative;

    width: 105px;

    height: 105px;

    display: grid;

    place-items: center;

    border:
        1px solid
        rgba(96,165,250,.17);

    border-radius: 50%;

    background:
        rgba(37,99,235,.07);

    box-shadow:
        0 0 40px
        rgba(37,99,235,.12);

    animation:
        emptyFloat
        3s
        ease-in-out
        infinite;

}


.empty-orbit::before,
.empty-orbit::after {

    content: "";

    position: absolute;

    inset: -11px;

    border:
        1px solid
        rgba(96,165,250,.09);

    border-radius: 50%;

    animation:
        orbit
        3s
        linear
        infinite;

}


.empty-orbit::after {

    inset: -22px;

    border-color:
        rgba(124,58,237,.07);

    animation-duration: 5s;

}


.empty-orbit > div {

    font-size: 40px;

}


@keyframes emptyFloat {

    50% {
        transform:
            translateY(-7px);
    }

}


@keyframes orbit {

    to {
        transform:
            rotate(360deg);
    }

}


.empty-chat-label {

    margin-top: 25px;

    color:
        #60a5fa;

    font-size: 7px;

    font-weight: 950;

    letter-spacing: 1.8px;

}


.empty-chat h2 {

    margin:
        7px
        0
        7px;

    color:
        #f8fafc;

    font-size: 21px;

}


.empty-chat p {

    max-width:
        420px;

    margin: 0;

    color:
        #64748b;

    font-size: 9px;

    line-height: 1.7;

}


.empty-pulse-row {

    display: flex;

    gap: 7px;

    margin-top: 18px;

}


.empty-pulse-row span {

    width: 6px;

    height: 6px;

    border-radius: 50%;

    background: #3b82f6;

    box-shadow:
        0 0 12px
        rgba(59,130,246,.7);

    animation:
        emptyPulse
        1.5s
        ease-in-out
        infinite;

}


.empty-pulse-row span:nth-child(2) {

    animation-delay: .2s;

}


.empty-pulse-row span:nth-child(3) {

    animation-delay: .4s;

}


@keyframes emptyPulse {

    50% {
        transform:
            translateY(-5px);
        opacity: .55;
    }

}


/* ============================================================
   COMPOSER
============================================================ */

.message-composer {

    position: relative;

    flex: 0 0 auto;

    padding:
        10px
        12px
        11px;

    border-top:
        1px solid
        rgba(148,163,184,.08);

    background:
        rgba(2,6,23,.80);

    backdrop-filter:
        blur(15px);

}


.message-form {

    display: flex;

    align-items: flex-end;

    gap: 7px;

}


.composer-tool {

    width: 42px;

    height: 42px;

    flex: 0 0 42px;

    border:
        1px solid
        rgba(96,165,250,.12);

    border-radius: 12px;

    color:
        #bfdbfe;

    background:
        rgba(37,99,235,.10);

    font-size: 8px;

    font-weight: 950;

    cursor: pointer;

}


.composer-tool:hover {

    background:
        rgba(37,99,235,.18);

    transform:
        translateY(-1px);

}


/* ============================================================
   INPUT
============================================================ */

.message-input-shell {

    position: relative;

    flex: 1;

    overflow: hidden;

    border:
        1px solid
        rgba(148,163,184,.11);

    border-radius: 14px;

    background:
        rgba(15,23,42,.72);

}


.message-input-shell textarea {

    position: relative;

    z-index: 2;

    width: 100%;

    min-height: 42px;

    max-height: 140px;

    margin: 0;

    padding:
        12px
        13px;

    resize: none;

    border: none !important;

    outline: none !important;

    box-shadow: none !important;

    color:
        #e2e8f0;

    background:
        transparent !important;

    font-family: inherit;

    font-size: 9px;

    line-height: 1.5;

}


.message-input-shell textarea::placeholder {

    color:
        #64748b;

}


.input-scan-line {

    position: absolute;

    bottom: 0;

    left: -30%;

    width: 30%;

    height: 1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            #3b82f6,
            transparent
        );

    animation:
        inputScan
        4s
        linear
        infinite;

}


@keyframes inputScan {

    from {
        left: -30%;
    }

    to {
        left: 130%;
    }

}


/* ============================================================
   SEND
============================================================ */

.send-message-button {

    width: 42px;

    height: 42px;

    flex: 0 0 42px;

    display: grid;

    place-items: center;

    border:
        1px solid
        rgba(147,197,253,.20);

    border-radius: 12px;

    color: white;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    box-shadow:
        0 8px 23px
        rgba(37,99,235,.23);

    cursor: pointer;

    font-size: 16px;

}


.send-message-button:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 12px 30px
        rgba(37,99,235,.34);

}


/* ============================================================
   COMPOSER FOOTER
============================================================ */

.composer-footer {

    display: flex;

    justify-content: space-between;

    gap: 10px;

    margin-top: 6px;

    padding:
        0
        48px;

    color:
        #475569;

    font-size: 6px;

}


/* ============================================================
   GIF POPUP
============================================================ */

.gif-popup {

    position: absolute;

    left: 12px;

    bottom: 92px;

    z-index: 1000;

    display: none;

    width: 400px;

    max-width:
        calc(100% - 24px);

    max-height: 430px;

    overflow-y: auto;

    padding: 12px;

    border:
        1px solid
        rgba(96,165,250,.18);

    border-radius: 18px;

    background:
        rgba(2,6,23,.97);

    box-shadow:
        0 25px 80px
        rgba(0,0,0,.52);

    backdrop-filter:
        blur(18px);

}


.gif-popup.open {

    display: block;

    animation:
        gifOpen
        .22s
        ease-out;

}


@keyframes gifOpen {

    from {

        opacity: 0;

        transform:
            translateY(10px)
            scale(.98);

    }

    to {

        opacity: 1;

        transform:
            translateY(0)
            scale(1);

    }

}


/* ============================================================
   GIF HEADER
============================================================ */

.gif-popup-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 10px;

    margin-bottom: 10px;

}


.gif-popup-header span {

    display: block;

    color:
        #60a5fa;

    font-size: 6px;

    font-weight: 950;

    letter-spacing: 1.4px;

}


.gif-popup-header strong {

    display: block;

    margin-top: 3px;

    color:
        #e2e8f0;

    font-size: 11px;

}


.gif-close {

    width: 31px;

    height: 31px;

    border:
        1px solid
        rgba(148,163,184,.10);

    border-radius: 9px;

    color:
        #94a3b8;

    background:
        rgba(15,23,42,.62);

    cursor: pointer;

    font-size: 19px;

}


/* ============================================================
   GIF SEARCH
============================================================ */

.gif-search-box {

    display: flex;

    align-items: center;

    gap: 7px;

    margin-bottom: 6px;

    padding:
        0
        10px;

    border:
        1px solid
        rgba(148,163,184,.10);

    border-radius: 10px;

    background:
        rgba(15,23,42,.72);

}


.gif-search-box > span {

    color:
        #64748b;

    font-size: 14px;

}


.gif-search-box input {

    width: 100%;

    height: 34px;

    border: none !important;

    outline: none !important;

    background: transparent !important;

    color:
        #e2e8f0;

    box-shadow: none !important;

    font-size: 8px;

}


.gif-search-box input::placeholder {

    color:
        #64748b;

}


.gif-results-count {

    margin:
        0
        0
        8px;

    color:
        #475569;

    font-size: 6px;

}


/* ============================================================
   GIF GRID
============================================================ */

.gif-grid {

    display: grid;

    grid-template-columns:
        repeat(
            3,
            1fr
        );

    gap: 7px;

}


.gif-item-form {

    margin: 0;

}


.gif-item {

    position: relative;

    width: 100%;

    height: 92px;

    padding: 3px;

    overflow: hidden;

    border:
        1px solid
        rgba(148,163,184,.08);

    border-radius: 10px;

    background:
        rgba(15,23,42,.80);

    cursor: pointer;

}


.gif-item img {

    width: 100%;

    height: 100%;

    display: block;

    object-fit: cover;

    border-radius: 7px;

}


.gif-hover-label {

    position: absolute;

    left: 4px;

    right: 4px;

    bottom: 4px;

    padding: 4px;

    text-align: center;

    border-radius: 6px;

    color:
        white;

    background:
        rgba(2,6,23,.72);

    font-size: 6px;

    font-weight: 900;

    opacity: 0;

    transition:
        .2s
        ease;

}


.gif-item:hover {

    border-color:
        rgba(96,165,250,.32);

    transform:
        translateY(-2px);

}


.gif-item:hover
.gif-hover-label {

    opacity: 1;

}


.gif-error {

    display: grid;

    place-items: center;

}


/* ============================================================
   NO RESULTS
============================================================ */

.gif-no-search-results {

    padding:
        35px
        10px;

    text-align: center;

}


.gif-no-search-results div {

    font-size: 35px;

}


.gif-no-search-results strong {

    display: block;

    margin-top: 7px;

    color:
        #e2e8f0;

    font-size: 10px;

}


.gif-no-search-results small {

    display: block;

    margin-top: 3px;

    color:
        #64748b;

    font-size: 7px;

}


/* ============================================================
   EMPTY GIF LIBRARY
============================================================ */

.no-gifs {

    padding:
        40px
        10px;

    text-align: center;

}


.no-gifs > div {

    font-size: 43px;

}


.no-gifs h3 {

    margin:
        9px
        0
        6px;

    color:
        #e2e8f0;

    font-size: 13px;

}


.no-gifs p {

    margin:
        0
        0
        7px;

    color:
        #64748b;

    font-size: 8px;

}


.no-gifs code {

    display: inline-block;

    padding:
        7px
        9px;

    border-radius: 8px;

    color:
        #93c5fd;

    background:
        rgba(37,99,235,.10);

    font-size: 7px;

}


.no-gifs small {

    display: block;

    margin-top: 9px;

    color:
        #475569;

    font-size: 7px;

}


/* ============================================================
   NO CHAT
============================================================ */

.no-chat-selected {

    min-height: 690px;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    text-align: center;

    padding: 30px;

}


.no-chat-visual {

    position: relative;

    width: 120px;

    height: 120px;

    margin-bottom: 27px;

}


.no-chat-main-orb {

    position: absolute;

    inset: 15px;

    z-index: 2;

    display: grid;

    place-items: center;

    border:
        1px solid
        rgba(96,165,250,.22);

    border-radius: 31px;

    color: white;

    background:
        linear-gradient(
            135deg,
            rgba(37,99,235,.18),
            rgba(124,58,237,.13)
        );

    font-size: 46px;

    box-shadow:
        0 0 45px
        rgba(37,99,235,.15);

    animation:
        noChatOrb
        3s
        ease-in-out
        infinite;

}


@keyframes noChatOrb {

    50% {

        transform:
            translateY(-7px)
            rotate(2deg);

    }

}


.no-chat-ring {

    position: absolute;

    border:
        1px solid
        rgba(96,165,250,.10);

    border-radius: 50%;

}


.ring-one {

    inset: 2px;

    animation:
        orbitRing
        4s
        linear
        infinite;

}


.ring-two {

    inset: -11px;

    border-color:
        rgba(124,58,237,.08);

    animation:
        orbitRing
        6s
        linear
        infinite
        reverse;

}


@keyframes orbitRing {

    to {
        transform:
            rotate(360deg);
    }

}


.no-chat-label {

    color:
        #60a5fa;

    font-size: 7px;

    font-weight: 950;

    letter-spacing: 2px;

}


.no-chat-selected h2 {

    margin:
        7px
        0
        7px;

    color:
        #f8fafc;

    font-size: 24px;

}


.no-chat-selected > p {

    max-width:
        450px;

    margin: 0;

    color:
        #64748b;

    font-size: 9px;

    line-height: 1.7;

}


.no-chat-features {

    display:
        flex;

    flex-wrap: wrap;

    justify-content: center;

    gap: 7px;

    margin-top: 17px;

}


.no-chat-features > div {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    padding:
        7px
        10px;

    border:
        1px solid
        rgba(148,163,184,.08);

    border-radius: 9px;

    color:
        #64748b;

    background:
        rgba(15,23,42,.48);

    font-size: 7px;

    font-weight: 800;

}


.no-chat-features span {

    font-size: 11px;

}


/* ============================================================
   MOBILE
============================================================ */

@media (max-width: 950px) {

    .chat-container {

        grid-template-columns:
            280px
            minmax(0,1fr);

    }

}


@media (max-width: 780px) {

    .advanced-chat-page {

        padding:
            10px
            7px
            50px;

    }


    .chat-page-header {

        min-height: 90px;

        padding:
            14px;

    }


    .chat-hero-icon {

        width: 50px;

        height: 50px;

        flex-basis: 50px;

        border-radius: 14px;

        font-size: 23px;

    }


    .chat-hero-text h1 {

        font-size: 23px;

    }


    .chat-live-badge {

        display: none;

    }


    .chat-container {

        grid-template-columns: 1fr;

        min-height: auto;

    }


    .people-panel {

        max-height: 270px;

        border-right: none;

        border-bottom:
            1px solid
            rgba(148,163,184,.08);

    }


    .people-list {

        max-height: 155px;

    }


    .chat-window {

        min-height: 650px;

    }


    .no-chat-selected {

        min-height: 550px;

    }

}


@media (max-width: 550px) {

    .chat-container {

        border-radius: 18px;

    }


    .people-panel {

        max-height: 235px;

    }


    .people-list {

        max-height: 125px;

    }


    .conversation-item {

        min-height: 64px;

    }


    .conversation-avatar,
    .conversation-avatar img,
    .conversation-avatar-fallback {

        width: 41px;

        height: 41px;

    }


    .conversation-avatar {

        flex-basis: 41px;

    }


    .selected-avatar,
    .selected-avatar img,
    .selected-avatar-fallback {

        width: 43px;

        height: 43px;

    }


    .selected-avatar {

        flex-basis: 43px;

    }


    .secure-chat-indicator {

        display: none;

    }


    .message-area {

        padding:
            12px
            10px
            18px;

    }


    .message-content-wrap {

        max-width:
            84%;

    }


    .message-copy-button {

        display: none;

    }


    .message-gif {

        max-width: 230px;

        max-height: 230px;

    }


    .shared-post-card {

        width:
            min(
                290px,
                100%
            );

    }


    .composer-footer {

        padding:
            0
            46px;

    }


    .gif-grid {

        grid-template-columns:
            repeat(
                2,
                1fr
            );

    }


    .gif-popup {

        left: 6px;

        bottom: 82px;

        width:
            calc(
                100% - 12px
            );

    }


    .no-chat-selected h2 {

        font-size: 20px;

    }

}

</style>


<script>

/* ============================================================
   CONNECTHUB CHAT JAVASCRIPT
============================================================ */

(function () {

    "use strict";


    /* ========================================================
       ELEMENTS
    ======================================================== */

    const messageArea =
        document.getElementById(
            "messageArea"
        );


    const messageInput =
        document.getElementById(
            "messageInput"
        );


    const messageForm =
        document.getElementById(
            "messageForm"
        );


    const gifButton =
        document.getElementById(
            "gifButton"
        );


    const gifPopup =
        document.getElementById(
            "gifPopup"
        );


    const closeGif =
        document.getElementById(
            "closeGif"
        );


    const conversationSearch =
        document.getElementById(
            "conversationSearch"
        );


    const focusConversationSearch =
        document.getElementById(
            "focusConversationSearch"
        );


    const gifSearch =
        document.getElementById(
            "gifSearch"
        );


    const selectedUserId =
        <?= (int)$selected ?>;


    /* ========================================================
       SCROLL MESSAGE AREA
    ======================================================== */

    function scrollMessages(
        smooth
    ) {

        if (!messageArea) {
            return;
        }


        if (smooth) {

            messageArea.scrollTo({

                top:
                    messageArea.scrollHeight,

                behavior:
                    "smooth"

            });

        } else {

            messageArea.scrollTop =
                messageArea.scrollHeight;

        }

    }


    if (messageArea) {

        setTimeout(
            function () {

                scrollMessages(
                    false
                );

            },
            50
        );

    }


    /* ========================================================
       AUTO RESIZE MESSAGE INPUT
    ======================================================== */

    function resizeTextarea() {

        if (!messageInput) {
            return;
        }


        messageInput.style.height =
            "auto";


        messageInput.style.height =
            Math.min(
                messageInput.scrollHeight,
                140
            ) +
            "px";

    }


    if (messageInput) {

        messageInput.addEventListener(
            "input",
            resizeTextarea
        );


        resizeTextarea();


        /* ====================================================
           ENTER TO SEND
        ==================================================== */

        messageInput.addEventListener(
            "keydown",
            function (event) {

                if (
                    event.key ===
                    "Enter" &&
                    !event.shiftKey
                ) {

                    event.preventDefault();


                    if (
                        messageInput.value.trim()
                        !==
                        ""
                    ) {

                        if (
                            messageForm
                        ) {

                            messageForm.requestSubmit();

                        }

                    }

                }

            }
        );

    }


    /* ========================================================
       GIF POPUP
    ======================================================== */

    if (
        gifButton &&
        gifPopup
    ) {

        gifButton.addEventListener(
            "click",
            function (event) {

                event.stopPropagation();


                gifPopup.classList.toggle(
                    "open"
                );


                if (
                    gifPopup.classList.contains(
                        "open"
                    ) &&
                    gifSearch
                ) {

                    setTimeout(
                        function () {

                            gifSearch.focus();

                        },
                        80
                    );

                }

            }
        );

    }


    if (
        closeGif
    ) {

        closeGif.addEventListener(
            "click",
            function () {

                if (
                    gifPopup
                ) {

                    gifPopup.classList.remove(
                        "open"
                    );

                }

            }
        );

    }


    document.addEventListener(
        "click",
        function (event) {

            if (
                gifPopup &&
                gifButton &&
                !gifPopup.contains(
                    event.target
                ) &&
                !gifButton.contains(
                    event.target
                )
            ) {

                gifPopup.classList.remove(
                    "open"
                );

            }

        }
    );


    /* ========================================================
       GIF SEARCH
    ======================================================== */

    if (
        gifSearch
    ) {

        const gifItems =
            document.querySelectorAll(
                ".gif-item-form"
            );


        const resultsCounter =
            document.getElementById(
                "gifResultsCount"
            );


        const noResults =
            document.getElementById(
                "gifNoSearchResults"
            );


        function searchGifs() {

            const query =
                gifSearch.value
                    .trim()
                    .toLowerCase();


            let visible =
                0;


            gifItems.forEach(
                function (item) {

                    const filename =
                        (
                            item.getAttribute(
                                "data-gif-name"
                            ) ||
                            ""
                        )
                        .toLowerCase();


                    if (
                        query === "" ||
                        filename.includes(
                            query
                        )
                    ) {

                        item.style.display =
                            "";

                        visible++;

                    } else {

                        item.style.display =
                            "none";

                    }

                }
            );


            if (
                resultsCounter
            ) {

                resultsCounter.textContent =
                    visible +
                    " media files";

            }


            if (
                noResults
            ) {

                noResults.style.display =
                    (
                        visible === 0
                    )
                        ? "block"
                        : "none";

            }

        }


        gifSearch.addEventListener(
            "input",
            searchGifs
        );

    }


    /* ========================================================
       CONVERSATION SEARCH
    ======================================================== */

    if (
        conversationSearch
    ) {

        const conversations =
            document.querySelectorAll(
                ".conversation-item"
            );


        conversationSearch.addEventListener(
            "input",
            function () {

                const query =
                    conversationSearch.value
                        .trim()
                        .toLowerCase();


                conversations.forEach(
                    function (item) {

                        const name =
                            (
                                item.getAttribute(
                                    "data-name"
                                ) ||
                                ""
                            )
                            .toLowerCase();


                        item.style.display =
                            (
                                query ===
                                "" ||
                                name.includes(
                                    query
                                )
                            )
                                ? ""
                                : "none";

                    }
                );

            }
        );

    }


    /* ========================================================
       HEADER SEARCH BUTTON
    ======================================================== */

    if (
        focusConversationSearch
    ) {

        focusConversationSearch.addEventListener(
            "click",
            function () {

                if (
                    conversationSearch
                ) {

                    conversationSearch.focus();

                    conversationSearch.scrollIntoView({
                        behavior:
                            "smooth",
                        block:
                            "center"
                    });

                }

            }
        );

    }


    /* ========================================================
       COPY MESSAGE
    ======================================================== */

    document
        .querySelectorAll(
            ".message-copy-button"
        )
        .forEach(
            function (button) {

                button.addEventListener(
                    "click",
                    async function () {

                        const text =
                            button.getAttribute(
                                "data-copy"
                            ) ||
                            "";


                        if (
                            text === ""
                        ) {

                            return;

                        }


                        try {

                            await navigator
                                .clipboard
                                .writeText(
                                    text
                                );

                        } catch (
                            error
                        ) {

                            const textarea =
                                document.createElement(
                                    "textarea"
                                );


                            textarea.value =
                                text;


                            document.body.appendChild(
                                textarea
                            );


                            textarea.select();


                            document.execCommand(
                                "copy"
                            );


                            textarea.remove();

                        }


                        const oldText =
                            button.textContent;


                        button.textContent =
                            "✓";


                        setTimeout(
                            function () {

                                button.textContent =
                                    oldText;

                            },
                            1000
                        );

                    }
                );

            }
        );


    /* ========================================================
       HEARTBEAT
    ======================================================== */

    function heartbeat() {

        fetch(
            "chat.php?heartbeat=1",
            {
                method:
                    "GET",

                cache:
                    "no-store",

                credentials:
                    "same-origin"
            }
        )
        .catch(
            function () {}
        );

    }


    /* ========================================================
       STATUS REFRESH
    ======================================================== */

    function refreshStatuses() {

        const statusElements =
            document.querySelectorAll(
                "[data-status-for]"
            );


        const ids =
            Array.from(
                statusElements
            )
            .map(
                function (
                    element
                ) {

                    return element.getAttribute(
                        "data-status-for"
                    );

                }
            )
            .filter(
                function (
                    value,
                    index,
                    array
                ) {

                    return (
                        value &&
                        array.indexOf(
                            value
                        ) ===
                        index
                    );

                }
            );


        if (
            ids.length ===
            0
        ) {

            return;

        }


        fetch(
            "chat.php?status=1&ids=" +
            encodeURIComponent(
                ids.join(",")
            ),
            {
                method:
                    "GET",

                cache:
                    "no-store",

                credentials:
                    "same-origin"
            }
        )
        .then(
            function (response) {

                if (
                    !response.ok
                ) {

                    throw new Error(
                        "Status request failed"
                    );

                }


                return response.json();

            }
        )
        .then(
            function (data) {

                Object.keys(
                    data
                ).forEach(
                    function (id) {

                        const info =
                            data[id];


                        const online =
                            !!(
                                info &&
                                info.online
                            );


                        /* ------------------------------------
                           LIST DOT
                        ------------------------------------ */

                        document
                            .querySelectorAll(
                                "[data-status-for='" +
                                id +
                                "']"
                            )
                            .forEach(
                                function (
                                    element
                                ) {

                                    if (
                                        element.classList.contains(
                                            "conversation-status"
                                        )
                                    ) {

                                        element.classList.toggle(
                                            "status-online",
                                            online
                                        );


                                        element.classList.toggle(
                                            "status-offline",
                                            !online
                                        );

                                    }


                                    if (
                                        element.classList.contains(
                                            "conversation-status-text"
                                        )
                                    ) {

                                        element.textContent =
                                            online
                                                ? "Active now"
                                                : "Offline";


                                        element.classList.toggle(
                                            "text-online",
                                            online
                                        );

                                    }

                                }
                            );


                        /* ------------------------------------
                           SELECTED USER
                        ------------------------------------ */

                        if (
                            parseInt(
                                id,
                                10
                            ) ===
                            selectedUserId
                        ) {

                            const selectedDot =
                                document.getElementById(
                                    "selectedStatusDot"
                                );


                            const selectedText =
                                document.getElementById(
                                    "selectedStatusText"
                                );


                            if (
                                selectedDot
                            ) {

                                selectedDot.classList.toggle(
                                    "status-online",
                                    online
                                );


                                selectedDot.classList.toggle(
                                    "status-offline",
                                    !online
                                );

                            }


                            if (
                                selectedText
                            ) {

                                selectedText.textContent =
                                    online
                                        ? "Active now"
                                        : "Offline";


                                selectedText.classList.toggle(
                                    "online-text",
                                    online
                                );

                            }

                        }

                    }
                );

            }
        )
        .catch(
            function () {}
        );

    }


    /* ========================================================
       START STATUS SYSTEM
    ======================================================== */

    heartbeat();

    refreshStatuses();


    setInterval(
        function () {

            heartbeat();

            refreshStatuses();

        },
        20000
    );


    /* ========================================================
       REFRESH WHEN TAB BECOMES ACTIVE
    ======================================================== */

    document.addEventListener(
        "visibilitychange",
        function () {

            if (
                document.visibilityState ===
                "visible"
            ) {

                heartbeat();

                refreshStatuses();

            }

        }
    );


    /* ========================================================
       CLOSE GIF ON ESCAPE
    ======================================================== */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key ===
                "Escape"
            ) {

                if (
                    gifPopup
                ) {

                    gifPopup.classList.remove(
                        "open"
                    );

                }

            }

        }
    );


})();

</script>


<?php

require "footer.php";

?>