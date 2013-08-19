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
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
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

//Kent - Apparently no longer needed in 2.4
//function aspirelists_get_types() {
//    $readinglist = new object;
//    $readinglist->modclass = MOD_CLASS_RESOURCE;
//    $readinglist->type="aspirelists&amp;type=readinglist";
//    $readinglist->typestr = "Reading list";

//    return array($readinglist);
//}

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

// Curls a url and json decodes the response
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

    $response = curl_exec($ch);

    return $response;
}

//This gets an associative array of categories set out in a manner to be used
//with the mod_form
function aspirelists_getCats($baseurl, &$o, &$level, $shortname, $group) {

    $p = curlSource($baseurl . '.json');
    $p = json_decode($p, true);
    debugging(var_dump(ARRAY('p'=>$p, 'q'=>$q, 'o'=>$o),DEBUG_DEVELOPER));

    if(!empty($p[$baseurl]['http://rdfs.org/sioc/spec/parent_of'])) {

        foreach ($p[$baseurl]['http://rdfs.org/sioc/spec/parent_of'] as $c) {
            $level ++;
            $cn = curlSource($c['value'] . '.json');
            $cn = json_decode($cn, true);
            $o[$group][$group . '/' . substr($c['value'], strrpos($c['value'], '/') + 1)] = str_repeat('--', $level). ' ' .$shortname . ': ' .$cn[$c['value']]['http://rdfs.org/sioc/spec/name'][0]['value'];
            aspirelists_getCats($c['value'], $o, $level, $shortname, $group);
            $level --;
        }
    }
}

function aspirelists_getLists($site, $targetKG, $code, $timep) {
  global $COURSE;

  $config = get_config('aspirelists');
  $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
  // build the target URL of the JSON data we'll be requesting from Aspire
  $url = "$site/$targetKG/$code/lists.json";
  // using php curl, we'll now request the JSON data from Aspire
  $data = curlSource($url);

  if ($data) // if we get a valid response from curl...
  {

    $data = json_decode($data, true);

    if(isset($data["$site/$targetKG/$code"]) && isset($data["$site/$targetKG/$code"]['http://purl.org/vocab/resourcelist/schema#usesList'])) // if there are any lists...
    {
      foreach ($data["$site/$targetKG/$code"]['http://purl.org/vocab/resourcelist/schema#usesList'] as $usesList) // for each list this module uses...
      {

        $tp = strrev($data[$usesList['value']]['http://lists.talis.com/schema/temp#hasTimePeriod'][0]['value']);

        //$timep = get_config('aspirelists', 'modTimePeriod');

        if($tp[0] === $timep) {

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
      }
      uasort($lists,'aspirelists_sortByName');
    }
  } else {
      //If we had no response from the CURL request, then set a suitable message.
    return "<p>Could not communicate with reading list system for $COURSE->fullname.  Please check again later.</p>";
  }

  $output = '';

  if(!empty($lists)){

      
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
  } else { return null; }

  return $output;

}

// Given a page or category url this will get all of the resources from it and its children
// And print links out in a list
// warning: this is experimental for demo purposes and may pone talis and the server your
// running from
function aspirelists_getResources($baseurl) {

  $data = curlSource($baseurl . '.json');

  echo '<ul>';
        if(!empty($data[$baseurl]['http://rdfs.org/sioc/spec/container_of'])) {
            foreach($data[$baseurl]['http://rdfs.org/sioc/spec/container_of'] as $r) {
                $resurl = $r['value'];
                $rdata = curlSource($resurl . '.json');

                if(!empty($rdata[$resurl]['http://purl.org/vocab/resourcelist/schema#resource'])){

                    $tempurl = $rdata[$resurl]['http://purl.org/vocab/resourcelist/schema#resource'][0]['value'];

                    $rdets = curlSource($tempurl . '.json');

                    if(!empty($rdets[$tempurl]['http://purl.org/dc/terms/title'])) {
                        echo '<li><a href="' . $resurl . '">' . $rdets[$tempurl]['http://purl.org/dc/terms/title'][0]['value'] . '</a></li>';
                    }
                }
            }
        }

        if(!empty($data[$baseurl]['http://rdfs.org/sioc/spec/parent_of'])) {

          foreach ($data[$baseurl]['http://rdfs.org/sioc/spec/parent_of'] as $c) {
            $caturl = $c['value'];
            $cn = curlSource($c['value'] . '.json');
            echo '<li>';
            echo $cn[$caturl]['http://rdfs.org/sioc/spec/name'][0]['value'];

            aspirelists_getResources($caturl);

            echo '</li>';
          }

        }

    echo '</ul>';
}


/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return object info
 */
function aspirelists_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;

    //Get the resource
    if (!$aspireresource = $DB->get_record('aspirelists', array('id'=>$coursemodule->instance),
            'id, category, name, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();

    if($coursemodule->showdescription == 1) {
      $info->content = format_module_intro(get_string('modulename', 'aspirelists'), $aspireresource, $coursemodule->id, false);
    }
    //If we are not showing all categories then set the link to direct to a new tab.
    if($aspireresource->category != 'all'){
        $fullurl = "$CFG->wwwroot/mod/aspirelists/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->onclick = "window.open('$fullurl'); return false;";
    }



    return $info;
}