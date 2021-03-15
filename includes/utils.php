<?php

function a4v_get_arianna_item_metafields($post_id){
    $item = [];
    $item[COLLECTION_ITEM_FIELD_ID] = get_post_meta($post_id, COLLECTION_ITEM_FIELD_ID, true);
    $item[COLLECTION_ITEM_FIELD_LABEL] = get_post_meta($post_id, COLLECTION_ITEM_FIELD_LABEL, true);
    $item[COLLECTION_ITEM_FIELD_IMAGE] = get_post_meta($post_id, COLLECTION_ITEM_FIELD_IMAGE, true);
    return $item;    
}

function a4v_set_arianna_item_metafields($post_id, $data){
   add_post_meta($post_id, COLLECTION_ITEM_FIELD_ID, $data['id'], true);
   add_post_meta($post_id, COLLECTION_ITEM_FIELD_LABEL, $data['label'], true);
   add_post_meta($post_id, COLLECTION_ITEM_FIELD_IMAGE, $data['image'], true);
}

