<?php 

require_once dirname(__FILE__).'/../../../../classes/date.class.php';

class DateComparer {
	private $date;
	
	public function __construct($initialDate) {
		$this->date = new CDate($initialDate);
	}
	
	/**
	 * Compare the date hold by the instance with another
	 * take in by parameter
	 * @param string $otherDate string representation of a date
	 * @return DateComparisonResult
	 */
	public function compare($otherDate) {
		$comparison  = new CDate();
		$res = $comparison->compare(
			$this->date, new CDate($otherDate));
			
		switch($res) {
			case -1:
				return DateComparisonResult::$less;
			case 0:
				return DateComparisonResult::$equal;
			case 1:
				return DateComparisonResult::$great;
		}
		
		die("DateComparer: impossible to reach this point! " . 
		"Date comparison not executed.");
	}
	
	public function substract($otherDate) {
		$otherCDate = new CDate($otherDate);
		return $this->date->dateDiff($otherCDate);
	}
	
}

class DateComparisonResult {
	public static $less = "less";
	public static $equal = "equal";
	public static $great = "great";
}
?>