<?php
//
// Description
// -----------
// This function will generate the writing catalog page for the business.
//
// Arguments
// ---------
// ciniki:
// settings:        The web settings structure, similar to ciniki variable but only web specific information.
//
// Returns
// -------
//
function ciniki_writingcatalog_web_processRequest($ciniki, $settings, $business_id, $args) {

    //
    // Check to make sure the module is enabled
    //
    if( !isset($ciniki['business']['modules']['ciniki.writingcatalog']) ) {
        return array('stat'=>'404', 'err'=>array('code'=>'ciniki.writingcatalog.24', 'msg'=>"I'm sorry, the page you requested does not exist."));
    }
    $page = array(
        'title'=>$args['page_title'],
        'breadcrumbs'=>$args['breadcrumbs'],
        'blocks'=>array(),
        'submenu'=>array(),
        );

    //
    // Setup titles
    //
    if( isset($settings['page-writings-title']) && $settings['page-writings-title'] !='' ) {
        $module_title = $settings['page-writings-title'];
    } elseif( isset($settings['page-writings-name']) && $settings['page-writings-name'] != '' ) {
        $module_title = $settings['page-writings-name'];
    } elseif( isset($args['page_title']) ) {
        $module_title = $args['page_title'];
    } else {
        $module_title = 'Events';
    }

    $ciniki['response']['head']['og']['url'] = $args['domain_base_url'];

    //
    // Setup the base url as the base url for this page. This may be altered below
    // as the uri_split is processed, but we do not want to alter the original passed in.
    //
    $base_url = $args['base_url'];

    //
    // Parse the url to determine what was requested
    //
    $display = 'list';

    if( isset($args['uri_split'][0]) && $args['uri_split'][0] != '' ) {
        $writing_permalink = $args['uri_split'][0];

        //
        // Get the writing information
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'writingcatalog', 'web', 'writingDetails');
        $rc = ciniki_writingcatalog_web_writingDetails($ciniki, $settings, $ciniki['request']['business_id'], $writing_permalink);
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $item = $rc['item'];

        $page['title'] = $item['title'];

        if( isset($item['subtitle']) && $item['subtitle'] != '' ) {
            $page['subtitle'] = $item['subtitle'];
        }
        $base_url .= '/' . $writing_permalink;
        $page['breadcrumbs'][] = array('name'=>$item['title'], 'url'=>$base_url);

        if( isset($item['synopsis']) && $item['synopsis'] != '' ) {
            $ciniki['response']['head']['og']['description'] = strip_tags($item['synopsis']);
        } elseif( isset($item['description']) && $item['description'] != '' ) {
            $ciniki['response']['head']['og']['description'] = strip_tags($item['description']);
        }

        //
        // Check if we are the display a sample
        //
        if( isset($args['uri_split'][1]) && $args['uri_split'][1] == 'sample'
            && isset($args['uri_split'][2]) && $args['uri_split'][2] != ''
            ) {
            $sample_permalink = $args['uri_split'][2];

            //
            // Get the writing information
            //
            ciniki_core_loadMethod($ciniki, 'ciniki', 'writingcatalog', 'web', 'writingSample');
            $rc = ciniki_writingcatalog_web_writingSample($ciniki, $settings, $ciniki['request']['business_id'], $writing_permalink, $sample_permalink);
            if( $rc['stat'] != 'ok' ) {
                return $rc;
            }
            $sample = $rc['item'];
            $page['title'] = $sample['sample']['title'];
            $page['breadcrumbs'][] = array('name'=>$sample['sample']['title'], 'url'=>$base_url . '/sample/' . $sample_permalink);

            if( isset($sample['subtitle']) && $sample['subtitle'] != '' ) {
                $page['subtitle'] = $sample['subtitle'];
            }

            if( isset($sample['image_id']) && $sample['image_id'] > 0 ) {
                $page['blocks'][] = array('type'=>'asideimage', 'section'=>'primary-image', 'primary'=>'yes', 'image_id'=>$sample['image_id'], 'title'=>$sample['title'], 'caption'    =>'');
            }

            if( isset($sample['synopsis']) && $sample['synopsis'] != '' ) {
                $ciniki['response']['head']['og']['description'] = strip_tags($sample['synopsis']);
            } elseif( isset($sample['description']) && $sample['description'] != '' ) {
                $ciniki['response']['head']['og']['description'] = strip_tags($sample['description']);
            }

            //
            // Add the content
            //
            $page['blocks'][] = array('type'=>'content', 'section'=>'content', 'title'=>'', 'content'=>$sample['sample']['content']);
        }

        //
        // Check if we are to display a gallery image
        //
        elseif( isset($args['uri_split'][1]) && $args['uri_split'][1] == 'gallery'
            && isset($args['uri_split'][2]) && $args['uri_split'][2] != ''
            ) {
            $image_permalink = $args['uri_split'][2];

            if( !isset($item['images']) || count($item['images']) < 1 ) {
            $page['blocks'][] = array('type'=>'message', 'section'=>'item-image', 'content'=>"I'm sorry, but we can't seem to find the image you requested.");
            } else {
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'galleryFindNextPrev');
                $rc = ciniki_web_galleryFindNextPrev($ciniki, $item['images'], $image_permalink);
                if( $rc['stat'] != 'ok' ) {
                    return $rc;
                }
                if( $rc['img'] == NULL ) {
                    $page['blocks'][] = array('type'=>'message', 'section'=>'item-image', 'content'=>"I'm sorry, but we can't seem to find the image you requested.");
                } else {
                    $page['breadcrumbs'][] = array('name'=>$rc['img']['title'], 'url'=>$base_url . '/gallery/' . $image_permalink);
                    if( $rc['img']['title'] != '' ) {
                        $page['title'] .= ' - ' . $rc['img']['title'];
                    }
                    $block = array('type'=>'galleryimage', 'section'=>'item-image', 'primary'=>'yes', 'image'=>$rc['img']);
                    if( $rc['prev'] != null ) {
                        $block['prev'] = array('url'=>$base_url . '/gallery/' . $rc['prev']['permalink'], 'image_id'=>$rc['prev']['image_id']);
                    }
                    if( $rc['next'] != null ) {
                        $block['next'] = array('url'=>$base_url . '/gallery/' . $rc['next']['permalink'], 'image_id'=>$rc['next']['image_id']);
                    }
                    $page['blocks'][] = $block;
                }
            }
        }

        //
        // Display the writing
        //
        else {
            if( isset($item['image_id']) && $item['image_id'] > 0 ) {
                $page['blocks'][] = array('type'=>'asideimage', 'section'=>'primary-image', 'primary'=>'yes', 'image_id'=>$item['image_id'], 'title'=>$item['title'], 'caption'=>''    );
            }
            $page['blocks'][] = array('type'=>'content', 'section'=>'content', 'title'=>'',
                'content'=>(isset($item['description'])&&$item['description']!='')?$item['description']:$item['synopsis']);

            $page['blocks'][] = array('type'=>'content', 'section'=>'content', 'title'=>'', 'content'=>$item['content']);

            //
            // Check if there are reviews
            //
            if( isset($item['reviews']) && count($item['reviews']) > 0 ) {
                $reviews = '';
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
                foreach($item['reviews'] as $review) {
                    $reviews .= "<blockquote class='quote-text'>";
                    $rc = ciniki_web_processContent($ciniki, $settings, $review['content']);
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    $reviews .= $rc['content'];
                    if( isset($review['title']) && $review['title'] != '' ) {
                        $reviews .= "<cite class='quote-author alignright'>" . $review['title'] . "</cite>";
                    }
                    $reviews .= "</blockquote>";
                }
                $page['blocks'][] = array('type'=>'content', 'section'=>'content', 'title'=>'Reviews', 'html'=>$reviews);
            }

            //
            // Check if there are Samples
            //
            if( isset($item['samples']) && count($item['samples']) > 0 ) {
                $samples = '';
                $samples .= "<p>";
                foreach($item['samples'] as $sample) {
                    $samples .= "<a href='$base_url/sample/" . $sample['permalink'] . "'>" . $sample['title'] . "</a><br/>";
                }
                $samples .= "</p>";
                $page['blocks'][] = array('type'=>'content', 'section'=>'content', 'title'=>'Samples', 'html'=>$samples);
            }

            //
            // Check if there are purchasing options
            //
            if( isset($item['orderinfo']) && count($item['orderinfo']) > 0 ) {
                $content = '';
                ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'private', 'processContent');
                foreach($item['orderinfo'] as $orderinfo) {
                    if( isset($orderinfo['title']) && $orderinfo['title'] != '' ) {
                        $content .= "<b>" . $orderinfo['title'] . "</b>";
                    }
                    $rc = ciniki_web_processContent($ciniki, $settings, $orderinfo['content']);
                    if( $rc['stat'] != 'ok' ) {
                        return $rc;
                    }
                    $content .= $rc['content'];
                    if( isset($orderinfo['paypal_business']) && $orderinfo['paypal_business'] != '' ) {
                        $content .= "<form style='display:inline-block;width:10em;' target='paypal' action='https://www.paypal.com/cgi-bin/webscr' method='post'>"
                            . "<input type='hidden' name='business' value='" . $orderinfo['paypal_business'] . "'>"
                            . "<input type='hidden' name='cmd' value='_cart'>"
                            . "<input type='hidden' name='add' value='1'>"
                            . "<input type='hidden' name='item_name' value='" . $item['title'] . "'>"
                            . "<input type='hidden' name='amount' value='" . $orderinfo['paypal_price'] . "'>"
                            . "<input type='hidden' name='currency_code' value='" . $orderinfo['paypal_currency'] . "'>"
                            . "<input type='image' name='submit' border='0' src='https://www.paypalobjects.com/en_US/i/btn/btn_cart_LG.gif' alt='PayPal - The safer, easier way to     pay online'>"
                            . "<img alt='' border='0' width='1' height='1' src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif' >"
                            . "</form>";
                        $content .= "<form style='display:inline-block;width:10em;' target='paypal' action='https://www.paypal.com/cgi-bin/webscr' method='post'>"
                            . "<input type='hidden' name='business' value='" . $orderinfo['paypal_business'] . "'>"
                            . "<input type='hidden' name='cmd' value='_cart'>"
                            . "<input type='hidden' name='display' value='1'>"
                            . "<input type='image' name='submit' border='0' src='https://www.paypalobjects.com/en_US/i/btn/btn_viewcart_LG.gif' alt='PayPal - The safer, easier way     to pay online'>"
                            . "<img alt='' border='0' width='1' height='1' src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif' >"
                            . "</form>";
                        $content .= "<br/><br/>";
                    }
                }
                $page['blocks'][] = array('type'=>'content', 'section'=>'content', 'title'=>'Purchasing Options', 'html'=>$content);
            }

            //
            // Check if there are images to display
            //
            if( isset($item['images']) && count($item['images']) > 0 ) {
                $page['blocks'][] = array('type'=>'gallery', 'section'=>'gallery', 'title'=>'Additional Images', 'base_url'=>$base_url . '/gallery', 'images'=>$item['images']);
            }
        }
    }

    //
    // Display the list of writings
    //
    else {
        //
        // Get the list of categories
        //
        ciniki_core_loadMethod($ciniki, 'ciniki', 'writingcatalog', 'web', 'writingList');
        $rc = ciniki_writingcatalog_web_writingList($ciniki, $settings, $business_id, array());
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        $page['blocks'][] = array('type'=>'cilist', 'base_url'=>$base_url, 'categories'=>$rc['categories'], 'image_version'=>'original', 'image_width'=>'150');
    }

    return array('stat'=>'ok', 'page'=>$page);
}
?>
