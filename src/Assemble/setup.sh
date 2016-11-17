#!/bin/bash
cd "${0%/*}"

export DB_HOST="${DB_HOST:-127.0.0.1}"
export DB_NAME="${DB_NAME:-assemble}"
export DB_PORT="${DB_PORT:-3306}"
export DB_USER="${DB_USER:-assemble}"
export DB_PASS="${DB_PASS:-betterThanFacebookGroups}"

if [ $# -eq 0 ]; then
    read -p "Which user should we run these commands under? [$(whoami)]: " HTTP_USER
fi
HTTP_USER=${HTTP_USER:-$(whoami)}
EXEC_AS=""
COMPOSER_CMD="php composer.phar"

hash composer 2>/dev/null
if [ $? -eq 0 ]; then
    COMPOSER_CMD="composer"
elif [ ! -e composer.phar ]; then
    installComposer
fi

echo "--- Composer ready. ---"

installComposer() {
    echo "--- Composer Phar is missing, fixing... ---"
    $EXEC_AS curl https://getcomposer.org/installer | php
    echo "--- Downloaded composer. ---"
}
if [ ! -d ../../Logs ]; then
    $EXEC_AS mkdir ../../Logs
    chgrp -R $HTTP_USER  ../../Logs
    chmod -R 0764 ../../Logs
fi

$EXEC_AS $COMPOSER_CMD update
PATH="$PWD/vendor/bin":$PATH
echo "--- Installed/updated dependencies. ---"
cd ./Config/Propel || exit

existing(){
    $EXEC_AS propel migration:up
    $EXEC_AS propel migration:diff
    $EXEC_AS propel migration:migrate
    if [ ! $? -eq 0 ]; then
        if [ $# -eq 0 ]; then
            read -p $'\e[33m\e[1m>>> There is an issue with your database. Would you like to try resetting it?\e[0m\e[39m Answering \'y\' or \'yes\' will wipe all data (in the database). [y/n] ' -n 1 -r
        else
            REPLY="Y"
        fi

        echo    # (optional) move to a new line
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            $EXEC_AS propel sql:build --overwrite
            $EXEC_AS propel sql:insert
            REPLY=""
        else
            exit
        fi
    fi
}


$EXEC_AS propel config:convert
$EXEC_AS propel model:build
if [ -e ../../.existing-original-server ]; then
    existing
else
    if [ $# -eq 0 ]; then
        read -p $'\e[33m\e[1m>>> Is this a new install?\e[0m\e[39m Answering \'yes\' will wipe any existing database. [y/n] ' -n 1 -r
    else
        REPLY="Y"
    fi
    echo    # (optional) move to a new line
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        $EXEC_AS propel sql:build --overwrite
        $EXEC_AS propel sql:insert
    else
        existing
    fi
    REPLY=""
    touch ../../.existing-original-server
fi

echo "--- Propel finished. ---"
echo "--- Completely done! ---"
cd ../.. || exit

