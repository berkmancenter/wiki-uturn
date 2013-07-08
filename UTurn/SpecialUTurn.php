<?php

/*
 * UTurn 
 * v1.1
 * 
 * Tomas Reimers
 *
 * Constructor of the special page; also holds the actual function.
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
            '<div class="uturn_description">' . $this->msg('uturn-desc')->text() . '</div>' .
            Xml::openElement(
                'form',
                array(
                    'action' => $this->getTitle()->getLocalURL( 'action=submit' ),
                    'method' => 'post',
                    'id' => 'uturn-form'
                )
            ) .
                '<div class="uturn_option_pair">' .
                    '<div class="uturn_option_title">' . Xml::label( $this->msg('uturn-date-key')->text(), 'uturn-date' ) . '</div>' .
                    '<div class="uturn_option_input">' . Xml::input( 'uturndate', 40, '', array( 'id' => 'uturn-date' ) ) . '</div>' .
                '</div>' .
                '<div class="uturn_option_description">' . $this->msg('uturn-date-desc')->text() . '</div>' . 

                '<div class="uturn_option_pair">' .
                    '<div class="uturn_option_title">' . Xml::label( $this->msg('uturn-delete-key')->text(), 'uturn-delete' ) . '</div>' .
                    '<div class="uturn_option_input">' . Xml::check( 'uturndelete', false, array( 'id' => 'uturn-delete' ) ) . '</div>' .
                '</div>' .
                '<div class="uturn_option_description">' . $this->msg('uturn-delete-desc')->text() . '</div>' . 

                '<div class="uturn_option_pair">' .
                    '<div class="uturn_option_title">' . Xml::label( $this->msg('uturn-user-key')->text(), 'uturn-user' ) . '</div>' .
                    '<div class="uturn_option_input">' . Xml::check( 'uturnuser', false, array( 'id' => 'uturn-user' ) ) . '</div>' .
                '</div>' .
                '<div class="uturn_option_description">' . $this->msg('uturn-user-desc')->text() . '</div>' . 

                '<div class="uturn_option_pair">' .
                    '<div class="uturn_option_title">' . Xml::label( $this->msg('uturn-whitelist-namespaces-key')->text(), 'uturn-whitelist-namespaces' ) . '</div>' .
                    '<div class="uturn_option_input">' . Xml::input( 'uturnwhitelistnamespaces', 40, '', array( 'id' => 'uturn-whitelist-namespaces' ) ) . '</div>' .
                '</div>' .
                '<div class="uturn_option_description">' . $this->msg('uturn-whitelist-namespaces-desc')->text() . '</div>' . 

                '<div class="uturn_option_pair">' .
                    '<div class="uturn_option_title">' . Xml::label( $this->msg('uturn-whitelist-pages-key')->text(), 'uturn-whitelist-pages' ) . '</div>' .
                    '<div class="uturn_option_input">' . Xml::input( 'uturnwhitelistpages', 40, '', array( 'id' => 'uturn-whitelist-pages' ) ) . '</div>' .
                '</div>' .
                '<div class="uturn_option_description">' . $this->msg('uturn-whitelist-pages-desc')->text() . '</div>' . 

                '<div class="uturn_option_pair">' .
                    '<div class="uturn_option_title">' . Xml::label( $this->msg('uturn-whitelist-users-key')->text(), 'uturn-whitelist-users' ) . '</div>' .
                    '<div class="uturn_option_input">' . Xml::input( 'uturnwhitelistusers', 40, '', array( 'id' => 'uturn-whitelist-users' ) ) . '</div>' .
                '</div>' .
                '<div class="uturn_option_description">' . $this->msg('uturn-whitelist-users-desc')->text() . '</div>' . 

                '<div class="uturn_buttons">' . 
                    Xml::submitButton( $this->msg('uturn-submit')->text(), array('id' => 'uturn-submit') ) . 
                    '<div id="uturn-status"></div>' .
                '</div>' . 
            Xml::closeElement( 'form' )
        );
    }

    /*
     * Filtering function for inputs passed as arrays
     */ 

    function parse_value_list($input) {

        $exploded_input = explode("|", $input);

        $trimmed_array = array_map(function ($item){

            return trim(urldecode($item));

        }, $exploded_input);

        $filtered_array = array_filter($trimmed_array, function ($item){
            return $item != "";
        });

        return $filtered_array;

    }

    /*
     * The actual UTurn function: UTurns the wiki in response to a correct request
     */ 

    function UTurn() {

        $req = $this->getRequest();

        $revertTimestamp = intval( $req->getVal( 't' ) );
        
        $deletePages = ($req->getVal( 'deletePages' ) != NULL); 

        $whitelistNamespaces = $this->parse_value_list($req->getVal( 'whitelistNamespaces' )); 
        $whitelistPages = $this->parse_value_list($req->getVal( 'whitelistPages' )); 
        $whitelistUsers = $this->parse_value_list($req->getVal( 'whitelistUsers' )); 

        if ( $revertTimestamp > time() ) {
            return "Can't go into the future.";
        }

        $this->revertPages($revertTimestamp, $deletePages, $whitelistNamespaces, $whitelistPages, $whitelistUsers);
        if ($req->getVal( 'deleteUsers' ) != NULL){
            $this->revertUsers($revertTimestamp);
        }
    }

    /* 
     * Bans user accounts if created after the UTurn date (if that option was checked).
     */ 

    function revertUsers($revertTimestamp){
        $allUsers = array();
        $aufrom = '';
        // loop until completion (at which point we will break)
        // I don't define a limit because at this point the total page count is unknown
        while ( true ){

            // each page takes a while, and if your wiki has enough pages it will go over the PHP execution timelimit
            set_time_limit( 30 );

            // I only load one page at a time because internal requests are cheap, and there have been memory errors
            $params = array(
                'action' => 'query',
                'list'=> 'allusers',
                'aulimit'=> '1',
                'auexcludegroup' => 'bureaucrat|sysop',
                'auprop' => 'registration',
                'aufrom'=> $aufrom
            );

            $request = new FauxRequest( $params, true );
            $api = new ApiMain( $request );
            $api->execute();
            $result = $api->getResult();
            $rootData = $result->getData();
            
            $allUsers = $rootData['query']['allusers'];
            // I implemented a foreach, so that the aulimit above could theoritically be increased
            foreach ( $allUsers as $user ) {
                if ( array_key_exists( 'registration', $user ) ) {
                    if ($user["registration"] != ""){
                        $registered = strtotime($user["registration"]);
                        if ($registered >= $revertTimestamp){
                            $userName = $user["name"];
                            $userId = $user["userid"];
                            $user = User::newFromId($userId);

                            $currentUser = User::newFromSession();

                            $block = new Block(
                                $user,
                                $userId,
                                $currentUser->getId(),
                                $reason = 'UTurn to ' . $revertTimestamp,
                                0,
                                0,
                                'infinity'
                            );
                            $block->insert();
                        }
                    }
                }
            }
            if ( array_key_exists( 'query-continue', $rootData ) ) {
                $aufrom = $rootData['query-continue']['allusers']['aufrom'];
            }
            else {
                // at this point the UTurn is complete, and we can break out of the while(true)
                break;
            }
        } 
    }

    /* 
     * Deals with pages, also deletes files.
     */ 

    function revertPages($revertTimestamp, $deletePages, $whitelistNamespaces, $whitelistPages, $whitelistUsers){
        // get namespaces
        $namespaceParams = array(
            'action' => 'query',
            'meta' => 'siteinfo',
            'siprop'=> 'namespaces'
        );
        $namespaceRequest = new FauxRequest( $namespaceParams, true );
        $namespaceApi = new ApiMain( $namespaceRequest );
        $namespaceApi->execute();
        $namespaceResult = $namespaceApi->getResult();
        $namespaceData = $namespaceResult->getData();

        $namespaces = array_keys($namespaceData["query"]["namespaces"]);

        foreach ( $namespaces as $namespace ){

            // skip media and special namespaces
            if ($namespace < 0 || in_array($namespace, $whitelistNamespaces)){
                continue;
            }

            $allPages = array();
            $apfrom = '';
            // loop until completion (at which point we will break)
            // I don't define a limit because at this point the total page count is unknown
            while ( true ){

                // each page takes a while, and if your wiki has enough pages it will go over the PHP execution timelimit
                set_time_limit( 30 );

                // I only load one page at a time because internal requests are cheap, and there have been memory errors
                $params = array(
                    'action' => 'query',
                    'list'=> 'allpages',
                    'apnamespace' => (string) $namespace,
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

                    if ( in_array( $page['title'], $whitelistPages) ){
                        continue;
                    }

                    $theRevision = NULL;
                    $startAt = NULL;
                    while ( is_null( $theRevision ) ) {
                        // again, only one revision at a time; additionally the content is lazy loaded
                        $params = array(
                            'action' => 'query',
                            'prop' => 'revisions',
                            'pageids' => $page['pageid'],
                            'rvlimit'=> '1',
                            'rvprop'=> 'timestamp|ids|user',
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
                        
                        // skip if page revisions cannot be accessed
                        if ( !is_array( $revisions ) ){
                            $theRevision = array( 'skip' => 'true' );
                            break;
                        }
                        
                        // again, the rvlimit could theoretically be increased 
                        foreach( $revisions as $revision ) {
                            $revisionTimestamp = strtotime( $revision['timestamp'] );
                            if ( $revisionTimestamp <= $revertTimestamp || in_array($revision['user'], $whitelistUsers)) {
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
                    if ( !array_key_exists( 'skip', $theRevision ) ){
                        $content = '';
                        if ( !array_key_exists( 'delete', $theRevision ) ) {
                            // lazy load the content to prevent memory overflows
                            $contentParams = array(
                                'action' => 'query',
                                'prop' => 'revisions',
                                'pageids' => $page['pageid'],
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
                        $currentPage = WikiPage::newFromID( $page['pageid'] );
                        if ( $deletePages && $content == '' ) {
                            if ($namespace == NS_FILE){
                                $file = wfFindFile($currentPage->mTitle, array( 'ignoreRedirect' => true ) );
                                $old = "";
                                FileDeleteForm::doDelete( $currentPage->mTitle, $file, $old, $summary, false );
                            }
                            $errors = array();
                            // doDeleteArticleReal was not defined until 1.19, this will need to be revised when 1.18 is less prevalent
                            $currentPage->doDeleteArticle( $summary, false, 0, true, $errors, User::newFromSession() );
                        }
                        else {
                            $currentPage->doEdit( $content, $summary, EDIT_UPDATE, false, User::newFromSession() );
                        }
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
}