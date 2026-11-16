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
  ('sandales', 'Sandales en cuir', 'Chaussures', 'Pointure 39 · Bon état', 29.00, 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=700&q=80', 'Sandales à talon', 'Belle trouvaille', 8),
  ('robe-midi-fleurie', 'Robe midi fleurie', 'Vêtements', 'Taille S · Très bon état', 32.00, 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&w=700&q=80', 'Robe fleurie portée', 'Coup de cœur', 9),
  ('blazer-oversize', 'Blazer oversize', 'Vêtements', 'Taille M · Bon état', 34.00, 'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&w=700&q=80', 'Veste de style vintage', 'Belle trouvaille', 10),
  ('jupe-plissee', 'Jupe plissée', 'Vêtements', 'Taille 38 · Très bon état', 22.00, 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=700&q=80', 'Tenue avec jupe plissée', 'Comme neuve', 11),
  ('tee-blanc', 'T-shirt en coton', 'Vêtements', 'Taille M · Comme neuf', 12.00, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=700&q=80', 'T-shirt en coton clair', 'Essentiel', 12),
  ('cardigan-ecru', 'Cardigan écru', 'Vêtements', 'Taille S · Très bon état', 28.00, 'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?auto=format&fit=crop&w=700&q=80', 'Cardigan clair', 'Douceur vintage', 13),
  ('robe-noire', 'Petite robe noire', 'Vêtements', 'Taille M · Très bon état', 29.00, 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=700&q=80', 'Robe noire portée', 'Intemporelle', 14),
  ('chemise-lin', 'Chemise en lin', 'Vêtements', 'Taille L · Bon état', 21.00, 'https://images.unsplash.com/photo-1596755389378-c31d21fd1273?auto=format&fit=crop&w=700&q=80', 'Chemise en lin', 'Belle trouvaille', 15),
  ('pantalon-large', 'Pantalon fluide', 'Vêtements', 'Taille 40 · Très bon état', 24.00, 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?auto=format&fit=crop&w=700&q=80', 'Pantalon fluide', 'Confort chic', 16),
  ('veste-denim', 'Veste en jean', 'Vêtements', 'Taille M · Bon état', 30.00, 'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?auto=format&fit=crop&w=700&q=80', 'Veste en jean', 'Indispensable', 17),
  ('sweat-vintage', 'Sweat vintage', 'Vêtements', 'Taille L · Bon état', 18.00, 'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?auto=format&fit=crop&w=700&q=80', 'Sweat-shirt vintage', 'Esprit rétro', 18),
  ('manteau-laine', 'Manteau en laine', 'Vêtements', 'Taille M · Très bon état', 46.00, 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=700&q=80', 'Manteau en laine', 'Belle pièce', 19),
  ('blouse-brodee', 'Blouse brodée', 'Vêtements', 'Taille S · Bon état', 25.00, 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=700&q=80', 'Blouse portée', 'Détail délicat', 20),
  ('robe-chemise', 'Robe chemise', 'Vêtements', 'Taille M · Très bon état', 31.00, 'https://images.unsplash.com/photo-1566206091558-7f218b696731?auto=format&fit=crop&w=700&q=80', 'Robe chemise', 'Facile à porter', 21),
  ('short-denim', 'Short en jean', 'Vêtements', 'Taille 38 · Bon état', 20.00, 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=700&q=80', 'Short en jean', 'Look décontracté', 22),
  ('mariniere-coton', 'Marinière en coton', 'Vêtements', 'Taille M · Très bon état', 18.00, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=700&q=80', 'Haut en coton', 'Classique français', 23),
  ('jupe-denim', 'Jupe en jean', 'Vêtements', 'Taille 38 · Très bon état', 19.00, 'https://images.unsplash.com/photo-1503341455253-b2e723bb3dbb?auto=format&fit=crop&w=700&q=80', 'Jupe en jean', 'Indispensable', 24),
  ('pull-col-v', 'Pull col V', 'Vêtements', 'Taille L · Bon état', 27.00, 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?auto=format&fit=crop&w=700&q=80', 'Pull en maille', 'Maille douce', 25),
  ('veste-cuir', 'Veste en cuir', 'Vêtements', 'Taille M · Très bon état', 49.00, 'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&w=700&q=80', 'Veste en cuir', 'Pièce de caractère', 26),
  ('pantalon-tailleur', 'Pantalon de tailleur', 'Vêtements', 'Taille 40 · Très bon état', 35.00, 'https://images.unsplash.com/photo-1532453288672-3a27e9be9efd?auto=format&fit=crop&w=700&q=80', 'Pantalon de tailleur', 'Élégant', 27),
  ('robe-longue', 'Robe longue fluide', 'Vêtements', 'Taille S · Bon état', 36.00, 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=700&q=80', 'Robe longue portée', 'Belle trouvaille', 28),
  ('polo-coton', 'Polo en coton', 'Vêtements', 'Taille M · Très bon état', 16.00, 'https://images.unsplash.com/photo-1598032895397-b9472444bf93?auto=format&fit=crop&w=700&q=80', 'Polo en coton', 'Casual', 29),
  ('gilet-sans-manches', 'Gilet sans manches', 'Vêtements', 'Taille M · Bon état', 23.00, 'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?auto=format&fit=crop&w=700&q=80', 'Gilet sans manches', 'Tendance rétro', 30),
  ('trench-leger', 'Trench léger', 'Vêtements', 'Taille M · Très bon état', 42.00, 'https://images.unsplash.com/photo-1551232864-3f0890e580d9?auto=format&fit=crop&w=700&q=80', 'Trench porté', 'Belle pièce', 31),
  ('jupe-midi', 'Jupe midi', 'Vêtements', 'Taille 38 · Bon état', 20.00, 'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?auto=format&fit=crop&w=700&q=80', 'Jupe midi', 'Tout en légèreté', 32),
  ('top-satin', 'Top satiné', 'Vêtements', 'Taille S · Très bon état', 17.00, 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&w=700&q=80', 'Top satiné porté', 'Soirée douce', 33),
  ('combinaison-fluide', 'Combinaison fluide', 'Vêtements', 'Taille M · Très bon état', 39.00, 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=700&q=80', 'Combinaison portée', 'Prête à sortir', 34),
  ('chemisier-rayures', 'Chemisier à rayures', 'Vêtements', 'Taille L · Bon état', 20.00, 'https://images.unsplash.com/photo-1596755389378-c31d21fd1273?auto=format&fit=crop&w=700&q=80', 'Chemisier à rayures', 'Comme neuf', 35),
  ('veste-travail', 'Veste de travail', 'Vêtements', 'Taille M · Très bon état', 33.00, 'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?auto=format&fit=crop&w=700&q=80', 'Veste de travail', 'Vintage authentique', 36),
  ('robe-soiree', 'Robe de soirée', 'Vêtements', 'Taille S · Très bon état', 44.00, 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=700&q=80', 'Robe de soirée portée', 'Pièce unique', 37),
  ('gilet-vintage', 'Gilet vintage', 'Vêtements', 'Taille M · Bon état', 26.00, 'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?auto=format&fit=crop&w=700&q=80', 'Gilet vintage', 'Coup de cœur', 38),
  ('jean-wide-leg', 'Jean wide leg', 'Vêtements', 'Taille 38 · Très bon état', 24.00, 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?auto=format&fit=crop&w=700&q=80', 'Jean wide leg', 'Denim cool', 39),
  ('tunique-coton', 'Tunique en coton', 'Vêtements', 'Taille L · Bon état', 17.00, 'https://images.unsplash.com/photo-1503341455253-b2e723bb3dbb?auto=format&fit=crop&w=700&q=80', 'Tunique en coton', 'Facile à vivre', 40),
  ('short-lin', 'Short en lin', 'Vêtements', 'Taille 38 · Très bon état', 18.00, 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=700&q=80', 'Short en lin', 'Jours ensoleillés', 41),
  ('cardigan-marine', 'Cardigan bleu marine', 'Vêtements', 'Taille M · Bon état', 25.00, 'https://images.unsplash.com/photo-1589310243389-96a5483213a8?auto=format&fit=crop&w=700&q=80', 'Cardigan bleu marine', 'Maille vintage', 42),
  ('manteau-long', 'Manteau long', 'Vêtements', 'Taille L · Très bon état', 54.00, 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=700&q=80', 'Manteau long', 'Belle pièce', 43),
  ('chemise-denim', 'Chemise en denim', 'Vêtements', 'Taille M · Bon état', 23.00, 'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?auto=format&fit=crop&w=700&q=80', 'Chemise en denim', 'Casual vintage', 44),
  ('mocassins-cuir', 'Mocassins en cuir', 'Chaussures', 'Pointure 39 · Très bon état', 38.00, 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=700&q=80', 'Mocassins en cuir', 'Belle trouvaille', 45),
  ('bottines-vintage', 'Bottines vintage', 'Chaussures', 'Pointure 38 · Bon état', 42.00, 'https://images.unsplash.com/photo-1560343090-f0409e92791a?auto=format&fit=crop&w=700&q=80', 'Bottines vintage', 'Style rétro', 46),
  ('escarpins-bleus', 'Escarpins classiques', 'Chaussures', 'Pointure 37 · Très bon état', 31.00, 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=700&q=80', 'Chaussures classiques', 'Élégants', 47),
  ('derbies-cuir', 'Derbies en cuir', 'Chaussures', 'Pointure 40 · Bon état', 36.00, 'https://images.unsplash.com/photo-1603252110481-7ba873bf42ab?auto=format&fit=crop&w=700&q=80', 'Derbies en cuir', 'Indispensable', 48),
  ('sac-bandouliere', 'Sac bandoulière', 'Accessoires', 'Taille unique · Très bon état', 27.00, 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=700&q=80', 'Sac bandoulière', 'Pièce unique', 49),
  ('foulard-soie', 'Foulard en soie', 'Accessoires', 'Taille unique · Bon état', 15.00, 'https://images.unsplash.com/photo-1566206091558-7f218b696731?auto=format&fit=crop&w=700&q=80', 'Foulard imprimé', 'Petite touche', 50)
ON DUPLICATE KEY UPDATE id = VALUES(id);

