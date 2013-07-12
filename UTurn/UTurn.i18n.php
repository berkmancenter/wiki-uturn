<?php 

/*
 * UTurn 
 * v1.2
 * 
 * Tomas Reimers
 * 
 * Internationalization file.
 */

$messages = array();

/*
 * English
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
    'uturn-whitelist-users-desc' => 'A list of users whose edits to skip, delimited by a "|" (Pipe symbol).',
    'uturn-whitelist-admins' => 'Automatically whitelist admins.'
);

/*
 * Message Documentation
 */

$messages['qqq'] = array(
    'uturn' => 'The title.',
    'uturn-desc' => 'The description of the plugin.',
    'uturn-date-key' => 'The prompt asking when to UTurn to.',
    'uturn-date-desc' => 'A description of the above prompt specifying that time should be inputted in the form "MM/DD/YYYY HH:MM:SS".',
    'uturn-user-key' => 'A prompt asking whether to delete users as well.',
    'uturn-user-desc' => 'A description of what deleting users entails.',
    'uturn-delete-key' => 'A prompt asking whether to delete pages as well.',
    'uturn-delete-desc' => 'A description of the above.',
    'uturn-submit' => 'The submit button for the UTurn form.',
    'uturn-whitelist-namespaces-key' => 'The prompt for which namespaces to whitelist.',
    'uturn-whitelist-namespaces-desc' => 'A description of the above, and reminds users to delimit with a pipe symbol.',
    'uturn-whitelist-pages-key' => 'The prompt for which pages to whitelist.',
    'uturn-whitelist-pages-desc' => 'A description of the above, and reminds users to delimit with a pipe symbol.',
    'uturn-whitelist-users-key' => 'The prompt for which users to whitelist.',
    'uturn-whitelist-users-desc' => 'A description of the above, and reminds users to delimit with a pipe symbol.',
    'uturn-whitelist-admins' => 'The prompt whether to automatically whitelist admins.'
);