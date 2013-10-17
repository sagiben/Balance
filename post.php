<html>
<head>
     <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<?php

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : ' <br />');
date_default_timezone_set('Asia/Jerusalem');

require_once 'dbconnect.inc';
require_once 'HTML/Table.php';
dbConnect();

$arr = $_POST;
$numRows = (count($arr)-1)/5;

$cellType = array("date", "desc", "reference", "amount", "cat");
$insertTransFMT = "INSERT INTO transactions (id, date, reference_id, reference_desc, full_amount, current_amount, owner, account) 
                   VALUES (%d, '%s', %d, '%s', %f, %f, %d, %d);";
$insertTransToCatFMT = "INSERT INTO trans_to_category (cat_id, trans_id) VALUES (%d, %d);";
$insertWordsToCatFMT = "INSERT OR IGNORE INTO words_to_category (cat_id, word) VALUES (%d,'%s');";

$last_id_ar=dbSelect("seq", "SQLITE_SEQUENCE", "name='transactions'");
$last_id=0;
if ($last_id_ar!=NULL)
    $last_id=$last_id_ar['seq'];

$excludedTbl = dbGetTable('excluded_categories');
$excluded_cats = array();
while($row = $excludedTbl->fetchArray(SQLITE3_ASSOC) ){
    array_push($excluded_cats, $row['cat_id']);
}

echo '<div dir="ltr">', PHP_EOL;
$insert_sql = "BEGIN;" . PHP_EOL;
for ($i = 1, $id=$last_id; $i <= $numRows; $i++) {
    $typesWithRow = $cellType;
    foreach ($typesWithRow as &$value)
	$value = $value . '_' . $i;

    if ( in_array($arr[$typesWithRow[4]], $excluded_cats) ) {
	echo $arr[$typesWithRow[4]]. "is in excluded" . EOL;
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
echo $insert_sql , EOL, PHP_EOL;
var_dump(dbQuery($insert_sql));
echo '</div>'

/*
$attrs = array('width' => '600');
$table = new HTML_Table();
$table->setAttributes($attrs);
$hrAttrs = array('bgcolor' => 'gray');
$table->setRowAttributes(0, $hrAttrs, true);
$table->setColAttributes(0, $hrAttrs);

$table->setHeaderContents(0, 0, "#");
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