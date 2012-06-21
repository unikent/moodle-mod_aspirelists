<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_page_upgrade($oldversion) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/streamingvideo/db/upgradelib.php");

    $dbman = $DB->get_manager();

    // Moodle v2.1.0 release upgrade line
    // Put any upgrade step following this

    // Moodle v2.2.0 release upgrade line
    // Put any upgrade step following this

    return true;
}