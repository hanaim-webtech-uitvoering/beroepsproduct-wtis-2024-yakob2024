<?php
// Winkelmand verwerken en klaarzetten voor presentatie (verwerklaag)

require_once __DIR__ . '/sessie.php';
require_once __DIR__ . '/autorisatie.php';
require_once __DIR__ . '/../data/menu_data.php';

// Veilige sessiestart
startSecureSession();

// Alleen klanten mogen de winkelmand gebruiken
requireCustomer('/view/login.php');

// Winkelmand initialiseren
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // ['ProductNaam' => qty]
}

// Flash messages (1x tonen)
$flash = $_SESSION['cart_flash'] ?? null;
unset($_SESSION['cart_flash']);

// Errors voor view (geen fatal)
$errors = [];

// Productcatalogus ophalen uit DB (voor validatie + prijzen)
try {
    $catalog = menuGetProductsWithCategory(); // [product_name, price, category]
} catch (Throwable $e) {
    $catalog = [];
    $errors[] = 'Producten konden niet worden geladen. Probeer het later opnieuw.';
}

// Map: product_name => ['price'=>..,'category'=>..]
$catalogMap = [];
foreach ($catalog as $p) {
    $name = (string)($p['product_name'] ?? '');
    if ($name === '') {
        continue;
    }
    $catalogMap[$name] = [
        'price' => (float)($p['price'] ?? 0),
        'category' => (string)($p['category'] ?? '')
    ];
}

/**
 * Helpers (alleen binnen dit bestand)
 */
function cartNormalizeQty($qty): int
{
    $n = (int)$qty;
    if ($n < 0) $n = 0;
    if ($n > 99) $n = 99; // simpele limiet voor misbruik/overflow
    return $n;
}

function cartSaveFlash(string $msg): void
{
    $_SESSION['cart_flash'] = $msg;
}

/**
 * POST-acties verwerken (PRG pattern: Post -> Redirect -> Get)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $product = trim($_POST['product_name'] ?? '');
        $qty = cartNormalizeQty($_POST['qty'] ?? 1);

        if ($product === '' || !isset($catalogMap[$product])) {
            $errors[] = 'Ongeldig product.';
        } elseif ($qty <= 0) {
            $errors[] = 'Aantal moet minimaal 1 zijn.';
        } else {
            $_SESSION['cart'][$product] = (int)(($_SESSION['cart'][$product] ?? 0) + $qty);
            cartSaveFlash('Product toegevoegd aan winkelmandje.');
        }

    } elseif ($action === 'update') {
        $product = trim($_POST['product_name'] ?? '');
        $qty = cartNormalizeQty($_POST['qty'] ?? 1);

        if ($product === '' || !isset($_SESSION['cart'][$product])) {
            $errors[] = 'Product staat niet in winkelmandje.';
        } elseif ($qty <= 0) {
            // qty 0 = verwijderen
            unset($_SESSION['cart'][$product]);
            cartSaveFlash('Product verwijderd uit winkelmandje.');
        } else {
            $_SESSION['cart'][$product] = $qty;
            cartSaveFlash('Aantal bijgewerkt.');
        }

    } elseif ($action === 'remove') {
        $product = trim($_POST['product_name'] ?? '');

        if ($product !== '' && isset($_SESSION['cart'][$product])) {
            unset($_SESSION['cart'][$product]);
            cartSaveFlash('Product verwijderd uit winkelmandje.');
        }

    } elseif ($action === 'clear') {
        $_SESSION['cart'] = [];
        cartSaveFlash('Winkelmandje is leeggemaakt.');

    } else {
        $errors[] = 'Ongeldige actie.';
    }

    // Bij errors: opslaan zodat view ze na redirect kan tonen
    if (!empty($errors)) {
        $_SESSION['cart_errors'] = $errors;
    } else {
        unset($_SESSION['cart_errors']);
    }

    header('Location: /view/winkelmandje.php');
    exit;
}

// Errors uit vorige POST
if (isset($_SESSION['cart_errors']) && is_array($_SESSION['cart_errors'])) {
    $errors = array_values($_SESSION['cart_errors']);
    unset($_SESSION['cart_errors']);
}

// Winkelmand items opbouwen voor view
$cartItems = [];
$cartTotal = 0.0;
$cartCount = 0;

foreach ($_SESSION['cart'] as $productName => $qty) {
    $qty = (int)$qty;
    if ($qty <= 0) {
        continue;
    }

    // Product moet bestaan in catalogus (anders verwijderen uit cart)
    if (!isset($catalogMap[$productName])) {
        unset($_SESSION['cart'][$productName]);
        continue;
    }

    $price = (float)$catalogMap[$productName]['price'];
    $category = (string)$catalogMap[$productName]['category'];
    $subtotal = $price * $qty;

    $cartItems[] = [
        'product_name' => $productName,
        'category' => $category,
        'price' => $price,
        'qty' => $qty,
        'subtotal' => $subtotal
    ];

    $cartTotal += $subtotal;
    $cartCount += $qty;
}
