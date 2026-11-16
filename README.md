# 2me

Une boutique fictive de vêtements et accessoires de seconde main, réalisée en PHP, MySQL, CSS et JavaScript.

## Lancer le site avec XAMPP

1. Démarrez Apache et MySQL depuis le panneau de contrôle XAMPP.
2. Ouvrez `http://localhost/phpmyadmin`, puis importez le fichier `database.sql`. Il crée la base `2me`, ses tables et les produits de démonstration.
3. Placez le dossier du projet dans `C:\xampp\htdocs\2me` (ou dans le dossier `htdocs` de votre installation).
4. Visitez `http://localhost/2me/`.

La configuration MySQL par défaut utilise `127.0.0.1`, l’utilisateur `root` et un mot de passe vide, comme dans une installation XAMPP standard. Vous pouvez la remplacer avec les variables d’environnement `DB_HOST`, `DB_NAME`, `DB_USER` et `DB_PASSWORD`. PHP doit avoir l’extension PDO MySQL activée.

## Fonctionnalités

- Catalogue de vêtements, chaussures et accessoires chargé depuis MySQL
- Filtres par catégorie et recherche
- Panier de session avec ajout, retrait et calcul du total
- Commandes et articles associés enregistrés en base de données
- Pièces uniques marquées comme vendues après commande
- Protection CSRF des formulaires
- Commande de démonstration (aucun paiement réel)
- Mise en page adaptée aux téléphones et ordinateurs

Les commandes de démonstration enregistrent le nom et l’adresse e-mail saisis, mais ne déclenchent aucun paiement.

## Publier sur GitHub

Créez un dépôt vide sur GitHub, puis ouvrez un terminal dans le dossier du projet et exécutez :

```bash
git init
git add .
git commit -m "Créer le site 2me"
git branch -M main
git remote add origin https://github.com/VOTRE-NOM/VOTRE-DEPOT.git
git push -u origin main
```

Remplacez l’adresse `origin` par celle de votre dépôt GitHub. Si Git vous demande de vous identifier, suivez la procédure proposée par GitHub.

