<?php
/**
 * Функция для парсинга строки User-Agent.
 *
 * @param string $userAgent Строка User-Agent.
 * @return array Массив с ключами 'browser' и 'version'.
 */
function parseUserAgent($userAgent) 
{
    // Значения по умолчанию в случае ошибки определения
    $result = [
        'browser' => 'Неизвестный браузер',
        'version' => 'неизвестна'
    ];

    // Массив шаблонов для определения браузера и версии
    $patterns = [
        // Сначала проверяем Edge, так как он может содержать Chrome и Safari
        'Microsoft Edge'      => '/Edge\/([0-9\.]+)/i',
        // Новый Opera может быть обозначен как OPR
        'Opera'     => '/OPR\/([0-9\.]+)/i',
        // Затем Chrome
        'Google Chrome'    => '/Chrome\/([0-9\.]+)/i',
        // Safari: нужно использовать метку Version, чтобы не путать с Chrome
        'Safari'    => '/Version\/([0-9\.]+)/i',
        // Firefox
        'Mozilla Firefox'   => '/Firefox\/([0-9\.]+)/i',
        // Internet Explorer до версии 11
        'Internet Explorer' => '/MSIE\s([0-9\.]+)/i',
        // Internet Explorer 11 (UA содержит "Trident" и "rv:")
        'Internet Explorer' => '/Trident\/.*rv:([0-9\.]+)/i'
    ];

    // Перебор шаблонов для определения браузера
    foreach ($patterns as $browser => $pattern) {
        if (preg_match($pattern, $userAgent, $matches)) {
            // Для Safari проверяем, что это не Chrome (так как Chrome содержит "Safari" в User-Agent)
            if ($browser === 'Safari' && stripos($userAgent, 'Chrome') !== false) {
                continue;
            }
            $result['browser'] = $browser;
            $result['version'] = $matches[1];
            return $result;
        }
    }

    return $result;
}

?>
