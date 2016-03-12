<?php
//
// Description
// ===========
// This method updates one or more elements of an existing item in the writing catalog.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to the item is a part of.
// writingcatalog_id:	The ID of the item in the writing catalog.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_writingcatalog_itemUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'writingcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item'), 
        'title'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Title'), 
        'subtitle'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Subtitle'), 
		'type'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Type'),
        'webflags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Web Flags'), 
		'image_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Image'),
        'catalog_number'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Catalog Number'), 
        'year'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Year'), 
        'month'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Month'), 
        'day'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Day'), 
        'synopsis'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Synopsis'), 
        'description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Description'), 
        'content'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Content'), 
        'inspiration'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Inspiration'), 
        'awards'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Awards'), 
        'notes'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Notes'),
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
    $rc = ciniki_writingcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.writingcatalog.itemUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	if( isset($args['title']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
		$args['permalink'] = ciniki_core_makePermalink($ciniki, $args['title']);
		//
		// Make sure the permalink is unique
		//
		$strsql = "SELECT id, title, permalink "
			. "FROM ciniki_writingcatalog "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
			. "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'item');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['num_rows'] > 0 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2463', 'msg'=>'You already have an item with this title, please choose another title.'));
		}
	}

	//
	// Get the existing information
	//
	$strsql = "SELECT id, title, permalink "
		. "FROM ciniki_writingcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'item');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$item = $rc['item'];

	//  
	// Turn off autocommit
	//  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.writingcatalog');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Update the item
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
	$rc = ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.writingcatalog.item',
		$args['writingcatalog_id'], $args, 0x04);
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.writingcatalog');
		return $rc;
	}

	//
	// Update the categories
	//
	if( isset($args['categories']) ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsUpdate');
		$rc = ciniki_core_tagsUpdate($ciniki, 'ciniki.writingcatalog', 'tag', $args['business_id'],
			'ciniki_writingcatalog_tags', 'ciniki_writingcatalog_history',
			'writingcatalog_id', $args['writingcatalog_id'], 10, $args['categories']);
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

	return array('stat'=>'ok');
}
?>
