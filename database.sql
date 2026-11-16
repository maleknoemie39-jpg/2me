CREATE DATABASE IF NOT EXISTS `2me`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `2me`;

CREATE TABLE IF NOT EXISTS products (
  id VARCHAR(80) PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(60) NOT NULL,
  details VARCHAR(180) NOT NULL,
  price DECIMAL(8, 2) NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  image_alt VARCHAR(180) NOT NULL,
  tag VARCHAR(80) NOT NULL,
  available TINYINT(1) NOT NULL DEFAULT 1,
  display_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(120) NOT NULL,
  customer_email VARCHAR(190) NOT NULL,
  total DECIMAL(10, 2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id VARCHAR(80) NOT NULL,
  product_name VARCHAR(150) NOT NULL,
  unit_price DECIMAL(8, 2) NOT NULL,
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id),
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO products (id, name, category, details, price, image_url, image_alt, tag, display_order)
VALUES
  ('veste-lin', 'Veste en lin', 'Vêtements', 'Taille M · Très bon état', 28.00, 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=700&q=80', 'Veste beige en lin', 'Belle trouvaille', 1),
  ('sac-cuir', 'Sac en cuir vintage', 'Accessoires', 'Pièce unique · Bon état', 35.00, 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=700&q=80', 'Sac à main en cuir', 'Pièce unique', 2),
  ('chemise-rayee', 'Chemise rayée', 'Vêtements', 'Taille S · Très bon état', 19.00, 'https://images.unsplash.com/photo-1598554747436-c9293d6a588f?auto=format&fit=crop&w=700&q=80', 'Chemise rayée bleu et blanc', 'Comme neuve', 3),
  ('baskets-toile', 'Baskets en toile', 'Chaussures', 'Pointure 38 · Bon état', 24.00, 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=700&q=80', 'Baskets en toile claires', 'Bon plan', 4),
  ('pull-maille', 'Pull maille douce', 'Vêtements', 'Taille L · Très bon état', 26.00, 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=700&q=80', 'Pull en maille', 'Coup de cœur', 5),
  ('lunettes-soleil', 'Lunettes rétro', 'Accessoires', 'Taille unique · Bon état', 16.00, 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=700&q=80', 'Lunettes de soleil', 'Vintage', 6),
  ('jean-droit', 'Jean droit', 'Vêtements', 'Taille 38 · Très bon état', 22.00, 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?auto=format&fit=crop&w=700&q=80', 'Jean bleu', 'Indispensable', 7),
  ('sandales', 'Sandales en cuir', 'Chaussures', 'Pointure 39 · Bon état', 29.00, 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=700&q=80', 'Sandales à talon', 'Belle trouvaille', 8)
ON DUPLICATE KEY UPDATE id = VALUES(id);
