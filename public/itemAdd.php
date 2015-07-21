<?php
//
// Description
// ===========
// This method will add a new item to the art catalog.  The image for the item
// must be uploaded separately into the ciniki images module.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to add the item to.  The user must
//					an owner of the business.
//
// type:			The type of the item.  Currently
//					only two types are supported, Painting and Photographs.
//
//					1 - Painting
//					2 - Photograph
//					3 - Jewelry
//					4 - Sculpture
//					5 - Fibre Art
//					6 - Clothing *future*
//
// webflags:		(optional) The flags for displaying the item on the business website.
//
//					0x01 - Public item, to be displayed on the website
//
// image_id:		(optional) The ID of the image in the images module to be displayed for the item.  This
//					can be uploaded before or after the item is added to the writingcatalog.
//
// name:			The name of the item.  This name must be unique within the business, as it's
//					also used to generate the permalink.  The permalink must be usique because it
//					is used as in the URL to reference an item.
//
// catalog_number:	(optional) A freeform field to store a catalog number if the user wants.  The
//					can be any string of characters.
//					
// category:		(optional) The name of the category the item is a part of.  Only one category can
//					be assigned to each item.
//
// year:			(optional) The year the item was completed, or in the case of a photograph, when the
//					photo was taken.
//
// synopsis:		(optional) The synopsis of the item, which will be displayed on the website.
//
// description:		(optional) The description of the item, which will be displayed on the website.
//
// toc:				(optional) The table of contents for the item.
//
// inspiration:		(optional) Where the inspiration came from for the item.  This information will not be displayed on the website.
//
// awards:			(optional) Any awards that the item has won.  This information is displayed along with the description
//					on the website.
//
// notes:			(optional) Any notes the creator has for the item.  This information is private and will not be displayed on the website.
//
// lists:			(optional) The lists the item is a part of.
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
		'type'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Type'),
        'webflags'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Web Flags'), 
		'image_id'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'0', 'name'=>'Image'),
        'title'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Title'), 
        'catalog_number'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Catalog Number'), 
        'category'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Category'), 
        'year'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Year'), 
        'month'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Month'), 
        'day'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Day'), 
        'synopsis'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Description'), 
        'description'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Description'), 
        'toc'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Description'), 
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
	$strsql = "SELECT id, name, permalink FROM ciniki_writingcatalog "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'item');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( $rc['num_rows'] > 0 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'649', 'msg'=>'You already have an item with this title, please choose another title.'));
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

//	$ciniki['fbrefreshqueue'][] = array('business_id'=>$args['business_id'], 'url'=>'/gallery/category/' . urlencode($args['category']) . '/' . $args['permalink']);

	return array('stat'=>'ok', 'id'=>$writingcatalog_id);
}
?>
