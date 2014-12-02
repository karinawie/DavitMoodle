<?php

/**
 * This file keeps track of upgrades to the davit module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_davit
 * @copyright  2014 Karina Wiechork <karinawiechork@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute davit upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_davit_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager(); 
    
    return true;
}
