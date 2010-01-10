<?php 
// require_once 'DataArrayBuilder.php';

/**
 * This class provide the abstraction to able the client to build a associative
 * array with the informations needed by the Task class. This director manage a 
 * DataArrayBuilder to tailor a array in a decoupling fashion
 */
class DataArrayDirector {
	/**
	 * builder to tailor some entry for the final associative array
	 * @var DataArrayBuilder
	 */
	var $_dataArrayBuilder;
	
	/**
	 * constructor, takes a builder to direct
	 * @param DataArrayBuilder $dataArrayBuilder
	 */
	function __construct($dataArrayBuilder) {
		$this->_dataArrayBuilder = $dataArrayBuilder;
	}
	
	/**
	 * this method start the actions to compose a array for Task class
	 * @return void
	 */
	function composeArray() {
		$this->_dataArrayBuilder->buildWBSIdentifier();
		$this->_dataArrayBuilder->buildName();
		$this->_dataArrayBuilder->buildFtsDependencies();
		$this->_dataArrayBuilder->buildPlannedStartDate();
		$this->_dataArrayBuilder->buildPlannedFinishDate();
		$this->_dataArrayBuilder->buildActualStartDate();
		$this->_dataArrayBuilder->buildActualFinishDate();
		$this->_dataArrayBuilder->buildAssignedToTask();
		$this->_dataArrayBuilder->buildPlannedEffort();
		$this->_dataArrayBuilder->buildActualEffort();
		$this->_dataArrayBuilder->buildPlannedCost();
		$this->_dataArrayBuilder->buildActualCost();
		$this->_dataArrayBuilder->buildPercentage();
	}

}
?>