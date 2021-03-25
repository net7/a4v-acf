<?php

/**
 * The wp-clicommand of the plugin.
 *
 * @link       http://netseven.it
 * @since      1.0.0
 *
 * @package    A4v_Collection
 */

class A4v_Collection_Cli {

    const MURUCA_ELASTICSEARCH_LOG_DIR = __DIR__ . "/logs";
    
    // Add the command.
    public function sync_collections() {
        $posts = get_posts(
            array(
                "post_type" => [ MURUCA_CORE_PREFIX . "_a4v-item"],
                "posts_per_page" => -1,
                "fields" => "ids"
            )
        );
        $result = [];
        foreach( $posts as $pid) {
            $result[get_post_meta($pid, COLLECTION_ITEM_FIELD_ID, true)] = $pid ;
        }
        $url = get_option(MURUCA_CORE_PREFIX . "_graphql_url");
        $token = get_option(MURUCA_CORE_PREFIX . "_graphql_token");
        $a4view_connector = new A4v_Connector($url, $token);
        
        $response = $a4view_connector->get_resource_by_id(array_keys($result));
        if( isset( $response['error']) ){
            a4v_write_log(MURUCA_ELASTICSEARCH_LOG_DIR, "error: " . $response['error']);
            WP_CLI::error( $response['error'] );
        }
        
        if( $response['data']['getResourceById'] && is_array($response['data']['getResourceById'])){
            
            $count = 1;
            foreach( $response['data']['getResourceById'] as $item ){
                $params = [
                    "id" => $item['id'], 
                    "label" => $item['label'], 
                    "image" => isset($item['image']) ? $item['image'] : "" , 
                    'type' => isset($item['parent_type']) ? $item['parent_type'] : "", 
                    'classification' => !empty($item['document_classification']) ? $item['document_classification'] :  $item['document_type'] , 
                ];
                
                a4v_set_arianna_item_metafields($result[$item['id']], $params);
                a4v_write_log(MURUCA_ELASTICSEARCH_LOG_DIR, "$count: updated " . $item['id'] ." - wp id ". $result[$item['id']] );
                $count++;
            }
        }
            
        if ($result['errors']) {
            WP_CLI::error($result["errors"] );
        } else {
            WP_CLI::success( __("You have succesfully indexed ", MURUCA_CORE_TEXTDOMAIN)); 
        }
    }




    
}

/**
 * Registers our command when cli get's initialized.
 *
 * @since  1.0.0
 * @author Scott Anderson
 */
function a4v_cli_register_commands() {
	WP_CLI::add_command( 'a4v', 'A4v_Collection_Cli' );
}

add_action( 'cli_init', 'a4v_cli_register_commands' );