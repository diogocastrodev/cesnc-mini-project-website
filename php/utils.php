<?php

/**
 * Transform a string YYYY-MM-DD HH:MM:SS into a human-readable format.
 * If the date is today, it returns "Today HH:MM".
 * If the date is yesterday, it returns "Yesterday HH:MM".
 * Otherwise, it returns the date in the format "YYYY-MM-DD HH:MM".
 * @param string $datetimeStr The date and time string in the format YYYY-MM-DD HH:MM:SS.
 * @return string The human-readable date string.
 */
function humanDate($datetimeStr)
{
    $dt = new DateTime($datetimeStr);
    $now = new DateTime();
    $now->setTimezone(new DateTimeZone("Europe/Lisbon"));
    $today = $now->format('Y-m-d');
    $yesterday = $now->modify('-1 day')->format('Y-m-d');
    $datePart = $dt->format('Y-m-d');
    $timePart = $dt->format('H:i');

    if ($datePart === $today) {
        return "Today $timePart";
    } elseif ($datePart === $yesterday) {
        return "Yesterday $timePart";
    } else {
        return $dt->format('Y-m-d H:i');
    }
}

function capitalize($string)
{
    return ucwords(strtolower($string));
}
