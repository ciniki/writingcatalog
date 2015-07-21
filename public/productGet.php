<?php
//
// Description
// ===========
// This method will return all the information for an item in the art catalog.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id: 		The ID of the business to get the item from.
// writingcatalog_id:		The ID of the item in the catalog to be retrieved.
// 
// Returns
// -------
//
function ciniki_writingcatalog_productGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'writingcatalog_id'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Item'), 
        'product_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Product'), 
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
    $rc = ciniki_writingcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.writingcatalog.productGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Load INTL settings
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
	$rc = ciniki_businesses_intlSettings($ciniki, $args['business_id']);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$intl_timezone = $rc['settings']['intl-default-timezone'];
	$intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
	$intl_currency = $rc['settings']['intl-default-currency'];

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);
	
	//
	// Setup default settings
	//
	if( $args['product_id'] == 0 ) {
		$product = array('id'=>0,
			'name'=>'',
			'permalink'=>'',
			'flags'=>0,
			'sequence'=>0,
			'image_id'=>0,
			'synopsis'=>'',
			'description'=>'',
			'price'=>'',
			'taxtype_id'=>0,
			'inventory'=>'',
			);
		//
		// Get the image from the writingcatalog item
		//
		if( isset($args['writingcatalog_id']) && $args['writingcatalog_id'] > 0 ) {
			$strsql = "SELECT image_id "
				. "FROM ciniki_writingcatalog "
				. "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_id']) . "' "
				. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "";
			$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'item');
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['item']) ) {
				$product['image_id'] = $rc['item']['image_id'];
			}
		}
	} 

	//
	// Load the product details
	//
	else {
		$strsql = "SELECT id, name, permalink, "
			. "flags, sequence, image_id, "
			. "synopsis, "
			. "description, "
			. "price, "
			. "taxtype_id, "
			. "inventory "
			. "FROM ciniki_writingcatalog_products "
			. "WHERE id = '" . ciniki_core_dbQuote($ciniki, $args['product_id']) . "' "
			. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'product');
		if( $rc['stat'] != 'ok' ) {	
			return $rc;
		}
		if( !isset($rc['product']) ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2410', 'msg'=>'Unable to find product'));
		}
		$product = $rc['product'];
		$product['price'] = numfmt_format_currency($intl_currency_fmt, $rc['product']['price'], $intl_currency);
	}
	
	return array('stat'=>'ok', 'product'=>$product);
}
?>
