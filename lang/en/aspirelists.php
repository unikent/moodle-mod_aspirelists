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
 *                            en-lang file                            *
 *                                                                    *
 **********************************************************************/

defined('MOODLE_INTERNAL') || die();

/*********************************
 *            General            * 
 *********************************/

$string['modulename'] = 'Reading lists';
$string['modulenameplural'] = 'Reading lists';
$string['newmodulename'] = 'Reading lists';
$string['streamingvideo'] = 'Reading lists';
$string['pluginadministration'] = 'Reading lists administration';
$string['pluginname'] = 'Reading lists';

/*********************************
 *            Settings           * 
 *********************************/

$string['settings:timeout'] = 'Timeout';
$string['settings:configtimeout'] = 'Set timeout value for the request';

$string['settings:baseurl'] = 'Timeout';
$string['settings:configbaseurl'] = 'Target Aspire base URL (e.g. http://lists.broadminsteruniversity.org)';

$string['settings:group'] = 'Target knowledge group';
$string['settings:configgroup'] = 'Choose target knowledge group';

$string['settings:group:courses'] = 'Courses';
$string['settings:group:modules'] = 'Modules';
$string['settings:group:units'] = 'Units';
$string['settings:group:programmes'] = 'Programmes';
$string['settings:group:subjects'] = 'Subjects';

$string['settings:redirect'] = 'Redirect';
$string['settings:configredirect'] = 'Redirect on single reading lists';

/*********************************
 *             Errors            * 
 *********************************/

$string['error:nolist'] = '<p>Sorry, you are unable to add a reading list resource to a course at this time. This error has occured because there does not seem to be a list for this module on the reading list system.</p> <p>If your list is available on the <a href="http://resourcelists.kent.ac.uk">resource list</a> system and you would like assistance in linking it to Moodle please contact <a href="mailto:helpdesk@kent.ac.uk">helpdesk</a>.</p>';

$string['error:studentnolist'] = '<p>Sorry, but the reading list resource is unavailable for this course.  This Moodle course is not yet linked to the resource lists system.  You may be able to find your list through searching the resource lists system, or you can consult your Moodle module or lecturer for further information.</p>';
$string['error:staffnolist'] = '<p>Sorry, but the reading list resource is unavailable for this course.  If your list is available on the <a href="http://resourcelists.kent.ac.uk">resource list</a> system and you would like assistance in linking it to Moodle please contact <a href="mailto:helpdesk@kent.ac.uk">helpdesk</a>.</p>';
$string['error:defaultnolist'] = '<p>Sorry, but the reading list resource is unavailable for this course.  This Moodle course is not yet linked to the resource lists system.  You may be able to find your list through searching the <a href="http://resourcelists.kent.ac.uk">resource lists</a> system, or you can consult your Moodle module or lecturer for further information.<p>';