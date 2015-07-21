<?php
//
// Description
// -----------
// The objects for the writingcatalog.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_writingcatalog_objects($ciniki) {
	//
	// Object definitions
	//
	$objects = array();
	$objects['item'] = array(
		'name'=>'Writing Catalog Item',
		'sync'=>'yes',
		'table'=>'ciniki_writingcatalog',
		'fields'=>array(
			'name'=>array(),
			'permalink'=>array(),
			'type'=>array(),
			'webflags'=>array(),
			'image_id'=>array('ref'=>'ciniki.images.image'),
			'catalog_number'=>array(),
			'category'=>array(),
			'year'=>array(),
			'month'=>array(),
			'day'=>array(),
			'synopsis'=>array(),
			'description'=>array(),
			'awards'=>array(),
			'notes'=>array(),
			'inspiration'=>array(),
			'user_id'=>array('ref'=>'ciniki.users.user'),
			),
		'history_table'=>'ciniki_writingcatalog_history',
		);
	$objects['image'] = array(
		'name'=>'Image',
		'sync'=>'yes',
		'table'=>'ciniki_writingcatalog_images',
		'fields'=>array(
			'writingcatalog_id'=>array('ref'=>'ciniki.writingcatalog.item'),
			'name'=>array(),
			'permalink'=>array(),
			'webflags'=>array(),
			'sequence'=>array(),
			'image_id'=>array('ref'=>'ciniki.images.image'),
			'description'=>array(),
			),
		'history_table'=>'ciniki_writingcatalog_history',
		);
	$objects['tag'] = array(
		'name'=>'Writing Catalog Tag',
		'sync'=>'yes',
		'table'=>'ciniki_writingcatalog_tags',
		'fields'=>array(
			'writingcatalog_id'=>array('ref'=>'ciniki.writingcatalog.item'),
			'tag_type'=>array(),
			'tag_name'=>array(),
			),
		'history_table'=>'ciniki_writingcatalog_history',
		);
	$objects['product'] = array(
		'name'=>'Product',
		'sync'=>'yes',
		'table'=>'ciniki_writingcatalog_products',
		'fields'=>array(
			'writingcatalog_id'=>array('ref'=>'ciniki.writingcatalog.item'),
			'name'=>array(),
			'permalink'=>array(),
			'flags'=>array('default'=>'0'),
			'sequence'=>array('default'=>'0'),
			'image_id'=>array('default'=>'0'),
			'synopsis'=>array('default'=>''),
			'description'=>array('default'=>''),
			'price'=>array('default'=>'0'),
			'taxtype_id'=>array('default'=>'0'),
			'inventory'=>array('default'=>'0'),
			),
		'history_table'=>'ciniki_writingcatalog_history',
		);
	
	return array('stat'=>'ok', 'objects'=>$objects);
}
?>
