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
$string['modulename_help'] = 'The Reading List activity module enables inclusion of Talis Reading list link integrations within your modules.

There is the choice of either just providing a direct resource link to the entire reading list, or you can select a direct section to link to of the reading list.';
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

$string['settings:baseurl'] = 'Base URL';
$string['settings:configbaseurl'] = 'Target Aspire base URL (e.g. http://lists.broadminsteruniversity.org)';

$string['settings:altbaseurl'] = 'Alt Base URL';
$string['settings:altconfigbaseurl'] = 'Target Aspire base URL (e.g. http://lists.broadminsteruniversity.org)';

$string['settings:group'] = 'Target knowledge group';
$string['settings:configgroup'] = 'Choose target knowledge group';

$string['settings:group:courses'] = 'Courses';
$string['settings:group:modules'] = 'Modules';
$string['settings:group:units'] = 'Units';
$string['settings:group:programmes'] = 'Programmes';
$string['settings:group:subjects'] = 'Subjects';

$string['settings:redirect'] = 'Redirect';
$string['settings:configredirect'] = 'Redirect on single reading lists';

$string['config_timePeriod'] = 'Display time period';
$string['config_timePeriod_desc'] = 'Enter the time period you want to display (1 = 2011/2012, 2 = 2012/2013, etc)';
$string['config_timePeriod_ex'] = '2';

$string['config_altTimePeriod'] = 'Display alternate time period';
$string['config_altTimePeriod_desc'] = 'Enter the alternate time period you want to display (1 = 2012/2013, 2 = 2013/2014, etc)';
$string['config_altTimePeriod_ex'] = '1';

$string['config_cacheDelay'] = 'Cache TTL';
$string['config_cacheDelay_desc'] = 'Enter the normal TTL for cache entries';
$string['config_cacheDelay_ex'] = '60';

$string['config_cacheMaxDelay'] = 'Cache Max TTL';
$string['config_cacheMaxDelay_desc'] = 'Enter the max TTL for cache entries';
$string['config_cacheMaxDelay_ex'] = '600';


/*********************************
 *             Errors            * 
 *********************************/

$string['error:nolist'] = '<p>Sorry, you are unable to add a reading list resource to a course at this time. This error has occured because there does not seem to be a list for this module on the reading list system.</p> <p>If your list is available on the <a href="http://resourcelists.kent.ac.uk">resource list</a> system and you would like assistance in linking it to Moodle please contact <a href="mailto:helpdesk@kent.ac.uk">helpdesk</a>.</p>';

$string['error:studentnolist'] = '<p>Sorry, but the reading list resource is unavailable for this course.  This Moodle course is not yet linked to the resource lists system.  You may be able to find your list through searching the resource lists system, or you can consult your Moodle module or lecturer for further information.</p>';
$string['error:staffnolist'] = '<p>Sorry, but the reading list resource is unavailable for this course.  If your list is available on the <a href="http://resourcelists.kent.ac.uk">resource list</a> system and you would like assistance in linking it to Moodle please contact <a href="mailto:readinglisthelp@kent.ac.uk">Reading List Helpdesk</a>.</p>';
$string['error:defaultnolist'] = '<p>Sorry, but the reading list resource is unavailable for this course.  This Moodle course is not yet linked to the resource lists system.  You may be able to find your list through searching the <a href="http://resourcelists.kent.ac.uk">resource lists</a> system, or you can consult your Moodle module or lecturer for further information.<p>';


// MUC
$string['cachedef_aspirecache'] = 'Caches Aspire Lists';