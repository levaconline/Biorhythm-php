<?php 

#include 'Graph.php';

/**
 * Calculate biorhythm for specified day and return results in specified format.
 * Can be used as CLI or part of web app/API.
 * 
 * PHP 8.2.12
 * 
 * @author  Aleksandar Todorovic <levaconline@gmail.com> <aleksandar.todorovic.xyz@gmail.com> <aleksandar.todorovic.777@yandex.com>
 */
class Biorhythm 
{
    /**
     * Output format.
     *
     * @var string
     */
    public string $outputFormat = 'array'; // [or 'json']
     
    /**
     * Day of birth.
     *
     * @var int
     */
    private int $day;
    
    /**
     * Month of birth.
     *
     * @var int
     */
    private int $month;
    
    /**
     * Year of birth.
     *
     * @var int
     */
    private int $year;
    
    /**
     * Target day.
     */
    private int $targetDay;
	
    /**
     * Target month.
     *
     * @var int
     */
    private int $targetMonth;
    
    /**
     * Target year.
     *
     * @var int
     */
    private int $targetYear;
    
    /**
     * Birthday date (Unix time in seconds noon)
     *
     * @var int
     */
    private int $birthDate;

    /**
     * Target local noon (seconds). [default: today's noon]
     *
     * @var int
     */
    private int $targetNoon;
	
    /**
     * Time passed since birthdate noon till target day (in days)
     *
     * @var float
     */
    private float $daysPassed;
	
    /**
     * var containing messages, errors, etc.
     *
     * @var int
     */
    private  array $messages = [];
	
    /**
     * Resulting status - true (all OK) or false (something wnt wrong).
     *
     * @var bool
     */
    private bool $status = true;
	

    public function __construct(int $day, int $month, int $year, $targetDay = null, $targetMonth = null, $targetYear = null)
    {
	$this->day = $day;
	$this->month = $month;
	$this->year = $year;
		
	// If not set default is today as target day.
	$this->targetDay = $targetDay ?? date('d');
	$this->targetMonth = $targetMonth ?? date('m');
	$this->targetYear = $targetYear ?? date('Y');
		
	// Convert to unix ts.
	$this->birthDate = mktime( 12, 0, 0, $this->month, $this->day, $this->year); // Birth date - noon.
	$this->targetNoon = mktime( 12, 0, 0, $this->targetMonth, $this->targetDay, $this->targetYear); // Target date - noon.
    }

    /**
     * Beginning...
     *
     * @return mixed Formated results.
     */
    public function run()
    {
        // Validate input data.
	if(!$this->validateDate()){
	    return $this->result(['values'=>[], 'msg'=>$this->messages, 'status'=>$this->status]);
	}
		
        $this->passedDays(); // In days.
        #$graph = new Graph($this->getResult());
        #$graph->drawGraph();
	return $this->result($this->getResult());
    }

    /**
     * Calculate passed time since birthday (noon).
     * Biorhythm is not science, so we will allow calculate biorhuthm values for dates before birth.
     * Note: Dates BC not managed (but maybe could)
     *
     * @return void
     */
    private function passedDays(): void
    {
        // Special case: target date is birthdate.
        if ($this->birthDate == $this->targetNoon) {
            $this->daysPassed = 0.0;

            return;
        }
        
        // Keep origonal vars unchanged.
        $birthDate = $this->birthDate;
        $targetNoon = $this->targetNoon;
        
        // was it before 1.1.1970? Deal with negative numbers.
        if ($birthDate < 0) {
            $birthDate = abs($birthDate);
        }
        
        if ($targetNoon < 0) {
            $targetNoon = abs($targetNoon);
        }
        
        // Calculate delta days and take care about before/after 1.1.1970 times.
        
        // Case birth date before Unix era and target after, or oposite.
	if ( ($this->birthDate <= 0 && $this->targetNoon >= 0) || ($this->birthDate >= 0 && $this->targetNoon >= 0) ) { 
	    $this->daysPassed = round(($birthDate + $targetNoon) / 86400); // an day = 86400 seconds.
	// Case: Both birth date and target date are before Unix era or both are after 0 Unix time.
	} else if ($this->birthDate <= 0 && $this->targetNoon <= 0 && $this->birthDate > $this->targetNoon) { 
	    $this->daysPassed = round(($birthDate - $targetNoon) / 86400); // an day = 86400 seconds.
	} else if ($this->birthDate <= 0 && $this->targetNoon <= 0 && $this->birthDate < $this->targetNoon) { 
	    $this->daysPassed = round(($this->targetNoon + $this->birthDate) / 86400); // an day = 86400 seconds.
	} else if ($this->birthDate >= 0 && $this->targetNoon >= 0 && $this->birthDate > $this->targetNoon) { 
	    $this->daysPassed = round(($this->birthDate - $this->targetNoon) / 86400); // an day = 86400 seconds.
	} else {
	    $this->daysPassed = round(($this->targetNoon - $this->birthDate) / 86400);
	}
    }
 
    /**
     * Validate date (for resonable times)
     *
     * @return bool
     */
    private function validateDate(): bool
    {
        $non31DausMonths = [2, 4, 6, 9, 11];
        $this->status = false;

	//Does selected month contain 31 days?
	if (($this->day == '31' || $this->targetDay == 31)&& (!in_array($this->month, $non31DausMonths))) {
	    $this->messages['error'] =  "Invalid date.<br>{$this->month} or {$this->targetMonth}. have not 31 day.<br>";
            return false;
        // Check februarry - speciffic (28 or 29 days).
	} elseif (($this->month == '2' && ($this->day > '28' || $this->targetDay > '28') && ($this->year % 4 != '0')) || ($this->month == '2' && ($this->day > '29' || $this->targetDay > '29'))) { 
	    $this->messages['error'] = "Invalid date.<br>Selected February contains not $this->day dayas.<br>";
            return false;
        // BC?.
	} elseif ($this->year < 0 || $this->targetYear < 0) { 
	    $this->messages['error'] = "Invalid year: " . $this->year . " .<br>Yeares BC not allowed (for now).<br>";
            return false;
        }
        
        $this->status = true;
        return true;
    }

    /**
     * Calculate results.
     * Resulting values are between -1 and 1.
     * (Note it can be calculated in one step, but 2 step is more readable way (for debug))
     * e.g. $intellectual = round(sin(2*pi()*$this->daysPassed/33),2);
     *
     * @return array Results.
     */
    private function getResult(): array
    {
        // Find delta in the target day (floor to get vaues in target day between 0 and 1 ~ not necessary). 
	$deltaIntellectual = ($this->daysPassed/33)-floor($this->daysPassed/33);
	$deltaEmotional = ($this->daysPassed/28)-floor($this->daysPassed/28);
	$detaPhysical = ($this->daysPassed/23)-floor($this->daysPassed/23);
		
	// Calculate biorhythms
	$intellectual = round(sin(deg2rad(($deltaIntellectual)*360)),2);
	$emotional = round(sin(deg2rad(($deltaEmotional)*360)),2);
	$physical = round(sin(deg2rad(($detaPhysical)*360)),2);

        return [
            'values'=>[
                'intellectual' => $intellectual, 
                'emotional' => $emotional, 
                'physical' => $physical,
                'birthDate' => $this->birthDate,
                'targetNoon' => $this->targetNoon,
                'daysPassed' => $this->daysPassed,
                ], 
                 'msg'=>$this->messages, 
                 'status'=>$this->status,
            ];
    }
    
    /**
     * Format output. (at the moment only array and json.)
     * 
     * @param array $result Array that may be converted to json or returned as is.
     * @return array | json.
     */
    private function result(array $result)
    {
        switch($this->outputFormat){
            case 'array':
                return $result;
            case 'json':
                return json_encode($result);
        }
    }
}
