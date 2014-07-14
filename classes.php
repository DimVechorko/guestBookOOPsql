<?php


/*class connectMysqlDB {
    private $host = 'localhost';
    private $user = 'root';
    private $password = 'root';
    private $dbNname = 'guestbook';
    private static $dbc;

    private function connectDB($host, $login, $pass, $dbName){
        self::$dbc = mysqli_connect($host, $login, $pass, $dbName);
    }

    public static function getConnection($host, $login, $pass, $dbName){
        if(!self::$dbc){
            self::connectDB($host, $login, $pass, $dbName);
        }

        return self::$dbc;
    }

    public static function closeDB($dbc){
        if(self::$dbc)
            mysqli_close(self::$dbc);
    }
}
*/

class Pagination{

    /**
     * @return string
     */
    public function generate_page_links(){
        $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASS,'guestbook');

        $result_per_page=5;
        $query_for_links="SELECT * FROM guests";
        $result_for_links=mysqli_query($dbc,$query_for_links);
        $total=mysqli_num_rows($result_for_links);
        $num_pages=ceil($total/$result_per_page);
        $cur_page=isset($_GET['page'])?$_GET['page']:1;
        $page_links='';

        //Если это не первая стр. создание гиперссылки "предыдущая стр." (<<)
        if($cur_page>1){
            $page_links.='<a href="'. $_SERVER['PHP_SELF'] . '?page=' . ($cur_page-1) . '"><</a>';
        }
        else {
            $page_links.='<';
        }

        //Прохождение в цикле всех страниц и создание гиперссылок,
        //указывающих на конткретные страницы
        for ($i=1; $i<=$num_pages; $i++){
            if ($cur_page==$i){
                $page_links.=' ' . $i;
            }
            else {
                $page_links.='<a href="' . $_SERVER['PHP_SELF'] .
                    '?page=' . $i .'"> ' . $i .'</a>';
            }
        }

        //Если это не последняя страница - создание гиперссылки "следующая страница" (>>)
        if ($cur_page < $num_pages){
            $page_links.='<a href="' . $_SERVER['PHP_SELF'] .
                '?page=' . ($cur_page+1) .'">></a>';
        }
        else{
            $page_links.= ' >';
        }
        return $page_links;
    }
}

class Postlist{
    /**
     *
     */
    public function outputMessages(){
        $result_per_page=5;
        $cur_page=isset($_GET['page'])?$_GET['page']:1;
        $skip=(($cur_page-1)*$result_per_page);
        echo '<div class="postlist">
              <ol id="post" class="posts" start="1" >';
        $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASS,'guestbook');
        $query1="SELECT * FROM guests ORDER BY date_posted LIMIT $skip, $result_per_page"or die ('ERROR');
        $query=mysqli_query($dbc,$query1)or die ('ERROR1');
        if (mysqli_num_rows($query) >= 1){
            while($row=mysqli_fetch_array($query)){
                $id=$row['id'];
                $user_name=$row['username'];
                $email=$row['email'];
                $comment=$row['comment'];
                $date_posted=$row['date_posted'];


                echo'<li class="post_container">
                     <div class="post_head"><span>'.$date_posted.'</span></div>
                     <div class="post_details">
                          <div class="user_info">
                                '.$user_name.'</br>
                                </br>
                                </br>
                                 Email:
                                '.$email.'</br>
                                    Thanks
                                        0</br>
                                Thanked 0 Times in 0 Post
                        </div>
                        <div class="post_body">'.$comment.'
                            <div class="clear_div"></div>
                        </div>
                    </div>
                    </li>';
            }
        }
        echo '</ol>
              </div>';
    }
}


class Validation {

    public function validName($user_name){
        if (!empty($user_name)) {
           return preg_match('/^([a-zA-Z0-9]{3,}\s?).*/',$user_name);

        }else {
            $_SESSION['name_error'] = '|name error|';
            return false;
        }
    }

    public function validComment($comment){
        if (!empty($comment)){
            if (strlen($comment)<50){
                $_SESSION['comment_error']='|ERROR:entered text contains less than 50 characters|';
                return false;
            }else{
                return true;}
        }else{
            $_SESSION['comment_error']='|enter your comment|';
            return false;
        }
    }

    public  function validCaptcha($captcha){
        if ($_SESSION['pass_phrase']!==$captcha){
            $_SESSION['captcha_error']='|Enter the word verification|';
            return false;
        }else{
            return true;}
    }
}

class EmailAddressValidator {

    /**
     * Check email address validity
     * @param   strEmailAddress     Email address to be checked
     * @return  True if email is valid, false if not
     */
    public function check_email_address($strEmailAddress) {

        // If magic quotes is "on", email addresses with quote marks will
        // fail validation because of added escape characters. Uncommenting
        // the next three lines will allow for this issue.
        //if (get_magic_quotes_gpc()) {
        //    $strEmailAddress = stripslashes($strEmailAddress);
        //}

        // Control characters are not allowed
        if (preg_match('/[\x00-\x1F\x7F-\xFF]/', $strEmailAddress)) {
            return false;
        }

        // Split it into sections using last instance of "@"
        $intAtSymbol = strrpos($strEmailAddress, '@');
        if ($intAtSymbol === false) {
            // No "@" symbol in email.
            return false;
        }
        $arrEmailAddress[0] = substr($strEmailAddress, 0, $intAtSymbol);
        $arrEmailAddress[1] = substr($strEmailAddress, $intAtSymbol + 1);

        // Count the "@" symbols. Only one is allowed, except where
        // contained in quote marks in the local part. Quickest way to
        // check this is to remove anything in quotes.
        $arrTempAddress[0] = preg_replace('/"[^"]+"/'
            ,''
            ,$arrEmailAddress[0]);
        $arrTempAddress[1] = $arrEmailAddress[1];
        $strTempAddress = $arrTempAddress[0] . $arrTempAddress[1];
        // Then check - should be no "@" symbols.
        if (strrpos($strTempAddress, '@') !== false) {
            // "@" symbol found
            return false;
        }

        // Check local portion
        if (!$this->check_local_portion($arrEmailAddress[0])) {
            return false;
        }

        // Check domain portion
        if (!$this->check_domain_portion($arrEmailAddress[1])) {
            return false;
        }

        // If we're still here, all checks above passed. Email is valid.
        return true;

    }

    /**
     * Checks email section before "@" symbol for validity
     * @param   strLocalPortion     Text to be checked
     * @return  True if local portion is valid, false if not
     */
    protected function check_local_portion($strLocalPortion) {
        // Local portion can only be from 1 to 64 characters, inclusive.
        // Please note that servers are encouraged to accept longer local
        // parts than 64 characters.
        if (!$this->check_text_length($strLocalPortion, 1, 64)) {
            return false;
        }
        // Local portion must be:
        // 1) a dot-atom (strings separated by periods)
        // 2) a quoted string
        // 3) an obsolete format string (combination of the above)
        $arrLocalPortion = explode('.', $strLocalPortion);
        for ($i = 0, $max = sizeof($arrLocalPortion); $i < $max; $i++) {
            if (!preg_match('.^('
                .    '([A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]'
                .    '[A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]{0,63})'
                .'|'
                .    '("[^\\\"]{0,62}")'
                .')$.'
                ,$arrLocalPortion[$i])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks email section after "@" symbol for validity
     * @param   strDomainPortion     Text to be checked
     * @return  True if domain portion is valid, false if not
     */
    protected function check_domain_portion($strDomainPortion) {
        // Total domain can only be from 1 to 255 characters, inclusive
        if (!$this->check_text_length($strDomainPortion, 1, 255)) {
            return false;
        }
        // Check if domain is IP, possibly enclosed in square brackets.
        if (preg_match('/^(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'
                .'(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}$/'
                ,$strDomainPortion) ||
            preg_match('/^\[(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'
                .'(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}\]$/'
                ,$strDomainPortion)) {
            return true;
        } else {
            $arrDomainPortion = explode('.', $strDomainPortion);
            if (sizeof($arrDomainPortion) < 2) {
                return false; // Not enough parts to domain
            }
            for ($i = 0, $max = sizeof($arrDomainPortion); $i < $max; $i++) {
                // Each portion must be between 1 and 63 characters, inclusive
                if (!$this->check_text_length($arrDomainPortion[$i], 1, 63)) {
                    return false;
                }
                if (!preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|'
                    .'([A-Za-z0-9]+))$/', $arrDomainPortion[$i])) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Check given text length is between defined bounds
     * @param   strText     Text to be checked
     * @param   intMinimum  Minimum acceptable length
     * @param   intMaximum  Maximum acceptable length
     * @return  True if string is within bounds (inclusive), false if not
     */
    protected function check_text_length($strText, $intMinimum, $intMaximum) {
        // Minimum and maximum are both inclusive
        $intTextLength = strlen($strText);
        if (($intTextLength < $intMinimum) || ($intTextLength > $intMaximum)) {
            return false;
        } else {
            return true;
        }
    }


}
