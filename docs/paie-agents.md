# Paie des agents

## Installation

Sur la base de données active, exécuter une fois `app/Config/migration_create_agent_payroll.sql`, puis `app/Config/migration_create_cash_outflows_approval.sql` (par exemple depuis phpMyAdmin). La première migration crée les préférences salariales et les fiches mensuelles, et initialise en USD les caisses et banques déjà enregistrées, conformément au choix de l’établissement. La seconde ajoute les sorties de caisse et leur circuit d’approbation.

## Utilisation

Le module `/paies-agents` est disponible pour le comptable et l’administrateur de l’école. Le comptable peut consulter la liste des agents et modifier leur salaire de base et leur devise, sans pouvoir créer des agents ni gérer leurs comptes utilisateur.

Dans `/ecoles/settings`, l’administrateur désigne un ou plusieurs rôles approbateurs parmi les rôles disposant d’un compte actif dans l’école. Un seul utilisateur ayant l’un de ces rôles doit approuver chaque sortie. L’auteur d’une demande ne peut pas l’approuver lui-même. La page `/comptes` permet au comptable de soumettre les dépenses et aux approbateurs de traiter les demandes.

Chaque mois, préparer une fiche par agent, avec des primes et retenues facultatives. Le net à payer est calculé ainsi :

`salaire de base + total des primes - total des retenues`

La fiche conserve les montants et la devise au moment de sa création. Le paiement exige une caisse ou banque de la même devise et un solde suffisant. La soumission crée une demande de sortie en attente, sans débiter le compte. Un approbateur désigné confirme ou refuse la demande. Seule l’approbation débite le compte et marque la fiche payée dans une seule transaction. Une fiche payée ne peut pas être payée une seconde fois.

La devise d’une caisse ou banque ayant un historique de paie ou une sortie en attente ne peut pas être modifiée. Si son solde est non nul, le changement de devise exige une confirmation indiquant que le solde est déjà exprimé dans la devise choisie. Aucun montant n’est converti.
