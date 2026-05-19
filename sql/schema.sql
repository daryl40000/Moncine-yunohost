-- Schéma Moncine — dvdthèque personnelle (catalogue + bibliothèque)
-- Exécuté automatiquement au premier lancement si la base n'existe pas.

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL DEFAULT '',
    email TEXT NOT NULL DEFAULT '',
    password_hash TEXT NOT NULL DEFAULT '',
    role TEXT NOT NULL DEFAULT 'user' CHECK (role IN ('admin', 'user')),
    actif INTEGER NOT NULL DEFAULT 1,
    last_login_at TEXT DEFAULT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_utilisateurs_email
    ON utilisateurs(email) WHERE email != '';

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    token_hash TEXT NOT NULL,
    expires_at TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    used_at TEXT DEFAULT NULL
);

CREATE INDEX IF NOT EXISTS idx_password_reset_token_hash
    ON password_reset_tokens(token_hash);

CREATE INDEX IF NOT EXISTS idx_password_reset_expires
    ON password_reset_tokens(expires_at);

-- Catalogue d’œuvres (métadonnées Moncine, enrichissement TMDB optionnel)
CREATE TABLE IF NOT EXISTS oeuvres (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titre TEXT NOT NULL,
    titre_original TEXT DEFAULT '',
    realisateur TEXT DEFAULT '',
    duree_min INTEGER DEFAULT 0,
    styles TEXT DEFAULT '',
    annee INTEGER DEFAULT 0,
    nationalite TEXT DEFAULT '',
    tmdb_id INTEGER DEFAULT 0,
    tmdb_media_type TEXT DEFAULT '',
    tmdb_tv_kind TEXT DEFAULT '',
    realisateur_tmdb_id INTEGER DEFAULT 0,
    acteur_1 TEXT DEFAULT '',
    acteur_1_tmdb_id INTEGER DEFAULT 0,
    acteur_2 TEXT DEFAULT '',
    acteur_2_tmdb_id INTEGER DEFAULT 0,
    acteur_3 TEXT DEFAULT '',
    acteur_3_tmdb_id INTEGER DEFAULT 0,
    poster_url TEXT DEFAULT '',
    synopsis TEXT DEFAULT '',
    moncine_kind TEXT DEFAULT 'film',
    omdb_imdb_id TEXT DEFAULT '',
    omdb_enriched_at TEXT DEFAULT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT NULL,
    UNIQUE (titre, realisateur)
);

CREATE INDEX IF NOT EXISTS idx_oeuvres_tmdb ON oeuvres(tmdb_id) WHERE tmdb_id > 0;

-- Bibliothèque personnelle (collection ou wishlist)
CREATE TABLE IF NOT EXISTS bibliotheque (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL DEFAULT 1 REFERENCES utilisateurs(id),
    oeuvre_id INTEGER NOT NULL REFERENCES oeuvres(id) ON DELETE CASCADE,
    statut TEXT NOT NULL DEFAULT 'collection' CHECK (statut IN ('collection', 'wishlist')),
    support_physique TEXT DEFAULT '',
    format_image TEXT DEFAULT '',
    format_son TEXT DEFAULT '',
    saga TEXT DEFAULT '',
    saga_ordre INTEGER DEFAULT 0,
    saison_numero INTEGER DEFAULT 0,
    saison_label TEXT DEFAULT '',
    ean TEXT DEFAULT '',
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE (user_id, oeuvre_id)
);

CREATE INDEX IF NOT EXISTS idx_bibliotheque_user_statut ON bibliotheque(user_id, statut);

-- Ancienne table films (installations héritées ; migration 013 la remplit)
CREATE TABLE IF NOT EXISTS films (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titre TEXT NOT NULL,
    titre_original TEXT DEFAULT '',
    realisateur TEXT DEFAULT '',
    duree_min INTEGER DEFAULT 0,
    format_image TEXT DEFAULT '',
    format_son TEXT DEFAULT '',
    support_physique TEXT DEFAULT '',
    styles TEXT DEFAULT '',
    saga TEXT DEFAULT '',
    saga_ordre INTEGER DEFAULT 0,
    annee INTEGER DEFAULT 0,
    nationalite TEXT DEFAULT '',
    tmdb_id INTEGER DEFAULT 0,
    tmdb_media_type TEXT DEFAULT '',
    tmdb_tv_kind TEXT DEFAULT '',
    realisateur_tmdb_id INTEGER DEFAULT 0,
    acteur_1 TEXT DEFAULT '',
    acteur_1_tmdb_id INTEGER DEFAULT 0,
    acteur_2 TEXT DEFAULT '',
    acteur_2_tmdb_id INTEGER DEFAULT 0,
    acteur_3 TEXT DEFAULT '',
    acteur_3_tmdb_id INTEGER DEFAULT 0,
    poster_url TEXT DEFAULT '',
    synopsis TEXT DEFAULT '',
    moncine_kind TEXT DEFAULT 'film',
    saison_numero INTEGER DEFAULT 0,
    saison_label TEXT DEFAULT '',
    ean TEXT DEFAULT '',
    omdb_imdb_id TEXT DEFAULT '',
    omdb_enriched_at TEXT DEFAULT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE (titre, realisateur)
);

-- film_id = bibliotheque.id (entrée personnelle)
CREATE TABLE IF NOT EXISTS historique (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    film_id INTEGER NOT NULL,
    date_vue TEXT NOT NULL DEFAULT (date('now')),
    note INTEGER,
    FOREIGN KEY (film_id) REFERENCES bibliotheque(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_historique_film ON historique(film_id);
CREATE INDEX IF NOT EXISTS idx_historique_date ON historique(date_vue);
