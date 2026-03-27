# Projet ECF entreprise "Vite & Gourmand"
Application web développée dans le cadre de la formation Développeur Web et Web Mobile STUDI.
Elle permet à l’entreprise "Vite & Gourmand" de présenter ses menus et de faciliter la prise de commande en ligne.

---

# Configuration de mon environnement de travail en local (macOS / XAMPP)

1. Installer XAMPP (Apache + MySQL + PHP)
2. Installer Composer pour gérer les dépendances PHP
3. Installer Git pour le contrôle de version
4. Cloner le projet :

      git clone <url-du-repo>
      cd vite-gourmand
      composer install

5. Configurer les variables d’environnement locales dans config.local.php :

      <?php
      define('BASE_URL', '/vite-gourmand/public');
      define('DB_HOST', '127.0.0.1');
      define('DB_NAME', 'vite_gourmand');
      define('DB_USER', 'siteweb');
      define('DB_PASS', '****************');
      define('MONGODB_URI', 'mongodb+srv://vitegourmand:*****************@cluster0.ptlv7eh.mongodb.net/?appName=Cluster0&tlsAllowInvalidCertificates=true');

6. Démarrer Apache et MySQL via XAMPP.
