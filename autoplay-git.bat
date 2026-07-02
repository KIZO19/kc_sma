@echo off
:: Changer l'encodage pour afficher correctement les émojis et accents
chcp 65001 > nul

echo 🚀 Lancement de la sauvegarde automatique Git...

:: 1. Ajouter tous les changements
git add .

:: 2. Récupérer la date et l'heure au format propre (JJ/MM/AAAA à HH:MM:SS)
for /f "tokens=1-4 delims=/ " %%a in ('date /t') do set mydate=%%a
for /f "tokens=1-2 delims=: " %%a in ('time /t') do set mytime=%%a
set message=Auto-commit du %date% à %time%

:: 3. Committer
git commit -m "%message%"

:: 4. Pousser vers GitHub (Ajusté sur ta branche 'main')
git push origin main

echo ✅ Sauvegarde automatique terminée avec succès !
pause