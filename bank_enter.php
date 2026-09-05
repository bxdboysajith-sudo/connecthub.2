<?php
// =====================================================
// CONNECTHUB - BANKING ENTRY
// =====================================================

require "config.php";

// Always remove previous Banking verification
unset($_SESSION["bank_unlocked"]);
unset($_SESSION["bank_page_verified"]);

// Open Banking page
header("Location: bank.php");
exit;