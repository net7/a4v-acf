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
            $result[$pid] = a4v_get_arianna_item_metafields($pid);
        }
        $url = get_option(MURUCA_CORE_PREFIX . "_graphql_url");
        $token = get_option(MURUCA_CORE_PREFIX . "_graphql_token");
        $a4view_connector = new A4v_Connector($url, $token);
        var_dump(array_column($result, COLLECTION_ITEM_FIELD_ID));
		$results = $a4view_connector->get_resource_by_id(array_keys($result));
        
        if ($result['errors']) {
            WP_CLI::error($result["errors"] );
        } else {
            WP_CLI::success( __("You have succesfully indexed ", MURUCA_CORE_TEXTDOMAIN)); 
        }
        wp_die();
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