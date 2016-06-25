<?php
//
// Description
// ===========
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_writingcatalog_contentDelete(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'content_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Content'),
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
    $rc = ciniki_writingcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.writingcatalog.contentDelete'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Get the existing content information
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_writingcatalog_content "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'content');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['content']) ) {
        return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2499', 'msg'=>'Content does not exist'));
    }
    $content = $rc['content'];

    //
    // Delete the content
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    return ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.writingcatalog.content', 
        $args['content_id'], $content['uuid'], 0x07);
}
?>
