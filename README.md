# UTurn 

## Version

1.0

## About

There are a handful of situations when a MediaWiki install has to be locked for some period of time. Unfortunately, many times people forget to lock their wikis; they forget to close registration; they forget to keep moderating. An example might be a course website during the summer: once final exams come around, the professor forgets to close their wiki's registration, or moderate it. Then when they return to it at the start of a new semester, they realize that spam bots have infested it and filled the pages with links and spam.

Then, they realize that they should of blocked registration, or done something. But by then it is too late. By then the spam is there, and now they are trying to figure out what to do.

**UTurn is a MediaWiki extension that allows sysops to return a wiki to how it was at a specified point in time.**

UTurn is a standard MediaWiki extension written in PHP with a tiny bit of javascript for the front end.

## Installation

Like any other extension, drop the UTurn folder in /extensions; then include the follow line in your LocalSettings.php

    require_once( "$IP/extensions/UTurn/UTurn.php" );

## Usage

Once installed, simply visit `myWiki/Special:UTurn` while logged in as a sysop to UTurn the wiki.

## Maintenance Scripts of Interest

 * [*Cleanup Titles*](http://www.mediawiki.org/wiki/Manual:CleanupTitles.php): If you are having trouble deleting pages with strange titles (UTF-8 sequences), as described here: www.gossamer-threads.com/lists/wiki/mediawiki/274451, it is likely that the titles need to be cleaned up.

 * [*Reducing the Size of the Database*](http://www.mediawiki.org/wiki/Manual:Reduce_size_of_the_database): Removing/archiving deleted pages from the database and can shrink it significantly. 

## Lookback 

[https://github.com/tomasreimers/wiki-lookback](https://github.com/tomasreimers/wiki-lookback)

Lookback is a [MediaWiki Gadget](http://www.mediawiki.org/wiki/Extension:Gadgets) that allows you to view how a wiki was at a previous point in time (without UTurning it). 

Lookback is useful to preview a wiki before you decide to UTurn it.

## Details

UTurn works by listing through all the pages in the wiki and creating a new revision that matches the text of the page at the time you are UTurning to.

*For more details see the inline comments.*

## Files

 * *ext.uturn.js*: Javascript for UTurn page that submits AJAX request to perform the actual UTurn.
 * *SpecialUTurn.php*: Main File; contains all UTurn code.
 * *UTurn.alias.php*: Aliases name of special page for other languages.
 * *UTurn.i18n.php*: Internationalization.
 * *UTurn.php*: Metadata and includes all the other files.
 * *ext.uturn.css*:  Stylizes the admin page.

## Changelog 

1.1

 * Whitelists added

1.0

 * Passes pages by ID rather than by title.

0.3

 * Bans users registered after the date
 * Prettied up UI

0.2 

 * Fixes ALL namespaces

## Contributors

 * Tomas Reimers

## Copyright and License

(C) President and Fellows of Harvard College, 2013

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.