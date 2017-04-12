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
function ciniki_writingcatalog_web_writingDetails($ciniki, $settings, $business_id, $permalink) {

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
        . "ciniki_writingcatalog.description, "
        . "ciniki_writingcatalog.content, "
        . "ciniki_writingcatalog.inspiration, "
        . "ciniki_writingcatalog.awards "
        . "FROM ciniki_writingcatalog "
        . "WHERE ciniki_writingcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND ciniki_writingcatalog.permalink = '" . ciniki_core_dbQuote($ciniki, $permalink) . "' "
        . "AND (ciniki_writingcatalog.webflags&0x01) = 0x01 "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.writingcatalog', array(
        array('container'=>'items', 'fname'=>'id',
            'fields'=>array('id', 'title', 'subtitle', 'type', 'type_text', 'permalink', 'image_id', 'synopsis', 'description', 'content', 'inspiration', 'awards'),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['items']) || count($rc['items']) != 1 ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.writingcatalog.25', 'msg'=>"I'm sorry, but we can't seem to find the item you requested."));
    }
    $item = array_pop($rc['items']);

    //
    // Get the extra content for the book
    //
    $strsql = "SELECT id, title, permalink, content_type, image_id, content, "
        . "paypal_business, FORMAT(paypal_price, 2) AS paypal_price, paypal_currency "
        . "FROM ciniki_writingcatalog_content "
        . "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND ciniki_writingcatalog_content.writingcatalog_id = '" . ciniki_core_dbQuote($ciniki, $item['id']) . "' "
        . "ORDER BY content_type, sequence "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.writingcatalog', array(
        array('container'=>'content_types', 'fname'=>'content_type',
            'fields'=>array('content_type')),
        array('container'=>'content', 'fname'=>'id', 
            'fields'=>array('id', 'title', 'permalink', 'content_type', 'image_id', 'content', 
                'paypal_business', 'paypal_price', 'paypal_currency')),
    ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['content_types']['10']) ) {
        $item['reviews'] = $rc['content_types']['10']['content'];
    }
    if( isset($rc['content_types']['20']) ) {
        $item['samples'] = $rc['content_types']['20']['content'];
    }
    if( isset($rc['content_types']['30']) ) {
        $item['orderinfo'] = $rc['content_types']['30']['content'];
    }

    //
    // Get any images 
    //
    $strsql = "SELECT id, image_id, name, permalink, sequence, webflags, description, "
        . "UNIX_TIMESTAMP(last_updated) AS last_updated "
        . "FROM ciniki_writingcatalog_images "
        . "WHERE writingcatalog_id = '" . ciniki_core_dbQuote($ciniki, $item['id']) . "' "
        . "AND business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND (webflags&0x01) = 1 "        // Visible images
        . "ORDER BY sequence, date_added, name "
        . "";
    $rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.writingcatalog', array(
        array('container'=>'images', 'fname'=>'id', 
            'fields'=>array('id', 'image_id', 'title'=>'name', 'permalink', 'sequence', 'webflags', 
                'description', 'last_updated')),
        ));
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
    if( isset($rc['images']) ) {
        $item['images'] = $rc['images'];
    } else {
        $item['images'] = array();
    }

    return array('stat'=>'ok', 'item'=>$item);
}
?>
