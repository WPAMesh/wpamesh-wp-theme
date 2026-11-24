#!/bin/sh
# Builds a local copy of wpamesh.net at http://localhost:8080 from the WXR export in ../exports.
# Media isn't imported: the live site's Cloudflare challenge blocks the importer's downloads.
set -e
cd "$(dirname "$0")"

docker compose up -d
wp() { docker compose run --rm -T cli wp "$@"; }

until wp db check >/dev/null 2>&1; do sleep 2; done

if ! wp core is-installed 2>/dev/null; then
    wp core install --url=http://localhost:8080 --title=WPAmesh --admin_user=admin --admin_password=admin \
        --admin_email=admin@example.com --skip-email
fi

wp rewrite structure '/posts/%postname%/' --hard
wp plugin install --activate secure-custom-fields wordpress-importer forminator wp-user-manager announcer
wp theme activate wpamesh-theme
wp plugin activate wpamesh-sites

export_file=$(ls ../exports/*.xml | tail -n 1)
wp import "/exports/$(basename "$export_file")" --authors=create --skip=attachment

wp option update show_on_front page
wp option update page_on_front "$(wp post list --post_type=page --name=wpamesh-home --field=ID)"
