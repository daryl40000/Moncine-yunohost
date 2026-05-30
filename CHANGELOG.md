# Journal des versions (Moncine)

Format inspiré de [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/).
Les numéros suivent le [versionnement sémantique](https://semver.org/lang/fr/).

**Tags Git :** releases taguées `v0.7.x` puis **`v0.8.0`** à partir de cette version. Le tag historique `0.7` couvrait plusieurs versions intermédiaires non taguées individuellement.

---

## [0.8.8] — 2026-05-19

Phase **7 bis** (suite cibles d’achat sur les envies), hors comparateur de prix.

### Ajouté

- **Partage visiteur (envies)** : sur les liens « Mes envies », affichage en lecture seule des **versions recherchées** (support + EAN) — liste `/partage.php` et fiche `/partage-film.php`.
- **« J’ai acheté »** : choix d’une **version cible** (`wishlist_targets`) pour pré-remplir le support et l’EAN lors du passage en collection — fiche film et liste Mes envies.
- **Migration `025_ean_digits_only.sql`** : normalisation des EAN déjà en base (chiffres seuls).

### Amélioré

- **Mes envies** : choix de la version achetée en **liste déroulante** (lignes du tableau plus compactes qu’avec les boutons radio).

### Corrigé

- **EAN** : stockage, affichage et formulaires en **chiffres seuls** (plus d’espaces pour la lecture ni en base) ; correction automatique à l’ouverture d’une fiche collection.

### Reporté

- **Comparateur de prix** (phase 7 bis.2) : aucune API publique retenue pour l’instant.

### Tests

- `WishlistTargetsTest` (promotion avec cible, EAN sans espaces), `ShareFeaturesTest` (cibles sur lien envies), `OeuvreEanNormalizeTest`.

---

## [0.8.7] — 2026-05-19

### Ajouté

- **Recherche par acteur / réalisateur** (`/personnes.php`) : résultats sur **tout le catalogue partagé**, avec badge **Dans ma collection**, **Dans mes envies** ou **Pas dans ma liste** ; suggestions de noms issues du catalogue entier.

### Amélioré

- **Page d’accueil** : retrait des boutons redondants avec le menu (Voir mes films, Statistiques, Mon profil) — conservent Lancer le questionnaire et Ajouter film.

### Tests

- `PersonSearchTest`.

---

## [0.8.6] — 2026-05-19

### Ajouté

- **En-tête** : bouton **Mon profil** (icône utilisateur) à côté des notifications — ouvre votre profil public.
- **Page d’accueil** : **activité récente** sur **3 lignes** (comme le profil) — bandeaux horizontaux de vignettes : 5 derniers films vus, 5 derniers ajouts à la collection, 5 derniers ajouts aux envies (liens vers les fiches).
- **Profil public** : section « 5 derniers ajouts à la collection » (votre profil et celui des amis / groupe).
- **Liens de partage** : après création d’un lien, partage par **e-mail** (messagerie locale ou envoi serveur) et par **Bluesky** (intent) ; copie de l’URL ; URL mémorisée 24 h en session pour les liens récents.

### Tests

- `ShareLinkShareTest`, `ShareLinkSessionStoreTest`, `UserPublicProfileCollectionTest`.

---

## [0.8.5] — 2026-05-19

### Ajouté

- **Maintenance catalogue** : sauvegarde et restauration de la base SQLite complète (`moncine.db`) — catalogue, bibliothèques, utilisateurs, historique, envies, groupes, etc.
- **Export** : téléchargement d’un fichier `.db` via `/admin-export-base.php` (POST, mot de passe admin, CSRF, limite de fréquence).
- **Restauration** : remplacement de la base avec validation du fichier, confirmation **RESTAURER**, copie de secours automatique dans `data/db_snapshots/`.
- Journal admin : actions **export** et **restauration** de la base.

### Sécurité

- Accès **administrateur** uniquement ; **mot de passe** redemandé à chaque opération ; protection **CSRF** ; quotas session + IP (exports/restaurations et échecs de mot de passe) ; fichiers temporaires hors répertoire web.

### Tests

- `DatabaseBackupServiceTest`, `DatabaseBackupRestoreTest`.

---

## [0.8.4] — 2026-05-19

### Ajouté

- **Statistiques** : carte **temps de vision cumulé** depuis le début (durée de chaque film × nombre de visionnages, re-visions incluses) — affichage **2h 30min** sous un jour, **3j 5h 30min** au-delà.
- **Infobulle** sur le libellé de cette carte (icône **i** au survol) : explication du calcul et du format, sans texte permanent sous la carte.

### Corrigé

- **Correction TMDB par identifiant** : le **titre français** (fr-FR) de la fiche œuvre est mis à jour ; l’**enrichissement par titre** ne modifie pas le titre saisi.

### Tests

- `CollectionStatsDurationTest`, `CollectionStatsViewingDurationTest`.
- `FilmEnricherTmdbTitleTest`.

---

## [0.8.3] — 2026-05-19

### Ajouté

- **Profil public utilisateur** (`/utilisateur.php`) : visible par les **amis** et les **membres du même groupe** — pseudo, statistiques (collection, envies, films vus, films vus cette année), 5 derniers films vus et 5 derniers ajouts aux envies en **vignettes**.
- Listes complètes en lecture seule : **collection**, **envies** et **films vus** (date et note par vision ; filtre par année depuis les statistiques).
- Page **Mes amis** : section **membres du groupe** ; noms cliquables vers le profil (amis, demandes, groupe ; pas les comptes bloqués).
- Page **Mon groupe famille** : noms des membres cliquables vers le profil.

### Corrigé

- Profil public : les **5 derniers films vus** n’affichent plus des titres sans vision réelle (jointure SQL `historique` ↔ `bibliotheque`).
- Listes collection et envies sur le profil : affichage des films corrigé (`$films` / `$listFilms`).

### Tests

- `UserPublicProfileTest` (accès ami, membres du groupe, refus étranger, historique des visions).

---

## [0.8.2] — 2026-05-19

### Ajouté

- **Versions recherchées sur les envies** : table `wishlist_targets` (migration `024_wishlist_targets.sql`) — plusieurs combinaisons **support + EAN** par film en wishlist, distinctes de l’EAN catalogue et de l’exemplaire futur en collection.
- Fiche film (envie) : panneau « Versions que je cherche », ajout manuel ou depuis les EAN catalogue de l’œuvre.
- Liste **Mes envies** : colonne récapitulative des versions recherchées.

### Tests

- `WishlistTargetsTest` (ajouts multiples, promotion vers collection, import depuis EAN catalogue).

### Prochaine évolution (roadmap)

- Phase **7 bis** : affichage des versions sur le partage visiteur, comparateur de prix (support + EAN), pré-remplissage du support au « J’ai acheté ».

---

## [0.8.1] — 2026-05-19

### Sécurité

- **Partage visiteur** : limite anti brute-force par **adresse IP** (en plus de la session), quota de **10 liens actifs** par compte, en-têtes `X-Robots-Tag: noindex` et `Cache-Control: no-store` sur les pages `/partage.php` et `/partage-film.php`.
- **En-têtes globaux** : `Content-Security-Policy` (scripts depuis `/assets/js/` uniquement ; styles inline autorisés pour les graphiques), **HSTS** envoyé uniquement en HTTPS (production).
- **Recherche SQL** : échappement LIKE unifié via `LikePattern` dans tout le catalogue et les collections.
- Script inline du catalogue déplacé vers `app.js` (compatible CSP `script-src 'self'`).

### Tests

- `ShareSecurityTest`, `LikePatternTest`, `SecurityHeadersTest`.

---

## [0.8.0] — 2026-05-19

### Ajouté

- **Phase 6 bis — EAN catalogue** : table `oeuvre_eans`, gestion sur la fiche œuvre admin (un EAN par support DVD / Blu-ray / 4K), suggestion sur le formulaire « mon exemplaire ».
- **Phase 7 — Partage visiteur** : liens lecture seule (collection du foyer ou envies personnelles), pages publiques `/partage.php` et `/partage-film.php`, gestion `/gerer-partages.php`, expiration 90 jours, révocation, limite anti brute-force.

### Amélioré

- Page partagée visiteur : même confort que **Mes films** — affiches, bascule **Liste** / **Vignettes**, filtres par type (Tout, Films, Séries…), recherche et tri.

### Migrations

- `017_share_links.sql` — liens de partage (`share_links`)
- `023_oeuvre_eans.sql` — codes EAN catalogue (`oeuvre_eans`)

### Déploiement

Après mise à jour du code : `php lib/cli/migrate.php` (applique les migrations 017 et 023 si besoin).

---

## [0.7.10] — 2026-05-21

### Sécurité (fonctions sociales)

- Recherche utilisateurs : échappement des caractères spéciaux SQL `LIKE` (`%`, `_`) — une recherche « % » ne liste plus tout le monde.
- Limitation d’abus : max **20 demandes d’ami / 24 h** et **30 recherches / minute** par compte.
- **Blocage** d’utilisateur : plus de demande d’ami, plus d’invitation groupe, masqué de la recherche ; liste et déblocage dans **Mes amis**.
- Page `bloquer-utilisateur.php` (POST + CSRF) depuis la recherche.

### Déploiement

- Aucune migration SQL.

---

## [0.7.9] — 2026-05-21

### Amélioré

- Lisibilité des liens sur thème sombre (couleurs dédiées, compatibilité `color-scheme: dark`).
- Onglets **Mes films** (Tout, Film, Série…) et **Liste / Vignettes** : contraste stable (texte foncé sur fond doré), y compris pour les liens déjà visités (`:visited`).
- Filtres **Support physique** et onglets **Mes envies / Envies du groupe** : même composant visuel.

### Technique (dette priorité haute)

- Composant CSS réutilisable **`.ui-pill`** / **`.ui-pill-bar`** pour les filtres et onglets (remplace les anciennes classes par page).
- Règles de liens de contenu simplifiées (sélecteurs ciblés `.lead`, `.hint`, etc. — plus de longue chaîne `:not()` sur `main`).
- Fichier **`CHANGELOG.md`** ajouté pour documenter les releases.

### Déploiement

- Aucune migration SQL.
- Remplacer les fichiers ; vider le cache navigateur si besoin (Ctrl+F5).

---

## [0.7.8] — 2026-05-19

### Ajouté

- **Envies du groupe** : agrégation par œuvre, tri par nombre de demandes, liste des votants, bouton « Moi aussi ».
- Ajout en un clic dans la bibliothèque après proposition catalogue acceptée.
- Notifications enrichies (proposition acceptée → lien vers ajout rapide).

---

## [0.7.7] — 2026-05-19

### Ajouté

- **Amis** : demandes, acceptation, refus.
- **Groupes famille** créés par les utilisateurs (remplace la création de foyers par l’admin).
- Invitations au groupe ; `/foyers.php` admin en lecture seule.

### Changé

- Nouveaux comptes sans foyer assigné automatiquement.

---

## [0.7.6] — 2026-05-19

### Ajouté

- Ville optionnelle sur le profil.
- Recherche d’utilisateurs par pseudo / ville.
- Opt-out « masquer mon profil de la recherche ».
- Cloche de notifications compacte dans l’en-tête.

---

## [0.7.4] — 2026-05-19

### Ajouté

- Soumissions au catalogue (proposer, valider, refuser).
- Notifications in-app et par e-mail.
- Navigation Préc./Suiv. entre fiches catalogue et films.

---

## [0.7.0] — 2026-05-19

### Ajouté

- Foyers et collection partagée.
- Envies et historique personnels par utilisateur.

---

## Liens

- Détail des phases futures : [ROADMAP.md](ROADMAP.md)
- Installation et usage : [README.md](README.md)
