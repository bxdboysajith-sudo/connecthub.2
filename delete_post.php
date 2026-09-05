<?php

require "config.php";

login_required();

$uid = $_SESSION["user_id"];

$post_id = intval($_GET["id"] ?? 0);


/* Get post belonging to logged-in user */

$stmt = $conn->prepare("
    SELECT image
    FROM posts
    WHERE id = ?
    AND user_id = ?
");

$stmt->bind_param("ii", $post_id, $uid);

$stmt->execute();

$post = $stmt->get_result()->fetch_assoc();


if (!$post) {
    die("Post not found or you don't have permission to delete it.");
}


/* Delete database record */

$stmt = $conn->prepare("
    DELETE FROM posts
    WHERE id = ?
    AND user_id = ?
");

$stmt->bind_param("ii", $post_id, $uid);

$stmt->execute();


/* Delete image from uploads folder */

if (!empty($post["image"])) {

    $image_path = "uploads/" . $post["image"];

    if (file_exists($image_path)) {
        unlink($image_path);
    }
}


/* Return to profile */

header("Location: profile.php");
exit;

?>