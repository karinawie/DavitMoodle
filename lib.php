<?php

/** @global int $davit_COLUMN_HEIGHT */
global $davit_COLUMN_HEIGHT;
$davit_COLUMN_HEIGHT = 300;

/** @global int $davit_COLUMN_WIDTH */
global $davit_COLUMN_WIDTH;
$davit_COLUMN_WIDTH = 300;
global $ROL;

define('DAVIT_PUBLISH_ANONYMOUS', '0');
define('DAVIT_PUBLISH_NAMES',     '1');

define('DAVIT_SHOWRESULTS_NOT',          '0');
define('DAVIT_SHOWRESULTS_AFTER_ANSWER', '1');
define('DAVIT_SHOWRESULTS_AFTER_CLOSE',  '2');
define('DAVIT_SHOWRESULTS_ALWAYS',       '3');
define('DAVIT_TEACHER', 3);

define('DAVIT_CORRECTED_ACTIVITY', 99);

define('DAVIT_INSERT_ACTIVITY', 1);
define('DAVIT_UPDATE_ACTIVITY', 2);

define('DAVIT_DISPLAY_HORIZONTAL',  '0');
define('DAVIT_DISPLAY_VERTICAL',    '1');

/** @global array $DAVIT_PUBLISH */
global $DAVIT_PUBLISH;
$DAVIT_PUBLISH = array (DAVIT_PUBLISH_ANONYMOUS  => get_string('publishanonymous', 'davit'),
                         DAVIT_PUBLISH_NAMES     => get_string('publishnames', 'davit'));

/** @global array $DAVIT_SHOWRESULTS */
global $DAVIT_SHOWRESULTS;
$DAVIT_SHOWRESULTS = array (DAVIT_SHOWRESULTS_NOT       => get_string('publishnot', 'davit'),
                         DAVIT_SHOWRESULTS_AFTER_ANSWER => get_string('publishafteranswer', 'davit'),
                         DAVIT_SHOWRESULTS_AFTER_CLOSE  => get_string('publishafterclose', 'davit'),
                         DAVIT_SHOWRESULTS_ALWAYS       => get_string('publishalways', 'davit'));

/** @global array $DAVIT_DISPLAY */
global $DAVIT_DISPLAY;
$DAVIT_DISPLAY = array (DAVIT_DISPLAY_HORIZONTAL   => get_string('displayhorizontal', 'davit'),
                         DAVIT_DISPLAY_VERTICAL    => get_string('displayvertical','davit'));

function davit_add_instance($davit) {
    global $DB;

    $davit->timemodified = time();

    if (empty($davit->timerestrict)) {
        $davit->timeopen = 0;
        $davit->timeclose = 0;
    }

    //insert answers
    $davit->id = $DB->insert_record("davit", $davit);
    return $davit->id;
}
function davit_update_instance($davit) {
    global $DB;

    $davit->id = $davit->instance;
    $davit->timemodified = time();


    if (empty($davit->timerestrict)) {
        $davit->timeopen = 0;
        $davit->timeclose = 0;
    }

    //update, delete or insert answers
    foreach ($davit->option as $key => $value) {
        $value = trim($value);
        $option = new stdClass();
        $option->text = $value;
        $option->davitid = $davit->id;
        if (isset($davit->limit[$key])) {
            $option->maxanswers = $davit->limit[$key];
        }
        $option->timemodified = time();
        if (isset($davit->optionid[$key]) && !empty($davit->optionid[$key])){//existing davit record
            $option->id=$davit->optionid[$key];
        } 
    }
    return $DB->update_record('davit', $davit);
}
function davit_prepare_options($davit, $user, $coursemodule, $allresponses) {
    global $DB;

    $cdisplay = array('options'=>array());

    $cdisplay['limitanswers'] = true;
    $context = context_module::instance($coursemodule->id);

    foreach ($davit->option as $optionid => $text) {
        if (isset($text)) { 
            $option = new stdClass;
            $option->attributes = new stdClass;
            $option->attributes->value = $optionid;
            $option->text = format_string($text);
            $option->maxanswers = $davit->maxanswers[$optionid];
            $option->displaylayout = $davit->display;

            if (isset($allresponses[$optionid])) {
                $option->countanswers = count($allresponses[$optionid]);
            } else {
                $option->countanswers = 0;
            }
            if ( $davit->limitanswers && ($option->countanswers >= $option->maxanswers) && empty($option->attributes->checked)) {
                $option->attributes->disabled = true;
            }
            $cdisplay['options'][] = $option;
        }
    }

    $cdisplay['hascapability'] = is_enrolled($context, NULL, 'mod/davit:choose'); //only enrolled users are allowed to make a davit

    return $cdisplay;
}
function davit_user_submit_response($formanswer, $davit, $userid, $course, $cm) {
    global $DB, $CFG;
    require_once($CFG->libdir.'/completionlib.php');

    $context = context_module::instance($cm->id);

    $countanswers=0;
    if($davit->limitanswers) {
        if (groups_get_activity_groupmode($cm) > 0) {
            $currentgroup = groups_get_activity_group($cm);
        } else {
            $currentgroup = 0;
        }
        if ($answers) {
            foreach ($answers as $a) { 
                if (is_enrolled($context, $a->userid, 'mod/davit:choose')) {
                    $countanswers++;
                }
            }
        }
        $maxans = $davit->maxanswers[$formanswer];
    }
    if (!($davit->limitanswers && ($countanswers >= $maxans) )) {
        if ($current) {

            $newanswer = $current;
            $newanswer->optionid = $formanswer;
            $newanswer->timemodified = time();
            $eventdata = array();
            $eventdata['context'] = $context;
            $eventdata['objectid'] = $newanswer->id;
            $eventdata['userid'] = $userid;
            $eventdata['courseid'] = $course->id;
            $eventdata['other'] = array();
            $eventdata['other']['davitid'] = $davit->id;
            $eventdata['other']['optionid'] = $formanswer;

            $event = \mod_davit\event\answer_updated::create($eventdata);
            $event->add_record_snapshot('course', $course);
            $event->add_record_snapshot('course_modules', $cm);
            $event->trigger();
        } else {
            $newanswer = new stdClass();
            $newanswer->davitid = $davit->id;
            $newanswer->userid = $userid;
            $newanswer->optionid = $formanswer;
            $newanswer->timemodified = time();

            // Update completion state
            $completion = new completion_info($course);
            if ($completion->is_enabled($cm) && $davit->completionsubmit) {
                $completion->update_state($cm, COMPLETION_COMPLETE);
            }

            $eventdata = array();
            $eventdata['context'] = $context;
            $eventdata['objectid'] = $newanswer->id;
            $eventdata['userid'] = $userid;
            $eventdata['courseid'] = $course->id;
            $eventdata['other'] = array();
            $eventdata['other']['davitid'] = $davit->id;
            $eventdata['other']['optionid'] = $formanswer;

            $event = \mod_davit\event\answer_submitted::create($eventdata);
            $event->add_record_snapshot('course', $course);
            $event->add_record_snapshot('course_modules', $cm);
            $event->trigger();
        }
    } else {
        if (!($current->optionid==$formanswer)) { //check to see if current davit already selected - if not display error
            print_error('davitfull', 'davit');
        }
    }
}
function davit_show_reportlink($user, $cm) {
    $responsecount =0;
    foreach($user as $optionid => $userlist) {
        if ($optionid) {
            $responsecount += count($userlist);
        }
    }
    echo '<div class="reportlink">';
    echo "<a href=\"report.php?id=$cm->id\">".get_string("viewallresponses", "davit", $responsecount)."</a>";
    echo '</div>';
}
function prepare_davit_show_results($davit, $course, $cm, $allresponses, $forcepublish=false) {
    global $CFG, $davit_COLUMN_HEIGHT, $FULLSCRIPT, $PAGE, $OUTPUT, $DB;

    $display = clone($davit);
    $display->coursemoduleid = $cm->id;
    $display->courseid = $course->id;

    //overwrite options value;
    $display->options = array();
    $totaluser = 0;
    foreach ($davit->option as $optionid => $optiontext) {
        $display->options[$optionid] = new stdClass;
        $display->options[$optionid]->text = $optiontext;
        $display->options[$optionid]->maxanswer = $davit->maxanswers[$optionid];

        if (array_key_exists($optionid, $allresponses)) {
            $display->options[$optionid]->user = $allresponses[$optionid];
            $totaluser += count($allresponses[$optionid]);
        }
    }
    unset($display->option);
    unset($display->maxanswers);

    $display->numberofuser = $totaluser;
    $context = context_module::instance($cm->id);
    $display->viewresponsecapability = has_capability('mod/davit:readresponses', $context);
    $display->deleterepsonsecapability = has_capability('mod/davit:deleteresponses',$context);
    $display->fullnamecapability = has_capability('moodle/site:viewfullnames', $context);

    if (empty($allresponses)) {
        echo $OUTPUT->heading(get_string("nousersyet"), 3, null);
        return false;
    }


    $totalresponsecount = 0;
    foreach ($allresponses as $optionid => $userlist) {
        if ($davit->showunanswered || $optionid) {
            $totalresponsecount += count($userlist);
        }
    }

    $hascapfullnames = has_capability('moodle/site:viewfullnames', $context);

    $viewresponses = has_capability('mod/davit:readresponses', $context);
    switch ($forcepublish) {
        case DAVIT_PUBLISH_NAMES:
            echo '<div id="tablecontainer">';
            if ($viewresponses) {
                echo '<form id="attemptsform" method="post" action="'.$FULLSCRIPT.'" onsubmit="var menu = document.getElementById(\'menuaction\'); return (menu.options[menu.selectedIndex].value == \'delete\' ? \''.addslashes_js(get_string('deleteattemptcheck','quiz')).'\' : true);">';
                echo '<div>';
                echo '<input type="hidden" name="id" value="'.$cm->id.'" />';
                echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
                echo '<input type="hidden" name="mode" value="overview" />';
            }

            echo "<table cellpadding=\"5\" cellspacing=\"10\" class=\"results names\">";
            echo "<tr>";

            $columncount = array(); // number of votes in each column
            if ($davit->showunanswered) {
                $columncount[0] = 0;
                echo "<th class=\"col0 header\" scope=\"col\">";
                print_string('notanswered', 'davit');
                echo "</th>";
            }
            $count = 1;
            foreach ($davit->option as $optionid => $optiontext) {
                $columncount[$optionid] = 0; // init counters
                echo "<th class=\"col$count header\" scope=\"col\">";
                echo format_string($optiontext);
                echo "</th>";
                $count++;
            }
            echo "</tr><tr>";

            if ($davit->showunanswered) {
                echo "<td class=\"col$count data\" >";
                echo "<table class=\"davitresponse\"><tr><td></td></tr>";
                if (!empty($allresponses[0])) {
                    foreach ($allresponses[0] as $user) {
                        echo "<tr>";
                        echo "<td class=\"picture\">";
                        echo $OUTPUT->user_picture($user, array('courseid'=>$course->id));
                        echo "</td><td class=\"fullname\">";
                        echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user->id&amp;course=$course->id\">";
                        echo fullname($user, $hascapfullnames);
                        echo "</a>";
                        echo "</td></tr>";
                    }
                }
                echo "</table></td>";
            }
            $count = 1;
            foreach ($davit->option as $optionid => $optiontext) {
                    echo '<td class="col'.$count.' data" >';
                    echo '<table class="davitresponse"><tr><td></td></tr>';
                    if (isset($allresponses[$optionid])) {
                        foreach ($allresponses[$optionid] as $user) {
                            $columncount[$optionid] += 1;
                            echo '<tr><td class="attemptcell">';
                            if ($viewresponses and has_capability('mod/davit:deleteresponses',$context)) {
                                echo '<input type="checkbox" name="attemptid[]" value="'. $user->id. '" />';
                            }
                            echo '</td><td class="picture">';
                            echo $OUTPUT->user_picture($user, array('courseid'=>$course->id));
                            echo '</td><td class="fullname">';
                            echo "<a href=\"$CFG->wwwroot/user/view.php?id=$user->id&amp;course=$course->id\">";
                            echo fullname($user, $hascapfullnames);
                            echo '</a>';
                            echo '</td></tr>';
                       }
                    }
                    $count++;
                    echo '</table></td>';
            }
            echo "</tr><tr>";
            $count = 1;

            if ($davit->showunanswered) {
                echo "<td></td>";
            }

            foreach ($davit->option as $optionid => $optiontext) {
                echo "<td align=\"center\" class=\"col$count count\">";
                if ($davit->limitanswers) {
                    echo get_string("taken", "davit").":";
                    echo $columncount[$optionid];
                    echo "<br/>";
                    echo get_string("limit", "davit").":";
                    echo $davit->maxanswers[$optionid];
                } else {
                    if (isset($columncount[$optionid])) {
                        echo $columncount[$optionid];
                    }
                }
                echo "</td>";
                $count++;
            }
            echo "</tr>";
            if ($viewresponses and has_capability('mod/davit:deleteresponses',$context)) {
                echo '<tr><td></td><td>';
                echo '<a href="javascript:select_all_in(\'DIV\',null,\'tablecontainer\');">'.get_string('selectall').'</a> / ';
                echo '<a href="javascript:deselect_all_in(\'DIV\',null,\'tablecontainer\');">'.get_string('deselectall').'</a> ';
                echo '&nbsp;&nbsp;';
                echo html_writer::label(get_string('withselected', 'davit'), 'menuaction');
                echo html_writer::select(array('delete' => get_string('delete')), 'action', '', array(''=>get_string('withselectedusers')), array('id'=>'menuaction', 'class' => 'autosubmit'));
                $PAGE->requires->yui_module('moodle-core-formautosubmit',
                    'M.core.init_formautosubmit',
                    array(array('selectid' => 'menuaction'))
                );
                echo '<noscript id="noscriptmenuaction" style="display:inline">';
                echo '<div>';
                echo '<input type="submit" value="'.get_string('go').'" /></div></noscript>';
                echo '</td><td></td></tr>';
            }

            echo "</table></div>";
            if ($viewresponses) {
                echo "</form></div>";
            }
            break;
    }
    return $display;
}
function davit_delete_responses($attemptids, $davit, $cm, $course) {
    global $DB, $CFG;
    require_once($CFG->libdir.'/completionlib.php');

    if(!is_array($attemptids) || empty($attemptids)) {
        return false;
    }
    foreach($attemptids as $num => $attemptid) {
        if(empty($attemptid)) {
            unset($attemptids[$num]);
        }
    }
    $completion = new completion_info($course);
    return true;
}
function davit_delete_instance($id) {
    global $DB;

    if (! $davit = $DB->get_record("davit", array("id"=>"$id"))) {
        return false;
    }
    $result = true;
    if (! $DB->delete_records("davit", array("id"=>"$davit->id"))) {
        $result = false;
    }
    return $result;
}
function davit_get_view_actions() {
    return array('view','view all','report');
}
function davit_get_post_actions() {
    return array('choose','choose again');
}
function davit_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'davitheader', get_string('modulenameplural', 'davit'));
    $mform->addElement('advcheckbox', 'reset_davit', get_string('removeresponses','davit'));
}
function davit_reset_course_form_defaults($course) {
    return array('reset_davit'=>1);
}
function davit_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'davit');
    $status = array();

    if (!empty($data->reset_davit)) {
        $davitssql = "SELECT ch.id FROM {davit} ch WHERE ch.course=?";
        $status[] = array('component'=>$componentstr, 'item'=>get_string('removeresponses', 'davit'), 'error'=>false);
    }

    /// updating dates - shift may be negative too
    if ($data->timeshift) {
        shift_course_mod_dates('davit', array('timeopen', 'timeclose'), $data->timeshift, $data->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
    }
    return $status;
}
function davit_get_response_data($davit, $cm, $groupmode) {
    global $CFG, $USER, $DB;

    $context = context_module::instance($cm->id);

/// Get the current group
    if ($groupmode > 0) {
        $currentgroup = groups_get_activity_group($cm);
    } else {
        $currentgroup = 0;
    }

/// Initialise the returned array, which is a matrix:  $allresponses[responseid][userid] = responseobject
    $allresponses = array();

/// First get all the users who have access here
/// To start with we assume they are all "unanswered" then move them later
    $allresponses[0] = get_enrolled_users($context, 'mod/davit:choose', $currentgroup, user_picture::fields('u', array('idnumber')));

/// Use the responses to move users into the correct column

    if ($rawresponses) {
        foreach ($rawresponses as $response) {
            if (isset($allresponses[0][$response->userid])) {   // This person is enrolled and in correct group
                $allresponses[0][$response->userid]->timemodified = $response->timemodified;
                $allresponses[$response->optionid][$response->userid] = clone($allresponses[0][$response->userid]);
                unset($allresponses[0][$response->userid]);   // Remove from unanswered column
            }
        }
    }
    return $allresponses;
}
function davit_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}
function davit_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        default: return null;
    }
}
function davit_extend_settings_navigation(settings_navigation $settings, navigation_node $davitnode) {
    global $PAGE;

    if (has_capability('mod/davit:readresponses', $PAGE->cm->context)) {

        $groupmode = groups_get_activity_groupmode($PAGE->cm);
        if ($groupmode) {
            groups_get_activity_group($PAGE->cm, true);
        }
        // We only actually need the davit id here
        $davit = new stdClass;
        $davit->id = $PAGE->cm->instance;
        $allresponses = davit_get_response_data($davit, $PAGE->cm, $groupmode);   // Big function, approx 6 SQL calls per user

        $responsecount =0;
        foreach($allresponses as $optionid => $userlist) {
            if ($optionid) {
                $responsecount += count($userlist);
            }
        }
        $davitnode->add(get_string("viewallresponses", "davit", $responsecount), new moodle_url('/mod/davit/report.php', array('id'=>$PAGE->cm->id)));
    }
}
function davit_get_completion_state($course, $cm, $userid, $type) {
    global $CFG,$DB;

    // Get davit details
    $davit = $DB->get_record('davit', array('id'=>$cm->instance), '*',
            MUST_EXIST);
}
function davit_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-davit-*'=>get_string('page-mod-davit-x', 'davit'));
    return $module_pagetype;
}
function davit_activity_student($idCourse){
    global $DB;
    $retorno = $DB->get_records_sql("select * from mdl_davit_activity_student daa left join mdl_user u on daa.userid = u.id where daa.id_course_modules = $1", array('id_course_modules' => $idCourse ));
    $table = new html_table();
    $table->head=array('Nivel','Nome','Corrigido','');
    $data = array();
    if(count($retorno)<1){
        $data[]=array("Não foi encontrada nenhuma atividade!","","","");
    }
    foreach ($retorno as $row) {
        $ar[0]=$row->level;
        $ar[2]=$row->firstname.' '.$row->lastname;
        $ar[3]=$row->corrected == 'N'?'Não':'Sim';
        $ar[4]="<a href='view.php?id={$idCourse}&userid={$row->userid}&actionAT=".(DAVIT_CORRECTED_ACTIVITY)."'>Ver</a>";      
        $data[] = $ar;
    }
    $table->data = $data;
    return $table;     
}
function davit_options_dificuldade($level=null){
    $niveis = array('Escolha'=>"",
                    'Fácil'=>"setupFacil();",
                    'Médio'=>"setupMedio();",
                    'Difícil'=>"setupDificil();");
    $option ="";
    foreach ($niveis as $key => $value) {
        $option .= "<option".(substr_count($value,$level)>=1?' selected':'')." value='{$value}'>{$key}</option>";
    }
    return $option;
}
function is_teacher($context){
    global $ROL;
    if($ROL == null){
        $ROL = array_pop(get_user_roles($context));
    }
    if($ROL->roleid == DAVIT_TEACHER){
        return TRUE;
    }else{
        return FALSE;
    }
        
}
function get_activity_by_user($idCourse,$userid){
    global $DB;
    return $DB->get_record('davit_activity_student',array('userid'=>$userid,'id_course_modules'=>$idCourse));
}
function davit_get_user_by_id($id){
    global $DB;
    return $DB->get_record('user', array('id'=>$id));
}
function davit_update_activity_student($activity){
    global $DB;
    $sql = "update mdl_davit_activity_student set text=$4, level=$3, corrected=$5 where userid=$1 and id_course_modules=$2";
    return $DB->execute($sql,(array)$activity);
}
function davit_insert_activity_student($activity){
    global $DB;
    return $DB->insert_record('davit_activity_student', $activity, false, false);
}