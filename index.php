<?php
// Start session at the very beginning before any output
session_start();

// Include required files
require_once 'config.php';

// Skip admin check for this simplified version
$isAdmin = false;

// Process filter parameters - simplified
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get filtered brands - simplified
$stmt = $pdo->query("SELECT * FROM brands ORDER BY name ASC");
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Clothing Database (Simplified)</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <div class="header-container">
            <h1>Quality Clothing Database (Simplified)</h1>
            <a href="index.php" class="login-btn">Back to Full Version</a>
        </div>
        
        <form method="GET" action="" id="searchForm">
            <input type="text" name="search" placeholder="Search brands..." class="search-bar" value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <div class="brand-list">
            <?php if (count($brands) > 0): ?>
                <?php foreach ($brands as $brand): ?>
                    <div class="brand">
                        <div class="brand-header">
                            <span class="brand-name"><?php echo htmlspecialchars($brand['name']); ?></span>
                            <span class="brand-price"><?php echo htmlspecialchars($brand['price_range']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">No results were found</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
