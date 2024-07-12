<?php 

require 'ComparativeBiorhythm.php';

if (!isset($argv[1]) || !isset($argv[2]) || !isset($argv[3]) || !isset($argv[4]) || !isset($argv[5]) || !isset($argv[6])) {
    echo "\nERROR: No required params.\n\n";
    echo "Params (EU date order: day, month, year): persone 1 birth persone 1 day birth month and birth year are required,\n";
    echo "Try something like following:\n";
    echo "php comparative-demo-cli.php 27 1 2000 \n\n";
    die();
}

$day1 = $argv[1];
$month1 = $argv[2];
$year1 = $argv[3];

$targetDay = $argv[4] ?? null;
$targetMonth = $argv[5] ?? null;
$targetYear = $argv[6] ?? null;


$bio = new Biorhythm($day, $month, $year, $targetDay, $targetMonth, $targetYear);
//$biorhythm = $bio->outputFormat = 'json'; # default is array.
$biorhythm = $bio->run();

var_dump($biorhythm);

// Draw graph.
$graph = new Graph($biorhythm);
$graph->drawGraph();

