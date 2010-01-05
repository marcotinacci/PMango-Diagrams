<?php

require_once dirname(__FILE__).'/TaskData.php';
require_once dirname(__FILE__).'/StubTask.php';

/**
 * Classe stub di un taskDataTree contenente dati fittizzi
 */
class StubTaskDataTree {
	private $root;
	
	 public function __construct(){
	
		$A = new TaskData(new StubTask());
		$B = new TaskData(new StubTask());		
		$C = new TaskData(new StubTask());		
		$D = new TaskData(new StubTask());		
		$E = new TaskData(new StubTask());		
		$F = new TaskData(new StubTask());		
		$G = new TaskData(new StubTask());		

		// Dipendenze gerarchiche
		$A->addChild($B); $A->addChild($C); $A->addChild($D);
		$B->addChild($E);
		$C->addChild($F); $C->addChild($G);

		// Dipendenze finish to start
		$C->setFtsDependencies(array($E));
		$D->setFtsDependencies(array($G));
		$D->setFtsDependencies(array($F));
		
		// Nodi collassati
		$B->setCollapsed(true);
	}
	
	/**
	 * Metodo accessore alla struttura
	 * @return TaskData
	 */
	public function getRoot(){
		return $this->root;
	}
	.
	public function setRoot($root){
		$this->root = $root;
	}
	
	public function deepVisit(){
		$res = $this->root->deepVisit();
		return $res;
	}
	
	public function wideVisit(){
		$res = $this->root->wideVisit();
		return $res;
	}
	
	public function getLeaves(){
		$leaves = $this->root->getLeaves();
		return $leaves;
	}
}


?>