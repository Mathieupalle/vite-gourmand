-- ROLE
CREATE TABLE role (
  role_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(50) NOT NULL UNIQUE
);

-- UTILISATEUR
CREATE TABLE utilisateur (
  utilisateur_id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,   -- hash (même si le MCD dit "password", on stocke un hash)
  prenom VARCHAR(50) NULL,
  telephone VARCHAR(50) NULL,
  ville VARCHAR(50) NULL,
  pays VARCHAR(50) NULL,
  adresse_postale VARCHAR(50) NULL,
  role_id INT NOT NULL,
  CONSTRAINT fk_utilisateur_role FOREIGN KEY (role_id) REFERENCES role(role_id)
);

-- REGIME
CREATE TABLE regime (
  regime_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(50) NOT NULL UNIQUE
);

-- THEME
CREATE TABLE theme (
  theme_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(50) NOT NULL UNIQUE
);

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
);

-- PLAT
CREATE TABLE plat (
  plat_id INT AUTO_INCREMENT PRIMARY KEY,
  titre_plat VARCHAR(50) NOT NULL,
  photo BLOB NULL
);

-- MENU <-> PLAT (propose) (N..N)
CREATE TABLE menu_plat (
  menu_id INT NOT NULL,
  plat_id INT NOT NULL,
  PRIMARY KEY (menu_id, plat_id),
  CONSTRAINT fk_menu_plat_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE,
  CONSTRAINT fk_menu_plat_plat FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE
);

-- ALLERGENE
CREATE TABLE allergene (
  allergene_id INT AUTO_INCREMENT PRIMARY KEY,
  libelle VARCHAR(50) NOT NULL UNIQUE
);

-- PLAT <-> ALLERGENE (contient) (N..N)
CREATE TABLE plat_allergene (
  plat_id INT NOT NULL,
  allergene_id INT NOT NULL,
  PRIMARY KEY (plat_id, allergene_id),
  CONSTRAINT fk_plat_allergene_plat FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE,
  CONSTRAINT fk_plat_allergene_allergene FOREIGN KEY (allergene_id) REFERENCES allergene(allergene_id) ON DELETE CASCADE
);

-- HORAIRE
CREATE TABLE horaire (
  horaire_id INT AUTO_INCREMENT PRIMARY KEY,
  jour VARCHAR(50) NOT NULL,
  heure_ouverture VARCHAR(50) NOT NULL,
  heure_fermeture VARCHAR(50) NOT NULL
);

-- PLAT <-> HORAIRE (contient) (N..N)
CREATE TABLE plat_horaire (
  plat_id INT NOT NULL,
  horaire_id INT NOT NULL,
  PRIMARY KEY (plat_id, horaire_id),
  CONSTRAINT fk_plat_horaire_plat FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE,
  CONSTRAINT fk_plat_horaire_horaire FOREIGN KEY (horaire_id) REFERENCES horaire(horaire_id) ON DELETE CASCADE
);

-- COMMANDE
CREATE TABLE commande (
  num_commande VARCHAR(50) PRIMARY KEY,
  date_commande DATE NOT NULL,
  date_prestation DATE NOT NULL,
  heure_livraison VARCHAR(50) NULL,
  prix_menu DOUBLE NOT NULL,
  nombre_personne INT NOT NULL,
  prix_livraison DOUBLE NULL,
  statut VARCHAR(50) NOT NULL,
  prix_materiel BOOL NULL,
  location_materiel BOOL NULL,
  utilisateur_id INT NOT NULL,
  menu_id INT NOT NULL,
  CONSTRAINT fk_commande_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
  CONSTRAINT fk_commande_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id)
);

-- AVIS
CREATE TABLE avis (
  avis_id INT AUTO_INCREMENT PRIMARY KEY,
  note VARCHAR(50) NULL,
  description VARCHAR(50) NULL,
  statut VARCHAR(50) NULL,
  utilisateur_id INT NOT NULL,
  CONSTRAINT fk_avis_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id) ON DELETE CASCADE
);
