#!/usr/bin/env bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PHP=`which php`
$PHP $DIR/minusl.php $@