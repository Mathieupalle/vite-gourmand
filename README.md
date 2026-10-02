# Projet ECF – Vite & Gourmand

Application web développée dans le cadre de la formation Développeur Web et Web Mobile STUDI.
Elle permet à l'entreprise « Vite & Gourmand » de présenter ses menus et de faciliter la prise de commande en ligne.

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

7. Créer la base `vite_gourmand` dans phpMyAdmin, puis importer `sql/01.schema.sql` et `sql/02.insert.sql`
8. Démarrer Apache et MySQL via XAMPP, puis ouvrir `http://localhost/vite-gourmand/public`

## Environnement local avec Docker

Prérequis : Docker Desktop, ports 3306 et 8080 libres (arrêter MySQL de XAMPP si besoin).

```bash
git clone https://github.com/Mathieupalle/vite-gourmand.git
cd vite-gourmand
cp config.local.example.php config.local.php
docker compose up -d --build
docker compose exec app composer install
```

Après environ 30 secondes (initialisation de la base avec les fichiers du dossier `sql/`), ouvrir `http://localhost:8080`.
Pour réinitialiser la base : `docker compose down -v`.