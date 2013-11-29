<?php
/**
 * /tmp/phptidy-sublime-buffer.php
 *
 * @package default
 */


defined('MOODLE_INTERNAL') || die;


/**
 *
 *
 * @param unknown $feature
 * @return unknown
 */
function aspirelists_supports($feature) {
  switch ($feature) {
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


/**
 *
 *
 * @return unknown
 */
function aspirelists_get_extra_capabilities() {
  return array('moodle/site:accessallgroups');
}


/**
 *
 *
 * @param unknown $data
 * @return unknown
 */
function aspirelists_reset_userdata($data) {
  return array();
}


/**
 *
 *
 * @return unknown
 */
function aspirelists_get_view_actions() {
  return array('view', 'view all');
}


/**
 *
 *
 * @return unknown
 */
function aspirelists_get_post_actions() {
  return array('update', 'add');
}


/**
 *
 *
 * @param unknown $data
 * @param unknown $mform
 * @return unknown
 */
function aspirelists_update_instance($data, $mform) {
  global $DB;
  $data->timemodified = time();
  $data->id           = $data->instance;
  $DB->update_record('aspirelists', $data);
  return true;
}


/**
 *
 *
 * @param unknown $data
 * @param unknown $mform
 * @return unknown
 */
function aspirelists_add_instance($data, $mform) {
  global $DB;
  $data->id = $DB->insert_record('aspirelists', $data);
  return $data->id;
}


/**
 *
 *
 * @param unknown $id
 * @return unknown
 */
function aspirelists_delete_instance($id) {
  global $DB;

  if (!$readinglist = $DB->get_record('aspirelists', array('id' => $id))) {
    return false;
  }

  $DB->delete_records('aspirelists', array('id' => $resource->id));
  return true;
}


/**
 * Curls a url and json decodes the response
 * Caches n seconds of records to optimise requests
 *
 * @param unknown $url
 * @return unknown
 */
function aspirelists_curlSource($url) {

  // MUC - Cache the specific URLS
  $cache = cache::make('mod_aspirelists', 'aspirecache');
  $response = $cache->get($url);
  if ($response !== false) {
    return $response;
  }

  $config = get_config('aspirelists');

  $ch = curl_init();
  $options = array(
    CURLOPT_URL            => $url,
    CURLOPT_HEADER         => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => $config->timeout,
    CURLOPT_TIMEOUT       => $config->timeout,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1
  );
  curl_setopt_array($ch, $options);

  $response = curl_exec($ch);

  $cache->set($url, $response);

  return $response;
}


/**
 * This gets an associative array of categories set out in a manner to be used
 * with the mod_form
 *
 * @param unknown $baseurl
 * @param unknown $o         (reference)
 * @param unknown $level     (reference)
 * @param unknown $shortname
 * @param unknown $group
 */
function aspirelists_getCats($baseurl, &$o, &$level, $shortname, $group) {
  $p = aspirelists_curlSource($baseurl . '.json');
  $p = json_decode($p, true);
  if (!empty($p[$baseurl]['http://rdfs.org/sioc/spec/parent_of'])) {
    foreach ($p[$baseurl]['http://rdfs.org/sioc/spec/parent_of'] as $c) {
      $level++;
      $cn = aspirelists_curlSource($c['value'] . '.json');
      $cn = json_decode($cn, true);
      $o[$group][$group . '/' . substr($c['value'], strrpos($c['value'], '/') + 1)] = str_repeat('--', $level). ' ' .$shortname . ': ' .$cn[$c['value']]['http://rdfs.org/sioc/spec/name'][0]['value'];
      aspirelists_getCats($c['value'], $o, $level, $shortname, $group);
      $level--;
    }
  }
}


/**
 *
 *
 * @param unknown $site
 * @param unknown $targetKG
 * @param unknown $code
 * @param unknown $timep
 * @return unknown
 */
function aspirelists_getLists($site, $targetKG, $code, $timep) {
  global $COURSE;

  $config = get_config('aspirelists');
  $context = context_course::instance($COURSE->id);
  // build the target URL of the JSON data we'll be requesting from Aspire
  $url = "$site/$targetKG/$code/lists.json";
  // using php curl, we'll now request the JSON data from Aspire
  $data = aspirelists_curlSource($url);
  $lists = array();

  if ($data) {
    $data = json_decode($data, true);
    if (isset($data["$site/$targetKG/$code"]) && isset($data["$site/$targetKG/$code"]['http://purl.org/vocab/resourcelist/schema#usesList'])) {
      foreach ($data["$site/$targetKG/$code"]['http://purl.org/vocab/resourcelist/schema#usesList'] as $usesList) {
        $tp = strrev($data[$usesList['value']]['http://lists.talis.com/schema/temp#hasTimePeriod'][0]['value']);
        if ($tp[0] === $timep) {
          $list = array();
          $list["url"] = $usesList["value"]; // extract the list URL
          $list["name"] = $data[$list["url"]]['http://rdfs.org/sioc/spec/name'][0]['value']; // extract the list name

          // let's try and get a last updated date
          if (isset($data[$list["url"]]['http://purl.org/vocab/resourcelist/schema#lastUpdated'])) {
            // ..and extract the date in a friendly, human readable format...
            $ludTime = strtotime($data[$list["url"]]['http://purl.org/vocab/resourcelist/schema#lastUpdated'][0]['value']);
            $list['lastUpdatedDate'] = date('l j F Y', $ludTime);
          }

          // now let's count the number of items
          $itemCount = 0;
          if (isset($data[$list["url"]]['http://purl.org/vocab/resourcelist/schema#contains'])) {
            foreach ($data[$list["url"]]['http://purl.org/vocab/resourcelist/schema#contains'] as $things) {
              if (preg_match('/\/items\//', $things['value'])) {
                $itemCount++;
              }
            }
          }
          $list['count'] = $itemCount;
          $lists[$list["url"]] = $list;
        }
      }

      uasort($lists, function ($a, $b) {
        return strcmp($a["name"], $b["name"]);
      });
    }
  } else {
    //If we had no response from the CURL request, then set a suitable message.
    return "<p>Could not communicate with reading list system for $COURSE->fullname.  Please check again later.</p>";
  }


  $output = '';

  if (!empty($lists)) {


    $output .= '<ul class="list_item_inset">';

    foreach ($lists as $list) {
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
      if ($list['count'] > 0) {
        $output .= '<span class="list_item_count">';
        $output .= $list['count'] . ' ' .  $itemNoun;
        $output .= '</span>';
      }
      $output .= '</a>';

      $output .= '</td>';
      // add update text if we have it
      if (isset($list["lastUpdatedDate"])) {
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


/**
 * Given a page or category url this will get all of the resources from it and its children
 * And print links out in a list
 * warning: this is experimental for demo purposes and may pone talis and the server your
 * running from
 *
 * @param unknown $baseurl
 */
function aspirelists_getResources($baseurl) {

  $data = aspirelists_curlSource($baseurl . '.json');

  echo '<ul>';
  if (!empty($data[$baseurl]['http://rdfs.org/sioc/spec/container_of'])) {
    foreach ($data[$baseurl]['http://rdfs.org/sioc/spec/container_of'] as $r) {
      $resurl = $r['value'];
      $rdata = aspirelists_curlSource($resurl . '.json');

      if (!empty($rdata[$resurl]['http://purl.org/vocab/resourcelist/schema#resource'])) {

        $tempurl = $rdata[$resurl]['http://purl.org/vocab/resourcelist/schema#resource'][0]['value'];

        $rdets = aspirelists_curlSource($tempurl . '.json');

        if (!empty($rdets[$tempurl]['http://purl.org/dc/terms/title'])) {
          echo '<li><a href="' . $resurl . '">' . $rdets[$tempurl]['http://purl.org/dc/terms/title'][0]['value'] . '</a></li>';
        }
      }
    }
  }

  if (!empty($data[$baseurl]['http://rdfs.org/sioc/spec/parent_of'])) {

    foreach ($data[$baseurl]['http://rdfs.org/sioc/spec/parent_of'] as $c) {
      $caturl = $c['value'];
      $cn = aspirelists_curlSource($c['value'] . '.json');
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
 * @param object  $coursemodule
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

  if ($coursemodule->showdescription == 1) {
    $info->content = format_module_intro(get_string('modulename', 'aspirelists'), $aspireresource, $coursemodule->id, false);
  }
  //If we are not showing all categories then set the link to direct to a new tab.
  if ($aspireresource->category != 'all') {
    $fullurl = "$CFG->wwwroot/mod/aspirelists/view.php?id=$coursemodule->id&amp;redirect=1";
    $info->onclick = "window.open('$fullurl'); return false;";
  }

  return $info;
}
