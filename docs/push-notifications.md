# Notifications push KC_SMA

Les notifications push système peuvent être reçues lorsque l’application est fermée, si le navigateur et le système d’exploitation prennent en charge Web Push. Le serveur PHP doit rester actif et avoir accès à Internet pour envoyer les notifications. Le navigateur ou l’appareil doit aussi être en ligne, avoir autorisé les notifications KC_SMA et, sur iPhone/iPad, KC_SMA doit être installée sur l’écran d’accueil.

## Configuration VAPID

1. Depuis la racine du projet, générez une paire VAPID :

   ```powershell
   $env:OPENSSL_CONF = 'C:\xampp\php\extras\openssl\openssl.cnf'
   C:\xampp\php\php.exe scripts\generate_vapid_keys.php
   ```

   Si XAMPP est installé ailleurs, adaptez le chemin du fichier `openssl.cnf`.

2. Copiez les trois lignes affichées dans `.env.local` :

   ```ini
   PUSH_VAPID_PUBLIC_KEY=...
   PUSH_VAPID_PRIVATE_KEY=...
   PUSH_VAPID_SUBJECT=mailto:admin@votre-domaine.example
   ```

3. Gardez la clé privée dans `.env.local` sur le serveur uniquement. Ne la publiez pas et ne la commitez jamais. La clé publique est envoyée au navigateur pour créer l’abonnement.

4. Redémarrez Apache après modification de la configuration, puis activez « Utiliser la sonnerie du système » dans le menu des notifications sur chaque appareil.

Utilisez le même couple de clés VAPID en production tant que les abonnements des appareils sont conservés. Une rotation des clés oblige les utilisateurs à se réabonner.

## Notifications d’événements planifiés

Les nouveaux messages et messages internes déclenchent un push à leur création. Les notifications d’événements sont dues à l’heure de début; pour les envoyer même si aucun utilisateur n’ouvre l’application, configurez l’exécution périodique suivante, idéalement chaque minute :

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\kc_sma\scripts\send_due_event_pushes.php
```

Le script est réservé à la ligne de commande et traite uniquement les événements devenus dus pour les comptes actifs de l’école.

Web Push ne permet pas à KC_SMA de choisir une sonnerie particulière : le navigateur applique les réglages de notification du système.
