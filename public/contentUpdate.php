<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business the content belongs to.
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_writingcatalog_contentUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'content_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'ID'), 
        'title'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Title'), 
        'permalink'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Permalink'), 
        'content_type'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Type'), 
        'sequence'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Sequence'), 
		'image_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Image'),
        'content'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Content'), 
        'paypal_business'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Paypal Business'), 
        'paypal_price'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Paypal Price'), 
        'paypal_currency'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Paypal Currency'), 
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
    $rc = ciniki_writingcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.writingcatalog.contentUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	//
	// Get the existing content details
	//
	$strsql = "SELECT uuid, writingcatalog_id, image_id "
		. "FROM ciniki_writingcatalog_content "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'content');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['content']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2374', 'msg'=>'Content not found'));
	}
	$content = $rc['content'];

	if( isset($args['title']) && $args['title'] != '' ) {
		$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['title'])));
		//
		// Make sure the permalink is unique
		//
		$strsql = "SELECT id, title, permalink "
			. "FROM ciniki_writingcatalog_content "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
			. "AND writingcatalog_id = '" . ciniki_core_dbQuote($ciniki, $content['writingcatalog_id']) . "' "
			. "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'content');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['num_rows'] > 0 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2375', 'msg'=>'You already have an content with this title, please choose another title'));
		}
	} 

	//
	// Update the content
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
	return ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.writingcatalog.content', $args['content_id'], $args);
}
?>
