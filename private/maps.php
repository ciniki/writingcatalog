<?php
//
// Description
// -----------
// The mappings for int fields to text.
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_writingcatalog_maps($ciniki) {

	$maps = array();
	$maps['item'] = array(
		'typecode'=>array(
			''=>'unknown',
			'0'=>'unknown',
			'30'=>'book',
			'31'=>'shortstory',
			'32'=>'article',
			),
		'typepermalinks'=>array(
			'30'=>'books',
			'31'=>'shortstories',
			'32'=>'articles',
			),	
		'type'=>array(
			'30'=>'Book',
			'40'=>'Short Story',
			'50'=>'Articles',
			'60'=>'Poetry',
			),
		);
	
	return array('stat'=>'ok', 'maps'=>$maps);
}
?>
