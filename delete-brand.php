<?php
// Start session at the very beginning
session_start();

require_once 'config.php';
require_once 'auth_functions.php';

// Protect this page - only allow admin access
requireAdminLogin();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$brandId = (int)$_GET['id'];

// Verify brand exists
$stmt = $pdo->prepare("SELECT id FROM brands WHERE id = ?");
$stmt->execute([$brandId]);
if (!$stmt->fetch()) {
    header('Location: index.php');
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Delete related records first (brand_links, brand_categories, brand_materials, brand_styles)
    $pdo->prepare("DELETE FROM brand_links WHERE brand_id = ?")->execute([$brandId]);
    $pdo->prepare("DELETE FROM brand_categories WHERE brand_id = ?")->execute([$brandId]);
    $pdo->prepare("DELETE FROM brand_materials WHERE brand_id = ?")->execute([$brandId]);
    $pdo->prepare("DELETE FROM brand_styles WHERE brand_id = ?")->execute([$brandId]);
    
    // Finally delete the brand
    $pdo->prepare("DELETE FROM brands WHERE id = ?")->execute([$brandId]);
    
    // Commit transaction
    $pdo->commit();
    
    // Redirect with success message
    header('Location: index.php?message=Brand+deleted+successfully');
} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    // Redirect with error message
    header('Location: index.php?error=' . urlencode('Error deleting brand: ' . $e->getMessage()));
}
exit;