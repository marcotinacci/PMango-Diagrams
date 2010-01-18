<?php
/**
 * Questa classe accede alla LibDB già presente e recupera le informazioni
 * riguardanti il progetto corrente, rendedole disponibili tramite metodi accessori.
 *
 * @author: Francesco Calabri
 * @version: 0.1
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */

class Project{
	
	private $project_info;

	/**
	 * Questo metdodo è da eseguirsi prima di poter accedere alle info.
	 * E' necessario in quanto carica le informazioni che preleva dal DB,
	 * prendendo l'id del progetto dalla sessione.
	 */
	public function loadProjectInfo(){
		$project_id = defVal(@$_REQUEST['project_id'], 0);
		$sql = "SELECT project_name, project_short_name, project_color_identifier FROM projects WHERE project_id = '$project_id'";
		$proj = db_loadHash($sql, $projects);
		$this->project_info =  $projects;
	}
	
	public function getProjectName(){
		return $this->project_info[0];
	}
	
	public function getProjectShortName(){
		return $this->project_info[1];
	}
	
	public function getProjectColor(){
		return $this->project_info[2];
	}
	
	
}
?>