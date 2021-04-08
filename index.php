<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
    
</body>
</html>
<?php



  $db = mysqli_connect('localhost', 'libgir1_ali', '^*C5ErPI*tH_', 'libgir1_gir');
    if($db->connect_errno > 0){
        die('Unable to connect to database [' . $db->connect_error . ']');
    }
    
    $db->set_charset("utf8");

 if(isset($_GET['From'])){
          getsms($_GET['From'],$_GET['Text'],$db);
      }

    function getsms($from,$text,$db){
   

    if(checkfounduser($from)){
    //   $userid = checkfounduser($from) ;
    $phon = "0".$from;
    $query_getuserid="SELECT id FROM users WHERE phonenumber='{$phon}'";
        if( ! $result_getuserid = $db->query( $query_getuserid ) ){
            die(__LINE__.'error while running Query (' . $db->error . ')' );
            $_SESSION["alert_titr"]= "خطا در درخواست!";
            $_SESSION["alert_type"]= false;
            $_SESSION["alert"]= "مشکلی در انتخاب کتاب وجود دارد";
           
        }
        else{
            $row_getuserid= $result_getuserid->fetch_assoc();
            $userid = $row_getuserid["id"];
        }
        //$db->close(); 
    
    if(checkmaxbook($userid)){
        $text = trim($text," ");
        $arr=sepratecode($text);
       
     
        
        $query_book="SELECT * FROM books WHERE book_id={$arr[2]} AND mozoo={$arr[1]}";
        
        // var_dump($db);
        if( ! $result_book = $db->query( $query_book ) ){
            die(__LINE__.'error while running Query (' . $db->error . ')' );
            $_SESSION["alert_titr"]= "خطا در درخواست!";
            $_SESSION["alert_type"]= false;
            $_SESSION["alert"]= "مشکلی در انتخاب کتاب وجود دارد";
          
        }
        else{
             while($row_book= $result_book->fetch_assoc()) {
                 
                ///////////////
                
                
            $id= $row_book["id"];
            settype($userid,"integer");

                      



                      $query_select_situation_for_notif = "SELECT books.title AS title,secondaries.situation AS situation ,secondaries.loc AS loc FROM secondaries JOIN books  ON books.id = '{$id}' WHERE secondaries.book_id ='{$id}'  AND secondaries.secondary_id ='{$arr[0]}'  ";
                      if( ! $result_select_situation_for_notif = $db->query( $query_select_situation_for_notif ) ){
                          die(__LINE__.'error while running Query (' . $db->error . ')' );
                      }
                      else{
                          $row = $result_select_situation_for_notif->fetch_assoc();
                          
                          
                          if($row["situation"] == "user" && $row["loc"] != $userid){
                             
                              notif($row["loc"],"transferfromuser",$id,$arr[0]);
                          }
                          else if($row["situation"] == "shelf"){
                              $query_select_shelfadmin_data = "SELECT admin_id FROM shelf_admins  WHERE shelf_admins.shelf_id ='{$row["loc"]}'";
                      if( ! $result_select_shelfadmin_data = $db->query( $query_select_shelfadmin_data ) ){
                          die(__LINE__.'error while running Query (' . $db->error . ')' );
                      }
                      else{
                          while($row_select_shelfadmin_data = $result_select_shelfadmin_data->fetch_assoc()){
                             
                              notif($row_select_shelfadmin_data["admin_id"],"transferfromshelf",$id,$arr[0]);
                          }
                   
                           
                          }
                          }
                          else if($row["situation"] == "library"){
                              settype($row["loc"],"integer");
                              $query_select_cityadmin_id = "SELECT id FROM  users  WHERE  users.type = 'cityadmin' AND  users.cityid = {$row["loc"]} ";
                              if( ! $result_select_cityadmin_id = $db->query( $query_select_cityadmin_id ) ){
                                  die(__LINE__.'error while running Query (' . $db->error . ')' );
                              }
                              else{
                                 while ( $row_select_cityadmin_id = $result_select_cityadmin_id->fetch_assoc()){
                                  
                                  notif($row_select_cityadmin_id["id"],"transferfromlibrary",$id,$arr[0]);
                                 }
                              }
                          }  
                           
                  }
                   



                     
           
            $query_getbook="UPDATE secondaries SET situation='user' , loc={$userid} WHERE book_id={$id} AND secondary_id={$arr[0]} ";
            if( ! $result_getbook = $db->query( $query_getbook ) ){
               
             die(__LINE__.'error while running Query (' . $db->error . ')' );
              
            }
            if(mysqli_affected_rows($db)){
                
               



                $time = date('Y-m-d H:i:s');
                $queryy ="INSERT INTO `book_history` (`book_id`, `secondary_id`, `user_id`, `created_at`, `func`, `loc`) VALUES ({$id}, {$arr[0]}, {$userid}, '{$time}', 'getbook', {$userid});";
                if( ! $resultt = $db->query( $queryy ) ){
                    
                die(__LINE__.'error while running Query (' . $db->error . ')' );
            
                    }
                    else{
                            
                            
                            $type = "getbook";

                   
                           
                              $bool = notif($userid,$type,$id,$arr[0]);
                              
                                    if($bool == true){
                                      
                                     
                                      
                                    }else{
                                        die(__LINE__.'error while running Query (' . $db->error . ')' );
                                        
                                    }

                                  
                                   
                            
                                
                        
                       
                }
               
            }else{
                $query_title = "SELECT title FROM book WHERE  id = '{$id}' ";
    


                  $query_select_sit_book = "SELECT situation,loc FROM secondaries  WHERE  book_id = '{$id}' AND  secondary_id = '{$arr[0]}' ";
                              if( ! $result_select_sit_book = $db->query( $query_select_sit_book) ){
              die(__LINE__.'error while running Query (' . $db->error . ')' );
                              }
                              else{
                                $row_select_sit_book = $result_select_sit_book->fetch_assoc();
                                if(count($row_select_sit_book) > 0){
                                $type = "extbook";
                               
                             
                                
                               
                                $bool = notif($userid,$type,$id,$arr[0]);
                                    if($bool == true){
                                        $time = date('Y-m-d H:i:s');
                                        $query_history_ext ="INSERT INTO `book_history` (`book_id`, `secondary_id`, `user_id`, `created_at`, `func`,`loc`) VALUES ({$id}, {$arr[0]}, {$userid}, '{$time}','extbook',{$userid});";
                                        if( ! $result_history_ext = $db->query( $query_history_ext ) ){
                                           die(__LINE__.'error while running Query (' . $db->error . ')' );
                                            }
                                            else{
                                                $_SESSION["alert_titr"]= "عملیات با موفقیت انجام شد";
                                                $_SESSION["alert_type"]= true;
                                                $_SESSION["alert"]= "زمان تحویل این کتاب برای شما تمدید شد";
                                               
                                        }

                                    }else{
                               die(__LINE__.'error while running Query (' . $db->error . ')' );
                                        
                                    }

                                }
                                else{
                                    
                                   echo "کتابی با این مشخصات یافت نشد";
                                   
                                }
                                
                              }
            }
                
                
                
                ///////////////////////
             }
            
        }
      
      
    
       
    }else{
       echo "حداکثر فقط 5 کتاب";
    }

    }
    else{ 
        $password = generateRandomString(8) ;
           $pass = password_encrypt($password);
            $phone = "0".$from;
      
      
try{

// دامنه سایت را در خط زیر وارد نمائید
            $client = new SoapClient('https://www.payam-resan.com/ws/v2/ws.asmx?WSDL');
            
            $parameters['Username'] = "09105095100";
            $parameters['PassWord'] = "81448634";
            $parameters['SenderNumber'] = "5000203000333";
            $parameters['RecipientNumbers'] = array($phone);
            $parameters['MessageBodie'] ="به سامانه وقف در گردش خوش آمديد!"."\r\n"."براي استفاده از خدمات بيشتر به حساب خود در سايت مراجعه كنيد."."\r\n"."نام كاربري : ".$phone."\r\n"."رمز عبور : ".$password."\r\n"."http://libg.ir/login";
            $parameters['Type'] = 1;
            $parameters['AllowedDelay'] = 0;
            
            $res = $client->GetCredit($parameters);
            echo $res->GetCreditResult;
            $res = $client->SendMessage($parameters);
            foreach ($res->SendMessageResult as $r)
            echo $r;
            } 
            catch (SoapFault $ex) 
            {
            echo $ex->faultstring;
            }
            
      
      
        ///ارسال پیامک
       
            $query = "INSERT INTO users (phonenumber,name,family,password,cityid,stateid,birthyear,adress,remember_token,created_at,updated_at,sms) VALUES ('{$phone}','لطفا اطلاعات خود را کامل کنید',' ','{$pass}',null,null,null,null,null,null,null,1)";
    
    
    		if( ! $result = $db->query( $query ) ){
            }
            else{
                getsms($from,$text,$db);
               
            }


         
        
    }
    
      }
      
      
      function checkfounduser($phone){
    $phone = "0".$phone;
    global $db;
    $query_found_user="SELECT id FROM users WHERE phonenumber='{$phone}'";
    if( ! $result_found_user = $db->query( $query_found_user ) ){
        echo "مشکلی در ارتباط با دیتابیس رخ داده است";
    }
    else{
        $row_found_user= $result_found_user->fetch_assoc();
      if(!empty($row_found_user["id"])){
      return true;
      }
      else{
        return false ;
      }
    }
}


function checkmaxbook($id){
   global $db;
    $query_count="SELECT COUNT(id) FROM secondaries WHERE loc='{$id}' AND situation='user' ";
    if( ! $result_count = $db->query( $query_count ) ){
        die(__LINE__.'error while running Query (' . $db->error . ')' );
    }
    $row_count= $result_count->fetch_assoc();
    //var_dump($row_count);
    
    $result_count->free();
   
    if($row_count["COUNT(id)"] > 4){
        return false;
    }else{
        return true;
    }

}

function sepratecode($code){
    $gid = "";
    $bookid = "" ;
    $seconid = "";
    $code = str_split($code);
    foreach (  $code as $key => $value) {
        global $gid,$bookid, $seconid;
        if($key < 2){
            $gid.=$value;
        }
        else if($key > 1 && $key < 8 ){
            $bookid.=$value;
        }
        else{
            $seconid.=$value;
        }
    }




  return [$seconid,$gid,$bookid];
}
    
    
    function notif($user_id,$type,$book_id,$secondary_id){
        
$db = mysqli_connect('localhost', 'libgir1_ali', '^*C5ErPI*tH_', 'libgir1_gir');
    if($db->connect_errno > 0){
        die('Unable to connect to database [' . $db->connect_error . ']');
    }
    
    $db->set_charset("utf8");

    $time = date('Y-m-d H:i:s');

    $query_notif = "INSERT INTO `book_notif` (`type`, `book_id`, `secondary_id` ,`user_id`,`created_at`) VALUES ('{$type}', '{$book_id}', '{$secondary_id}', '{$user_id}', '{$time}');";
    if( ! $result_notif = $db->query( $query_notif ) ){
        return false;
        }
        else{
            return true;
        }
        
        $db->close();
        
}


 function password_encrypt($password) {
        $hash_format = "$2y$10$";   // Tells PHP to use Blowfish with a "cost" of 10
        $salt_length = 22; 					// Blowfish salts should be 22-characters or more
        $salt = generate_salt($salt_length);
        $format_and_salt = $hash_format . $salt;
        $hash = crypt($password, $format_and_salt);
          return $hash;
      }
      
      function generate_salt($length) {
        // Not 100% unique, not 100% random, but good enough for a salt
        // MD5 returns 32 characters
        $unique_random_string = md5(uniqid(mt_rand(), true));
        
          // Valid characters for a salt are [a-zA-Z0-9./]
        $base64_string = base64_encode($unique_random_string);
        
          // But not '+' which is valid in base64 encoding
        $modified_base64_string = str_replace('+', '.', $base64_string);
        
          // Truncate string to the correct length
        $salt = substr($modified_base64_string, 0, $length);
        
          return $salt;
      }
      
      function generateRandomString($length) {
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

?>