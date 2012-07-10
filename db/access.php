<?php  // $Id$
/**
 * Capability definitions for the cla module.
 *
 * For naming conventions, see lib/db/access.php.
 */
$capabilities = array(

    // Ability to manage/publish reading lists
    'mod/talis:pub' => array(
        'riskbitmask' => RISK_MANAGETRUST & RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'guest' => CAP_PREVENT,
            'student' => CAP_PREVENT,
        )
    ),

);