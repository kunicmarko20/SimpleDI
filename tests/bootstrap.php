<?php

const AUTOLOAD_FILE = __DIR__.'/../vendor/autoload.php';

if (is_file(AUTOLOAD_FILE)) {
    include_once AUTOLOAD_FILE;
} else {
    die('Unable to find autoload.php file, please use composer to load dependencies:

wget http://getcomposer.org/composer.phar
php composer.phar install

Visit http://getcomposer.org/ for more information.

');
}
