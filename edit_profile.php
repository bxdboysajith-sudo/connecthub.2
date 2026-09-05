<?php
// ============================================================
// CONNECTHUB - EDIT PROFILE
// HIGH-TECH PROFILE EDITOR
// POST HANDLING BEFORE HEADER.PHP (RULE 6)
// ============================================================

require "config.php";

login_required();

$uid = (int)($_SESSION["user_id"] ?? 0);

if ($uid <= 0) {
    header("Location: login.php");
    exit;
}

$message = "";
$error = "";

/* GET CURRENT USER */
$stmt = $conn->prepare("
    SELECT id, name, email, profile_image
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: logout.php");
    exit;
}

/* UPDATE PROFILE - BEFORE HEADER.PHP */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_profile"])) {

    $name = trim((string)($_POST["name"] ?? ""));
    $email = trim((string)($_POST["email"] ?? ""));

    if ($name === "" || $email === "") {
        $error = "Name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {

        // Check if another user has this email
        $stmt = $conn->prepare("
            SELECT id FROM users
            WHERE email = ? AND id != ?
            LIMIT 1
        ");
        $stmt->bind_param("si", $email, $uid);
        $stmt->execute();
        $emailConflict = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($emailConflict) {
            $error = "This email address is already in use by another account.";
        } else {
            $profile_image = $user["profile_image"];

            /* PROFILE IMAGE UPLOAD */
            if (
                isset($_FILES["profile_image"]) &&
                $_FILES["profile_image"]["error"] === UPLOAD_ERR_OK
            ) {
                $allowed = ["jpg", "jpeg", "png", "webp", "gif"];
                $originalExt = strtolower(
                    pathinfo(
                        $_FILES["profile_image"]["name"],
                        PATHINFO_EXTENSION
                    )
                );

                if (in_array($originalExt, $allowed, true)) {
                    $upload_folder = __DIR__ . "/uploads/";
                    if (!is_dir($upload_folder)) {
                        mkdir($upload_folder, 0777, true);
                    }

                    $new_name = "profile_" . $uid . "_" . time() . "_" . bin2hex(random_bytes(3)) . "." . $originalExt;
                    $destination = $upload_folder . $new_name;

                    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $destination)) {
                        $profile_image = $new_name;
                    } else {
                        $error = "Image upload failed. Please try again.";
                    }
                } else {
                    $error = "Only JPG, JPEG, PNG, GIF, and WEBP images are allowed.";
                }
            }

            if ($error === "") {
                $stmt = $conn->prepare("
                    UPDATE users
                    SET name = ?,
                        email = ?,
                        profile_image = ?
                    WHERE id = ?
                ");

                $stmt->bind_param("sssi", $name, $email, $profile_image, $uid);

                if ($stmt->execute()) {
                    $_SESSION["name"] = $name;
                    $message = "Profile updated successfully! ✅";
                    $user["name"] = $name;
                    $user["email"] = $email;
                    $user["profile_image"] = $profile_image;
                } else {
                    $error = "Could not update profile.";
                }
                $stmt->close();
            }
        }
    }
}

// ============================================================
// HEADER LOADED AFTER ACTIONS ARE FINISHED
// ============================================================
require "header.php";

$avatarSrc = profile_image_url($user["profile_image"] ?? "");
?>

<div class="edit-profile-shell">

    <div class="edit-profile-card">
        
        <div class="edit-header">
            <div class="edit-badge">IDENTITY DASHBOARD</div>
            <h1>✏️ Edit Profile</h1>
            <p>Customize your personal ConnectHub identity and credentials.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert-success">
                <span>✓</span>
                <div><?= e($message) ?></div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-error">
                <span>✕</span>
                <div><?= e($error) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="edit-form" autocomplete="off">
            
            <div class="avatar-preview-section">
                <div class="avatar-circle">
                    <?php if ($avatarSrc !== ""): ?>
                        <img id="avatarPreview" src="<?= e($avatarSrc) ?>" alt="Avatar">
                    <?php else: ?>
                        <div id="avatarFallback" class="avatar-fallback">
                            <?= e(strtoupper(substr($user["name"] ?? "U", 0, 1))) ?>
                        </div>
                    <?php endif; ?>
                    <label for="profileImageInput" class="avatar-upload-btn" title="Choose new photo">
                        📷
                    </label>
                </div>
                <div class="avatar-tip">
                    <span>Click camera icon to change profile photo</span>
                    <small>Supports JPG, PNG, WEBP, GIF (Max 10MB)</small>
                </div>
                <input
                    type="file"
                    id="profileImageInput"
                    name="profile_image"
                    accept=".jpg,.jpeg,.png,.webp,.gif"
                    style="display:none;"
                    onchange="previewProfileImage(this);"
                >
            </div>

            <div class="form-group">
                <label for="nameInput">FULL NAME</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input
                        type="text"
                        id="nameInput"
                        name="name"
                        value="<?= e($user["name"]) ?>"
                        placeholder="Your full name"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="emailInput">EMAIL ADDRESS</label>
                <div class="input-wrapper">
                    <span class="input-icon">✉️</span>
                    <input
                        type="email"
                        id="emailInput"
                        name="email"
                        value="<?= e($user["email"]) ?>"
                        placeholder="yourname@domain.com"
                        required
                    >
                </div>
                <small class="field-hint">Note: Your email is private and never shown to other users.</small>
            </div>

            <div class="form-actions">
                <button type="submit" name="update_profile" class="btn-save">
                    💾 Save Changes
                </button>
                <a href="profile.php" class="btn-cancel">
                    ✕ Cancel
                </a>
            </div>

        </form>

    </div>

</div>

<style>
.edit-profile-shell {
    width: 100%;
    max-width: 680px;
    margin: 30px auto 60px;
    padding: 0 16px;
}

.edit-profile-card {
    background: rgba(15, 23, 42, 0.88);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(99, 102, 241, 0.22);
    border-radius: 24px;
    padding: 36px 32px;
    box-shadow: 0 20px 50px rgba(2, 6, 23, 0.55), 0 0 35px rgba(79, 70, 229, 0.12);
    color: #f8fafc;
}

.edit-header {
    text-align: center;
    margin-bottom: 28px;
}

.edit-badge {
    display: inline-block;
    padding: 5px 14px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.5px;
    color: #818cf8;
    background: rgba(99, 102, 241, 0.12);
    border: 1px solid rgba(99, 102, 241, 0.25);
    margin-bottom: 10px;
}

.edit-header h1 {
    font-size: 28px;
    margin: 6px 0;
    color: #ffffff;
}

.edit-header p {
    color: #94a3b8;
    font-size: 14px;
    margin: 0;
}

.alert-success {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 12px;
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.3);
    color: #34d399;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-error {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 12px;
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #f87171;
    margin-bottom: 20px;
    font-size: 14px;
}

.avatar-preview-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 28px;
}

.avatar-circle {
    position: relative;
    width: 130px;
    height: 130px;
    border-radius: 50%;
    padding: 4px;
    background: linear-gradient(135deg, #4f46e5, #06b6d4);
    box-shadow: 0 0 25px rgba(99, 102, 241, 0.35);
}

.avatar-circle img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    display: block;
    background: #0f172a;
}

.avatar-fallback {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: #1e293b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    font-weight: 800;
    color: #818cf8;
}

.avatar-upload-btn {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #4f46e5;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 18px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    border: 2px solid #0f172a;
    transition: transform .2s ease, background .2s ease;
}

.avatar-upload-btn:hover {
    transform: scale(1.1);
    background: #6366f1;
}

.avatar-tip {
    text-align: center;
    margin-top: 12px;
}

.avatar-tip span {
    display: block;
    font-size: 13px;
    color: #cbd5e1;
}

.avatar-tip small {
    color: #64748b;
    font-size: 11px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    color: #94a3b8;
    margin-bottom: 8px;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 14px;
    font-size: 16px;
    color: #64748b;
    pointer-events: none;
}

.input-wrapper input {
    width: 100%;
    padding: 13px 14px 13px 44px;
    border-radius: 12px;
    border: 1px solid rgba(148, 163, 184, 0.2);
    background: rgba(2, 6, 23, 0.7);
    color: #ffffff;
    font-size: 15px;
    outline: none;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.input-wrapper input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 16px rgba(99, 102, 241, 0.25);
}

.field-hint {
    display: block;
    margin-top: 6px;
    color: #64748b;
    font-size: 11px;
}

.form-actions {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-top: 30px;
}

.btn-save {
    flex: 1;
    padding: 14px 20px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    color: #ffffff;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(79, 70, 229, 0.35);
    transition: transform .18s ease, filter .18s ease;
}

.btn-save:hover {
    transform: translateY(-2px);
    filter: brightness(1.1);
}

.btn-cancel {
    padding: 14px 22px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #cbd5e1;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: background .2s ease, color .2s ease;
}

.btn-cancel:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
}
</style>

<script>
function previewProfileImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let img = document.getElementById('avatarPreview');
            const fallback = document.getElementById('avatarFallback');
            if (!img && fallback) {
                img = document.createElement('img');
                img.id = 'avatarPreview';
                fallback.parentNode.insertBefore(img, fallback);
                fallback.remove();
            }
            if (img) {
                img.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require "footer.php"; ?>