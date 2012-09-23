<?php

/*
 * UTurn 
 * v0.1
 * 
 * Tomas Reimers
 *
 * Constructor of the special page; also holds the 
 */

class SpecialUTurn extends SpecialPage {

    /*
     * Creates the instance of the special page class
     */

    function __construct() {
        parent::__construct( 'UTurn' , 'uturn' );
    }

    /*
     * Responds to any page requests, by either
     *     Renders the page
     *     Processing submitted form
     * 
     * string $par - any trailing URL parameters 
     */

    function execute( $par ) {
        global $wgRequest, $wgOut, $wgUser;

        // check permissions 
        if ( !$this->userCanExecute( $wgUser ) ) {
            $this->displayRestrictionError();
            return;
        }

        $req = $this->getRequest();

        // if a request was posted, handle it
        if (
            $req->getVal( 'action' ) == 'submit'
            && $this->getUser()->matchEditToken( $req->getVal('editToken') )
            && $req->wasPosted()
        ) {
            $this->UTurn();
        }

        // respond to all requests with form; even the API calls, because the content of those won't be displayed
        $this->showPage();
    }

    /* 
     * Renders the HTML of the page
     */

    function showPage() {

        $this->setHeaders();

        $out = $this->getOutput();
        $out->addModules( 'ext.uturn' );
        $out->addHTML(
            Xml::openElement(
                'form',
                array(
                    'action' => $this->getTitle()->getLocalURL( 'action=submit' ),
                    'method' => 'post',
                    'id' => 'uturn-form'
                )
            )
            . '<table><tr>'
                . '<td>' . Xml::label( $this->msg('uturn-date')->text(), 'uturn-date' ) . '</td>'
                . '<td>' . Xml::input( 'uturndate', 40, '', array( 'id' => 'uturn-date' ) ) . '</td>'
                . '<td id="uturn-status"></td>'
            . '</tr><tr>'
                . '<td></td>'
                . '<td>' . Xml::submitButton( $this->msg('uturn-submit')->text(), array('id' => 'uturn-submit') ) . '</td>'
                . '<td></td>'
            . '</tr></table>'
            . Xml::closeElement( 'form' )
        );
    }

    /*
     * The actual UTurn function: UTurns the wiki in response to a correct request
     */ 

    function UTurn() {

        $req = $this->getRequest();

        $revertTimestamp = intval( $req->getVal( 't' ) );
        // I may implement an option (in the form of a checkbox) for this later
        $deletePages = true; 
        // unsetting as many vars as possible, because there have been memory errors before
        unset( $req );

        if ( $revertTimestamp > time() ) {
            return "Can't go into the future.";
        }

        $allPages = array();
        $apfrom = '';
        // loop until completion (at which point we will break)
        // I don't define a limit because at this point the total page count is unknown
        while ( true ){

            // each page takes a while, and if your wiki has enough pages it will go over the PHP execution timelimit
            // I arbitrarily chose 30s; it is in the todo to update this
            set_time_limit( 30 );

            // I only load one page at a time because internal requests are cheap, and there have been memory errors
            $params = array(
                'action' => 'query',
                'list'=> 'allpages',
                'apnamespace' => '0',
                'aplimit'=> '1',
                'apfrom'=> $apfrom
            );

            $request = new FauxRequest( $params, true );
            $api = new ApiMain( $request );
            $api->execute();
            $result = $api->getResult();
            $rootData = $result->getData();
            
            $allPages = $rootData['query']['allpages'];
            // I implemented a foreach, so that the aplimit above could theoritically be increased
            foreach ( $allPages as $page ) {
                $theRevision = NULL;
                $startAt = NULL;
                while ( is_null( $theRevision ) ) {
                    // again, only one revision at a time; additionally the content is lazy loaded
                    $params = array(
                        'action' => 'query',
                        'prop' => 'revisions',
                        'titles' => $page['title'],
                        'rvlimit'=> '1',
                        'rvprop'=> 'timestamp|ids',
                        'rvdir' => 'older'
                    );
                    if ( !is_null( $startAt ) ) {
                        $params['rvstartid'] = $startAt;
                    }
                    $request = new FauxRequest( $params, true );
                    $api = new ApiMain( $request );
                    $api->execute();
                    $result = $api->getResult();
                    $data = $result->getData();
                    unset( $result );

                    $revisions = $data['query']['pages'][(string)$page['pageid']]['revisions'];

                    // again, the rvlimit could theoretically be increased 
                    foreach( $revisions as $revision ) {
                        $revisionTimestamp = strtotime( $revision['timestamp'] );
                        if ( $revisionTimestamp <= $revertTimestamp ) {
                            $theRevision = $revision;
                            break;
                        }
                    }

                    if ( array_key_exists( 'query-continue', $data ) ) {
                        $startAt = $data['query-continue']['revisions']['rvstartid'];
                    }
                    else if ( is_null( $theRevision ) ) {
                        // if we got here and neither $data['query-continue'] nor $theRevision are defined, the page didn't exist then 
                        $theRevision = array( 'delete' => 'true' );
                    }
                }
                $content = '';
                if ( !array_key_exists( 'delete', $theRevision ) ) {
                    // lazy load the content to prevent memory overflows
                    $contentParams = array(
                        'action' => 'query',
                        'prop' => 'revisions',
                        'titles' => $page['title'],
                        'rvlimit'=> '1',
                        'rvprop'=> 'content',
                        'rvdir' => 'older',
                        'rvstartid' => $theRevision['revid']
                    );
                    $contentRequest = new FauxRequest( $contentParams, true );
                    $contentAPI = new ApiMain( $contentRequest );
                    $contentAPI->execute();
                    $contentResult = $contentAPI->getResult();
                    $contentData = $contentResult->getData();
                    $content = $contentData['query']['pages'][(string)$page['pageid']]['revisions'][0]['*'];
                }

                $summary = 'UTurn to ' . $revertTimestamp;
                $currentPage = new WikiPage( Title::newFromText( $page['title'] ), 0 );
                if ( $deletePages && $content == '' ) {
                    $errors = array();
                    // doDeleteArticleReal was not defined until 1.19, this will need to be revised when 1.18 is less prevalent
                    $currentPage->doDeleteArticle( $summary, false, 0, true, $errors, User::newFromSession() );
                }
                else {
                    $currentPage->doEdit( $content, $summary, EDIT_UPDATE, false, User::newFromSession() );
                }

            }
            if ( array_key_exists( 'query-continue', $rootData ) ) {
                $apfrom = $rootData['query-continue']['allpages']['apfrom'];
            }
            else {
                // at this point the UTurn is complete, and we can break out of the while(true)
                break;
            }
        } 
    }
}