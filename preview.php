<html lang="he" dir="rtl">
<head>
     <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
     <title>Balance :: קובץ חדש</title>
     <style type="text/css">@import url("css/style1.css");</style>
     <style type="text/css">@import url("css/nav.css");</style>
     <style type="text/css">@import url("css/menu.css");</style>
          
<script>
     $(document).ready(function() {
	     $('form').submit(function(msg) {  
		     $.post("post.php",$(this).serialize(),function(data){
			     alert(data);
			 });
		     return false; // return false to stop the page submitting. 
		 });
	 });

function validateCategories() {
    var catInputs = document.getElementsByTagName("select");
    var numNotSelected = 0;
    for (x=0;x<catInputs.length;x++){
	var selectElem = document.getElementById("cat_" + (x+1));
	if (selectElem.value == '0') { 
	    numNotSelected++;
	    selectElem.style.borderColor = "red";
	}
    }
    return (numNotSelected==0);
}
</script>
</head>
<body>
<?php
  require_once 'nav_header.php';
  header_select("קובץ חדש");
?>

<h1> העלאת קובץ פעולות </h1>

<?php

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : ' <br />');
date_default_timezone_set('Asia/Jerusalem');

require_once 'HTML/Table.php';
require_once 'dbWrapper.php';
require_once 'Accounts.php';

//TODO : input validation
$owner = $_POST['owner'];
$account = $_POST['account'];

$db = dbWrapper::getInstance();
$categories[] = "<option value='0'>--?--</option>";
$catTbl = $db->getTable('categories', "name");
while($row = $catTbl->fetchArray(SQLITE3_ASSOC) ){
  $categories[] = "<option value='" . $row['id']. "'>" . $row['name']. "</option>";
}

$wordsTbl = $db->getTable('words_to_category');
$words_to_cat = array();
while($row = $wordsTbl->fetchArray(SQLITE3_ASSOC) ){
  $words_to_cat[$row['word']]=$row['cat_id'];
}

$accountsTbl = $db->select('class', 'accounts', "id='" . $account . "'");
$accountClass = $accountsTbl['class'];
$inputFileName = $_FILES["file"]["tmp_name"];
$myAccount = new $accountClass($categories, $words_to_cat);
if (!$myAccount->parseExcel($inputFileName))
    return;

$table = $myAccount->getHtmlTable();

echo '<form method="post" id="test" enctype="multipart/form-data" action="post.php" onsubmit="return validateCategories()">', PHP_EOL;
echo $table;
echo '<input type="hidden" name="owner" value="', $owner, '" />', PHP_EOL;
echo '<input type="hidden" name="account" value="', $account, '" />', PHP_EOL;
echo '<input type="submit" value="שלח" name="submit" id="submit" />', PHP_EOL;
echo '</from>', PHP_EOL;

?>
</body>
</html>