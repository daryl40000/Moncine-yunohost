# Roadmap Moncine

Document de planification des **évolutions fonctionnelles** de l’application Moncine (dvdthèque personnelle : films, bandes dessinées, puis magazines).

Les phases sont **ordonnées par dépendances** : chaque étape s’appuie sur la précédente. Les changements de base de données passent par des **migrations SQL numérotées**, testées depuis la version précédente.

---

## Vision cible

| Acteur | Capacités |
|--------|-----------|
| **Administrateur** | Gère le **catalogue** d’œuvres partagé, valide les propositions, enrichit les fiches via TMDB — **ne gère plus les foyers** (phase 6) |
| **Utilisateur** | **Amis**, création de **groupes famille** avec d’autres amis, bibliothèque partagée du groupe, **sa** wishlist, **ses** notes et visions ; prêts |
| **Membre d’un groupe « famille »** | Même collection physique que le groupe ; wishlist et historique **personnels** (comme aujourd’hui avec le foyer v0.7) |
| **Tous** | Ne modifient pas les métadonnées catalogue — seulement les infos de **leur** exemplaire (`support`, format image/son, etc.) |

Fonctionnalités métier visées :

1. Comptes **admin** / **utilisateur** (connexion, gestion des comptes, changement et réinitialisation de mot de passe)
2. **Réseau d’amis** (demandes, acceptation) — **socle** du système social
3. **Groupes « famille » / foyer** : créés **par les utilisateurs** (amis qui s’associent), avec bibliothèque partagée — **remplace** la gestion admin des foyers (phase 4)
4. ~~**Partage visiteur**~~ — **livré v0.8.0** : lien URL en **lecture seule** vers **Mes films** et **Mes envies** (fiche film consultable, aucune modification)
5. **Prêts** : savoir quoi a été prêté, à qui, quand, et le retour
6. **Stockage de fichiers** volumineux (PDF magazines, etc.) : dossier partagé type YunoHost avec **racine unique** configurable (`MONCINE_MEDIA_PATH`) et sous-dossiers gérés par Moncine
7. **Export PDF** de la bibliothèque / des envies
8. Page **Mes BD** (collection + wishlist)
9. ~~**Soumissions** au catalogue~~ — **livré v0.7.4** (propositions, validation admin, notifications)
10. ~~**EAN multiples par œuvre** (catalogue)~~ — **livré v0.8.0** : un code-barres par édition / support (DVD, Blu-ray, 4K…) — socle pour **recherche d’achat** ultérieure
10 bis. ~~**Versions recherchées sur les envies**~~ — **livré v0.8.2** : plusieurs supports et EAN par envie (`wishlist_targets`) — complément personnel pour futurs comparateurs de prix
10 ter. ~~**Profil public utilisateur**~~ — **livré v0.8.3** : page profil (stats, vignettes), listes lecture seule, liens depuis Mes amis / groupe
11. ~~**Suite cibles d’achat (envies)**~~ (v0.8.8, partiel) : partage visiteur + « J’ai acheté » avec choix de version — **comparateur de prix reporté**
12. **Collections de magazines** (titres, numéros, organisation par collection)
13. **Magazines en PDF** + **lecteur PDF** (s’appuie sur la couche stockage)

---

## État actuel

**Version applicative : 0.8.8**

Application PHP + SQLite, déployable en local ou sur un serveur web classique.

### Déjà en place

| Domaine | Contenu |
|---------|---------|
| **Catalogue & bibliothèque** | Tables `oeuvres`, `bibliotheque`, `historique` ; films, envies, import/export CSV |
| **Enrichissement** | TMDB, OMDB, affiches, statistiques, quiz, sagas |
| **Comptes (phase 1)** | Connexion, déconnexion, premier admin, CRUD utilisateurs, rôles, protection des pages |
| **Mots de passe (phase 1 bis)** | Mon compte, changement de mot de passe, oublié par e-mail, reset admin |
| **Exemplaire personnel (phase 2)** | `format_image` / `format_son` sur `bibliotheque` ; formulaire « mon exemplaire » ; enrichissement catalogue réservé admin |
| **Admin catalogue (phase 3)** | Liste, fiche œuvre, maintenance, affiche manuelle |
| **Foyers (phase 4)** | Collection partagée, envies / historique personnels |
| **Profil (v0.7.2)** | Prénom, pseudo, menu Paramètres / Gestion, navigation fiches |
| **Soumissions catalogue (phase 5, v0.7.4)** | Proposer, valider, refuser ; notifications in-app + e-mail |
| **Profil & recherche (v0.7.6)** | Ville optionnelle, recherche par pseudo/ville, opt-out recherche, cloche compacte |
| **Amis & groupes famille (phase 6, v0.7.7)** | Demandes d’ami, groupe famille utilisateur, invitations, admin foyers lecture seule |
| **Envies groupe & UX (v0.7.8)** | Envies agrégées du groupe, votes « Moi aussi », ajout direct après proposition acceptée |
| **UX & release (v0.7.9)** | Liens lisibles thème sombre, composant `.ui-pill`, `CHANGELOG.md`, tags `v0.7.x` |
| **Sécurité sociale (v0.7.10)** | LIKE recherche, rate limit amis/recherche, blocage utilisateur |
| **EAN catalogue (v0.8.0)** | Table `oeuvre_eans`, admin fiche œuvre, suggestion EAN exemplaire |
| **Partage visiteur (v0.8.0)** | Liens lecture seule collection / envies, pages publiques sécurisées |
| **Cibles d’achat envies (v0.8.2)** | Table `wishlist_targets`, support + EAN multiples par envie, lien optionnel vers `oeuvre_eans` |
| **Profil public (v0.8.3)** | `/utilisateur.php`, stats et vignettes pour amis / membres du groupe |
| **Temps de vision cumulé (v0.8.4)** | Carte sur `/statistiques.php`, `CollectionStats::totalViewingMinutes()` |
| **Sauvegarde base SQLite (v0.8.5)** | Export / restauration admin sur `/maintenance-catalogue.php` |
| **UX accueil & partage (v0.8.6)** | Vignettes accueil, bouton profil, partage lien e-mail / Bluesky |
| **Recherche personnes catalogue (v0.8.7)** | `/personnes.php` sur tout le catalogue, statut collection / envies |
| **Suite cibles d’achat (v0.8.8)** | Partage visiteur des versions recherchées ; « J’ai acheté » ; EAN normalisés ; UX liste envies compacte |
| **Migrations SQL** | `SchemaMigrator`, CLI `php lib/cli/migrate.php`, migrations `001` → `016`, `017`, `023`, `024`, `025` |
| **Tests** | PHPUnit (import, catalogue, foyers, soumissions, notifications) |

### Point d’étape — mai 2026

**Version actuelle : 0.8.8.** Partage visiteur des versions recherchées et « J’ai acheté » avec choix de version. **Comparateur de prix (7 bis.2) reporté.** Prochaine évolution majeure : **phase 8** (prêts).

| Version | Contenu principal |
|---------|-------------------|
| 0.7.0 | Foyers & collection partagée |
| 0.7.1 | Affiche manuelle admin (catalogue) |
| 0.7.2 | Profil, menus, navigation Préc./Suiv. entre fiches |
| 0.7.4 | Soumissions catalogue + notifications + UX catalogue |
| 0.7.6 | Ville, recherche utilisateurs, opt-out recherche, cloche notifications |
| 0.7.7 | Amis, groupes famille, invitations, admin foyers lecture seule |
| 0.7.8 | Envies du groupe, notifications proposition acceptée, ajout en un clic |
| 0.7.9 | UX thème sombre, composant `.ui-pill`, CHANGELOG, tags Git alignés |
| 0.7.10 | Sécurité sociale (LIKE, rate limit, blocage utilisateur) |
| 0.8.0 | EAN multiples catalogue + partage visiteur (liens lecture seule) |
| 0.8.2 | Versions recherchées sur envies (support + EAN multiples) |
| 0.8.3 | Profil public utilisateur (amis / groupe) |
| 0.8.4 | Temps de vision cumulé (statistiques) + correction titre TMDB par ID |
| 0.8.5 | Sauvegarde / restauration complète de la base SQLite (admin) |
| 0.8.6 | Accueil vignettes, profil en un clic, partage lien e-mail / Bluesky |
| 0.8.7 | Recherche personnes sur le catalogue + badges bibliothèque |
| 0.8.8 | Phase 7 bis : partage visiteur cibles, « J’ai acheté » (liste déroulante), EAN chiffres seuls (`025`) |

### Prochaines étapes

| Phase | Statut |
|-------|--------|
| Phase 3 — Admin catalogue | ✅ Livré (v0.6) |
| Phase 4 — Foyers & famille | ✅ Livré (v0.7) |
| Phase 5 — Soumissions catalogue | ✅ Livré (v0.7.4) |
| Pré-phase 6 — Profil ville & recherche utilisateurs | ✅ Livré (v0.7.6) |
| Phase 6 — Amis & groupes famille (foyers utilisateurs) | ✅ Livré (v0.7.7) |
| Phase 6 bis — EAN multiples par œuvre (catalogue) | ✅ Livré (v0.8.0) |
| Phase 7 — Partage visiteur (lien lecture seule) | ✅ Livré (v0.8.0) |
| Cibles d’achat — envies (support + EAN) | ✅ Livré (v0.8.2) |
| Profil public utilisateur (social) | ✅ Livré (v0.8.3) |
| Phase 7 bis — Suite cibles d’achat (envies) | **Partielle** (sans comparateur prix) |
| Phase 8 — Prêts entre utilisateurs | ✅ Livré (v0.8.9) |
| Phase 9 — Stockage fichiers (local) | **Livré v0.9.0** |
| Phase 10 — Export PDF | À faire |
| Phase 11 — Mes BD | À faire |
| Phase 12 — Collections de magazines | À faire |
| Phase 13 — Magazines PDF & lecteur | À faire |

---

## Vue d’ensemble des phases

```mermaid
flowchart TD
    P1[Phase 1 - Comptes]
    P1b[Phase 1 bis - Mots de passe]
    P2[Phase 2 - Champs perso exemplaire]
    P3[Phase 3 - Admin catalogue]
    P4[Phase 4 - Foyers]
    P5[Phase 5 - Soumissions]
    P6[Phase 6 - Amis et groupes famille]
    P6b[Phase 6 bis - EAN catalogue]
    P7[Phase 7 - Partage visiteur]
    P7b[Phase 7 bis - Suite achat envies]
    P8[Phase 8 - Prets]
    P9[Phase 9 - Stockage fichiers]
    P10[Phase 10 - Export PDF]
    P11[Phase 11 - Mes BD]
    P12[Phase 12 - Magazines]
    P13[Phase 13 - PDF magazines]

    P1 --> P1b
    P1 --> P2
    P1 --> P3
    P1b --> P2
    P2 --> P4
    P3 --> P5
    P4 --> P6
    P1 --> P6
    P4 --> P7
    P2 --> P7
    P3 --> P6b
    P6b --> P7
    P7 --> P7b
    P6b --> P7b
    P7b --> P8
    P6 --> P8
    P4 --> P8
    P9 --> P13
    P2 --> P10
    P4 --> P10
    P7 --> P10
    P5 --> P11
    P2 --> P11
    P4 --> P11
    P11 --> P12
    P12 --> P13
```

---

## Stratégie SQL : migrations versionnées

### Suivi du schéma

```sql
schema_migrations (name TEXT PRIMARY KEY, applied_at)

app_metadata (
  key TEXT PRIMARY KEY,
  value TEXT NOT NULL
)
-- Clés : schema_version, …
```

- **`schema_version`** : numéro de la dernière migration appliquée
- **`schema_migrations`** : traçabilité fichier par fichier

### Convention

| Règle | Exemple |
|-------|---------|
| Nom de fichier | `007_admin_audit_log.sql`, `008_foyers.sql` |
| Numéro sur 3 chiffres, croissant | Ne jamais modifier un fichier déjà publié |
| Une responsabilité par fichier | Auth ≠ foyers ≠ BD |
| SQL + commentaire en tête | `-- requires: schema_version >= 6` |
| Données : `INSERT…SELECT` explicite | Pour les transformations de données existantes |

### Schéma cible (après toutes les phases)

```text
oeuvres              -- catalogue partagé (films, BD, magazines, …)
bibliotheque         -- lien foyer/user + statut + champs perso exemplaire
historique           -- visions + notes (+ user_id)
utilisateurs         -- comptes (role ; lien groupe via group_members)
foyers               -- groupes « famille » (type famille), créés par les utilisateurs (phase 6)
group_members        -- appartenance user ↔ groupe (rôle fondateur / membre)
friendships          -- liens amis (prérequis pour créer ou rejoindre un groupe)
catalogue_soumissions
notifications          -- alertes in-app (soumissions catalogue, etc.)
loans                -- prêts d’exemplaires (phase 7)
stored_objects       -- métadonnées fichiers (stockage local, hors www/) (phase 9)
share_links          -- jetons URL partagée lecture seule (phase 9)
magazine_collections -- titres / séries de magazines (phase 11)
magazine_numeros     -- numéros rattachés à une collection (phase 11)
magazine_fichiers    -- lien vers stored_objects (phase 12)
schema_migrations
app_metadata
sessions             -- si sessions en base
```

### Outils

| Élément | Rôle |
|---------|------|
| `sql/schema.sql` | Install fraîche (base vide) |
| `sql/migrations/*.sql` | Évolutions incrémentales |
| `php lib/cli/migrate.php` | Appliquer les migrations (dev et production) |

---

## Phase 1 — Comptes, connexion et rôles ✅

**Objectif :** fin du mono-utilisateur ; base pour familles et soumissions.

**Statut : livré** (migration `002_utilisateurs_auth.sql`).

| # | Tâche | Statut |
|---|--------|--------|
| 1.1 | Connexion / déconnexion | ✅ |
| 1.2 | `UserContext` ← session | ✅ |
| 1.3 | Protection pages + menu par rôle | ✅ |
| 1.4 | Assistant premier admin (DB vide) | ✅ |
| 1.5 | CRUD utilisateurs (admin) | ✅ |
| 1.6 | `canManageCatalog()` ← `role` | ✅ |
| 1.7 | Durcissement sécurité (CSRF, limite connexion, hash) | ✅ |

**Critère :** deux comptes → deux bibliothèques distinctes.

---

## Phase 1 bis — Mots de passe ✅

**Objectif :** chaque utilisateur gère son mot de passe ; récupération en cas d’oubli.

**Statut : livré** (migration `004_password_reset_tokens.sql`).

| # | Tâche | Statut |
|---|--------|--------|
| 1 bis.1 | Page Mon compte (profil) | ✅ |
| 1 bis.2 | Changer son mot de passe | ✅ |
| 1 bis.3 | Admin : réinitialiser le mot de passe d’un compte | ✅ |
| 1 bis.4 | Mot de passe oublié (formulaire e-mail) | ✅ |
| 1 bis.5 | Nouveau mot de passe via jeton (expiration, usage unique) | ✅ |
| 1 bis.6 | Envoi e-mail (SMTP ou `mail()`) | ✅ |
| 1 bis.7 | Limite de débit sur « oublié » | ✅ |

### Hors scope court terme

| Idée | Phase suggérée |
|------|----------------|
| Forcer changement au premier login | 1 bis.9 |
| Authentification à deux facteurs (2FA) | Hors périmètre |
| SSO / LDAP | Phase ultérieure (optionnelle) |

---

## Phase 2 — Catalogue vs exemplaire personnel ✅

**Objectif :** séparer les métadonnées catalogue (partagées) des infos de l’exemplaire personnel (support, formats).

**Statut : livré** (migrations `005_format_exemplaire_bibliotheque.sql`, `006_drop_oeuvre_format_columns.sql`).

| # | Tâche | Statut |
|---|--------|--------|
| 2.1 | `format_image`, `format_son` sur `bibliotheque` | ✅ |
| 2.2 | Formulaires utilisateur : champs exemplaire uniquement | ✅ |
| 2.3 | Blocage serveur : pas de modification catalogue par un user | ✅ |
| 2.4 | Enrichissement TMDB réservé admin | ✅ |
| 2.5 | Migration des données existantes | ✅ |

**Critère :** l’utilisateur modifie support/format ; pas le titre catalogue.

---

## Phase 3 — Admin catalogue ✅

**Objectif :** outils de maintenance du catalogue pour les administrateurs.

**Statut : livré** (v0.6.0, migration `007_admin_audit_log.sql`).

| # | Tâche | Statut |
|---|--------|--------|
| 3.1 | Page admin : vue d’ensemble (doublons, fiches incomplètes) | ✅ |
| 3.2 | Fusion de doublons (`oeuvres` → une seule fiche) | ✅ |
| 3.3 | Journal des actions admin sur le catalogue | ✅ |
| 3.4 | Outils de nettoyage (affiches orphelines, doublons TMDB) | ✅ |

Page : `/maintenance-catalogue.php` (menu admin **Maintenance**).

**Critère :** un admin peut détecter et fusionner un doublon sans perte de bibliothèque utilisateur.

---

## Phase 4 — Foyers & famille ✅

**Objectif :** collection partagée au niveau du foyer ; wishlist et historique personnels.

**Statut : livré** (v0.7.0, migrations `008`–`011`, script `FoyerMigration`).

**Dépend de :** phase 2. **Migration la plus délicate** — prévoir sauvegarde avant upgrade.

### Migrations SQL

```text
008_foyers.sql — table foyers, foyer_id sur utilisateurs
009_bibliotheque_foyer_collection.sql — foyer_id sur collection
010_historique_user_id.sql — user_id sur historique
011_wishlist_per_user.sql — contraintes collection (foyer) / envies (user)
```

Script PHP post-SQL : `lib/FoyerMigration.php`.

| # | Tâche | Statut |
|---|--------|--------|
| 4.1 | CRUD foyers (admin) | ✅ |
| 4.2 | Affectation utilisateur → foyer | ✅ |
| 4.3 | Collection visible par tous les membres du foyer | ✅ |
| 4.4 | Wishlist et historique filtrés par `user_id` | ✅ |
| 4.5 | Interface « famille » (sous-comptes, affectation foyer) | ✅ |

Pages : `/foyers.php`, `/utilisateurs.php`, `/mon-compte.php`.

**Critère terminé :** deux membres d’un même foyer voient la même collection ; leurs envies et notes restent séparées.

> **Évolution prévue (phase 6)** : le modèle ci-dessus reste **valide techniquement** (tables `foyers`, `foyer_id`, collection partagée), mais la **gouvernance** change. L’admin ne crée plus les foyers : des **amis** créent ensemble un **groupe famille** qui reprend les mêmes droits (bibliothèque commune, envies perso). Les foyers v0.7 seront **migrés** vers ce modèle ; `/foyers.php` admin sera retiré ou remplacé par une gestion côté utilisateur.

---

## Phase 5 — Soumissions catalogue ✅

**Objectif :** les utilisateurs proposent de nouvelles œuvres ; l’admin valide avant insertion dans `oeuvres`.

**Statut : livré** (v0.7.4, migration `013_catalogue_soumissions.sql`).

**Dépend de :** phase 3 (recommandé) ou phase 1 (minimum).

### Migrations SQL livrées

```text
012_utilisateur_profil.sql   -- prénom, pseudo (v0.7.2)
013_catalogue_soumissions.sql
014_notifications.sql
```

| # | Tâche |
|---|--------|
| 5.1 | Formulaire « proposer une œuvre » (TMDB optionnel, autocomplétion) | ✅ |
| 5.2 | File d’attente admin (approuver / rejeter / modifier la fiche) | ✅ |
| 5.3 | Notification admin (nouvelle proposition) et utilisateur (acceptée / refusée), in-app + e-mail | ✅ |
| 5.4 | Aucune écriture directe dans `oeuvres` par un utilisateur non admin | ✅ |
| 5.5 | Menus : Paramètres (Compte, Proposer, Importer) ; Gestion admin ; pas de « Proposer » pour l’admin | ✅ |
| 5.6 | Navigation Préc./Suiv. (fiches film, fiches catalogue, pagination liste catalogue) | ✅ |

Pages : `/proposer-oeuvre.php`, `/mes-soumissions.php`, `/soumissions-catalogue.php`, `/notifications.php`.

**Critère terminé :** une proposition validée apparaît dans le catalogue ; une rejetée ne laisse aucune trace dans `oeuvres` ; les parties concernées sont notifiées.

> **Après migration :** `php lib/cli/migrate.php` (applique `013` et `014` si besoin).

---

## Phase 6 — Amis & groupes « famille » (nouveau modèle de foyer)

**Objectif :** le **réseau d’amis** devient le socle social. Le **foyer** n’est plus un objet créé par l’**admin** : c’est un **groupe d’amis** de type **famille** (ou **foyer**), que **deux utilisateurs amis** (ou plus) **créent ensemble** et auquel ils invitent d’autres amis. Ce groupe conserve tout ce que fait le foyer actuel : **bibliothèque partagée**, envies et historique **personnels** par membre.

**Dépend de :** phases 1 et 4 (comptes + modèle collection partagée déjà en place — à faire évoluer).

### Principe (remplace la gestion admin des foyers)

| Avant (v0.7, phase 4) | Après (phase 6) |
|------------------------|-----------------|
| L’admin crée un foyer et affecte les utilisateurs | Deux **amis** créent ensemble un groupe **famille** |
| `/foyers.php` réservé admin | **Mes groupes** / **Créer un groupe famille** côté utilisateur |
| `utilisateurs.foyer_id` imposé par admin | Appartenance via **group_members** ; un user peut appartenir à un groupe (règle à préciser : un seul groupe famille actif ou plusieurs — v1 : **un groupe famille principal**) |
| Amis et foyers séparés | **Amis d’abord** → puis groupe famille = ancien « foyer » |

```mermaid
flowchart LR
    A[Utilisateur A] <-->|demande acceptée| B[Utilisateur B]
    A --> G[Groupe famille]
    B --> G
    G --> C[Collection partagée bibliotheque]
    A --> W1[Wishlist perso A]
    B --> W2[Wishlist perso B]
```

### Migrations SQL prévues

```text
016_friendships_and_groups.sql
  - friendships (requester_id, addressee_id, status pending|accepted|blocked, …)
  - foyers : kind = 'famille' | …, created_by_user_id, created_at
  - group_members (foyer_id, user_id, role founder|member, joined_at, invited_by)
  - migration données : chaque foyer v0.7 → groupe famille + membres existants
  - utilisateurs : foyer_id conservé comme groupe actif ou dérivé de group_members
```

> Le nom de table **`foyers`** peut être conservé en base pour limiter la casse (migrations 008–011), mais l’**interface** et les **droits** parlent de **groupe famille**.

### Tâches

| # | Tâche |
|---|--------|
| 6.1 | **Demandes d’ami** : envoyer / accepter / refuser (+ notifications) | ✅ |
| 6.2 | Page **Mes amis** (liste, demandes en attente) | ✅ |
| 6.3 | **Créer un groupe famille** (nom, un groupe actif par utilisateur) | ✅ |
| 6.4 | **Inviter un ami** dans le groupe (invitation acceptée par l’invité) | ✅ |
| 6.5 | **Quitter le groupe** / transfert du rôle fondateur (v1 minimal) | ✅ |
| 6.6 | **Bibliothèque partagée** : `bibliotheque.foyer_id` du groupe | ✅ |
| 6.7 | **Migration v0.7** : foyers → groupes + `group_members` | ✅ |
| 6.8 | **Retrait admin** : `/foyers.php` lecture seule ; comptes sans affectation foyer | ✅ |
| 6.9 | Visibilité profil / wishlist ami | — (phase ultérieure) |
| 6.10 | Modération admin signalement / blocage | — (phase ultérieure) |

**Critère terminé :** Alice et Bob sont amis ; ils créent ensemble le groupe « Famille Martin » ; leur collection DVD est commune ; leurs envies restent séparées ; l’admin ne crée plus de foyer depuis l’interface Gestion.

### Ce qui ne change pas (héritage phase 4)

- Collection = lignes `bibliotheque` liées au **groupe** (`foyer_id`).
- Envies et historique = **par utilisateur** (`user_id`).
- Sous-comptes « famille » sans e-mail propre : à redéfinir (compte enfant rattaché à un adulte du groupe — phase ultérieure ou règle v1 simplifiée).

### Points d’attention (phase 6)

- **Utilisateur sans groupe** : bibliothèque personnelle seule jusqu’à création ou invitation dans un groupe famille.
- **Un ou plusieurs groupes** : v1 recommandé = **un groupe famille actif** par utilisateur pour éviter la confusion des collections.
- **Compatibilité** : sauvegarde obligatoire avant migration ; script de reprise des foyers admin existants.

---

## Phase 6 bis — EAN multiples par œuvre (catalogue)

**Statut : livré** (v0.8.0, migration `023_oeuvre_eans.sql`).

**Objectif :** sur une **fiche catalogue** (`oeuvres`), enregistrer **plusieurs codes EAN** pour le **même film** (ou la même œuvre), selon l’**édition physique** : par exemple un EAN pour le **DVD**, un autre pour le **Blu-ray**, un autre pour le **Blu-ray 4K**.

Ces codes sont des **métadonnées catalogue** (partagées par tous les foyers), distinctes de l’EAN éventuel saisi sur **un exemplaire** dans `bibliotheque` (mon exemplaire personnel).

**Usage futur :** alimenter une **recherche d’achat** (comparateurs de prix, marketplaces, alertes stock) — phase dédiée **hors périmètre v1** de cette étape ; la phase 6 bis pose uniquement le **modèle de données** et l’**interface de gestion**.

**Dépend de :** phase 3 (catalogue admin) ; `SupportPhysique` (DVD / Blu-ray / Blu-ray 4K) déjà en place pour les exemplaires.

### Situation actuelle (v0.8.0)

| Emplacement | Rôle |
|------------|------|
| `bibliotheque.ean` | Code-barres sur **mon exemplaire** (collection ou envie) |
| `oeuvre_eans` | EAN **catalogue** par œuvre et par support (DVD / Blu-ray / 4K) |
| Fiche œuvre admin | Section « Codes EAN catalogue » + formulaire d’ajout |
| Fiche film | Suggestion d’EAN catalogue selon le support choisi |

### Modèle cible

```text
oeuvre_eans
  - id
  - oeuvre_id       → oeuvres(id)
  - ean             TEXT NOT NULL   (chiffres uniquement, 8–14 car.)
  - support_physique TEXT NOT NULL  -- 'dvd' | 'bluray' | 'bluray_4k' | '' (édition générique)
  - label           TEXT            -- libellé libre optionnel « Édition digibook »
  - source          TEXT            -- 'manual' | 'import' | 'submission' (optionnel)
  - created_at

  UNIQUE (oeuvre_id, support_physique)   -- un EAN par type de support et par œuvre
  UNIQUE (ean)                           -- un EAN ne pointe que vers une œuvre
```

> Le champ `bibliotheque.ean` reste possible pour l’exemplaire réellement possédé ; à terme, choisir un **support** dans le formulaire peut **proposer** l’EAN catalogue correspondant.

### Migrations SQL prévues

```text
023_oeuvre_eans.sql
  - table oeuvre_eans (voir ci-dessus)
  - index oeuvre_id, index ean
  - migration optionnelle : recopier les EAN distincts déjà présents sur bibliotheque
    vers oeuvre_eans si support_physique renseigné (script PHP post-migration)
```

> Numérotation **023** pour ne pas croiser 017 (partage visiteur) ni 018+ (prêts, stockage…). Peut être livrée **en parallèle** de la phase 7.

### Tâches

| # | Tâche |
|---|--------|
| 6b.1 | Table + repository `OeuvreEanRepository` (CRUD, liste par `oeuvre_id`, recherche par EAN) | ✅ |
| 6b.2 | Fiche **catalogue** (`oeuvre.php` / admin) : section « Codes EAN » avec ajout / suppression par support | ✅ |
| 6b.3 | Validation : EAN numérique, longueur, unicité globale, support parmi `SupportPhysique` | ✅ |
| 6b.4 | Soumissions catalogue : champs optionnels EAN + support dans la proposition ; reprise à la validation | — (ultérieur) |
| 6b.5 | Formulaire **Mes films** : suggestion de l’EAN catalogue selon le support | ✅ |
| 6b.6 | Import CSV catalogue : colonnes EAN multiples | — (ultérieur) |
| 6b.7 | Tests PHPUnit : unicité EAN, plusieurs supports sur une œuvre | ✅ |

### Recherche d’achat (phase ultérieure — rappel)

| # | Idée (non livré en 6 bis) |
|---|---------------------------|
| — | Rechercher un film par **scan** ou saisie EAN → retrouver l’œuvre catalogue |
| 7 bis.2 | Comparer les prix / disponibilité par support + EAN (API externe ou liens) — **phase 7 bis** |
| — | Alertes « bon plan » sur un EAN de la wishlist (ultérieur) |

La phase 6 bis et v0.8.2 **préparent** ces évolutions ; la **phase 7 bis** en est la prochaine livraison ciblée.

### Critère terminé

Sur la fiche catalogue d’un film, l’admin (ou une proposition validée) peut enregistrer **au moins deux EAN** pour des supports différents (ex. DVD + Blu-ray) ; la recherche par EAN renvoie **une seule** œuvre ; aucun doublon d’EAN sur deux œuvres différentes.

### Points d’attention

- **BD / magazines** : même mécanisme via `moncine_kind` et supports adaptés plus tard (phase 11+).
- **EAN invalide ou doublon import** : rejet avec message clair en admin.
- **Confidentialité** : les EAN catalogue ne sont pas des données personnelles ; visibles sur fiche catalogue comme le reste des métadonnées partagées.

---

## Phase 7 — Partage visiteur (lien lecture seule)

**Statut : livré** (v0.8.0, migration `017_share_links.sql`).

**Objectif :** permettre à un utilisateur connecté de **générer un lien** qu’il envoie à un proche (sans compte Moncine). Le **visiteur** ouvre une page publique en **lecture seule** :

- **Mes films** : la collection du **groupe famille** (foyer), comme sur l’écran connecté mais sans boutons d’action ;
- **Mes envies** : la wishlist **personnelle** de l’utilisateur qui a créé le lien ;
- **Fiche film** : en cliquant sur un titre, le visiteur voit la fiche (métadonnées catalogue + infos exemplaire partagées), **sans** pouvoir modifier, noter, prêter, supprimer ou accéder au reste du site.

**Aucune modification** n’est possible pour le visiteur : pas de formulaires POST utiles, pas d’accès admin, catalogue, comptes, import, ni aux données d’autres membres (e-mails, notes privées, historique détaillé d’autrui).

**Dépend de :** phases 2 et 4 (collection + wishlist) ; phase 6 utile (groupe famille) mais pas bloquante.

### Principe

| Élément | Règle |
|---------|--------|
| Qui crée le lien | Utilisateur **connecté** (Paramètres, Mes films ou Mes envies) |
| Portée **collection** | `foyer_id` du groupe actif → liste = **Mes films** du foyer |
| Portée **wishlist** | `user_id` du créateur → liste = **ses** envies uniquement |
| URL | Chemin dédié, ex. `/partage/{jeton}` — jeton **long**, **aléatoire**, **non devinable** |
| Stockage jeton | En base : **hash** du jeton uniquement (comme un mot de passe), jamais le jeton en clair après création |
| Expiration | Optionnelle (`expires_at`) ; révocation immédiate par le propriétaire |
| Visiteur | **GET uniquement** ; pages hors `Auth::enforceWebAccess()` mais contrôlées par service dédié |

```mermaid
flowchart LR
    U[Utilisateur connecté]
    U -->|Crée lien + copie URL| L[share_links]
    V[Visiteur sans compte]
    V -->|GET /partage/token| P[Pages visiteur]
    P -->|Liste| F[Mes films ou Mes envies]
    P -->|Clic film| D[Fiche film lecture seule]
    L -.->|Valide hash + scope| P
```

### Migrations SQL prévues

```text
017_share_links.sql
  - share_links (
      id, token_hash, scope TEXT NOT NULL,  -- 'collection' | 'wishlist'
      foyer_id INTEGER NULL,                -- si scope = collection
      user_id INTEGER NOT NULL,             -- créateur du lien
      label TEXT,                           -- nom optionnel « Lien famille »
      created_at, expires_at, revoked_at,
      last_access_at, access_count
    )
  - index sur token_hash (unique), user_id, foyer_id
```

> Numérotation : **017** (après 016 amis/groupes). Les prêts passeront en **018**.

### Tâches fonctionnelles

| # | Tâche |
|---|--------|
| 7.1 | Page **Mes films** / **Mes envies** : bouton « Partager » → `/gerer-partages.php` | ✅ |
| 7.2 | Page **Paramètres** : section partage + `/gerer-partages.php` (créer, révoquer, libellé) | ✅ |
| 7.3 | Route publique **`/partage.php`** : validation jeton, liste collection ou wishlist | ✅ |
| 7.4 | Vue visiteur : **Liste** / **Vignettes**, affiches, filtres type, recherche, tri (comme Mes films) | ✅ |
| 7.5 | Scope **wishlist** : liste personnelle du créateur uniquement | ✅ |
| 7.6 | Vue visiteur **`/partage-film.php`** : fiche lecture seule + retour liste | ✅ |
| 7.7 | Navigation Préc./Suiv. entre fiches du scope partagé | — (ultérieur) |

### Exigences de sécurité (anti-abus / « piratage »)

| # | Mesure |
|---|--------|
| S1 | Jeton **≥ 32 octets** aléatoires (`random_bytes`) ; affiché **une seule fois** à la création |
| S2 | En base : **`token_hash`** (ex. `hash('sha256', $token)`) — fuite SQL ≠ accès direct |
| S3 | Chaque requête visiteur : résolution par hash + vérif **non révoqué** + **non expiré** + scope cohérent |
| S4 | **Rate limiting** sur `/partage/*` (par IP et par jeton) — anti brute-force du token |
| S5 | **GET seulement** sur routes visiteur ; aucun POST/PUT/DELETE sans session |
| S6 | Le visiteur **ne peut pas** deviner d’autres `film_id` : fiche accessible **uniquement** si le film appartient au scope du lien (requête filtrée par `share_link_id` + `foyer_id` / `user_id`) |
| S7 | Pas d’exposition : e-mails, mots de passe, utilisateurs du foyer, envies des **autres** membres, admin, maintenance, TMDB clés API |
| S8 | Pas d’**énumération** : message générique « Lien invalide ou expiré » (même code HTTP 404 ou 403 selon choix documenté) |
| S9 | En-têtes : `X-Robots-Tag: noindex`, `Referrer-Policy`, pas de session visiteur inutile |
| S10 | Journal optionnel : `access_count`, `last_access_at` pour détecter un lien trop diffusé |
| S11 | Le propriétaire peut **révoquer** instantanément ; changement de foyer / quitter le groupe **invalide** les liens collection concernés |

### Pages et fichiers cibles (indicatif)

| Fichier | Rôle |
|---------|------|
| `lib/ShareLinkService.php` | Création, révocation, validation jeton, résolution scope |
| `lib/ShareLinkRepository.php` | Accès SQL `share_links` |
| `www/partage.php` | Point d’entrée visiteur (token en query ou path) |
| `www/gerer-partages.php` | Gestion des liens (connecté, POST + CSRF) |
| `templates/partage.php`, `partage-film.php`, `_partage_collection_*.php` | Listes et fiche film **visiteur** |
| `lib/Auth.php` | Ajouter chemins `/partage.php`, `/partage-film.php` (ou équivalent) à `PUBLIC_PATHS` |

### Critère terminé

1. Depuis **Mes films**, l’utilisateur copie un lien ; un invité voit la **collection du foyer** et peut ouvrir chaque **fiche** en lecture seule.  
2. Depuis **Mes envies**, il copie un lien ; l’invité voit **ses envies** uniquement.  
3. L’invité ne peut **rien modifier** ni accéder au reste de Moncine sans connexion.  
4. Un lien **révoqué** ou **expiré** ne fonctionne plus ; un jeton invalide ne donne aucune information sur l’existence d’un compte.

### Points d’attention (phase 7)

- **Wishlist** = personnelle ; **collection** = partagée par le groupe : deux liens distincts, deux scopes.  
- **Affiches** : servies comme aujourd’hui si déjà publiques côté app ; pas d’URL directe vers `data/` hors contrôle PHP si politique stricte.  
- **Export PDF** : reporté à la **phase 10** (même domaine « partage », livrable différent).  
- Tests PHPUnit : validation jeton, scope film, refus ID hors scope, révocation.

---

## Phase 7 bis — Suite cibles d’achat (envies)

**Statut : partiellement livré** (7 bis.1 et 7 bis.3) — **comparateur de prix (7 bis.2) reporté** (aucune API publique retenue pour l’instant).

**Objectif :** exploiter la table **`wishlist_targets`** (support + EAN par envie) pour faciliter l’achat et la consultation par un proche, sans modifier le périmètre « lecture seule » du partage visiteur.

**Dépend de :** v0.8.2 (`024_wishlist_targets.sql`), phase 7 (partage visiteur), phase 6 bis (EAN catalogue — utile pour le comparateur).

### Tâches fonctionnelles

| # | Tâche |
|---|--------|
| 7 bis.1 | **Partage visiteur** : afficher les versions recherchées (support + EAN) sur **`/partage.php`** (liste envies) et **`/partage-film.php`** (fiche), en **lecture seule** | ✅ |
| 7 bis.2 | **Comparateur de prix** : à partir du support et de l’EAN d’une cible, proposer des liens ou une API externe de comparaison / disponibilité (choix technique ouvert en v1 : URLs marchands ou connecteur dédié) | — reporté |
| 7 bis.3 | **« J’ai acheté »** : permettre de **choisir** une version cible en wishlist pour **pré-remplir** le support (et l’EAN exemplaire si pertinent) lors du passage en collection | ✅ |

### Critère terminé

1. Un visiteur ouvrant un lien **Mes envies** voit, pour chaque titre concerné, les **supports et EAN** recherchés par le créateur du lien.  
2. Sur la fiche envie (connecté), l’utilisateur peut lancer une **recherche d’offres** à partir d’au moins une cible (support + EAN).  
3. Lors du **« J’ai acheté »**, si plusieurs cibles existent, l’utilisateur peut en sélectionner une et le formulaire de promotion propose le **bon support** par défaut.

### Points d’attention

- **Confidentialité partage** : les cibles d’achat suivent le même scope que la wishlist du lien (personnel du créateur uniquement).  
- **Comparateur** : pas d’engagement fournisseur en v1 — liens ou API documentés, gestion des erreurs réseau.  
- **Phase 8 (prêts)** : reste planifiée **après** la 7 bis pour ne pas mélanger prêt physique et intention d’achat.

### Fichiers cibles (indicatif)

| Fichier | Rôle |
|---------|------|
| `lib/ShareLinkService.php` | Charger `wishlist_targets` pour scope wishlist |
| `templates/partage.php`, `partage-film.php` | Affichage lecture seule des cibles |
| `lib/WishlistTargetRepository.php` | Requêtes partage + promotion |
| `www/souhaits.php`, `templates/souhaits.php` | Sélection de cible au « J’ai acheté » |
| Nouveau service comparateur (ex. `lib/PriceCompareService.php`) | Construction URLs / appel API |

---

## Phase 8 — Prêts entre utilisateurs

**Objectif :** suivre ce qui a été **prêté** (DVD, BD, magazine…), **à qui**, **quand**, et le **retour** — à un ami Moncine ou à une personne externe (nom libre).

**Dépend de :** phases 4, 6, 7 et **7 bis** (recommandé : envies et partage stabilisés avant prêts).

### Migrations SQL prévues

```text
018_loans.sql
  - loans (bibliotheque_id, lender_user_id, borrower_user_id NULL,
    borrower_name TEXT, loaned_at, due_at, returned_at, note)
```

| # | Tâche |
|---|--------|
| 8.1 | Marquer un exemplaire comme **prêté** (date de départ) |
| 8.2 | Bénéficiaire : utilisateur **ami** ou **nom libre** |
| 8.3 | Date de retour prévue et **retour effectif** |
| 8.4 | Vues **Prêts en cours** / **Historique** |
| 8.5 | Indicateur sur la fiche (« prêté à … ») |
| 8.6 | Rappels d’échéance — optionnel v1 |

**Critère terminé :** les exemplaires prêtés sont identifiables ; un retour remet l’exemplaire en disponible.

---

## Phase 9 — Stockage de fichiers (dossier partagé) — **livré v0.9.0**

**Objectif :** stocker les **fichiers volumineux** (PDF magazines, etc.) hors `www/`, avec un dossier personnalisable type **YunoHost**.

Le serveur peut gérer S3 en amont si besoin (montage, sync, etc.), mais **Moncine ne gère que du stockage local**.
On configure une **racine unique** `MONCINE_MEDIA_PATH`, puis Moncine crée et utilise ses **sous-dossiers** (magazines, livres, exports, …).

**Dépend de :** phase 1 (configuration instance). **Prérequis** pour la phase 13 (PDF magazines).

### Configuration cible (exemple YunoHost)

```text
/home/yunohost.multimedia/share/moncine/
  ├── objects/     # PDF et binaires
  ├── posters/     # affiches (migration possible)
  └── exports/     # PDF générés
```

| Variable | Exemple | Rôle |
|----------|---------|------|
| `MONCINE_DATA_PATH` | `…/data` | SQLite, clés API |
| `MONCINE_MEDIA_PATH` | `/home/yunohost.multimedia/share/moncine` | Racine médias (unique) |

### Migrations SQL prévues

```text
019_stored_objects.sql
  - stored_objects (backend local, path, mime, size_bytes, checksum, …)
  - app_metadata : chemins et mode de stockage
```

| # | Tâche |
|---|--------|
| 9.1 | Interface **`ObjectStorage`** (put, get, delete, stream) |
| 9.2 | Backend **filesystem local** (`MONCINE_MEDIA_PATH`) |
| 9.3 | Création/gestion des **sous-dossiers** (ex. `magazines/`, `books/`, `exports/`) |
| 9.4 | Page/admin de config : définir la **racine unique** + test écriture/lecture |
| 9.5 | Doc déploiement YunoHost (droits, backup du share) |
| 9.6 | Lecture des fichiers **via PHP** (pas d’URL publique directe) |

**Critère terminé :** un dossier share configurable (`MONCINE_MEDIA_PATH`) ; le code métier ne dépend plus d’un chemin fixe sous `www/`.

### Points d’attention (phase 9)

- **Droits Unix** : le serveur web doit pouvoir écrire dans `MONCINE_MEDIA_PATH`.
- **Backup** : inclure `MONCINE_MEDIA_PATH` dans la stratégie de sauvegarde.

---

## Phase 10 — Export PDF

**Objectif :** permettre d’**exporter en PDF** la bibliothèque et la wishlist depuis **Mes films** et **Mes envies** (utilisateur connecté).

**Dépend de :** phases 2, 4 et **7** (mêmes périmètres de données que le partage visiteur).

> Le **partage par lien** (phase 7) est livré **avant** cette phase ; l’export PDF réutilise les listes déjà filtrées côté foyer / utilisateur.

### Migrations SQL prévues

Aucune table obligatoire (génération à la volée). Option : métadonnée `app_metadata` pour modèle de mise en page.

| # | Tâche |
|---|--------|
| 10.1 | **Export PDF** depuis **Mes films** : collection du foyer (filtres / tri courants reflétés) |
| 10.2 | **Export PDF** depuis **Mes envies** : wishlist personnelle |
| 10.3 | Mise en page lisible (titres, années, réalisateurs, affiches optionnelles en miniature) |

**Critère terminé :** depuis Mes films et Mes envies, un PDF téléchargeable reflète la liste affichée à l’écran.

### Points d’attention (phase 10)

- Pas d’exposer de données hors périmètre (notes d’autres membres, e-mails).
- Taille du PDF raisonnable (pagination, limite de lignes si besoin).

---

## Phase 11 — Mes BD

**Objectif :** gérer les bandes dessinées comme les films (collection, envies, statistiques).

**Dépend de :** phases 2, 4, 7 et 10 (recommandé : partage visiteur et export films stabilisés).

### Migrations SQL prévues

```text
020_oeuvres_bd_metadata.sql
  - champs spécifiques BD sur oeuvres (série, tome, ISBN, …)
  - moncine_kind = 'bd'
```

| # | Tâche |
|---|--------|
| 11.1 | Page Mes BD (collection + wishlist) |
| 11.2 | Formulaires ajout / modification BD |
| 11.3 | Import CSV étendu (format BD) |
| 11.4 | Statistiques et filtres BD |
| 11.5 | Soumissions BD (réutilise phase 5) |
| 11.6 | Partage visiteur, export PDF et prêts BD (réutilise phases 7, 8 et 10) |

**Critère terminé :** une BD peut être ajoutée, classée en collection ou envie, notée et exportée.

---

## Phase 12 — Collections de magazines

**Objectif :** gérer des **collections de magazines** (titre de la revue, numéros, organisation) dans la bibliothèque du foyer, sur le même modèle que films et BD (collection / envies, fiche par numéro ou par parution).

**Dépend de :** phases 4 et 11 (recommandé : foyers + habitudes « type d’œuvre » déjà en place pour BD).

### Migrations SQL prévues

```text
021_magazine_collections.sql
  - magazine_collections (nom, éditeur, périodicité, description, …)
  - magazine_numeros (collection_id, numero, date_parution, titre_numero, …)
  - lien bibliotheque / oeuvres ou tables dédiées selon modèle retenu
  - moncine_kind = 'magazine' sur oeuvres si catalogue unifié
```

| # | Tâche |
|---|--------|
| 12.1 | Modèle de données magazines (collection + numéros) |
| 12.2 | Page **Mes magazines** (liste des collections, numéros possédés / manquants) |
| 12.3 | Ajout / édition d’une collection et d’un numéro |
| 12.4 | Intégration foyer (collection partagée) et envies personnelles |
| 12.5 | Import / export CSV magazines (schéma à définir) |
| 12.6 | Filtres et statistiques de base (par collection, par année) |

**Critère terminé :** une collection « Tintin magazine » (ex.) peut être créée, ses numéros référencés, et chaque numéro ajouté à la collection du foyer ou aux envies d’un membre.

---

## Phase 13 — Magazines PDF & lecteur

**Objectif :** associer un **fichier PDF** à un numéro de magazine, via la **couche stockage (phase 9)**, et proposer un **lecteur PDF** intégré.

**Dépend de :** phases 9 et 12 (stockage objets + numéros magazine en base).

### Migrations SQL prévues

```text
022_magazine_pdf.sql
  - magazine_fichiers (numero_id, stored_object_id, …)
  - métadonnées optionnelles (nombre de pages, langue)
```

| # | Tâche |
|---|--------|
| 13.1 | Upload PDF → `stored_objects` (local) |
| 13.2 | Fiche numéro : lien « Lire le PDF » |
| 13.3 | Lecteur PDF (streaming via ObjectStorage) |
| 13.4 | Contrôle d’accès (foyer ; pas d’URL publique vers le binaire) |
| 13.5 | Quotas espace disque / bucket |
| 13.6 | Doc sauvegarde share YunoHost (`MONCINE_MEDIA_PATH`) |

**Critère terminé :** PDF consultable depuis Moncine ; fichier sous `MONCINE_MEDIA_PATH`, pas sous `www/`.

### Points d’attention (phase 13)

- **Droits d’auteur** : usage personnel / foyer uniquement.
- **Performance** : streaming par pages.

---

## Import / export de données

Fonctionnalité transversale déjà partiellement en place :

| # | Tâche | Statut |
|---|--------|--------|
| I.1 | Export CSV (collection, envies, historique) | ✅ |
| I.2 | Import CSV bibliothèque | ✅ |
| I.3 | Import CSV catalogue (admin) | ✅ |
| I.4 | Export/import affiches (`posters/`) | ✅ |
| I.5 | Documentation utilisateur import/export | À enrichir |

---

## Checklist avant chaque release applicative

1. Numéro de version et `schema_version` cible documentés dans ce fichier
2. Fichiers SQL nouveaux uniquement (ne jamais modifier une migration déjà publiée)
3. Test **install fraîche** (`schema.sql` + toutes les migrations)
4. Test **upgrade** depuis la version précédente sur une base de test
5. Tests PHPUnit (`composer test`)
6. Notes de version : migrations, actions manuelles éventuelles
7. Entrée dans **`CHANGELOG.md`** et tag Git annoté **`vX.Y.Z`** (ex. `v0.8.0`)

---

## Décisions techniques

| Sujet | Choix retenu |
|-------|--------------|
| Authentification | Session PHP + `password_hash` |
| Collection foyer | `foyer_id` sur `bibliotheque` (collection) |
| Wishlist | `user_id` personnel |
| BD | Même table `oeuvres`, type `bd` via `moncine_kind` |
| Soumissions catalogue | Table `catalogue_soumissions` ; validation admin ; utilisateurs non admin ne créent plus d’œuvres directement (v0.7.4) |
| Notifications | Table `notifications` ; e-mail optionnel (`MailService`, `MONCINE_MAIL_FROM`) |
| Amis / foyers | Amis = socle ; **groupe famille** = ancien foyer, **créé par les utilisateurs** (plus par l’admin) ; table `foyers` + `group_members` (phase 6) |
| EAN catalogue | Table `oeuvre_eans` (oeuvre + support + ean unique) ; socle recherche d’achat (phase 6 bis) |
| Partage visiteur | Jeton hashé, scope collection\|wishlist, pages GET lecture seule (phase 7) |
| Prêts | Table `loans` liée à `bibliotheque` (phase 8) |
| Stockage fichiers | `MONCINE_MEDIA_PATH` (local, racine unique) (phase 9) |
| Export PDF | PDF généré côté serveur (phase 10) |
| Magazines | Collections + numéros (phase 12) ; PDF via ObjectStorage (phase 13) |
| Chemins données | `MONCINE_DATA_PATH` (SQLite, clés) ; `MONCINE_MEDIA_PATH` (objets, affiches) |

---

## Hors périmètre (pour plus tard)

- Application mobile native
- Sync multi-instances
- Marketplace entre foyers
- API publique
- Authentification à deux facteurs (2FA)

---

## Suivi de la roadmap

### Historique

- 2026-05-19 — **Phase 5 validée** — version **0.7.4** : soumissions catalogue, notifications, navigation catalogue/fiches, menus Compte / Paramètres / Gestion
- 2026-05-19 — Version **0.7.2** : profil (prénom, pseudo), menu Gestion / Paramètres, navigation Préc./Suiv. entre fiches film
- 2026-05-19 — Version **0.7.1** : dépôt d’affiche manuel sur une fiche catalogue (admin)
- 2026-05-16 — Phases **1**, **1 bis** et **2** livrées (comptes, mots de passe, champs exemplaire)
- 2026-05-19 — Roadmap recentrée sur les **fonctionnalités logicielles** ; séparation upstream / packaging externalisée
- 2026-05-19 — Version **0.7.0** : phase **4** (foyers, collection partagée, envies et historique personnels)
- 2026-05-19 — Version **0.6.0** : phase **3** (maintenance catalogue : doublons, fusion, journal, nettoyage affiches)
- 2026-05-19 — Version **0.51.0** : correction création premier compte, mise en page fiches sans affiche

---

## Liens utiles dans le code

| Sujet | Fichiers |
|-------|----------|
| Connexion DB + migrations | `lib/Database.php`, `lib/SchemaMigrator.php` |
| Chemins données | `lib/config.php` (`MONCINE_DATA_PATH`) |
| Utilisateur courant | `lib/UserContext.php`, `lib/FoyerRepository.php` |
| Connexion / session | `lib/Auth.php`, `lib/LoginThrottle.php` |
| Comptes (admin) | `www/utilisateurs.php`, `lib/UtilisateurRepository.php` |
| Mots de passe | `www/mon-compte.php`, `www/mot-de-passe-oublie.php` |
| Format exemplaire | `sql/migrations/005_*.sql`, `lib/CatalogSchema.php` |
| Import / export | `www/import.php`, `www/export.php` |
| Maintenance catalogue | `www/maintenance-catalogue.php`, `lib/CatalogMaintenance.php` |
| Soumissions & notifications | `lib/CatalogSubmission.php`, `lib/NotificationService.php`, `www/proposer-oeuvre.php`, `www/soumissions-catalogue.php` |
| Navigation listes | `lib/FilmListContext.php`, `lib/CatalogListContext.php` |
| Schéma | `sql/schema.sql` |
| CLI migrations | `lib/cli/migrate.php` |
| Journal des versions | `CHANGELOG.md` |
| Styles UI (pilules / filtres) | `www/assets/css/style.css` (`.ui-pill`, `.ui-pill-bar`) |
| Partage visiteur (v0.8.0) | `lib/ShareLinkService.php`, `www/partage.php`, `www/gerer-partages.php`, `017_share_links.sql` |
| EAN catalogue (v0.8.0) | `lib/OeuvreEanRepository.php`, `www/enregistrer-oeuvre-ean.php`, `023_oeuvre_eans.sql` |
| Cibles d’achat envies (v0.8.2) | `lib/WishlistTargetRepository.php`, `www/enregistrer-wishlist-cible.php`, `024_wishlist_targets.sql` |
| Profil public (v0.8.3) | `lib/UserPublicProfileService.php`, `www/utilisateur.php`, `templates/utilisateur.php` |
| Temps de vision cumulé (v0.8.4) | `lib/CollectionStats.php`, `templates/statistiques.php` |
| Sauvegarde base (v0.8.5) | `lib/DatabaseBackupService.php`, `www/admin-export-base.php`, `www/maintenance-catalogue.php` |
| Partage & accueil (v0.8.6) | `lib/ShareLinkShare.php`, `templates/home.php`, `templates/layout.php` |
| Recherche personnes (v0.8.7) | `lib/CatalogFilmRepository.php`, `www/personnes.php`, `templates/personnes.php` |

---

### Historique roadmap (récent)

- 2026-05-19 — **Version 0.8.8** : **partage visiteur** des versions recherchées sur les liens envies ; **« J’ai acheté »** avec choix d’une cible (support + EAN pré-remplis). Comparateur de prix reporté.
- 2026-05-19 — **Version 0.8.7** : **recherche acteur/réalisateur** sur tout le catalogue avec statut collection / envies ; accueil épuré.
- 2026-05-19 — **Version 0.8.6** : **accueil** (vignettes), **bouton profil**, **partage** des liens par e-mail et Bluesky.
- 2026-05-19 — **Version 0.8.5** : **sauvegarde / restauration** de la base SQLite complète (admin, sécurisée).
- 2026-05-19 — **Version 0.8.4** : **temps de vision cumulé** sur les statistiques ; correction **titre français TMDB** lors d’une correction par identifiant.
- 2026-05-19 — **Version 0.8.3** : **profil public** (stats, vignettes, listes lecture seule) pour amis et membres du groupe.
- 2026-05-19 — **Version 0.8.2** : saisie des **versions recherchées** sur les envies (`wishlist_targets`) ; **prochaine** : phase **7 bis** (partage, comparateur, « J’ai acheté »).
- 2026-05-19 — **Version 0.8.0** : phases **6 bis** (EAN catalogue) et **7** (partage visiteur) livrées ; liste partagée avec affiches et modes Liste / Vignettes.
- 2026-05-21 — **Phase 7 redéfinie** : partage visiteur (lien lecture seule Mes films / Mes envies + fiche film) **avant** les prêts ; anciennes phases 7–12 renumérotées en 8–13 ; export PDF séparé (phase 10).
- 2026-05-21 — **Phase 6 bis** : EAN multiples par œuvre catalogue (DVD / Blu-ray / 4K), préparation recherche d’achat.

---

*Dernière mise à jour : 19 mai 2026 — v0.8.8 livrée (phase 7 bis partielle) ; prochaine cible : **phase 8** (prêts) ou comparateur de prix si API retenue.*
