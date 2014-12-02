<?php

/**
 * Definition of log events
 *
 * NOTE: this is an example how to insert log event during installation/update.
 * It is not really essential to know about it, but these logs were created as example
 * in the previous 1.9 NEWMODULE.
 *
 * @package    mod_davit
 * @copyright  @copyright  2014 Karina Wiechork <karinawiechork@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$logs = array(
    array('module'=>'davit', 'action'=>'view', 'mtable'=>'davit', 'field'=>'name'),
    array('module'=>'davit', 'action'=>'update', 'mtable'=>'davit', 'field'=>'name'),
    array('module'=>'davit', 'action'=>'add', 'mtable'=>'davit', 'field'=>'name'),
    array('module'=>'davit', 'action'=>'report', 'mtable'=>'davit', 'field'=>'name')
	
);
