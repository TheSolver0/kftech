-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 26 mars 2026 à 16:23
-- Version du serveur : 10.4.24-MariaDB
-- Version de PHP : 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `kftech`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `nom_auteur` varchar(100) DEFAULT 'Anonyme',
  `note` int(11) NOT NULL CHECK (`note` between 1 and 5),
  `commentaire` text DEFAULT NULL,
  `verifie` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`id`, `produit_id`, `utilisateur_id`, `nom_auteur`, `note`, `commentaire`, `verifie`, `created_at`) VALUES
(1, 1, NULL, 'Jean-Paul M.', 5, 'Produit excellent ! Livraison très rapide, emballage soigné. Je recommande KF Tech à tous mes amis.', 1, '2026-03-25 10:56:26'),
(2, 1, NULL, 'Marie K.', 4, 'Très bon produit, conforme à la description. Service client réactif.', 1, '2026-03-25 10:56:26'),
(3, 1, NULL, 'Christian B.', 4, 'Bonne qualité, rapport qualité/prix correct. Livraison en 2 jours.', 0, '2026-03-25 10:56:26'),
(4, 4, NULL, 'Sandrine T.', 5, 'Je suis très satisfaite. Le produit fonctionne parfaitement.', 1, '2026-03-25 10:56:26'),
(5, 4, NULL, 'Paul N.', 4, 'Bon smartphone, photos excellentes, batterie tient bien la journée.', 1, '2026-03-25 10:56:26');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icone` varchar(50) DEFAULT 'fas fa-box',
  `ordre` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`, `slug`, `icone`, `ordre`) VALUES
(1, 'Laptops & Desktop', 'laptops', 'fas fa-laptop', 1),
(2, 'Smartphones', 'smartphones', 'fas fa-mobile-alt', 2),
(3, 'Tablettes', 'tablettes', 'fas fa-tablet-alt', 3),
(4, 'Accessoires', 'accessoires', 'fas fa-headphones', 4),
(5, 'Gaming & Fun', 'gaming', 'fas fa-gamepad', 5),
(6, 'TV & Audio', 'tv-audio', 'fas fa-tv', 6),
(7, 'Caméras & Photo', 'cameras', 'fas fa-camera', 7);

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `total` decimal(12,2) NOT NULL,
  `statut` enum('en_attente','confirmee','expediee','livree','annulee') DEFAULT 'en_attente',
  `adresse_livraison` text DEFAULT NULL,
  `telephone` varchar(25) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `commande_details`
--

CREATE TABLE `commande_details` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `prix_unitaire` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `favoris`
--

CREATE TABLE `favoris` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `favoris`
--

INSERT INTO `favoris` (`id`, `utilisateur_id`, `produit_id`, `created_at`) VALUES
(4, 5, 3, '2026-03-26 12:27:57');

-- --------------------------------------------------------

--
-- Structure de la table `hero_slides`
--

CREATE TABLE `hero_slides` (
  `id` int(11) NOT NULL,
  `titre` varchar(150) NOT NULL,
  `sous_titre` varchar(150) DEFAULT '',
  `badge_texte` varchar(80) DEFAULT '100% meilleurs produits',
  `image` varchar(255) NOT NULL,
  `prix` decimal(12,2) DEFAULT NULL,
  `ancien_prix` decimal(12,2) DEFAULT NULL,
  `produit_id` int(11) DEFAULT NULL,
  `btn_principal_texte` varchar(60) DEFAULT 'Acheter Maintenant',
  `btn_principal_lien` varchar(255) DEFAULT '#',
  `btn_secondaire_texte` varchar(60) DEFAULT 'Voir Plus',
  `stock_pct` int(11) DEFAULT 50,
  `actif` tinyint(1) DEFAULT 1,
  `ordre` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `hero_slides`
--

INSERT INTO `hero_slides` (`id`, `titre`, `sous_titre`, `badge_texte`, `image`, `prix`, `ancien_prix`, `produit_id`, `btn_principal_texte`, `btn_principal_lien`, `btn_secondaire_texte`, `stock_pct`, `actif`, `ordre`) VALUES
(1, 'LENOVO YOGA PRO 9i', 'Widescreen 4k Ultra Laptop', '100% meilleurs produits', 'assets/images/i35.jpg', '1299000.00', '1890000.00', 1, 'Acheter Maintenant', 'product.php?id=1', 'Voir Plus', 65, 1, 1),
(2, 'SAMSUNG GALAXY S23 FE', 'Smartphone 5G Double SIM', 'Offre limitée !', 'assets/images/i77.jpg', '465000.00', '530000.00', 4, 'Acheter Maintenant', 'product.php?id=4', 'Voir Plus', 40, 1, 2),
(3, 'Legion go', 'Mini pc  gaming 12gb dedie gddr6', 'Promo exclusive', 'assets/images/i78.jpg', '890000.00', '1050000.00', 5, 'Acheter Maintenant', 'product.php?id=5', 'Voir Plus', 80, 1, 3),
(4, 'Manettes Gaming XBOX one ', 'Manettes de jeux videos xbox', 'Best seller', 'assets/images/i42.jpg', '290000.00', '350000.00', 3, 'Acheter Maintenant', 'product.php?id=3', 'Voir Plus', 55, 1, 4),
(5, 'CyberPower PC gaming RGB', 'Desktop Gaming fiable de derniere generation ultra performant', 'Promo de saison', 'assets/images/i2.jpg', '520000.00', '620000.00', 10, 'Acheter Maintenant', 'product.php?id=10', 'Voir Plus', 70, 1, 5);

-- --------------------------------------------------------

--
-- Structure de la table `newsletter`
--

CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(12,2) NOT NULL,
  `ancien_prix` decimal(12,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT 'assets/images/placeholder.jpg',
  `categorie_id` int(11) NOT NULL,
  `marque` varchar(100) DEFAULT '',
  `stock` int(11) DEFAULT 0,
  `badge` varchar(30) DEFAULT 'NEW',
  `note` decimal(2,1) DEFAULT 4.0,
  `nb_avis` int(11) DEFAULT 0,
  `actif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `nom`, `description`, `prix`, `ancien_prix`, `image`, `categorie_id`, `marque`, `stock`, `badge`, `note`, `nb_avis`, `actif`, `created_at`) VALUES
(1, 'Lenovo Yoga Pro 9i', 'Laptop 2-en-1 haut de gamme, écran OLED 4K, Intel Core i9, 32Go RAM, 1To SSD. Idéal pour les professionnels et créatifs.', '1299000.00', '1890000.00', 'assets/images/i35.jpg', 1, 'Lenovo', 334, 'NEW', '4.5', 210, 1, '2026-03-25 10:56:26'),
(2, 'Asus ROG ZYPHYRUS', 'Laptop polyvalent Ultra puissant avec écran Full HD, Intel Core i9, 16Go RAM, 512Go SSD. Parfait pour les gameurs et les professionnels.', '1200000.00', '1050000.00', 'assets/images/i39.jpg', 1, 'Asus', 180, 'NEW', '3.5', 126, 1, '2026-03-25 10:56:26'),
(3, 'Sony WH-1000XM5', 'Casque Bluetooth gaming avec réduction de bruit active, autonomie 30h, charge rapide USB-C.', '50000.00', '75000.00', 'assets/images/i44.jpg', 4, 'Sony', 150, 'NEW', '5.0', 210, 1, '2026-03-25 10:56:26'),
(4, 'Samsung Galaxy S23 FE 5G', 'Smartphone 5G, écran 6.4\" Dynamic AMOLED 120Hz, triple caméra 50MP, batterie 4500mAh.', '465000.00', '530000.00', 'assets/images/i77.jpg', 2, 'Samsung', 200, 'NEW', '4.0', 320, 1, '2026-03-25 10:56:26'),
(5, 'Tablette pour enfant 128/8 GB', 'Tablette pour enfants haut de gamme', '90000.00', '1050000.00', 'assets/images/i41.jpg', 3, 'Apple', 75, 'PROMO', '5.0', 445, 1, '2026-03-25 10:56:26'),
(6, 'Moniteur XIAOMI Redmi gaming incurvé', 'moniteur 4k gaming de derniere generation', '200000.00', '260000.00', 'assets/images/i46.jpg', 3, 'Samsung', 120, 'NEW', '4.0', 198, 1, '2026-03-25 10:56:26'),
(7, 'Redmi note 15 Pro', 'Smartphone premium avec caméra Leica, écran OLED 6.6\" 120Hz, charge rapide 66W. 256/8gb', '150000.00', '180000.00', 'assets/images/i43.jpg', 2, 'Huawei', 95, 'NEW', '4.0', 167, 1, '2026-03-25 10:56:26'),
(8, 'Carte graphique Nvidia geforce RTX 5080', 'carte graphique de derniere generation avec 12gb de dédié GDDR6', '200000.00', '230000.00', 'assets/images/i48.jpg', 2, 'Xiaomi', 110, 'NEW', '4.0', 143, 1, '2026-03-25 10:56:26'),
(9, 'BenQ HT2050A Projecteur', 'Projecteur Full HD 1080p, 2200 lumens, contraste 15000:1, idéal home cinema.', '285000.00', '340000.00', 'assets/images/i79.jpg', 6, 'BenQ', 334, 'NEW', '3.0', 126, 1, '2026-03-25 10:56:26'),
(10, 'Dell Inspiron 15 3000', 'Laptop entrée de gamme, Intel Core i3, 8Go RAM, 256Go SSD, idéal bureau et études.', '520000.00', '620000.00', 'assets/images/produit10.jpg', 1, 'Dell', 250, 'PROMO', '4.0', 89, 1, '2026-03-25 10:56:26');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(25) DEFAULT '',
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('client','admin') DEFAULT 'client',
  `actif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `prenom`, `nom`, `email`, `telephone`, `mot_de_passe`, `role`, `actif`, `created_at`) VALUES
(1, 'Admin', 'KFTech', 'admin@kftech.cm', '+237 6 90 04 84 82', '$2y$12$llUkPOlqgWBTvV5oUHGMCOeVMyssnT6q8KIEOoF7j1XjrzAyMxTte', 'admin', 1, '2026-03-25 10:56:26'),
(5, 'Franck', 'Fombano', 'fombano2@gmail.com', '+237693865206', '$2y$12$hX3zGN8/ot0RIhjh3OXjyeX47QZRdY6ZQZO84VKCzoXId4edZLm2K', 'client', 1, '2026-03-26 12:07:17');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produit_id` (`produit_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `commande_details`
--
ALTER TABLE `commande_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commande_id` (`commande_id`),
  ADD KEY `produit_id` (`produit_id`);

--
-- Index pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favori` (`utilisateur_id`,`produit_id`),
  ADD KEY `produit_id` (`produit_id`);

--
-- Index pour la table `hero_slides`
--
ALTER TABLE `hero_slides`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `newsletter`
--
ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categorie_id` (`categorie_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commande_details`
--
ALTER TABLE `commande_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `favoris`
--
ALTER TABLE `favoris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `hero_slides`
--
ALTER TABLE `hero_slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `newsletter`
--
ALTER TABLE `newsletter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`),
  ADD CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD CONSTRAINT `commandes_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `commande_details`
--
ALTER TABLE `commande_details`
  ADD CONSTRAINT `commande_details_ibfk_1` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`),
  ADD CONSTRAINT `commande_details_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`);

--
-- Contraintes pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD CONSTRAINT `favoris_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favoris_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
  ADD CONSTRAINT `produits_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
