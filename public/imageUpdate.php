<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business the image belongs to.
// name:				The name of the image.  
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_writingcatalog_imageUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'writingcatalog_image_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Additional Image'), 
        'name'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Title'), 
        'permalink'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Permalink'), 
        'webflags'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Website Flags'), 
        'sequence'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Sequence'), 
		'image_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Image'),
        'description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Description'), 
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
    $rc = ciniki_writingcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.writingcatalog.imageUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	//
	// Get the existing image details
	//
	$strsql = "SELECT uuid, writingcatalog_id, image_id FROM ciniki_writingcatalog_images "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_image_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'item');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['item']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2454', 'msg'=>'Image not found'));
	}
	$item = $rc['item'];

	if( isset($args['name']) ) {
		if( $args['name'] != '' ) { 
			$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['name'])));
		} else {
			// Use the UUID if image name is not specified
			$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($item['uuid'])));
		}
		//
		// Make sure the permalink is unique
		//
		$strsql = "SELECT id, name, permalink FROM ciniki_writingcatalog_images "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
			. "AND writingcatalog_id = '" . ciniki_core_dbQuote($ciniki, $item['writingcatalog_id']) . "' "
			. "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_image_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'image');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['num_rows'] > 0 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2455', 'msg'=>'You already have an image with this name, please choose another name'));
		}
	} 

	//
	// Update the image
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
	return ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.writingcatalog.image', $args['writingcatalog_image_id'], $args);
}
?>
