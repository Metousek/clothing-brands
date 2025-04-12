<?php
require_once 'config.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$countryIds = isset($_GET['country']) ? (is_array($_GET['country']) ? $_GET['country'] : [$_GET['country']]) : [];
$priceRanges = isset($_GET['price']) ? (is_array($_GET['price']) ? $_GET['price'] : [$_GET['price']]) : [];
$categoryIds = isset($_GET['category']) ? (is_array($_GET['category']) ? $_GET['category'] : [$_GET['category']]) : [];
$styleIds = isset($_GET['style']) ? (is_array($_GET['style']) ? $_GET['style'] : [$_GET['style']]) : [];
$materialIds = isset($_GET['material']) ? (is_array($_GET['material']) ? $_GET['material'] : [$_GET['material']]) : [];

// Get data from database
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

$brands = filterBrands($pdo, $search, $countryIds, $priceRanges, $categoryIds, $materialIds, $styleIds);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Clothing Database</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <div class="container">
        <h1>Quality Clothing Database</h1>
        
        <form method="GET" action="" id="searchForm">
            <input type="text" name="search" placeholder="Search brands..." class="search-bar" value="<?php echo htmlspecialchars($search); ?>">
            
            <div class="active-filters" id="active-filters">
                <?php 
                // Displaying active country filters
                if (!empty($countryIds)) {
                    foreach ($countryIds as $id) {
                        foreach ($countries as $country) {
                            if ($country['id'] == $id) {
                                echo '<div class="filter-tag" data-type="country" data-id="' . $id . '">';
                                echo htmlspecialchars($country['name']);
                                echo '<button type="button" onclick="removeFilter(\'country\', \'' . $id . '\')">×</button>';
                                echo '</div>';
                                echo '<input type="hidden" name="country[]" value="' . $id . '">';
                            }
                        }
                    }
                }
                
                // Displaying active price filters
                if (!empty($priceRanges)) {
                    foreach ($priceRanges as $price) {
                        echo '<div class="filter-tag" data-type="price" data-id="' . htmlspecialchars($price) . '">';
                        echo htmlspecialchars($price);
                        echo '<button type="button" onclick="removeFilter(\'price\', \'' . htmlspecialchars($price) . '\')">×</button>';
                        echo '</div>';
                        echo '<input type="hidden" name="price[]" value="' . htmlspecialchars($price) . '">';
                    }
                }

                // Displaying active style filters
                if (!empty($styleIds)) {
                    foreach ($styleIds as $id) {
                        foreach ($clothingStyles as $style) {
                            if ($style['id'] == $id) {
                                echo '<div class="filter-tag" data-type="style" data-id="' . $id . '">';
                                echo htmlspecialchars($style['name']);
                                echo '<button type="button" onclick="removeFilter(\'style\', \'' . $id . '\')">×</button>';
                                echo '</div>';
                                echo '<input type="hidden" name="style[]" value="' . $id . '">';
                            }
                        }
                    }
                }
                
                // Displaying active category filters
                if (!empty($categoryIds)) {
                    foreach ($categoryIds as $id) {
                        foreach ($categories as $category) {
                            if ($category['id'] == $id) {
                                echo '<div class="filter-tag" data-type="category" data-id="' . $id . '">';
                                echo htmlspecialchars($category['name']);
                                echo '<button type="button" onclick="removeFilter(\'category\', \'' . $id . '\')">×</button>';
                                echo '</div>';
                                echo '<input type="hidden" name="category[]" value="' . $id . '">';
                            }
                        }
                    }
                }
                
                // Displaying active material filters
                if (!empty($materialIds)) {
                    foreach ($materialIds as $id) {
                        foreach ($materials as $material) {
                            if ($material['id'] == $id) {
                                echo '<div class="filter-tag" data-type="material" data-id="' . $id . '">';
                                echo htmlspecialchars($material['name']);
                                echo '<button type="button" onclick="removeFilter(\'material\', \'' . $id . '\')">×</button>';
                                echo '</div>';
                                echo '<input type="hidden" name="material[]" value="' . $id . '">';
                            }
                        }
                    }
                }
                ?>
            </div>
            
            <div class="filter-container">
                <div class="filters">
                    <div class="filter">
                        <button type="button" class="filter-btn" onclick="toggleDropdown('country-dropdown')">Country</button>
                        <div id="country-dropdown" class="dropdown">
                            <?php foreach ($countries as $country): ?>
                                <label>
                                    <input type="checkbox" onchange="addFilter('country', <?php echo $country['id']; ?>, '<?php echo htmlspecialchars($country['name']); ?>')" 
                                    <?php if (in_array($country['id'], $countryIds)) echo 'checked'; ?>>
                                    <?php echo htmlspecialchars($country['name']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="filter">
                        <button type="button" class="filter-btn" onclick="toggleDropdown('price-dropdown')">Price range</button>
                        <div id="price-dropdown" class="dropdown">
                            <?php foreach(['$', '$$', '$$$', '$$$$'] as $price): ?>
                                <label>
                                    <input type="checkbox" onchange="addFilter('price', '<?php echo $price; ?>', '<?php echo $price; ?>')" 
                                    <?php if (in_array($price, $priceRanges)) echo 'checked'; ?>>
                                    <?php echo $price; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="filter">
                        <button type="button" class="filter-btn" onclick="toggleDropdown('style-dropdown')">Clothing Style</button>
                        <div id="style-dropdown" class="dropdown">
                            <?php foreach ($clothingStyles as $style): ?>
                                <label>
                                    <input type="checkbox" onchange="addFilter('style', <?php echo $style['id']; ?>, '<?php echo htmlspecialchars($style['name']); ?>')" 
                                    <?php if (in_array($style['id'], $styleIds)) echo 'checked'; ?>>
                                    <?php echo htmlspecialchars($style['name']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="filter">
                        <button type="button" class="filter-btn" onclick="toggleDropdown('category-dropdown')">Clothing Type</button>
                        <div id="category-dropdown" class="dropdown">
                            <?php foreach ($categories as $category): ?>
                                <label>
                                    <input type="checkbox" onchange="addFilter('category', <?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')" 
                                    <?php if (in_array($category['id'], $categoryIds)) echo 'checked'; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="filter">
                        <button type="button" class="filter-btn" onclick="toggleDropdown('material-dropdown')">Materials</button>
                        <div id="material-dropdown" class="dropdown">
                            <?php foreach ($materials as $material): ?>
                                <label>
                                    <input type="checkbox" onchange="addFilter('material', <?php echo $material['id']; ?>, '<?php echo htmlspecialchars($material['name']); ?>')" 
                                    <?php if (in_array($material['id'], $materialIds)) echo 'checked'; ?>>
                                    <?php echo htmlspecialchars($material['name']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Vložení prázdného div prvku pro vytvoření mezery -->
        <div class="brand-list-spacer"></div>

        <div class="brand-list">
            <?php if (count($brands) > 0): ?>
                <?php foreach ($brands as $brand): ?>
                    <div class="brand">
                        <div class="brand-header" onclick="toggleBrandDetails(this)">
                            <span class="brand-name"><?php echo htmlspecialchars($brand['name']); ?></span>
                            <?php if (!empty($brand['country_name'])): ?>
                                <span class="brand-country"><?php echo $brand['flag_emoji']; ?></span>
                            <?php endif; ?>
                            <span class="brand-price"><?php echo htmlspecialchars($brand['price_range']); ?></span>
                        </div>
                        <div class="brand-details">
                            <p style="white-space: pre-line;"><?php echo htmlspecialchars($brand['description']); ?></p>
                            
                            <?php 
                            $brandDetails = getBrandDetails($pdo, $brand['id']);
                            if (!empty($brandDetails['links'])):
                                echo '<div class="brand-links">';
                                foreach ($brandDetails['links'] as $link):
                            ?>
                                <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($link['title']); ?>
                                </a>
                            <?php 
                                endforeach;
                                echo '</div>';
                            endif;
                            ?>
                            
                            <div class="brand-details-content">
                                <?php if (!empty($brandDetails['styles'])): ?>
                                    <p><strong>Style:</strong> <?php echo implode(', ', $brandDetails['styles']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($brandDetails['categories'])): ?>
                                    <p><strong>Types:</strong> <?php echo implode(', ', $brandDetails['categories']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($brandDetails['materials'])): ?>
                                    <p><strong>Materials:</strong> <?php echo implode(', ', $brandDetails['materials']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="brand-actions">
                                <a href="edit-brand.php?id=<?php echo $brand['id']; ?>" class="action-btn edit-btn">Edit Brand</a>
                                <a href="delete-brand.php?id=<?php echo $brand['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this brand?')">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">No results were found</div>
            <?php endif; ?>
        </div>
        
        <div class="add-brand-container">
            <a href="add-brand.php" class="add-brand-btn">Add New Brand</a>
        </div>
    </div>
    
    <script src="scripts.js"></script>
</body>
</html>