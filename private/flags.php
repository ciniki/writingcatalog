<?php
//
// Description
// -----------
// The module flags
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_writingcatalog_flags($ciniki, $modules) {
    //
    // The flags should be kept the same as art catalog incase modules should be merged in the future
    //
    $flags = array(
        // 0x01
//      array('flag'=>array('bit'=>'1', 'name'=>'Lists')),
        array('flag'=>array('bit'=>'2', 'name'=>'Products')),
        array('flag'=>array('bit'=>'3', 'name'=>'Categories')),
//      array('flag'=>array('bit'=>'4', 'name'=>'')),
        // 0x10
//      array('flag'=>array('bit'=>'5', 'name'=>'')),
//      array('flag'=>array('bit'=>'6', 'name'=>'')),
//      array('flag'=>array('bit'=>'7', 'name'=>'')),
//      array('flag'=>array('bit'=>'8', 'name'=>'')),
        // 0x0100
        array('flag'=>array('bit'=>'9', 'name'=>'Reviews')),
        array('flag'=>array('bit'=>'10', 'name'=>'Samples')),
        array('flag'=>array('bit'=>'11', 'name'=>'Ordering')),
        array('flag'=>array('bit'=>'12', 'name'=>'Images')),
        );

    return array('stat'=>'ok', 'flags'=>$flags);
}
?>
