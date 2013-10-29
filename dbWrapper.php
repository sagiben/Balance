<?php

class dbWrapper
{
    private $m_db = null;
    private static $_instance;

    const DB_FILENAME = 'balance.db';

    public static function getInstance() {

	if(is_null(self::$_instance)) {
	    self::$_instance = new self();
	}
	return self::$_instance;
    }

    private function __construct() {
	
	if (!($this->m_db = new SQLite3(self::DB_FILENAME))) {
	    echo $this->m_db->lastErrorMsg();
	    die('Could not connect: ' . $this->m_db->lastErrorMsg());
	}
    }

    private function __clone() {}

    function __destruct() {
	$this->m_db->close();
	self::$_instance = null;
    }

    public function getTable($tbl, $orderby = null) {
	
	$select = 'SELECT * FROM ' . $tbl;
	if ( $orderby != null)
	    $select .= ' ORDER BY ' . $orderby;
	$statement = $this->m_db->prepare($select . ';');
	$result = $statement->execute();
	$statement = null;
	return $result;
    }

    public function select($what, $from, $where){
	
	$result = array();
	$select = sprintf('SELECT %s FROM %s WHERE %s;', $what, $from, $where);
	//    $select = $db->escapeString($select);
	$qres = $this->m_db->query($select);
	
	$cols = $qres->numColumns();
	while ($row = $qres->fetchArray()) {
	    for ($i = 0; $i < $cols; $i++) {
		$result[$qres->columnName($i)] = $row[$i];
	    }
	}
	return $result;
    }

    public function query($sql){

	//    $sql = $db->escapeString($sql);
	//    $statement = $db->prepare($sql);
	//$result = $statement->execute();
	//$statement = null;
	$result = $this->m_db->exec($sql);
	if (!$result)
	    echo $this->m_db->lastErrorMsg();
	else
	    return $result;
    }
    
}

?>
