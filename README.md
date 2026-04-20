# LMS simple en PHP avec PDO

Projet réalisé à partir du cahier des charges : plateforme web de gestion de cours en ligne avec rôles **admin** et **student**.

## Fonctionnalités incluses

- inscription utilisateur
- connexion / déconnexion
- gestion de session
- protection des pages selon le rôle
- admin : ajouter, modifier, supprimer, lister les cours
- étudiant : consulter les cours, s'inscrire, se désinscrire, voir ses cours inscrits

## Structure

```text
projet/
├── admin/
├── assets/css/
├── config/
├── includes/
├── student/
├── index.php
├── login.php
├── logout.php
├── register.php
└── database.sql
```

## Installation

1. Copier le dossier dans `htdocs` si vous utilisez XAMPP.
2. Créer la base de données avec le fichier `database.sql`.
3. Vérifier `config/database.php` :
   - host
   - dbname
   - username
   - password
4. Ouvrir dans le navigateur :
   - `http://localhost/projet/`

## Comptes de test

- **Admin** : `admin@lms.com` / `123456`
- **Étudiant** : `student@lms.com` / `123456`

## Remarque

J'ai gardé le projet volontairement simple pour respecter le minimum demandé dans le cahier des charges. J'ai ajouté juste le nécessaire pour que le flux soit complet :
- messages flash
- protection des pages par rôle
- jeu de données de démonstration

Si vous placez le dossier sous un autre nom que `projet`, il faudra remplacer ce chemin dans les liens des fichiers PHP.
