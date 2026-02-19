-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : jeu. 19 fév. 2026 à 14:04
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `vite_gourmand`
--

-- --------------------------------------------------------

--
-- Structure de la table `allergene`
--

CREATE TABLE `allergene` (
  `allergene_id` int(11) NOT NULL,
  `libelle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `allergene`
--

INSERT INTO `allergene` (`allergene_id`, `libelle`) VALUES
(5, 'Arachides'),
(9, 'Céleri'),
(2, 'Crustacés'),
(8, 'Fruits à coque'),
(1, 'Gluten'),
(11, 'Graines de sésame'),
(7, 'Lait'),
(13, 'Lupin'),
(14, 'Mollusques'),
(10, 'Moutarde'),
(4, 'Poisson'),
(6, 'Soja'),
(12, 'Sulfites'),
(3, 'Œufs');

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `avis_id` int(11) NOT NULL,
  `note` varchar(50) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `date_avis` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `commande_id` int(11) NOT NULL,
  `numero_commande` varchar(50) NOT NULL,
  `date_commande` datetime NOT NULL DEFAULT current_timestamp(),
  `date_prestation` date NOT NULL,
  `heure_livraison` varchar(50) DEFAULT NULL,
  `prix_menu` double NOT NULL,
  `nombre_personne` int(11) NOT NULL,
  `prix_livraison` double DEFAULT NULL,
  `statut` varchar(50) NOT NULL,
  `pret_materiel` tinyint(1) DEFAULT NULL,
  `restitution_materiel` tinyint(1) DEFAULT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `adresse_prestation` varchar(150) NOT NULL,
  `ville_prestation` varchar(50) NOT NULL,
  `distance_km` double DEFAULT NULL,
  `remise` double NOT NULL DEFAULT 0,
  `date_livraison` date NOT NULL,
  `ville_livraison` varchar(100) NOT NULL,
  `adresse_livraison` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`commande_id`, `numero_commande`, `date_commande`, `date_prestation`, `heure_livraison`, `prix_menu`, `nombre_personne`, `prix_livraison`, `statut`, `pret_materiel`, `restitution_materiel`, `utilisateur_id`, `menu_id`, `adresse_prestation`, `ville_prestation`, `distance_km`, `remise`, `date_livraison`, `ville_livraison`, `adresse_livraison`) VALUES
(8, 'CMD-20260217-004414', '2026-02-17 00:44:14', '2026-10-20', '19:00', 30, 10, 0, 'accepte', NULL, NULL, 5, 9, 'FDKFDKD', 'Bordeaux', NULL, 0, '0000-00-00', '', ''),
(9, 'CMD-20260217-154206', '2026-02-17 15:42:06', '2026-02-20', '19:00', 30, 10, 0, 'en_preparation', NULL, NULL, 5, 9, 'kjhgfdsdfgh', 'Bordeaux', NULL, 0, '0000-00-00', '', ''),
(10, 'CMD1771411974', '2026-02-18 11:52:54', '1997-10-20', '18:00', 40, 20, 0, 'annulee', 0, 0, 7, 5, 'JHGFDSDHJHGF', 'Bordeaux', 0, 0, '1997-10-20', 'Bordeaux', 'JHGFDSDHJHGF'),
(11, 'CMD1771428911', '2026-02-18 16:35:11', '1997-10-20', '19:00', 30, 10, 10.9, 'en_attente', 0, 0, 5, 9, 'JHGFD', 'Marseille', 10, 0, '1997-10-20', 'Marseille', 'JHGFD'),
(12, 'CMD1771431361', '2026-02-18 17:16:01', '1997-10-20', '19:00', 30, 10, 0, 'en_attente', 0, 0, 5, 9, 'JHGFDSHJ', 'Bordeaux', 0, 0, '1997-10-20', 'Bordeaux', 'JHGFDSHJ'),
(13, 'CMD1771502202', '2026-02-19 12:56:42', '2026-10-20', '19:00', 30, 10, 0, 'en_attente', 0, 0, 5, 9, 'HSHGFDFGF', 'Bordeaux', 0, 0, '2026-10-20', 'Bordeaux', 'HSHGFDFGF');

-- --------------------------------------------------------

--
-- Structure de la table `commande_suivi`
--

CREATE TABLE `commande_suivi` (
  `suivi_id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `statut` varchar(50) NOT NULL,
  `date_modif` datetime NOT NULL DEFAULT current_timestamp(),
  `mode_contact` varchar(50) DEFAULT NULL,
  `motif` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande_suivi`
--

INSERT INTO `commande_suivi` (`suivi_id`, `commande_id`, `statut`, `date_modif`, `mode_contact`, `motif`) VALUES
(1, 10, 'en_attente', '2026-02-18 11:52:54', NULL, NULL),
(2, 9, 'en_preparation', '2026-02-18 13:52:29', NULL, NULL),
(3, 11, 'en_attente', '2026-02-18 16:35:11', NULL, NULL),
(4, 12, 'en_attente', '2026-02-18 17:16:01', NULL, NULL),
(5, 13, 'en_attente', '2026-02-19 12:56:42', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `horaire`
--

CREATE TABLE `horaire` (
  `horaire_id` int(11) NOT NULL,
  `jour` varchar(50) NOT NULL,
  `heure_ouverture` varchar(50) NOT NULL,
  `heure_fermeture` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `horaire`
--

INSERT INTO `horaire` (`horaire_id`, `jour`, `heure_ouverture`, `heure_fermeture`) VALUES
(15, 'Lundi', '09:00:00', '18:00:00'),
(16, 'Mardi', '09:00:00', '18:00:00'),
(17, 'Mercredi', '09:00:00', '18:00:00'),
(18, 'Jeudi', '09:00:00', '18:00:00'),
(19, 'Vendredi', '09:00:00', '18:00:00'),
(20, 'Samedi', '10:00:00', '16:00:00'),
(21, 'Dimanche', '00:00:00', '00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `menu`
--

CREATE TABLE `menu` (
  `menu_id` int(11) NOT NULL,
  `titre` varchar(50) NOT NULL,
  `nombre_personne_minimum` int(11) NOT NULL,
  `prix_par_personne` double NOT NULL,
  `description` varchar(50) DEFAULT NULL,
  `quantite_restante` int(11) DEFAULT NULL,
  `regime_id` int(11) NOT NULL,
  `theme_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `menu`
--

INSERT INTO `menu` (`menu_id`, `titre`, `nombre_personne_minimum`, `prix_par_personne`, `description`, `quantite_restante`, `regime_id`, `theme_id`) VALUES
(1, 'Menu Mariage Prestige', 50, 65, 'Menu pour mariage et grandes réceptions.', 5, 4, 4),
(2, 'Menu Cocktail d\'entreprise', 20, 35, 'Assortiment de pièces salées et sucrées', 15, 4, 8),
(3, 'Menu Séminaire ', 15, 30, 'Menu adapté aux séminaires d\'entreprise.', 10, 5, 9),
(4, 'Menu Brunch', 10, 30, 'Brunch composé d\'options sans gluten.', 15, 7, 12),
(5, 'Menu repas de Gala ', 20, 40, 'Menu adapté aux évènements traditionnels casher.', 7, 9, 11),
(6, 'Menu Baptème', 20, 30, 'Menu familial adapté aux réceptions de baptème.', 12, 4, 6),
(7, 'Menu Anniversaire', 20, 40, 'Menu adapté aux évènements familiaux halal.', 8, 8, 5),
(8, 'Menu soirée privée', 10, 25, 'Menu 100% vegan pour évènements privés.', 10, 6, 10),
(9, 'Menu EVJF / EVG Festif', 10, 30, 'Menu festif idéal pour une soirée entre amis.', 11, 4, 7);

-- --------------------------------------------------------

--
-- Structure de la table `menu_plat`
--

CREATE TABLE `menu_plat` (
  `menu_id` int(11) NOT NULL,
  `plat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `menu_plat`
--

INSERT INTO `menu_plat` (`menu_id`, `plat_id`) VALUES
(1, 2),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 15),
(1, 16),
(1, 18),
(1, 20),
(1, 21),
(1, 25),
(1, 33),
(2, 3),
(2, 4),
(2, 12),
(2, 14),
(2, 15),
(2, 16),
(2, 18),
(2, 20),
(2, 21),
(2, 24),
(2, 32),
(2, 33),
(3, 6),
(3, 10),
(3, 11),
(3, 13),
(3, 14),
(3, 16),
(3, 18),
(3, 20),
(3, 23),
(3, 28),
(3, 29),
(4, 2),
(4, 6),
(4, 8),
(4, 9),
(4, 14),
(4, 16),
(4, 18),
(4, 21),
(4, 22),
(4, 26),
(4, 31),
(5, 5),
(5, 6),
(5, 8),
(5, 14),
(5, 16),
(5, 18),
(5, 22),
(5, 23),
(5, 31),
(6, 3),
(6, 4),
(6, 8),
(6, 9),
(6, 14),
(6, 15),
(6, 16),
(6, 18),
(6, 21),
(6, 22),
(6, 25),
(6, 33),
(7, 3),
(7, 5),
(7, 6),
(7, 8),
(7, 12),
(7, 14),
(7, 16),
(7, 18),
(7, 20),
(7, 21),
(7, 24),
(7, 33),
(8, 5),
(8, 6),
(8, 11),
(8, 13),
(8, 14),
(8, 16),
(8, 18),
(8, 24),
(8, 28),
(8, 31),
(9, 2),
(9, 4),
(9, 8),
(9, 12),
(9, 14),
(9, 15),
(9, 16),
(9, 18),
(9, 20),
(9, 21),
(9, 22),
(9, 23),
(9, 33);

-- --------------------------------------------------------

--
-- Structure de la table `plat`
--

CREATE TABLE `plat` (
  `plat_id` int(11) NOT NULL,
  `titre_plat` varchar(50) NOT NULL,
  `photo` blob DEFAULT NULL,
  `categorie` varchar(20) NOT NULL DEFAULT 'plat'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `plat`
--

INSERT INTO `plat` (`plat_id`, `titre_plat`, `photo`, `categorie`) VALUES
(2, 'Verrine saumon avocat', NULL, 'entree'),
(3, 'Mini wrap poulet crudités', NULL, 'entree'),
(4, 'Brochettes caprese (tomate, mozzarella, basilic)', NULL, 'entree'),
(5, 'Houmous et légumes croquants', NULL, 'entree'),
(6, 'Salade quinoa légumes grillés', NULL, 'entree'),
(7, 'Filet de boeuf sauce morilles', NULL, 'plat'),
(8, 'Poulet rôti aux herbes', NULL, 'plat'),
(9, 'Saumon au citron et aneth', NULL, 'plat'),
(10, 'Lasagnes végétariennes', NULL, 'plat'),
(11, 'Curry de légumes vegan', NULL, 'plat'),
(12, 'Tajine poulet citron', NULL, 'plat'),
(13, 'Dhal de lentilles vegan', NULL, 'plat'),
(14, 'Riz basmati aux épices', NULL, 'plat'),
(15, 'Gratin dauphinois', NULL, 'plat'),
(16, 'Légumes rôtis de saison', NULL, 'plat'),
(18, 'Salade de fruits frais', NULL, 'dessert'),
(20, 'Tarte aux fruits', NULL, 'dessert'),
(21, 'Mousse au chocolat', NULL, 'dessert'),
(22, 'Pomme de terre grenaille', NULL, 'plat'),
(23, 'Salade verte vinaigrette', NULL, 'entree'),
(24, 'Couscous aux légumes', NULL, 'plat'),
(25, 'Feuilletés chèvre miel', NULL, 'entree'),
(26, 'Oeufs brouillés aux fines herbes', NULL, 'plat'),
(27, 'Bagel saumon fromage frais', NULL, 'plat'),
(28, 'Avocado toast', NULL, 'plat'),
(29, 'Granola maison et yaourt', NULL, 'dessert'),
(31, 'Smoothie fruits rouges', NULL, 'dessert'),
(32, 'Brick au thon', NULL, 'entree'),
(33, 'Tiramisu', NULL, 'dessert');

-- --------------------------------------------------------

--
-- Structure de la table `plat_allergene`
--

CREATE TABLE `plat_allergene` (
  `plat_id` int(11) NOT NULL,
  `allergene_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `plat_allergene`
--

INSERT INTO `plat_allergene` (`plat_id`, `allergene_id`) VALUES
(2, 4),
(3, 1),
(4, 7),
(5, 11),
(7, 7),
(9, 4),
(10, 1),
(10, 7),
(15, 7),
(20, 1),
(20, 3),
(20, 7),
(21, 3),
(21, 7),
(23, 10),
(24, 1),
(25, 1),
(25, 7),
(26, 3),
(27, 1),
(27, 4),
(27, 7),
(28, 1),
(29, 1),
(29, 7),
(29, 8),
(32, 1),
(32, 4),
(33, 1),
(33, 3),
(33, 7);

-- --------------------------------------------------------

--
-- Structure de la table `plat_horaire`
--

CREATE TABLE `plat_horaire` (
  `plat_id` int(11) NOT NULL,
  `horaire_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `regime`
--

CREATE TABLE `regime` (
  `regime_id` int(11) NOT NULL,
  `libelle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `regime`
--

INSERT INTO `regime` (`regime_id`, `libelle`) VALUES
(4, 'Aucun régime'),
(9, 'Casher'),
(8, 'Halal'),
(7, 'Sans gluten'),
(6, 'Vegan'),
(5, 'Végétarien');

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `libelle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`role_id`, `libelle`) VALUES
(3, 'admin'),
(2, 'employee'),
(1, 'user');

-- --------------------------------------------------------

--
-- Structure de la table `theme`
--

CREATE TABLE `theme` (
  `theme_id` int(11) NOT NULL,
  `libelle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `theme`
--

INSERT INTO `theme` (`theme_id`, `libelle`) VALUES
(5, 'Anniversaire'),
(6, 'Baptême'),
(12, 'Brunch'),
(8, 'Cocktail d\'entreprise'),
(7, 'EVJF / EVG'),
(4, 'Mariage'),
(11, 'Repas de gala'),
(9, 'Séminaire'),
(10, 'Soirée privée');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `utilisateur_id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `ville` varchar(50) DEFAULT NULL,
  `adresse_postale` varchar(50) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`utilisateur_id`, `email`, `password`, `nom`, `prenom`, `telephone`, `ville`, `adresse_postale`, `role_id`, `reset_token`, `reset_expires`, `actif`) VALUES
(5, 'user@demo.fr', 'User123!', '', NULL, NULL, NULL, NULL, 1, NULL, NULL, 1),
(6, 'admin@demo.fr', 'Admin123!', '', NULL, NULL, NULL, NULL, 3, NULL, NULL, 1),
(7, 'employe@demo.fr', 'Employe123!', '', NULL, NULL, NULL, NULL, 2, NULL, NULL, 1),
(9, 'mathieu.palle@outlook.fr', 'Mathieudu13400!', 'Palle', 'Mathieu', '0650504430', NULL, 'JHGFDSGH', 1, NULL, NULL, 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `allergene`
--
ALTER TABLE `allergene`
  ADD PRIMARY KEY (`allergene_id`),
  ADD UNIQUE KEY `libelle` (`libelle`);

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`avis_id`),
  ADD KEY `fk_avis_utilisateur` (`utilisateur_id`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`commande_id`),
  ADD KEY `fk_commande_utilisateur` (`utilisateur_id`),
  ADD KEY `fk_commande_menu` (`menu_id`);

--
-- Index pour la table `commande_suivi`
--
ALTER TABLE `commande_suivi`
  ADD PRIMARY KEY (`suivi_id`),
  ADD KEY `commande_id` (`commande_id`);

--
-- Index pour la table `horaire`
--
ALTER TABLE `horaire`
  ADD PRIMARY KEY (`horaire_id`);

--
-- Index pour la table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`menu_id`),
  ADD KEY `fk_menu_regime` (`regime_id`),
  ADD KEY `fk_menu_theme` (`theme_id`);

--
-- Index pour la table `menu_plat`
--
ALTER TABLE `menu_plat`
  ADD PRIMARY KEY (`menu_id`,`plat_id`),
  ADD KEY `fk_menu_plat_plat` (`plat_id`);

--
-- Index pour la table `plat`
--
ALTER TABLE `plat`
  ADD PRIMARY KEY (`plat_id`);

--
-- Index pour la table `plat_allergene`
--
ALTER TABLE `plat_allergene`
  ADD PRIMARY KEY (`plat_id`,`allergene_id`),
  ADD KEY `fk_plat_allergene_allergene` (`allergene_id`);

--
-- Index pour la table `plat_horaire`
--
ALTER TABLE `plat_horaire`
  ADD PRIMARY KEY (`plat_id`,`horaire_id`),
  ADD KEY `fk_plat_horaire_horaire` (`horaire_id`);

--
-- Index pour la table `regime`
--
ALTER TABLE `regime`
  ADD PRIMARY KEY (`regime_id`),
  ADD UNIQUE KEY `libelle` (`libelle`);

--
-- Index pour la table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `libelle` (`libelle`);

--
-- Index pour la table `theme`
--
ALTER TABLE `theme`
  ADD PRIMARY KEY (`theme_id`),
  ADD UNIQUE KEY `libelle` (`libelle`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`utilisateur_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_utilisateur_role` (`role_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `allergene`
--
ALTER TABLE `allergene`
  MODIFY `allergene_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `commande_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `commande_suivi`
--
ALTER TABLE `commande_suivi`
  MODIFY `suivi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `horaire`
--
ALTER TABLE `horaire`
  MODIFY `horaire_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `menu`
--
ALTER TABLE `menu`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `plat`
--
ALTER TABLE `plat`
  MODIFY `plat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `regime`
--
ALTER TABLE `regime`
  MODIFY `regime_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `theme`
--
ALTER TABLE `theme`
  MODIFY `theme_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `utilisateur_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `fk_avis_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`utilisateur_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `fk_commande_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`),
  ADD CONSTRAINT `fk_commande_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`utilisateur_id`);

--
-- Contraintes pour la table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `fk_menu_regime` FOREIGN KEY (`regime_id`) REFERENCES `regime` (`regime_id`),
  ADD CONSTRAINT `fk_menu_theme` FOREIGN KEY (`theme_id`) REFERENCES `theme` (`theme_id`);

--
-- Contraintes pour la table `menu_plat`
--
ALTER TABLE `menu_plat`
  ADD CONSTRAINT `fk_menu_plat_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_menu_plat_plat` FOREIGN KEY (`plat_id`) REFERENCES `plat` (`plat_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `plat_allergene`
--
ALTER TABLE `plat_allergene`
  ADD CONSTRAINT `fk_plat_allergene_allergene` FOREIGN KEY (`allergene_id`) REFERENCES `allergene` (`allergene_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_plat_allergene_plat` FOREIGN KEY (`plat_id`) REFERENCES `plat` (`plat_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `plat_horaire`
--
ALTER TABLE `plat_horaire`
  ADD CONSTRAINT `fk_plat_horaire_horaire` FOREIGN KEY (`horaire_id`) REFERENCES `horaire` (`horaire_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_plat_horaire_plat` FOREIGN KEY (`plat_id`) REFERENCES `plat` (`plat_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `fk_utilisateur_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
