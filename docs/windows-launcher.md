# Lanceur Windows KC-SMA

`dist/KC-SMA-Lanceur.exe` est un lanceur pour une installation KC-SMA deja hebergee sur un serveur. Il demande l'adresse HTTP/HTTPS au premier lancement, l'enregistre dans le profil Windows de l'utilisateur, puis ouvre l'application dans son navigateur habituel.

Le lanceur ne contient pas l'application PHP, PHP, MySQL ni les donnees. Le serveur doit etre installe et accessible depuis les postes clients. L'adresse saisie doit pointer vers la racine publique de l'application, par exemple `http://192.168.1.10/kc_sma/public/`. L'adresse peut etre modifiee en relancant le fichier avec l'argument `/configurer`.

## Regenerer le fichier EXE

Depuis la racine du depot, sur Windows avec le compilateur .NET Framework :

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\packaging\windows\build.ps1
```

Le resultat est genere dans `dist/KC-SMA-Lanceur.exe`.
