<?php

# include -> local/php_interface/init.php

use Bitrix\Main\Loader;

Loader::includeModule('iblock');
Loader::includeModule('catalog');
Loader::includeModule('sale');

if (file_exists($_SERVER['DOCUMENT_ROOT']."/local/php_interface/includes/functions.php"))
	include_once($_SERVER['DOCUMENT_ROOT']."/local/php_interface/includes/functions.php");
