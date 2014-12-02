<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of davit
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_davit
 * @copyright  2014 Karina Wiechork <karinawiechork@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/* visualizado para o aluno quando o mesmo vai fazer a activity */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once (dirname(dirname(dirname(__FILE__))) .'/lib/accesslib.php');
require_once (dirname(dirname(dirname(__FILE__))) . '/auth/shibboleth/auth.php');

global $USER;
$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n = optional_param('n', 0, PARAM_INT);  // davit instance ID - it should be named as the first character of the module
$save = optional_param('save', null, PARAM_TEXT);
$userid = optional_param('userid', null, PARAM_INT);
$action = optional_param('actionAT', null, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('davit', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $davit = $DB->get_record('davit', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $davit = $DB->get_record('davit', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $davit->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('davit', $davit->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}
require_login($course, true, $cm);
$context = context_module::instance($cm->id);

if (!is_null($action)) {
    switch ($action) {
        case DAVIT_INSERT_ACTIVITY:
            $activity = new stdClass();
            $activity->userid = $USER->id;
            $activity->id_course_modules = $id;
            $activity->level = substr(optional_param('level', null, PARAM_TEXT), 0, -3);
            $activity->text = optional_param('programa', null, PARAM_TEXT);//verifica primeiro pelo post
            global $suss;
            $suss = davit_insert_activity_student($activity);
        break;
        case DAVIT_CORRECTED_ACTIVITY:
            if(is_teacher($context)){
                if(optional_param('save', null, PARAM_TEXT)!=null){
                    echo'';
                    $activity = new stdClass();
                    $activity->userid = $userid;
                    $activity->id_course_modules = $id;
                    $activity->level = substr(optional_param('level', null, PARAM_TEXT), 0, -3);
                    $activity->text = optional_param('programa', null, PARAM_TEXT);
                    $activity->corrected = 'S';
                    //mensagem
                    global $suss;
                    $suss = davit_update_activity_student($activity);
                }                
            }            
        break;
        case DAVIT_UPDATE_ACTIVITY:
            $activity->userid = $USER->id;
            $activity->id_course_modules = $id;
            $activity->level = substr(optional_param('level', null, PARAM_TEXT), 0, -3);
            $activity->text = optional_param('programa', null, PARAM_TEXT);
            $activity->corrected = 'N';
            global $suss;
            $suss = davit_update_activity_student($activity);
        break;
        default:
        break;
    }
}

add_to_log($course->id, 'davit', 'view', "view.php?id={$cm->id}", $davit->name, $cm->id);

$PAGE->set_url('/mod/davit/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($davit->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
require_login();

echo $OUTPUT->header();

if ($davit->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('davit', $davit, $cm->id), 'generalbox mod_introbox', 'davitintro');
}

$timenow = time();
$current = false;  // Initialise for later
$davitopen = true;

if (($davit->timeopen < $timenow && $davit->timeclose > $timenow) || is_teacher($context)) {
    $activity;
    if (is_teacher($context) && $action === DAVIT_CORRECTED_ACTIVITY) {
        $activity = get_activity_by_user($id, $userid);
        $usuario = davit_get_user_by_id($userid);
        $activity->actionAT = DAVIT_CORRECTED_ACTIVITY;
    } else {
        $activity = get_activity_by_user($id, $USER->id);
        if ($activity === FALSE) {
            $activity = new stdClass();
            $activity->actionAT = DAVIT_INSERT_ACTIVITY;
        } else {
            $activity->actionAT = DAVIT_UPDATE_ACTIVITY;
        }
    }
    include_once 'davit.php';
    
    if (is_teacher($context)) {
        echo html_writer::table(davit_activity_student($id));
    }
} else { 
    echo $OUTPUT->box(get_string("expired", "davit", userdate($davit->timeclose)), "generalbox expired");
    $davitopen = false;
}
echo $OUTPUT->heading(format_string($davit->name)); 

echo $OUTPUT->footer();
