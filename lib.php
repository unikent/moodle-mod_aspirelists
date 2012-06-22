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
 *                              lib file                              *
 *                                                                    *
 **********************************************************************/

defined('MOODLE_INTERNAL') || die;

function aspirelists_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

function aspirelists_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

function aspirelists_reset_userdata($data) {
    return array();
}

function aspirelists_get_view_actions() {
    return array('view','view all');
}

function aspirelists_get_post_actions() {
    return array('update', 'add');
}

function aspirelists_update_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");
    $data->timemodified = time();
    $data->id           = $data->instance;

    // This will preven the resource being added if the item is empty
    // aspirelists_check_reading_lists();

    $DB->update_record('aspirelists', $data);
    return true;
}

function aspirelists_add_instance($data, $mform) {
	global $CFG, $DB;

    // This will preven the resource being added if the item is empty
    // aspirelists_check_reading_lists();
    
    $data->id = $DB->insert_record('aspirelists', $data);

    return $data->id;
}

function aspirelists_delete_instance($id) {
    global $DB;

    if (!$readinglist = $DB->get_record('aspirelists', array('id'=>$id))) {
        return false;
    }

    // note: all context files are deleted automatically

    $DB->delete_records('aspirelists', array('id'=>$resource->id));

    return true;
}

function aspirelists_get_types() {
    $readinglist = new object;
    $readinglist->modclass = MOD_CLASS_RESOURCE;
    $readinglist->type="aspirelists&amp;type=readinglist";
    $readinglist->typestr = "Reading list";

    return array($readinglist);
}

function aspirelists_check_reading_lists() {
    global $DB, $CFG, $COURSE;

    $config = get_config('aspirelists');

    $shortname_full = explode(' ', $COURSE->shortname);
    $shortnames = explode('/', strtolower($shortname_full[0]));

    $lists = 0;

    foreach($shortnames as $shortname) {

      $url = "$config->baseurl/$config->group/$shortname/lists.json"; // build the target URL of the JSON data we'll be requesting from Aspire

      // using php curl, we'll now request the JSON data from Aspire
      $ch = curl_init();
      $options = array(
              CURLOPT_URL            => $url, // tell curl the URL
              CURLOPT_HEADER         => false,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CONNECTTIMEOUT => $config->timeout,
              CURLOPT_TIMEOUT => $config->timeout,
              CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1
      );
      curl_setopt_array($ch, $options);

      $response = curl_exec($ch); // execute the request and get a response

      if($response) {
        $data = json_decode($response,true); // decode the returned JSON data
        if(!empty($data)) {
          $lists ++;
        }
      }
    }
    if($lists === 0) {
        throw new Exception(get_string('error:nolist', 'aspirelists'));
      }
}

function curlSource($url) {

    $config = get_config('aspirelists');

    $ch = curl_init();
    $options = array(
          CURLOPT_URL            => $url, // tell curl the URL
          CURLOPT_HEADER         => false,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_CONNECTTIMEOUT => $config->timeout,
          CURLOPT_TIMEOUT => $config->timeout,
          CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1
    );
    curl_setopt_array($ch, $options);

    return json_decode(curl_exec($ch), true); // execute the request and get a response
}


function getCats($baseurl, &$o, &$level, $shortname) {

    $p = curlSource($baseurl . '.json');

    if(!empty($p[$baseurl]['http://rdfs.org/sioc/spec/parent_of'])) {

        foreach ($p[$baseurl]['http://rdfs.org/sioc/spec/parent_of'] as $c) {
            $level ++;
            $cn = curlSource($c['value'] . '.json');
            $o[substr($c['value'], strrpos($c['value'], '/') + 1)] = str_repeat('--', $level). ' ' .$shortname . ': ' .$cn[$c['value']]['http://rdfs.org/sioc/spec/name'][0]['value'];
            getCats($c['value'], $o, $level, $shortname);
            $level --;
        }
    }
}