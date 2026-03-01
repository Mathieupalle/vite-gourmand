-- ROLE
INSERT INTO role (role_id, libelle) VALUES
(3, 'admin'),
(2, 'employee'),
(1, 'user');

-- UTILISATEUR
INSERT INTO utilisateur (utilisateur_id, email, password, prenom, telephone, ville, pays, adresse_postale, role_id) VALUES
(5, 'user@demo.fr', 'User123!', NULL, NULL, NULL, NULL, NULL, 1),
(6, 'admin@demo.fr', 'Admin123!', NULL, NULL, NULL, NULL, NULL, 3),
(7, 'employe@demo.fr', 'Employe123!', NULL, NULL, NULL, NULL, NULL, 2),

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
(8, 'Cocktail d''entreprise'),
(7, 'EVJF / EVG'),
(4, 'Mariage'),
(11, 'Repas de gala'),
(9, 'Séminaire'),
(10, 'Soirée privée');

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
(15, 'Lundi', '09:00:00', '18:00:00'),
(16, 'Mardi', '09:00:00', '18:00:00'),
(17, 'Mercredi', '09:00:00', '18:00:00'),
(18, 'Jeudi', '09:00:00', '18:00:00'),
(19, 'Vendredi', '09:00:00', '18:00:00'),
(20, 'Samedi', '10:00:00', '16:00:00'),
(21, 'Dimanche', '00:00:00', '00:00:00');

-- PLAT
INSERT INTO plat (plat_id, titre_plat, photo) VALUES
(2,'Verrine saumon avocat',NULL),
(3,'Mini wrap poulet crudités',NULL),
(4,'Brochettes caprese (tomate, mozzarella, basilic)',NULL),
(5,'Houmous et légumes croquants',NULL),
(6,'Salade quinoa légumes grillés',NULL),
(7,'Filet de boeuf sauce morilles',NULL),
(8,'Poulet rôti aux herbes',NULL),
(9,'Saumon au citron et aneth',NULL),
(10,'Lasagnes végétariennes',NULL),
(11,'Curry de légumes vegan',NULL),
(12,'Tajine poulet citron',NULL),
(13,'Dhal de lentilles vegan',NULL),
(14,'Riz basmati aux épices',NULL),
(15,'Gratin dauphinois',NULL),
(16,'Légumes rôtis de saison',NULL),
(18,'Salade de fruits frais',NULL),
(20,'Tarte aux fruits',NULL),
(21,'Mousse au chocolat',NULL),
(22,'Pomme de terre grenaille',NULL),
(23,'Salade verte vinaigrette',NULL),
(24,'Couscous aux légumes',NULL),
(25,'Feuilletés chèvre miel',NULL),
(26,'Oeufs brouillés aux fines herbes',NULL),
(27,'Bagel saumon fromage frais',NULL),
(28,'Avocado toast',NULL),
(29,'Granola maison et yaourt',NULL),
(31,'Smoothie fruits rouges',NULL),
(32,'Brick au thon',NULL),
(33,'Tiramisu',NULL);

-- MENU
INSERT INTO menu (menu_id, titre, nombre_personne_minimum, prix_par_personne, description, quantite_restante, regime_id, theme_id) VALUES
(1,'Menu Mariage Prestige',50,65,'Menu pour mariage et grandes réceptions.',5,4,4),
(2,'Menu Cocktail d''entreprise',20,35,'Assortiment de pièces salées et sucrées',15,4,8),
(3,'Menu Séminaire ',15,30,'Menu adapté aux séminaires d''entreprise.',10,5,9),
(4,'Menu Brunch',10,30,'Brunch composé d''options sans gluten.',15,7,12),
(5,'Menu repas de Gala ',20,40,'Menu adapté aux évènements traditionnels casher.',7,9,11),
(6,'Menu Baptème',20,30,'Menu familial adapté aux réceptions de baptème.',12,4,6),
(7,'Menu Anniversaire',20,40,'Menu adapté aux évènements familiaux halal.',8,8,5),
(8,'Menu soirée privée',10,25,'Menu 100% vegan pour évènements privés.',10,6,10),
(9,'Menu EVJF / EVG Festif',10,30,'Menu festif idéal pour une soirée entre amis.',11,4,7);
