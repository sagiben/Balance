<html dir=rtl>
<head>
     <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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

//error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : ' <br />');
date_default_timezone_set('Asia/Jerusalem');

require_once 'HTML/Table.php';
require_once 'dbconnect.inc';
require_once 'Accounts.php';

//TODO : input validation
$owner = $_POST['owner'];
$account = $_POST['account'];

dbconnect();
$categories[] = "<option value='0'>--?--</option>";
$catTbl = dbGetTable('categories');
while($row = $catTbl->fetchArray(SQLITE3_ASSOC) ){
  $categories[] = "<option value='" . $row['id']. "'>" . $row['name']. "</option>";
}

$wordsTbl = dbGetTable('words_to_category');
$words_to_cat = array();
while($row = $wordsTbl->fetchArray(SQLITE3_ASSOC) ){
  $words_to_cat[$row['word']]=$row['cat_id'];
}

$accountsTbl = dbSelect('class', 'accounts', "id='" . $account . "';");
$accountClass = $accountsTbl['class'];
dbclose();

$inputFileName = $_FILES["file"]["tmp_name"];
$myAccount = new $accountClass($categories, $words_to_cat);
$myAccount->parseExcel($inputFileName);
$table = $myAccount->getHtmlTable();

echo '<form method="post" id="test" enctype="multipart/form-data" action="post.php" onsubmit="return validateCategories()">';
echo $table;
echo '<input type="hidden" name="owner" value="', $owner, '"></input>';
echo '<input type="hidden" name="account" value="', $account, '"></input>';
echo '<input type="submit" value="שלח" name="submit" id="submit" />';
echo '</from>';

?>
</body>
</html>