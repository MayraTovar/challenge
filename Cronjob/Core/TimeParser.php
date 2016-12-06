<?php

final class Classes_CronJob_Adapter_TimeParser
{

    public function  __construct()
    {
    }
    public function  __destruct()
    {
    }

    public static function isTimeToRun($frequency)
    {
        $items = explode(' ',trim($frequency));

        if( count($items) != 5)
        {
            Classes_Core_Log_Handler::info("Unexpected frequency value: {$frequency} ");
            return false;
        }
        
        //Validate weekday, 1 for monday, 7 for sunday WITHOUT leading zeros
        if( !Classes_CronJob_Adapter_TimeParser::validateItem($items[4], date('N')) )
        {
            return false;
        }
        //Validate month, 1 for january, 12 for december WITHOUT leading zeros
        if( !Classes_CronJob_Adapter_TimeParser::validateItem($items[3], date('m')) )
        {
            return false;
        }
        //Validate day, WITHOUT leading zeros
        if( !Classes_CronJob_Adapter_TimeParser::validateItem($items[2], date('d')) )
        {
            return false;
        }
        //Validate hour, WITHOUT leading zeros
        if( !Classes_CronJob_Adapter_TimeParser::validateItem($items[1], date('G')) )
        {
            return false;
        }
        //Validate minute, WITH leading zeros
        if( !Classes_CronJob_Adapter_TimeParser::validateItem($items[0], date('i')) )
        {
            return false;
        }

        return true;
    }

    public static function validateItem($value, $compare)
    {
        if( $value == '*' )
        {
            return true;
        }
        if( strpos($value, '*/' ) === 0 )
        {
            $number = (int)substr($value, 2 );
            if( $number > 0 )
            {
                $compare = (int)$compare;
                $result = $compare % $number;
                $result = (int)$result;
                if($result === 0)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
        if( in_array($compare, explode(',',$value) ) )
        {
            return true;
        }
        return false;
    }

}

