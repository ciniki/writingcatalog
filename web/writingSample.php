<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_writingcatalog_web_writingSample($ciniki, $settings, $business_id, $permalink, $sample_permalink) {

	//
	// Load INTL settings
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'intlSettings');
	$rc = ciniki_businesses_intlSettings($ciniki, $business_id);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$intl_timezone = $rc['settings']['intl-default-timezone'];
	$intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
	$intl_currency = $rc['settings']['intl-default-currency'];

	//
	// Get the book information
	//
	$strsql = "SELECT ciniki_writingcatalog.id, "
		. "ciniki_writingcatalog.title, "
		. "ciniki_writingcatalog.subtitle, "
		. "ciniki_writingcatalog.permalink, "
		. "ciniki_writingcatalog.type, "
		. "ciniki_writingcatalog.type AS type_text, "
		. "ciniki_writingcatalog.webflags, "
		. "ciniki_writingcatalog.image_id, "
		. "ciniki_writingcatalog.synopsis, "
		. "ciniki_writingcatalog.description "
		. "FROM ciniki_writingcatalog "
		. "WHERE ciniki_writingcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_writingcatalog.permalink = '" . ciniki_core_dbQuote($ciniki, $permalink) . "' "
		. "AND (ciniki_writingcatalog.webflags&0x01) = 0x01 "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.writingcatalog', array(
		array('container'=>'items', 'fname'=>'id',
			'fields'=>array('id', 'title', 'subtitle', 'type', 'type_text', 'permalink', 'image_id', 'synopsis', 'description'),
			),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['items']) && count($rc['items']) != 1 ) {
		return array('stat'=>'404', 'err'=>array('pkg'=>'ciniki', 'code'=>'2501', 'msg'=>"I'm sorry, but we can't seem to find the item you requested."));
	}
	$item = array_pop($rc['items']);

	//
	// Get the sample for the book
	//
	$strsql = "SELECT id, title, permalink, content_type, image_id, content "
		. "FROM ciniki_writingcatalog_content "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_writingcatalog_content.writingcatalog_id = '" . ciniki_core_dbQuote($ciniki, $item['id']) . "' "
		. "AND ciniki_writingcatalog_content.permalink = '" . ciniki_core_dbQuote($ciniki, $sample_permalink) . "' "
		. "";
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.writingcatalog', array(
		array('container'=>'content', 'fname'=>'id', 
			'fields'=>array('id', 'title', 'permalink', 'content_type', 'image_id', 'content')),
	));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$item['sample'] = array();
	if( isset($rc['content']) ) {
		$item['sample'] = array_pop($rc['content']);
	}

	return array('stat'=>'ok', 'item'=>$item);
}
?>
