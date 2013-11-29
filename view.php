<?php


/**********************************************************************
 *                      @package Kent aspirelists                     *
 *                                                                    *
 *              University of Kent aspirelists resource               *
 *                                                                    *
 *                       Authors: jwk8, fg30, jw26                    *
 *                                                                    *
 *--------------------------------------------------------------------*
 *                                                                    *
 *                              Item view                             *
 *                                                                    *
 **********************************************************************/

require('../../config.php');
require_once("lib.php");
global $CFG, $USER, $DB, $PAGE;

// dont do anything if not logged in
require_login();

$id = optional_param('id', 0, PARAM_INT);        // Course Module ID
$listid = optional_param('list', NAN, PARAM_INT); // streamingvideo id


if (is_integer($id)) {
    if (! $module = $DB->get_record("course_modules", array("id"=>$id))) {
        throw new Exception(get_string('cmunknown','error'));
    }

    if (! $course = $DB->get_record("course", array("id"=>$module->course))) {
        throw new Exception(get_string('invalidcourseid','error', $module->course));
    }

    if (!$readinglist = $DB->get_record('aspirelists', array('id'=>$module->instance), '*', MUST_EXIST)) {
        throw new Exception(get_string('cmunknown','error'));
    }

} else if ($listid) {
    
    if (! $readinglist = $DB->get_record('aspirelists', array('id'=>$listid), '*', MUST_EXIST)) {
        throw new Exception(get_string('cmunknown','error'));
    }

    if (! $module = $DB->get_record("course_modules", array("instance"=>$readinglist->id))) {
        throw new Exception(get_string('cmunknown','error'));
    }

    if (! $course = $DB->get_record('course', array('id'=>$readinglist->course))) {
        throw new Exception(get_string('invalidcourseid','error', $readinglist->course));
    }
} else throw new Exception("A module ID or resource id must be specified");

$config = get_config('aspirelists');

add_to_log($course->id, 'aspirelist', 'view', "view.php?id={$id}", '');

$context = context_course::instance($course->id);
$PAGE->set_context($context);

//Set page params and layout
$PAGE->set_url('/mod/aspirelists/view.php', array('id'=>$id));
$PAGE->set_title(format_string($readinglist->name));
$PAGE->add_body_class('mod_aspirelists');
$PAGE->set_heading(format_string($readinglist->name));
$PAGE->navbar->add($course->shortname,"{$CFG->wwwroot}/course/view.php?id=$course->id");
$PAGE->navbar->add(get_string('modulename', 'aspirelists'));
$PAGE->set_pagelayout('admin');


$shortname_full = explode(' ', $course->shortname);
$shortnames = explode('/', strtolower($shortname_full[0]));

$output = '';
$lists = array();

//Check to see if a specific category has been picked
if($readinglist->category != 'all') {

    $category = explode('/', $readinglist->category);
    
    switch ($category[0]) {
      case 'medway':
        $base_url = $config->altBaseurl;
        break;
      case 'canterbury':
      default:
        $base_url = $config->baseurl;
        break;
    }

    $url = $base_url . '/sections/' . $category[1];
    
    debugging(var_export(ARRAY('call'=>'view-not-all','category'=>$category,'url'=>$url), TRUE),DEBUG_DEVELOPER);
    
    if(isset($CFG->aspirelists_resourcelist) && $CFG->aspirelists_resourcelist === true) {
        aspirelists_getResources($url);
    } else {
        redirect($url . '.html') ;
    }
    

} else { // if not then display reading lists for any short codes given

    echo $OUTPUT->header();
    echo $OUTPUT->heading("$readinglist->name", 2, 'aspirelists_main', '');
    
    foreach($shortnames as $shortname){

        $m = aspirelists_getLists($config->baseurl, $config->group, $shortname,$config->modTimePeriod);
        if(!empty($m)) {$main[] = $m; }

        $a = aspirelists_getLists($config->altBaseurl, $config->group, $shortname,$config->altModTimePeriod);
        if(!empty($a)) {$alt[] = $a; }

        if(!empty($main)) {
            $output .= '<h3 style="margin: 10px 0px;">Canterbury</h3>';
            foreach ($main as $i) {
                $output .= $i;
            }
        }

        if(!empty($alt)) {
            $output .= '<h3 style="margin: 10px 0px; ">Medway</h3>';
            foreach ($alt as $i) {
                $output .= $i;
            }
        }
    }


    if ($output == '') {
        echo aspirelists_resource_not_ready($context);
        
    } else {
        $output .= '<link rel="stylesheet" href="styles/fontello.css">';
        echo $output;
    }

    echo $OUTPUT->footer();
    if (isset($redirectpage) && $redirectpage != false){
        redirect($redirectpage);
    }

}

function aspirelists_resource_not_ready($context){
    global $USER;
    $output = "";
    $role = get_user_roles($context, $USER->id);

    if(isset($role[1]) && ($role[1]->shortname == 'student' || $role[1]->shortname == 'sds_student')) {
        $output .= get_string('error:studentnolist', 'aspirelists');
    } else if (has_capability('moodle/course:update', $context)){
        $output .= get_string('error:staffnolist', 'aspirelists');
    } else {
        $output .= get_string('error:defaultnolist', 'aspirelists');
    }
    
    return $output;
}
