<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('a4v_acf_field_A4v_field') ) :


class a4v_acf_field_A4v_field extends acf_field {
	
	
	private $filter_post_type_choices = array(
		"oggetto-culturale" => "oggetto culturale",
		"a4.oc.ua.UA" => "unità archivistica",
		"la.oc.la.LA" => "libro antico",
		"veac301.oc.veac301.VEAC301" => "vestimento (VEAC 3.01)",
		"f400.oc.f400.F400" => "fotografia (F 4.00)",
		"uasc.oc.uasc.UASC" => "cartografica",
		"dc.oc.dc.DC" => "scheda Dublin Core",
		"oa300.oc.oa300.OA300" => "Scheda OA (3.00)",
		"rmmus.oc.rmmus.RMMUS" => "materiale musicale",
		"oac300.oc.oac300.OAC300" => "opera d'arte contemporanea (OAC 3.00)",
		"audiovideo.oc.audiovideo.AudioVideo" => "audio/video",
		"aggregazione-logica" => "Aggregazione logica",
		"ff400.al.ff400.FF400" => "fondo fotografico (FF 4.00)",
		"a4.al.al.AL" => "Aggregazione logica",		
		"persona" => "Entità persona",
		"luogo" => "Entità luogo",
		"organizzazione" => "Entità organizzazione",	
		"famiglia" => "Entità famiglia",
		"cosa notevole" => "Cosa notevole",
		"evento" => "Entità evento"	
	);
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	4/03/2021
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct( $settings ) {
		
		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/
		
		$this->name = 'a4v_field';
		
		
		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/
		
		$this->label = __('a4v field', 'a4v_textdomain');
		
		
		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/
		
		$this->category = 'relational';
		
		
		/*
		*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
		*/
		
		$this->defaults = array(
			'arianna_graphql_url'	=> '',
			'arianna_graphql_token' => '',
			'arianna_post_type' => "a4v_a4v-item",
			'max' => 1
		);
		
		
		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('A4v_field', 'error');
		*/
		
		$this->l10n = array(
			'error'	=> __('Error! Please enter a higher value', 'a4v_textdomain'),
		);
		
		
		/*
		*  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
		*/
		
		$this->settings = $settings;
		
		add_action('wp_ajax_acf/fields/a4v/query', array($this, 'ajax_query'));
		add_action('wp_ajax_acf/fields/a4v/addItem', array($this, 'ajax_add_item'));
		add_action('wp_ajax_nopriv_acf/fields/a4v/query', array($this, 'ajax_query'));
	
		// do not delete!
    	parent::__construct();
    	
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	
	function render_field_settings( $field ) {
		
		/*
		*  acf_render_field_setting
		*
		*  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
		*  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
		*
		*  More than one setting can be added by copy/paste the above code.
		*  Please note that you must also have a matching $defaults value for the field name (font_size)
		*/
		
		acf_render_field_setting( $field, array(
			'label'			=> __('Arianna Graphql Url','a4v_textdomain'),
			'instructions'	=> __('the graphql endpoint','a4v_textdomain'),
			'type'			=> 'url',
			'name'			=> 'arianna_graphql_url',
			'prepend'		=> '',
		));

		acf_render_field_setting( $field, array(
			'label'			=> __('Arianna Graphql Auth token','a4v_textdomain'),
			'instructions'	=> __('the authentication token','a4v_textdomain'),
			'type'			=> 'text',
			'name'			=> 'arianna_graphql_token',
			'prepend'		=> '',
		));

		acf_render_field_setting( $field, array(
			'label'			=> __('Post type','a4v_textdomain'),
			'instructions'	=> __('the post type to create on A4w resource selection','a4v_textdomain'),
			'type'			=> 'select',
			'name'			=> 'post_type',
			'choices'		=> acf_get_pretty_post_types(),
			'name'			=> 'arianna_post_type',
			'prepend'		=> '',
		));

		/*acf_render_field_setting( $field, array(
			'label'			=> __('select resource','a4v_textdomain'),
			'instructions'	=> __('yes to allow resource selection','a4v_textdomain'),
			'type'			=> 'true_false',
			'ui'			=> 1,
			'name'			=> 'arianna_select_resource',
			'prepend'		=> '',
		));*/

		$field['max'] = 1;
	}
	
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	
	function render_field( $field ) {
		
		
		/*
		*  Review the data of $field.
		*  This will show what data is available
		*/
	
		
		/*
		*  Create a simple text input using the 'font_size' setting.
		*/
		$atts = array(
			'id'				=> $field['id'],
			'data-max'			=> $field['max'],
			'class'				=> "acf-a4v-field {$field['class']}",
			'data-post_type'	=> $field['arianna_post_type'],
			'data-s'			=> '',
			'data-id'			=> '',
			'data-type'			=> '',
			'data-paged'		=> 1,
			'data-taxonomy'		=> '',
		);
		
		?>
		<?php acf_hidden_input( array('name' => $field['name'], 'value' => '') ); ?>
		<div <?php echo acf_esc_attrs($atts); ?>>
		<div class="filters -f3">
			
			<div class="filter -search">
				<?php acf_text_input( array('placeholder' => __("Search title...",'acf'), 'data-filter' => 's') ); ?>
			</div>
			
			<div class="filter -id">
				<?php acf_text_input( array('placeholder' => __("Search id",'acf'), 'data-filter' => 'id') ); ?>
			</div>
			
			
			<div class="filter -type">
				<?php acf_select_input( array('choices' => $this->filter_post_type_choices, 'data-filter' => 'type') ); ?>
			</div>	
		
		</div>
		<div class="selection">
			<div class="choices">
				<ul class="acf-bl list choices-list"></ul>
			</div>
			<div class="values">
				<ul class="acf-bl list values-list">
				<?php if( !empty($field['value']) ): 
				
				// get posts
				$posts = acf_get_posts(array(
					'post__in' => $field['value'],
					'post_type'	=> $field['arianna_post_type']
				));
				// loop
				foreach( $posts as $post ): ?>
				<?php 
					$meta = a4v_get_arianna_item_metafields($post->ID);
					$value = "";
					foreach( $meta as $key => $val ){
						$value .= $value == "" ? strip_tags($val) : "|||" . strip_tags($val);
					}
				?>
					<li>
						<?php acf_hidden_input( array('name' => $field['name'].'[]', 'value' => $value) ); ?>
						<span data-id="<?php echo esc_attr($post->ID); ?>" class="acf-rel-item">
							<?php echo acf_esc_html( $post->post_title ); ?>
							<a href="#" class="acf-icon -minus small dark" data-name="remove_item"></a>
							<a href="#" class="acf-icon -pencil small dark" 
								data-toggle="modal" 
								data-target="#modal-addPostObject" 
								data-name="edit_item"
								data-edit="edit"
								data-child="<?php echo get_post_type($post->ID); ?>"
								data-key="<?php echo $field['key'];?>"
								data-postid="<?php echo $post->ID;?>"
								data-label="<?php echo $field['label'];?>"
								data-subtype="<?php  isset($field['subtype']) ? $field['subtype'] : ''; ?>"
							></a>
						</span>
					</li>
				<?php endforeach; ?>
				<?php endif; ?>
				</ul>
			</div> <!--class=values -->
		</div>
	</div>
		<?php
	}
	
	
		
	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	
	
	function input_admin_enqueue_scripts() {
		
		// vars
		$url = $this->settings['url'];
		$version = $this->settings['version'];
		
		
		// register & include JS
		wp_register_script('a4v_scripts', "{$url}assets/js/input.js", array('acf-input'), $version);
		wp_enqueue_script('a4v_scripts');
		
		
		// register & include CSS
		wp_register_style('a4v_style', "{$url}assets/css/input.css", array('acf-input'), $version);
		wp_enqueue_style('a4v_style');
		
	}
	
	
	
	
	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
		
	function input_admin_head() {
	
		
		
	}
	
	*/
	
	
	/*
   	*  input_form_data()
   	*
   	*  This function is called once on the 'input' page between the head and footer
   	*  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and 
   	*  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
   	*  seen on comments / user edit forms on the front end. This function will always be called, and includes
   	*  $args that related to the current screen such as $args['post_id']
   	*
   	*  @type	function
   	*  @date	6/03/2014
   	*  @since	5.0.0
   	*
   	*  @param	$args (array)
   	*  @return	n/a
   	*/
   	
   	/*
   	
   	function input_form_data( $args ) {
	   	
		
	
   	}
   	
   	*/
	
	
	/*
	*  input_admin_footer()
	*
	*  This action is called in the admin_footer action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_footer)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
		
	function input_admin_footer() {
	
		
		
	}
	
	*/
	
	
	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
	
	function field_group_admin_enqueue_scripts() {
		
	}
	
	*/

	
	/*
	*  field_group_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is edited.
	*  Use this action to add CSS and JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*
	
	function field_group_admin_head() {
	
	}
	
	*/


	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	
	
	function load_value( $value, $post_id, $field ) {
		
		return $value;
		
	}
	
	
	
	
	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is saved in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	
	
	function update_value( $value, $post_id, $field ) {
		$values = [];

		$return_ids = [];

		foreach ($value as $v){
			$t = explode("|||", $v);
			$values[$t[0]] = ["id" => $t[0], "label" => $t[1], "image" => $t[2], 'type' => $t[3], 'classification' => $t[4]];
		}
		
		$query = new WP_Query([
			"post_type" => $field['arianna_post_type'],
			'meta_query' => array(
				array(
					'key'     => COLLECTION_ITEM_FIELD_ID,
					'value'   => array_keys($values),
					'meta_compare' => "IN"
				),
			),
		]);

		if($query->have_posts()){
			foreach ($query->posts as $post){
				$return_ids[] = $post->ID;
			}			 
		}
		 else {
			foreach ($values as $id => $v){
				$pid = wp_insert_post(array(
					"ID" => 0,
					"post_title" => strip_tags($v['label']),
					"post_type" => $field['arianna_post_type'],
					"post_status" => "publish"
				));

				if ( $pid ){
					$return_ids[] = $pid;
					a4v_set_arianna_item_metafields($pid, $values[$id]);
				}
			}
		 }
		return $return_ids;		
	}
	
	public function ajax_add_item(){

		$data = $_REQUEST["value"];
		$field = acf_get_field($_REQUEST["field_key"]);
		$pid = $this->update_value([$data], $_REQUEST["post_id"], $field);		
		if(!empty($pid)) {
			wp_send_json( array("post_id" => $pid[0]) );
		}
	}

	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
		
	/*
	
	function format_value( $value, $post_id, $field ) {
		
		// bail early if no value
		if( empty($value) ) {
		
			return $value;
			
		}
		
		
		// apply setting
		if( $field['font_size'] > 12 ) { 
			
			// format the value
			// $value = 'something';
		
		}
		
		
		// return
		return $value;
	}
	
	*/
	
	
	/*
	*  validate_value()
	*
	*  This filter is used to perform validation on the value prior to saving.
	*  All values are validated regardless of the field's required setting. This allows you to validate and return
	*  messages to the user if the value is not correct
	*
	*  @type	filter
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$valid (boolean) validation status based on the value and the field's required setting
	*  @param	$value (mixed) the $_POST value
	*  @param	$field (array) the field array holding all the field options
	*  @param	$input (string) the corresponding input name for $_POST value
	*  @return	$valid
	*/
	
	/*
	
	function validate_value( $valid, $value, $field, $input ){
		
		// Basic usage
		if( $value < $field['custom_minimum_setting'] )
		{
			$valid = false;
		}
		
		
		// Advanced usage
		if( $value < $field['custom_minimum_setting'] )
		{
			$valid = __('The value is too little!','a4v_textdomain'),
		}
		
		
		// return
		return $valid;
		
	}
	
	*/
	
	
	/*
	*  delete_value()
	*
	*  This action is fired after a value has been deleted from the db.
	*  Please note that saving a blank value is treated as an update, not a delete
	*
	*  @type	action
	*  @date	6/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (mixed) the $post_id from which the value was deleted
	*  @param	$key (string) the $meta_key which the value was deleted
	*  @return	n/a
	*/
	
	/*
	
	function delete_value( $post_id, $key ) {
		
		
		
	}
	
	*/
	
	
	/*
	*  load_field()
	*
	*  This filter is applied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0	
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/
	
	/*
	
	function load_field( $field ) {
		
		return $field;
		
	}	
	
	*/
	
	
	/*
	*  update_field()
	*
	*  This filter is applied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/
	
	/*
	
	function update_field( $field ) {
		
		return $field;
		
	}	
	
	*/
	
	
	/*
	*  delete_field()
	*
	*  This action is fired after a field is deleted from the database
	*
	*  @type	action
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	n/a
	*/
	
	/*
	
	function delete_field( $field ) {
		
		
		
	}	
	
	*/

	/*
	*  ajax_query
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_query() {
		
		// validate
		if( !acf_verify_ajax() ) die();
		// defaults
		$options = wp_parse_args($_POST, array(
			'post_id'		=> 0,
			'max' 		    => 1,
			's'				=> '',
			'id'			=> '',
			'type'			=> '',
			'field_key'		=> '',
			'paged'			=> 1,
			'post_type'		=> '',
			'taxonomy'		=> '',
			'limit'			=> 30
		));
		
		
		// load field
		$field = acf_get_field( $options['field_key'] );
		if( !$field ) return false;
		
		$url = isset($field['arianna_graphql_url']) ? $field['arianna_graphql_url'] : "";
		$token = isset($field['arianna_graphql_token']) ? $field['arianna_graphql_token'] : "";
		$a4view_connector = new A4v_Connector($url, $token);

		$results = $a4view_connector->get_resources($options);

		$types = [];
		if(isset($results['data']['search']['facets'])) {
			foreach( $results['data']['search']['facets'] as $facet ){
				if( $facet['id'] == "query-links" ){
					foreach ($facet['data'] as $d){
						$types[] =  $d['value']; //$this->filter_post_type_choices[$d['value']];
					}
				} else if( $facet['id'] == "doc-classification"){
					foreach ($facet['data'] as $d){
						$types[] = $d['value']; //$this->filter_post_type_choices[$d['value']];
					}
				}
			}
		}

		foreach($this->filter_post_type_choices as $key => $val ) {
			if (!in_array($key, $types)){
				unset ($this->filter_post_type_choices[$key]);
			}
		}
		// get choices
		$response = array(
			'results'	=> $results['data']['search']['results']['items'],
			'types'    => $this->filter_post_type_choices,
			'limit'		=> $options['limit']
		);
		// return
		acf_send_ajax_results($response);
			
	}
	
	

}


// initialize
new a4v_acf_field_A4v_field( $this->settings );


// class_exists check
endif;

?>