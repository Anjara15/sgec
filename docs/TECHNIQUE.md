# Documentation technique — SGEC

## Stack

- PHP 8.2+
- Symfony 7.4 (compatible Symfony 6+ patterns)
- Doctrine ORM 3
- Twig + Bootstrap 5
- Symfony Security / Form / Mailer / Validator
- PHPUnit

## Architecture

```
src/
  Entity/          # Modèle métier
  Enum/            # Types, statuts, priorités
  Repository/      # Accès données + recherche
  Form/            # Formulaires Symfony
  Controller/      # MVC web + Api/
  Service/         # Workflow, notifications, journal, upload, stats
  Security/        # Contrôle statut utilisateur
  DataFixtures/    # Jeu de données de démo
templates/         # Vues Twig
migrations/        # Schéma Doctrine
tests/             # Tests unitaires / fonctionnels
```

## Rôles Symfony

| Rôle | Accès |
|------|--------|
| `ROLE_ADMIN` | Administration complète |
| `ROLE_RESPONSABLE` | Validation, archivage, rapports |
| `ROLE_AGENT_COURRIER` | Création, édition, transmission |
| `ROLE_USER` | Consultation, notifications |

Les permissions fines sont aussi portées par l'entité `Role` (JSON) et la méthode `User::hasPermission()`.

## Workflow courrier

`brouillon → enregistre → en_validation → (valide | rejete | a_corriger) → en_transmission → recu → traite → archive`

L'archivage est **logique** (`statut = archive` + `dateArchivage`), jamais une suppression physique.

## API REST

Authentification HTTP Basic.

- `GET /api/courriers`
- `GET /api/courriers/{id}`
- `GET /api/services`
- `GET /api/users` (admin)

## Sécurité

- Authentification formulaire + CSRF
- Hash auto des mots de passe
- Contrôle d'accès serveur (`access_control` + `IsGranted`)
- Doctrine (requêtes paramétrées) → protection injection SQL
- Twig auto-escape → protection XSS
- Tokens CSRF sur actions sensibles

## Commandes utiles

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
php bin/console cache:clear
php bin/phpunit
php -S 127.0.0.1:8000 -t public
```

## Comptes de démo

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| admin@sgec.local | Admin123! | Administrateur |
| agent@sgec.local | Agent123! | Agent de courrier |
| responsable@sgec.local | Resp123! | Responsable |
| user@sgec.local | User123! | Utilisateur |
