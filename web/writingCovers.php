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
function ciniki_writingcatalog_web_writingCovers($ciniki, $settings, $business_id, $args) {

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
    // Get the list of books.
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
        . "'yes' AS is_details, "
        . "UNIX_TIMESTAMP(ciniki_writingcatalog.last_updated) AS last_updated "
        . "FROM ciniki_writingcatalog "
        . "WHERE ciniki_writingcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND (ciniki_writingcatalog.webflags&0x01) = 0x01 "
        . "ORDER BY ciniki_writingcatalog.type, ciniki_writingcatalog.title "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.writingcatalog', array(
        array('container'=>'categories', 'fname'=>'type',
            'fields'=>array('type', 'type_text'),
            'maps'=>array('type_text'=>$maps['item']['type']),
            ),
        array('container'=>'list', 'fname'=>'id',
            'fields'=>array('id', 'title', 'subtitle', 'type', 'type_text', 'permalink', 'image_id', 'synopsis', 'is_details', 'last_updated'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $categories = array();
    if( isset($rc['categories']) ) {
        $categories = $rc['categories'];
    }

    return array('stat'=>'ok', 'categories'=>$categories);
}
?>
