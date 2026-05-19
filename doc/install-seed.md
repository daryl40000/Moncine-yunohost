# Graine d’installation automatique

## Objectif

Lors d’une **première installation** du paquet YunoHost, importer automatiquement :

1. le **catalogue partagé** (CSV export admin) ;
2. les **affiches** (archive ZIP au format Moncine).

Les instances qui ont déjà un catalogue ne sont jamais modifiées.

## Déclenchement

| Événement | Graine appliquée ? |
|-----------|-------------------|
| `yunohost app install` | Oui, si catalogue vide + fichiers présents |
| `yunohost app upgrade` | Non |
| Import manuel `/import.php` | Non (inchangé) |

Commande interne : `php lib/cli/install-seed.php` (appelée par `scripts/install`).

## Sécurités

- Métadonnée SQLite `install_seed_applied` après succès.
- Abandon si `SELECT COUNT(*) FROM oeuvres` > 0.
- Pas de compte administrateur requis (import système).

## Fichiers

Voir [install_seed/README.md](../install_seed/README.md).
