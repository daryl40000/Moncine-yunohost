# Installer Moncine sur YunoHost (tests)

## Prérequis

- Serveur **YunoHost 12.x** recommandé (helpers 2.1)
- PHP **8.4** avec sqlite3, mbstring, intl, curl (voir `manifest.toml`)
- Accès **admin** (SSH)

## Installation depuis ce dépôt

1. Copier le projet sur le serveur :

   ```bash
   scp -r Moncine root@votre-serveur:/tmp/Moncine
   ```

2. Installer (adapter le domaine) :

   ```bash
   sudo yunohost app install /tmp/Moncine \
     --force \
     -a "domain=collection.cineconcept.fr&path=/&init_main_permission=visitors"
   ```

   - **`--force`** : confirme l’installation d’une app hors catalogue
   - **`-a`** : une seule chaîne `domain=…&path=…` (pas d’option `-p` séparée)
   - Si YunoHost demande une confirmation texte, taper exactement : `Yes, I understand`

3. Ouvrir `https://votre-domaine/` puis **premier compte** : `/premier-compte.php`

## Mise à jour du code

```bash
cd /tmp/Moncine && git pull
sudo yunohost app upgrade moncine --force -u /tmp/Moncine
```

Le paquet copie `www/`, `lib/`, `sql/`, `doc/` et **`templates/`** vers `/var/www/moncine/`.

## Infos sur l’app installée

```bash
sudo yunohost app info moncine
```

Le dossier des données est en général `/home/yunohost.app/moncine/` (`MONCINE_DATA_PATH`).

## Dépannage

| Problème | Solution |
|----------|----------|
| **413 Request Entity Too Large** (import ZIP affiches) | Limite nginx/PHP trop basse. Mettre à jour le paquet (`yunohost app upgrade moncine -u …`) : nginx `85M`, PHP `upload_max_filesize` / `post_max_size` à `85M`. Puis `sudo systemctl reload nginx` et `php8.4-fpm`. |
| **500 — readonly database** | Corrigé dans le paquet : migrations en `sudo -u moncine`. Réinstallez après mise à jour du dépôt. |
| **Template introuvable** | Dossier `templates/` manquant → `git pull` puis `yunohost app upgrade moncine -u /tmp/Moncine` |
| **500 autre** | `sudo tail -30 /var/log/nginx/VOTRE-DOMAINE-error.log` |
| Dossier data | `ls -la /home/yunohost.app/moncine/` — `moncine.db` doit être `moncine:www-data` |
| Test manuel | `sudo -u moncine env MONCINE_DATA_PATH=/home/yunohost.app/moncine php /var/www/moncine/lib/cli/migrate.php` |

## Désinstallation

```bash
sudo yunohost app remove moncine
sudo yunohost app remove moncine --purge   # supprime aussi les données
```
