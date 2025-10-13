<?php

declare(strict_types=1);

namespace hcf\utils\time;

use InvalidArgumentException;

/**
 * Class Timer
 * @package hcf\utils
 */
final class Timer
{
    
    /**
     * @param string $duration
     * @throws InvalidArgumentException
     * @return int
     */
    public static function time(string $duration): int
    {
        if (preg_match('/^(\d+)(h|m|s)$/', $duration, $matches)) {
            $valor = (int)$matches[1];
            $unidad = strtolower($matches[2]);
            switch ($unidad) {
                case 'h':
                    return $valor * 3600; // 1 hora equivale a 3600 segundos
                case 'm':
                    return $valor * 60;   // 1 minuto equivale a 60 segundos
                case 's':
                    return $valor;        // Los segundos ya están en segundos
                default:
                    return 0;             // Unidad no válida, devolvemos 0
            }
        } else {
            // Si el formato del string no es válido, devolvemos un valor predeterminado (0)
            return 0;
        }
    }
    
    /**
     * @param int $time
     * @return string
     */
    public static function date(int $time): string
    {
        $weeks = $time / 604800 % 52;
        $hours = $time / 3600 % 24;
        $minutes = $time / 60 % 60;
        $seconds = $time % 60;
        
        return $weeks . ' week(s), ' . $hours . ' hour(s), ' . $minutes . ' minute(s) and ' . $seconds . ' second(s)';
    }
    
    /**
     * @param int $time
     * @return string
     */
    public static function getTimeToString(int $time): string
    {
        if ($time >= 3600)
            return gmdate('H:i:s', $time);
        elseif ($time < 60)
            return $time . 's';
        return gmdate('i:s', $time);
    }

    public static function format1(int $time): string
    {
        // Tiempo personalizado
        $customTime = $time * 10;

        // Redondear a 1 decimal
        $formatted = round($customTime, 1);

        if($formatted >= 3600) {
            return gmdate('H:i:s', $formatted);
        } else if($formatted < 60) {
            return $formatted . 's';
        } else {
            return gmdate('i:s', $formatted);
        }
    }
    
    public static function convert(int $time): string
    {
        if ($time < 60)
            return $time . 's';
        elseif ($time < 3600) {
            $minutes = intval($time / 60) % 60;
            return $minutes . 'm';
        } elseif ($time < 86400) {
            $hours = (int)($time / 3600) % 24;
            return (int)$hours . 'h';
        } else {
            $days = floor($time / 86400);
            return $days . 'd';
        }
    }

    public static function getTimeToFullString(int $time) : string {
		$s = $time % 60;	
		$m = null;		
		$h = null;		
		$d = null;
		
		if($time >= 60){			
			$m = floor(($time % 3600) / 60);		
			if($time >= 3600){				
				$h = floor(($time % 86400) / 3600);				
				if($time >= 3600 * 24){					
					$d = floor($time / 86400);					
				}			
			}		
		}		
		return ($m !== null ? ($h !== null ? ($d !== null ? "$d days " : "")."$h hours " : "")."$m minutes " : "")."$s seconds";
	}
}