# Migrations historiques (My Webapp / dev mono-utilisateur)

Ces fichiers **ne sont plus exécutés** par le paquet YunoHost.

- Ils documentent l’évolution de l’ancienne application (`Moncine (origine)`).
- Une **install fraîche** du paquet utilise uniquement `sql/schema.sql` + `sql/migrations/001_…`.

Pour récupérer des données : export CSV depuis l’ancienne instance, puis import dans le paquet (voir `doc/migration-export-import.md`).
