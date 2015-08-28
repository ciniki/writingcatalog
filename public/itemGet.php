<?php
//
// Description
// ===========
// This method will return all the information for an item in the writing catalog.
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
function ciniki_writingcatalog_itemGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'writingcatalog_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Item'), 
		'images'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Images'),
		'content'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Content'),
		'categories'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Categories'),
//		'invoices'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Invoices'),
//		'products'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Products'),
		// PDF options
//        'output'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Output Type'), 
//       'layout'=>array('required'=>'no', 'blank'=>'no', 'default'=>'list', 'name'=>'Layout',
//			'validlist'=>array('thumbnails', 'list', 'quad', 'single', 'excel')), 
//       'pagetitle'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Title'), 
//      'fields'=>array('required'=>'no', 'blank'=>'yes', 'default'=>'', 'name'=>'Fields'), 
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
    $rc = ciniki_writingcatalog_checkAccess($ciniki, $args['business_id'], 'ciniki.writingcatalog.itemGet'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }
	$modules = $rc['modules'];

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
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
	$datetime_format = ciniki_users_datetimeFormat($ciniki);
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);

	//
	// Load the status maps for the text description of each status
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'writingcatalog', 'private', 'maps');
	$rc = ciniki_writingcatalog_maps($ciniki);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$maps = $rc['maps'];

	if( $args['writingcatalog_id'] == '0' ) {
		$item = array(
			'id'=>'0',
			'title'=>'',
			'subtitle'=>'',
			'permalink'=>'',
			'image_id'=>'0',
			'type'=>'30',
			'type_text'=>'Book',
			'website'=>'',
			'webflags'=>'0',
			'synopsis'=>'',
			'description'=>'',
			'notes'=>'',
			'categories'=>'',
			);
	} else {
		$strsql = "SELECT ciniki_writingcatalog.id, "
			. "ciniki_writingcatalog.title, "
			. "ciniki_writingcatalog.subtitle, "
			. "ciniki_writingcatalog.permalink, "
			. "ciniki_writingcatalog.image_id, "
			. "ciniki_writingcatalog.type, "
			. "ciniki_writingcatalog.type AS type_text, "
			. "IF((ciniki_writingcatalog.webflags&0x01)=0x01, 'visible', 'hidden') AS website, "
			. "webflags, catalog_number, year, month, day, "
			. "ciniki_writingcatalog.synopsis, "
			. "ciniki_writingcatalog.description, "
			. "ciniki_writingcatalog.notes "
			. "FROM ciniki_writingcatalog "
			. "WHERE ciniki_writingcatalog.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND ciniki_writingcatalog.id = '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_id']) . "' "
			. "";

		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.writingcatalog', array(
			array('container'=>'items', 'fname'=>'id', 'name'=>'item',
				'fields'=>array('id', 'title', 'subtitle', 'permalink', 'image_id', 'type', 'type_text',
					'webflags', 'catalog_number', 'year', 'month', 'day', 
					'website', 'synopsis', 'description', 'notes'),
				'maps'=>array('type_text'=>$maps['item']['type'])),
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( !isset($rc['items']) ) {
			return array('stat'=>'ok', 'err'=>array('pkg'=>'ciniki', 'code'=>'2461', 'msg'=>'Unable to find item'));
		}
		$item = $rc['items'][0]['item'];

		//
		// Get the categories for the item
		//
		if( ($ciniki['business']['modules']['ciniki.writingcatalog']['flags']&0x04) > 0 ) {
			$strsql = "SELECT tag_type, tag_name AS lists "
				. "FROM ciniki_writingcatalog_tags "
				. "WHERE writingcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_id']) . "' "
				. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "ORDER BY tag_type, tag_name "
				. "";
			$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.writingcatalog', array(
				array('container'=>'tags', 'fname'=>'tag_type', 'name'=>'tags',
					'fields'=>array('tag_type', 'lists'), 'dlists'=>array('lists'=>'::')),
				));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['tags']) ) {
				foreach($rc['tags'] as $tags) {
					if( $tags['tags']['tag_type'] == 10 ) {
						$item['categories'] = $tags['tags']['lists'];
					}
				}
			}
		}

		//
		// Get the additional images if requested
		//
		if( isset($args['images']) && $args['images'] == 'yes' ) {
			ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
			$strsql = "SELECT ciniki_writingcatalog_images.id, "
				. "ciniki_writingcatalog_images.image_id, "
				. "ciniki_writingcatalog_images.name, "
				. "ciniki_writingcatalog_images.sequence, "
				. "ciniki_writingcatalog_images.webflags, "
				. "ciniki_writingcatalog_images.description "
				. "FROM ciniki_writingcatalog_images "
				. "WHERE ciniki_writingcatalog_images.writingcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_id']) . "' "
				. "AND ciniki_writingcatalog_images.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "ORDER BY ciniki_writingcatalog_images.sequence, ciniki_writingcatalog_images.date_added, ciniki_writingcatalog_images.name "
				. "";
			$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.writingcatalog', array(
				array('container'=>'images', 'fname'=>'id', 'name'=>'image',
					'fields'=>array('id', 'image_id', 'name', 'sequence', 'webflags', 'description')),
				));
			if( $rc['stat'] != 'ok' ) {	
				return $rc;
			}
			if( isset($rc['images']) ) {
				$item['images'] = $rc['images'];
				foreach($item['images'] as $inum => $img) {
					if( isset($img['image']['image_id']) && $img['image']['image_id'] > 0 ) {
						$rc = ciniki_images_loadCacheThumbnail($ciniki, $args['business_id'], $img['image']['image_id'], 75);
						if( $rc['stat'] != 'ok' ) {
							return $rc;
						}
						$item['images'][$inum]['image']['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
					}
				}
			}
		}

		//
		// Get the additional content if requested
		//
		if( isset($args['content']) && $args['content'] == 'yes' ) {
			$strsql = "SELECT id, title, permalink, content_type "
				. "FROM ciniki_writingcatalog_content "
				. "WHERE writingcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_id']) . "' "
				. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "ORDER BY ciniki_writingcatalog_content.content_type, ciniki_writingcatalog_content.sequence, ciniki_writingcatalog_content.title "
				. "";
			$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.writingcatalog', array(
				array('container'=>'types', 'fname'=>'content_type', 'name'=>'type',
					'fields'=>array('content_type')),
				array('container'=>'content', 'fname'=>'id', 'name'=>'content',
					'fields'=>array('id', 'title', 'permalink')),
				));
			if( $rc['stat'] != 'ok' ) {	
				return $rc;
			}
			if( isset($rc['types']) ) {
				foreach($rc['types'] as $tid => $type) {
					if( $type['type']['content_type'] == '10' ) {
						$item['reviews'] = $type['type']['content'];
					} elseif( $type['type']['content_type'] == '20' ) {
						$item['samples'] = $type['type']['content'];
					} elseif( $type['type']['content_type'] == '30' ) {
						$item['orderinginfo'] = $type['type']['content'];
					}
				}
			}
		}
	}

	//
	// Get the product list if requested
	//
//	if( isset($args['products']) && $args['products'] == 'yes' 
//		&& ($ciniki['business']['modules']['ciniki.writingcatalog']['flags']&0x02) > 0
//		) {
//		$strsql = "SELECT id, name, inventory, price "
//			. "FROM ciniki_writingcatalog_products "
//			. "WHERE writingcatalog_id = '" . ciniki_core_dbQuote($ciniki, $args['writingcatalog_id']) . "' "
//			. "AND business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
//			. "ORDER BY ciniki_writingcatalog_products.name "
//			. "";
//		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.writingcatalog', array(
//			array('container'=>'products', 'fname'=>'id', 'name'=>'product',
//				'fields'=>array('id', 'name', 'inventory', 'price')),
//			));
//		if( $rc['stat'] != 'ok' ) {	
//			return $rc;
//		}
//		if( isset($rc['products']) ) {
//			$item['products'] = $rc['products'];
//			foreach($item['products'] as $pid => $product) {
//				$item['products'][$pid]['product']['price'] = numfmt_format_currency($intl_currency_fmt, $product['product']['price'], $intl_currency);
//			}
//		}
//	}

	$rsp = array('stat'=>'ok', 'item'=>$item);

	//
	// Get any invoices for this piece of writing
	//
//	if( isset($args['invoices']) && $args['invoices'] == 'yes' && isset($modules['ciniki.sapos']) ) {
//		ciniki_core_loadMethod($ciniki, 'ciniki', 'sapos', 'private', 'objectInvoices');
//		$rc = ciniki_sapos_objectInvoices($ciniki, $args['business_id'], 'ciniki.writingcatalog.item', $args['writingcatalog_id']);
//		if( $rc['stat'] != 'ok' ) {
//			return $rc;
//		}
//		if( isset($rc['invoices']) ) {
//			$item['invoices'] = $rc['invoices'];
//		}
//	}

	//
	// Check if all tags should be returned
	//
	$rsp['categories'] = array();
	if( ($ciniki['business']['modules']['ciniki.writingcatalog']['flags']&0x04) > 0
		&& isset($args['categories']) && $args['categories'] == 'yes' 
		) {
		//
		// Get the available tags
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'tagsList');
		$rc = ciniki_core_tagsList($ciniki, 'ciniki.writingcatalog', $args['business_id'], 
			'ciniki_writingcatalog_tags', 10);
		if( $rc['stat'] != 'ok' ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'2462', 'msg'=>'Unable to get list of categories', 'err'=>$rc['err']));
		}
		if( isset($rc['tags']) ) {
			$rsp['categories'] = $rc['tags'];
		}
	}

	return $rsp;
}
?>
