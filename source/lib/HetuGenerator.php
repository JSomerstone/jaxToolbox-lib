<?php
namespace jaxToolbox\lib;

abstract class HetuGenerator
{
    static $currentDate = '1930-01-01';
    static $counter = 1;

    public static function init($startDate = '1930-01-01', $counter = 1)
    {
        self::$currentDate = $startDate;
        self::$counter = $counter;
    }

    public static function giveNext()
    {
        if (self::$counter > 899)
        {
            self::$currentDate = date('Y-m-d', strtotime(self::$currentDate. ' + 1 days'));
            self::$counter = 1;
        }

	$date = date('dmy', strtotime(self::$currentDate));
        $indentifier = str_pad(self::$counter, 3, '0', STR_PAD_LEFT);

        //Check checksum
        $checksumTable = array(
            0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4,
            5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9,
            10 => 'A', 11 => 'B', 12 => 'C',
            13 => 'D', 14 => 'E', 15 => 'F',
            16 => 'H', 17 => 'J', 18 => 'K',
            19 => 'L', 20 => 'M', 21 => 'N',
            22 => 'P', 23 => 'R', 24 => 'S',
            25 => 'T', 26 => 'U', 27 => 'V',
            28 => 'W', 29 => 'X', 30 => 'Y',
        );

        $checksumNums = $date.$indentifier;
        $checksumIndex = ($checksumNums % 31);

        self::$counter += 1;

	return $date.'-'.$indentifier. $checksumTable[$checksumIndex];
    }
}