# Projet ECF entreprise "Vite & Gourmand"
Application web développée dans le cadre de la formation Développeur Web et Web Mobile STUDI.
Elle permet à l’entreprise "Vite & Gourmand" de présenter ses menus et de faciliter la prise de commande en ligne.

---

# Configuration de mon environnement de travail

En local (macOS / XAMPP)

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

---

En production (Heroku)

1. Déployer via Git : git push heroku main
2. Configurer les variables d’environnement dans Heroku Dashboard :

      - BASE_URL : https://vite-gourmand-mathieu-db469fca4eaf.herokuapp.com
      - DB_HOST : d1kb8x1fu8rhcnej.cbetxkdyhwsb.us-east-1.rds.amazonaws.com
      - DB_NAME : kxdo1g9nh3n1jekt
      - DB_PASS : ****************
      - DB_PORT : 3306
      - DB_USER : x9fu5b5l9sd09tzc
      - MONGODB_URI : mongodb+srv://vitegourmand:*****************@cluster0.ptlv7eh.mongodb.net/?appName=Cluster0&authSource=admin
      - JAWSDB_URL : mysql://x9fu5b5l9sd09tzc:****************@d1kb8x1fu8rhcnej.cbetxkdyhwsb.us-east-1.rds.amazonaws.com:3306/kxdo1g9nh3n1jekt
