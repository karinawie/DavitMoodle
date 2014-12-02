<?php
/**
 * @package    mod_davit
 * @copyright  2014 Karina Wiechork <karinawiechork@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    
}
require_once ($CFG->dirroot.'/course/moodleform_mod.php');
class mod_davit_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DAVIT_SHOWRESULTS, $DAVIT_PUBLISH, $DAVIT_DISPLAY, $DB;
        $mform  =& $this->_form;
//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('davitname', 'davit'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->add_intro_editor(true, get_string('chatintro', 'chat'));

        $mform->addElement('select', 'display', get_string("displaymode","davit"), $DAVIT_DISPLAY);
//-------------------------------------------------------------------------------
        $mform->addElement('header', 'timerestricthdr', get_string('availability'));
        $mform->addElement('checkbox', 'timerestrict', get_string('timerestrict', 'davit'));

        $mform->addElement('date_time_selector', 'timeopen', get_string("davitopen", "davit"));
        $mform->disabledIf('timeopen', 'timerestrict');

        $mform->addElement('date_time_selector', 'timeclose', get_string("davitclose", "davit"));
        $mform->disabledIf('timeclose', 'timerestrict');

        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        $this->add_action_buttons();
    }
    function data_preprocessing(&$default_values){
        global $DB;
        if (empty($default_values['timeopen'])) {
            $default_values['timerestrict'] = 0;
        } else {
            $default_values['timerestrict'] = 1;
        }
    }
    function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        if (!empty($data->completionunlocked)) {
            if (empty($data->completionsubmit)) {
                $data->completionsubmit = 0;
            }
        }
        return $data;
    }
    function add_completion_rules() {
        $mform =& $this->_form;
        $mform->addElement('checkbox', 'completionsubmit', '', get_string('completionsubmit', 'davit'));
        return array('completionsubmit');
    }
    function completion_rule_enabled($data) {
        return !empty($data['completionsubmit']);
    }
}

