<?php

define('BMU_VERSION', '0.2b');

require_once "phar://bmu.phar/BackMeUpUtils.php";
require_once "phar://bmu.phar/BackMeUp.php";

try {
	$bmu = new BackMeUp();
	$bmu->run();
} catch (Exception $e) {
	echo $e->getMessage() . "\n\n";
}
