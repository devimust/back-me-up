<?php

define('BMU_VERSION', '0.3');
define('BMU_DATE', 'January 26, 2016');

require_once "phar://bmu.phar/BackMeUpUtils.php";
require_once "phar://bmu.phar/BackMeUp.php";

try {
	$bmu = new BackMeUp();
	$bmu->run();
} catch (Exception $e) {
	echo $e->getMessage() . "\n\n";
}
