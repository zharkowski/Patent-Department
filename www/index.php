 <?php
 include_once "../php/classes/User.php";

 $user = new User();
 if (isset($_COOKIE['session_id']))
 {
     $user->authorizeBySessionId($_COOKIE['session_id']);
 }

 if ($user->isAuthorized())
 {
     header("Location: /pages/home");
     return;
 }
 header("Location: /pages/login");
