<?php
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_HOST', 'localhost');

session_start();
require_once ('form.html');
require_once ('classes.php');

$dbc = new ConnectMysqlDB();

$validation = new Validation();
$validation_email = new EmailAddressValidator();






$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASS,'guestbook');

if (isset($_POST['submit'])) {
    $user_name = strip_tags(mysqli_real_escape_string($dbc,trim($_POST['name'])));
    $strEmailAddress = strip_tags(mysqli_real_escape_string($dbc,trim($_POST['email'])));
    $comment = strip_tags(mysqli_real_escape_string($dbc,trim($_POST['comment'])));
    $captcha=trim(sha1($_POST['captcha']));
    $_SESSION['captcha']=$captcha;


    $valid_name = $validation->validName($user_name);
    //var_dump($valid_name);
    $valid_comment = $validation->validComment($comment);
    //var_dump($valid_comment);
    $valid_captcha = $validation->validCaptcha($captcha);
    //var_dump($valid_captcha);
    $valid_email = $validation_email ->check_email_address($strEmailAddress);
    //var_dump($valid_email);
    if ($valid_email == true) {
        // Оставленный здесь return, т.к. он вне фукнции останавливает выполнение скрипта
        // И до сохранения сообщения не доходит
        //return true;
    }else{
        $_SESSION['email_error']="|email error|";
    }

    // Эта проверка нужна только при сабмите формы, так что заносим ее в ветку обработки поста
    if ($valid_name==1 && $valid_comment==true && $valid_captcha==true && $valid_email==true) {
        //echo $user_name; echo $strEmailAddress; echo $comment;
        $query="INSERT INTO guests (username, email, comment, date_posted)VALUE ('$user_name','$strEmailAddress','$comment',NOW())";
        $result=mysqli_query($dbc,$query)or die("error query");
        // Отключаем echo, для нормальной работы редиректа
        //echo '<p>' . $user_name .  ', Сообщение отправлено!</p>';
        header( 'Location: index.php');
        exit();
    }
}

// Этот блок не нужно обрабатывать при корректном сабмите формы, так что его лучше поместить здесь
// чтобы зря не выполнять
// кроме того он будет мешать корректной работе header ghb сабмите формы, если будет находиться выше
$pagination = new Pagination();
$postlist= new Postlist();
$pagination=$pagination->generate_page_links($num_pages);
echo $pagination;
$postlist=$postlist->outputMessages();

// Какое назначение этой строки?
// session_destroy();

