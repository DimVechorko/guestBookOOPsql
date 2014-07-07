<?php
session_start();
define('CAPTCHA_WIDTH',100);
define('CAPTCHA_HEIGHT',25);
define('CAPTCHA_NUMCHARS',2);

//Создание идентификационной фразы случайным образом
$pass_phrase="";
for ($i=0; $i<CAPTCHA_NUMCHARS; $i++){
    $pass_phrase.=chr(rand(97,122));
}

//Сохранение идентификационной фразы в переменной сессии в зашифрованном виде
$_SESSION['pass_phrase']=sha1($pass_phrase);

//Создание изображения
$img=imagecreatetruecolor(CAPTCHA_WIDTH,CAPTCHA_HEIGHT);

//Установка цветов: белого для фона ,черного для текста и серого для графических элементов
$bg_color=imagecolorallocate($img,220,220,220);// white color
$text_color=imagecolorallocate($img,0,0,0);//black color
$graphic_color=imagecolorallocate($img,64,64,64); //taupe color

//Заполнеение фона
imagefilledrectangle($img,0,0,CAPTCHA_WIDTH,CAPTCHA_HEIGHT,$bg_color);

//Рисование линий расположенных случайным образом
for ($i=0; $i<5; $i++) {
    imageline($img,0,rand()%CAPTCHA_HEIGHT,CAPTCHA_WIDTH,rand()%CAPTCHA_HEIGHT,$graphic_color);
}

//Рисование точек, расположенных случайным образом
for ($i=0; $i<50; $i++){
    imagesetpixel($img,rand()%CAPTCHA_WIDTH,rand()%CAPTCHA_HEIGHT,$graphic_color);
}

//Написание строки содержащей идентификационную фразу
imagettftext($img,18,0,5,CAPTCHA_HEIGHT -5,$text_color,"fonts/cour.ttf", $pass_phrase);

//Вывод изображения в формате PNG с помощью HTTP-заголовка
header("Content-type:image/png");
imagepng($img);

//Удаление изображения
imagedestroy($img);
?>