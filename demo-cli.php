<?php 

require 'Biorhythm.php';

if (!isset($argv[1]) || !isset($argv[2]) || !isset($argv[3])) {
    echo "\nERROR: No required params.\n\n";
    echo "Params (EU date order: day, month, year): birth day birth month and birth year are required,\n";
    echo "Try something like following:\n";
    echo "php demo-cli.php 27 1 2000 \n\n";
    die();
}

$day = $argv[1];
$month = $argv[2];
$year = $argv[3];

$targetDay = $argv[4] ?? null;
$targetMonth = $argv[5] ?? null;
$targetYear = $argv[6] ?? null;


$bio = new Biorhythm($day, $month, $year, $targetDay, $targetMonth, $targetYear);
//$biorhythm = $bio->outputFormat = 'json'; # default is array.
$biorhythm = $bio->run();

var_dump($biorhythm);
