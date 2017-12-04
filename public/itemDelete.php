<?php
//
// Description
// ===========
// This method will remove an item from the writing catalog.  All information
// will be removed, so be sure you want it deleted.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to remove the item from.
// writingcatalog_id:       The ID of the item in the catalog to be removed.
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_writingcatalog_itemDelete(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'writingcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item'), 
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
    $rc = ciniki_writingcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.writingcatalog.itemDelete'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Get the uuid of the writingcatalog item to be deleted
    //
    $strsql = "SELECT uuid "
        . "FROM ciniki_writingcatalog "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND id = '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'writingcatalog');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['writingcatalog']) ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.writingcatalog.16', 'msg'=>'Unable to find existing item'));
    }
    $uuid = $rc['writingcatalog']['uuid'];

    //
    // FIXME: Check if any products still exist **Future**
    //
//  $strsql = "SELECT id, uuid "
//      . "FROM ciniki_writingcatalog_products "
//      . "WHERE writingcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_id']) . "' "
//      . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
//      . "";
//  $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'product');
//  if( $rc['stat'] != 'ok' ) {
//      ciniki_core_dbTransactionRollback($ciniki, 'ciniki.writingcatalog');
//      return $rc;
//  }
//  if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
//      return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.writingcatalog.17', 'msg'=>'You must remove all products for this item first.'));
//  }
//
    //
    // Check if writingcatalog item is used anywhere
    //
    foreach($ciniki['tenant']['modules'] as $module => $m) {
        list($pkg, $mod) = explode('.', $module);
        $rc = ciniki_core_loadMethod($ciniki, $pkg, $mod, 'hooks', 'checkObjectUsed');
        if( $rc['stat'] == 'ok' ) {
            $fn = $rc['function_call'];
            $rc = $fn($ciniki, $args['tnid'], array(
                'object'=>'ciniki.writingcatalog.item', 
                'object_id'=>$args['writingcatalog_id'],
                ));
            if( $rc['stat'] != 'ok' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.writingcatalog.18', 'msg'=>'Unable to check if item is still be used', 'err'=>$rc['err']));
            }
            if( $rc['used'] != 'no' ) {
                return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.writingcatalog.19', 'msg'=>"Item is still in use. " . $rc['msg']));
            }
        }
    }


    //  
    // Turn off autocommit
    // 
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.writingcatalog');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Remove any additional images
    //
    $strsql = "SELECT id, uuid, image_id "
        . "FROM ciniki_writingcatalog_images "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND writingcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'image');
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.writingcatalog');
        return $rc;
    }
    if( isset($rc['rows']) ) {
        $images = $rc['rows'];
        foreach($images as $rid => $row) {
            $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.writingcatalog.image',
                $row['id'], $row['uuid'], 0x04);
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.writingcatalog');
                return $rc;
            }
        }
    }

    //
    // Remove any additional content
    //
    $strsql = "SELECT id, uuid "
        . "FROM ciniki_writingcatalog_content "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND writingcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_id']) . "' "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'image');
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.writingcatalog');
        return $rc;
    }
    if( isset($rc['rows']) ) {
        $content = $rc['rows'];
        foreach($content as $rid => $row) {
            $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.writingcatalog.content',
                $row['id'], $row['uuid'], 0x04);
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'ciniki.writingcatalog');
                return $rc;
            }
        }
    }

    //
    // Remove any tags
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsDelete');
    $rc = ciniki_core_tagsDelete($ciniki, 'ciniki.writingcatalog', 'tag', $args['tnid'], 
        'ciniki_writingcatalog_tags', 'ciniki_writingcatalog_history', 'writingcatalog_id', $args['writingcatalog_id']);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.writingcatalog');
        return $rc;
    }

    //
    // Remove the writingcatalog item
    //
    $rc = ciniki_core_objectDelete($ciniki, $args['tnid'], 'ciniki.writingcatalog.item',
        $args['writingcatalog_id'], $uuid, 0x06);
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'ciniki.writingcatalog');
        return $rc;
    }

    //
    // Commit the database changes
    //
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.writingcatalog');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    return array('stat'=>'ok');
}
?>
