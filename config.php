```php
<?php
// =====================================================
// CONNECTHUB - DATABASE CONFIGURATION
// =====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// =====================================================
// DATABASE CONNECTION
// =====================================================

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "connecthub"
);


if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}


$conn->set_charset("utf8mb4");


// =====================================================
// SAFE OUTPUT FUNCTION
// =====================================================

if (!function_exists("e")) {

    function e($value)
    {
        return htmlspecialchars(
            $value ?? "",
            ENT_QUOTES,
            "UTF-8"
        );
    }
}


// =====================================================
// LOGIN REQUIRED
// =====================================================

if (!function_exists("login_required")) {

    function login_required()
    {
        if (
            !isset($_SESSION["user_id"]) ||
            (int)$_SESSION["user_id"] <= 0
        ) {

            header("Location: login.php");

            exit;
        }
    }
}


// =====================================================
// OPTIONAL: CURRENT USER ID
// =====================================================

if (!function_exists("current_user_id")) {

    function current_user_id()
    {
        return (int)(
            $_SESSION["user_id"] ?? 0
        );
    }
}


// =====================================================
// OPTIONAL: CURRENT USER NAME
// =====================================================

if (!function_exists("current_user_name")) {

    function current_user_name()
    {
        return $_SESSION["name"] ?? "";
    }
}


// =====================================================
// IMAGE RESOLUTION HELPERS (RULE 34 & 35)
// Prevents duplicate uploads/uploads/ and resolves paths
// =====================================================

if (!function_exists("image_url")) {

    function image_url($path, string $default = ""): string
    {
        $raw = trim((string)$path, " \t\n\r\0\x0B");
        $raw = str_replace(["\r", "\n", "\\r", "\\n"], "", $raw);
        $raw = trim($raw);
        if ($raw === "") {
            return $default;
        }

        // Keep remote URLs or data URIs intact
        if (preg_match('~^(https?:)?//|^data:~i', $raw)) {
            return $raw;
        }

        // Clean redundant project prefixes and separators
        $clean = str_replace("\\", "/", $raw);
        $clean = preg_replace('~^/*(connecthub/|gconnecthub/)+~i', '', $clean);
        $clean = ltrim($clean, '/');

        // Prevent duplicate uploads/ repetition
        while (preg_match('~^uploads/+uploads/+~i', $clean)) {
            $clean = preg_replace('~^uploads/+~i', '', $clean);
        }

        $base = basename($clean);
        $candidates = [
            $clean,
            "uploads/" . $clean,
            "uploads/" . $base,
            "products/" . $base,
            $base
        ];

        foreach ($candidates as $cand) {
            $full = __DIR__ . "/" . str_replace("/", DIRECTORY_SEPARATOR, $cand);
            if (is_file($full)) {
                return $cand;
            }
        }

        if (!preg_match('~^(uploads/|products/|images/)~i', $clean)) {
            return "uploads/" . $clean;
        }

        return $clean;
    }
}


if (!function_exists("product_image_url")) {

    function product_image_url($path, string $default = ""): string
    {
        $raw = trim((string)$path, " \t\n\r\0\x0B");
        $raw = str_replace(["\r", "\n", "\\r", "\\n"], "", $raw);
        $raw = trim($raw);
        if ($raw === "") {
            return $default;
        }

        if (preg_match('~^(https?:)?//|^data:~i', $raw)) {
            return $raw;
        }

        $clean = str_replace("\\", "/", $raw);
        $clean = preg_replace('~^/*(connecthub/|gconnecthub/)+~i', '', $clean);
        $clean = ltrim($clean, '/');
        $base = basename($clean);

        $candidates = [
            "products/" . $base,
            "uploads/products/" . $base,
            "uploads/" . $base,
            "products/" . $clean,
            $clean
        ];

        foreach ($candidates as $cand) {
            $full = __DIR__ . "/" . str_replace("/", DIRECTORY_SEPARATOR, $cand);
            if (is_file($full)) {
                return $cand;
            }
        }

        return "products/" . $base;
    }
}


if (!function_exists("profile_image_url")) {

    function profile_image_url($path): string
    {
        $raw = trim((string)$path);
        if ($raw === "" || strtolower($raw) === "default.png") {
            if (is_file(__DIR__ . "/uploads/profiles/default.png")) {
                return "uploads/profiles/default.png";
            }
            if (is_file(__DIR__ . "/uploads/default.png")) {
                return "uploads/default.png";
            }
            return "";
        }

        if (preg_match('~^(https?:)?//|^data:~i', $raw)) {
            return $raw;
        }

        $clean = str_replace("\\", "/", $raw);
        $clean = preg_replace('~^/*(connecthub/|gconnecthub/)+~i', '', $clean);
        $clean = ltrim($clean, '/');
        $base = basename($clean);

        $candidates = [
            "uploads/" . $clean,
            "uploads/profiles/" . $base,
            "uploads/" . $base,
            $clean
        ];

        foreach ($candidates as $cand) {
            $full = __DIR__ . "/" . str_replace("/", DIRECTORY_SEPARATOR, $cand);
            if (is_file($full)) {
                return $cand;
            }
        }

        return "uploads/" . $base;
    }
}

?>
```
