<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * mod_aspirelists aspirelists
 *
 * @package    mod_aspirelists
 * @copyright  2014 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_aspirelists\core;

defined('MOODLE_INTERNAL') || die();

/**
 * mod_aspirelists aspirelists class.
 *
 * @package    mod_aspirelists
 * @copyright  2014 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class aspirelists {

    /**
     * Returns block content
     * 
     * @author God Knows
     * @return [type] [description]
     */
    public static function get_block_content($courseid, $shortname) {
        global $CFG, $DB, $USER;

        static $content;

        if ($content !== null) {
            return $content;
        }

        $sites = array();

        // Get config for the current target.
        $site = get_config('aspirelists', 'targetAspire');
        if ($site) {
            $sites["Canterbury"] = array(
                "url"  => $site,
                "time" => get_config('aspirelists', 'timePeriod')
            );
        }

        // Get config for the alt target.
        $altsite = get_config('aspirelists', 'altTargetAspire');
        if ($altsite) {
            $sites["Medway"] = array(
                "url"  => $altsite,
                "time" => get_config('aspirelists', 'altTimePeriod')
            );
        }

        // Die if we cant do this.
        if (empty($sites)) {
            $content->text = "Talis Aspire base URL not configured. Contact the system administrator.";
            return $content;
        }

        $targetkg = get_config('aspirelists', 'targetKG');
        if (empty($targetkg)) {
            $targetkg = "modules";
        }

        $campus = true;
        $connectcourses = \local_connect\course::get_by('mid', $courseid);
        if (!empty($connectcourses)) {
            foreach ($connectcourses as $connectcourse) {
                $campus = in_array(strtolower($connectcourse->campus_name), $CFG->aspirelist_campus_white_list) ? true : false;
                if (!$campus) {
                    break;
                }
            }
        }

        $content = new \stdClass();
        $content->text = "";
        $content->footer = "";

        if ($shortname && $campus) {
            // Get the code from the global course object, lowercasing it in the process.
            $subject = strtolower($shortname);
            preg_match_all("([a-z]{2,4}[0-9]{3,4})", $subject, $matches);

            $output = '';
            foreach ($matches[0] as $match) {
                $code = trim($match);

                foreach ($sites as $site => $siteconfig) {
                    $output .= '<h3 style="margin-bottom: 2px;">'.$site.'</h3>';
                    $output .= static::curl_list($siteconfig["url"], $siteconfig["time"], $targetkg, $code);
                }
            }

            if ($output == '') {
                if (!has_capability('moodle/course:update', \context_course::instance($courseid))) {
                    $content->text = <<<HTML
                        <p>This Moodle course is not yet linked to the resource lists system.
                        You may be able to find your list through searching the resource lists system,
                        or you can consult your Moodle module or lecturer for further information.</p>
HTML;
                } else {
                    $content->text = <<<HTML
                        <p>If your list is available on the <a href='http://resourcelists.kent.ac.uk'>resource list</a>
                        system and you would like assistance in linking it to Moodle please contact
                        <a href='mailto:readinglisthelp@kent.ac.uk'>Reading List Helpdesk</a>.</p>
HTML;
                }
            } else {
                $content->text = $output;
            }
        }

        return $content;
    }

    /**
     * Curl off aspire lists
     *
     * @author God Knows
     * @param  [type] $site     [description]
     * @param  [type] $timep    [description]
     * @param  [type] $targetkg [description]
     * @param  [type] $code     [description]
     * @return [type]           [description]
     */
    private static function curl_list($site, $timep, $targetkg, $code) {
        $url = "$site/$targetkg/$code/lists.json";

        $cache = \cache::make('mod_aspirelists', 'aspirecache_json');
        $cachecontent = $cache->get($url);
        if ($cachecontent !== false) {
            return $cachecontent;
        }

        $aconfig = get_config('aspirelists');

        $lists = array();

        $ch = curl_init();
        $options = array(
            CURLOPT_URL             => $url,
            CURLOPT_HEADER          => false,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_CONNECTTIMEOUT  => 2000,
            CURLOPT_TIMEOUT         => 4000,
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1
        );
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response && $httpcode === 200) {
            // Decode the returned JSON data.
            $data = json_decode($response, true);
            if ($data === null) {
                // If the JSON decode failed, error out.
                $out = "<p>Could not communicate with reading list system for $code.  Please check again later.</p>";
                $cache->set($url, $out);
                return $out;
            }

            if (isset($data["$site/$targetkg/$code"]) &&
                isset($data["$site/$targetkg/$code"]['http://purl.org/vocab/resourcelist/schema#usesList'])) {
                foreach ($data["$site/$targetkg/$code"]['http://purl.org/vocab/resourcelist/schema#usesList'] as $useslist) {
                    $tp = strrev($data[$useslist["value"]]['http://lists.talis.com/schema/temp#hasTimePeriod'][0]['value']);
                    if (strpos($tp, strrev($timep)) === 0) {
                        $list = array();
                        $list["url"] = clean_param($useslist["value"], PARAM_URL);
                        $list["name"] = clean_param($data[$list["url"]]['http://rdfs.org/sioc/spec/name'][0]['value'], PARAM_TEXT);

                        // Let's try and get a last updated date.
                        if (isset($data[$list["url"]]['http://purl.org/vocab/resourcelist/schema#lastUpdated'])) {
                            // And extract the date in a friendly, human readable format...
                            $param = $data[$list["url"]]['http://purl.org/vocab/resourcelist/schema#lastUpdated'][0]['value'];
                            $time = strtotime(clean_param($param, PARAM_TEXT));
                            $list['lastUpdatedDate'] = date('l j F Y', $time);
                        }

                        // Now let's count the number of items.
                        $itemcount = 0;
                        if (isset($data[$list["url"]]['http://purl.org/vocab/resourcelist/schema#contains'])) {
                            foreach ($data[$list["url"]]['http://purl.org/vocab/resourcelist/schema#contains'] as $things) {
                                if (preg_match('/\/items\//', clean_param($things['value'], PARAM_URL))) {
                                    $itemcount++;
                                }
                            }
                        }
                        $list['count'] = $itemcount;
                        $lists[$list["url"]] = $list;
                    }
                }

                // Sort the list.
                usort($lists, function($a, $b) {
                    return strcmp($a["name"], $b["name"]);
                });
            }
        } else {
            // If we had no response from the CURL request, then set a suitable message.
            $out = "<p>Could not communicate with reading list system for $code.  Please check again later.</p>";
            $cache->set($url, $out);
            return $out;
        }

        $output = '';

        if (!empty($lists)) {
            foreach ($lists as $list) {
                // Get a friendly, human readable noun for the items.
                $itemnoun = ($list['count'] == 1) ? "item" : "items";

                // Finally, we're ready to output information to the browser.
                $output .= "<p><a href='".$list['url']."'>".$list['name']."</a>";

                if ($list['count'] > 0) {
                    $output .= " (" . $list['count'] . " $itemnoun)";
                }

                if (isset($list["lastUpdatedDate"])) {
                    $output .= ', last updated ' . static::contextual_time(strtotime($list["lastUpdatedDate"]));
                }

                $output .= "</p>\n";
            }
        } else {
            $cache->set($url, "");
            return null;
        }

        $cache->set($url, $output);

        return $output;
    }

    /**
     * Convert timestamp to contextual time
     * 
     * @author God Knows
     * @param  [type]  $smallts [description]
     * @param  boolean $largets [description]
     * @return [type]            [description]
     */
    private static function contextual_time($smallts, $largets = false) {
        if (!$largets) {
            $largets = time();
        }

        $n = $largets - $smallts;
        if ($n <= 1) {
            return 'less than 1 second ago';
        }

        if ($n < (60)) {
            return $n . ' seconds ago';
        }

        if ($n < (60 * 60)) {
            $minutes = round($n / 60);
            return 'about ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        }

        if ($n < (60 * 60 * 16)) {
            $hours = round($n / (60 * 60));
            return 'about ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        }

        if ($n < (time() - strtotime('yesterday'))) {
            return 'yesterday';
        }

        if ($n < (60 * 60 * 24)) {
            $hours = round($n / (60 * 60));
            return 'about ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        }

        if ($n < (60 * 60 * 24 * 6.5)) {
            return 'about ' . round($n / (60 * 60 * 24)) . ' days ago';
        }

        if ($n < (time() - strtotime('last week'))) {
            return 'last week';
        }

        if (round($n / (60 * 60 * 24 * 7)) == 1) {
            return 'about a week ago';
        }

        if ($n < (60 * 60 * 24 * 7 * 3.5)) {
            return 'about ' . round($n / (60 * 60 * 24 * 7)) . ' weeks ago';
        }

        if ($n < (time() - strtotime('last month'))) {
            return 'last month';
        }

        if (round($n / (60 * 60 * 24 * 7 * 4)) == 1) {
            return 'about a month ago';
        }

        if ($n < (60 * 60 * 24 * 7 * 4 * 11.5)) {
            return 'about ' . round($n / (60 * 60 * 24 * 7 * 4)) . ' months ago';
        }

        if ($n < (time() - strtotime('last year'))) {
            return 'last year';
        }

        if (round($n / (60 * 60 * 24 * 7 * 52)) == 1) {
            return 'about a year ago';
        }

        if ($n >= (60 * 60 * 24 * 7 * 4 * 12)) {
            return 'about ' . round($n / (60 * 60 * 24 * 7 * 52)) . ' years ago';
        }

        return false;
    }
}