# Swapify User

## Prérequis

Avant d'installer et d'exécuter ce projet, assurez-vous d'avoir les éléments suivants installés sur votre système :

- PHP 8
- Composer
- Symfony CLI
- Une base de données (MySQL, PostgreSQL, SQLite, etc.)
- Un compte Mailtrap pour gérer l'envoi d'e-mails (SMTP)

## Installation

Suivez ces étapes pour configurer et exécuter le projet :

1. **Configurer la base de données**  
   - Ouvrez le fichier `.env` et ajoutez les identifiants de connexion à votre base de données. Exemple :  
     ```
     DATABASE_URL="mysql://username:password@127.0.0.1:3306/nom_de_la_base"
     ```

2. **Configurer les identifiants SMTP pour l'envoi d'e-mails**  
   - Toujours dans `.env`, ajoutez vos identifiants SMTP Mailtrap :  
     ```
     MAILER_DSN=smtp://username:password@smtp.mailtrap.io:2525
     ```

3. **Installer les dépendances**  
   ```
   composer install
# Création du projet Symfony
composer create-project symfony/skeleton symfony_login

# Accéder au dossier du projet
cd swapify-main

# Création du super administrateur à partir de l'invite de commande
php bin/console app:create-super-admin



# Création du schéma de la base de données
php bin/console doctrine:schema:create

# Démarrer le serveur Symfony
symfony serve
