<?php 

/*
 * UTurn 
 * v1.2
 * 
 * Tomas Reimers
 * 
 * Setup file for plugin.
 */

if ( ! defined( 'MEDIAWIKI' ) ) {
    die( 'Not an entry point.' );
}

$wgExtensionCredits['specialpage'][] = array(
    'path'           => __FILE__,
    'name'           => 'UTurn',
    'descriptionmsg' => 'uturn-desc',
    'author'         => 'Tomas Reimers',
    'url'            => 'https://github.com/berkmancenter/wiki-uturn',
    'version'        => 1.2,
);

$dir = dirname( __FILE__ ) . '/';

$wgAutoloadClasses['SpecialUTurn'] = $dir . 'SpecialUTurn.php'; 
$wgExtensionMessagesFiles['UTurn'] = $dir . 'UTurn.i18n.php'; 
$wgExtensionMessagesFiles['UTurnAlias'] = $dir . 'UTurn.alias.php'; 
$wgAutoloadClasses['SpecialUTurn'] = $dir . 'SpecialUTurn.php';
$wgSpecialPages['UTurn'] = 'SpecialUTurn'; 

$wgSpecialPageGroups['UTurn'] = 'pagetools';

$wgGroupPermissions['sysop']['uturn'] = true;
$wgAvailableRights[] = 'uturn';

$wgResourceModules['ext.uturn'] = array(
    'localBasePath' => dirname( __FILE__ ),
    'remoteExtPath' => 'UTurn',
    'scripts' => array(
        'ext.uturn.js'
    ),
    'styles' => array(
        'ext.uturn.css'
    ),
    'messages' => array()
);
