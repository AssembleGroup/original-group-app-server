#!/bin/bash
if [ ! -e composer.phar ]; then
    echo "--- Composer Phar is missing, fixing... ---"
    curl https://getcomposer.org/installer | php
    echo "--- Downloaded composer. ---"
else
    echo "--- Composer detected. ---"
fi

php composer.phar install -o
PATH=$(realpath ./vendor/bin):$PATH
echo "--- Installed/updated dependencies. ---"
cd ./Config/Propel || exit

existing(){
    propel migration:up
    if [ ! $? -eq 0 ]; then
        read -p $'\e[33m\e[1m>>> There is an issue with your database. Would you like to try resetting it?\e[0m\e[39m Answering \'y\' or \'yes\' will wipe all data (in the database). [y/n] ' -n 1 -r
        echo    # (optional) move to a new line
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            propel sql:build --overwrite
            propel sql:insert
        else
            exit
        fi
    fi
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

