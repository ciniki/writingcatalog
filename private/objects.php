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
			'title'=>array(),
			'subtitle'=>array('default'=>''),
			'permalink'=>array(),
			'type'=>array(),
			'webflags'=>array(),
			'image_id'=>array('ref'=>'ciniki.images.image'),
			'catalog_number'=>array(),
			'year'=>array(),
			'month'=>array(),
			'day'=>array(),
			'synopsis'=>array(),
			'description'=>array(),
			'notes'=>array(),
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
	$objects['content'] = array(
		'name'=>'Content',
		'sync'=>'yes',
		'table'=>'ciniki_writingcatalog_content',
		'fields'=>array(
			'writingcatalog_id'=>array('ref'=>'ciniki.writingcatalog.item'),
			'title'=>array(),
			'permalink'=>array('default'=>''),
			'content_type'=>array('default'=>'0'),
			'sequence'=>array('default'=>'0'),
			'image_id'=>array('default'=>'0'),
			'content'=>array('default'=>''),
			),
		'history_table'=>'ciniki_writingcatalog_history',
		);
	
	return array('stat'=>'ok', 'objects'=>$objects);
}
?>
