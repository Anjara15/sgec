# SGEC — Système de Gestion Électronique des Courriers

Application Symfony complète pour l'enregistrement, la validation, la transmission, l'archivage et le suivi des courriers.

## Prérequis

- PHP 8.2+
- Composer
- MySQL 8+ (recommandé) **ou** SQLite pour démarrer rapidement

## Installation

```bash
composer install
# Configurer DATABASE_URL dans .env (MySQL ou SQLite)
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n
php bin/console doctrine:fixtures:load -n
php -S 127.0.0.1:8000 -t public
```

Ouvrir [http://127.0.0.1:8000](http://127.0.0.1:8000)

## Comptes de démonstration

| Email | Mot de passe | Profil |
|-------|--------------|--------|
| admin@sgec.local | Admin123! | Administrateur |
| agent@sgec.local | Agent123! | Agent de courrier |
| responsable@sgec.local | Resp123! | Responsable de service |
| user@sgec.local | User123! | Utilisateur |

## Fonctionnalités

- CRUD User, Role, Service, Courrier, Paramètres
- Workflow validation (approuver / rejeter / corriger)
- Transmissions + réceptions
- Historique des statuts + journal d'activité
- Notifications internes + email (Symfony Mailer)
- Archivage logique + recherche archives
- Recherche multicritère
- Export / impression (fiche, accusé, bordereau, CSV, rapport)
- API REST JSON (`/api/...`)
- Tests PHPUnit
- Documentation : `docs/TECHNIQUE.md`

## Structure

Voir `docs/TECHNIQUE.md` pour l'architecture détaillée.
