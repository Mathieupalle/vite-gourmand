# Projet ECF entreprise "Vite & Gourmand"
Application web développée dans le cadre de la formation Développeur Web et Web Mobile STUDI.
Ce projet a pour objectif de concevoir une application web permettant à l’entreprise "Vite & Gourmand" de présenter ses menus et de faciliter la prise de commande en ligne.

---

## Accès au projet
- Dépôt GitHub : https://github.com/Mathieupalle/vite-gourmand
- Application en ligne : https://xxxx.herokuapp.com

---

## Architecture
Le projet repose sur une architecture client-serveur séparant le front-end (interface utilisateur) du back-end (logique métier et gestion des données).

---

## Technologies utilisées
- HTML / CSS / JavaScript : pour la structure, le style et l’interactivité de l’interface utilisateur  
- PHP : pour la logique côté serveur et le traitement des données  
- MySQL : base de données relationnelle prévue pour stocker les informations structurées  
- MongoDB : base de données non relationnelle prévue pour le stockage de données analytiques et statistiques (implémentation prévue dans une phase ultérieure du projet)

---

## Outils de développement
- Visual Studio Code : éditeur de code utilisé pour le développement
- Git : gestion de version via le Terminal (Bash) sur MacOS
- GitHub : hébergement du code et suivi des versions
- XAMPP : environnement serveur local pour les tests en phase de développement
- Heroku : plateforme cloud utilisée pour le déploiement et l’hébergement de l’application en production

---

## Environnement de travail et justification des choix
Le développement est réalisé en environnement local (XAMPP), tandis que le déploiement en production est effectué sur une plateforme cloud afin de séparer clairement les phases de développement et de mise en ligne.

Ce choix permet de disposer facilement d’un environnement complet regroupant Apache, PHP et MySQL, sans configuration complexe. 
L’utilisation d’un serveur local facilite les tests de l’application avant son déploiement en ligne.

L’éditeur de code utilisé est Visual Studio Code, choisi pour sa légèreté, sa simplicité d’utilisation et la disponibilité de nombreuses extensions utiles au développement web.

Le dépôt est hébergé sur GitHub pour centraliser le code, le sauvegarder en ligne et faciliter le partage du projet.

Ce fichier README.md a été rédigé pour documenter l’environnement de travail, les prérequis et les étapes nécessaires pour installer et lancer le projet en local, conformément aux bonnes pratiques de développement.

---

## Gestion de version
Le projet est versionné avec Git et hébergé sur GitHub.
La gestion de version est réalisée via le terminal macOS (Bash), permettant d’exécuter les commandes Git telles que `git init`, `git add`, `git commit`, `git pull` et `git push`.
Cette gestion permet de suivre les modifications du projet, d’assurer une sauvegarde en ligne et de respecter les bonnes pratiques de développement.

---

## Prérequis
- Un navigateur web (Google Chrome)
- Un serveur local (XAMPP)
- PHP
- MySQL

---

## Installation en local
1. Télécharger le dépôt GitHub
2. Placer le dossier du projet dans le dossier `htdocs` de XAMPP
3. Lancer XAMPP et démarrer Apache et MySQL
4. Accéder au projet via l’adresse `http://localhost`

---

## Déploiement
Le projet est déployé sur la plateforme cloud Heroku afin d’héberger l’application web complète.
Le déploiement est réalisé via Git depuis le terminal, permettant d’envoyer le code vers l’environnement de production. Heroku exécute ensuite l’application sur un serveur distant accessible en ligne.

---

## Statut du projet
Projet en cours de développement dans le cadre de l'ECF STUDI
