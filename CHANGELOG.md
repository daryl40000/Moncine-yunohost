# Journal des versions (Moncine)

Format inspiré de [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/).
Les numéros suivent le [versionnement sémantique](https://semver.org/lang/fr/).

**Tags Git :** releases taguées `v0.7.x` puis **`v0.8.0`** à partir de cette version. Le tag historique `0.7` couvrait plusieurs versions intermédiaires non taguées individuellement.

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
