<?php 
class DateComparer {
	private $date;
	private $comparison;
	
	public function __construct($initialDate) {
		$this->date = new CDate($initialDate);
		$this->comparison = new CDate();
	}
	
	/**
	 * Compare the date hold by the instance with another
	 * take in by parameter
	 * @param string $otherDate string representation of a date
	 * @return DateComparisonResult
	 */
	public function compare($otherDate) {
		$res = $this->comparison->compare(
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
}

class DateComparisonResult {
	public static $less = "less";
	public static $equal = "equal";
	public static $great = "great";
}
?>