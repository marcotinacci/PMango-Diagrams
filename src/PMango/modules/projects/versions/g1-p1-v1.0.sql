# Backup of project 1 version 1.0
# Generated on 29 October 2009, 15:09:09
# OS: Linux
# PHP version: 5.2.6-1+lenny3
# MySQL version: 5.0.51a-24+lenny2


INSERT INTO `projects` ( `project_group`, `project_name`, `project_short_name`, `project_url`, `project_start_date`, `project_finish_date`, `project_today`, `project_status`, `project_effort`, `project_color_identifier`, `project_description`, `project_target_budget`, `project_hard_budget`, `project_creator`, `project_active`, `project_current`, `project_priority`, `project_type` )
 VALUES ('1','Elbonian MegaZot Ver. 1.0','MegaZot','','2009-10-14 00:00:00','2009-12-31 23:59:00','2009-10-16 00:00:00','2','160','FFFFFF','','5000.00','5600.00','2','0','g1p1v1.0','0','2');


DROP TABLE IF EXISTS `Tg1p1v5`;
CREATE TABLE `Tg1p1v5` (
`old` int(11) NOT NULL default '0',
`new` int(11) NOT NULL default '0',
PRIMARY KEY  (`old`,`new`)
) TYPE=MyISAM;
INSERT INTO `tasks` ( `task_name`, `task_parent`, `task_milestone`, `task_project`, `task_wbs_index`, `task_start_date`, `task_today`, `task_finish_date`, `task_status`, `task_priority`, `task_description`, `task_related_url`, `task_creator`, `task_order`, `task_access`, `task_custom`, `task_type`, `task_model` )
 SELECT 'Requirement Guessing','1','0',MAX(project_id),'1','2009-10-15 09:00:00','2009-10-14 00:00:00','2009-10-19 17:00:00','0','1','prova task','','2','0','0','','2','0'FROM `projects` WHERE project_current = 'g1p1v1.0';
INSERT INTO `Tg1p1v5` (old,new)
SELECT 1, MAX(task_id)
FROM `tasks`
 WHERE task_project = (SELECT MAX(project_id) FROM projects WHERE project_current = 'g1p1v1.0');
INSERT INTO `tasks` ( `task_name`, `task_parent`, `task_milestone`, `task_project`, `task_wbs_index`, `task_start_date`, `task_today`, `task_finish_date`, `task_status`, `task_priority`, `task_description`, `task_related_url`, `task_creator`, `task_order`, `task_access`, `task_custom`, `task_type`, `task_model` )
 SELECT 'Astrological prediction','1','0',MAX(project_id),'1','2009-10-16 09:00:00','2009-10-16 00:00:00','2009-10-16 17:00:00','0','0','','','2','0','0','','0','0'FROM `projects` WHERE project_current = 'g1p1v1.0';
INSERT INTO `Tg1p1v5` (old,new)
SELECT 4, MAX(task_id)
FROM `tasks`
 WHERE task_project = (SELECT MAX(project_id) FROM projects WHERE project_current = 'g1p1v1.0');
INSERT INTO `tasks` ( `task_name`, `task_parent`, `task_milestone`, `task_project`, `task_wbs_index`, `task_start_date`, `task_today`, `task_finish_date`, `task_status`, `task_priority`, `task_description`, `task_related_url`, `task_creator`, `task_order`, `task_access`, `task_custom`, `task_type`, `task_model` )
 SELECT 'Ching revision','1','0',MAX(project_id),'2','2009-10-19 09:00:00','2009-10-16 00:00:00','2009-10-19 12:00:00','0','0','','','2','0','0','','0','0'FROM `projects` WHERE project_current = 'g1p1v1.0';
INSERT INTO `Tg1p1v5` (old,new)
SELECT 5, MAX(task_id)
FROM `tasks`
 WHERE task_project = (SELECT MAX(project_id) FROM projects WHERE project_current = 'g1p1v1.0');
INSERT INTO `tasks` ( `task_name`, `task_parent`, `task_milestone`, `task_project`, `task_wbs_index`, `task_start_date`, `task_today`, `task_finish_date`, `task_status`, `task_priority`, `task_description`, `task_related_url`, `task_creator`, `task_order`, `task_access`, `task_custom`, `task_type`, `task_model` )
 SELECT 'Flip coin check','1','0',MAX(project_id),'3','2009-10-19 12:00:00','2009-10-16 00:00:00','2009-10-19 14:00:00','0','0','','','2','0','0','','0','0'FROM `projects` WHERE project_current = 'g1p1v1.0';
INSERT INTO `Tg1p1v5` (old,new)
SELECT 6, MAX(task_id)
FROM `tasks`
 WHERE task_project = (SELECT MAX(project_id) FROM projects WHERE project_current = 'g1p1v1.0');
INSERT INTO `tasks` ( `task_name`, `task_parent`, `task_milestone`, `task_project`, `task_wbs_index`, `task_start_date`, `task_today`, `task_finish_date`, `task_status`, `task_priority`, `task_description`, `task_related_url`, `task_creator`, `task_order`, `task_access`, `task_custom`, `task_type`, `task_model` )
 SELECT 'Idzuut\'i propitial dance','1','0',MAX(project_id),'4','2009-10-19 15:00:00','2009-10-16 00:00:00','2009-10-19 17:00:00','0','0','','','2','0','0','','0','0'FROM `projects` WHERE project_current = 'g1p1v1.0';
INSERT INTO `Tg1p1v5` (old,new)
SELECT 7, MAX(task_id)
FROM `tasks`
 WHERE task_project = (SELECT MAX(project_id) FROM projects WHERE project_current = 'g1p1v1.0');
INSERT INTO `tasks` ( `task_name`, `task_parent`, `task_milestone`, `task_project`, `task_wbs_index`, `task_start_date`, `task_today`, `task_finish_date`, `task_status`, `task_priority`, `task_description`, `task_related_url`, `task_creator`, `task_order`, `task_access`, `task_custom`, `task_type`, `task_model` )
 SELECT 'Release','1','1',MAX(project_id),'5','2009-10-19 16:00:00','2009-10-27 00:00:00','2009-10-19 17:00:00','0','0','','','2','0','0','','0','0'FROM `projects` WHERE project_current = 'g1p1v1.0';
INSERT INTO `Tg1p1v5` (old,new)
SELECT 133, MAX(task_id)
FROM `tasks`
 WHERE task_project = (SELECT MAX(project_id) FROM projects WHERE project_current = 'g1p1v1.0');
UPDATE `tasks` SET `task_parent` = (SELECT new FROM `Tg1p1v5` WHERE old =1) WHERE task_id = (SELECT new FROM `Tg1p1v5` WHERE old =1);
UPDATE `tasks` SET `task_parent` = (SELECT new FROM `Tg1p1v5` WHERE old =1) WHERE task_id = (SELECT new FROM `Tg1p1v5` WHERE old =4);
UPDATE `tasks` SET `task_parent` = (SELECT new FROM `Tg1p1v5` WHERE old =1) WHERE task_id = (SELECT new FROM `Tg1p1v5` WHERE old =5);
UPDATE `tasks` SET `task_parent` = (SELECT new FROM `Tg1p1v5` WHERE old =1) WHERE task_id = (SELECT new FROM `Tg1p1v5` WHERE old =6);
UPDATE `tasks` SET `task_parent` = (SELECT new FROM `Tg1p1v5` WHERE old =1) WHERE task_id = (SELECT new FROM `Tg1p1v5` WHERE old =7);
UPDATE `tasks` SET `task_parent` = (SELECT new FROM `Tg1p1v5` WHERE old =1) WHERE task_id = (SELECT new FROM `Tg1p1v5` WHERE old =133);


INSERT INTO `user_projects` ( `user_id`, `proles_id`, `project_id` )
 SELECT '5','7',MAX(project_id)FROM `projects` WHERE project_current = 'g1p1v1.0';
INSERT INTO `user_projects` ( `user_id`, `proles_id`, `project_id` )
 SELECT '4','7',MAX(project_id)FROM `projects` WHERE project_current = 'g1p1v1.0';
INSERT INTO `user_projects` ( `user_id`, `proles_id`, `project_id` )
 SELECT '3','0',MAX(project_id)FROM `projects` WHERE project_current = 'g1p1v1.0';
INSERT INTO `user_projects` ( `user_id`, `proles_id`, `project_id` )
 SELECT '6','0',MAX(project_id)FROM `projects` WHERE project_current = 'g1p1v1.0';
INSERT INTO `user_projects` ( `user_id`, `proles_id`, `project_id` )
 SELECT '4','2',MAX(project_id)FROM `projects` WHERE project_current = 'g1p1v1.0';
INSERT INTO `user_projects` ( `user_id`, `proles_id`, `project_id` )
 SELECT '2','1',MAX(project_id)FROM `projects` WHERE project_current = 'g1p1v1.0';


INSERT INTO `models` ( `model_association`, `model_type`, `model_delivery_day`, `model_pt` )
 SELECT '1','2','2009-10-15 00:00:00',MAX(project_id)FROM `projects` WHERE project_current = 'g1p1v1.0';


INSERT INTO `task_log` ( `task_log_task`, `task_log_name`, `task_log_description`, `task_log_creator`, `task_log_hours`, `task_log_creation_date`, `task_log_edit_date`, `task_log_start_date`, `task_log_finish_date`, `task_log_proles_id`, `task_log_progress`, `task_log_problem` )
 SELECT new,'prova task','fatto prova','4','0','2009-10-16 09:45:06','2009-10-16 09:45:06','2009-10-16 09:45:00','2009-10-16 09:45:00','7','100','0'FROM `Tg1p1v5` WHERE old = 1;


INSERT INTO `task_dependencies` ( `dependencies_task_id`, `dependencies_task_type_id`, `dependencies_req_task_id` )
 SELECT new,'1','4'FROM `Tg1p1v5` WHERE old = 5;
UPDATE `task_dependencies` SET `dependencies_req_task_id` = (SELECT new FROM `Tg1p1v5` WHERE old =4) WHERE dependencies_task_id = (SELECT new FROM `Tg1p1v5` WHERE old =5) && dependencies_req_task_id = 4;
INSERT INTO `task_dependencies` ( `dependencies_task_id`, `dependencies_task_type_id`, `dependencies_req_task_id` )
 SELECT new,'1','5'FROM `Tg1p1v5` WHERE old = 6;
UPDATE `task_dependencies` SET `dependencies_req_task_id` = (SELECT new FROM `Tg1p1v5` WHERE old =5) WHERE dependencies_task_id = (SELECT new FROM `Tg1p1v5` WHERE old =6) && dependencies_req_task_id = 5;
INSERT INTO `task_dependencies` ( `dependencies_task_id`, `dependencies_task_type_id`, `dependencies_req_task_id` )
 SELECT new,'1','6'FROM `Tg1p1v5` WHERE old = 7;
UPDATE `task_dependencies` SET `dependencies_req_task_id` = (SELECT new FROM `Tg1p1v5` WHERE old =6) WHERE dependencies_task_id = (SELECT new FROM `Tg1p1v5` WHERE old =7) && dependencies_req_task_id = 6;


INSERT INTO `user_tasks` ( `user_id`, `proles_id`, `task_id`, `effort`, `perc_effort`, `user_task_priority` )
 SELECT '5','7',new,'4','50','0'FROM `Tg1p1v5` WHERE old = 1;
INSERT INTO `user_tasks` ( `user_id`, `proles_id`, `task_id`, `effort`, `perc_effort`, `user_task_priority` )
 SELECT '4','2',new,'10','25','0'FROM `Tg1p1v5` WHERE old = 1;
INSERT INTO `user_tasks` ( `user_id`, `proles_id`, `task_id`, `effort`, `perc_effort`, `user_task_priority` )
 SELECT '4','2',new,'4','50','0'FROM `Tg1p1v5` WHERE old = 4;
INSERT INTO `user_tasks` ( `user_id`, `proles_id`, `task_id`, `effort`, `perc_effort`, `user_task_priority` )
 SELECT '4','2',new,'4','133','0'FROM `Tg1p1v5` WHERE old = 5;
INSERT INTO `user_tasks` ( `user_id`, `proles_id`, `task_id`, `effort`, `perc_effort`, `user_task_priority` )
 SELECT '5','7',new,'2','200','0'FROM `Tg1p1v5` WHERE old = 6;
INSERT INTO `user_tasks` ( `user_id`, `proles_id`, `task_id`, `effort`, `perc_effort`, `user_task_priority` )
 SELECT '4','2',new,'2','100','0'FROM `Tg1p1v5` WHERE old = 7;
INSERT INTO `user_tasks` ( `user_id`, `proles_id`, `task_id`, `effort`, `perc_effort`, `user_task_priority` )
 SELECT '5','7',new,'2','100','0'FROM `Tg1p1v5` WHERE old = 7;




INSERT INTO `models` ( `model_pt`, `model_association`, `model_type`, `model_delivery_day` )
 SELECT new,'2','1',''FROM `Tg1p1v5` WHERE old = 1;



DROP TABLE IF EXISTS `Tg1p1v5`;