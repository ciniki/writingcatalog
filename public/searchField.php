<?php
//
// Description
// -----------
// This method will search a field for the search string provided.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to search.
// field:			The field to search.  Possible fields available to search are:
//
//					- category
//					- location
//
// start_needle:	The search string to search the field for.
//
// limit:			(optional) Limit the number of results to be returned. 
//					If the limit is not specified, the default is 25.
// 
// Returns
// -------
// <results>
//		<result name="Landscape" />
//		<result name="Portrait" />
// </results>
//
function ciniki_writingcatalog_searchField($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Field'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Search'), 
        'limit'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Limit'), 
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
    $rc = ciniki_writingcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.writingcatalog.searchField'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Reject if an unknown field
	//
	if( $args['field'] != 'year') {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'599', 'msg'=>'Unvalid search field'));
	}
	//
	// Get the number of faqs in each status for the business, 
	// if no rows found, then return empty array
	//
	if( $args['field'] == 'price' ) {
		$strsql = "SELECT DISTINCT ROUND(price, 2) AS name ";
	} else {
		$strsql = "SELECT DISTINCT " . $args['field'] . " AS name ";
	}
	$strsql .= "FROM ciniki_writingcatalog "
		. "WHERE ciniki_writingcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND (" . $args['field']  . " LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "AND " . $args['field'] . " <> '' "
			. ") "
		. "";
	if( $args['field'] == 'year' ) {
		$strsql .= "ORDER BY " . $args['field'] . " COLLATE latin1_general_cs DESC ";
	} else {
		$strsql .= "ORDER BY " . $args['field'] . " COLLATE latin1_general_cs ";
	}
	if( isset($args['limit']) && $args['limit'] != '' && $args['limit'] > 0 ) {
		$strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
	} else {
		$strsql .= "LIMIT 25 ";
	}
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.writingcatalog', array(
		array('container'=>'results', 'fname'=>'name', 'name'=>'result', 'fields'=>array('name')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['results']) || !is_array($rc['results']) ) {
		return array('stat'=>'ok', 'results'=>array());
	}
	return array('stat'=>'ok', 'results'=>$rc['results']);
}
?>
