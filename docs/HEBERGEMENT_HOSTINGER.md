# Hébergement sur Hostinger

## Préparer le compte

1. Dans hPanel, relier `ahdd.ahdd.net` à l’hébergement et activer le certificat SSL.
2. Créer une base MySQL et un utilisateur dans **Bases de données MySQL**. Noter le nom complet de la base, le nom complet de l’utilisateur, l’hôte MySQL indiqué et le mot de passe.
3. Sélectionner une version PHP 8.0 ou supérieure et activer `pdo_mysql`.

## Mettre les fichiers en ligne

1. Téléverser les fichiers du projet dans le dossier du site. Garder `public/`, `app/` et `vendor/` ensemble : le `.htaccess` à la racine dirige les requêtes vers `public/` et bloque l’accès web direct aux fichiers internes.
2. Ne pas téléverser `.env.local`, `.git/`, `.venv/`, `COMPTES_TEST.txt` ni les exports de base de données.
3. Copier `.env.example` en `.env.local` à la racine du projet et remplacer au minimum `DB_HOST`, `DB_NAME`, `DB_USER` et `DB_PASS` par les valeurs de hPanel. Laisser `BASE_URL=` vide pour le domaine à la racine et définir `APP_PUBLIC_URL=https://ahdd.ahdd.net`.
4. Protéger `.env.local` : il contient les identifiants de la base et des services externes. Ne jamais le publier dans Git ni le partager.
5. Importer l’export SQL complet du projet dans la base depuis phpMyAdmin. Les fichiers `migration_*.sql` sont des migrations et ne remplacent pas un export complet.
6. Vérifier la connexion sur `https://ahdd.ahdd.net`, puis configurer les paramètres SMTP et, si nécessaire, les fournisseurs SMS/WhatsApp dans `.env.local`.

Les téléversements faits par l’application doivent être accessibles en écriture par PHP dans `public/uploads/`. Ne rendez pas les autres dossiers du projet accessibles en écriture.
