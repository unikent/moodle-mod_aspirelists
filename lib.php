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

    aspirelists_check_reading_lists();

    $DB->update_record('aspirelists', $data);
    return true;
}

function aspirelists_add_instance($data, $mform) {
	global $CFG, $DB;

    aspirelists_check_reading_lists();
    
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

/**
 * processes the updating or addition of a streaming
 * video resource. This will check if a file has been
 * uploaded and push it to the encoding server if it has.
 * If not it will validate and process submitted data into
 * valid format for database entry. 
 *
 * @param object $video the data that came from the form.
 * @param object $form the form object itself
 * @return object $data updated data object
 */
function aspirelists_postprocess($video, $form) {

    global $DB, $CFG;
    //Include the module config
    $config = get_config('aspirelists');

    if(!isset($video->fileaddress)) $video->fileaddress = '';

    $target_file = sanitize($form->get_new_filename('streamingfileupload'), false);
    //Save submitted file if there is one else FALSE
    $source_file = $form->save_temp_file('streamingfileupload');

    // If file has been uploaded
    if($source_file != FALSE) { 

        //check the source file exists
        if(!file_exists($source_file)) {
            throw new Exception('Source file does not exist');
        }

        // check the source file is valid
        

        //Get course details
        $courseid = $video->course;

        $course = $DB->get_record('course', array('id'=>$courseid));

        $shortname = sanitize($course->shortname);

        // get the smb stream object
        require_once('smb.php');

        // setup encoder files and folders
        $enc_share = "smb://{$config->encdomain};{$config->encuser}:{$config->encpass}@{$config->encserver}/{$config->encshare}";
        $target_dir = "{$enc_share}/{$shortname}";

        // if (!is_uploaded_file($source_file))
        // {
        //     throw new Exception($target_dir);
        // }


        // find or make the target directory on the encoding server
        if (!file_exists($target_dir)) {
            $success = mkdir($target_dir);
            if (!$success) {
                debugging("Unable to find or make directory on encoding server. Are the credentials in config.php correct? Have you specified the full location of smbclient executable in smb.php?");
                throw new Exception('Unable to connect to the encoding server. Please try again later.');
            }
        }

        // create source file stream
        $source_handle = fopen($source_file, 'r');
        if (!$source_handle) {
            debugging('Cannot open source file stream: '.$source_file);
            throw new Exception('Cannot open submitted file.');
        }

        // create target file stream
        $target_handle = fopen("{$target_dir}/{$target_file}", 'w');
        if (!$target_handle) {
            debugging('Cannot create handle to target file '.$target_file);
            throw new Exception('Cannot connect to encoding server. Please try again later.');
        }

        // write source to target in packets
        while (!feof($source_handle)) {
            $packet = fread($source_handle, $config->packetsize);
            if (fwrite($target_handle, $packet) === FALSE) {
                debugging('Cannot write to the target file '.$target_file);
                throw new Exception('Cannot write to encoding server. Please try again later.');
            }
        }

        // work out what the filename will be after mp4 encoding
        $encoded_file_name = $target_file;
        $encoded_file_name = substr($encoded_file_name, 0, (strlen ($encoded_file_name)) - (strlen (strrchr($encoded_file_name,'.'))));
        $encoded_file_name = $encoded_file_name . ".mp4";

        // set the streaming file reference
        $video->fileaddress = "{$config->streamingserverbase}{$shortname}/{$encoded_file_name}";

        $video->serveraddress = $config->streamingserverurl;


    } else { // Existing video source used
       
        if (!isset($video->fileaddress)
            || $video->fileaddress == null
            || $video->fileaddress == '') {
            debugging('A video file was not specified');
            throw new Exception('A video file was not specified.');
        }

        if (isset($video->serveraddress)
            && $video->serveraddress != null
            && $video->serveraddress != '') {

            // video file is on a streaming server
            $streaming_server_address = $video->serveraddress;
            if ($streaming_server_address[(strlen($streaming_server_address)-1)] != '/') $streaming_server_address .= '/';
            $video->serveraddress = $streaming_server_address;

            // reformat file for streaming server, if not the file reference will be left as it is
            $streaming_server_file = $video->fileaddress;
            if ($streaming_server_file[0] != '/') $streaming_server_file = '/' . $streaming_server_file;
            $video->fileaddress = $streaming_server_file;
        }
    }

    // set the width
    if (isset($video->width)) {
        $video->width = intval($video->width);
    } else {
        $video->width = $config->width;
    }

    // set the height
    if (isset($video->height)) {
        $video->height = intval($video->height);
    } else {
        $video->height = $config->height;
    }

    // all good, return the data
    return $video;
}