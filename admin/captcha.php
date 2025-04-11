<?php
session_start();

// Генерация случайного кода капчи
$possibleCharacters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$captchaLength = 6;
$captchaText = '';

for ($i = 0; $i < $captchaLength; $i++) {
    $captchaText .= substr($possibleCharacters, rand(0, strlen($possibleCharacters) - 1), 1);
}

// Сохраняем сгенерированный код в сессию для дальнейшей проверки
$_SESSION['captcha_code'] = $captchaText;

// Параметры изображения
$imageWidth = 150;
$imageHeight = 40;

// Создаём изображение
$image = imagecreatetruecolor($imageWidth, $imageHeight);

// Определяем цвета
$bgColor    = imagecolorallocate($image, 255, 255, 255);    // белый фон
$textColor  = imagecolorallocate($image, 0, 0, 0);           // чёрный текст
$noiseColor = imagecolorallocate($image, 100, 120, 180);       // цвет шума

// Заполняем фон изображения
imagefilledrectangle($image, 0, 0, $imageWidth, $imageHeight, $bgColor);

// Добавляем шум: случайные линии
for ($i = 0; $i < 10; $i++) {
    imageline(
        $image,
        rand(0, $imageWidth), rand(0, $imageHeight),
        rand(0, $imageWidth), rand(0, $imageHeight),
        $noiseColor
    );
}

// Добавляем шум: случайные точки
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, $imageWidth), rand(0, $imageHeight), $noiseColor);
}

// Параметры текста
$fontSize = 20; // размер шрифта (в пунктах)
$fontFile = 'assets/fonts/captcha.ttf'; // укажите корректный путь к TTF-шрифту

// Вычисляем размеры текста для центрирования
$textBox   = imagettfbbox($fontSize, 0, $fontFile, $captchaText);
$textWidth = $textBox[2] - $textBox[0];
$textHeight = $textBox[1] - $textBox[7];

// Вычисляем координаты для центрирования текста
$x = ($imageWidth - $textWidth) / 2;
$y = ($imageHeight + $textHeight) / 2;

// Выводим текст капчи
imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontFile, $captchaText);

// Отдаем изображение в браузер
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
exit;
?>
