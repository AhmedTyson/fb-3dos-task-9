<?php
require __DIR__ . '/vendor/autoload.php';
$rc = new ReflectionClass('OpenSpout\Common\Entity\Row');
foreach($rc->getMethods() as $m) { echo $m->getName() . "\n"; }
