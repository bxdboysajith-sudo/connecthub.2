<?php
// =====================================================
// CONNECTHUB - EXIT BANKING
// =====================================================

require "config.php";

// Remove Banking verification and unlock state
unset($_SESSION["bank_unlocked"]);
unset($_SESSION["bank_page_verified"]);

// Redirect to Home
header("Location: index.php");
exit;