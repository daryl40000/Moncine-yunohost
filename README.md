# Moncine

**Version : 0.9.5**

**Auteur :** Stéphane MATER  
**Licence :** [GNU General Public License v3.0 ou ultérieure](LICENSE) (GPL-3.0-or-later)

Application web pour gérer une **dvdthèque personnelle** : films, envies, notes, enrichissement TMDB, import/export CSV, comptes utilisateurs.

---

## Fonctionnalités actuelles (v0.9)

| Domaine | Disponible |
|---------|------------|
| Collection & envies | Mes films, Mes envies, sagas, statistiques (dont temps de vision cumulé), **questionnaire du soir**, **listes imprimables** (PDF via le navigateur) |
| Prêts | Demandes entre amis, réservation, validation et retour (`/mes-prets.php`) |
| Stockage médias | Fichiers volumineux hors `www/` (`MONCINE_MEDIA_PATH`, admin **Gestion → Médias**) |
| Foyers & famille | Collection partagée par foyer ; envies et historique personnels |
| Catalogue partagé | Fiches œuvres, enrichissement TMDB / OMDB, affiches |
| Comptes | Connexion, rôles admin/utilisateur, gestion des comptes |
| Mots de passe & compte | Mon compte, changement, oublié par e-mail, reset admin, **suppression du compte** (utilisateurs) |
| Inscription publique | Confirmation e-mail, approbation admin optionnelle (si activée) |
| Exemplaire personnel | Support, format image/son (séparés du catalogue) |
| Maintenance catalogue | Doublons, fusion, journal admin, nettoyage affiches, sauvegarde / restauration base SQLite |
| Soumissions catalogue | Proposer une œuvre (utilisateur) ; validation admin ; **notifications** in-app + e-mail |
| Profil & recherche | Ville optionnelle ; recherche par pseudo/ville ; masquer son profil de la recherche |
| Amis & groupe famille | Demandes d’ami ; créer / rejoindre un groupe ; collection partagée |
| Envies du groupe | Voir les envies de tous les membres ; tri par demandes ; bouton « Moi aussi » |
| Partage visiteur | Lien lecture seule Mes films / Mes envies + fiche film (sans compte) |
| EAN catalogue | Plusieurs codes-barres par œuvre (DVD, Blu-ray, 4K) pour le catalogue |
| Données | Import / export CSV, affiches |

### Prochaines étapes (v0.8 → v1.0)

- ~~Soumissions au catalogue~~ (v0.7.4)
- ~~Profil ville & recherche utilisateurs~~ (v0.7.6)
- ~~Amis & groupes famille~~ (v0.7.7)
- ~~Envies du groupe & ajout rapide~~ (v0.7.8)
- ~~UX thème sombre & composant filtres (ui-pill)~~ (v0.7.9)
- ~~Sécurité sociale (recherche, blocage, limites)~~ (v0.7.10)
- ~~Partage visiteur~~ (v0.8.0)
- ~~EAN multiples par œuvre catalogue~~ (v0.8.0)
- ~~Versions recherchées sur les envies (support + EAN)~~ (v0.8.2)
- ~~Profil public utilisateur (amis / groupe)~~ (v0.8.3)
- ~~Temps de vision cumulé (statistiques)~~ (v0.8.4)
- ~~Sauvegarde / restauration base SQLite (admin)~~ (v0.8.5)
- ~~Accueil vignettes, bouton profil, partage e-mail / Bluesky~~ (v0.8.6)
- ~~Recherche acteur/réalisateur sur tout le catalogue~~ (v0.8.7)
- ~~Suite cibles d’achat (partage visiteur + « J’ai acheté » avec choix de version)~~ (v0.8.8) — comparateur de prix reporté
- ~~Prêts entre utilisateurs (phase 8)~~ (v0.8.9)
- ~~Stockage fichiers (racine `MONCINE_MEDIA_PATH`, hors www)~~ (v0.9.0)
- ~~Listes imprimables Mes films / Mes envies~~ (v0.9.1) — alternative à l’export PDF serveur
- Export PDF serveur (phase 10, optionnel plus tard)
- Mes BD
- Collections de magazines
- Magazines PDF & lecteur intégré

Détail : [ROADMAP.md](ROADMAP.md). Historique des versions : [CHANGELOG.md](CHANGELOG.md).

---

## Comprendre le code (par où commencer)

| Fichier | Rôle |
|---------|------|
| `lib/bootstrap.php` | Chargé par chaque page : config, base, connexion obligatoire |
| `lib/Auth.php` | Qui est connecté, login, pages publiques |
| `lib/UserContext.php` | ID utilisateur et foyer pour « Mes films » / envies |
| `lib/FoyerRepository.php` | Foyers (collection partagée) |
| `lib/Database.php` | SQLite + migrations automatiques |
| `lib/FilmRepository.php` | Accès aux films de l’utilisateur courant |
| `www/*.php` | Une page = un fichier (contrôleur léger) |
| `www/partage.php` | Liste partagée visiteur (lecture seule, sans compte) |
| `www/gerer-partages.php` | Création / révocation des liens de partage |
| `www/quiz.php` / `www/resultat.php` | Questionnaire du soir et proposition de film |
| `www/imprimer-films.php` / `www/imprimer-envies.php` | Listes imprimables (Mes films / Mes envies) |
| `templates/*.php` | HTML affiché (via `View::render`) |

Documentation : [questionnaire du soir](doc/questionnaire-du-soir.md) · [listes imprimables](doc/listes-imprimables.md).

---

## Structure du projet

```text
Moncine/
├── www/              pages web
├── lib/              code PHP (+ cli/migrate.php)
├── templates/        vues HTML
├── sql/
│   ├── schema.sql    schéma complet (install fraîche)
│   ├── migrations/   évolutions SQL (001, 002…)
│   └── migrations_legacy/  historique dev (non exécuté)
├── data/             base SQLite, clés API, affiches (non versionné)
├── tests/            tests PHPUnit
└── doc/
```

---

## Prérequis

- PHP **8.2+** avec extension **sqlite3**
- [Composer](https://getcomposer.org/) (pour les tests)

---

## Installation et test en local

```bash
cd /chemin/vers/Moncine
composer install
php lib/cli/migrate.php --fresh   # première fois (crée data/moncine.db)
php -S localhost:8080 -t www
```

Ouvrir http://localhost:8080 — à la première visite, créez le **compte administrateur** sur `/premier-compte.php`.

### Variables d’environnement utiles

| Variable | Rôle |
|----------|------|
| `MONCINE_DATA_PATH` | Dossier des données (base SQLite, clés API, affiches). Par défaut : `./data/` |
| `MONCINE_MEDIA_PATH` | Racine des fichiers volumineux (PDF, exports…). Par défaut : `./data/media/` |
| `MONCINE_BASE_URL` | URL publique de l’app (liens dans les e-mails de réinitialisation de mot de passe) |

---

## Comptes utilisateurs et foyers

- **Premier lancement** : `/premier-compte.php` (administrateur + foyer par défaut)
- **Connexion** : `/connexion.php`
- **Paramètres → Compte** (profil, mot de passe) : `/parametres.php` ; import et propositions catalogue dans le même menu
- **Gestion des comptes** : `/utilisateurs.php` (admin uniquement)
- **Foyers** : `/foyers.php` (admin — collection partagée entre membres)

Les membres d’un même foyer voient la **même collection** ; chacun garde **ses envies** et **son historique**.

- Documentation mots de passe : [doc/comptes-mot-de-passe.md](doc/comptes-mot-de-passe.md)
- Documentation questionnaire : [doc/questionnaire-du-soir.md](doc/questionnaire-du-soir.md)
- Listes imprimables : [doc/listes-imprimables.md](doc/listes-imprimables.md)

---

## Migrations SQL

- **Install fraîche** : `sql/schema.sql` si la base est vide, puis `sql/migrations/*.sql`
- **Mise à jour** : `php lib/cli/migrate.php`

Les fichiers dans `sql/migrations_legacy/` ne sont **pas** appliqués (historique uniquement).

**Important** : sauvegardez `data/moncine.db` avant une mise à jour.

| Version | Migrations notables |
|---------|---------------------|
| v0.7 | Foyers, collection partagée (`008`–`011`) |
| **v0.8.0** | Partage visiteur (`017_share_links`), EAN catalogue (`023_oeuvre_eans`) |
| **v0.8.2** | Versions recherchées sur envies (`024_wishlist_targets`) |
| **v0.8.3** | Profil public `/utilisateur.php` (stats, vignettes, listes lecture seule) |
| **v0.8.4** | Temps de vision cumulé sur `/statistiques.php` (aucune migration SQL) |
| **v0.8.5** | Sauvegarde / restauration `moncine.db` depuis `/maintenance-catalogue.php` (aucune migration SQL) |
| **v0.8.6** | Accueil (vignettes), bouton profil, partage lien e-mail / Bluesky (aucune migration SQL) |
| **v0.8.7** | Recherche personnes sur le catalogue + statut bibliothèque (aucune migration SQL) |
| **v0.8.8** | Phase 7 bis : partage visiteur des cibles d’achat, « J’ai acheté » avec choix de version, EAN chiffres seuls (`025`) |
| **v0.8.9** | Prêts entre amis (`018`, `026`) |
| **v0.9.0** | Stockage médias local (`019_stored_objects`), page `/maintenance-medias.php` ; UX proposition questionnaire (`/resultat.php`) |
| **v0.9.1** | Listes imprimables `/imprimer-films.php`, `/imprimer-envies.php` (aucune migration SQL) |
| **v0.9.2** | Sécurité médias et listes imprimables (aucune migration SQL) |
| **v0.9.3** | Inscription publique (`027`, `028`), throttle auth session + IP |
| **v0.9.4** | Correctifs inscription / reset MDP (aucune migration SQL) |
| **v0.9.5** | Suppression du compte depuis Mon compte (aucune migration SQL) |

---

## Tests automatisés (PHPUnit)

Vérifie l’import/export (détection de format, parsing CSV, import bibliothèque et catalogue) sur une base SQLite temporaire.

```bash
composer test
```

---

## Import / export

- **Export** : page `/export.php` (CSV collection, envies, historique)
- **Import** : page `/import.php` (bibliothèque ou catalogue admin)
- **Impression** : bouton **Version imprimable** sur Mes films et Mes envies → PDF via le navigateur ([doc/listes-imprimables.md](doc/listes-imprimables.md))

Les affiches locales sont stockées dans `data/posters/` (ou le dossier défini par `MONCINE_DATA_PATH`).
