<?php
//
// Description
// ===========
// This method will add a new item to the writing catalog.  The image for the item
// must be uploaded separately into the ciniki images module.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to add the item to.  The user must
//					an owner of the business.
// 
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_writingcatalog_itemAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'title'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Title'), 
        'subtitle'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Subtitle'), 
        'permalink'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Permalink'), 
		'type'=>array('required'=>'no', 'blank'=>'no', 'default'=>'30', 'name'=>'Type'),
        'webflags'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Web Flags'), 
		'image_id'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Image'),
        'catalog_number'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Catalog Number'), 
        'year'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Year'), 
        'month'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Month'), 
        'day'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Day'), 
        'synopsis'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Synopsis'), 
        'description'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Description'), 
        'content'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Content'), 
        'inspiration'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Inspiration'), 
        'awards'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Awards'), 
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Notes'), 
		'categories'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'list', 'delimiter'=>'::', 'name'=>'Categories'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'writingcatalog', 'private', 'checkAccess');
    $rc = ciniki_writingcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.writingcatalog.itemAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
	$args['permalink'] = ciniki_core_makePermalink($ciniki, $args['title']);
    
	//
	// Get a new UUID
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUUID');
	$rc = ciniki_core_dbUUID($ciniki, 'ciniki.writingcatalog');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args['uuid'] = $rc['uuid'];

	//
	// Check the permalink doesn't already exist
	//
	$strsql = "SELECT id, title, permalink FROM ciniki_writingcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'item');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( $rc['num_rows'] > 0 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2456', 'msg'=>'You already have an item with this title, please choose another title.'));
	}

	//  
	// Turn off autocommit
	//  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.writingcatalog');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	$rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.writingcatalog.item', $args, 0x04);
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.writingcatalog');
		return $rc;
	}
	$writingcatalog_id = $rc['id'];

	//
	// Update the categories
	//
	if( isset($args['categories']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
		$rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.writingcatalog', 'tag', $args['business_id'],
			'ciniki_writingcatalog_tags', 'ciniki_writingcatalog_history',
			'writingcatalog_id', $writingcatalog_id, 10, $args['categories']);
		if( $rc['stat'] != 'ok' ) {
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.writingcatalog');
			return $rc;
		}
	}

	//
	// Commit the database changes
	//
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.writingcatalog');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'writingcatalog');

	return array('stat'=>'ok', 'id'=>$writingcatalog_id);
}
?>
