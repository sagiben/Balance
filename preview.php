<html dir=rtl>
<head>
     <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
     <script>
     $(document).ready(function() {
	     $('form').submit(function(msg) {  
		     alert($(this).serialize()); // check to show that all form data is being submitted
		     $.post("post.php",$(this).serialize(),function(data){
			     alert(data); //post check to show that the mysql string is the same as submit                        
			 });
		     return false; // return false to stop the page submitting. You could have the form action set to the same PHP page so if people dont have JS on they can still use the form
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

require_once 'libs/PHPExcel/Classes/PHPExcel/IOFactory.php';
require_once 'HTML/Table.php';
require_once 'dbconnect.inc';

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
dbclose();

$inputFileName = $_FILES["file"]["tmp_name"];
$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($inputFileName);

$sheet = $objPHPExcel->getSheet(0); 
$highestRow = $sheet->getHighestRow(); 
$highestColumn = $sheet->getHighestColumn();

$attrs = array('width' => '600');
$table = new HTML_Table();
$table->setAttributes($attrs);
$hrAttrs = array('bgcolor' => 'gray');
$table->setRowAttributes(0, $hrAttrs, true);
$table->setColAttributes(0, $hrAttrs);

$table->setHeaderContents(0, 0, '#');
$table->setHeaderContents(0, 1, 'תאריך');
$table->setHeaderContents(0, 2, 'תיאור');
$table->setHeaderContents(0, 3, 'אסמכתא');
$table->setHeaderContents(0, 4, 'סכום');
$table->setHeaderContents(0, 5, 'קטגוריה');
$cellType = array("date", "desc", "reference", "amount","cat");
$cellFMT = '<input type="hidden" name="%s_%d" id="%s_%d" value="%s" /> %s';
$catsFMT = '<select id="%s_%d" name="%s_%d" onchange="this.style.borderColor=\'black\';">" %s </select>';
//  Loop through each row of the worksheet in turn
for ($row = 17; $row <= $highestRow; $row++){ 
    //  Read a row of data into an array
    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                                    NULL,
                                    TRUE,
                                    FALSE);
    
    $table->setCellContents($row-16, 0, $row-16);
    for ($i = 0; $i < 3; $i++) {
	$cellContent = sprintf($cellFMT, $cellType[$i], $row-16, $cellType[$i], $row-16, htmlspecialchars($rowData[0][$i]), $rowData[0][$i]);
	$table->setCellContents($row-16, $i+1, $cellContent);
    }

    $amount = 0;
    if ( isset($rowData[0][3]) ) {
	$amount -= $rowData[0][3];
    }
    else if ( isset($rowData[0][4]) ){
	$amount += $rowData[0][4];
    }
    $cellContent = sprintf($cellFMT, $cellType[3], $row-16, $cellType[3], $row-16, $amount, $amount);
    $table->setCellContents($row-16, 4, $cellContent);
    

    if ( array_key_exists($rowData[0][1], $words_to_cat) ) {
	$cat_name=$rowData[0][1];
	$cat_index=$words_to_cat[$cat_name];
	$to_replace="value='" . $cat_index ."'";
	$tmp_categories = str_replace($to_replace, $to_replace . " selected", $categories);
	$cats = sprintf($catsFMT, $cellType[4], $row-16, $cellType[4], $row-16, implode(' ', $tmp_categories));
    }
    else 
      $cats = sprintf($catsFMT, $cellType[4], $row-16, $cellType[4], $row-16, implode(' ', $categories));
    $table->setCellContents($row-16, 5, $cats);
}

$altRow = array('bgcolor' => 'silver');
$table->altRowAttributes(1, null, $altRow);

echo '<form method="post" id="test" enctype="multipart/form-data" action="post.php" onsubmit="return validateCategories()">';
echo $table->toHtml();
echo '<input type="hidden" name="owner" value="', $owner, '"></input>';
echo '<input type="hidden" name="account" value="', $account, '"></input>';
echo '<input type="submit" value="שלח" name="submit" id="submit" />';
echo '</from>';

?>
</body>
</html>