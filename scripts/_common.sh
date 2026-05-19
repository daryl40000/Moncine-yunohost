#!/bin/bash
# Fonctions communes — paquet YunoHost Moncine
#
# Appelées par scripts/install, upgrade, restore.
# Variables fournies par YunoHost : $app, $install_dir, $data_dir, $domain, $path, …

MONCINE_PACKAGE_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# Droits sur data/ et moncine.db (lecture/écriture pour PHP-FPM = utilisateur $app).
moncine_fix_data_permissions() {
    if [[ -z "${data_dir:-}" || ! -d "${data_dir}" ]]; then
        return
    fi
    chown -R "${app}:www-data" "${data_dir}"
    chmod 750 "${data_dir}"
    find "${data_dir}" -maxdepth 1 -type f \( -name 'moncine.db' -o -name 'moncine.db-*' \) -exec chmod 660 {} + 2>/dev/null || true
}

# Crée ou met à jour moncine.db (toujours en tant que $app, jamais en root).
moncine_run_migrate() {
    local migrate_php="${install_dir}/lib/cli/migrate.php"
    if [[ ! -f "${migrate_php}" ]]; then
        ynh_exit 1 --message="migrate.php introuvable dans ${install_dir}/lib/cli/"
    fi

    moncine_prepare_data_dir

    local base_url=""
    if [[ -n "${domain:-}" ]]; then
        base_url="https://${domain}${path}"
    fi

    ynh_print_info "Application des migrations SQL Moncine (utilisateur ${app})…"
    sudo -u "${app}" env \
        MONCINE_DATA_PATH="${data_dir}" \
        MONCINE_BASE_URL="${base_url}" \
        php "${migrate_php}" \
        || ynh_exit 1 --message="Échec des migrations Moncine"

    moncine_fix_data_permissions
}

# Catalogue + affiches depuis install_seed/ (installation neuve, catalogue vide uniquement).
moncine_apply_install_seed() {
    local seed_php="${install_dir}/lib/cli/install-seed.php"
    if [[ ! -f "${seed_php}" ]]; then
        ynh_print_info "install-seed.php absent — graine d’installation ignorée."
        return 0
    fi

    moncine_prepare_install_seed_dir

    ynh_print_info "Graine d’installation (catalogue / affiches) si fichiers présents…"
    sudo -u "${app}" env \
        MONCINE_DATA_PATH="${data_dir}" \
        php "${seed_php}" \
        || ynh_exit 1 --message="Échec de la graine d’installation Moncine (install_seed/)"
}

# Dossier persistant + copie optionnelle depuis le paquet (CSV/ZIP non versionnés).
moncine_prepare_install_seed_dir() {
    mkdir -p "${data_dir}/install_seed"
    if [[ -d "${MONCINE_PACKAGE_ROOT}/install_seed" ]]; then
        local f
        for f in "${MONCINE_PACKAGE_ROOT}/install_seed/"*; do
            [[ -f "${f}" ]] || continue
            local base
            base="$(basename "${f}")"
            if [[ ! -f "${data_dir}/install_seed/${base}" ]]; then
                cp -a "${f}" "${data_dir}/install_seed/"
            fi
        done
    fi
    moncine_fix_data_permissions
}

# Copie le code PHP depuis ce dépôt vers /var/www/moncine (install depuis chemin local).
moncine_copy_sources() {
    local dest="${1:?}"

    if [[ ! -d "${MONCINE_PACKAGE_ROOT}/www" ]]; then
        ynh_exit 1 --message="Paquet incomplet : ${MONCINE_PACKAGE_ROOT}/www introuvable (réinstallez depuis le dossier Moncine complet)."
    fi

    mkdir -p "${dest}/www/posters"
    rsync -a --delete --exclude 'posters/' \
        "${MONCINE_PACKAGE_ROOT}/www/" "${dest}/www/"

    # templates/ : pages HTML (obligatoire — View::render cherche MONCINE_ROOT/templates/)
    if [[ ! -d "${MONCINE_PACKAGE_ROOT}/templates" ]]; then
        ynh_exit 1 --message="Paquet incomplet : ${MONCINE_PACKAGE_ROOT}/templates introuvable."
    fi
    rsync -a --delete "${MONCINE_PACKAGE_ROOT}/templates/" "${dest}/templates/"

    local item
    for item in lib sql doc install_seed; do
        if [[ -d "${MONCINE_PACKAGE_ROOT}/${item}" ]]; then
            rsync -a --delete "${MONCINE_PACKAGE_ROOT}/${item}/" "${dest}/${item}/"
        fi
    done
}

# Dossier persistant YunoHost : base SQLite + clé TMDB (hors /var/www).
moncine_prepare_data_dir() {
    mkdir -p "${data_dir}"
    moncine_fix_data_permissions
}
