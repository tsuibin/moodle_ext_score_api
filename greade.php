<?php
/**
 * Created by PhpStorm.
 * User: tsuibin
 * Date: 2016/3/7
 * Time: 9:04
 */

header("Content-type:application/json;charset=utf-8");

if (!file_exists('./config.php')) {
    header('Location: install.php');
}

require_once('config.php');

$link = new mysqli($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname);


if($link->connect_error){
    die("connect error: " . $link->connect_error);
}
$args_error = "When you see this message , it imply the illegal word sign in the data.";


$sid = $_REQUEST['sid'];

$courseid = $_REQUEST['courseid'];

if (!$courseid) die('Error args courseid');

preg_match('/^\d+$/i', $courseid) or die($args_error);

$safe_courseid = $courseid;

$array_sno = explode(',',$sid);

$json_array = array();


foreach($array_sno as $sid){

    if (!$sid) die('Error args sid');

    preg_match('/^\d+$/i', $sid) or die($args_error);

    $safe_sid = $sid;

    $sql = "SELECT mdl_user.username as sid, TRUNCATE(max(mdl_grade_grades_history.rawgrade),1) as grade FROM mdl_user 
            join mdl_grade_grades_history 
            on mdl_grade_grades_history.userid = mdl_user.id 
            and mdl_user.deleted='0' 
            and mdl_user.username=".$safe_sid." 
            and mdl_grade_grades_history.source='mod/quiz' 
            join mdl_grade_items 
            on mdl_grade_items.courseid = ".$safe_courseid."  
            and mdl_grade_items.id = mdl_grade_grades_history.itemid 
            group by mdl_grade_grades_history.userid";

    $result = $link->query($sql);
    if (!$result) {
        printf("Error: %s\n", $link->connect_error);
        exit();
    }

    $rows=$result->fetch_assoc();

    array_push($json_array,$rows);

}

echo $str=json_encode($json_array);//将数组进行json编码
