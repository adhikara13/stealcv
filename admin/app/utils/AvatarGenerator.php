<?php
class AvatarGenerator {
    private $width;
    private $height;
    private $gridWidth;
    private $gridHeight;
    private $independentColumns;
    private $cellWidth;
    private $cellHeight;
    private $seed;

    /**
     * Конструктор для настройки параметров аватара.
     *
     * @param string $seed      Seed для генерации идентикона.
     * @param int    $width     Ширина аватара.
     * @param int    $height    Высота аватара.
     * @param int    $gridWidth Количество столбцов в сетке.
     * @param int    $gridHeight Количество строк в сетке.
     */
    public function __construct($seed = 'default_seed', $width = 200, $height = 200, $gridWidth = 5, $gridHeight = 5) {
        $this->seed = $seed;
        $this->width = $width;
        $this->height = $height;
        $this->gridWidth = $gridWidth;
        $this->gridHeight = $gridHeight;
        $this->independentColumns = ceil($gridWidth / 2);
        $this->cellWidth  = $width / $gridWidth;
        $this->cellHeight = $height / $gridHeight;
    }

    /**
     * Генерирует изображение аватара в виде симметричной мозаики.
     *
     * @return resource Изображение в формате GD.
     */
    public function generate() {
        // Создаем изображение с поддержкой прозрачности
        $image = imagecreatetruecolor($this->width, $this->height);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);

        // Получаем MD5-хеш из seed
        $hash = md5($this->seed);

        // Определяем цвет заливки на основе первых 6 символов хеша
        $red   = hexdec(substr($hash, 0, 2));
        $green = hexdec(substr($hash, 2, 2));
        $blue  = hexdec(substr($hash, 4, 2));
        $fillColor = imagecolorallocate($image, $red, $green, $blue);

        // Генерируем шаблон для независимой части мозаики (gridHeight * independentColumns)
        $pattern = [];
        $patternSize = $this->gridHeight * $this->independentColumns;
        for ($i = 0; $i < $patternSize; $i++) {
            // Используем символы хеша начиная с позиции 6
            $pattern[$i] = (hexdec($hash[$i + 6]) % 2 === 0);
        }

        // Рисуем мозаичную сетку с симметрией по вертикали
        for ($y = 0; $y < $this->gridHeight; $y++) {
            for ($x = 0; $x < $this->gridWidth; $x++) {
                if ($x < $this->independentColumns) {
                    $index = $y * $this->independentColumns + $x;
                } else {
                    $index = $y * $this->independentColumns + ($this->gridWidth - 1 - $x);
                }
                if (isset($pattern[$index]) && $pattern[$index]) {
                    $x1 = $x * $this->cellWidth;
                    $y1 = $y * $this->cellHeight;
                    imagefilledrectangle($image, $x1, $y1, $x1 + $this->cellWidth, $y1 + $this->cellHeight, $fillColor);
                }
            }
        }

        return $image;
    }

    /**
     * Выводит сгенерированный аватар в формате PNG.
     */
    public function output() {
        header("Content-Type: image/png");
        $img = $this->generate();
        imagepng($img);
        imagedestroy($img);
    }
}

?>
