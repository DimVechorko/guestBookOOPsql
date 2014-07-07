<?php
session_start();
require_once ('form.html');
require_once ('classes.php');

$dbc = new ConnectMysqlDB();
$pagination = new Pagination();
$postlist= new Postlist();
$validation = new Validation();
$validation_email = new EmailAddressValidator();





$pagination=$pagination->generate_page_links($num_pages);
echo $pagination;

$postlist=$postlist->outputMessages();

$dbc = mysqli_connect('localhost','root','root','guestbook');

if (isset($_POST['submit'])) {
    $user_name = strip_tags(mysqli_real_escape_string($dbc,trim($_POST['name'])));
    $strEmailAddress = strip_tags(mysqli_real_escape_string($dbc,trim($_POST['email'])));
    $comment = strip_tags(mysqli_real_escape_string($dbc,trim($_POST['comment'])));
    $captcha=trim(sha1($_POST['captcha']));
    $_SESSION['captcha']=$captcha;


    $valid_name = $validation->validName($user_name);
    var_dump($valid_name);
    $valid_comment = $validation->validComment($comment);
    var_dump($valid_comment);
    $valid_captcha = $validation->validCaptcha($captcha);
    var_dump($valid_captcha);
    $valid_email = $validation_email ->check_email_address($strEmailAddress);
    var_dump($valid_email);
    if ($valid_email == true) {
        return true;
    }else{
        $_SESSION['email_error']="|email error|";
    }
}

    if ($valid_name==1 && $valid_comment==true && $valid_captcha==true && $valid_email==true) {
        //echo $user_name; echo $strEmailAddress; echo $comment;
        $query="INSERT INTO guests (username, email, comment, date_posted)VALUE ('$user_name','$strEmailAddress','$comment',NOW())";
        $result=mysqli_query($dbc,$query)or die("error query");
        echo '<p>' . $user_name .  ', Сообщение отправлено!</p>';
        header( 'Location: index.php');
    }


session_destroy();
