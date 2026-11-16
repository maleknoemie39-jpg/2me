<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/database.php';

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $query): never
{
    header('Location: index.php' . $query);
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$notice = '';
$errorMessage = '';

try {
    $pdo = getDatabaseConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submittedToken = $_POST['csrf_token'] ?? '';
        if (!is_string($submittedToken) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            http_response_code(400);
            $errorMessage = 'Votre session a expiré. Rechargez la page puis réessayez.';
        } else {
            $action = $_POST['action'] ?? '';
            $productId = $_POST['product_id'] ?? '';

            if ($action === 'add' && is_string($productId)) {
                $statement = $pdo->prepare('SELECT id FROM products WHERE id = :id AND available = 1');
                $statement->execute(['id' => $productId]);
                if ($statement->fetchColumn() !== false) {
                    $_SESSION['cart'][$productId] = true;
                    redirect('?message=added#boutique');
                }
                $errorMessage = 'Cette pièce n’est plus disponible.';
            } elseif ($action === 'remove' && is_string($productId)) {
                unset($_SESSION['cart'][$productId]);
                redirect('?open_cart=1');
            } elseif ($action === 'checkout') {
                $customerName = trim((string) ($_POST['customer_name'] ?? ''));
                $customerEmail = trim((string) ($_POST['customer_email'] ?? ''));

                if ($customerName === '' || mb_strlen($customerName) > 120 || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL) || mb_strlen($customerEmail) > 190) {
                    $errorMessage = 'Saisissez un nom et une adresse e-mail valides pour enregistrer la commande.';
                } elseif ($_SESSION['cart'] === []) {
                    $errorMessage = 'Votre panier est vide.';
                } else {
                    try {
                        $pdo->beginTransaction();
                        $cartIds = array_keys($_SESSION['cart']);
                        $placeholders = implode(',', array_fill(0, count($cartIds), '?'));
                        $statement = $pdo->prepare("SELECT id, name, price FROM products WHERE available = 1 AND id IN ($placeholders) FOR UPDATE");
                        $statement->execute($cartIds);
                        $productsInCart = $statement->fetchAll();

                        if (count($productsInCart) !== count($cartIds)) {
                            throw new RuntimeException('Une ou plusieurs pièces du panier ne sont plus disponibles.');
                        }

                        $totalCents = 0;
                        foreach ($productsInCart as $product) {
                            $totalCents += (int) round((float) $product['price'] * 100);
                        }

                        $statement = $pdo->prepare('INSERT INTO orders (customer_name, customer_email, total) VALUES (:name, :email, :total)');
                        $statement->execute([
                            'name' => $customerName,
                            'email' => $customerEmail,
                            'total' => number_format($totalCents / 100, 2, '.', ''),
                        ]);
                        $orderId = (int) $pdo->lastInsertId();

                        $insertItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, unit_price) VALUES (:order_id, :product_id, :product_name, :unit_price)');
                        $markSold = $pdo->prepare('UPDATE products SET available = 0 WHERE id = :id AND available = 1');
                        foreach ($productsInCart as $product) {
                            $insertItem->execute([
                                'order_id' => $orderId,
                                'product_id' => $product['id'],
                                'product_name' => $product['name'],
                                'unit_price' => $product['price'],
                            ]);
                            $markSold->execute(['id' => $product['id']]);
                            if ($markSold->rowCount() !== 1) {
                                throw new RuntimeException('Une pièce vient d’être réservée par une autre commande.');
                            }
                        }

                        $pdo->commit();
                        $_SESSION['cart'] = [];
                        redirect('?message=ordered&order=' . $orderId);
                    } catch (RuntimeException $exception) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        $errorMessage = $exception->getMessage();
                    } catch (PDOException $exception) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        error_log('Échec de l’enregistrement de la commande : ' . $exception->getMessage());
                        $errorMessage = 'La commande n’a pas pu être enregistrée. Réessayez dans un instant.';
                    }
                }
            } else {
                http_response_code(400);
                $errorMessage = 'Action non reconnue.';
            }
        }
    }

    $products = $pdo->query('SELECT id, name, category, details, price, image_url, image_alt, tag FROM products WHERE available = 1 ORDER BY display_order, name')->fetchAll();
    $cartIds = array_keys($_SESSION['cart']);
    $cartProducts = [];
    if ($cartIds !== []) {
        $placeholders = implode(',', array_fill(0, count($cartIds), '?'));
        $statement = $pdo->prepare("SELECT id, name, details, price, image_url FROM products WHERE available = 1 AND id IN ($placeholders)");
        $statement->execute($cartIds);
        $cartProducts = $statement->fetchAll();
    }
    $availableCartIds = array_column($cartProducts, 'id');
    foreach (array_diff($cartIds, $availableCartIds) as $unavailableId) {
        unset($_SESSION['cart'][$unavailableId]);
    }
} catch (PDOException $exception) {
    error_log('Connexion ou lecture de la base 2me impossible : ' . $exception->getMessage());
    http_response_code(503);
    $databaseUnavailable = true;
}

if (isset($databaseUnavailable)) {
    ?>
    <!doctype html>
    <html lang="fr">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Base de données indisponible — 2me</title>
      <link rel="stylesheet" href="style.css">
    </head>
    <body data-open-cart="<?= (isset($_GET['open_cart']) || ($_SERVER['REQUEST_METHOD'] === 'POST' && $errorMessage !== '')) ? '1' : '0' ?>">
      <main class="shop-section">
        <p class="eyebrow">2me</p>
        <h1>La boutique est momentanément indisponible.</h1>
        <p>Vérifiez la configuration MySQL et importez le fichier <code>database.sql</code>.</p>
      </main>
    </body>
    </html>
    <?php
    exit;
}

$cartCount = count($cartProducts);
$cartTotalCents = 0;
foreach ($cartProducts as $product) {
    $cartTotalCents += (int) round((float) $product['price'] * 100);
}
$message = $_GET['message'] ?? '';
if ($message === 'added') {
    $notice = 'La pièce a été ajoutée à votre panier.';
} elseif ($message === 'ordered' && isset($_GET['order']) && ctype_digit((string) $_GET['order'])) {
    $notice = 'Merci ! Votre commande n°' . $_GET['order'] . ' est enregistrée. Aucun paiement réel n’a été effectué.';
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="2me, la boutique en ligne de vêtements et accessoires de seconde main soigneusement sélectionnés.">
  <meta name="theme-color" content="#f6f3eb">
  <title>2me — La mode a déjà une histoire</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <script src="script.js" defer></script>
</head>
<body>
  <div class="announcement">La seconde main, le bon choix. Livraison offerte dès 60 €.</div>
  <header class="site-header">
    <a class="wordmark" href="index.php" aria-label="2me, accueil">2me</a>
    <nav class="main-nav" aria-label="Navigation principale">
      <a href="#boutique">La boutique</a>
      <a href="#histoire">Notre histoire</a>
    </nav>
    <button class="cart-button" id="open-cart" type="button" aria-haspopup="dialog">
      Mon panier <span class="cart-count" id="cart-count" aria-label="<?= $cartCount ?> article<?= $cartCount > 1 ? 's' : '' ?>"><?= $cartCount ?></span>
    </button>
  </header>

  <main>
    <section class="hero">
      <div class="hero-copy">
        <p class="eyebrow"><span class="eyebrow-line"></span> BIEN PORTÉ, PRÊT À REVIVRE</p>
        <h1>La mode a<br>déjà une <em>histoire.</em></h1>
        <p class="hero-description">Des pièces uniques, choisies avec soin. À vous de leur inventer la suite.</p>
        <a class="button button-dark" href="#boutique">Découvrir la sélection <span aria-hidden="true">↗</span></a>
        <div class="hero-note"><span class="note-icon" aria-hidden="true">✳</span> Chaque pièce est unique, comme vous.</div>
      </div>
      <div class="hero-visual">
        <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1100&q=85" alt="Deux amies choisissent des vêtements dans une boutique">
        <div class="floating-note"><span>100 %</span><br>seconde main</div>
        <div class="hero-caption">DES TROUVAILLES QUI ONT DU SENS <span>01 — 04</span></div>
      </div>
    </section>

    <section class="values-strip" aria-label="Nos engagements">
      <p><span>✳</span> Sélection vérifiée</p>
      <p><span>↻</span> Une mode qui circule</p>
      <p><span>♡</span> Des prix tout doux</p>
    </section>

