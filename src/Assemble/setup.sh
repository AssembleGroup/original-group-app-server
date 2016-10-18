#!/bin/bash
if [ ! -e composer.phar ]; then
    curl -s https://getcomposer.org/installer | php
    echo "--- Downloaded composer. ---"
else
    echo "--- Composer detected. ---"
fi

php composer.phar install -o
PATH=$(realpath ./vendor/bin):$PATH
echo "--- Installed/updated dependencies. ---"
cd ./Config/Propel || exit

existing(){
    propel migration:diff
    propel migration:up
}


propel config:convert
propel model:build
if [ -e ../../.existing-original-server ]; then
    existing
else
    read -p $'\e[33m\e[1m>>> Is this a new install?\e[0m\e[39m Answering \'yes\' will wipe any existing database. [y/n] ' -n 1 -r
    echo    # (optional) move to a new line
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        propel sql:build --overwrite
        propel sql:insert
    else
        existing
    fi
    touch ../../.existing-original-server
fi

echo "--- Propel finished. ---"
echo "--- Completely done! ---"
cd ../.. || exit

