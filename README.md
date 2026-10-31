# Projet ECF – Vite & Gourmand

Application web développée dans le cadre de la formation Développeur Web et Web Mobile STUDI.
Elle permet à l'entreprise « Vite & Gourmand » de présenter ses menus et de faciliter la prise de commande en ligne.

- Application en ligne : https://vite-gourmand-mathieu-db469fca4eaf.herokuapp.com/
- Dépôt GitHub : https://github.com/Mathieupalle/vite-gourmand

Comptes de démonstration (détaillés dans le `Manuel d'utilisation.pdf`) :

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur | admin@demo.fr | Admin1234! |
| Employé | employe@demo.fr | Employe1234! |
| Utilisateur | user@demo.fr | User12345! |

## Environnement local avec XAMPP (macOS)

1. Installer XAMPP (Apache + MySQL + PHP)
2. Installer Composer pour gérer les dépendances PHP
3. Installer Git pour le contrôle de version
4. Cloner le projet dans le dossier `htdocs` de XAMPP :

```bash
git clone https://github.com/Mathieupalle/vite-gourmand.git
cd vite-gourmand
composer install
```

5. Créer le fichier `config.local.php` (ignoré par Git) à partir du modèle :

```bash
cp config.local.example.php config.local.php
```

6. Renseigner l'URI MongoDB dans `config.local.php` (facultatif : elle ne sert qu'aux statistiques de l'administrateur). Remplacer la valeur vide du tableau final :

```php
return [
    'MONGODB_URI' => 'mongodb+srv://UTILISATEUR:MOT_DE_PASSE@CLUSTER.mongodb.net/?appName=Cluster0'
];
```

7. Dans phpMyAdmin (onglet SQL), créer la base et l'utilisateur utilisés par `config.local.php`, puis importer `sql/01.schema.sql` et `sql/02.insert.sql` dans la base `vite_gourmand` :

```sql
CREATE DATABASE vite_gourmand CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'siteweb'@'127.0.0.1' IDENTIFIED BY 'motdepasse';
GRANT ALL PRIVILEGES ON vite_gourmand.* TO 'siteweb'@'127.0.0.1';
```

Le mot de passe doit être le même que `DB_PASS` dans `config.local.php`.
8. Démarrer Apache et MySQL via XAMPP, puis ouvrir `http://localhost/vite-gourmand/public`

## Environnement local avec Docker

Prérequis : Docker Desktop, ports 8080 et 3307 libres (la base Docker utilise le port 3307 pour ne pas gêner MySQL de XAMPP).

```bash
git clone https://github.com/Mathieupalle/vite-gourmand.git
cd vite-gourmand
cp config.local.example.php config.local.php
docker compose up -d --build
docker compose exec app composer install
```

Après environ 30 secondes (initialisation de la base avec les fichiers du dossier `sql/`), ouvrir `http://localhost:8080`.
Pour réinitialiser la base : `docker compose down -v`.

## Production (Heroku)

Déploiement : `git push heroku main`. La configuration passe par des variables d'environnement (jamais dans le code) :

| Variable | Rôle |
|---|---|
| `JAWSDB_URL` | Connexion MySQL (ajoutée par l'add-on JawsDB) |
| `MONGODB_URI` | Connexion MongoDB Atlas (statistiques) |
| `BASE_URL` | Racine des URL du site |
| `APP_URL` | Adresse complète du site, utilisée dans les liens des emails |
| `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `SMTP_FROM` | Envoi des emails (PHPMailer) |
| `CONTACT_TO` | Adresse qui reçoit le formulaire de contact (sinon `SMTP_FROM`) |

Pour recopier toutes les commandes MySQL dans MongoDB (création ou mise à jour) :
`heroku run php scripts/migrateOrders.php -a vite-gourmand-mathieu`

## Historique Git

La version évaluée à l'ECF (février 2026) était incomplète. À partir de la correction du formateur,
j'ai repris le projet en grande partie, recréé ce dépôt et versionné la nouvelle version par fonctionnalité.

## Tests

`vendor/bin/phpunit` (ou `docker compose exec app vendor/bin/phpunit` avec Docker)
