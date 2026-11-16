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
  <meta name="theme-color" content="#edf3f7">
  <title>2me — La mode a déjà une histoire</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <script src="script.js?v=<?= filemtime(__DIR__ . '/script.js') ?>" defer></script>
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
      <p><span aria-hidden="true">✳</span> Sélection vérifiée</p>
      <p><span aria-hidden="true">↻</span> Une mode qui circule</p>
      <p><span aria-hidden="true">♡</span> Des prix tout doux</p>
    </section>

    <section class="shop-section" id="boutique">
      <div class="section-heading">
        <div>
          <p class="eyebrow">LE DRESSING 2me</p>
          <h2>De belles pièces,<br><em>une deuxième vie.</em></h2>
        </div>
        <p class="section-intro">On chine, on vérifie, on vous déniche des vêtements et accessoires qui méritent de continuer leur histoire.</p>
      </div>

      <div class="shop-tools">
        <div class="filters" role="group" aria-label="Filtrer par catégorie">
          <button class="filter-button active" type="button" data-filter="tout" aria-pressed="true">Tout voir</button>
          <button class="filter-button" type="button" data-filter="Vêtements" aria-pressed="false">Vêtements</button>
          <button class="filter-button" type="button" data-filter="Chaussures" aria-pressed="false">Chaussures</button>
          <button class="filter-button" type="button" data-filter="Accessoires" aria-pressed="false">Accessoires</button>
        </div>
        <label class="search-box">
          <span class="visually-hidden">Rechercher un article</span>
          <span aria-hidden="true">⌕</span>
          <input id="product-search" type="search" placeholder="Une petite recherche…">
        </label>
        <label class="sort-box" for="product-sort">
          Trier par
          <select id="product-sort">
            <option value="featured">Sélection 2me</option>
            <option value="price-asc">Prix croissant</option>
            <option value="price-desc">Prix décroissant</option>
            <option value="name-asc">Nom A–Z</option>
          </select>
        </label>
      </div>
      <p class="results-count" id="results-count" aria-live="polite"></p>
      <div class="product-grid" id="product-grid">
        <?php foreach ($products as $product): ?>
          <article class="product-card" data-category="<?= escape($product['category']) ?>" data-search="<?= escape($product['name'] . ' ' . $product['category'] . ' ' . $product['details']) ?>" data-name="<?= escape($product['name']) ?>" data-price="<?= escape((string) $product['price']) ?>">
            <div class="product-image-wrap">
              <img class="product-image" src="<?= escape($product['image_url']) ?>" alt="<?= escape($product['image_alt']) ?>" loading="lazy">
              <span class="product-tag"><?= escape($product['tag']) ?></span>
              <form method="post" action="index.php#boutique">
                <input type="hidden" name="csrf_token" value="<?= escape($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= escape($product['id']) ?>">
                <button class="add-button" type="submit" aria-label="Ajouter <?= escape($product['name']) ?> au panier">+</button>
              </form>
            </div>
            <div class="product-info">
              <p class="product-category"><?= escape($product['category']) ?></p>
              <div class="product-name-row">
                <h3 class="product-name"><?= escape($product['name']) ?></h3>
                <span class="product-price"><?= number_format((float) $product['price'], 0, ',', ' ') ?> €</span>
              </div>
              <p class="product-details"><?= escape($product['details']) ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <p class="empty-state" id="empty-state" hidden>Aucune pièce trouvée. Essayez une autre recherche.</p>
    </section>

    <section class="story-section" id="histoire">
      <div class="story-image">
        <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1000&q=85" alt="Sélection de vêtements de seconde main aux couleurs douces" loading="lazy">
      </div>
      <div class="story-copy">
        <p class="eyebrow">MOINS MAIS MIEUX</p>
        <h2>Le style n’a pas<br>besoin d’être <em>neuf.</em></h2>
        <p>Choisir la seconde main, c’est donner une nouvelle chance aux belles choses. Chez 2me, chaque pièce est inspectée et décrite avec honnêteté avant de rejoindre votre dressing.</p>
        <a class="text-link" href="#boutique">Trouver ma prochaine pièce <span aria-hidden="true">→</span></a>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <a class="wordmark" href="index.php" aria-label="2me, accueil">2me</a>
    <p>De belles histoires à porter.</p>
  </footer>

  <dialog class="cart-dialog" id="cart-dialog" aria-labelledby="cart-title">
    <div class="cart-header">
      <div>
        <p class="eyebrow">VOS TROUVAILLES</p>
        <h2 id="cart-title">Mon panier <span id="dialog-cart-count">(<?= $cartCount ?>)</span></h2>
      </div>
      <button class="close-button" id="close-cart" type="button" aria-label="Fermer le panier">×</button>
    </div>
    <div class="cart-items">
      <?php foreach ($cartProducts as $product): ?>
        <article class="cart-item">
          <img src="<?= escape($product['image_url']) ?>" alt="">
          <div>
            <h3><?= escape($product['name']) ?></h3>
            <p><?= escape($product['details']) ?></p>
            <form method="post" action="index.php?open_cart=1">
              <input type="hidden" name="csrf_token" value="<?= escape($_SESSION['csrf_token']) ?>">
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="product_id" value="<?= escape($product['id']) ?>">
              <button class="remove-item" type="submit">Retirer</button>
            </form>
          </div>
          <span class="cart-item-price"><?= number_format((float) $product['price'], 0, ',', ' ') ?> €</span>
        </article>
      <?php endforeach; ?>
    </div>
    <?php if ($cartCount === 0): ?>
      <div class="cart-empty">
        <span aria-hidden="true">♡</span>
        <p>Votre panier attend sa première trouvaille.</p>
        <button class="text-link" id="continue-shopping" type="button">Continuer mes découvertes →</button>
      </div>
    <?php else: ?>
      <div class="cart-footer">
        <p class="cart-total">Sous-total <strong><?= number_format($cartTotalCents / 100, 2, ',', ' ') ?> €</strong></p>
        <p class="checkout-note">Commande de démonstration, sans paiement réel.</p>
        <form method="post" action="index.php">
          <input type="hidden" name="csrf_token" value="<?= escape($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="action" value="checkout">
          <label class="checkout-label" for="customer-name">Votre nom</label>
          <input class="checkout-input" id="customer-name" name="customer_name" type="text" maxlength="120" autocomplete="name" required>
          <label class="checkout-label" for="customer-email">Votre e-mail</label>
          <input class="checkout-input" id="customer-email" name="customer_email" type="email" maxlength="190" autocomplete="email" required>
          <button class="button button-dark checkout-button" type="submit">Enregistrer ma commande</button>
        </form>
      </div>
    <?php endif; ?>
  </dialog>

  <?php if ($notice !== ''): ?>
    <div class="toast visible" id="toast" role="status" aria-live="polite"><?= escape($notice) ?></div>
  <?php elseif ($errorMessage !== ''): ?>
    <div class="toast visible" id="toast" role="alert" aria-live="assertive"><?= escape($errorMessage) ?></div>
  <?php else: ?>
    <div class="toast" id="toast" role="status" aria-live="polite"></div>
  <?php endif; ?>
</body>
</html>

