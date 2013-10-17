<?php

require_once 'libs/PHPExcel/Classes/PHPExcel/IOFactory.php';
require_once 'HTML/Table.php';

abstract class Account
{
    protected $m_table;
    protected $m_cellType, $m_cellFMT, $m_catsFMT;
    protected $m_categories, $m_wordsToCat;

    public function __construct (&$categories, &$wordsToCat) {
	$this->m_categories = $categories;
	$this->m_wordsToCat = $wordsToCat;
        $this->m_table = new HTML_Table();
	
	$this->m_cellType = array("date", "desc", "reference", "amount","cat");
	$this->m_cellFMT = '<input type="hidden" name="%s_%d" id="%s_%d" value="%s" /> %s';
	$this->m_catsFMT = '<select id="%s_%d" name="%s_%d" onchange="this.style.borderColor=\'black\';">" %s </select>';
    }
    
    abstract public function parseExcel($inputFileName);

    public function getTable() {
	return $this->m_table;
    }

    public function getHtmlTable() {
	return $this->m_table->toHtml();
    }

    protected function initTable() {
	$attrs = array('width' => '600');
	$this->m_table->setAttributes($attrs);
	$hrAttrs = array('bgcolor' => 'gray');
	$this->m_table->setRowAttributes(0, $hrAttrs, true);
	$this->m_table->setColAttributes(0, $hrAttrs);

	$this->m_table->setHeaderContents(0, 0, '#');
	$this->m_table->setHeaderContents(0, 1, 'תאריך');
	$this->m_table->setHeaderContents(0, 2, 'תיאור');
	$this->m_table->setHeaderContents(0, 3, 'אסמכתא');
	$this->m_table->setHeaderContents(0, 4, 'סכום');
	$this->m_table->setHeaderContents(0, 5, 'קטגוריה');
	$altRow = array('bgcolor' => 'silver');
	$this->m_table->altRowAttributes(1, null, $altRow);
    }
}

class BankLeumi extends Account {

    const AccountName = 'בנק לאומי';

    public function parseExcel($inputFileName) {

	$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($inputFileName);
	$sheet = $objPHPExcel->getSheet(0); 
	$highestRow = $sheet->getHighestRow(); 
	$highestColumn = $sheet->getHighestColumn();
	$this->initTable();
	$transactionsTotal = 0;
	//  Loop through each row of the worksheet in turn
	for ($row = 17; $row <= $highestRow; $row++){ 
	    //  Read a row of data into an array
	    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
	    $this->m_table->setCellContents($row-16, 0, $row-16);
	    for ($i = 0; $i < 3; $i++) {
		$cellContent = sprintf($this->m_cellFMT, $this->m_cellType[$i], $row-16, $this->m_cellType[$i], $row-16, htmlspecialchars($rowData[0][$i]), $rowData[0][$i]);
		$this->m_table->setCellContents($row-16, $i+1, $cellContent);
	    }

	    $amount = 0;
	    if ( isset($rowData[0][3]) ) {
		$amount -= $rowData[0][3];
	    }
	    else if ( isset($rowData[0][4]) ){
		$amount += $rowData[0][4];
	    }
	    $transactionsTotal += $amount;
	    $cellContent = sprintf($this->m_cellFMT, $this->m_cellType[3], $row-16, $this->m_cellType[3], $row-16, $amount, $amount);
	    $this->m_table->setCellContents($row-16, 4, $cellContent);
    
	    if ( array_key_exists($rowData[0][1], $this->m_wordsToCat) ) {
		$cat_name=$rowData[0][1];
		$cat_index=$this->m_wordsToCat[$cat_name];
		$to_replace="value='" . $cat_index ."'";
		$tmp_categories = str_replace($to_replace, $to_replace . " selected", $this->m_categories);
		$cats = sprintf($this->m_catsFMT, $this->m_cellType[4], $row-16, $this->m_cellType[4], $row-16, implode(' ', $tmp_categories));
	    }
	    else 
		$cats = sprintf($this->m_catsFMT, $this->m_cellType[4], $row-16, $this->m_cellType[4], $row-16, implode(' ', $this->m_categories));
	    $this->m_table->setCellContents($row-16, 5, $cats);
	}
	$this->m_table->setCellContents($row-16, 2, 'סה"כ');
	$this->m_table->setCellContents($row-16, 4, round($transactionsTotal,2));
    }
}

?>