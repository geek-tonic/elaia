#!/bin/bash
set -e

until bash -c "echo > /dev/tcp/db/3306" 2>/dev/null; do
  echo "En attente de la base de données..."
  sleep 3
done
echo "Base de données accessible !"

if [ ! -f /var/www/html/wp-config.php ]; then
  sudo wp config create \
    --path=/var/www/html \
    --dbname=wordpress \
    --dbuser=user \
    --dbpass=password \
    --dbhost=db \
    --allow-root
  echo "wp-config.php créé !"
else
  sudo wp config set DB_HOST db --path=/var/www/html --allow-root
  sudo wp config set DB_NAME wordpress --path=/var/www/html --allow-root
  sudo wp config set DB_USER user --path=/var/www/html --allow-root
  sudo wp config set DB_PASSWORD password --path=/var/www/html --allow-root
fi

if ! sudo wp core is-installed --path=/var/www/html --allow-root 2>/dev/null; then
  sudo wp core install \
    --path=/var/www/html \
    --url="http://localhost:8080" \
    --title="Elaia Dev" \
    --admin_user="admin" \
    --admin_password="admin" \
    --admin_email="dev@elaia.local" \
    --allow-root
  echo "WordPress installé !"
fi

sudo wp plugin activate elaia-plugin --path=/var/www/html --allow-root
echo "Plugin elaia-plugin activé !"

cat > /home/vscode/.welcome <<'EOF'

==========================================
  WordPress prêt !
  Site      : http://localhost:8080
  Admin     : http://localhost:8080/wp-admin
  Login     : admin
  Password  : admin
==========================================

EOF

if ! grep -q "welcome-elaia" /home/vscode/.bashrc 2>/dev/null; then
  cat >> /home/vscode/.bashrc <<'EOF'

# welcome-elaia
if [ -f "$HOME/.welcome" ]; then
  cat "$HOME/.welcome"
fi
EOF
fi
