<?php

require_once 'libs/PHPExcel/Classes/PHPExcel/IOFactory.php';
require_once 'HTML/Table.php';

abstract class Account
{
    protected $m_table;
    protected $m_cellType, $m_cellFMT, $m_catsFMT;
    protected $m_categories, $m_wordsToCat;
    protected $m_amountAttr, $m_amountAltAttr;

    public function __construct (&$categories, &$wordsToCat) {
	$this->m_categories = $categories;
	$this->m_wordsToCat = $wordsToCat;
        $this->m_table = new HTML_Table();
	
	$this->m_cellType = array("date", "desc", "reference", "amount", "cat");
	$this->m_cellFMT = '<input type="hidden" name="%s_%d" id="%s_%d" value="%s" />%s';
	$this->m_catsFMT = '<select id="%s_%d" name="%s_%d" onchange="this.style.borderColor=\'black\';">" %s </select>';

	$this->m_amountAttr = array('class' => 'pme-cell-0-amount');
	$this->m_amountAltAttr = array('class' => 'pme-cell-1-amount');
    }
    
    abstract public function parseExcel($inputFileName);

    public function getTable() {
	return $this->m_table;
    }

    public function getHtmlTable() {
	return $this->m_table->toHtml();
    }

    protected function initTable() {
	$attrs = array('width' => '600', 'class' => 'pme-main');
	$this->m_table->setAttributes($attrs);
	//	$hrAttrs = array('bgcolor' => 'gray');
	$hrAttrs = array('class' => 'pme-header');
	$this->m_table->setRowAttributes(0, $hrAttrs, true);
	$this->m_table->setColAttributes(0, $hrAttrs);
	$this->m_table->setColAttributes(1, $hrAttrs);
	$this->m_table->setColAttributes(2, $hrAttrs);
	$this->m_table->setColAttributes(3, $hrAttrs);
	$this->m_table->setColAttributes(4, $hrAttrs);
	$this->m_table->setColAttributes(5, $hrAttrs);
	
	$this->m_table->setHeaderContents(0, 0, '#');
	$this->m_table->setHeaderContents(0, 1, 'תאריך');
	$this->m_table->setHeaderContents(0, 2, 'תיאור');
	$this->m_table->setHeaderContents(0, 3, 'אסמכתא');
	$this->m_table->setHeaderContents(0, 4, 'סכום');
	$this->m_table->setHeaderContents(0, 5, 'קטגוריה');
    }

    protected function finishTable($numRows) {
	//	$altRow = array('bgcolor' => 'silver');
	$row = array('class'=>'pme-row-0');
	$cell = array('class' => 'pme-cell-0');
	$this->m_table->altRowAttributes(0, null, $row, true);
	$this->m_table->altRowAttributes(0, null, $cell, false);
	$altRow = array('class'=>'pme-row-1');
	$altCell = array('class' => 'pme-cell-1');
	$this->m_table->altRowAttributes(1, null, $altRow, true);
	$this->m_table->altRowAttributes(1, null, $altCell, false);

	for($i=1; $i<=$numRows+1;$i++) {
	    if ( $i % 2 == 1 )
		$this->m_table->setCellAttributes($i, 4, $this->m_amountAttr);
	    else 
		$this->m_table->setCellAttributes($i, 4, $this->m_amountAltAttr);
	}
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

	$cellVal = $sheet->getCell('A2')->getValue();
	if (false === mb_strpos($cellVal, $this::AccountName)) {
	    echo "<h2> שגיאה בהעלאת הקובץ </h2>";
	    return false;
	}

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
	$this->finishTable($row-17);
	return true;
    }
}


class VisaLeumi extends Account {

    const AccountName = 'ויזה לאומי';

    public function parseExcel($inputFileName) {
	$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($inputFileName);
	$sheet = $objPHPExcel->getSheet(0); 
	$highestRow = $sheet->getHighestRow(); 
	$highestColumn = $sheet->getHighestColumn();

	$signatureArr = array ("תאריך עסקה", "תאריך חיוב", "שם בית העסק", "סוג עסקה","מטבע עסקה", "סכום עסקה", "סכום חיוב ₪", "הערות");
	$validateArr = $sheet->rangeToArray('A1:H1', NULL, TRUE, FALSE);
	if ($signatureArr != $validateArr[0]) {
	    echo "<h2> שגיאה בהעלאת הקובץ </h2>";
	    return false;
	}

	$this->initTable();

	$total = 0;
	$rowCounter = 1;
	//  Loop through each row of the worksheet in turn
	for ($row = 2; $row <= $highestRow; $row++){ 
	    //  Read a row of data into an array
	    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
	    $col = 0;
	    if ($rowData[0][6] == '0') {
		continue;
	    }

	    // Number
	    $this->m_table->setCellContents($rowCounter, $col++, $rowCounter);

	    //Date
	    $date = PHPExcel_Style_NumberFormat::toFormattedString($rowData[0][0], 'dd/mm/yy');
	    $cellContent = sprintf($this->m_cellFMT, 
				   $this->m_cellType[0], $rowCounter, 
				   $this->m_cellType[0], $rowCounter, 
				   htmlspecialchars($date), $date);
	    $this->m_table->setCellContents($rowCounter, $col++, $cellContent);

	    //Desc
	    $cellContent = sprintf($this->m_cellFMT, 
				   $this->m_cellType[1], $rowCounter, 
				   $this->m_cellType[1], $rowCounter, 
				   htmlspecialchars($rowData[0][2]), $rowData[0][2]);
	    $this->m_table->setCellContents($rowCounter, $col++, $cellContent);

	    //Reference
	    $cellContent = sprintf($this->m_cellFMT, 
				   $this->m_cellType[2], $rowCounter, 
				   $this->m_cellType[2], $rowCounter, 
				   '', '');
	    $this->m_table->setCellContents($rowCounter, $col++, $cellContent);
	    
	    //Amount
	    $amount = -$rowData[0][6];
	    $total += $amount;
	    $cellContent = sprintf($this->m_cellFMT, 
				   $this->m_cellType[3], $rowCounter, 
				   $this->m_cellType[3], $rowCounter, 
				   htmlspecialchars(round($amount, 2)), round($amount, 2));
	    $this->m_table->setCellContents($rowCounter, $col++, $cellContent);
	    
	    //Category
	    if ( array_key_exists($rowData[0][2], $this->m_wordsToCat) ) {
		$cat_name=$rowData[0][2];
		$cat_index=$this->m_wordsToCat[$cat_name];
		$to_replace="value='" . $cat_index ."'";
		$tmp_categories = str_replace($to_replace, $to_replace . " selected", $this->m_categories);
		$cats = sprintf($this->m_catsFMT, 
				$this->m_cellType[4], $rowCounter, 
				$this->m_cellType[4], $rowCounter, implode(' ', $tmp_categories));
	    }
	    else 
		$cats = sprintf($this->m_catsFMT, 
				$this->m_cellType[4], $rowCounter,
				$this->m_cellType[4], $rowCounter,
				implode(' ', $this->m_categories));
	    $this->m_table->setCellContents($rowCounter, $col, $cats);

	    $rowCounter++;
	}
	$this->m_table->setCellContents($rowCounter, 2, 'סה"כ');
	$this->m_table->setCellContents($rowCounter, 4, round($total,2));
	$this->finishTable($rowCounter-1);
	return true;
    }
}


class IsraCard extends Account {

    const AccountName = 'ישראכרט';

    public function parseExcel($inputFileName) {
	$objReader = PHPExcel_IOFactory::createReader('HTML');
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($inputFileName);
	$sheet = $objPHPExcel->getSheet(0); 
	$highestRow = $sheet->getHighestRow(); 
	$highestColumn = $sheet->getHighestColumn();

	$signatureArr = array ("תאריך רכישה", "שם בית עסק", "סכום עסקה", "סכום לחיוב", "מספר שובר", "פרוט נוסף");
	$validateArr = $sheet->rangeToArray('A4:F4', NULL, TRUE, FALSE);
	if ($signatureArr !== $validateArr[0]) {
	    echo "<h2> שגיאה בהעלאת הקובץ </h2>";
	    return false;
	}

	$this->initTable();

	$totalRow = $highestRow-1;
	$rowData = $sheet->rangeToArray('C' . $totalRow . ':' . 'D' . $totalRow, NULL, TRUE, FALSE);
	$expectedTotal = $rowData[0][1];
	$interval = new DateInterval('P1M1D');
	$chargeDate = DateTime::createFromFormat('d/m/y', $rowData[0][0])->sub($interval);
	
	$total = 0;
	$rowCounter = 1;
	//  Loop through each row of the worksheet in turn
	for ($row = 6; $row <= $highestRow-2; $row++){ 
	    //  Read a row of data into an array
	    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
	    $col = 0;
	    if ($rowData[0][3] == '0') {
		continue;
	    }

	    // Number
	    $this->m_table->setCellContents($rowCounter, $col++, $rowCounter);

	    //Date
	    $date = DateTime::createFromFormat('d/m/Y', $rowData[0][0]);
	    if ( $date < $chargeDate )
		$dateStr = $chargeDate->format('d/m/y');
	    else
		$dateStr = $date->format('d/m/y');
	    $cellContent = sprintf($this->m_cellFMT, 
				   $this->m_cellType[0], $rowCounter, 
				   $this->m_cellType[0], $rowCounter, 
				   htmlspecialchars($dateStr), $dateStr);
	    $this->m_table->setCellContents($rowCounter, $col++, $cellContent);

	    //Desc
	    $strDesc = str_replace('\'', '', str_replace('"', '', $rowData[0][1]));
	    $cellContent = sprintf($this->m_cellFMT, 
				   $this->m_cellType[1], $rowCounter, 
				   $this->m_cellType[1], $rowCounter, 
				   htmlspecialchars($strDesc), $strDesc);
	    $this->m_table->setCellContents($rowCounter, $col++, $cellContent);

	    //Reference
	    $cellContent = sprintf($this->m_cellFMT, 
				   $this->m_cellType[2], $rowCounter, 
				   $this->m_cellType[2], $rowCounter, 
				   htmlspecialchars($strDesc), $rowData[0][4]);
	    $this->m_table->setCellContents($rowCounter, $col++, $cellContent);
	    
	    //Amount
	    $amount = -$rowData[0][3];
	    $total += $amount;
	    $cellContent = sprintf($this->m_cellFMT, 
				   $this->m_cellType[3], $rowCounter, 
				   $this->m_cellType[3], $rowCounter, 
				   htmlspecialchars(round($amount, 2)), round($amount, 2));
	    $this->m_table->setCellContents($rowCounter, $col++, $cellContent);
	    
	    //Category
	    if ( array_key_exists($rowData[0][1], $this->m_wordsToCat) ) {
		$cat_name=$rowData[0][1];
		$cat_index=$this->m_wordsToCat[$cat_name];
		$to_replace="value='" . $cat_index ."'";
		$tmp_categories = str_replace($to_replace, $to_replace . " selected", $this->m_categories);
		$cats = sprintf($this->m_catsFMT, 
				$this->m_cellType[4], $rowCounter, 
				$this->m_cellType[4], $rowCounter, implode(' ', $tmp_categories));
	    }
	    else 
		$cats = sprintf($this->m_catsFMT, 
				$this->m_cellType[4], $rowCounter,
				$this->m_cellType[4], $rowCounter,
				implode(' ', $this->m_categories));
	    $this->m_table->setCellContents($rowCounter, $col, $cats);

	    $rowCounter++;
	}
	$this->m_table->setCellContents($rowCounter, 2, 'סה"כ');
	$this->m_table->setCellContents($rowCounter, 4, round($total,2));
	$this->finishTable($rowCounter-1);
	return true;
    }
}



class VisaCAL extends Account {

    const AccountName = 'ויזה כאל';

    public function parseExcel($inputFileName) {
	$objReader = PHPExcel_IOFactory::createReader('HTML');
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($inputFileName);
	$sheet = $objPHPExcel->getSheet(0); 
	$highestRow = $sheet->getHighestRow(); 
	$highestColumn = $sheet->getHighestColumn();

	$signatureArr = array ('תאריך העסקה', 'שם בית העסק', 'סכום העסקה', '', 'סכום החיוב', '', 'פירוט נוסף');
	$validateArr = $sheet->rangeToArray('A6:G6', NULL, TRUE, FALSE);
	$validateArr = str_replace(PHP_EOL, ' ', $validateArr[0]);
	if ($signatureArr !== $validateArr) {
	    echo "<h2> שגיאה בהעלאת הקובץ </h2>";
	    return false;
	}

	$this->initTable();
	/*
	$totalRow = $highestRow-1;
	$rowData = $sheet->rangeToArray('C' . $totalRow . ':' . 'D' . $totalRow, NULL, TRUE, FALSE);
	$expectedTotal = $rowData[0][1];
	$interval = new DateInterval('P1M1D');
	$chargeDate = DateTime::createFromFormat('d/m/y', $rowData[0][0])->sub($interval);
	*/
	
	$total = 0;
	$rowCounter = 1;
	//  Loop through each row of the worksheet in turn
	for ($row = 7; $row <= $highestRow-1; $row++){ 
	    //  Read a row of data into an array
	    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
	    $col = 0;
	    if ($rowData[0][3] == '0') {
		continue;
	    }

	    // Number
	    $this->m_table->setCellContents($rowCounter, $col++, $rowCounter);

	    //Date
	    $date = DateTime::createFromFormat('d/m/y', $rowData[0][0]);
	    //if ( $date < $chargeDate )
	    //$dateStr = $chargeDate->format('d/m/y');
	    //else
		$dateStr = $date->format('d/m/y');
	    $cellContent = sprintf($this->m_cellFMT, 
				   $this->m_cellType[0], $rowCounter, 
				   $this->m_cellType[0], $rowCounter, 
				   htmlspecialchars($dateStr), $dateStr);
	    $this->m_table->setCellContents($rowCounter, $col++, $cellContent);

	    //Desc
	    //$strDesc = str_replace('\'', '', str_replace('"', '', $rowData[0][1]));
	    $cellContent = sprintf($this->m_cellFMT, 
				   $this->m_cellType[1], $rowCounter, 
				   $this->m_cellType[1], $rowCounter, 
				   htmlspecialchars($rowData[0][1]), $rowData[0][1]);
	    $this->m_table->setCellContents($rowCounter, $col++, $cellContent);

	    //Reference
	    $cellContent = sprintf($this->m_cellFMT, 
				   $this->m_cellType[2], $rowCounter, 
				   $this->m_cellType[2], $rowCounter, 
				   htmlspecialchars($rowData[0][1]), '');
	    $this->m_table->setCellContents($rowCounter, $col++, $cellContent);
	    
	    //Amount
	    $amount = -$rowData[0][4];
	    $total += $amount;
	    $cellContent = sprintf($this->m_cellFMT, 
				   $this->m_cellType[3], $rowCounter, 
				   $this->m_cellType[3], $rowCounter, 
				   htmlspecialchars(round($amount, 2)), round($amount, 2));
	    $this->m_table->setCellContents($rowCounter, $col++, $cellContent);
	    
	    //Category
	    if ( array_key_exists($rowData[0][1], $this->m_wordsToCat) ) {
		$cat_name=$rowData[0][1];
		$cat_index=$this->m_wordsToCat[$cat_name];
		$to_replace="value='" . $cat_index ."'";
		$tmp_categories = str_replace($to_replace, $to_replace . " selected", $this->m_categories);
		$cats = sprintf($this->m_catsFMT, 
				$this->m_cellType[4], $rowCounter, 
				$this->m_cellType[4], $rowCounter, implode(' ', $tmp_categories));
	    }
	    else 
		$cats = sprintf($this->m_catsFMT, 
				$this->m_cellType[4], $rowCounter,
				$this->m_cellType[4], $rowCounter,
				implode(' ', $this->m_categories));
	    $this->m_table->setCellContents($rowCounter, $col, $cats);

	    $rowCounter++;
	}
	$this->m_table->setCellContents($rowCounter, 2, 'סה"כ');
	$this->m_table->setCellContents($rowCounter, 4, round($total,2));
	$this->finishTable($rowCounter-1);
	return true;
    }
}


?>
