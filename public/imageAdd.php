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
// <rsp stat='ok' id='34' />
//
function ciniki_writingcatalog_imageAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'writingcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item'),
        'name'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Title'), 
        'sequence'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'name'=>'Sequence'),
        'permalink'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Permalink'), 
        'webflags'=>array('required'=>'no', 'default'=>'0', 'blank'=>'yes', 'name'=>'Website Flags'), 
        'image_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Image'),
        'description'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Description'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'writingcatalog', 'private', 'checkAccess');
    $rc = ciniki_writingcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.writingcatalog.imageAdd'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Get a new UUID, do this first so it can be used as permalink if necessary
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUUID');
    $rc = ciniki_core_dbUUID($ciniki, 'ciniki.writingcatalog');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args['uuid'] = $rc['uuid'];

    //
    // Determine the permalink
    //
    if( !isset($args['permalink']) || $args['permalink'] == '' ) {
        if( isset($args['name']) && $args['name'] != '' ) {
            $args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['name'])));
        } else {
            $args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['uuid'])));
        }
    }

    //
    // Check the permalink doesn't already exist for this item in the writingcatalog
    //
    $strsql = "SELECT id, name, permalink FROM ciniki_writingcatalog_images "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND writingcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_id']) . "' "
        . "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'image');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( $rc['num_rows'] > 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.writingcatalog.9', 'msg'=>'You already have an image with this name, please choose another name'));
    }

    if( $args['writingcatalog_id'] <= 0 ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.writingcatalog.10', 'msg'=>'No writingcatalog specified'));
    }

    //
    // Add the image
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
    return ciniki_core_objectAdd($ciniki, $args['tnid'], 'ciniki.writingcatalog.image', $args, 0x07);
}
?>
