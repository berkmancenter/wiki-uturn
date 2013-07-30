# UTurn: The Documentation

*Note: This file assumes familiarity with both the functionality of UTurn and the README.*

## High Level Overview

UTurn affects 3 major things: pages, files, and users. 

### Pages

For pages, UTurn iterates over every page, and for every page it iterates over every revision (in reverse chronological order). When UTurn finds a revision that is before the date it proceeds to take the text of that revision and create a new version of the page that has that text. (The logic behind creating a new revision rather than deleting the old ones is that it permits a UTurn to be UTurned.)

Should UTurn reach the end of a page's revisions without finding one before the UTurn date, it will either make the page blank (allowing for the UTurn to be UTurned) OR delete the page (if the user selected that checkbox at the time of invoking the UTurn).

### Files

Files are associated with pages in the file namespace. Should UTurn encounter a file, it attempts to revert it to a previous version if it can find it. Should UTurn find no revision before the UTurn date, it deletes the file.

*Note: See the "Taking it Further" section below on how to actually delete the file files hosted on the server.*

### Users

If the admin calling UTurn selects the delete users options, then users will be iterated through. Any user that was created before the date will be banned from the wiki. (As this will also prevent re-registration.)

### Whitelisting

The Admin is also given the option to whitelist namespaces, users, and pages (including file pages). 

If UTurn finds a namespace, a page, a revision made by a whitelisted user, or a user on the whitelist, UTurn will simply skip that item and continue onto the next iteration.

## File Specifics

 * *ext.uturn.js*: Javascript for UTurn page that submits AJAX request to perform the actual UTurn.
 * *SpecialUTurn.php*: Main File; contains all UTurn code.
 * *UTurn.alias.php*: Aliases name of special page for other languages.
 * *UTurn.i18n.php*: Internationalization.
 * *UTurn.php*: Metadata and includes all the other files.
 * *ext.uturn.css*:  Stylizes the admin page.

## Anatomy of a UTurn 

When the admin requests the Special:UTurn page, the page checks for passed arguments:

### If the necessary arguments to do a UTurn are NOT passed:

The form to invoke a UTurn will be returned. 

When the user submits the form, the submission will be cancelled by JS, and an alternate ajax submission will be started. While the UTurn is completing, all the form items will be disabled.

### If the necessary arguments to do a UTurn are passed:

UTurn will attempt to parse the arguments; should the arguments be valid (i.e. not a time in the future), the UTurn will begin.

Most of UTurn works within a very large while(true) loop, and within that loop the internal api is queried for a new page. When the api retunrns no new pages, the while(true) loop is broken out of. 

For each page, revisions are iterated over much the same way as the pages were (with a while(true) loop that breaks when there are no more revisions.

On completion of the UTurn, the form to invoke a UTurn is returned as it is an ajax request and will not be shown to the user.


