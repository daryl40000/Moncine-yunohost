#!/usr/bin/env bash
# Lance la suite PHPUnit (installe les dépendances si besoin).
set -euo pipefail
cd "$(dirname "$0")/.."

if [[ ! -x vendor/bin/phpunit ]]; then
  if [[ ! -f composer.phar ]]; then
    echo "Composer introuvable : installez composer ou placez composer.phar à la racine."
    exit 1
  fi
  php composer.phar install --no-interaction
fi

exec php vendor/bin/phpunit "$@"
