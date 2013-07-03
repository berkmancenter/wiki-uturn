<?php 

/*
 * UTurn 
 * v0.3
 * 
 * Tomas Reimers
 * 
 * Internationalization file, currently only contains english.
 */

$messages = array();

/*
 * English (English)
 */
$messages['en'] = array(
    'uturn' => 'UTurn',
    'uturn-desc' => 'Gives sysops the ability to return the wiki to a specific point in time.',
    'uturn-date-key' => 'When do you want to UTurn to?',
    'uturn-date-desc' => 'Please have the date in the format "MM/DD/YYYY HH:MM:SS".',
    'uturn-user-key' => 'Delete users as well?',
    'uturn-user-desc' => 'This will ban all users registered after the point you are UTurning to.',
    'uturn-delete-key' => 'Delete pages?',
    'uturn-delete-desc' => 'When UTurn finds a page that didn\'t exist before the point you are UTurning to, should it change it to be blank (default) or delete it?',
    'uturn-submit' => 'UTurn.',
    'uturn-whitelist-namespaces-key' => 'Whitelist Namespaces:',
    'uturn-whitelist-namespaces-desc' => 'A list of namespaces to skip, delimited by a "|" (Pipe symbol).',
    'uturn-whitelist-pages-key' => 'Whitelist Pages:',
    'uturn-whitelist-pages-desc' => 'A list of pages to skip, delimited by a "|" (Pipe symbol).',
    'uturn-whitelist-users-key' => 'Whitelist Edits Made By Users:',
    'uturn-whitelist-users-desc' => 'A list of users whose edits to skip, delimited by a "|" (Pipe symbol).'
);