<?php
require_once 'config.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$brandId = (int)$_GET['id'];

// Get brand details for editing
$brand = getBrandDetails($pdo, $brandId);
if (!$brand) {
    header('Location: index.php');
    exit;
}

// Get all countries, categories, and materials for the form
$countries = getAllCountries($pdo);
$categories = getAllClothingCategories($pdo);
$materials = getAllMaterials($pdo);

// Define clothing styles
$clothingStyles = [
    ['id' => 1, 'name' => 'Heritage'],
    ['id' => 2, 'name' => 'Formal'],
    ['id' => 3, 'name' => 'Casual'],
    ['id' => 4, 'name' => 'Streetwear']
];

// Get selected categories and materials
$selectedCategories = array_map(function($category) {
    // Find the category ID from the name
    global $categories;
    foreach ($categories as $cat) {
        if ($cat['name'] === $category) {
            return $cat['id'];
        }
    }
    return null;
}, $brand['categories']);

$selectedMaterials = array_map(function($material) {
    // Find the material ID from the name
    global $materials;
    foreach ($materials as $mat) {
        if ($mat['name'] === $material) {
            return $mat['id'];
        }
    }
    return null;
}, $brand['materials']);

// Get selected styles (from brand_styles table)
$selectedStyles = [];
if (isset($brand['styles'])) {
    $selectedStyles = array_map(function($style) {
        global $clothingStyles;
        foreach ($clothingStyles as $s) {
            if ($s['name'] === $style) {
                return $s['id'];
            }
        }
        return null;
    }, $brand['styles']);
} else {
    // Query to get selected styles if not already in brand details
    $styleStmt = $pdo->prepare("SELECT style_id FROM brand_styles WHERE brand_id = ?");
    $styleStmt->execute([$brandId]);
    $selectedStyles = array_column($styleStmt->fetchAll(PDO::FETCH_ASSOC), 'style_id');
}

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start a transaction
        $pdo->beginTransaction();
        
        // Update brand
        $stmt = $pdo->prepare("
            UPDATE brands 
            SET name = ?, description = ?, price_range = ?, country_id = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['price_range'],
            $_POST['country_id'] ?: null,
            $brandId
        ]);
        
        // Update categories - first remove all existing ones
        $pdo->prepare("DELETE FROM brand_categories WHERE brand_id = ?")->execute([$brandId]);
        
        // Add new categories
        if (!empty($_POST['categories'])) {
            $categoryInsert = $pdo->prepare("
                INSERT INTO brand_categories (brand_id, category_id)
                VALUES (?, ?)
            ");
            
            foreach ($_POST['categories'] as $categoryId) {
                $categoryInsert->execute([$brandId, $categoryId]);
            }
        }
        
        // Update materials - first remove all existing ones
        $pdo->prepare("DELETE FROM brand_materials WHERE brand_id = ?")->execute([$brandId]);
        
        // Add new materials
        if (!empty($_POST['materials'])) {
            $materialInsert = $pdo->prepare("
                INSERT INTO brand_materials (brand_id, material_id)
                VALUES (?, ?)
            ");
            
            foreach ($_POST['materials'] as $materialId) {
                $materialInsert->execute([$brandId, $materialId]);
            }
        }
        
        // Update styles - first remove all existing ones
        $pdo->prepare("DELETE FROM brand_styles WHERE brand_id = ?")->execute([$brandId]);
        
        // Add new styles
        if (!empty($_POST['styles'])) {
            $styleInsert = $pdo->prepare("
                INSERT INTO brand_styles (brand_id, style_id)
                VALUES (?, ?)
            ");
            
            foreach ($_POST['styles'] as $styleId) {
                $styleInsert->execute([$brandId, $styleId]);
            }
        }
        
        // Update links - first remove all existing ones
        $pdo->prepare("DELETE FROM brand_links WHERE brand_id = ?")->execute([$brandId]);
        
        // Add new links
        if (!empty($_POST['link_titles']) && !empty($_POST['link_urls'])) {
            $linkInsert = $pdo->prepare("
                INSERT INTO brand_links (brand_id, title, url)
                VALUES (?, ?, ?)
            ");
            
            $linkTitles = $_POST['link_titles'];
            $linkUrls = $_POST['link_urls'];
            
            for ($i = 0; $i < count($linkTitles); $i++) {
                if (!empty($linkTitles[$i]) && !empty($linkUrls[$i])) {
                    $linkInsert->execute([$brandId, $linkTitles[$i], $linkUrls[$i]]);
                }
            }
        }
        
        // Commit the transaction
        $pdo->commit();
        
        // Refresh brand data
        $brand = getBrandDetails($pdo, $brandId);
        
        $message = 'Brand updated successfully!';
        $messageType = 'success';
    } catch (PDOException $e) {
        // Rollback the transaction in case of error
        $pdo->rollBack();
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Brand - Quality Clothing Database</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Edit Brand</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Brand Name*</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($brand['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control"><?php echo htmlspecialchars($brand['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price_range">Price Range*</label>
                    <select id="price_range" name="price_range" class="form-control" required>
                        <option value="">Select price range</option>
                        <option value="$" <?php if ($brand['price_range'] === '$') echo 'selected'; ?>>$ (Budget)</option>
                        <option value="$$" <?php if ($brand['price_range'] === '$$') echo 'selected'; ?>>$$ (Mid-range)</option>
                        <option value="$$$" <?php if ($brand['price_range'] === '$$$') echo 'selected'; ?>>$$$ (Premium)</option>
                        <option value="$$$$" <?php if ($brand['price_range'] === '$$$$') echo 'selected'; ?>>$$$$ (Luxury)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="country_id">Country of Origin</label>
                    <select id="country_id" name="country_id" class="form-control">
                        <option value="">Select country</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?php echo $country['id']; ?>" <?php if ($brand['country_id'] === $country['id']) echo 'selected'; ?>>
                                <?php echo $country['flag_emoji'] . ' ' . htmlspecialchars($country['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Clothing Style</label>
                    <div class="checkbox-group">
                        <?php foreach ($clothingStyles as $style): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="styles[]" value="<?php echo $style['id']; ?>" <?php if (in_array($style['id'], $selectedStyles)) echo 'checked'; ?>>
                                <?php echo htmlspecialchars($style['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Clothing Type</label>
                    <div class="checkbox-group">
                        <?php foreach ($categories as $category): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="categories[]" value="<?php echo $category['id']; ?>" <?php if (in_array($category['id'], $selectedCategories)) echo 'checked'; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Materials</label>
                    <div class="checkbox-group">
                        <?php foreach ($materials as $material): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="materials[]" value="<?php echo $material['id']; ?>" <?php if (in_array($material['id'], $selectedMaterials)) echo 'checked'; ?>>
                                <?php echo htmlspecialchars($material['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Links</label>
                    <div id="links-container">
                        <?php if (!empty($brand['links'])): ?>
                            <?php foreach ($brand['links'] as $index => $link): ?>
                                <div class="link-group">
                                    <button type="button" class="link-remove" onclick="removeLink(this)">×</button>
                                    <div class="form-group">
                                        <label for="link_titles[<?php echo $index; ?>]">Link Title</label>
                                        <input type="text" id="link_titles[<?php echo $index; ?>]" name="link_titles[]" class="form-control" value="<?php echo htmlspecialchars($link['title']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="link_urls[<?php echo $index; ?>]">Link URL</label>
                                        <input type="url" id="link_urls[<?php echo $index; ?>]" name="link_urls[]" class="form-control" value="<?php echo htmlspecialchars($link['url']); ?>" required>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" class="add-link-btn" onclick="addLinkField()">Add Link</button>
                </div>
                
                <div class="button-group">
                    <a href="index.php" class="cancel-btn">Cancel</a>
                    <button type="submit" class="submit-btn">Update Brand</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="scripts.js"></script>
</body>
</html>