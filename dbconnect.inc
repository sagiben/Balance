<?php

define('DB_FILENAME', 'balance.db');
$db  = NULL;

function dbConnect() {
  global $db;

  if (!($db = new SQLite3(DB_FILENAME))) {
      echo $db->lastErrorMsg();
      die('Could not connect: ' . $dbcon->lastErrorMsg());
  }
}

function dbClose() {
    global $db;
    $db->close();
}

function dbGetTable($tbl) {
    global $db;

    if ( $db == null )
	return null;

    $statement = $db->prepare('SELECT * FROM ' . $tbl. ';');
    $result = $statement->execute();
    $statement = null;
    return $result;
}

function dbSelect($what, $from, $where){
    global $db;

    if ( $db == null )
	return null;

    $result = array();
    $select = sprintf('SELECT %s FROM %s WHERE %s;', $what, $from, $where);
    //    $select = $db->escapeString($select);
    $qres = $db->query($select);
    
    $cols = $qres->numColumns();
    while ($row = $qres->fetchArray()) {
	for ($i = 0; $i < $cols; $i++) {
	    $result[$qres->columnName($i)] = $row[$i];
	}
    }
    return $result;
}

function dbQuery($sql){
    global $db;

    if ( $db == null )
	return null;

    //    $sql = $db->escapeString($sql);
    //    $statement = $db->prepare($sql);
    //$result = $statement->execute();
    //$statement = null;
    $result = $db->exec($sql);
    if (!$result)
	echo $db->lastErrorMsg();
    else
	return $result;
}
?>