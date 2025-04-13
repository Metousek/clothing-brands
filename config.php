<?php
// Database connection settings
$host = 'localhost';
$dbname = 'clothing_brands';
$username = 'root'; // In production environment use a more secure username
$password = ''; // In production environment use a strong password

// Admin credentials - secure hash for "admin123"
// This hash was generated with password_hash('admin123', PASSWORD_DEFAULT)
$admin_password_hash = '$2y$10$BDJGwqMzienY1jspW/WgwO.3uapJoRdn4n4g0xhn6KcnXyi2snp6C';

// Function to check admin password - uses PHP's built-in password_verify
function verifyAdminPassword($password) {
    global $admin_password_hash;
    return password_verify($password, $admin_password_hash);
}

// Creating PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8mb4");
} catch (PDOException $e) {
    die("Failed to connect to database: " . $e->getMessage());
}

// Define proper countries order
$countryOrder = [
    'USA', 'Japan', 'United Kingdom', 'Scotland', 'Germany', 'Sweden', 'Norway',
    'Belgium', 'Poland', 'Switzerland', 'France', 'Italy', 'Spain', 'Portugal',
    'India', 'Indonesia', 'South Korea', 'China', 'Australia', 'Mexico', 'Africa',
    'Canada', 'South America', 'Rest of Europe', 'Rest of Asia'
];

// Define proper categories order
$categoryOrder = [
    'Outerwear', 'Footwear', 'Knitwear', 'Cotton goods', 'Bottoms', 'Formalwear',
    'Headwear', 'Eyewear'
];

// Define proper materials order
$materialOrder = [
    'Denim', 'Canvas (waxed/unwaxed)', 'Cotton', 'Linen', 'Silk', 'Wool',
    'Mohair', 'Cashmere', 'Leather', 'Down filled', 'Synthetics'
];

// Function to load all countries
function getAllCountries($pdo) {
    global $countryOrder;
    $stmt = $pdo->query("SELECT * FROM countries");
    $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Sort countries based on defined order
    usort($countries, function($a, $b) use ($countryOrder) {
        $posA = array_search($a['name'], $countryOrder);
        $posB = array_search($b['name'], $countryOrder);
        return $posA - $posB;
    });
    
    return $countries;
}

// Function to load all clothing categories
function getAllClothingCategories($pdo) {
    global $categoryOrder;
    $stmt = $pdo->query("SELECT * FROM clothing_categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Sort categories based on defined order
    usort($categories, function($a, $b) use ($categoryOrder) {
        $posA = array_search($a['name'], $categoryOrder);
        $posB = array_search($b['name'], $categoryOrder);
        return $posA - $posB;
    });
    
    return $categories;
}

// Function to load all materials
function getAllMaterials($pdo) {
    global $materialOrder;
    $stmt = $pdo->query("SELECT * FROM materials");
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Sort materials based on defined order
    usort($materials, function($a, $b) use ($materialOrder) {
        $posA = array_search($a['name'], $materialOrder);
        $posB = array_search($b['name'], $materialOrder);
        return $posA - $posB;
    });
    
    return $materials;
}

// Function to get brand details including all connected data
function getBrandDetails($pdo, $brandId) {
    // Getting basic information about the brand
    $stmt = $pdo->prepare("
        SELECT b.*, c.name as country_name, c.flag_emoji 
        FROM brands b
        LEFT JOIN countries c ON b.country_id = c.id
        WHERE b.id = ?
    ");
    $stmt->execute([$brandId]);
    $brand = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$brand) {
        return null;
    }

    // Getting brand categories
    $stmt = $pdo->prepare("
        SELECT cc.name
        FROM brand_categories bc
        JOIN clothing_categories cc ON bc.category_id = cc.id
        WHERE bc.brand_id = ?
    ");
    $stmt->execute([$brandId]);
    $brand['categories'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Getting brand materials
    $stmt = $pdo->prepare("
        SELECT m.name
        FROM brand_materials bm
        JOIN materials m ON bm.material_id = m.id
        WHERE bm.brand_id = ?
    ");
    $stmt->execute([$brandId]);
    $brand['materials'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Getting brand links
    $stmt = $pdo->prepare("
        SELECT title, url
        FROM brand_links
        WHERE brand_id = ?
    ");
    $stmt->execute([$brandId]);
    $brand['links'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Getting brand styles
    $stmt = $pdo->prepare("
        SELECT style_id
        FROM brand_styles
        WHERE brand_id = ?
    ");
    $stmt->execute([$brandId]);
    $styleIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $styles = [];
    $clothingStyles = [
        ['id' => 1, 'name' => 'Heritage'],
        ['id' => 2, 'name' => 'Formal'],
        ['id' => 3, 'name' => 'Casual'],
        ['id' => 4, 'name' => 'Streetwear']
    ];
    
    foreach ($styleIds as $styleId) {
        foreach ($clothingStyles as $style) {
            if ($style['id'] == $styleId) {
                $styles[] = $style['name'];
            }
        }
    }
    
    $brand['styles'] = $styles;

    return $brand;
}

// Function for filtering brands
function filterBrands($pdo, $searchTerm = '', $countryIds = [], $priceRanges = [], $categoryIds = [], $materialIds = [], $styleIds = []) {
    // Ensure parameters are arrays
    if (!is_array($countryIds)) $countryIds = $countryIds ? [$countryIds] : [];
    if (!is_array($priceRanges)) $priceRanges = $priceRanges ? [$priceRanges] : [];
    if (!is_array($categoryIds)) $categoryIds = $categoryIds ? [$categoryIds] : [];
    if (!is_array($materialIds)) $materialIds = $materialIds ? [$materialIds] : [];
    if (!is_array($styleIds)) $styleIds = $styleIds ? [$styleIds] : [];
    
    $query = "
        SELECT DISTINCT b.*, c.name as country_name, c.flag_emoji 
        FROM brands b
        LEFT JOIN countries c ON b.country_id = c.id
    ";
    
    $params = [];
    $conditions = [];
    
    // Connecting necessary tables
    if (!empty($categoryIds)) {
        $query .= " LEFT JOIN brand_categories bc ON b.id = bc.brand_id";
    }
    
    if (!empty($materialIds)) {
        $query .= " LEFT JOIN brand_materials bm ON b.id = bm.brand_id";
    }
    
    if (!empty($styleIds)) {
        $query .= " LEFT JOIN brand_styles bs ON b.id = bs.brand_id";
    }
    
    // Adding conditions
    if (!empty($searchTerm)) {
        $conditions[] = "(b.name LIKE ? OR b.description LIKE ?)";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
    }
    
    if (!empty($countryIds)) {
        $placeholders = str_repeat('?,', count($countryIds) - 1) . '?';
        $conditions[] = "b.country_id IN ($placeholders)";
        foreach ($countryIds as $id) {
            $params[] = $id;
        }
    }
    
    if (!empty($priceRanges)) {
        $placeholders = str_repeat('?,', count($priceRanges) - 1) . '?';
        $conditions[] = "b.price_range IN ($placeholders)";
        foreach ($priceRanges as $price) {
            $params[] = $price;
        }
    }
    
    if (!empty($categoryIds)) {
        $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
        $conditions[] = "bc.category_id IN ($placeholders)";
        foreach ($categoryIds as $id) {
            $params[] = $id;
        }
    }
    
    if (!empty($materialIds)) {
        $placeholders = str_repeat('?,', count($materialIds) - 1) . '?';
        $conditions[] = "bm.material_id IN ($placeholders)";
        foreach ($materialIds as $id) {
            $params[] = $id;
        }
    }
    
    if (!empty($styleIds)) {
        $placeholders = str_repeat('?,', count($styleIds) - 1) . '?';
        $conditions[] = "bs.style_id IN ($placeholders)";
        foreach ($styleIds as $id) {
            $params[] = $id;
        }
    }
    
    // Composing the WHERE part
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    // Sorting by name
    $query .= " ORDER BY b.name ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>