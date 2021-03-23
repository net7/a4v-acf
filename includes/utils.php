<?php

function a4v_get_arianna_item_metafields($post_id){
    $item = [];
    $item[COLLECTION_ITEM_FIELD_ID] = get_post_meta($post_id, COLLECTION_ITEM_FIELD_ID, true);
    $item[COLLECTION_ITEM_FIELD_LABEL] = get_post_meta($post_id, COLLECTION_ITEM_FIELD_LABEL, true);
    $item[COLLECTION_ITEM_FIELD_IMAGE] = get_post_meta($post_id, COLLECTION_ITEM_FIELD_IMAGE, true);
    $item[COLLECTION_ITEM_FIELD_TYPE] = get_post_meta($post_id, COLLECTION_ITEM_FIELD_TYPE, true);
    $item[COLLECTION_ITEM_FIELD_CLASSIFICATION] = get_post_meta($post_id, COLLECTION_ITEM_FIELD_CLASSIFICATION, true);
    return $item;    
}

function a4v_set_arianna_item_metafields($post_id, $data){

    foreach ($data as $d => $value){
        $data[$d] = $value !== "undefined" &&  $value !== "null" ? $value : null;
    }

   add_post_meta($post_id, COLLECTION_ITEM_FIELD_ID, $data['id'], true);
   add_post_meta($post_id, COLLECTION_ITEM_FIELD_LABEL, $data['label'], true);
   add_post_meta($post_id, COLLECTION_ITEM_FIELD_IMAGE, $data['image'], true);
   add_post_meta($post_id, COLLECTION_ITEM_FIELD_TYPE, $data['type'], true);
   add_post_meta($post_id, COLLECTION_ITEM_FIELD_CLASSIFICATION, $data['classification'], true);
}

function a4v_write_log($log_dir, $message) {
    $current_log_file =  $log_dir . "/" . date("Y-m-d") . ".log";
    file_put_contents($current_log_file,  "\n" . date("Y-m-d H:i:s ")  . $message, FILE_APPEND);
}