<html lang="he" dir="rtl">
<head>
     <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>

<form action="preview.php" method="post" enctype="multipart/form-data">
<?php
date_default_timezone_set('Asia/Jerusalem');

require_once 'HTML/Table.php';
require_once 'dbconnect.inc';
dbconnect();

$tblOwners = dbGetTable("owners");
$owners[] = "<option value='0'>--?--</option>";
while($row = $tblOwners->fetchArray(SQLITE3_ASSOC) ){
  $owners[] = "<option value='" . $row['id']. "'>" . $row['name']. "</option>";
}

$attrs = array('width' => '600');
$table = new HTML_Table();
$table->setAttributes($attrs);
$table->setCellContents(0, 0, 'בעל החשבון');
$ownerSelect = '<select id="owner" name="owner">' . PHP_EOL . implode(PHP_EOL, $owners) . PHP_EOL . '</select>';
$table->setCellContents(0, 1, $ownerSelect);

$tblAccounts = dbGetTable("accounts");
$accounts[] = "<option value='0'>--?--</option>";
while($row = $tblAccounts->fetchArray(SQLITE3_ASSOC) ){
  $accounts[] = "<option value='" . $row['id']. "'>" . $row['name']. "</option>";
}

$table->setCellContents(1, 0, 'סוג החשבון');
$accountSelect = '<select id="account" name="account">' . PHP_EOL . implode(PHP_EOL, $accounts) . PHP_EOL .'</select>';
$table->setCellContents(1, 1, $accountSelect);

$table->setCellContents(2, 0, '<label for="file">קובץ:</label>');
$table->setCellContents(2, 1, '<input type="file" name="file" id="file">');
echo $table->toHtml();
?>

  <input type="submit" name="submit" value="שלח">
</form>

</body>
</html> 
