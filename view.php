<?php

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


add_to_log($course->id, 'aspirelist', 'view', "view.php?id={$id}", '');

$context = get_context_instance(CONTEXT_COURSE, $course->id);
$PAGE->set_context($context);

$config = get_config('aspirelists');

//Set page params and layout
$PAGE->set_url('/mod/aspirelists/view.php', array('id'=>$id));
$PAGE->set_title(format_string($readinglist->name));
$PAGE->add_body_class('mod_aspirelists');
$PAGE->set_heading(format_string($readinglist->name));
$PAGE->navbar->add($course->shortname,"{$CFG->wwwroot}/course/view.php?id=$course->id");
$PAGE->navbar->add(get_string('modulename', 'aspirelists'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

echo $OUTPUT->heading("$readinglist->name", 2, 'aspirelists_main', '');

$shortname_full = explode(' ', $course->shortname);
$shortnames = explode('/', strtolower($shortname_full[0]));

$output = '';
$lists = array();

foreach($shortnames as $shortname){

    // get the code from the global course object, lowercasing it in the process

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

    if ($response) // if we get a valid response from curl...
    {
            $data = json_decode($response,true); // decode the returned JSON data
            if(isset($data["$config->baseurl/$config->group/$shortname"]) && isset($data["$config->baseurl/$config->group/$shortname"]['http://purl.org/vocab/resourcelist/schema#usesList'])) // if there are any lists...
            {
                    foreach ($data["$config->baseurl/$config->group/$shortname"]['http://purl.org/vocab/resourcelist/schema#usesList'] as $usesList) // for each list this module uses...
                    {
                            $list = array();
                            $list["url"] = $usesList["value"]; // extract the list URL
                            $list["name"] = $data[$list["url"]]['http://rdfs.org/sioc/spec/name'][0]['value']; // extract the list name

                            // let's try and get a last updated date
                            if (isset($data[$list["url"]]['http://purl.org/vocab/resourcelist/schema#lastUpdated'])) // if there is a last updated date...
                            {
                                    // set up the timezone 
                                    date_default_timezone_set('Europe/London');

                                    // ..and extract the date in a friendly, human readable format...
                                    $list['lastUpdatedDate'] = date('l j F Y',
                                            strtotime($data[$list["url"]]['http://purl.org/vocab/resourcelist/schema#lastUpdated'][0]['value'])); 
                            }

                            // now let's count the number of items
                            $itemCount = 0; 
                            if (isset($data[$list["url"]]['http://purl.org/vocab/resourcelist/schema#contains'])) // if the list contains anything...
                            {
                                    foreach ($data[$list["url"]]['http://purl.org/vocab/resourcelist/schema#contains'] as $things) // loop through the list of things the list contains...
                                    {
                                            if (preg_match('/\/items\//',$things['value'])) // if the thing is an item, incrememt the item count (lists can contain sections, too)
                                            {
                                                    $itemCount++; 
                                            }
                                    }
                            }
                            $list['count'] = $itemCount;
                            //array_push($lists,$list);
                            $lists[$list["url"]] = $list;
                    }
                    uasort($lists,'sortByName');

            }
    } else {
        //If we had no response from the CURL request, then set a suitable message.
        $output = "<p>Could not communicate with reading list system for $COURSE->fullname.  Please check again later.</p>";
    }
}

if(!empty($lists)){

        $output .= '<link rel="stylesheet" href="fontello.css">';
        $output .= '<ul class="list_item_inset">';

    foreach ($lists as $list)
    {
        $itemNoun = ($list['count'] == 1) ? "item" : "items"; // get a friendly, human readable noun for the items
        
        
        // finally, we're ready to output information to the browser#
            $output .= '<li class="list_item">';
                $output .= '<table>';
                    $output .= '<tr>';
                        $output .= '<td  class="list_item_dets">';
                            $output .= '<a href="'.$list['url'].'" target="_blank">';
                                $output .= '<i class="icon-right-circle2"></i>';
                                $output .= '<span class="list_item_link">'.$list['name'].'</span>';
                                
                                // add the item count if there are any...
                                if ($list['count'] > 0) 
                                {
                                    $output .= '<span class="list_item_count">';
                                        $output .= $list['count'] . ' ' .  $itemNoun;
                                    $output .= '</span>';
                                }
                                $output .= '</a>';

                        $output .= '</td>';
                        // add update text if we have it
                        if (isset($list["lastUpdatedDate"]))
                        {
                            $output .= '<td class="list_update">';
                                $output .= '<ul class="list_item_update">';
                                    $output .= '<li class="title">last updated</li>';
                                    $output .= '<li class="month">' . date('F', strtotime($list["lastUpdatedDate"])) . '</li>';
                                    $output .= '<li class="day">' . date('j', strtotime($list['lastUpdatedDate'])) . '</li>';
                                    $output .= '<li class="year">' . date('Y', strtotime($list['lastUpdatedDate'])) . '</li>';
                                $output .= '</ul>';
                            $output .= '</td>';
                        }
                    $output .= '</tr>';
                $output .= '</table>';
            $output .= '</li>';
    }
        $output .= '</ul>';

    if ($output=='') {
        
        $role = get_user_roles($context, $USER->id);
        
        if($role[1]->shortname == 'student' || $role[1]->shortname == 'sds_student') {
            echo "<p>This Moodle course is not yet linked to the resource lists system.  You may be able to find your list through searching the resource lists system, or you can consult your Moodle module or lecturer for further information.</p>";    
        } else  if (has_capability('moodle/course:update', $context)){
            echo "<p>If your list is available on the <a href='http://resourcelists.kent.ac.uk'>resource list</a> system and you would like assistance in linking it to Moodle please contact <a href='mailto:helpdesk@kent.ac.uk'>helpdesk</a>.</p>";
        } else {
            echo "<p>This Moodle course is not yet linked to the resource lists system.  You may be able to find your list through searching the <a href='http://resourcelists.kent.ac.uk'>resource lists</a> system, or you can consult your Moodle module or lecturer for further information.<p>";
        }
            
    } else {
       echo $output;
    }
}

echo $OUTPUT->footer();

function contextualTime($small_ts, $large_ts=false) {
  if(!$large_ts) $large_ts = time();
  $n = $large_ts - $small_ts;
  if($n <= 1) return 'less than 1 second ago';
  if($n < (60)) return $n . ' seconds ago';
  if($n < (60*60)) { $minutes = round($n/60); return 'about ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago'; }
  if($n < (60*60*16)) { $hours = round($n/(60*60)); return 'about ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago'; }
  if($n < (time() - strtotime('yesterday'))) return 'yesterday';
  if($n < (60*60*24)) { $hours = round($n/(60*60)); return 'about ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago'; }
  if($n < (60*60*24*6.5)) return 'about ' . round($n/(60*60*24)) . ' days ago';
  if($n < (time() - strtotime('last week'))) return 'last week';
  if(round($n/(60*60*24*7))  == 1) return 'about a week ago';
  if($n < (60*60*24*7*3.5)) return 'about ' . round($n/(60*60*24*7)) . ' weeks ago';
  if($n < (time() - strtotime('last month'))) return 'last month';
  if(round($n/(60*60*24*7*4))  == 1) return 'about a month ago';
  if($n < (60*60*24*7*4*11.5)) return 'about ' . round($n/(60*60*24*7*4)) . ' months ago';
  if($n < (time() - strtotime('last year'))) return 'last year';
  if(round($n/(60*60*24*7*52)) == 1) return 'about a year ago';
  if($n >= (60*60*24*7*4*12)) return 'about ' . round($n/(60*60*24*7*52)) . ' years ago'; 
  return false;
}


function sortByName($a,$b)
{
    return strcmp($a["name"], $b["name"]);
}