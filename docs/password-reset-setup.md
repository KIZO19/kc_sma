# Réinitialisation du mot de passe

Le parcours de récupération utilise un lien aléatoire à usage unique envoyé à l’adresse courriel associée au compte. Le lien expire après 30 minutes. Les courriels et numéros de téléphone enregistrés peuvent servir à retrouver le compte ; les identifiants non reconnus reçoivent le même message de confirmation.

## Installation

1. Installer les dépendances PHP avec `composer install`.
2. Exécuter `app/Config/migration_create_password_reset.sql` sur la base de données active.
3. Renseigner les paramètres SMTP dans `.env.local` en s’appuyant sur `.env.example`.
4. Définir `APP_PUBLIC_URL` avec l’URL publique complète de l’application, jusqu’au répertoire `public` (par exemple `https://ecole.example/kc_sma/public`). Utiliser HTTPS en production.

`MAIL_SMTP_ENCRYPTION` accepte `tls`, `ssl` ou `none`. Le serveur SMTP, l’expéditeur et l’URL publique doivent être configurés pour activer l’envoi. Garder les identifiants SMTP secrets et ne pas les ajouter au dépôt.

Les demandes sont limitées à cinq par adresse IP sur quinze minutes et à une demande par compte sur deux minutes. Le mot de passe doit contenir au moins huit caractères.
