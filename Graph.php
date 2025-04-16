<?php 

/**
 * Generate biorhythm image for specified day and longer period of biorhythm (intelectuel: 33 days).
 * 
 * @author  Aleksandar Todorovic <levaconline@gmail.com> <aleksandar.todorovic.xyz@gmail.com> <aleksandar.todorovic.777@yandex.com>
 */
class Graph
{
    #################################################
    ## PUBLIC ACCESS VARS  - CAN BE SET DYNAMICALY ##
    #################################################
    
    // Image Size (in px).
    public int $canvasWidth = 1920; // 800; // px
    public int $canvasHeight = 1080; // 600; // px
    
    // Colors definitions.
    public array $colorBackground = [222, 222, 222]; // RGB
    public array $colorText = [100, 100, 100]; // RGB
    public array $colorIntellectual = [255, 0, 0]; // RGB
    public array $colorEmotional = [255, 0, 255]; // RGB
    public array $colorPhysical = [0, 150, 0]; // RGB
    public array $colorFill = [50, 50, 50]; // RGB
    public array $colorCritical = [255, 200, 0]; // RGB
    
    public float $marginLeftRightPercents = 2.0; // in %.
    public float $marginTopPercents = 3.0; // in %.
    public float $marginBottomPercents = 7.0; // in %.
    
    
    #########################
    ## PRIVATE ACCESS VARS ##
    #########################
    
    /**
     * TTFont
     *
     * string $font
     */
    private string $font = 'fonts/Arial regular.ttf';

    /**
     * Dot density in graph.
     * In huge image resolution distance between dots in graph can be prety big. 
     * This var can increase density of dots in high resolutions.Smaller number higher density.
     *
     * string $density
     */
    public float $density = 0.1; 

    /**
     * Set title size.
     *
     * * @var int.
     */
    private int $titleSize = 14;
    
    /**
     * Set text size - General text size.
     *
     * * @var int.
     */
    private int $textSize = 12; // General text size.
    
    /**
     * Set text size. - Days segments under graph.
     *
     * * @var int.
     */
    private int $daysTextSize = 2; 
    
    /**
     * Set output image name.
     *
     * * @var string.
     */
    private string $imageName = "biorhythm.png";

    /**
     * Set title text
     *
     * @var string.
     */
    private string $title = "BIORHYTHM";

    /**
     * Birthday date (Unix time in seconds noon)
     *
     * @var int
     */
    private int $birthDate;

    /**
     * Target date local noon (seconds). [default: today's noon]
     *
     * @var int
     */
    private int $targetNoon;
    
    private int $backgroundColor;
    private int $textColor;
    private int $physicalColor;
    private int $emotionalColor;
    private int $intellectualColor;
    private int $fillColor;
    private int $criticalColor;
    
    // Values
    private float $intellectual;
    private float $emotional;
    private float $physical;
    private float $daysPassed;

    private int $marginLeftRight; // in px
    private int $marginTop; // px
    private int $marginBottom; // px
    
    private array $yCentralAxis;
    private array $xAxis;
    private array $drawZone;

    private array $criticalPoints = [];
    
    private  GdImage $img;
    
    
    /**
     * Set vars in constructor using containing values.
     * 
     * @param array  $values Keys: float ["values"]['intellectual'], float ["values"]['emotional'], float ["values"]['physical'], int ["values"]['birthDate'], int ["values"]['targetNoon']
     */
    public function __construct(array $values)
    {
        $this->intellectual = $values["values"]['intellectual'];
        $this->emotional = $values["values"]['emotional'];
        $this->physical = $values["values"]['physical'];
        
        $this->birthDate = $values["values"]['birthDate'];
        $this->targetNoon = $values["values"]['targetNoon']; 
        $this->daysPassed = $values["values"]['daysPassed'];
    }
        
    public function drawGraph(): void
    {
        // Delete old graph if exists.
        $imgPath = __DIR__ . "/" . $this->imageName;
        @unlink($imgPath);


        // Init image.
        $this->img = @imagecreatetruecolor($this->canvasWidth, $this->canvasHeight) or die ("Image create failed.");
        
        // Set measures based of percents (margins, etc.)
        $this->setMeasures();
        
        // Define colors.
        $this->defineColors();
        
        // Draw border.
        $this->drawBorder();
        
        // Drawing zone.
        $this->drawingZone();
        
        // Draw axes.
        $this->drawAxes();
        
        // Draw texts.
        $this->drawTexts();
        
        // Drav sines
        $this->drawSines();
        
        // Mark targer values on grph (in the middle y axis).
        $this->markValuesOnYAxis();

        $this->markCriticalPoints();
        
        // Make image file (png).
        imagepng ($this->img, $this->imageName);
        imagedestroy($this->img);

        // Check of graph exist

        if (file_exists($imgPath)) {
            echo "\nGraph created: " . $imgPath . "\n\n";
        } else {
            echo "\nError: Graph not created.\n\n";
        }

    }
    
    private function drawSines(): void
    {
        $passedDays = $this->daysPassed;
        $dayIntelectual = ($passedDays / 33) - floor($passedDays / 33);
        $dayEmotional = ($passedDays / 28) - floor($passedDays / 28);
        $dayPhysical = ($passedDays / 23) - floor($passedDays / 23);
        
        $drawAreaHeight = $this->canvasHeight - ($this->marginBottom + $this->marginTop);
        $halfOfDrawAreaH = $drawAreaHeight / 2;
        $absY0 = $halfOfDrawAreaH + $this->marginTop; // True position of x axis 0.
        $width = ($this->canvasWidth - $this->marginLeftRight * 2) / 33;
        
        for ($st = 1.0; $st < $this->canvasWidth - $this->marginLeftRight * 2; $st = $st + $this->density) {
            $x = $st + $this->marginLeftRight; // X correction.
            
            // Intellectual
            // 16.5 is half of 33 (move target date to middle of graph.)
            $yi =  $halfOfDrawAreaH - ($halfOfDrawAreaH * sin(deg2rad($st + (($dayIntelectual * 33) - 16.5) * $width) / ($width * 33 / 360))) + $this->marginTop;
            imagesetpixel($this->img, $x, $yi, $this->intellectualColor);
            
            // Emotional
            $ye =  $halfOfDrawAreaH - ($halfOfDrawAreaH * sin(deg2rad($st + (($dayEmotional * 28) - 16.5) * $width) / ($width * 28 / 360))) + $this->marginTop;
            imagesetpixel($this->img, $x, $ye, $this->emotionalColor);
            
            // Physical
            $yf =  $halfOfDrawAreaH - ($halfOfDrawAreaH * sin(deg2rad($st + (($dayPhysical * 23) - 16.5) * $width) / ($width * 23 / 360))) + $this->marginTop;
            imagesetpixel($this->img, $x, $yf, $this->physicalColor);

            // Memoty criticals.
            if ($this->inColision($yi, $ye)) {
                $this->criticalPoints[] = [$x, (int)$yi];
            }
            
            if ($this->inColision($yi, $yf)) {
                $this->criticalPoints[] = [$x,  (int)$yf];
            }
            
            if ($this->inColision($ye, $yf)) {
                $this->criticalPoints[] = [$x,  (int)$ye];
            }
            
            if ($this->inColision($yi, $absY0)) {
                $this->criticalPoints[] = [$x,  (int)$yi];
            }
            
            if ($this->inColision($yf, $absY0)) {
                $this->criticalPoints[] = [$x,  (int)$yf];
            }
            
            if ($this->inColision($ye, $absY0)) {
                $this->criticalPoints[] = [$x,  (int)$ye];
            }
        }
    }

    private function inColision($val1, $val2): bool
    {
        $delta = 3; // Fine tunning for detec collision.

        if ($val1 >=  $val2) {
            return ($val1 - $val2) < $delta;
        } else {
            return ($val2 - $val1) < $delta;
        }
    }
    
    private function drawTexts(): void
    {
        $this->drawTopMarginTexts(); // (title, birthdate & target date.)
        $this->drawBottomTexts(); // Date time of script execution.
        $this->drawDataTexts(); // Texts in draw area (Biorhythm values)
        $this->drawLeftMarginTexts(); // In left margin (Y coordinates: 1, 0, -1)
    }

    private function drawDataTexts(): void
    {
           // Prepare texts.
        $texetIntellectual = "Intellectual: " . $this->intellectual;
        $texetEmotional = "Emotional: " . $this->emotional;
        $texetPhysical = "Physical: " . $this->physical;

              // Texts nside draw area. (birthdate & target date.)
              $data = $this->textSize((float)$this->textSize, $this->font, (string)$texetIntellectual);
        imagettftext(
            $this->img, 
            $this->titleSize, 
            0, 
            $this->marginLeftRight + $data['height'],
            $this->marginTop + $data['height'] * 3,
            $this->intellectualColor, 
            $this->font, 
            $texetIntellectual,
        );
        
        imagettftext(
            $this->img, 
            $this->titleSize, 
            0, 
            $this->marginLeftRight + $data['height'],
            $this->marginTop + $data['height'] * 6,
            $this->emotionalColor, 
            $this->font, 
            $texetEmotional,
        );
        
        imagettftext(
            $this->img, 
            $this->titleSize, 
            0, 
            $this->marginLeftRight + $data['height'],
            $this->marginTop + $data['height'] * 9,
            $this->physicalColor, 
            $this->font, 
            $texetPhysical,
        );
    }
    
    private function drawingZone(): void
    {
        // Graph zone.
        imagefilledrectangle(
            $this->img, 
            $this->marginLeftRight,
            $this->marginTop, 
            $this->drawZone['x2'], 
            $this->drawZone['y2'], 
            $this->fillColor
        );
    }
    
    private function drawBorder(): void
    {
        // Draw edges.
        imagerectangle(
            $this->img, 
            1, 
            1, 
            $this->canvasWidth - 1, 
            $this->canvasHeight - 1, 
            $this->textColor
        );
    }
    
    private function drawTopMarginTexts(): void
    {
        $textBirthDate = "Birth Date: " . date('d. m. Y.', $this->birthDate);
        $textTargetDate = "Target Date: " . date('d. m. Y. H:i:s', $this->targetNoon);

        // Write title in top margin, left = canvas begin, top = calculated base in margin height)
        $data = $this->textSize((float)$this->titleSize, $this->font, (string)$this->title);
        $textY = $this->marginTop - $data['height'] / 2;
        
        imagettftext(
            $this->img, 
            $this->titleSize, 
            0, 
            $this->marginLeftRight,
            $textY,
            $this->textColor, 
            $this->font, 
            $this->title
        );

        // Inside top margin. (birthdate & target date.)
        // Middle align.
        $data = $this->textSize((float)$this->titleSize, $this->font, (string)$textBirthDate);
        imagettftext(
            $this->img, 
            $this->titleSize, 
            0, 
            ($this->canvasWidth - $this->marginLeftRight * 2) / 2 - $data['width'] / 2,
            $textY,
            $this->textColor, 
            $this->font, 
            $textBirthDate,
        );
        
        // Right allign/
        $data = $this->textSize((float)$this->titleSize, $this->font, (string)$textTargetDate);
        imagettftext(
            $this->img, 
            $this->titleSize, 
            0, 
            ($this->canvasWidth - $this->marginLeftRight) - $data['width'],
            $textY,
            $this->textColor, 
            $this->font, 
            $textTargetDate,
        );
    }

    private function markCriticalPoints(): void
    {
        if (!empty($this->criticalPoints)) {
            foreach ($this->criticalPoints as $critical) {
                // Draw circle on critical point.
                imagefilledellipse(
                    $this->img,
                    $critical[0], // x
                    $critical[1], // y
                    7,
                    7,
                    $this->criticalColor
                );

            }
        }
    }

    private function drawLeftMarginTexts()
    {
        $data = $this->textSize((float)$this->titleSize, $this->font, (string)'+1');
        $x = $this->marginLeftRight - $data['width'];
        $textHeightHalf = $data['height'] / 2;

        imagettftext(
            $this->img, 
            $this->titleSize, 
            0, 
            $x,
            $this->marginTop + $textHeightHalf,
            $this->textColor, 
            $this->font, 
            '+1',
        );

        imagettftext(
            $this->img, 
            $this->titleSize, 
            0, 
            $x,
            $this->xAxis['y1'] + $textHeightHalf, 
            $this->textColor, 
            $this->font, 
            '0',
        );

        imagettftext(
            $this->img, 
            $this->titleSize, 
            0, 
            $x,
            $this->canvasHeight - $this->marginBottom + $textHeightHalf,
            $this->textColor, 
            $this->font, 
            '-1',
        );

    }
    
    private function drawAxes(): void
    {
        // Draw x axis.
        imageline(
            $this->img, 
            $this->xAxis['x1'], 
            $this->xAxis['y1'], 
            $this->xAxis['x2'], 
            $this->xAxis['y2'], 
            $this->textColor
        );
        
        // Draw y syb exes = days,
        $segment = $this->drawZone['width'] / 33; // 33 days is one cycle for longest biorhythm: Intelectuel.
        $startDate = $this->targetNoon - 86400 / 2 - 17 * 86400; // 17 is hallf or 34, middle of gtaph; 86400 is one days in seconcs.
        $counter = 1;

        $dashedStyle = [
            $this->textColor, 
            IMG_COLOR_TRANSPARENT, 
            IMG_COLOR_TRANSPARENT, 
            IMG_COLOR_TRANSPARENT
        ];

        imagesetstyle($this->img, $dashedStyle);
        
        // Segments through loop (33 + 1 day.)
        for($x = 0; $x <= $this->drawZone['width']+1; $x += $segment){
            $counter++;

            imageline(
                $this->img, 
                $x + $this->marginLeftRight, 
                $this->marginTop, //$this->yCentralAxis['y1'], 
                $x + $this->marginLeftRight, 
                $this->yCentralAxis['y2'], 
                IMG_COLOR_STYLED
            );
            
            // Calculate date (day) for every sub axe.
            $date = date("j M", $startDate + 86400 * ($counter - 1));
            
            // Draw date botom of y subaxes (days).
            $data = $this->textSize((float)$this->daysTextSize, $this->font, (string)$date);

            imagettftext(
                $this->img, 
                $this->daysTextSize, 
                0, 
                $x + $this->marginLeftRight - $data['width'] / 2, // Horizontal middle aligned.
                $this->yCentralAxis['y2'] + $data['height'] * 2, 
                $this->textColor, 
                $this->font, 
                $date
            );
        }
        
        // Draw y axis for target day. (middle of graph)
        imageline(
            $this->img, 
            $this->yCentralAxis['x1'], 
            $this->yCentralAxis['y1'], 
            $this->yCentralAxis['x2'], 
            $this->yCentralAxis['y2'], 
            $this->textColor
        );
    }
    
    private function markValuesOnYAxis(): void
    {
        // Draw zone of canwas.
        $halfY = $this->drawZone['height'] / 2;
        
        // All are on same central y axis, so calculate only positions on y axis (* -1 - correction of +/- values due to diffrence of Decart  coordinates and display).
        $intellectualY = $this->xAxis['y1'] + $this->intellectual * $halfY * -1;
        $emotionalY = $this->xAxis['y1'] + $this->emotional * $halfY * -1;
        $physicalY = $this->xAxis['y1'] + $this->physical * $halfY * -1;
        
        // Draw small circles on central y axe.
        imagearc($this->img, $this->yCentralAxis['x1'], $intellectualY, 7, 7, 0, 360, $this->intellectualColor);
        imagearc($this->img, $this->yCentralAxis['x1'], $emotionalY, 7, 7, 0, 360, $this->emotionalColor);
        imagearc($this->img, $this->yCentralAxis['x1'], $physicalY, 7, 7, 0, 360, $this->physicalColor);
        
        // Deaw of every biorhythm values near the circle (for targer day).
        $data = $this->textSize((float)$this->titleSize, $this->font, (string)$this->intellectual);
        
        // Take care value not write out of the area.
        $textY = $this->intellectual > 0 ? $intellectualY + $data['height'] : $intellectualY - $data['height'];
        imagettftext(
            $this->img, 
            (float)$this->titleSize, 
            0, 
            $this->yCentralAxis['x1'] + 20,
            $textY,
            $this->intellectualColor, 
            $this->font, 
            (string)$this->intellectual
        );
        
        $textY = $this->emotional > 0 ? $emotionalY + $data['height'] : $emotionalY - $data['height'];
        imagettftext(
            $this->img, 
            (float)$this->titleSize, 
            0, 
            $this->yCentralAxis['x1'] + 20,
            $textY,
            $this->emotionalColor, 
            $this->font, 
            (string)$this->emotional
        );
        
        $textY = $this->physical > 0 ? $physicalY + $data['height'] : $physicalY - $data['height'];
        imagettftext(
            $this->img, 
            (float)$this->titleSize, 
            0, 
            $this->yCentralAxis['x1'] + 20,
            $textY,
            $this->physicalColor, 
            $this->font, 
            (string)$this->physical
        );
    }
    
    private function drawBottomTexts(): void
    {
        // Date time of scriot execution.
        $dateTime = date('d. M. Y. H:i:s');
        $text = "Created at: " . $dateTime;

        $data = $this->textSize((float)$this->titleSize, $this->font, (string)$text);

        imagettftext(
            $this->img, 
            $this->titleSize, 
            0, 
            $this->marginLeftRight, // Left aligned.
            $this->canvasHeight - $data['height'] * 1.5, 
            $this->textColor, 
            $this->font, 
            $text
        );
    }
    
    private function defineColors()
    {
        // Define colors.        
        $this->backgroundColor = imagecolorallocate($this->img, $this->colorBackground[0], $this->colorBackground[1], $this->colorBackground[2]);
        $this->textColor = imagecolorallocate($this->img, $this->colorText[0],  $this->colorText[1], $this->colorText[2]);
        $this->physicalColor = imagecolorallocate($this->img, $this->colorPhysical[0], $this->colorPhysical[1], $this->colorPhysical[2]);
        $this->emotionalColor = imagecolorallocate($this->img, $this->colorEmotional[0], $this->colorEmotional[1], $this->colorEmotional[2]);
        $this->intellectualColor = imagecolorallocate($this->img, $this->colorIntellectual[0], $this->colorIntellectual[1],$this->colorIntellectual[2]);
        $this->fillColor = imagecolorallocate($this->img, $this->colorFill[0], $this->colorFill[1], $this->colorFill[2]);
        $this->criticalColor = imagecolorallocate($this->img, $this->colorCritical[0], $this->colorCritical[1], $this->colorCritical[2]);
    }
    
    /**
     * Calculate coordinates for central y axis (main - target day).
     *
     * @return array.
     */
    private function yCentralAxis(): array
    {
        $coordinates['x1'] = $this->marginLeftRight + ($this->canvasWidth - $this->marginLeftRight * 2) / 2;
        $coordinates['y1'] = $this->marginTop;
        $coordinates['x2'] = $coordinates['x1'];
        $coordinates['y2'] = $this->canvasHeight - $this->marginBottom;
        return $coordinates;
    }
    
    /**
     * Calculate coordinates for central x axis.
     *
     * @return array.
     */
    private function xAxis(): array
    {
        $coordinates['x1'] = $this->marginLeftRight;
        $coordinates['y1'] = $this->marginTop + ($this->canvasHeight - $this->marginTop - $this->marginBottom) / 2;
        $coordinates['x2'] = $this->canvasWidth - $this->marginLeftRight;
        $coordinates['y2'] = $coordinates['y1'];
        return $coordinates;
    }
    
    /**
     * Calculate coordinates for draw zone.
     *
     * @return array.
     */
    private function drawZone(): array
    {
        $coordinates['x1'] = $this->marginLeftRight;
        $coordinates['y1'] = $this->marginTop;
        $coordinates['x2'] = $this->canvasWidth - $this->marginLeftRight;
        $coordinates['y2'] = $this->canvasHeight - $this->marginBottom;
        $coordinates['height'] = $coordinates['y2'] - $coordinates['y1'];
        $coordinates['width'] = $coordinates['x2'] - $coordinates['x1'];
        return $coordinates;
    }
    
    /**
     * Calculate measures based on defined percents (margins etc.)
     *
     * @return void.
     */
    private function setMeasures(): void
    {
        $this->marginLeftRight = $this->marginLeftRight();
        $this->marginTop = $this->marginTop();
        $this->marginBottom = $this->marginBottom();
        
        // TITLE //
        $titleSixe = 5;
        
        //$this->titleLeft = $this->marginLeftRight;
        $this->yCentralAxis = $this->yCentralAxis();
        $this->xAxis = $this->xAxis();
        $this->drawZone = $this->drawZone();
        
        // Fonts sizes (proportional, base on image size) //
        $this->titleSize = $this->convertPercentToPixels(50.0, $this->marginTop);
        $this->textSize = 12; // General text size.
        $this->daysTextSize = 10; 
        
    }
    
    /**
     * Try to fin text size in peixels (since php8#^ use points for TTF)
     * 
     * @param float $size - pontsixe in points.
     * @param string  $font - TTF font name.
     * @paran string $text - Text (string).
     * @param float $angle - Text angle (default is 0.0)
     *
     * @return array.
     */
    private function textSize($size, $font, $text, $angle = 0.0): array | false
    {
        $coordinates =  imagettfbbox(
            (float)$size,
            (float)$angle,
            $font,
            $text
        );
        
        $data = [];
        
        // If angle is 0. (no text rotation)
        if ((float)$angle == 0.0) {
            $data['height'] = $coordinates[1] - $coordinates[7];
            $data['width'] = $coordinates[2] - $coordinates[0];
            $data['points'] = $size;
            $data['text'] = $text;
            return $data;
        } elseif ((float)$angle > 0.0 && (float)$angle < 180.0) {
            $data['height'] = $coordinates[1] - $coordinates[7];
            $data['width'] = $coordinates[4] - $coordinates[0];
            $data['points'] = $size;
            $data['text'] = $text;
            return $data;
        } elseif ((float)$angle > 180.0 && (float)$angle < 300.0) {
            // TODO: Cover rotated texts.
            return false;
        }
    }
        
    /**
     * Get margin in pixels.
     *
     * @return integer.
     */
    private function marginLeftRight(): int
    {
        return (int)$this->convertPercentToPixels((float)$this->marginLeftRightPercents, $this->canvasWidth);
    }
    
    /**
     * Get margin in pixels.
     *
     * @return integer.
     */
    private function marginTop(): int
    {
        return ceil($this->convertPercentToPixels((float)$this->marginTopPercents, $this->canvasHeight));
    }
    
    /**
     * Get margin in pixels.
     *
     * @return float.
     */
    private function marginBottom(): int
    {
        return (int)$this->convertPercentToPixels((float)$this->marginBottomPercents, $this->canvasHeight);
    }
    
    /**
     * Convertor: percent to pixels.
     * 
     * @param int $number.
     * @param float $percents.
     *
     * @return float.
     */
    private function convertPercentToPixels(float $percentsint, int $number): float
    {
        return round(($percentsint / 100) * $number, 3);
    }
}

