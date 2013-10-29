<html lang="he" dir="rtl">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Balance :: קובץ חדש</title>
	<style type="text/css">@import url("css/style1.css");</style>
	<style type="text/css">@import url("css/nav.css");</style>
	<style type="text/css">@import url("css/menu.css");</style>

	<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
	<script type="text/javascript" src="js/jscalendar/lang/calendar-he-utf8.js"></script>
	<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
	<link rel="stylesheet" type="text/css" media="screen" href="js/jscalendar/calendar-blue-mine.css">
</head>
<body>
<?php
  require_once 'nav_header.php';
  header_select("ריכוז פעולות");
?>

<h1>ריכוז פעולות</h1>
<?php

require_once 'general.inc';

// Table Name
$opts['tb'] = 'complete';

// Name of field which is the unique key
$opts['key'] = 'id';

// Type of key field (int/real/string/date etc.)
$opts['key_type'] = 'int';

// Sorting field(s)
$opts['sort_field'] = array('date');

/* Field definitions */
$opts['fdd']['id'] = array(
  'name'     => 'מס"ד',
  'select'   => 'T',
  'maxlen'   => 9,
  'sort'     => true,
  'colattrs' => 'width="40"'
);

$opts['fdd']['current_amount'] = array(
  'name'     => 'סכום',
  'select'   => 'T',
  'maxlen'   => 30,
  'sort'     => true,
  'css'      => array('postfix' => 'amount'),
  'colattrs' => 'width="60"'
);
$opts['fdd']['current_amount']['js']['required'] = true;

$opts['fdd']['reference_id'] = array(
  'name'     => 'אסמכתא',
  'select'   => 'T',
  'maxlen'   => 80,
  'nowrap'   => true,
  'sort'     => true,
  'colattrs' => 'width="70"'
);
$opts['fdd']['reference_id']['js']['required'] = true;

$opts['fdd']['reference_desc'] = array(
  'name'     => 'תיאור',
  'select'   => 'T',
  'maxlen'   => 80,
  'nowrap'   => true,
  'sort'     => true,
  'colattrs' => 'width="150"'
);
$opts['fdd']['reference_desc']['js']['required'] = true;

$opts['fdd']['date'] = array(
  'name'     => 'תאריך',
  'select'   => 'T',
  'maxlen'   => 10,
  'sort'     => true,
  'colattrs' => 'width="80"'
);

$opts['fdd']['owner_name'] = array(
  'name'     => 'בעל החשבון',
  'select'   => 'T',
  'maxlen'   => 80,
  'nowrap'   => true,
  'sort'     => true
);

$opts['fdd']['account_name'] = array(
  'name'     => 'חשבון',
  'select'   => 'T',
  'maxlen'   => 80,
  'nowrap'   => true,
  'sort'     => true,
  'colattrs' => 'width="80"'
);



// Now important call to phpMyEdit
//require_once 'libs/phpMyEdit-5.7.1/phpMyEdit.class.php';
//new phpMyEdit($opts);

require_once 'libs/phpMyEdit-5.7.1/extensions/phpMyEdit-mce-cal.class.php';
new phpMyEdit_mce_cal($opts);

?>


</body>
</html>
