# UTurn 

## Version

0.1

## About

There are a handful of situations when a MediaWiki install has to be locked for some period of time. Unfortunately, many times people forget to lock their wikis; they forget to close registration; they forget to keep moderating. An example might be a course website during the summer: once final exams come around, the professor forgets to close their wiki's registration, or moderate it. Then when they return to it at the start of a new semseter, they realize that spam bots have infested it and filled the pages with links and spam.

Then, they realize that they should of blocked registration, or done something. But by then it is too late. By then the spam is there, and now they are trying to figure out what to do.

*Enter UTurn.*

**UTurn is a MediaWiki extension that allows sysops to return a wiki to how it was at a specified point in time.**

## Installation

Like any other extension, drop the UTurn folder in /extensions; then include the follow line in your LocalSettings.php

    require_once( "$IP/extensions/UTurn/UTurn.php" );

## Usage

Once installed, simply visit [http://localhost/mediawiki/index.php/Special:UTurn] while logged in as a sysop to UTurn the wiki.

## Details

UTurning is very hard to undo, please contemplate deeply about whether you need to UTurn to a specific date. 

Basically, UTurn works by listing through all the pages in the wiki. For every page it checks if the page existed before the date; if it didn't, it is deleted; if it did, the page is updated to the content of the last revision prior to the date you are reverting to.

## Design Decisions

*No Lock*

At the moment, there is no lock on UTurning (i.e. two sysops could hypothetically UTurn at the same time to different points). This was done because UTurn is built for small-medium sized wikis. In those cases, typically there is one sysop, or a couple of them with very good communication. It simply doesn't seem likely that the page would be reverted to two different times by different sysops, at the same time.

However, it is on the Todo to add a lock.

*Deleting Pages*

UTurn deletes pages which weren't created before the date. The reason being that many spammers and bots create pages with spammy titles. To combat this, I simply delete all those pages. *NOTE: This means you can NOT UTurn a UTurn, to undo that first UTurn because a UTurn does not undelete pages.*

## Todo

 * Log errors
 * Define better timelimit in SpecialUTurn.php
 * Undelete pages (possibly by looping through delete log)
 * Prevent simultaneous UTurns:
 ** On UTurn start, create page in MediaWiki namespace.
 ** on end, delete it. 
 ** Do not start another UTurn if the page exists.

## Contributors

 * Tomas Reimers

## Copyright and License

(C) President and Fellows of Harvard College, 2012

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.