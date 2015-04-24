#!/bin/bash -x
#
# Configure export-one-db
#

# Download phpMyAdmin
~/src/debian-server-tools/package/phpmyadmin-get-sf.sh || exit 1

# Copy included files
pushd phpMyAdmin-*-english || exit 2
cat ../exp-o-pma-includes.txt|xargs -I {} cp -a --parents {} ../ || exit 3
popd || exit 4
rm -rf phpMyAdmin-*-english || exit 5

# Generate 2048 bit RSA encryption key
openssl genpkey -algorithm rsa -pkeyopt rsa_keygen_bits:2048 -out exp-o-private.key || exit 6
openssl rsa -in exp-o-private.key -pubout -out exp-o-public.pem || exit 7

# Setup configuration file
which pwgen &>/dev/null || exit 8
IV=$(pwgen 16 1)
SECRET=$(pwgen 30 1)
sed -i "s/'????????????????'/'${IV}'/" exp-o-config.php || exit 9
sed -i "s/'??????????????????????????????'/'${SECRET}'/" exp-o-config.php || exit 10

# Remote access
read -p "Enter user agent sting: " UA || exit 11
sed -i "s/<UA>/${UA}/" .htaccess || exit 12
IP="$(/sbin/ifconfig | grep -m1 -w -o 'inet addr:[0-9.]*' | cut -d':' -f2)"
read -p "Enter management server IP: " -i "$IP" MGMNT || exit 13
sed -i "s/<IP-REGEXP>/${MGMNT}/" .htaccess || exit 14
sed -i "s/<IP>/${MGMNT}/" .htaccess || exit 15

# How to backup
echo "wget -q -S --content-disposition --user-agent='${UA}' --header='X-Secret-Key: ${SECRET}' 'http://---/export-one-db.php"

# Clean up
rm README.md exp-o-pma-includes.txt exp-o-util.sh

echo "Don't upload exp-o-private.key!!!"
echo
echo "Save IV and private key."
echo -e "IV:\n${IV}"
cat exp-o-private.key
