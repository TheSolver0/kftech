-- =============================================
-- KF TECH - Base de données complète
-- Importer dans phpMyAdmin : clic sur "Importer"
-- ⚠️ Ce script supprime et recrée toutes les tables
-- =============================================

CREATE DATABASE IF NOT EXISTS kftech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kftech;

-- Désactiver les contraintes le temps de supprimer
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS commande_details;
DROP TABLE IF EXISTS commandes;
DROP TABLE IF EXISTS avis;
DROP TABLE IF EXISTS newsletter;
DROP TABLE IF EXISTS hero_slides;
DROP TABLE IF EXISTS produits;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS utilisateurs;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- TABLE : categories
-- =============================================
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  icone VARCHAR(50) DEFAULT 'fas fa-box',
  ordre INT DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO categories (nom, slug, icone, ordre) VALUES
('Laptops & Desktop',    'laptops',      'fas fa-laptop',      1),
('Smartphones',          'smartphones',  'fas fa-mobile-alt',  2),
('Tablettes',            'tablettes',    'fas fa-tablet-alt',  3),
('Accessoires',          'accessoires',  'fas fa-headphones',  4),
('Gaming & Fun',         'gaming',       'fas fa-gamepad',     5),
('TV & Audio',           'tv-audio',     'fas fa-tv',          6),
('Caméras & Photo',      'cameras',      'fas fa-camera',      7);

-- =============================================
-- TABLE : produits
-- =============================================
CREATE TABLE produits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(255) NOT NULL,
  description TEXT,
  prix DECIMAL(12,2) NOT NULL,
  ancien_prix DECIMAL(12,2) DEFAULT NULL,
  image VARCHAR(255) DEFAULT 'assets/images/placeholder.jpg',
  categorie_id INT NOT NULL,
  marque VARCHAR(100) DEFAULT '',
  stock INT DEFAULT 0,
  badge VARCHAR(30) DEFAULT 'NEW',
  note DECIMAL(2,1) DEFAULT 4.0,
  nb_avis INT DEFAULT 0,
  actif TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categorie_id) REFERENCES categories(id)
) ENGINE=InnoDB;

INSERT INTO produits (nom, description, prix, ancien_prix, image, categorie_id, marque, stock, badge, note, nb_avis) VALUES
('Lenovo Yoga Pro 9i', 'Laptop 2-en-1 haut de gamme, écran OLED 4K, Intel Core i9, 32Go RAM, 1To SSD. Idéal pour les professionnels et créatifs.', 1299000, 1890000, 'assets/images/produit1.jpg', 1, 'Lenovo', 334, 'NEW', 4.5, 210),
('Asus Vivobook 15 2022', 'Laptop polyvalent avec écran Full HD, Intel Core i5, 16Go RAM, 512Go SSD. Parfait pour les étudiants.', 850000, 1050000, 'assets/images/produit2.jpg', 1, 'Asus', 180, 'NEW', 3.5, 126),
('Sony WH-1000XM5', 'Casque Bluetooth avec réduction de bruit active, autonomie 30h, charge rapide USB-C.', 290000, 350000, 'assets/images/produit3.jpg', 4, 'Sony', 150, 'NEW', 5.0, 210),
('Samsung Galaxy S23 FE 5G', 'Smartphone 5G, écran 6.4" Dynamic AMOLED 120Hz, triple caméra 50MP, batterie 4500mAh.', 465000, 530000, 'assets/images/produit4.jpg', 2, 'Samsung', 200, 'NEW', 4.0, 320),
('iPad Pro 12.9" M2', 'Tablette Apple avec puce M2, écran Liquid Retina XDR, Face ID, compatible Apple Pencil 2.', 890000, 1050000, 'assets/images/produit5.jpg', 3, 'Apple', 75, 'PROMO', 5.0, 445),
('Samsung Galaxy Tab S9 Ultra', 'Tablette premium 14.6" Dynamic AMOLED, Snapdragon 8 Gen 2, S Pen inclus, IP68.', 620000, 720000, 'assets/images/produit6.jpg', 3, 'Samsung', 120, 'NEW', 4.0, 198),
('Huawei P50 Pro', 'Smartphone premium avec caméra Leica, écran OLED 6.6" 120Hz, charge rapide 66W.', 580000, 680000, 'assets/images/produit7.jpg', 2, 'Huawei', 95, 'NEW', 4.0, 167),
('Xiaomi 13 Pro 5G', 'Flagship Xiaomi, caméra Leica 50MP, Snapdragon 8 Gen 2, charge 120W.', 490000, 560000, 'assets/images/produit8.jpg', 2, 'Xiaomi', 110, 'NEW', 4.0, 143),
('BenQ HT2050A Projecteur', 'Projecteur Full HD 1080p, 2200 lumens, contraste 15000:1, idéal home cinema.', 285000, 340000, 'assets/images/produit9.jpg', 6, 'BenQ', 334, 'NEW', 3.0, 126),
('Dell Inspiron 15 3000', 'Laptop entrée de gamme, Intel Core i3, 8Go RAM, 256Go SSD, idéal bureau et études.', 520000, 620000, 'assets/images/produit10.jpg', 1, 'Dell', 250, 'PROMO', 4.0, 89);

-- =============================================
-- TABLE : hero_slides  (diaporama accueil)
-- =============================================
CREATE TABLE hero_slides (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(150) NOT NULL,
  sous_titre VARCHAR(150) DEFAULT '',
  badge_texte VARCHAR(80) DEFAULT '100% meilleurs produits',
  image VARCHAR(255) NOT NULL,
  prix DECIMAL(12,2) DEFAULT NULL,
  ancien_prix DECIMAL(12,2) DEFAULT NULL,
  produit_id INT DEFAULT NULL,
  btn_principal_texte VARCHAR(60) DEFAULT 'Acheter Maintenant',
  btn_principal_lien VARCHAR(255) DEFAULT '#',
  btn_secondaire_texte VARCHAR(60) DEFAULT 'Voir Plus',
  stock_pct INT DEFAULT 50,
  actif TINYINT(1) DEFAULT 1,
  ordre INT DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO hero_slides (titre, sous_titre, badge_texte, image, prix, ancien_prix, produit_id, btn_principal_lien, stock_pct, ordre) VALUES
('LENOVO YOGA PRO 9i',    'Widescreen 4k Ultra Laptop',    '100% meilleurs produits', 'assets/images/produit1.jpg', 1299000, 1890000, 1, 'product.php?id=1', 65, 1),
('SAMSUNG GALAXY S23 FE', 'Smartphone 5G Double SIM',      'Offre limitée !',         'assets/images/produit4.jpg', 465000,  530000,  4, 'product.php?id=4', 40, 2),
('IPAD PRO M2 12.9"',     'Tablette Professionnelle Apple','Promo exclusive',          'assets/images/produit5.jpg', 890000,  1050000, 5, 'product.php?id=5', 80, 3),
('SONY WH-1000XM5',       'Casque Réduction de Bruit',     'Best seller',              'assets/images/produit3.jpg', 290000,  350000,  3, 'product.php?id=3', 55, 4),
('DELL INSPIRON 15 3000',  'Laptop Bureautique Fiable',    'Promo de saison',          'assets/images/produit10.jpg',520000,  620000,  10,'product.php?id=10',70, 5);

-- =============================================
-- TABLE : utilisateurs
-- =============================================
CREATE TABLE utilisateurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  prenom VARCHAR(100) NOT NULL,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  telephone VARCHAR(25) DEFAULT '',
  mot_de_passe VARCHAR(255) NOT NULL,
  role ENUM('client','admin') DEFAULT 'client',
  actif TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- COMPTE ADMIN par défaut
-- Email    : admin@kftech.cm
-- Mot de passe : Admin1234!
-- IMPORTANT : Changez ce mot de passe après la première connexion !
-- Pour générer un nouveau hash PHP : password_hash('VotreMotDePasse', PASSWORD_BCRYPT)
-- =============================================
INSERT INTO utilisateurs (prenom, nom, email, telephone, mot_de_passe, role) VALUES
('Admin', 'KFTech', 'admin@kftech.cm', '+237 6 90 04 84 82',
 '$2y$12$LVAKdMMOAWDMFqMRPnFxReZl8RIPVJv4HR7L7YFPNfZqSoJVE6rSm', 'admin');
-- Hash ci-dessus = password_hash('Admin1234!', PASSWORD_BCRYPT)
-- Si la connexion admin ne fonctionne pas, exécutez dans phpMyAdmin :
-- UPDATE utilisateurs SET mot_de_passe = '$2y$12$LVAKdMMOAWDMFqMRPnFxReZl8RIPVJv4HR7L7YFPNfZqSoJVE6rSm' WHERE email = 'admin@kftech.cm';

-- =============================================
-- TABLE : newsletter
-- =============================================
CREATE TABLE newsletter (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL UNIQUE,
  date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- TABLE : avis
-- =============================================
CREATE TABLE avis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  produit_id INT NOT NULL,
  utilisateur_id INT DEFAULT NULL,
  nom_auteur VARCHAR(100) DEFAULT 'Anonyme',
  note INT NOT NULL CHECK (note BETWEEN 1 AND 5),
  commentaire TEXT,
  verifie TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (produit_id) REFERENCES produits(id),
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
) ENGINE=InnoDB;

INSERT INTO avis (produit_id, nom_auteur, note, commentaire, verifie) VALUES
(1, 'Jean-Paul M.',  5, 'Produit excellent ! Livraison très rapide, emballage soigné. Je recommande KF Tech à tous mes amis.', 1),
(1, 'Marie K.',      4, 'Très bon produit, conforme à la description. Service client réactif.', 1),
(1, 'Christian B.',  4, 'Bonne qualité, rapport qualité/prix correct. Livraison en 2 jours.', 0),
(4, 'Sandrine T.',   5, 'Je suis très satisfaite. Le produit fonctionne parfaitement.', 1),
(4, 'Paul N.',       4, 'Bon smartphone, photos excellentes, batterie tient bien la journée.', 1);

-- =============================================
-- TABLE : commandes (pour plus tard)
-- =============================================
CREATE TABLE commandes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  utilisateur_id INT DEFAULT NULL,
  total DECIMAL(12,2) NOT NULL,
  statut ENUM('en_attente','confirmee','expediee','livree','annulee') DEFAULT 'en_attente',
  adresse_livraison TEXT,
  telephone VARCHAR(25),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
) ENGINE=InnoDB;

CREATE TABLE commande_details (
  id INT AUTO_INCREMENT PRIMARY KEY,
  commande_id INT NOT NULL,
  produit_id INT NOT NULL,
  quantite INT NOT NULL DEFAULT 1,
  prix_unitaire DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (commande_id) REFERENCES commandes(id),
  FOREIGN KEY (produit_id) REFERENCES produits(id)
) ENGINE=InnoDB;
