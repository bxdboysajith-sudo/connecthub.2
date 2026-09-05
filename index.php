<?php

// ============================================================
// CONNECTHUB - PREMIUM HOME PAGE
// COMPLETE REPLACEMENT
// Compatible with current ConnectHub header.php
// ============================================================

require_once "config.php";

login_required();

$uid = (int)($_SESSION["user_id"] ?? 0);

if ($uid <= 0) {
    header("Location: login.php");
    exit;
}


// ============================================================
// HELPERS
// ============================================================

if (!function_exists("homeMediaPath")) {

    function homeMediaPath($value): string
    {
        $value = trim((string)$value);

        if ($value === "") {
            return "";
        }

        // External image URL
        if (preg_match('/^(https?:)?\/\//i', $value)) {
            return $value;
        }

        // Already has uploads/
        if (strpos($value, "uploads/") === 0) {
            return $value;
        }

        // Starts with /uploads/
        if (strpos($value, "/uploads/") === 0) {
            return ltrim($value, "/");
        }

        return "uploads/" . basename($value);
    }

}


if (!function_exists("redirectHome")) {

    function redirectHome(string $hash = ""): void
    {
        header(
            "Location: index.php" . $hash
        );

        exit;
    }

}


// ============================================================
// CURRENT USER
// ============================================================

$currentUserName =
    trim(
        (string)(
            $_SESSION["name"] ?? "User"
        )
    );

if ($currentUserName === "") {
    $currentUserName = "User";
}

$currentUserImage = "";


// ============================================================
// LOAD CURRENT USER
// ============================================================

$userStmt =
    $conn->prepare("
        SELECT
            name,
            profile_image
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

if ($userStmt) {

    $userStmt->bind_param(
        "i",
        $uid
    );

    $userStmt->execute();

    $userRow =
        $userStmt
            ->get_result()
            ->fetch_assoc();

    if ($userRow) {

        if (
            trim(
                (string)(
                    $userRow["name"] ?? ""
                )
            ) !== ""
        ) {

            $currentUserName =
                trim(
                    $userRow["name"]
                );

        }

        $currentUserImage =
            homeMediaPath(
                $userRow["profile_image"] ?? ""
            );

    }

    $userStmt->close();

}


// ============================================================
// ERROR
// ============================================================

$error = "";


// ============================================================
// CREATE POST
// ============================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["create_post"])
) {

    $caption =
        trim(
            (string)(
                $_POST["caption"] ?? ""
            )
        );

    $mediaName = "";

    $mediaType = "image";


    if (
        isset($_FILES["media"]) &&
        $_FILES["media"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        if (
            $_FILES["media"]["error"] !== UPLOAD_ERR_OK
        ) {

            $error =
                "The file upload failed.";

        } else {

            $uploadDir =
                __DIR__ .
                DIRECTORY_SEPARATOR .
                "uploads" .
                DIRECTORY_SEPARATOR;


            if (
                !is_dir($uploadDir)
            ) {

                if (
                    !@mkdir(
                        $uploadDir,
                        0777,
                        true
                    )
                ) {

                    $error =
                        "Could not create the uploads folder.";

                }

            }


            if (
                $error === ""
            ) {

                $originalName =
                    basename(
                        (string)(
                            $_FILES["media"]["name"] ?? ""
                        )
                    );


                $extension =
                    strtolower(
                        pathinfo(
                            $originalName,
                            PATHINFO_EXTENSION
                        )
                    );


                $imageExtensions = [
                    "jpg",
                    "jpeg",
                    "png",
                    "gif",
                    "webp"
                ];


                $videoExtensions = [
                    "mp4",
                    "webm",
                    "mov",
                    "ogg"
                ];


                if (
                    in_array(
                        $extension,
                        $imageExtensions,
                        true
                    )
                ) {

                    $mediaType = "image";

                } elseif (
                    in_array(
                        $extension,
                        $videoExtensions,
                        true
                    )
                ) {

                    $mediaType = "video";

                } else {

                    $error =
                        "Unsupported file type.";

                }


                if (
                    $error === ""
                ) {

                    try {

                        $randomPart =
                            bin2hex(
                                random_bytes(6)
                            );

                    } catch (Throwable $e) {

                        $randomPart =
                            uniqid();

                    }


                    $mediaName =
                        "post_" .
                        $uid .
                        "_" .
                        time() .
                        "_" .
                        $randomPart .
                        "." .
                        $extension;


                    $destination =
                        $uploadDir .
                        $mediaName;


                    if (
                        !move_uploaded_file(
                            $_FILES["media"]["tmp_name"],
                            $destination
                        )
                    ) {

                        $mediaName = "";

                        $error =
                            "Could not save the uploaded file.";

                    }

                }

            }

        }

    }


    if (
        $error === "" &&
        $caption === "" &&
        $mediaName === ""
    ) {

        $error =
            "Write something or select a photo/video.";

    }


    if (
        $error === ""
    ) {

        $postStmt =
            $conn->prepare("
                INSERT INTO posts
                (
                    user_id,
                    image,
                    caption,
                    media_type
                )
                VALUES (?, ?, ?, ?)
            ");


        if ($postStmt) {

            $postStmt->bind_param(
                "isss",
                $uid,
                $mediaName,
                $caption,
                $mediaType
            );


            if (
                $postStmt->execute()
            ) {

                $postStmt->close();

                redirectHome();

            }


            $error =
                "Could not publish your post.";

            $postStmt->close();

        } else {

            $error =
                "Could not prepare the post.";

        }

    }

}


// ============================================================
// FOLLOW USER
// ============================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["follow_user"])
) {

    $followingId =
        (int)(
            $_POST["following_id"] ?? 0
        );


    if (
        $followingId > 0 &&
        $followingId !== $uid
    ) {

        $stmt =
            $conn->prepare("
                INSERT IGNORE INTO follows
                (
                    follower_id,
                    following_id
                )
                VALUES (?, ?)
            ");


        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $uid,
                $followingId
            );

            $stmt->execute();

            $stmt->close();

        }

    }


    redirectHome();

}


// ============================================================
// UNFOLLOW USER
// ============================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["unfollow_user"])
) {

    $followingId =
        (int)(
            $_POST["following_id"] ?? 0
        );


    if (
        $followingId > 0 &&
        $followingId !== $uid
    ) {

        $stmt =
            $conn->prepare("
                DELETE FROM follows
                WHERE follower_id = ?
                AND following_id = ?
            ");


        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $uid,
                $followingId
            );

            $stmt->execute();

            $stmt->close();

        }

    }


    redirectHome();

}


// ============================================================
// LIKE / UNLIKE
// IMPORTANT:
// SELECT 1 instead of SELECT id
// because your likes table does not appear to use an id column.
// ============================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["like_post"])
) {

    $postId =
        (int)(
            $_POST["post_id"] ?? 0
        );


    if (
        $postId > 0
    ) {

        $alreadyLiked = false;


        $stmt =
            $conn->prepare("
                SELECT 1
                FROM likes
                WHERE post_id = ?
                AND user_id = ?
                LIMIT 1
            ");


        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $postId,
                $uid
            );

            $stmt->execute();

            $alreadyLiked =
                $stmt
                    ->get_result()
                    ->num_rows > 0;

            $stmt->close();

        }


        if (
            $alreadyLiked
        ) {

            $stmt =
                $conn->prepare("
                    DELETE FROM likes
                    WHERE post_id = ?
                    AND user_id = ?
                ");

        } else {

            $stmt =
                $conn->prepare("
                    INSERT INTO likes
                    (
                        post_id,
                        user_id
                    )
                    VALUES (?, ?)
                ");

        }


        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $postId,
                $uid
            );

            $stmt->execute();

            $stmt->close();

        }

    }


    redirectHome(
        "#post-" . $postId
    );

}


// ============================================================
// ADD COMMENT
// ============================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["add_comment"])
) {

    $postId =
        (int)(
            $_POST["post_id"] ?? 0
        );


    $comment =
        trim(
            (string)(
                $_POST["comment"] ?? ""
            )
        );


    if (
        $postId > 0 &&
        $comment !== ""
    ) {

        $stmt =
            $conn->prepare("
                INSERT INTO comments
                (
                    post_id,
                    user_id,
                    comment
                )
                VALUES (?, ?, ?)
            ");


        if ($stmt) {

            $stmt->bind_param(
                "iis",
                $postId,
                $uid,
                $comment
            );

            $stmt->execute();

            $stmt->close();

        }

    }


    redirectHome(
        "#post-" . $postId
    );

}


// ============================================================
// DELETE POST
// ============================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_post"])
) {

    $postId =
        (int)(
            $_POST["post_id"] ?? 0
        );


    $postData = null;


    if (
        $postId > 0
    ) {

        $stmt =
            $conn->prepare("
                SELECT image
                FROM posts
                WHERE id = ?
                AND user_id = ?
                LIMIT 1
            ");


        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $postId,
                $uid
            );

            $stmt->execute();

            $postData =
                $stmt
                    ->get_result()
                    ->fetch_assoc();

            $stmt->close();

        }

    }


    if (
        $postData
    ) {

        $imageName =
            trim(
                (string)(
                    $postData["image"] ?? ""
                )
            );


        if (
            $imageName !== ""
        ) {

            $filePath =
                __DIR__ .
                DIRECTORY_SEPARATOR .
                "uploads" .
                DIRECTORY_SEPARATOR .
                basename(
                    $imageName
                );


            if (
                is_file($filePath)
            ) {

                @unlink(
                    $filePath
                );

            }

        }


        // Delete likes

        $stmt =
            $conn->prepare("
                DELETE FROM likes
                WHERE post_id = ?
            ");


        if ($stmt) {

            $stmt->bind_param(
                "i",
                $postId
            );

            $stmt->execute();

            $stmt->close();

        }


        // Delete comments

        $stmt =
            $conn->prepare("
                DELETE FROM comments
                WHERE post_id = ?
            ");


        if ($stmt) {

            $stmt->bind_param(
                "i",
                $postId
            );

            $stmt->execute();

            $stmt->close();

        }


        // Delete post

        $stmt =
            $conn->prepare("
                DELETE FROM posts
                WHERE id = ?
                AND user_id = ?
            ");


        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $postId,
                $uid
            );

            $stmt->execute();

            $stmt->close();

        }

    }


    redirectHome();

}


// ============================================================
// SHARE POST
// ============================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["share_post"])
) {

    $postId =
        (int)(
            $_POST["post_id"] ?? 0
        );


    $receiverId =
        (int)(
            $_POST["receiver_id"] ?? 0
        );


    if (
        $postId > 0 &&
        $receiverId > 0 &&
        $receiverId !== $uid
    ) {

        $postExists = false;

        $receiverExists = false;


        // Check post

        $stmt =
            $conn->prepare("
                SELECT 1
                FROM posts
                WHERE id = ?
                LIMIT 1
            ");


        if ($stmt) {

            $stmt->bind_param(
                "i",
                $postId
            );

            $stmt->execute();

            $postExists =
                $stmt
                    ->get_result()
                    ->num_rows > 0;

            $stmt->close();

        }


        // Check receiver

        $stmt =
            $conn->prepare("
                SELECT 1
                FROM users
                WHERE id = ?
                LIMIT 1
            ");


        if ($stmt) {

            $stmt->bind_param(
                "i",
                $receiverId
            );

            $stmt->execute();

            $receiverExists =
                $stmt
                    ->get_result()
                    ->num_rows > 0;

            $stmt->close();

        }


        if (
            $postExists &&
            $receiverExists
        ) {

            $chatId = 0;


            // -----------------------------------------------
            // FIND EXISTING CHAT
            // -----------------------------------------------

            $stmt =
                $conn->prepare("
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


            if ($stmt) {

                $stmt->bind_param(
                    "ii",
                    $uid,
                    $receiverId
                );

                $stmt->execute();

                $chat =
                    $stmt
                        ->get_result()
                        ->fetch_assoc();

                if ($chat) {

                    $chatId =
                        (int)$chat["id"];

                }

                $stmt->close();

            }


            // -----------------------------------------------
            // CREATE CHAT IF NEEDED
            // -----------------------------------------------

            if (
                $chatId === 0
            ) {

                $created =
                    $conn->query("
                        INSERT INTO chats ()
                        VALUES ()
                    ");


                if (
                    $created
                ) {

                    $chatId =
                        (int)$conn->insert_id;


                    // Current user

                    $stmt =
                        $conn->prepare("
                            INSERT INTO chat_members
                            (
                                chat_id,
                                user_id
                            )
                            VALUES (?, ?)
                        ");


                    if ($stmt) {

                        $stmt->bind_param(
                            "ii",
                            $chatId,
                            $uid
                        );

                        $stmt->execute();

                        $stmt->close();

                    }


                    // Receiver

                    $stmt =
                        $conn->prepare("
                            INSERT INTO chat_members
                            (
                                chat_id,
                                user_id
                            )
                            VALUES (?, ?)
                        ");


                    if ($stmt) {

                        $stmt->bind_param(
                            "ii",
                            $chatId,
                            $receiverId
                        );

                        $stmt->execute();

                        $stmt->close();

                    }

                }

            }


            // -----------------------------------------------
            // SEND SHARED POST MESSAGE
            // -----------------------------------------------

            if (
                $chatId > 0
            ) {

                $message =
                    "📸 Shared a post with you";

                $filePath =
                    (string)$postId;

                $messageType =
                    "post";


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

            }

        }

    }


    redirectHome();

}


// ============================================================
// SHARE USERS
// ============================================================

$shareUsers = [];


$stmt =
    $conn->prepare("
        SELECT
            id,
            name,
            profile_image
        FROM users
        WHERE id != ?
        ORDER BY name ASC
    ");


if ($stmt) {

    $stmt->bind_param(
        "i",
        $uid
    );

    $stmt->execute();

    $result =
        $stmt->get_result();


    while (
        $row =
        $result->fetch_assoc()
    ) {

        $shareUsers[] =
            $row;

    }


    $stmt->close();

}


// ============================================================
// COUNTS
// ============================================================

$postCount = 0;
$followingCount = 0;
$followerCount = 0;


// Posts

$stmt =
    $conn->prepare("
        SELECT COUNT(*) AS total
        FROM posts
        WHERE user_id = ?
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

    $postCount =
        (int)(
            $row["total"] ?? 0
        );

    $stmt->close();

}


// Following

$stmt =
    $conn->prepare("
        SELECT COUNT(*) AS total
        FROM follows
        WHERE follower_id = ?
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

    $followingCount =
        (int)(
            $row["total"] ?? 0
        );

    $stmt->close();

}


// Followers

$stmt =
    $conn->prepare("
        SELECT COUNT(*) AS total
        FROM follows
        WHERE following_id = ?
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

    $followerCount =
        (int)(
            $row["total"] ?? 0
        );

    $stmt->close();

}


// ============================================================
// FEED
// ============================================================

$posts = false;


$postsStatement =
    $conn->prepare("
        SELECT

            posts.id,
            posts.user_id,
            posts.image,
            posts.caption,
            posts.media_type,
            posts.created_at,

            users.name,
            users.profile_image

        FROM posts

        INNER JOIN users
            ON users.id = posts.user_id

        WHERE

            posts.user_id = ?

            OR posts.user_id IN (

                SELECT following_id

                FROM follows

                WHERE follower_id = ?

            )

        ORDER BY posts.id DESC
    ");


if ($postsStatement) {

    $postsStatement->bind_param(
        "ii",
        $uid,
        $uid
    );

    $postsStatement->execute();

    $posts =
        $postsStatement
            ->get_result();

}


// ============================================================
// HEADER
// ============================================================

require "header.php";

?>


<!-- ============================================================
     CONNECTHUB HOME
============================================================ -->

<div class="ch-home">


    <!-- ========================================================
         HERO
    ========================================================= -->

    <section class="ch-hero">


        <div class="ch-hero-glow one"></div>

        <div class="ch-hero-glow two"></div>


        <div class="ch-hero-main">


            <div class="ch-avatar-wrap">


                <?php if (
                    $currentUserImage !== ""
                ): ?>

                    <img
                        src="<?= e($currentUserImage) ?>"
                        class="ch-main-avatar"
                        alt="Profile"
                    >

                <?php else: ?>

                    <div class="
                        ch-main-avatar
                        ch-avatar-fallback
                    ">
                        <?= e(
                            strtoupper(
                                substr(
                                    $currentUserName,
                                    0,
                                    1
                                )
                            )
                        ) ?>
                    </div>

                <?php endif; ?>


                <span class="ch-online"></span>


            </div>


            <div class="ch-hero-copy">


                <div class="ch-kicker">
                    CONNECTHUB • YOUR DIGITAL SPACE
                </div>


                <h1>

                    Welcome back,

                    <span>
                        <?= e($currentUserName) ?>
                    </span>

                    👋

                </h1>


                <p>
                    Connect with people, share moments,
                    explore products, manage your account
                    and enjoy the ConnectHub arcade.
                </p>


            </div>


        </div>


        <div class="ch-hero-buttons">


            <a
                href="#create-post"
                class="ch-button primary"
            >
                ✦ Create Post
            </a>


            <a
                href="users.php"
                class="ch-button"
            >
                👥 Find People
            </a>


            <a
                href="chat.php"
                class="ch-button"
            >
                💬 Messages
            </a>


            <a
                href="games.php"
                class="ch-button"
            >
                🎮 Arcade
            </a>


        </div>


    </section>


    <!-- ========================================================
         STAT CARDS
    ========================================================= -->

    <section class="ch-stat-grid">


        <div class="ch-stat">

            <div class="ch-stat-icon blue">
                📸
            </div>

            <div>

                <small>
                    POSTS
                </small>

                <strong>
                    <?= $postCount ?>
                </strong>

            </div>

        </div>


        <div class="ch-stat">

            <div class="ch-stat-icon purple">
                👥
            </div>

            <div>

                <small>
                    FOLLOWING
                </small>

                <strong>
                    <?= $followingCount ?>
                </strong>

            </div>

        </div>


        <div class="ch-stat">

            <div class="ch-stat-icon cyan">
                🤝
            </div>

            <div>

                <small>
                    FOLLOWERS
                </small>

                <strong>
                    <?= $followerCount ?>
                </strong>

            </div>

        </div>


        <a
            href="games.php"
            class="ch-stat ch-stat-link"
        >

            <div class="ch-stat-icon gold">
                🎮
            </div>

            <div>

                <small>
                    CONNECTHUB ARCADE
                </small>

                <strong>
                    PLAY & EARN
                </strong>

            </div>

            <span>
                →
            </span>

        </a>


    </section>


    <!-- ========================================================
         QUICK BUTTONS
    ========================================================= -->

    <div class="ch-quick-bar">


        <a
            href="#create-post"
            class="ch-quick"
        >
            <span>✦</span>
            Post
        </a>


        <a
            href="users.php"
            class="ch-quick"
        >
            <span>👥</span>
            People
        </a>


        <a
            href="chat.php"
            class="ch-quick"
        >
            <span>💬</span>
            Messages
        </a>


        <a
            href="shop.php"
            class="ch-quick"
        >
            <span>🛒</span>
            Shop
        </a>


        <a
            href="bank.php"
            class="ch-quick"
        >
            <span>🏦</span>
            Banking
        </a>


        <a
            href="profile.php"
            class="ch-quick"
        >
            <span>👤</span>
            Profile
        </a>


    </div>


    <!-- ========================================================
         CONTENT
    ========================================================= -->

    <div class="ch-columns">


        <!-- ====================================================
             FEED
        ===================================================== -->

        <section class="ch-feed">


            <!-- ==================================================
                 CREATE POST
            =================================================== -->

            <section
                class="ch-composer"
                id="create-post"
            >


                <div class="ch-section-title">


                    <div class="ch-section-icon">
                        ✦
                    </div>


                    <div>

                        <small>
                            SHARE SOMETHING
                        </small>

                        <h2>
                            Create a Post
                        </h2>

                        <p>
                            Publish a photo, video or thought.
                        </p>

                    </div>


                </div>


                <?php if (
                    $error !== ""
                ): ?>

                    <div class="ch-error">

                        ⚠️

                        <?= e($error) ?>

                    </div>

                <?php endif; ?>


                <form
                    method="POST"
                    enctype="multipart/form-data"
                >


                    <div class="ch-compose-row">


                        <?php if (
                            $currentUserImage !== ""
                        ): ?>

                            <img
                                src="<?= e($currentUserImage) ?>"
                                class="ch-compose-avatar"
                                alt="You"
                            >

                        <?php else: ?>

                            <div class="
                                ch-compose-avatar
                                ch-avatar-fallback
                            ">
                                <?= e(
                                    strtoupper(
                                        substr(
                                            $currentUserName,
                                            0,
                                            1
                                        )
                                    )
                                ) ?>
                            </div>

                        <?php endif; ?>


                        <textarea
                            name="caption"
                            maxlength="3000"
                            placeholder="What's on your mind, <?= e($currentUserName) ?>?"
                        ></textarea>


                    </div>


                    <div
                        class="ch-preview"
                        id="chPreview"
                    >


                        <button
                            type="button"
                            id="chPreviewRemove"
                            class="ch-preview-remove"
                        >
                            ×
                        </button>


                        <img
                            id="chImagePreview"
                            src=""
                            alt="Preview"
                        >


                        <video
                            id="chVideoPreview"
                            controls
                            muted
                            playsinline
                        ></video>


                        <div
                            id="chPreviewName"
                            class="ch-preview-name"
                        ></div>


                    </div>


                    <div class="ch-compose-bottom">


                        <label class="ch-upload">


                            <span>
                                📷
                            </span>


                            <div>

                                <strong>
                                    Photo / Video
                                </strong>

                                <small>
                                    Add media
                                </small>

                            </div>


                            <input
                                type="file"
                                name="media"
                                id="chMediaInput"
                                accept="
                                    image/jpeg,
                                    image/png,
                                    image/gif,
                                    image/webp,
                                    video/mp4,
                                    video/webm,
                                    video/quicktime,
                                    video/ogg
                                "
                            >


                        </label>


                        <span class="ch-formats">
                            JPG • PNG • GIF • WEBP • MP4 • WEBM • MOV
                        </span>


                        <button
                            type="submit"
                            name="create_post"
                            class="ch-publish"
                        >
                            📤 Publish
                        </button>


                    </div>


                </form>


            </section>


            <!-- ==================================================
                 FEED TITLE
            =================================================== -->

            <div class="ch-feed-heading">


                <div>

                    <small>
                        COMMUNITY FEED
                    </small>

                    <h2>
                        Latest Posts
                    </h2>

                    <p>
                        New updates from your ConnectHub network.
                    </p>

                </div>


                <div class="ch-live">

                    <span></span>

                    LIVE

                </div>


            </div>


            <!-- ==================================================
                 POSTS
            =================================================== -->

            <div class="ch-post-list">


                <?php if (
                    $posts &&
                    $posts->num_rows > 0
                ): ?>


                    <?php while (
                        $post =
                        $posts->fetch_assoc()
                    ): ?>


                        <?php

                        $postId =
                            (int)$post["id"];

                        $postUserId =
                            (int)$post["user_id"];

                        $postName =
                            trim(
                                (string)(
                                    $post["name"] ??
                                    "User"
                                )
                            );

                        if (
                            $postName === ""
                        ) {
                            $postName = "User";
                        }


                        $postProfile =
                            homeMediaPath(
                                $post["profile_image"] ?? ""
                            );


                        $postMedia =
                            homeMediaPath(
                                $post["image"] ?? ""
                            );


                        $mediaType =
                            strtolower(
                                trim(
                                    (string)(
                                        $post["media_type"] ??
                                        "image"
                                    )
                                )
                            );


                        if (
                            $mediaType !== "video"
                        ) {
                            $mediaType = "image";
                        }


                        // ==========================================
                        // FOLLOW CHECK
                        // SELECT 1, not SELECT id
                        // ==========================================

                        $isFollowing = false;


                        if (
                            $postUserId !== $uid
                        ) {

                            $stmt =
                                $conn->prepare("
                                    SELECT 1
                                    FROM follows
                                    WHERE follower_id = ?
                                    AND following_id = ?
                                    LIMIT 1
                                ");


                            if ($stmt) {

                                $stmt->bind_param(
                                    "ii",
                                    $uid,
                                    $postUserId
                                );

                                $stmt->execute();

                                $isFollowing =
                                    $stmt
                                        ->get_result()
                                        ->num_rows > 0;

                                $stmt->close();

                            }

                        }


                        // ==========================================
                        // LIKE COUNT
                        // ==========================================

                        $likeCount = 0;


                        $stmt =
                            $conn->prepare("
                                SELECT COUNT(*) AS total
                                FROM likes
                                WHERE post_id = ?
                            ");


                        if ($stmt) {

                            $stmt->bind_param(
                                "i",
                                $postId
                            );

                            $stmt->execute();

                            $row =
                                $stmt
                                    ->get_result()
                                    ->fetch_assoc();

                            $likeCount =
                                (int)(
                                    $row["total"] ?? 0
                                );

                            $stmt->close();

                        }


                        // ==========================================
                        // CURRENT LIKE
                        // ==========================================

                        $userLiked = false;


                        $stmt =
                            $conn->prepare("
                                SELECT 1
                                FROM likes
                                WHERE post_id = ?
                                AND user_id = ?
                                LIMIT 1
                            ");


                        if ($stmt) {

                            $stmt->bind_param(
                                "ii",
                                $postId,
                                $uid
                            );

                            $stmt->execute();

                            $userLiked =
                                $stmt
                                    ->get_result()
                                    ->num_rows > 0;

                            $stmt->close();

                        }


                        // ==========================================
                        // COMMENT COUNT
                        // ==========================================

                        $commentCount = 0;


                        $stmt =
                            $conn->prepare("
                                SELECT COUNT(*) AS total
                                FROM comments
                                WHERE post_id = ?
                            ");


                        if ($stmt) {

                            $stmt->bind_param(
                                "i",
                                $postId
                            );

                            $stmt->execute();

                            $row =
                                $stmt
                                    ->get_result()
                                    ->fetch_assoc();

                            $commentCount =
                                (int)(
                                    $row["total"] ?? 0
                                );

                            $stmt->close();

                        }

                        ?>


                        <!-- ==================================================
                             POST CARD
                        =================================================== -->

                        <article
                            class="ch-post"
                            id="post-<?= $postId ?>"
                        >


                            <!-- ==============================================
                                 POST HEADER
                            =============================================== -->

                            <div class="ch-post-header">


                                <div class="ch-author">


                                    <div class="ch-post-avatar-wrap">


                                        <?php if (
                                            $postProfile !== ""
                                        ): ?>


                                            <img
                                                src="<?= e($postProfile) ?>"
                                                class="ch-post-avatar"
                                                alt="Profile"
                                                loading="lazy"
                                                onerror="
                                                    this.style.display='none';
                                                    this.nextElementSibling.style.display='grid';
                                                "
                                            >


                                            <div
                                                class="
                                                    ch-post-avatar
                                                    ch-avatar-fallback
                                                "
                                                style="display:none;"
                                            >
                                                <?= e(
                                                    strtoupper(
                                                        substr(
                                                            $postName,
                                                            0,
                                                            1
                                                        )
                                                    )
                                                ) ?>
                                            </div>


                                        <?php else: ?>


                                            <div class="
                                                ch-post-avatar
                                                ch-avatar-fallback
                                            ">
                                                <?= e(
                                                    strtoupper(
                                                        substr(
                                                            $postName,
                                                            0,
                                                            1
                                                        )
                                                    )
                                                ) ?>
                                            </div>


                                        <?php endif; ?>


                                        <span class="ch-post-online"></span>


                                    </div>


                                    <div class="ch-author-info">


                                        <strong>
                                            <?= e($postName) ?>
                                        </strong>


                                        <span>
                                            <?= e(
                                                (string)(
                                                    $post["created_at"] ??
                                                    ""
                                                )
                                            ) ?>
                                        </span>


                                    </div>


                                </div>


                                <?php if (
                                    $postUserId !== $uid
                                ): ?>


                                    <form
                                        method="POST"
                                    >


                                        <input
                                            type="hidden"
                                            name="following_id"
                                            value="<?= $postUserId ?>"
                                        >


                                        <?php if (
                                            $isFollowing
                                        ): ?>


                                            <button
                                                type="submit"
                                                name="unfollow_user"
                                                class="ch-follow following"
                                            >
                                                ✓ Following
                                            </button>


                                        <?php else: ?>


                                            <button
                                                type="submit"
                                                name="follow_user"
                                                class="ch-follow"
                                            >
                                                + Follow
                                            </button>


                                        <?php endif; ?>


                                    </form>


                                <?php endif; ?>


                            </div>


                            <!-- ==============================================
                                 MEDIA
                            =============================================== -->

                            <?php if (
                                $postMedia !== ""
                            ): ?>


                                <div class="ch-post-media">


                                    <div class="ch-media-label">

                                        <?= $mediaType === "video"
                                            ? "▶ VIDEO"
                                            : "✦ POST"
                                        ?>

                                    </div>


                                    <?php if (
                                        $mediaType === "video"
                                    ): ?>


                                        <video
                                            class="ch-post-media-element"
                                            controls
                                            playsinline
                                            preload="metadata"
                                        >

                                            <source
                                                src="<?= e($postMedia) ?>"
                                            >

                                        </video>


                                    <?php else: ?>


                                        <button
                                            type="button"
                                            class="ch-image-button"
                                            data-image="<?= e($postMedia) ?>"
                                        >


                                            <img
                                                src="<?= e($postMedia) ?>"
                                                class="ch-post-media-element"
                                                alt="Post image"
                                                loading="lazy"
                                            >


                                            <span class="ch-expand">
                                                ⤢
                                            </span>


                                        </button>


                                    <?php endif; ?>


                                </div>


                            <?php endif; ?>


                            <!-- ==============================================
                                 CAPTION
                            =============================================== -->

                            <?php if (
                                trim(
                                    (string)(
                                        $post["caption"] ?? ""
                                    )
                                ) !== ""
                            ): ?>


                                <div class="ch-caption">

                                    <?= nl2br(
                                        e(
                                            $post["caption"]
                                        )
                                    ) ?>

                                </div>


                            <?php endif; ?>


                            <!-- ==============================================
                                 ACTION BAR
                            =============================================== -->

                            <div class="ch-actions">


                                <form
                                    method="POST"
                                >


                                    <input
                                        type="hidden"
                                        name="post_id"
                                        value="<?= $postId ?>"
                                    >


                                    <button
                                        type="submit"
                                        name="like_post"
                                        class="
                                            ch-action
                                            <?= $userLiked ? "liked" : "" ?>
                                        "
                                    >

                                        <span>
                                            <?= $userLiked
                                                ? "❤️"
                                                : "🤍"
                                            ?>
                                        </span>

                                        Like

                                        <b>
                                            <?= $likeCount ?>
                                        </b>

                                    </button>


                                </form>


                                <button
                                    type="button"
                                    class="ch-action ch-comment-button"
                                    data-input="comment-<?= $postId ?>"
                                >

                                    💬 Comment

                                    <b>
                                        <?= $commentCount ?>
                                    </b>

                                </button>


                                <button
                                    type="button"
                                    class="ch-action ch-share-button"
                                    data-target="share-<?= $postId ?>"
                                >

                                    ↗ Share

                                </button>


                            </div>


                            <!-- ==============================================
                                 SHARE PANEL
                            =============================================== -->

                            <div
                                class="ch-share-panel"
                                id="share-<?= $postId ?>"
                            >


                                <div class="ch-share-heading">


                                    <div>

                                        <strong>
                                            Share this post
                                        </strong>

                                        <small>
                                            Send it to another ConnectHub user.
                                        </small>

                                    </div>


                                    <button
                                        type="button"
                                        class="ch-share-close"
                                        data-target="share-<?= $postId ?>"
                                    >
                                        ×
                                    </button>


                                </div>


                                <?php if (
                                    count($shareUsers) > 0
                                ): ?>


                                    <form
                                        method="POST"
                                        class="ch-share-form"
                                    >


                                        <input
                                            type="hidden"
                                            name="post_id"
                                            value="<?= $postId ?>"
                                        >


                                        <select
                                            name="receiver_id"
                                            required
                                        >

                                            <option value="">
                                                Select person
                                            </option>


                                            <?php foreach (
                                                $shareUsers
                                                as $shareUser
                                            ): ?>


                                                <option
                                                    value="<?= (int)$shareUser["id"] ?>"
                                                >
                                                    <?= e(
                                                        $shareUser["name"]
                                                    ) ?>
                                                </option>


                                            <?php endforeach; ?>


                                        </select>


                                        <button
                                            type="submit"
                                            name="share_post"
                                        >
                                            📤 Send
                                        </button>


                                    </form>


                                <?php else: ?>


                                    <div class="ch-no-users">
                                        No other users available.
                                    </div>


                                <?php endif; ?>


                            </div>


                            <!-- ==============================================
                                 COMMENTS
                            =============================================== -->

                            <div class="ch-comments">


                                <?php

                                $comments = false;


                                $commentStmt =
                                    $conn->prepare("
                                        SELECT
                                            comments.comment,
                                            comments.created_at,
                                            users.name,
                                            users.profile_image
                                        FROM comments

                                        INNER JOIN users
                                            ON users.id =
                                               comments.user_id

                                        WHERE comments.post_id = ?

                                        ORDER BY
                                            comments.created_at ASC
                                    ");


                                if ($commentStmt) {

                                    $commentStmt->bind_param(
                                        "i",
                                        $postId
                                    );

                                    $commentStmt->execute();

                                    $comments =
                                        $commentStmt
                                            ->get_result();

                                }

                                ?>


                                <?php if (
                                    $comments &&
                                    $comments->num_rows > 0
                                ): ?>


                                    <div class="ch-comment-list">


                                        <?php while (
                                            $comment =
                                            $comments->fetch_assoc()
                                        ): ?>


                                            <?php

                                            $commentName =
                                                trim(
                                                    (string)(
                                                        $comment["name"] ??
                                                        "User"
                                                    )
                                                );

                                            if (
                                                $commentName === ""
                                            ) {
                                                $commentName = "User";
                                            }


                                            $commentImage =
                                                homeMediaPath(
                                                    $comment["profile_image"] ?? ""
                                                );

                                            ?>


                                            <div class="ch-comment">


                                                <?php if (
                                                    $commentImage !== ""
                                                ): ?>


                                                    <img
                                                        src="<?= e($commentImage) ?>"
                                                        alt="Profile"
                                                        loading="lazy"
                                                    >


                                                <?php else: ?>


                                                    <div class="
                                                        ch-comment-avatar
                                                        ch-avatar-fallback
                                                    ">
                                                        <?= e(
                                                            strtoupper(
                                                                substr(
                                                                    $commentName,
                                                                    0,
                                                                    1
                                                                )
                                                            )
                                                        ) ?>
                                                    </div>


                                                <?php endif; ?>


                                                <div class="ch-comment-body">


                                                    <strong>
                                                        <?= e($commentName) ?>
                                                    </strong>


                                                    <p>
                                                        <?= nl2br(
                                                            e(
                                                                $comment["comment"] ?? ""
                                                            )
                                                        ) ?>
                                                    </p>


                                                </div>


                                            </div>


                                        <?php endwhile; ?>


                                    </div>


                                <?php endif; ?>


                                <?php

                                if (
                                    $commentStmt
                                ) {

                                    $commentStmt->close();

                                }

                                ?>


                                <!-- Comment form -->

                                <form
                                    method="POST"
                                    class="ch-comment-form"
                                >


                                    <input
                                        type="hidden"
                                        name="post_id"
                                        value="<?= $postId ?>"
                                    >


                                    <?php if (
                                        $currentUserImage !== ""
                                    ): ?>


                                        <img
                                            src="<?= e($currentUserImage) ?>"
                                            alt="You"
                                        >


                                    <?php else: ?>


                                        <div class="
                                            ch-comment-me
                                            ch-avatar-fallback
                                        ">
                                            <?= e(
                                                strtoupper(
                                                    substr(
                                                        $currentUserName,
                                                        0,
                                                        1
                                                    )
                                                )
                                            ) ?>
                                        </div>


                                    <?php endif; ?>


                                    <input
                                        type="text"
                                        name="comment"
                                        id="comment-<?= $postId ?>"
                                        maxlength="1000"
                                        autocomplete="off"
                                        placeholder="Write a comment..."
                                        required
                                    >


                                    <button
                                        type="submit"
                                        name="add_comment"
                                    >
                                        ➤
                                    </button>


                                </form>


                            </div>


                            <!-- ==============================================
                                 DELETE
                            =============================================== -->

                            <?php if (
                                $postUserId === $uid
                            ): ?>


                                <div class="ch-delete-area">


                                    <form
                                        method="POST"
                                        onsubmit="
                                            return confirm(
                                                'Delete this post?'
                                            );
                                        "
                                    >


                                        <input
                                            type="hidden"
                                            name="post_id"
                                            value="<?= $postId ?>"
                                        >


                                        <button
                                            type="submit"
                                            name="delete_post"
                                            class="ch-delete"
                                        >
                                            🗑 Delete Post
                                        </button>


                                    </form>


                                </div>


                            <?php endif; ?>


                        </article>


                    <?php endwhile; ?>


                <?php else: ?>


                    <!-- ==================================================
                         EMPTY FEED
                    =================================================== -->

                    <section class="ch-empty">


                        <div class="ch-empty-icon">

                            <span>✦</span>

                        </div>


                        <small>
                            CONNECTHUB FEED
                        </small>


                        <h2>
                            Your feed is waiting for you
                        </h2>


                        <p>
                            Create your first post or discover
                            people and start building your network.
                        </p>


                        <div class="ch-empty-buttons">


                            <a
                                href="#create-post"
                                class="ch-empty-button primary"
                            >
                                📸 Create Post
                            </a>


                            <a
                                href="users.php"
                                class="ch-empty-button"
                            >
                                👥 Find People
                            </a>


                        </div>


                    </section>


                <?php endif; ?>


            </div>


        </section>


        <!-- ====================================================
             RIGHT SIDEBAR
        ===================================================== -->

        <aside class="ch-sidebar">


            <!-- ==================================================
                 PROFILE CARD
            =================================================== -->

            <section class="ch-profile-card">


                <div class="ch-cover">


                    <div class="ch-cover-glow"></div>


                </div>


                <div class="ch-profile-body">


                    <div class="ch-side-avatar">


                        <?php if (
                            $currentUserImage !== ""
                        ): ?>


                            <img
                                src="<?= e($currentUserImage) ?>"
                                alt="Profile"
                            >


                        <?php else: ?>


                            <div class="
                                ch-side-avatar-img
                                ch-avatar-fallback
                            ">
                                <?= e(
                                    strtoupper(
                                        substr(
                                            $currentUserName,
                                            0,
                                            1
                                        )
                                    )
                                ) ?>
                            </div>


                        <?php endif; ?>


                    </div>


                    <h3>
                        <?= e($currentUserName) ?>
                    </h3>


                    <span>
                        CONNECTHUB MEMBER
                    </span>


                    <div class="ch-side-stats">


                        <div>

                            <strong>
                                <?= $postCount ?>
                            </strong>

                            <small>
                                Posts
                            </small>

                        </div>


                        <div>

                            <strong>
                                <?= $followerCount ?>
                            </strong>

                            <small>
                                Followers
                            </small>

                        </div>


                        <div>

                            <strong>
                                <?= $followingCount ?>
                            </strong>

                            <small>
                                Following
                            </small>

                        </div>


                    </div>


                    <a
                        href="profile.php"
                        class="ch-view-profile"
                    >
                        👤 View Profile
                    </a>


                </div>


            </section>


            <!-- ==================================================
                 QUICK ACCESS
            =================================================== -->

            <section class="ch-side-panel">


                <div class="ch-side-title">
                    ⚡ Quick Access
                </div>


                <a
                    href="users.php"
                    class="ch-side-link"
                >

                    <span class="blue">
                        👥
                    </span>

                    <div>

                        <strong>
                            Find People
                        </strong>

                        <small>
                            Discover new users
                        </small>

                    </div>

                    <b>
                        →
                    </b>

                </a>


                <a
                    href="chat.php"
                    class="ch-side-link"
                >

                    <span class="purple">
                        💬
                    </span>

                    <div>

                        <strong>
                            Messages
                        </strong>

                        <small>
                            Chat with connections
                        </small>

                    </div>

                    <b>
                        →
                    </b>

                </a>


                <a
                    href="shop.php"
                    class="ch-side-link"
                >

                    <span class="cyan">
                        🛒
                    </span>

                    <div>

                        <strong>
                            Shop
                        </strong>

                        <small>
                            Explore products
                        </small>

                    </div>

                    <b>
                        →
                    </b>

                </a>


                <a
                    href="bank.php"
                    class="ch-side-link"
                >

                    <span class="green">
                        🏦
                    </span>

                    <div>

                        <strong>
                            Banking
                        </strong>

                        <small>
                            Manage your account
                        </small>

                    </div>

                    <b>
                        →
                    </b>

                </a>


            </section>


            <!-- ==================================================
                 ARCADE
            =================================================== -->

            <section class="ch-arcade">


                <div class="ch-arcade-glow"></div>


                <div class="ch-arcade-top">

                    <span>
                        CONNECTHUB ARCADE
                    </span>

                    <b>
                        🎮
                    </b>

                </div>


                <h3>
                    Play. Score. Compete.
                </h3>


                <p>
                    Enter your ConnectHub games,
                    improve your scores and collect
                    your game rewards.
                </p>


                <div class="ch-arcade-icons">


                    <span>
                        🥷
                    </span>


                    <span>
                        ⚔️
                    </span>


                    <span>
                        🚀
                    </span>


                    <span>
                        🏎️
                    </span>


                    <span>
                        🐍
                    </span>


                </div>


                <a
                    href="games.php"
                >
                    ENTER ARCADE →
                </a>


            </section>


            <!-- ==================================================
                 SECURITY
            =================================================== -->

            <section class="ch-side-panel">


                <div class="ch-side-title">
                    🛡 Security
                </div>


                <div class="ch-security">


                    <div>
                        🔐
                    </div>


                    <p>
                        Never share your password,
                        Banking PIN or account details
                        with anyone.
                    </p>


                </div>


            </section>


        </aside>


    </div>


</div>


<!-- ============================================================
     IMAGE LIGHTBOX
============================================================ -->

<div
    class="ch-lightbox"
    id="chLightbox"
>


    <button
        type="button"
        id="chLightboxClose"
    >
        ×
    </button>


    <img
        id="chLightboxImage"
        src=""
        alt="Full size post"
    >


</div>


<style>

/* ============================================================
   HOME BASE
============================================================ */

.ch-home {

    width:
        min(
            1380px,
            100%
        );

    margin:
        0 auto;

    padding:
        22px
        22px
        75px;

    color:
        #e2e8f0;

}


/* ============================================================
   HERO
============================================================ */

.ch-hero {

    position:
        relative;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        20px;

    min-height:
        205px;

    padding:
        29px;

    margin-bottom:
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
            .23
        );

    border-radius:
        24px;

    background:

        linear-gradient(
            135deg,
            rgba(
                3,
                10,
                27,
                .96
            ),
            rgba(
                8,
                31,
                72,
                .89
            ),
            rgba(
                49,
                38,
                94,
                .84
            )
        );

    box-shadow:

        0
        22px
        60px
        rgba(
            0,
            0,
            0,
            .27
        ),

        inset
        0
        1px
        0
        rgba(
            255,
            255,
            255,
            .05
        );

    backdrop-filter:
        blur(
            12px
        );

}


.ch-hero-main {

    position:
        relative;

    z-index:
        3;

    display:
        flex;

    align-items:
        center;

    gap:
        17px;

    min-width:
        0;

}


.ch-avatar-wrap {

    position:
        relative;

    flex:
        0
        0
        80px;

}


.ch-main-avatar {

    width:
        80px;

    height:
        80px;

    display:
        block;

    object-fit:
        cover;

    border:
        2px
        solid
        rgba(
            96,
            165,
            250,
            .68
        );

    border-radius:
        24px;

    box-shadow:

        0
        0
        0
        5px
        rgba(
            37,
            99,
            235,
            .07
        ),

        0
        16px
        35px
        rgba(
            0,
            0,
            0,
            .30
        );

}


.ch-avatar-fallback {

    display:
        grid;

    place-items:
        center;

    color:
        white;

    background:

        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5,
            #7c3aed
        );

    font-weight:
        1000;

}


.ch-online {

    position:
        absolute;

    right:
        -2px;

    bottom:
        -2px;

    width:
        18px;

    height:
        18px;

    border:
        3px
        solid
        #08152c;

    border-radius:
        50%;

    background:
        #22c55e;

    box-shadow:
        0
        0
        14px
        rgba(
            34,
            197,
            94,
            .72
        );

}


.ch-kicker {

    color:
        #67e8f9;

    font-size:
        8px;

    font-weight:
        950;

    letter-spacing:
        1.9px;

}


.ch-hero-copy h1 {

    margin:
        7px
        0
        8px;

    color:
        #f8fafc;

    font-size:
        clamp(
            27px,
            3.2vw,
            38px
        );

    line-height:
        1.08;

    font-weight:
        1000;

    letter-spacing:
        -1px;

}


.ch-hero-copy h1 span {

    color:
        #60a5fa;

}


.ch-hero-copy p {

    max-width:
        700px;

    margin:
        0;

    color:
        #9eafc7;

    font-size:
        11px;

    line-height:
        1.65;

}


.ch-hero-buttons {

    position:
        relative;

    z-index:
        3;

    display:
        flex;

    flex-wrap:
        wrap;

    justify-content:
        flex-end;

    gap:
        7px;

}


.ch-button {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    min-height:
        39px;

    padding:
        0
        12px;

    border:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .17
        );

    border-radius:
        10px;

    color:
        #dbeafe;

    background:
        rgba(
            15,
            23,
            42,
            .54
        );

    text-decoration:
        none;

    font-size:
        8px;

    font-weight:
        900;

    transition:
        .20s
        ease;

}


.ch-button:hover {

    transform:
        translateY(
            -2px
        );

    color:
        #fff;

    border-color:
        rgba(
            96,
            165,
            250,
            .40
        );

    background:
        rgba(
            30,
            64,
            175,
            .20
        );

}


.ch-button.primary {

    color:
        #fff;

    border-color:
        rgba(
            96,
            165,
            250,
            .40
        );

    background:

        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    box-shadow:
        0
        10px
        25px
        rgba(
            37,
            99,
            235,
            .22
        );

}


.ch-hero-glow {

    position:
        absolute;

    border-radius:
        50%;

    pointer-events:
        none;

}


.ch-hero-glow.one {

    width:
        350px;

    height:
        350px;

    right:
        -90px;

    top:
        -185px;

    background:
        radial-gradient(
            circle,
            rgba(
                59,
                130,
                246,
                .25
            ),
            transparent 70%
        );

}


.ch-hero-glow.two {

    width:
        290px;

    height:
        290px;

    left:
        43%;

    bottom:
        -205px;

    background:
        radial-gradient(
            circle,
            rgba(
                124,
                58,
                237,
                .18
            ),
            transparent 70%
        );

}


/* ============================================================
   STATS
============================================================ */

.ch-stat-grid {

    display:
        grid;

    grid-template-columns:
        repeat(
            4,
            1fr
        );

    gap:
        8px;

    margin-bottom:
        9px;

}


.ch-stat {

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

    min-width:
        0;

    padding:
        11px
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
        14px;

    color:
        #fff;

    background:
        rgba(
            3,
            12,
            28,
            .74
        );

    text-decoration:
        none;

    backdrop-filter:
        blur(
            9px
        );

}


.ch-stat small {

    display:
        block;

    color:
        #64748b;

    font-size:
        6px;

    font-weight:
        900;

    letter-spacing:
        .9px;

}


.ch-stat strong {

    display:
        block;

    margin-top:
        3px;

    color:
        #f8fafc;

    font-size:
        14px;

    font-weight:
        1000;

}


.ch-stat-icon {

    width:
        38px;

    height:
        38px;

    display:
        grid;

    place-items:
        center;

    flex:
        0
        0
        38px;

    border-radius:
        10px;

    font-size:
        16px;

}


.ch-stat-icon.blue {

    background:
        rgba(
            59,
            130,
            246,
            .15
        );

}


.ch-stat-icon.purple {

    background:
        rgba(
            124,
            58,
            237,
            .15
        );

}


.ch-stat-icon.cyan {

    background:
        rgba(
            6,
            182,
            212,
            .14
        );

}


.ch-stat-icon.gold {

    background:
        rgba(
            234,
            179,
            8,
            .14
        );

}


.ch-stat-link {

    transition:
        .18s
        ease;

}


.ch-stat-link:hover {

    transform:
        translateY(
            -2px
        );

    border-color:
        rgba(
            96,
            165,
            250,
            .28
        );

}


.ch-stat-link > span {

    margin-left:
        auto;

    color:
        #60a5fa;

    font-size:
        17px;

}


/* ============================================================
   QUICK BAR
============================================================ */

.ch-quick-bar {

    display:
        flex;

    align-items:
        center;

    flex-wrap:
        wrap;

    gap:
        6px;

    margin-bottom:
        18px;

}


.ch-quick {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        5px;

    min-height:
        31px;

    padding:
        0
        10px;

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
        999px;

    color:
        #94a3b8;

    background:
        rgba(
            3,
            12,
            28,
            .55
        );

    text-decoration:
        none;

    font-size:
        8px;

    font-weight:
        850;

    transition:
        .18s
        ease;

}


.ch-quick:hover {

    color:
        #fff;

    border-color:
        rgba(
            96,
            165,
            250,
            .28
        );

    background:
        rgba(
            37,
            99,
            235,
            .13
        );

}


.ch-quick span {

    color:
        #60a5fa;

}


/* ============================================================
   COLUMNS
============================================================ */

.ch-columns {

    display:
        grid;

    grid-template-columns:
        minmax(
            0,
            1fr
        )
        300px;

    gap:
        18px;

    align-items:
        start;

}


.ch-feed,
.ch-sidebar {

    min-width:
        0;

}


.ch-sidebar {

    display:
        flex;

    flex-direction:
        column;

    gap:
        12px;

}


/* ============================================================
   COMPOSER
============================================================ */

.ch-composer {

    padding:
        17px;

    margin-bottom:
        18px;

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
        18px;

    background:
        rgba(
            3,
            12,
            28,
            .74
        );

    backdrop-filter:
        blur(
            10px
        );

}


.ch-section-title {

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    margin-bottom:
        13px;

}


.ch-section-icon {

    width:
        41px;

    height:
        41px;

    display:
        grid;

    place-items:
        center;

    border-radius:
        12px;

    color:
        #fff;

    background:

        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

}


.ch-section-title small,
.ch-feed-heading small {

    color:
        #67e8f9;

    font-size:
        7px;

    font-weight:
        950;

    letter-spacing:
        1.4px;

}


.ch-section-title h2 {

    margin:
        3px
        0
        2px;

    color:
        #f8fafc;

    font-size:
        16px;

}


.ch-section-title p {

    margin:
        0;

    color:
        #64748b;

    font-size:
        8px;

}


.ch-error {

    margin-bottom:
        10px;

    padding:
        9px
        10px;

    border:
        1px
        solid
        rgba(
            248,
            113,
            113,
            .18
        );

    border-radius:
        9px;

    color:
        #fecaca;

    background:
        rgba(
            127,
            29,
            29,
            .17
        );

    font-size:
        8px;

}


.ch-compose-row {

    display:
        flex;

    align-items:
        flex-start;

    gap:
        9px;

}


.ch-compose-avatar {

    width:
        41px;

    height:
        41px;

    flex:
        0
        0
        41px;

    display:
        block;

    object-fit:
        cover;

    border:
        2px
        solid
        rgba(
            96,
            165,
            250,
            .35
        );

    border-radius:
        50%;

}


.ch-compose-row textarea {

    width:
        100%;

    min-height:
        108px;

    padding:
        12px;

    border:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .12
        );

    border-radius:
        12px;

    outline:
        none;

    resize:
        vertical;

    color:
        #e2e8f0;

    background:
        rgba(
            2,
            6,
            23,
            .55
        );

    font:
        inherit;

    font-size:
        10px;

}


.ch-compose-row textarea::placeholder {

    color:
        #64748b;

}


.ch-compose-row textarea:focus {

    border-color:
        rgba(
            96,
            165,
            250,
            .42
        );

    box-shadow:
        0
        0
        0
        3px
        rgba(
            37,
            99,
            235,
            .07
        );

}


.ch-compose-bottom {

    display:
        flex;

    align-items:
        center;

    gap:
        7px;

    margin-top:
        8px;

}


.ch-upload {

    position:
        relative;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    min-height:
        40px;

    padding:
        6px
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
            .14
        );

    border-radius:
        10px;

    color:
        #dbeafe;

    background:
        rgba(
            15,
            23,
            42,
            .50
        );

    cursor:
        pointer;

}


.ch-upload input {

    position:
        absolute;

    inset:
        0;

    opacity:
        0;

    cursor:
        pointer;

}


.ch-upload > span {

    font-size:
        16px;

}


.ch-upload strong {

    display:
        block;

    font-size:
        8px;

}


.ch-upload small {

    display:
        block;

    margin-top:
        1px;

    color:
        #64748b;

    font-size:
        6px;

}


.ch-formats {

    margin-left:
        auto;

    color:
        #52637c;

    font-size:
        6px;

    text-align:
        right;

}


.ch-publish {

    min-height:
        40px;

    padding:
        0
        12px;

    border:
        0;

    border-radius:
        10px;

    color:
        #fff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    font-size:
        8px;

    font-weight:
        950;

    cursor:
        pointer;

}


/* ============================================================
   PREVIEW
============================================================ */

.ch-preview {

    position:
        relative;

    display:
        none;

    margin:
        9px 0 0 50px;

    padding:
        8px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .13
        );

    border-radius:
        11px;

    background:
        rgba(
            2,
            6,
            23,
            .55
        );

}


.ch-preview.show {

    display:
        block;

}


.ch-preview img,
.ch-preview video {

    display:
        none;

    width:
        100%;

    max-height:
        330px;

    object-fit:
        contain;

    border-radius:
        10px;

    background:
        #020617;

}


.ch-preview img.active,
.ch-preview video.active {

    display:
        block;

}


.ch-preview-remove {

    position:
        absolute;

    right:
        9px;

    top:
        9px;

    z-index:
        3;

    width:
        28px;

    height:
        28px;

    border:
        1px
        solid
        rgba(
            255,
            255,
            255,
            .10
        );

    border-radius:
        50%;

    color:
        #fff;

    background:
        rgba(
            2,
            6,
            23,
            .74
        );

    cursor:
        pointer;

}


.ch-preview-name {

    margin-top:
        5px;

    overflow:
        hidden;

    color:
        #93c5fd;

    font-size:
        7px;

    white-space:
        nowrap;

    text-overflow:
        ellipsis;

}


/* ============================================================
   FEED HEADING
============================================================ */

.ch-feed-heading {

    display:
        flex;

    align-items:
        flex-end;

    justify-content:
        space-between;

    gap:
        10px;

    margin-bottom:
        9px;

}


.ch-feed-heading h2 {

    margin:
        3px 0;

    color:
        #f8fafc;

    font-size:
        20px;

}


.ch-feed-heading p {

    margin:
        0;

    color:
        #64748b;

    font-size:
        8px;

}


.ch-live {

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
            .16
        );

    border-radius:
        999px;

    color:
        #bbf7d0;

    background:
        rgba(
            20,
            83,
            45,
            .14
        );

    font-size:
        6px;

    font-weight:
        950;

}


.ch-live span {

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
        rgba(
            34,
            197,
            94,
            .70
        );

}


/* ============================================================
   POST LIST
============================================================ */

.ch-post-list {

    display:
        flex;

    flex-direction:
        column;

    gap:
        14px;

}


.ch-post {

    overflow:
        hidden;

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
        17px;

    background:
        rgba(
            3,
            12,
            28,
            .73
        );

    box-shadow:
        0
        15px
        34px
        rgba(
            0,
            0,
            0,
            .17
        );

    backdrop-filter:
        blur(
            9px
        );

}


.ch-post-header {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        8px;

    padding:
        12px
        13px;

}


.ch-author {

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

    min-width:
        0;

}


.ch-post-avatar-wrap {

    position:
        relative;

    flex:
        0
        0
        43px;

}


.ch-post-avatar {

    width:
        43px;

    height:
        43px;

    display:
        block;

    object-fit:
        cover;

    border:
        2px
        solid
        rgba(
            96,
            165,
            250,
            .33
        );

    border-radius:
        50%;

}


.ch-post-online {

    position:
        absolute;

    right:
        0;

    bottom:
        0;

    width:
        9px;

    height:
        9px;

    border:
        2px
        solid
        #071329;

    border-radius:
        50%;

    background:
        #22c55e;

}


.ch-author-info {

    min-width:
        0;

}


.ch-author-info strong {

    display:
        block;

    color:
        #f8fafc;

    font-size:
        11px;

    font-weight:
        950;

    overflow:
        hidden;

    text-overflow:
        ellipsis;

    white-space:
        nowrap;

}


.ch-author-info span {

    display:
        block;

    margin-top:
        2px;

    color:
        #64748b;

    font-size:
        7px;

}


.ch-follow {

    min-height:
        30px;

    padding:
        0
        9px;

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
        999px;

    color:
        #dbeafe;

    background:
        rgba(
            37,
            99,
            235,
            .11
        );

    font-size:
        8px;

    font-weight:
        900;

    cursor:
        pointer;

}


.ch-follow.following {

    color:
        #cbd5e1;

    border-color:
        rgba(
            148,
            163,
            184,
            .11
        );

    background:
        rgba(
            71,
            85,
            105,
            .15
        );

}


/* ============================================================
   POST MEDIA
============================================================ */

.ch-post-media {

    position:
        relative;

    overflow:
        hidden;

    background:
        #020617;

}


.ch-media-label {

    position:
        absolute;

    left:
        9px;

    top:
        9px;

    z-index:
        4;

    padding:
        5px
        7px;

    border:
        1px
        solid
        rgba(
            255,
            255,
            255,
            .09
        );

    border-radius:
        999px;

    color:
        #dbeafe;

    background:
        rgba(
            2,
            6,
            23,
            .64
        );

    font-size:
        6px;

    font-weight:
        900;

}


.ch-post-media-element {

    display:
        block;

    width:
        100%;

    max-height:
        680px;

    object-fit:
        contain;

    background:
        #020617;

}


.ch-image-button {

    position:
        relative;

    display:
        block;

    width:
        100%;

    padding:
        0;

    border:
        0;

    background:
        transparent;

    cursor:
        zoom-in;

}


.ch-expand {

    position:
        absolute;

    right:
        10px;

    bottom:
        10px;

    width:
        32px;

    height:
        32px;

    display:
        grid;

    place-items:
        center;

    border:
        1px
        solid
        rgba(
            255,
            255,
            255,
            .10
        );

    border-radius:
        50%;

    color:
        #fff;

    background:
        rgba(
            2,
            6,
            23,
            .72
        );

    font-size:
        15px;

}


/* ============================================================
   CAPTION
============================================================ */

.ch-caption {

    padding:
        11px
        13px
        5px;

    color:
        #d1dae8;

    font-size:
        10px;

    line-height:
        1.6;

    word-break:
        break-word;

}


/* ============================================================
   ACTIONS
============================================================ */

.ch-actions {

    display:
        flex;

    align-items:
        center;

    flex-wrap:
        wrap;

    gap:
        5px;

    padding:
        8px
        10px;

    border-top:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .06
        );

}


.ch-action {

    min-height:
        31px;

    padding:
        0
        9px;

    border:
        1px
        solid
        transparent;

    border-radius:
        9px;

    color:
        #cbd5e1;

    background:
        rgba(
            15,
            23,
            42,
            .46
        );

    font-size:
        8px;

    font-weight:
        850;

    cursor:
        pointer;

}


.ch-action:hover {

    background:
        rgba(
            30,
            41,
            59,
            .68
        );

}


.ch-action b {

    color:
        #64748b;

    font-size:
        7px;

}


.ch-action.liked {

    color:
        #fecdd3;

    border-color:
        rgba(
            244,
            63,
            94,
            .14
        );

    background:
        rgba(
            190,
            24,
            93,
            .08
        );

}


/* ============================================================
   SHARE PANEL
============================================================ */

.ch-share-panel {

    display:
        none;

    padding:
        10px;

    border-top:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .06
        );

    background:
        rgba(
            2,
            6,
            23,
            .27
        );

}


.ch-share-panel.open {

    display:
        block;

}


.ch-share-heading {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        8px;

    margin-bottom:
        8px;

}


.ch-share-heading strong {

    display:
        block;

    color:
        #f8fafc;

    font-size:
        9px;

}


.ch-share-heading small {

    display:
        block;

    margin-top:
        2px;

    color:
        #64748b;

    font-size:
        7px;

}


.ch-share-close {

    width:
        28px;

    height:
        28px;

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
        50%;

    color:
        #cbd5e1;

    background:
        rgba(
            15,
            23,
            42,
            .57
        );

    cursor:
        pointer;

}


.ch-share-form {

    display:
        grid;

    grid-template-columns:
        minmax(
            0,
            1fr
        )
        auto;

    gap:
        6px;

}


.ch-share-form select {

    min-height:
        37px;

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
        9px;

    color:
        #cbd5e1;

    background:
        #0f172a;

    outline:
        none;

    font-size:
        8px;

}


.ch-share-form button {

    min-height:
        37px;

    padding:
        0
        12px;

    border:
        0;

    border-radius:
        9px;

    color:
        #fff;

    background:
        #2563eb;

    font-size:
        8px;

    font-weight:
        900;

    cursor:
        pointer;

}


.ch-no-users {

    padding:
        9px;

    color:
        #64748b;

    text-align:
        center;

    font-size:
        8px;

}


/* ============================================================
   COMMENTS
============================================================ */

.ch-comments {

    padding:
        0
        10px
        11px;

}


.ch-comment-list {

    display:
        flex;

    flex-direction:
        column;

    gap:
        7px;

    margin-bottom:
        8px;

}


.ch-comment {

    display:
        flex;

    align-items:
        flex-start;

    gap:
        7px;

}


.ch-comment img,
.ch-comment-avatar,
.ch-comment-form img,
.ch-comment-me {

    width:
        29px;

    height:
        29px;

    flex:
        0
        0
        29px;

    display:
        block;

    object-fit:
        cover;

    border-radius:
        50%;

}


.ch-comment-body {

    padding:
        6px
        8px;

    border:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .06
        );

    border-radius:
        10px
        10px
        10px
        3px;

    background:
        rgba(
            15,
            23,
            42,
            .45
        );

}


.ch-comment-body strong {

    display:
        block;

    margin-bottom:
        2px;

    color:
        #bfdbfe;

    font-size:
        7px;

}


.ch-comment-body p {

    margin:
        0;

    color:
        #cbd5e1;

    font-size:
        8px;

    line-height:
        1.45;

    word-break:
        break-word;

}


.ch-comment-form {

    display:
        flex;

    align-items:
        center;

    gap:
        6px;

}


.ch-comment-form input {

    flex:
        1;

    min-width:
        0;

    height:
        34px;

    padding:
        0
        9px;

    border:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .09
        );

    border-radius:
        9px;

    outline:
        none;

    color:
        #e2e8f0;

    background:
        rgba(
            2,
            6,
            23,
            .48
        );

    font:
        inherit;

    font-size:
        8px;

}


.ch-comment-form input:focus {

    border-color:
        rgba(
            96,
            165,
            250,
            .38
        );

}


.ch-comment-form button {

    width:
        34px;

    height:
        34px;

    border:
        0;

    border-radius:
        9px;

    color:
        #fff;

    background:
        #2563eb;

    cursor:
        pointer;

}


/* ============================================================
   DELETE
============================================================ */

.ch-delete-area {

    padding:
        0
        10px
        10px;

}


.ch-delete {

    padding:
        5px
        8px;

    border:
        1px
        solid
        rgba(
            248,
            113,
            113,
            .12
        );

    border-radius:
        7px;

    color:
        #fca5a5;

    background:
        rgba(
            127,
            29,
            29,
            .10
        );

    font-size:
        7px;

    font-weight:
        800;

    cursor:
        pointer;

}


/* ============================================================
   PROFILE SIDEBAR
============================================================ */

.ch-profile-card {

    overflow:
        hidden;

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
        18px;

    background:
        rgba(
            3,
            12,
            28,
            .74
        );

}


.ch-cover {

    position:
        relative;

    height:
        72px;

    background:

        linear-gradient(
            135deg,
            #0c3477,
            #312e81,
            #111827
        );

}


.ch-cover-glow {

    position:
        absolute;

    width:
        180px;

    height:
        180px;

    right:
        -60px;

    top:
        -90px;

    border-radius:
        50%;

    background:
        radial-gradient(
            circle,
            rgba(
                96,
                165,
                250,
                .27
            ),
            transparent
            70%
        );

}


.ch-profile-body {

    padding:
        0
        13px
        14px;

    text-align:
        center;

}


.ch-side-avatar {

    margin-top:
        -31px;

}


.ch-side-avatar img,
.ch-side-avatar-img {

    width:
        63px;

    height:
        63px;

    display:
        grid;

    place-items:
        center;

    margin:
        0
        auto;

    object-fit:
        cover;

    border:
        3px
        solid
        #091632;

    border-radius:
        19px;

    box-shadow:
        0
        0
        0
        2px
        rgba(
            96,
            165,
            250,
            .38
        );

}


.ch-side-avatar-img {

    color:
        #fff;

    font-size:
        20px;

}


.ch-profile-body h3 {

    margin:
        8px
        0
        2px;

    color:
        #f8fafc;

    font-size:
        13px;

    font-weight:
        950;

}


.ch-profile-body > span {

    color:
        #67e8f9;

    font-size:
        6px;

    font-weight:
        950;

    letter-spacing:
        1px;

}


.ch-side-stats {

    display:
        grid;

    grid-template-columns:
        repeat(
            3,
            1fr
        );

    gap:
        5px;

    margin:
        11px
        0;

}


.ch-side-stats div {

    padding:
        7px
        4px;

    border:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .07
        );

    border-radius:
        8px;

    background:
        rgba(
            15,
            23,
            42,
            .44
        );

}


.ch-side-stats strong {

    display:
        block;

    color:
        #f8fafc;

    font-size:
        10px;

}


.ch-side-stats small {

    display:
        block;

    margin-top:
        2px;

    color:
        #64748b;

    font-size:
        6px;

}


.ch-view-profile {

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    min-height:
        34px;

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
        #dbeafe;

    background:
        rgba(
            37,
            99,
            235,
            .10
        );

    text-decoration:
        none;

    font-size:
        8px;

    font-weight:
        900;

}


/* ============================================================
   SIDE PANEL
============================================================ */

.ch-side-panel {

    padding:
        12px;

    border:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .09
        );

    border-radius:
        17px;

    background:
        rgba(
            3,
            12,
            28,
            .72
        );

}


.ch-side-title {

    margin-bottom:
        7px;

    color:
        #f8fafc;

    font-size:
        9px;

    font-weight:
        950;

}


.ch-side-link {

    display:
        grid;

    grid-template-columns:
        34px
        minmax(
            0,
            1fr
        )
        14px;

    align-items:
        center;

    gap:
        7px;

    padding:
        7px
        0;

    border-bottom:
        1px
        solid
        rgba(
            148,
            163,
            184,
            .06
        );

    color:
        #fff;

    text-decoration:
        none;

}


.ch-side-link:last-child {

    border-bottom:
        0;

}


.ch-side-link > span {

    width:
        34px;

    height:
        34px;

    display:
        grid;

    place-items:
        center;

    border-radius:
        9px;

    font-size:
        14px;

}


.ch-side-link > span.blue {

    background:
        rgba(
            59,
            130,
            246,
            .14
        );

}


.ch-side-link > span.purple {

    background:
        rgba(
            124,
            58,
            237,
            .14
        );

}


.ch-side-link > span.cyan {

    background:
        rgba(
            6,
            182,
            212,
            .13
        );

}


.ch-side-link > span.green {

    background:
        rgba(
            34,
            197,
            94,
            .13
        );

}


.ch-side-link strong {

    display:
        block;

    color:
        #e2e8f0;

    font-size:
        8px;

}


.ch-side-link small {

    display:
        block;

    margin-top:
        2px;

    color:
        #64748b;

    font-size:
        6px;

}


.ch-side-link > b {

    color:
        #60a5fa;

}


/* ============================================================
   ARCADE
============================================================ */

.ch-arcade {

    position:
        relative;

    overflow:
        hidden;

    padding:
        15px;

    border:
        1px
        solid
        rgba(
            96,
            165,
            250,
            .17
        );

    border-radius:
        17px;

    background:

        linear-gradient(
            135deg,
            rgba(
                8,
                35,
                77,
                .94
            ),
            rgba(
                48,
                46,
                129,
                .82
            ),
            rgba(
                3,
                12,
                28,
                .96
            )
        );

}


.ch-arcade-glow {

    position:
        absolute;

    width:
        190px;

    height:
        190px;

    right:
        -80px;

    top:
        -85px;

    border-radius:
        50%;

    background:
        radial-gradient(
            circle,
            rgba(
                34,
                211,
                238,
                .23
            ),
            transparent
            70%
        );

}


.ch-arcade-top {

    position:
        relative;

    z-index:
        2;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    color:
        #67e8f9;

    font-size:
        6px;

    font-weight:
        950;

    letter-spacing:
        1.1px;

}


.ch-arcade-top b {

    font-size:
        18px;

}


.ch-arcade h3 {

    position:
        relative;

    z-index:
        2;

    margin:
        10px
        0
        5px;

    color:
        #fff;

    font-size:
        16px;

}


.ch-arcade p {

    position:
        relative;

    z-index:
        2;

    margin:
        0;

    color:
        #94a3b8;

    font-size:
        8px;

    line-height:
        1.6;

}


.ch-arcade-icons {

    position:
        relative;

    z-index:
        2;

    display:
        flex;

    gap:
        5px;

    margin:
        10px
        0;

}


.ch-arcade-icons span {

    width:
        29px;

    height:
        29px;

    display:
        grid;

    place-items:
        center;

    border:
        1px
        solid
        rgba(
            255,
            255,
            255,
            .08
        );

    border-radius:
        8px;

    background:
        rgba(
            255,
            255,
            255,
            .07
        );

}


.ch-arcade > a {

    position:
        relative;

    z-index:
        2;

    display:
        inline-flex;

    align-items:
        center;

    min-height:
        31px;

    padding:
        0
        10px;

    border:
        1px
        solid
        rgba(
            255,
            255,
            255,
            .10
        );

    border-radius:
        9px;

    color:
        #fff;

    background:
        rgba(
            255,
            255,
            255,
            .08
        );

    text-decoration:
        none;

    font-size:
        7px;

    font-weight:
        900;

}


/* ============================================================
   SECURITY
============================================================ */

.ch-security {

    display:
        flex;

    align-items:
        flex-start;

    gap:
        7px;

}


.ch-security > div {

    width:
        29px;

    height:
        29px;

    display:
        grid;

    place-items:
        center;

    flex:
        0
        0
        29px;

    border-radius:
        8px;

    background:
        rgba(
            59,
            130,
            246,
            .10
        );

}


.ch-security p {

    margin:
        0;

    color:
        #64748b;

    font-size:
        7px;

    line-height:
        1.5;

}


/* ============================================================
   EMPTY FEED
============================================================ */

.ch-empty {

    padding:
        47px
        20px;

    text-align:
        center;

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
        17px;

    background:
        rgba(
            3,
            12,
            28,
            .70
        );

}


.ch-empty-icon {

    position:
        relative;

    width:
        84px;

    height:
        84px;

    margin:
        0
        auto
        14px;

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
        50%;

    animation:
        emptyPulse
        2.4s
        ease-in-out
        infinite;

}


.ch-empty-icon span {

    width:
        40px;

    height:
        40px;

    display:
        grid;

    place-items:
        center;

    border-radius:
        50%;

    color:
        #fff;

    background:

        linear-gradient(
            135deg,
            #2563eb,
            #7c3aed
        );

}


@keyframes emptyPulse {

    0%,
    100% {
        transform:
            scale(1);

        box-shadow:
            0
            0
            0
            rgba(
                59,
                130,
                246,
                0
            );
    }

    50% {
        transform:
            scale(1.06);

        box-shadow:
            0
            0
            30px
            rgba(
                59,
                130,
                246,
                .15
            );
    }

}


.ch-empty > small {

    color:
        #67e8f9;

    font-size:
        7px;

    font-weight:
        950;

    letter-spacing:
        1.4px;

}


.ch-empty h2 {

    margin:
        7px
        0;

    color:
        #f8fafc;

    font-size:
        20px;

}


.ch-empty p {

    max-width:
        460px;

    margin:
        0
        auto;

    color:
        #64748b;

    font-size:
        8px;

    line-height:
        1.65;

}


.ch-empty-buttons {

    display:
        flex;

    justify-content:
        center;

    gap:
        7px;

    flex-wrap:
        wrap;

    margin-top:
        14px;

}


.ch-empty-button {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    min-height:
        34px;

    padding:
        0
        11px;

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
        #dbeafe;

    background:
        rgba(
            15,
            23,
            42,
            .50
        );

    text-decoration:
        none;

    font-size:
        8px;

    font-weight:
        900;

}


.ch-empty-button.primary {

    color:
        #fff;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

}


/* ============================================================
   LIGHTBOX
============================================================ */

.ch-lightbox {

    position:
        fixed;

    inset:
        0;

    z-index:
        999999;

    display:
        none;

    align-items:
        center;

    justify-content:
        center;

    padding:
        25px;

    background:
        rgba(
            2,
            6,
            23,
            .93
        );

    backdrop-filter:
        blur(
            12px
        );

}


.ch-lightbox.open {

    display:
        flex;

}


.ch-lightbox img {

    max-width:
        95vw;

    max-height:
        90vh;

    object-fit:
        contain;

    border-radius:
        14px;

    box-shadow:
        0
        30px
        100px
        rgba(
            0,
            0,
            0,
            .60
        );

}


.ch-lightbox > button {

    position:
        absolute;

    right:
        17px;

    top:
        17px;

    width:
        42px;

    height:
        42px;

    border:
        1px
        solid
        rgba(
            255,
            255,
            255,
            .11
        );

    border-radius:
        50%;

    color:
        #fff;

    background:
        rgba(
            15,
            23,
            42,
            .75
        );

    font-size:
        23px;

    cursor:
        pointer;

}


/* ============================================================
   RESPONSIVE
============================================================ */

@media (
    max-width:1080px
) {

    .ch-columns {

        grid-template-columns:
            1fr;

    }


    .ch-sidebar {

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

        align-items:
            start;

    }


    .ch-profile-card {

        grid-row:
            span 2;

    }

}


@media (
    max-width:850px
) {

    .ch-home {

        padding:
            17px
            12px
            55px;

    }


    .ch-hero {

        flex-direction:
            column;

        align-items:
            flex-start;

        padding:
            23px;

    }


    .ch-hero-buttons {

        justify-content:
            flex-start;

    }


    .ch-stat-grid {

        grid-template-columns:
            1fr
            1fr;

    }


    .ch-compose-bottom {

        flex-wrap:
            wrap;

    }


    .ch-formats {

        width:
            100%;

        margin-left:
            0;

        text-align:
            left;

    }

}


@media (
    max-width:650px
) {

    .ch-home {

        padding:
            10px
            8px
            42px;

    }


    .ch-hero {

        padding:
            19px;

        border-radius:
            17px;

    }


    .ch-hero-main {

        align-items:
            flex-start;

    }


    .ch-avatar-wrap {

        flex-basis:
            60px;

    }


    .ch-main-avatar {

        width:
            60px;

        height:
            60px;

        border-radius:
            18px;

    }


    .ch-hero-copy h1 {

        font-size:
            21px;

    }


    .ch-hero-copy p {

        font-size:
            9px;

    }


    .ch-hero-buttons {

        width:
            100%;

        display:
            grid;

        grid-template-columns:
            1fr
            1fr;

    }


    .ch-button {

        width:
            100%;

    }


    .ch-stat-grid {

        grid-template-columns:
            1fr
            1fr;

    }


    .ch-quick-bar {

        display:
            grid;

        grid-template-columns:
            1fr
            1fr;

    }


    .ch-quick {

        justify-content:
            center;

    }


    .ch-sidebar {

        display:
            flex;

    }


    .ch-composer {

        padding:
            13px;

        border-radius:
            15px;

    }


    .ch-compose-bottom {

        flex-direction:
            column;

        align-items:
            stretch;

    }


    .ch-upload,
    .ch-publish {

        width:
            100%;

        justify-content:
            center;

    }


    .ch-formats {

        text-align:
            center;

    }


    .ch-preview {

        margin-left:
            0;

    }


    .ch-feed-heading {

        align-items:
            flex-start;

        flex-direction:
            column;

    }


    .ch-post-header {

        padding:
            10px;

    }


    .ch-actions {

        padding:
            7px;

    }


    .ch-action {

        padding:
            0
            7px;

    }


    .ch-share-form {

        grid-template-columns:
            1fr;

    }


    .ch-share-form button {

        width:
            100%;

    }

}


@media (
    max-width:430px
) {

    .ch-stat-grid {

        grid-template-columns:
            1fr;

    }


    .ch-hero-buttons {

        grid-template-columns:
            1fr;

    }

}

</style>


<script>

/* ============================================================
   MEDIA PREVIEW
============================================================ */

(function () {

    const input =
        document.getElementById(
            "chMediaInput"
        );


    const preview =
        document.getElementById(
            "chPreview"
        );


    const image =
        document.getElementById(
            "chImagePreview"
        );


    const video =
        document.getElementById(
            "chVideoPreview"
        );


    const name =
        document.getElementById(
            "chPreviewName"
        );


    const remove =
        document.getElementById(
            "chPreviewRemove"
        );


    if (
        !input ||
        !preview
    ) {
        return;
    }


    let objectUrl = "";


    function clearPreview() {

        input.value =
            "";


        preview.classList.remove(
            "show"
        );


        image.classList.remove(
            "active"
        );


        video.classList.remove(
            "active"
        );


        image.removeAttribute(
            "src"
        );


        video.removeAttribute(
            "src"
        );


        video.pause();


        name.textContent =
            "";


        if (
            objectUrl
        ) {

            URL.revokeObjectURL(
                objectUrl
            );

            objectUrl =
                "";

        }

    }


    input.addEventListener(
        "change",
        function () {

            clearPreview();


            const file =
                input.files &&
                input.files[0];


            if (
                !file
            ) {
                return;
            }


            objectUrl =
                URL.createObjectURL(
                    file
                );


            preview.classList.add(
                "show"
            );


            name.textContent =
                file.name;


            if (
                file.type.startsWith(
                    "image/"
                )
            ) {

                image.src =
                    objectUrl;

                image.classList.add(
                    "active"
                );

            } else if (
                file.type.startsWith(
                    "video/"
                )
            ) {

                video.src =
                    objectUrl;

                video.classList.add(
                    "active"
                );

            } else {

                clearPreview();

            }

        }
    );


    remove.addEventListener(
        "click",
        clearPreview
    );


})();


/* ============================================================
   SHARE PANELS
============================================================ */

document.addEventListener(
    "click",
    function (event) {

        const shareButton =
            event.target.closest(
                ".ch-share-button"
            );


        const closeButton =
            event.target.closest(
                ".ch-share-close"
            );


        if (
            shareButton
        ) {

            const target =
                shareButton.getAttribute(
                    "data-target"
                );


            const panel =
                document.getElementById(
                    target
                );


            if (
                panel
            ) {

                panel.classList.toggle(
                    "open"
                );

            }


            return;

        }


        if (
            closeButton
        ) {

            const target =
                closeButton.getAttribute(
                    "data-target"
                );


            const panel =
                document.getElementById(
                    target
                );


            if (
                panel
            ) {

                panel.classList.remove(
                    "open"
                );

            }

        }

    }
);


/* ============================================================
   COMMENT BUTTONS
============================================================ */

document.addEventListener(
    "click",
    function (event) {

        const button =
            event.target.closest(
                ".ch-comment-button"
            );


        if (
            !button
        ) {
            return;
        }


        const inputId =
            button.getAttribute(
                "data-input"
            );


        const input =
            document.getElementById(
                inputId
            );


        if (
            !input
        ) {
            return;
        }


        input.focus();


        input.scrollIntoView(
            {
                behavior:
                    "smooth",

                block:
                    "center"
            }
        );

    }
);


/* ============================================================
   IMAGE LIGHTBOX
============================================================ */

(function () {

    const lightbox =
        document.getElementById(
            "chLightbox"
        );


    const image =
        document.getElementById(
            "chLightboxImage"
        );


    const closeButton =
        document.getElementById(
            "chLightboxClose"
        );


    if (
        !lightbox ||
        !image ||
        !closeButton
    ) {
        return;
    }


    function closeLightbox() {

        lightbox.classList.remove(
            "open"
        );


        image.removeAttribute(
            "src"
        );

    }


    document.addEventListener(
        "click",
        function (event) {

            const button =
                event.target.closest(
                    ".ch-image-button"
                );


            if (
                !button
            ) {
                return;
            }


            const src =
                button.getAttribute(
                    "data-image"
                );


            if (
                !src
            ) {
                return;
            }


            image.src =
                src;


            lightbox.classList.add(
                "open"
            );

        }
    );


    closeButton.addEventListener(
        "click",
        closeLightbox
    );


    lightbox.addEventListener(
        "click",
        function (event) {

            if (
                event.target ===
                lightbox
            ) {

                closeLightbox();

            }

        }
    );


    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key ===
                "Escape"
            ) {

                closeLightbox();

            }

        }
    );


})();


/* ============================================================
   SCROLL POSITION
============================================================ */

(function () {

    const key =
        "connecthub_home_scroll";


    function saveScroll() {

        try {

            sessionStorage.setItem(
                key,
                String(
                    window.scrollY ||
                    window.pageYOffset ||
                    0
                )
            );

        } catch (
            e
        ) {}

    }


    document.addEventListener(
        "submit",
        saveScroll,
        true
    );


    function restoreScroll() {

        let value =
            null;


        try {

            value =
                sessionStorage.getItem(
                    key
                );

        } catch (
            e
        ) {

            return;

        }


        if (
            value ===
            null
        ) {

            return;

        }


        const position =
            parseInt(
                value,
                10
            );


        if (
            Number.isNaN(
                position
            )
        ) {

            return;

        }


        setTimeout(
            function () {

                window.scrollTo(
                    0,
                    position
                );

            },
            80
        );


        setTimeout(
            function () {

                try {

                    sessionStorage.removeItem(
                        key
                    );

                } catch (
                    e
                ) {}

            },
            700
        );

    }


    window.addEventListener(
        "pageshow",
        restoreScroll
    );


})();

</script>


<?php

// ============================================================
// CLOSE FEED STATEMENT AFTER ALL POSTS HAVE BEEN RENDERED
// ============================================================

if (
    isset($postsStatement) &&
    $postsStatement instanceof mysqli_stmt
) {

    $postsStatement->close();

}

?>


<?php
require "footer.php";
?>