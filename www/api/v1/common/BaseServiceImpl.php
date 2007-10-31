<?php

/*
+---------------------------------------------------------------------------+
| Openads v${RELEASE_MAJOR_MINOR}                                           |
| ============                                                              |
|                                                                           |
| Copyright (c) 2003-2007 Openads Limited                                   |
| For contact details, see: http://www.openads.org/                         |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id:$
*/

/**
 * @package    Openads
 * @author     Andriy Petlyovanyy <apetlyovanyy@lohika.com>
 *
 */

// Require the XMLRPC classes
require_once MAX_PATH . '/lib/OA/BaseObjectWithErrors.php';

// Init required files
require_once MAX_PATH . '/lib/max/Admin/Preferences.php';
require_once MAX_PATH . '/lib/max/language/Default.php';
require_once MAX_PATH . '/lib/max/other/lib-io.inc.php';
require_once MAX_PATH . '/lib/max/other/lib-db.inc.php';
require_once MAX_PATH . '/lib/max/other/lib-userlog.inc.php';
require_once MAX_PATH . '/www/admin/lib-gui.inc.php';
require_once MAX_PATH . '/www/admin/lib-permissions.inc.php';
require_once MAX_PATH . '/lib/max/Permission.php';

/**
 * Base Sevice Implementation
 */
class BaseServiceImpl extends  OA_BaseObjectWithErrors
{
    /**
     * Constructor
     *
     */
    function BaseServiceImpl()
    {
        $this->BaseObjectWithErrors();
    }

    /**
     * Session initialisation from sessionId
     *
     * @access private
     * @param string $sessionId
     *
     */
    function _setSessionId($sessionId)
    {
        global $_COOKIE;
        $_COOKIE['sessionID'] = $sessionId;
        $this->preInitSession();
        $this->postInitSession();
    }

    /**
     * Verify Session
     *
     * @param string $sessionId
     * @return boolean
     */
    function verifySession($sessionId)
    {
        if (!$this->_verifySessionLength($sessionId)) {
            return false;
        }

        $this->_setSessionId($sessionId);

        if (phpAds_IsLoggedIn()) {

            return true;
        } else {

            $this->raiseError('Session ID is invalid');
            return false;
        }
    }


    /**
     * Verify Session Length
     *
     * @access private
     * @param string $sessionId
     *
     * @return boolean
     */
    function _verifySessionLength($sessionId)
    {
        if (strlen($sessionId) > 32) {

            $this->raiseError('Session ID greater 32 characters');
            return false;
        } else {

            return true;
        }
    }

    /**
     * Pre Init Session
     *
     * @return boolean
     */
    function preInitSession()
    {
        global $pref;
        $oDbh = OA_DB::singleton();
        if (PEAR::isError($oDbh))
        {
            $this->raiseError("Could not connect to database");
            return false;
        }

        // Load the user preferences from the database
        $pref = MAX_Admin_Preferences::loadPrefs();

        // First thing to do is clear the $session variable to
        // prevent users from pretending to be logged in.
        unset($GLOBALS['session']);

        phpAds_SessionDataFetch();
        return true;
    }

    /**
     * Post init session
     *
     */
    function postInitSession()
    {
        global $session, $pref;
        global $affiliateid, $agencyid, $bannerid, $campaignid, $channelid;
        global $clientid, $day, $trackerid, $userlogid, $zoneid;

        // Overwrite certain preset preferences
        if (!empty($session['language']) && $session['language'] != $GLOBALS['pref']['language']) {
            $GLOBALS['_MAX']['CONF']['max']['language'] = $session['language'];
        }

        if (!phpAds_isUser(phpAds_Admin)) {
            // Reload user preferences from the database because we are using
            // agency/advertiser/publisher settings
            $pref = MAX_Admin_Preferences::loadPrefs(phpAds_getAgencyID());
        }

        // Rewrite column preferences
        $pref = MAX_Admin_Preferences::expandColumnPrefs();

        // Load the required language files
        Language_Default::load();

        // Register variables
        phpAds_registerGlobalUnslashed(
        'affiliateid'
        ,'agencyid'
        ,'bannerid'
        ,'campaignid'
        ,'channelid'
        ,'clientid'
        ,'day'
        ,'trackerid'
        ,'userlogid'
        ,'zoneid'
        );

        if (!isset($affiliateid))   $affiliateid = '';
        if (!isset($agencyid))      $agencyid = phpAds_getAgencyID();
        if (!isset($bannerid))      $bannerid = '';
        if (!isset($campaignid))    $campaignid = '';
        if (!isset($channelid))     $channelid = '';
        if (!isset($clientid))      $clientid = '';
        if (!isset($day))           $day = '';
        if (!isset($trackerid))     $trackerid = '';
        if (!isset($userlogid))     $userlogid = '';
        if (!isset($zoneid))        $zoneid = '';
    }
}


?>