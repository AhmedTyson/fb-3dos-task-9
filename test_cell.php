<?php
require __DIR__ . '/vendor/autoload.php';
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Style\Style;
$cell = Cell::fromValue(123);
$cell2 = $cell->withStyle(new Style());
echo get_class($cell2);
