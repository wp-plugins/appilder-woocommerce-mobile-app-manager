<?php
/**
 * @param int|WC_Product $product
 */
function get_product_short_details($product){
    if(!is_a($product,"WC_Product"))
        $product =  get_product($product);
    $details['product_id'] = $product->id;
    $details['product_total_stock'] =(int)$product->total_stock;
    $details['product_title'] = $product->post->post_title;
    $details['featured_src'] = wp_get_attachment_image_src(get_post_thumbnail_id($product->post->ID));
    $details['featured_src'] = $details['featured_src'][0];
    $details['product_type'] = $product->product_type;
    $details['product_price'] = $product->get_price();
    $details['product_regular_price'] = $product->get_regular_price();
    $details['product_sale_price'] = $product->get_sale_price();
    return $details;
}
