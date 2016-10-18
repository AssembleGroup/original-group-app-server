#!/bin/sh
echo "Please note, you must run this through 'source env.sh' - otherwise the PATH of your current shell will be unaffected."
PATH=$(realpath ../../vendor/bin):$PATH
echo "Added Composer bin directory to path!"
