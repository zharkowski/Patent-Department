<?php
//
//function checkPatentAccessRight ($patentId)
//{
//    $user = new User();
//    if (isset($_COOKIE['session_id']))
//    {
//        $user->authorizeBySessionId($_COOKIE['session_id']);
//    }
//
//    $userId = $user->getId();
//    $query = "SELECT user_id FROM patent_department_db.patents WHERE id = ?";
////    if ($stmt = $this->mysqli->prepare($query)) {
////        $stmt->bind_param("i", $patentId);
////        $stmt->execute();
////        $stmt->bind_result($ownerId);
////        $stmt->fetch();
////        $stmt->close();
////    }
//
//    if (!$user->getId() == $ownerId && !$user->getGroupId() == 2) {
//        header("Location: /");
//        return;
//    }
//}