<?php

namespace hcf\cooldown;

class Cooldown
{
    /**
     * Converts a time string into seconds.
     * 
     * @param string $time The time string to convert.
     * @return int The time in seconds.
     */
    public static function time(string $time): int
    {
        $time = strtolower($time);
        if (strpos($time, 'm') !== false) {
            $time = intval($time) * 60; 
        } elseif (strpos($time, 'h') !== false) {
            $time = intval($time) * 3600; 
        } else {
            $time = intval($time); 
        }

        return $time;
    }

    /**
     * Converts remaining time into MM:SS or SS format.
     *
     * @param int $time The time in seconds.
     * @return string The time in MM:SS or SS format.
     */
    public static function format(int $time): string
    {
        $minutes = floor($time / 60);
        $seconds = $time % 60;
        
        if ($minutes === 0) {
            return sprintf('%02d', $seconds) . '';  
        }

        return sprintf('%02d:%02d', $minutes, $seconds) . '';  
    }

    /**
     * Converts remaining time into HH:MM:SS format.
     *
     * @param int $time The time in seconds.
     * @return string The time in HH:MM:SS format.
     */
    public static function getTimeToFullString(int $time): string
    {
        $hours = floor($time / 3600);
        $minutes = floor(($time % 3600) / 60);
        $seconds = $time % 60;

        if ($hours === 0 && $minutes === 0) {
            return sprintf('%02d', $seconds) . ''; 
        }

        if ($hours === 0) {
            return sprintf('%02d:%02d', $minutes, $seconds);  
        }

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);  
    }

    /**
     * Converts remaining time to a string format with days, hours, minutes, seconds, and milliseconds.
     *
     * @param int  $time The time in seconds.
     * @param bool $true Whether to subtract the current time (if true).
     * @return string The time in "Days HH:MM:SS.MS" format.
     */
    public static function getIntToString(int $time, bool $true = true): string
    {
        $remaining = $true ? $time - time() : $time;

        $days = floor($remaining / 86400);
        $hours = floor(($remaining % 86400) / 3600);
        $minutes = floor(($remaining % 3600) / 60);
        $seconds = $remaining % 60;
        $milliseconds = sprintf('%03d', ($remaining * 1000) % 1000);  

        $timeString = ($days > 0 ? "$days days " : "") .
                      ($hours > 0 ? "$hours hours " : "") .
                      ($minutes > 0 ? "$minutes minutes " : "") .
                      "$seconds seconds " .
                      $milliseconds . ' ms';

        return $timeString;
    }
}
