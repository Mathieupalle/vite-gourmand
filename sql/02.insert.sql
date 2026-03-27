-- REGIME
INSERT INTO regime (regime_id, libelle) VALUES
(4, 'Aucun régime'),
(9, 'Casher'),
(8, 'Halal'),
(7, 'Sans gluten'),
(6, 'Vegan'),
(5, 'Végétarien');

-- THEME
INSERT INTO theme (theme_id, libelle) VALUES
(5, 'Anniversaire'),
(6, 'Baptème'),
(12, 'Brunch'),
(8, 'Cocktail d\'entreprise'),
(7, 'EVJF / EVG'),
(4, 'Mariage'),
(11, 'Repas de gala'),
(9, 'Séminaire'),
(10, 'Soirée privée');

-- ROLE
INSERT INTO role (role_id, libelle) VALUES
(3, 'admin'),
(2, 'employe'),
(1, 'user');

-- ALLERGENE
INSERT INTO allergene (allergene_id, libelle) VALUES
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

-- HORAIRE
INSERT INTO horaire (horaire_id, jour, heure_ouverture, heure_fermeture) VALUES
(15, 'Lundi', '09:00', '18:00'),
(16, 'Mardi', '09:00', '18:00'),
(17, 'Mercredi', '09:00', '18:00'),
(18, 'Jeudi', '09:00', '18:00'),
(19, 'Vendredi', '09:00', '18:00'),
(20, 'Samedi', '10:00', '16:00'),
(21, 'Dimanche', '00:00', '00:00');

-- MENU
INSERT INTO menu (menu_id, titre, nombre_personne_minimum, prix_par_personne, description, quantite_restante, regime_id, theme_id) VALUES
(1, 'Menu Mariage Prestige', 50, 65, 'Menu pour mariage et grandes réceptions.', 5, 4, 4),
(2, 'Menu Cocktail d\'entreprise', 20, 35, 'Assortiment de pièces salées et sucrées', 15, 4, 8),
(3, 'Menu Séminaire ', 15, 30, 'Menu adapté aux séminaires d\'entreprise.', 10, 5, 9),
(4, 'Menu Brunch', 10, 30, 'Brunch composé d\'options sans gluten.', 15, 7, 12),
(5, 'Menu repas de Gala ', 20, 40, 'Menu adapté aux évènements traditionnels casher.', 7, 9, 11),
(6, 'Menu Baptème', 20, 30, 'Menu familial adapté aux réceptions de baptème.', 12, 4, 6),
(7, 'Menu Anniversaire', 20, 40, 'Menu adapté aux évènements familiaux halal.', 8, 8, 5),
(8, 'Menu soirée privée', 10, 25, 'Menu 100% vegan pour évènements privés.', 10, 6, 10),
(9, 'Menu EVJF / EVG Festif', 10, 30, 'Menu festif idéal pour une soirée entre amis.', 11, 4, 7);

-- PLAT
INSERT INTO plat (plat_id, titre_plat, photo, categorie) VALUES
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

-- RELATION MENU_PLAT
INSERT INTO menu_plat (menu_id, plat_id) VALUES
(1,2),(1,6),(1,7),(1,8),(1,9),(1,15),(1,16),(1,18),(1,20),(1,21),(1,25),(1,33),
(2,3),(2,4),(2,12),(2,14),(2,15),(2,16),(2,18),(2,20),(2,21),(2,24),(2,32),(2,33),
(3,6),(3,10),(3,11),(3,13),(3,14),(3,16),(3,18),(3,20),(3,23),(3,28),(3,29),
(4,2),(4,6),(4,8),(4,9),(4,14),(4,16),(4,18),(4,21),(4,22),(4,26),(4,31),
(5,5),(5,6),(5,8),(5,14),(5,16),(5,18),(5,22),(5,23),(5,31),
(6,3),(6,4),(6,8),(6,9),(6,14),(6,15),(6,16),(6,18),(6,21),(6,22),(6,25),(6,33),
(7,3),(7,5),(7,6),(7,8),(7,12),(7,14),(7,16),(7,18),(7,20),(7,21),(7,24),(7,33),
(8,5),(8,6),(8,11),(8,13),(8,14),(8,16),(8,18),(8,24),(8,28),(8,31),
(9,2),(9,4),(9,8),(9,12),(9,14),(9,15),(9,16),(9,18),(9,20),(9,21),(9,22),(9,23),(9,33);

-- RELATION PLAT_ALLERGENE
INSERT INTO plat_allergene (plat_id, allergene_id) VALUES
(2,4),(3,1),(4,7),(5,11),(7,7),(9,4),
(10,1),(10,7),(15,7),
(20,1),(20,3),(20,7),
(21,3),(21,7),
(23,10),(24,1),
(25,1),(25,7),
(26,3),
(27,1),(27,4),(27,7),
(28,1),
(29,1),(29,7),(29,8),
(32,1),(32,4),
(33,1),(33,3),(33,7);

-- UTILISATEUR
INSERT INTO utilisateur (utilisateur_id, email, password, nom, prenom, telephone, ville, adresse_postale, role_id, actif) VALUES
(1, 'user@demo.fr', '$2y$10$73NwSFxbvRM2bdlLf8qzZuD1LKVS8CImAUSlTv6qxsb/mPH7mj4fW', 'Palle', 'Mathieu', '0606060606', NULL, 'JHGFDSDFGHJ', 1, 1),
(2, 'employe@demo.fr', '$2y$10$FLZz89lEgdUtjfotBqwfT.GA7qlr9RwqXCm3hxRiTCYqS7XNewyvG', 'Palle', 'Mathieu', '0606060606', NULL, 'KJHGFDFG', 2, 1),
(3, 'admin@demo.fr', '$2y$10$B4IE5i8LkJGxa6BDJ5nLu.cbwcMjnx0lfvMul5kbjxwHUxazSf6OK', 'Palle', 'Mathieu', '0606060606', NULL, 'JHFDGH', 3, 1);