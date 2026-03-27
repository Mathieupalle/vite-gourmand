-- ROLE
CREATE TABLE role (
  role_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(50) NOT NULL UNIQUE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- UTILISATEUR
CREATE TABLE utilisateur (
  utilisateur_id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nom VARCHAR(50) NOT NULL,
  prenom VARCHAR(50) NULL,
  telephone VARCHAR(50) NULL,
  ville VARCHAR(50) NULL,
  adresse_postale VARCHAR(50) NULL,
  role_id INT NOT NULL,
  reset_token VARCHAR(64) NULL,
  reset_expire DATETIME NULL,
  actif BOOL NULL,
  CONSTRAINT fk_utilisateur_role FOREIGN KEY (role_id) REFERENCES role(role_id)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- REGIME
CREATE TABLE regime (
  regime_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(50) NOT NULL UNIQUE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- THEME
CREATE TABLE theme (
  theme_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(50) NOT NULL UNIQUE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MENU
CREATE TABLE menu (
  menu_id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(50) NOT NULL,
  nombre_personne_minimum INT NOT NULL,
  prix_par_personne DOUBLE NOT NULL,
  description VARCHAR(255) NULL,
  quantite_restante INT NULL,
  regime_id INT NOT NULL,
  theme_id INT NOT NULL,
  CONSTRAINT fk_menu_regime FOREIGN KEY (regime_id) REFERENCES regime(regime_id),
  CONSTRAINT fk_menu_theme FOREIGN KEY (theme_id) REFERENCES theme(theme_id)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PLAT
CREATE TABLE plat (
  plat_id INT AUTO_INCREMENT PRIMARY KEY,
  titre_plat VARCHAR(50) NOT NULL,
  photo BLOB NULL,
  categorie VARCHAR(50) NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MENU <-> PLAT (N..N)
CREATE TABLE menu_plat (
  menu_id INT NOT NULL,
  plat_id INT NOT NULL,
  PRIMARY KEY (menu_id, plat_id),
  CONSTRAINT fk_menu_plat_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE,
  CONSTRAINT fk_menu_plat_plat FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ALLERGENE
CREATE TABLE allergene (
  allergene_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(50) NOT NULL UNIQUE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PLAT <-> ALLERGENE (N..N)
CREATE TABLE plat_allergene (
  plat_id INT NOT NULL,
  allergene_id INT NOT NULL,
  PRIMARY KEY (plat_id, allergene_id),
  CONSTRAINT fk_plat_allergene_plat FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE,
  CONSTRAINT fk_plat_allergene_allergene FOREIGN KEY (allergene_id) REFERENCES allergene(allergene_id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- HORAIRE
CREATE TABLE horaire (
  horaire_id INT AUTO_INCREMENT PRIMARY KEY,
  jour VARCHAR(50) NOT NULL,
  heure_ouverture VARCHAR(50) NOT NULL,
  heure_fermeture VARCHAR(50) NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PLAT <-> HORAIRE (N..N)
CREATE TABLE plat_horaire (
  plat_id INT NOT NULL,
  horaire_id INT NOT NULL,
  PRIMARY KEY (plat_id, horaire_id),
  CONSTRAINT fk_plat_horaire_plat FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE,
  CONSTRAINT fk_plat_horaire_horaire FOREIGN KEY (horaire_id) REFERENCES horaire(horaire_id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- COMMANDE
CREATE TABLE commande (
  commande_id INT AUTO_INCREMENT PRIMARY KEY,
  numero_commande VARCHAR(50) NOT NULL UNIQUE,
  date_commande DATE NOT NULL,
  date_prestation DATE NOT NULL,
  heure_livraison VARCHAR(50) NULL,
  prix_menu DOUBLE NOT NULL,
  nombre_personne INT NOT NULL,
  prix_livraison DOUBLE NULL,
  statut VARCHAR(50) NOT NULL,
  pret_materiel BOOL NULL,
  restitution_materiel BOOL NULL,
  adresse_prestation VARCHAR(255) NOT NULL,
  ville_prestation VARCHAR(50) NOT NULL,
  distance_km DOUBLE NULL,
  remise DOUBLE NULL,
  date_livraison DATE NOT NULL,
  ville_livraison VARCHAR(50) NOT NULL,
  adresse_livraison VARCHAR(255) NOT NULL,
  location_materiel BOOL NULL,
  utilisateur_id INT NOT NULL,
  menu_id INT NOT NULL,
  CONSTRAINT fk_commande_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
  CONSTRAINT fk_commande_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- COMMANDE_SUIVI (historique des statuts)
CREATE TABLE commande_suivi (
  suivi_id INT AUTO_INCREMENT PRIMARY KEY,
  commande_id INT NOT NULL,
  statut VARCHAR(50) NOT NULL,
  date_modif DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  mode_contact VARCHAR(50) NULL,
  motif TEXT NULL,
  CONSTRAINT fk_commande_suivi_commande
    FOREIGN KEY (commande_id) REFERENCES commande(commande_id)
    ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AVIS
CREATE TABLE avis (
  avis_id INT AUTO_INCREMENT PRIMARY KEY,
  note VARCHAR(50) NULL,
  description VARCHAR(255) NULL,
  statut VARCHAR(50) NULL,
  utilisateur_id INT NOT NULL,
  commande_id INT NOT NULL,
  date_avis DATETIME NULL,
  CONSTRAINT fk_avis_commande FOREIGN KEY (commande_id) REFERENCES commande(commande_id) ON DELETE CASCADE,
  CONSTRAINT fk_avis_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;