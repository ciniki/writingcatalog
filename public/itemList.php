<?php
//
// Description
// ===========
// This method will list the writing catalog items.
//
// Arguments
// ---------
// 
// Returns
// -------
//
function ciniki_writingcatalog_itemList($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
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
    $rc = ciniki_writingcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.writingcatalog.itemList'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    //
    // Load the status maps for the text description of each status
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'writingcatalog', 'private', 'maps');
    $rc = ciniki_writingcatalog_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Load INTL settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');

    $strsql = "SELECT ciniki_writingcatalog.id, "
        . "ciniki_writingcatalog.title, "
        . "ciniki_writingcatalog.subtitle, "
        . "ciniki_writingcatalog.permalink, "
        . "ciniki_writingcatalog.type, "
        . "ciniki_writingcatalog.type AS type_text, "
        . "ciniki_writingcatalog.webflags, "
        . "ciniki_writingcatalog.image_id, "
        . "ciniki_writingcatalog.synopsis "
        . "FROM ciniki_writingcatalog "
        . "WHERE ciniki_writingcatalog.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "ORDER BY ciniki_writingcatalog.type, ciniki_writingcatalog.title "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
    $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.writingcatalog', array(
        array('container'=>'items', 'fname'=>'id', 'name'=>'item',
            'fields'=>array('id', 'title', 'subtitle', 'type_text'),
            'maps'=>array('type_text'=>$maps['item']['type'])),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['items']) ) {
        $items = array();
    } else {
        $items = $rc['items'];
    }

    //
    // Add thumbnail information into list
    //
    if( count($items) > 0 ) {
        ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
        foreach($items as $iid => $item) {
            if( isset($item['item']['image_id']) && $item['item']['image_id'] > 0 ) {
                $rc = ciniki_images_loadCacheThumbnail($ciniki, $args['tnid'], $item['item']['image_id'], 75);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                $items[$iid]['item']['image'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
            }
        }
    }

    return array('stat'=>'ok', 'items'=>$items);
}
?>
