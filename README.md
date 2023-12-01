# Biorhythm-php
Biorhythm

How to use?

1. As CLI

// Example call via CLI: 
if (!isset($argv[1]) || !isset($argv[2]) || !isset($argv[3])) {
    echo "Lack of params. It must be called with: Birtgday, Birth Month and Birth year\n";
    echo "Patern: php Biorhythm.php Day Month Year [Target Day] [Target Month] [Target Year]\n";
    echo "php Biorhythm.php 12 1 1969\n";
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

In short:
=========
Biorhyrhm for current day (format: day month year):
php Biorhythm.php 12 1 1969 


Biorhyrhm for specified date (format: day month year target_day target_month target_year):
php Biorhythm.php 12 1 1969 2 7 1969


2. In PHP app:
Make form and pass the parans from form, It can be say something like following:

// Example: CALL via request
$day = (int)$_REQUEST['d'];
$month = (int)$_REQUEST['m'];
$year = (int)$_REQUEST['y'];
$targetDay = (int)$_REQUEST['td'];
$targetMonth = (int)$_REQUEST['tm'];
$targetYear = (int)$_REQUEST['ty'];


$bio = new Biorhythm($day, $month, $year, $targetDay, $targetMonth, $targetYear);
//$biorhythm = $bio->outputFormat = 'json'; # default is array.
$biorhythm = $bio->run();
