# Migration des données (ancienne prod → paquet YunoHost)

## Principe

1. **Exporter** depuis `Moncine (origine)` (CSV / ODS + affiches locales).
2. **Installer** le paquet Moncine (base vide, schéma paquet).
3. **Importer** sur `/import.php` dans cet ordre :
   - **Catalogue** (CSV ou ODS admin) — crée les œuvres et leurs ID
   - **Bibliothèque** par utilisateur (CSV ou ODS léger)
   - **ZIP affiches** (admin) — archive `posters/123.jpg` ou export « ZIP affiches locales »
4. Vérifier les comptages, puis basculer l’URL.

Alternative aux affiches : copier le dossier `www/posters/` sur le serveur (mêmes noms `123.jpg`), puis lancer « Gérer les affiches locales » ou réimporter le ZIP.

Pas de copie directe de `moncine.db` : le schéma évoluera (comptes, foyers, champs déplacés).

## Checklist

- [ ] Export collection (Mes films)
- [ ] Export wishlist (Mes envies) — colonne statut `wishlist` / `mes envies`
- [ ] Export historique (visions + notes)
- [ ] Sauvegarde `data/` et `www/posters/`
- [ ] Import catalogue → bibliothèque → ZIP affiches (ou copie `posters/`)
- [ ] Import sur paquet neuf
- [ ] Test sur URL de préproduction

## Formats supportés

- **Catalogue** : export admin CSV/ODS (`ID catalogue`, métadonnées œuvre)
- **Bibliothèque** : export personnel CSV/ODS (`ID catalogue`, support, statut, etc.)
- L’import « export complet » de l’ancienne webapp n’est **plus** pris en charge : utilisez les deux exports séparés ci-dessus.

## À développer (roadmap)

- Rapport post-import (films importés, lignes ignorées, doublons)
