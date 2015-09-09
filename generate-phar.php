<?php

$srcRoot = dirname(__FILE__) . '/src';
$buildRoot = dirname(__FILE__) . '/bin';
$projectName = 'bmu';

if (file_exists($buildRoot . '/' . $projectName .  '.phar')) {
	unlink($buildRoot . '/' . $projectName .  '.phar');
}

try {
	$phar = new Phar($buildRoot . '/' . $projectName .  '.phar', 0, $projectName .  '.phar');
	$phar->buildFromDirectory($srcRoot);

	$defaultStub = $phar->createDefaultStub('index.php');

	$phar->setStub("#!/usr/bin/env php \n" . $defaultStub);
} catch (Exception $e) {
	echo $e->getMessage() . "\n\n";
}
