# Moncine — édition paquet YunoHost

**Auteur :** Stéphane MATER  
**Licence :** [GNU General Public License v3.0 ou ultérieure](LICENSE) (GPL-3.0-or-later)

**Développement actif** de la future application YunoHost.

| Dossier | Rôle |
|---------|------|
| **`Moncine/`** (ici) | Paquet YunoHost, nouvelles fonctionnalités |
| **`Moncine (origine)/`** | Production figée My Webapp — export uniquement |

Voir aussi : [ROADMAP.md](ROADMAP.md), [LEGACY.md](LEGACY.md).

## Comprendre le code (par où commencer)

| Fichier | Rôle |
|---------|------|
| `lib/bootstrap.php` | Chargé par chaque page : config, base, connexion obligatoire |
| `lib/Auth.php` | Qui est connecté, login, pages publiques |
| `lib/UserContext.php` | ID utilisateur pour « Mes films » / envies |
| `lib/Database.php` | SQLite + migrations automatiques |
| `lib/FilmRepository.php` | Accès aux films de l’utilisateur courant |
| `www/*.php` | Une page = un fichier (contrôleur léger) |
| `templates/*.php` | HTML affiché (via `View::render`) |

## Structure

```text
Moncine/
├── www/              pages web
├── lib/              code PHP (+ cli/migrate.php)
├── sql/
│   ├── schema.sql    install fraîche
│   ├── migrations/   migrations paquet (001, 002…)
│   └── migrations_legacy/  anciennes 002–015 (non exécutées)
├── yunohost/         scripts install / upgrade / backup
├── data/             base SQLite (non versionnée)
├── install_seed/     CSV catalogue + ZIP affiches (install neuve uniquement)
└── doc/
```

### Installation neuve avec catalogue prérempli

Déposez votre export **CSV catalogue** et votre **ZIP affiches** dans `install_seed/` avant `yunohost app install` (voir [install_seed/README.md](install_seed/README.md)).  
L’import automatique ne s’exécute **pas** si la base contient déjà des œuvres.

## Tester en local

```bash
cd /chemin/vers/Moncine
php lib/cli/migrate.php --fresh   # première fois (sans moncine.db)
php -S localhost:8080 -t www
```

Ouvrir http://localhost:8080 — à la première visite, créez le **compte administrateur** (`/premier-compte.php`).

## Comptes utilisateurs (paquet 2.0)

- **Premier lancement** : `/premier-compte.php` (administrateur)
- **Connexion** : `/connexion.php`
- **Gestion des comptes** : `/utilisateurs.php` (admin uniquement)
- Chaque utilisateur a sa propre bibliothèque et ses envies

## Tests automatisés (PHPUnit)

Vérifie l’import/export (détection de format, parsing CSV, import bibliothèque et catalogue) sur une base SQLite temporaire.

```bash
cd /chemin/vers/Moncine
composer install
composer test
```

Prérequis : PHP 8.2+, extension `sqlite3`, [Composer](https://getcomposer.org/).

## Migrations SQL (paquet uniquement)

- **Install** : `sql/schema.sql` si la base est vide, puis `sql/migrations/*.sql`
- **Upgrade** : `php lib/cli/migrate.php` ou `yunohost/scripts/upgrade`
- Les fichiers dans `sql/migrations_legacy/` ne sont **pas** appliqués

## YunoHost

Paquet installable (format v2) à la racine du dépôt : `manifest.toml`, `scripts/`, `conf/`.

**Installation sur un serveur YunoHost :** voir [doc/packaging-yunohost.md](doc/packaging-yunohost.md).

```bash
sudo yunohost app install /chemin/vers/Moncine --force \
  -a "domain=moncine.votredomaine.tld&path=/&init_main_permission=visitors"
```

Variables utiles sur le serveur :

- `MONCINE_DATA_PATH` — base SQLite et clés API (`/home/yunohost.app/moncine/`)
- `MONCINE_BASE_URL` — URL publique (e-mails de réinitialisation)

## Migration depuis l’ancienne prod

Export CSV (+ affiches) depuis `Moncine (origine)`, puis import ici une fois l’outil prêt : [doc/migration-export-import.md](doc/migration-export-import.md).

## Prérequis

- PHP 8.1+ avec extension **sqlite3**
# Moncine
