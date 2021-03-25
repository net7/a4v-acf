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

   update_post_meta($post_id, COLLECTION_ITEM_FIELD_ID, $data['id']);
   update_post_meta($post_id, COLLECTION_ITEM_FIELD_LABEL, $data['label']);
   update_post_meta($post_id, COLLECTION_ITEM_FIELD_IMAGE, $data['image']);
   update_post_meta($post_id, COLLECTION_ITEM_FIELD_TYPE, $data['type']);
   update_post_meta($post_id, COLLECTION_ITEM_FIELD_CLASSIFICATION, $data['classification']);
}

function a4v_write_log($log_dir, $message) {
    $upload_dir = wp_upload_dir();

    $current_log_file =  $upload_dir['basedir'] . "/" .  $log_dir . "/" . date("Y-m-d") . ".log";

    if( !file_exists($upload_dir['basedir'] . "/" .  $log_dir)) {
        mkdir($upload_dir['basedir'] . "/" .  $log_dir);
    }
    file_put_contents($current_log_file,  "\n" . date("Y-m-d H:i:s ")  . $message, FILE_APPEND);
}