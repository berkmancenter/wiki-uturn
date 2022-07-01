<?php

/*
 * UTurn 
 * v1.2
 * 
 * Tomas Reimers
 *
 * Constructor of the special page; also holds the actual function.
 */

ini_set('max_execution_time', '2000');

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
                    'action' => $this->getContext()->getTitle()->getLocalURL( 'action=submit' ),
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
                    '<div class="uturn_option_input">' . Xml::input( 'uturnwhitelistnamespaces', 40, '', array( 'id' => 'uturn-whitelist-namespaces' ) ) . " " . Xml::check( 'uturnwhitelistadmins', false, array( 'id' => 'uturn-whitelist-admins' ) ) . " " . $this->msg('uturn-whitelist-admins')->text() . '</div>' .
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

        if ($req->getVal( 'whitelistAdmins' ) != NULL){
            $whitelistUsers = array_merge($whitelistUsers, $this->listAllAdmins());
        }

        if ( $revertTimestamp > time() ) {
            return "Can't go into the future.";
        }

        $this->revertPages($revertTimestamp, $deletePages, $whitelistNamespaces, $whitelistPages, $whitelistUsers);
        if ($req->getVal( 'deleteUsers' ) != NULL){
            $this->revertUsers($revertTimestamp);
        }
    }

    /*
     * Creates list of administrators and bureaucrats to whitelist
     */ 

    function listAllAdmins(){

        $toReturn = array();

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
                'augroup' => 'sysop',
                'aufrom'=> $aufrom
            );

            $request = new FauxRequest( $params, true );
            $api = new ApiMain( $request );
            $api->execute();
            $result = $api->getResult();
            $rootData = $result->getResultData();

            $allUsers = $rootData['query']['allusers'];
            // I implemented a foreach, so that the aulimit above could theoritically be increased
            foreach ( $allUsers as $user ) {
                array_push($toReturn, $user["name"]);
            }
            if ( array_key_exists( 'query-continue', $rootData ) ) {
                $aufrom = $rootData['query-continue']['allusers']['aufrom'];
            }
            else {
                // at this point the UTurn is complete, and we can break out of the while(true)
                break;
            }
        }

        return $toReturn;

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
            // I only load one page at a time because internal requests are cheap, and there have been memory errors
            $params = array(
                'action' => 'query',
                'list'=> 'allusers',
                'aulimit'=> '1',
                'auexcludegroup' => 'sysop',
                'auprop' => 'registration',
                'aufrom'=> $aufrom
            );

            $request = new FauxRequest( $params, true );
            $api = new ApiMain( $request );
            $api->execute();
            $result = $api->getResult();
            $rootData = $result->getResultData();
            
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

                            $block = new Block([
                                'user' => $user,
                                'by' => $currentUser->getId(),
                                'reason' => 'UTurn to ' . $revertTimestamp,
                                'expiry' => 'infinity'
                            ]);
                            $block->setTarget($user);
                            $block->insert();
                        }
                    }
                }
            }

            if ( array_key_exists( 'continue', $rootData ) ) {
                $aufrom = $rootData['continue']['aufrom'];
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
        $namespaceData = $namespaceResult->getResultData();

        $namespaces = $namespaceData["query"]["namespaces"];
        foreach ( $namespaces as $key => $namespace ){
            // skip meta elements
            if ($key === '_type' || $key === '_element') {
                continue;
            }

            // skip media and special namespaces
            if ($namespace['id'] < 0 || in_array($namespace['id'], $whitelistNamespaces)){
                continue;
            }

            $allPages = array();
            $apfrom = '';
            // loop until completion (at which point we will break)
            // I don't define a limit because at this point the total page count is unknown
            while ( true ){
                // I only load one page at a time because internal requests are cheap, and there have been memory errors
                $params = array(
                    'action' => 'query',
                    'list'=> 'allpages',
                    'apnamespace' => (string) $namespace['id'],
                    'aplimit'=> '10000',
                    'apfrom'=> $apfrom
                );

                $request = new FauxRequest( $params, true );
                $api = new ApiMain( $request );
                $api->execute();
                $result = $api->getResult();
                $rootData = $result->getResultData();

                $allPages = $rootData['query']['allpages'];
                usort($allPages, function ($item1, $item2) {
                    return $item2['pageid'] <=> $item1['pageid'];
                });

                // I implemented a foreach, so that the aplimit above could theoritically be increased
                foreach ( $allPages as $page ) {
                    if ( !is_array($page) || in_array( $page['title'], $whitelistPages) ){
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
                        $data = $result->getResultData();

                        $revisions = $data['query']['pages'][(string)$page['pageid']]['revisions'];

                        // skip if page revisions cannot be accessed
                        if ( !is_array( $revisions ) ){
                            $theRevision = array( 'skip' => 'true' );
                            break;
                        }
                        // again, the rvlimit could theoretically be increased 
                        foreach( $revisions as $revision ) {
                            if (!is_array($revision)) {
                                continue;
                            }
                            $revisionTimestamp = strtotime( $revision['timestamp'] );
                            if ( $revisionTimestamp <= $revertTimestamp || in_array($revision['user'], $whitelistUsers)) {
                                $theRevision = $revision;
                                break;
                            }
                        }
                        if ( array_key_exists( 'continue', $data ) ) {
                            $startAt = explode( '|', $data['continue']['rvcontinue'] )[1];
                        }
                        else if ( is_null( $theRevision ) ) {
                            // if we got here and neither $data['continue'] nor $theRevision are defined, the page didn't exist then
                            $theRevision = array( 'delete' => 'true' );
                        }
                    }

                    if ( !array_key_exists( 'skip', $theRevision ) ){
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
                            $contentData = $contentResult->getResultData();
                            $content = $contentData['query']['pages'][(string)$page['pageid']]['revisions'][0]['content'];
                        }

                        $summary = 'UTurn to ' . $revertTimestamp;
                        $currentPage = WikiPage::newFromID( $page['pageid'] );
                        if ( $deletePages && array_key_exists( 'delete', $theRevision ) ) {
                            $errors = array();
                            // doDeleteArticleReal was not defined until 1.19, this will need to be revised when 1.18 is less prevalent
                            $this->deletePermanently($currentPage->mTitle);

                            if ($namespace == NS_FILE){
                                $file = wfFindFile($currentPage->mTitle, array( 'ignoreRedirect' => true ) );
                                $file->delete( $summary, false );
                            }

                        }
                        else {
                          $currentPage->doEditContent( new WikitextContent($content), $summary, EDIT_UPDATE, false, User::newFromSession() );
                        }
                    }
                }

                if ( array_key_exists( 'continue', $rootData ) ) {
                    $apfrom = $rootData['continue']['apcontinue'];
                }
                else {
                    // at this point the UTurn is complete, and we can break out of the while(true)
                    break;
                }
            } 
        }
    }

    private function deletePermanently( Title $title ) {
      $ns = $title->getNamespace();
      $t = $title->getDBkey();
      $id = $title->getArticleID();
      $cats = $title->getParentCategories();

      $lb = MediaWiki\MediaWikiServices::getInstance()->getDBLoadBalancer();
      $dbw = $lb->getConnectionRef(DB_MASTER);
      $factory = \MediaWiki\MediaWikiServices::getInstance()->getDBLoadBalancerFactory();

      /*
       * First delete entries, which are in direct relation with the page:
       */

      $factory->beginMasterChanges(__METHOD__);

      # Delete redirect...
      $dbw->delete( 'redirect', [ 'rd_from' => $id ] );

      # Delete external links...
      $dbw->delete( 'externallinks', [ 'el_from' => $id ] );

      # Delete language links...
      $dbw->delete( 'langlinks', [ 'll_from' => $id ] );

      if ( $GLOBALS['wgDBtype'] !== "postgres" && $GLOBALS['wgDBtype'] !== "sqlite" ) {
        # Delete search index...
        $dbw->delete( 'searchindex', [ 'si_page' => $id ] );
      }

      # Delete restrictions for the page
      $dbw->delete( 'page_restrictions', [ 'pr_page' => $id ] );

      # Delete page links
      $dbw->delete( 'pagelinks', [ 'pl_from' => $id ] );

      # Delete category links
      $dbw->delete( 'categorylinks', [ 'cl_from' => $id ] );

      # Delete template links
      $dbw->delete( 'templatelinks', [ 'tl_from' => $id ] );

      # Read text entries for all revisions and delete them.
      $resRev = $dbw->select( 'revision', 'rev_id', "rev_page=$id" );

      foreach ($resRev as $rowRev) {
        $rev_id = $rowRev->rev_id;
        $this->removeContentBySlotRevId($rev_id, $dbw);
      }

      # In the table 'revision' : Delete all the revision of the page where 'rev_page' = $id
      $dbw->delete( 'revision', [ 'rev_page' => $id ] );

      # Delete image links
      $dbw->delete( 'imagelinks', [ 'il_from' => $id ] );

      /*
       * then delete entries which are not in direct relation with the page:
       */

      # Clean up recentchanges entries...
      $dbw->delete( 'recentchanges', [
        'rc_namespace' => $ns,
        'rc_title' => $t
      ] );

      # Read text entries for all archived pages and delete them.
      $res = $dbw->select( 'archive', 'ar_rev_id', [
        'ar_namespace' => $ns,
        'ar_title' => $t
      ] );

      foreach ( $res as $row ) {
        $rev_id = $row->ar_rev_id;
        $this->removeContentBySlotRevId($rev_id, $dbw);
      }

      # Clean up archive entries...
      $dbw->delete( 'archive', [
        'ar_namespace' => $ns,
        'ar_title' => $t
      ] );

      # Clean up log entries...
      $dbw->delete( 'logging', [
        'log_namespace' => $ns,
        'log_title' => $t
      ] );

      # Clean up watchlist...
      $dbw->delete( 'watchlist', [
        'wl_namespace' => $ns,
        'wl_title' => $t
      ] );

      $dbw->delete( 'watchlist', [
        'wl_namespace' => MWNamespace::getAssociated( $ns ),
        'wl_title' => $t
      ] );

      # In the table 'page' : Delete the page entry
      $dbw->delete( 'page', [ 'page_id' => $id ] );

      /*
       * If the article belongs to a category, update category counts
       */
      if ( !empty( $cats ) ) {
        foreach ( $cats as $parentcat => $currentarticle ) {
          $catname = preg_split( '/:/', $parentcat, 2 );
          $cat = Category::newFromName( $catname[1] );
          if ( !is_object( $cat ) ) {
            // Blank error to allow us to continue
          } else {
            DeferredUpdates::addCallableUpdate([$cat, 'refreshCounts' ]);
          }
        }
      }

      /*
       * If an image is being deleted, some extra work needs to be done
       */
      if ( $ns == NS_FILE ) {
        if ( method_exists( MediaWiki\MediaWikiServices::class, 'getRepoGroup' ) ) {
          // MediaWiki 1.34+
          $file = MediaWiki\MediaWikiServices::getInstance()->getRepoGroup()->findFile( $t );
        } else {
          $file = wfFindFile( $t );
        }

        if ( $file ) {
          # Get all filenames of old versions:
          $res = $dbw->select( 'oldimage', '*', [ 'oi_name' => $t ] );

          foreach ( $res as $row ) {
            $oldLocalFile = OldLocalFile::newFromRow( $row, $file->repo );
            $path = $oldLocalFile->getArchivePath() . '/' . $oldLocalFile->getArchiveName();

            try {
              unlink( $path );
            }
            catch ( Exception $e ) {
              return $e->getMessage();
            }
          }

          $path = $file->getLocalRefPath();

          try {
            $file->purgeThumbnails();
            unlink( $path );
          } catch ( Exception $e ) {
            return $e->getMessage();
          }
        }

        # Clean the filearchive for the given filename:
        $dbw->delete( 'filearchive', [ 'fa_name' => $t ] );

        # Delete old db entries of the image:
        $dbw->delete( 'oldimage', [ 'oi_name' => $t ] );

        # Delete archive entries of the image:
        $dbw->delete( 'filearchive', [ 'fa_name' => $t ] );

        # Delete image entry:
        $dbw->delete( 'image', [ 'img_name' => $t ] );

        $linkCache = MediaWiki\MediaWikiServices::getInstance()->getLinkCache();
        $linkCache->clear();
      }

      $factory->commitMasterChanges(__METHOD__);

      return true;
    }

    private function removeContentBySlotRevId($rev_id, $dbw) {
      $resSlots = $dbw->select( 'slots', 'slot_content_id', "slot_revision_id=$rev_id" );

      foreach ($resSlots as $rowSlot) {
        $slot_content_id = $rowSlot->slot_content_id;

        $resContent = $dbw->select( 'content', 'content_address', "content_id=$slot_content_id" );

        foreach ($resContent as $rowContent) {
          $dbw->delete( 'text', [ 'old_id' => str_replace('tt:', '', $rowContent->content_address) ] );
        }

        $dbw->delete( 'content', [ 'content_id' => $slot_content_id ] );
        $dbw->delete( 'slots', [ 'slot_content_id' => $slot_content_id ] );
      }
    }
}
