<?php
require_once 'config.php';

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

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start a transaction
        $pdo->beginTransaction();
        
        // Insert brand
        $stmt = $pdo->prepare("
            INSERT INTO brands (name, description, price_range, country_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['price_range'],
            $_POST['country_id'] ?: null
        ]);
        
        // Get the new brand ID
        $brandId = $pdo->lastInsertId();
        
        // Insert categories
        if (!empty($_POST['categories'])) {
            $categoryInsert = $pdo->prepare("
                INSERT INTO brand_categories (brand_id, category_id)
                VALUES (?, ?)
            ");
            
            foreach ($_POST['categories'] as $categoryId) {
                $categoryInsert->execute([$brandId, $categoryId]);
            }
        }
        
        // Insert materials
        if (!empty($_POST['materials'])) {
            $materialInsert = $pdo->prepare("
                INSERT INTO brand_materials (brand_id, material_id)
                VALUES (?, ?)
            ");
            
            foreach ($_POST['materials'] as $materialId) {
                $materialInsert->execute([$brandId, $materialId]);
            }
        }
        
        // Insert styles
        if (!empty($_POST['styles'])) {
            $styleInsert = $pdo->prepare("
                INSERT INTO brand_styles (brand_id, style_id)
                VALUES (?, ?)
            ");
            
            foreach ($_POST['styles'] as $styleId) {
                $styleInsert->execute([$brandId, $styleId]);
            }
        }
        
        // Insert link if provided
        if (!empty($_POST['link_title']) && !empty($_POST['link_url'])) {
            $linkInsert = $pdo->prepare("
                INSERT INTO brand_links (brand_id, title, url)
                VALUES (?, ?, ?)
            ");
            $linkInsert->execute([$brandId, $_POST['link_title'], $_POST['link_url']]);
        }
        
        // Commit the transaction
        $pdo->commit();
        
        $message = 'Brand added successfully!';
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
    <title>Add New Brand - Quality Clothing Database</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <div class="container">
        <h1>Add New Brand</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Brand Name*</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price_range">Price Range*</label>
                    <select id="price_range" name="price_range" class="form-control" required>
                        <option value="">Select price range</option>
                        <option value="$">$ (Budget)</option>
                        <option value="$$">$$ (Mid-range)</option>
                        <option value="$$$">$$$ (Premium)</option>
                        <option value="$$$$">$$$$ (Luxury)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="country_id">Country of Origin</label>
                    <select id="country_id" name="country_id" class="form-control">
                        <option value="">Select country</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?php echo $country['id']; ?>">
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
                                <input type="checkbox" name="styles[]" value="<?php echo $style['id']; ?>">
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
                                <input type="checkbox" name="categories[]" value="<?php echo $category['id']; ?>">
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
                                <input type="checkbox" name="materials[]" value="<?php echo $material['id']; ?>">
                                <?php echo htmlspecialchars($material['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="link_title">Link Title (Optional)</label>
                    <input type="text" id="link_title" name="link_title" class="form-control" placeholder="e.g. Official Website">
                </div>
                
                <div class="form-group">
                    <label for="link_url">Link URL (Optional)</label>
                    <input type="url" id="link_url" name="link_url" class="form-control" placeholder="https://example.com">
                </div>
                
                <div class="button-group">
                    <a href="index.php" class="cancel-btn">Cancel</a>
                    <button type="submit" class="submit-btn">Add Brand</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="scripts.js"></script>
</body>
</html>