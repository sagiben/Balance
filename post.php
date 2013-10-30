<html lang="he" dir="rtl">
<head>
     <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
     <title>Balance :: קובץ חדש</title>
     <style type="text/css">@import url("css/style1.css");</style>
     <style type="text/css">@import url("css/nav.css");</style>
     <style type="text/css">@import url("css/menu.css");</style>
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

require_once 'dbWrapper.php';
require_once 'HTML/Table.php';
$db = dbWrapper::getInstance();

$arr = $_POST;
$cellType = array("date", "desc", "reference", "amount", "cat");

$numRows = (int)((count($arr)-1)/count($cellType));

$insertTransFMT = "INSERT INTO transactions (id, date, reference_id, reference_desc, full_amount, current_amount, owner, account) 
                   VALUES (%d, '%s', %d, '%s', %f, %f, %d, %d);";
$insertTransToCatFMT = "INSERT INTO trans_to_category (cat_id, trans_id) VALUES (%d, %d);";
$insertWordsToCatFMT = "INSERT OR IGNORE INTO words_to_category (cat_id, word) VALUES (%d,'%s');";

$last_id_ar=$db->select("seq", "SQLITE_SEQUENCE", "name='transactions'");
$last_id=0;
if ($last_id_ar!=NULL)
    $last_id=$last_id_ar['seq'];

$excludedTbl = $db->getTable('excluded_categories');
$excluded_cats = array();
while($row = $excludedTbl->fetchArray(SQLITE3_ASSOC) ){
    array_push($excluded_cats, $row['cat_id']);
}

$excluded_transactions = array();

$insert_sql = "BEGIN;" . PHP_EOL;
for ($i = 1, $id=$last_id; $i <= $numRows; $i++) {
    $typesWithRow = $cellType;
    foreach ($typesWithRow as &$value)
	$value = $value . '_' . $i;
    
    if ( in_array($arr[$typesWithRow[4]], $excluded_cats) ) {
	$excludeMe = sprintf("התעלמתי מפעולה # %d השייכת לקטגוריה %d", $i, $arr[$typesWithRow[4]]);
	echo $excludeMe . EOL;     
	array_push($excluded_transactions, $arr[$typesWithRow[4]]);
	$insertWordsToCat = sprintf($insertWordsToCatFMT, $arr[$typesWithRow[4]], $arr[$typesWithRow[1]]);
	$insert_sql .= $insertWordsToCat . PHP_EOL;
	continue;
    }

    $date = DateTime::createFromFormat('d/m/y', $arr[$typesWithRow[0]]);
    $insertTrans = sprintf($insertTransFMT, ++$id, $date->format("Y-m-d"), $arr[$typesWithRow[2]], $arr[$typesWithRow[1]], 
			    $arr[$typesWithRow[3]], $arr[$typesWithRow[3]], $arr["owner"], $arr["account"]);
    $insertTransToCat = sprintf($insertTransToCatFMT, $arr[$typesWithRow[4]], $id);
    $insertWordsToCat = sprintf($insertWordsToCatFMT, $arr[$typesWithRow[4]], $arr[$typesWithRow[1]]);
    $insert_sql .= $insertTrans . PHP_EOL . $insertTransToCat . PHP_EOL . $insertWordsToCat . PHP_EOL;
}
$insert_sql .= 'COMMIT;' . PHP_EOL;
//echo '<div dir="rtl">', PHP_EOL . $insert_sql , EOL, PHP_EOL , '</div>';
if ($db->query($insert_sql))
    echo ($numRows-count($excluded_transactions)) . " פעולות, התווספו בהצלחה ";
else
    echo "ההוספה נכשלה";

/*
$attrs = array('width' => '600');
$table = new HTML_Table();
$table->setAttributes($attrs);
$hrAttrs = array('bgcolor' => 'gray');
$table->setRowAttributes(0, $hrAttrs, true);
$table->setColAttributes(0, $hrAttrs);

$table->setHeaderContents(0, 0, "");
$table->setHeaderContents(0, 1, 'תאריך');
$table->setHeaderContents(0, 2, 'תיאור');
$table->setHeaderContents(0, 3, 'אסמכתא');
$table->setHeaderContents(0, 4, 'סכום');
$table->setHeaderContents(0, 5, 'קטגוריה');

$cellFMT="%s_%d";
for ($i = 1; $i <= $numRows; $i++) {
    $table->setCellContents($i, 0, $i);
    for ($j=0; $j<5; $j++){
	$loc = sprintf($cellFMT, $cellType[$j], $i);
	$table->setCellContents($i, $j+1, $arr[$loc]);
    }
}
$altRow = array('bgcolor' => 'silver');
$table->altRowAttributes(1, null, $altRow);

echo $table->toHtml();
*/

?>
</body>
</html>