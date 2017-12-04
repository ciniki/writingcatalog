<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to add the content to.
// content_id:          The ID of the content to get.
//
// Returns
// -------
//
function ciniki_writingcatalog_contentGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'content_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Content'),
        'writingcatalog_id'=>array('required'=>'no', 'blank'=>'no', 'default'=>'0', 'name'=>'Item'),
        'content_type'=>array('required'=>'no', 'blank'=>'no', 'default'=>'10', 'name'=>'Type'),
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
    $rc = ciniki_writingcatalog_checkAccess($ciniki, $args['tnid'], 'ciniki.writingcatalog.contentGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);

    if( $args['content_id'] == '0' ) {
        $content = array(
            'title'=>'',
            'permalink'=>'',
            'content_type'=>$args['content_type'],
            'sequence'=>1,
            'image_id'=>'0',
            'content'=>'',
            'paypal_business'=>'',
            'paypal_price'=>'',
            'paypal_currency'=>'CAD',
            );
        $strsql = "SELECT MAX(sequence)+1 AS next_sequence "
            . "FROM ciniki_writingcatalog_content "
            . "WHERE ciniki_writingcatalog_content.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_writingcatalog_content.writingcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_id']) . "' "
            . "AND ciniki_writingcatalog_content.content_type = '" . ciniki_core_dbQuote($ciniki, $args['content_type']) . "' "
            . "";
        $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.writingcatalog', 'sequence');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['sequence']['next_sequence']) ) {
            $content['sequence'] = $rc['sequence']['next_sequence'];
        }
    } else {
        //
        // Get the main information
        //
        $strsql = "SELECT ciniki_writingcatalog_content.id, "
            . "ciniki_writingcatalog_content.title, "
            . "ciniki_writingcatalog_content.permalink, "
            . "ciniki_writingcatalog_content.content_type, "
            . "ciniki_writingcatalog_content.sequence, "
            . "ciniki_writingcatalog_content.image_id, "
            . "ciniki_writingcatalog_content.content, "
            . "ciniki_writingcatalog_content.paypal_business, "
            . "ciniki_writingcatalog_content.paypal_price, "
            . "ciniki_writingcatalog_content.paypal_currency "
            . "FROM ciniki_writingcatalog_content "
            . "WHERE ciniki_writingcatalog_content.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_writingcatalog_content.id = '" . ciniki_core_dbQuote($ciniki, $args['content_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
        $rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.writingcatalog', array(
            array('container'=>'content', 'fname'=>'id', 'name'=>'content',
                'fields'=>array('id', 'title', 'permalink', 'content_type', 'sequence', 'image_id', 'content',
                    'paypal_business', 'paypal_price', 'paypal_currency')),
            ));
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( !isset($rc['content']) ) {
            return array('stat'=>'ok', 'err'=>array('code'=>'ciniki.writingcatalog.6', 'msg'=>'Unable to find content'));
        }
        $content = $rc['content'][0]['content'];
    }
    
    return array('stat'=>'ok', 'content'=>$content);
}
?>
