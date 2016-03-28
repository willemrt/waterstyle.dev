<?php
class WPDD_Layouts_PostTypesManager
{
	private $post_types;
	private $post_types_options;
	const DDL_POST_TYPES_OPTIONS = 'ddl_post_types_options';
	const DDL_POST_TYPES_WAS_BATCHED = '_ddl_post_types_was_batched';
	const META_KEY = WPDDL_LAYOUTS_META_KEY;
	const KEY_PREFIX = 'layout_';
    const POST_TYPES_OPTION_NAME = 'post_types';
    const POST_TYPES_APPLY_ALL_OPTION_NAME = 'post_types_apply_all';

	public function __construct()
	{
		$this->post_types_options = new WPDDL_Options_Manager( self::DDL_POST_TYPES_OPTIONS );

		add_action('admin_init', array(&$this,'init_admin'), 99 );

		add_action('wp_ajax_set_layout_for_post_type_meta', array(&$this, 'set_layout_for_post_type_meta_callback') );

		add_action('wp_ajax_change_layout_usage_for_post_types', array(&$this, 'set_layouts_post_types_on_usage_change') );
                
                add_action('save_post', array(&$this, 'assign_layout_to_posts_created_using_api'),15 );
                
        add_action( 'cred_save_data', array(&$this, 'assign_layout_to_newly_created_post'), 10, 2 );

        add_action( 'wpcf_relationship_add_child', array(&$this, 'assign_layout_to_child_created_post'), 10, 2 );

        add_action('print_post_types_checkboxes_in_dialog', array(&$this, 'print_post_types_checkboxes_in_page'));

        add_action('wp_ajax_print_post_type_checkboxes_js', array(&$this, 'print_post_type_checkboxes_js') );

        add_filter( 'ddl_get_change_dialog_html', array(&$this, 'add_dialog_change_use_html'), 10, 5 );

     //   add_action('wcml_before_sync_product_data', array(&$this, 'fix_woo_product_on_update'), 10, 3);
		
		if( is_admin())	{
			global $pagenow;
			
			if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == WPDDL_LAYOUTS_POST_TYPE ){
				add_action('admin_notices', array($this, 'show_admin_messages'));
			} 
			if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'dd_layouts_edit' ){
				add_action('admin_notices', array($this, 'show_admin_messages'));
			} 
			if ($pagenow == 'plugins.php' && isset($_GET['activate']) && $_GET['activate'] == 'true' ){
				add_action('admin_notices', array($this, 'show_admin_messages'));
			} 
		}
		
		add_action('after_switch_theme', array($this, 'trigger_theme_check'));

	}
	
	function trigger_theme_check () {
		add_action('admin_notices', array($this, 'show_admin_messages'));
	}

    public function add_dialog_change_use_html($html, $current, $do_not_show, $id, $show_ui){
        $html .= $this->print_post_types_checkboxes($current, $do_not_show, $id, $show_ui);
        return $html;
    }
	
	function show_admin_messages () {
		global $pagenow;
		
		$theme = wp_get_theme();
		
		$template_check = new WPDDL_Options_Manager('ddl_template_check');
		$OK = $template_check->get_options('theme-' . $theme->get('Name'));
		if (!$OK) {
			if (!$this->check_theme_has_any_templates()) {
				?>
			
				<div class="error">
					<h3><?php _e('Layouts theme integration', 'ddl-layouts'); ?> </h3>
					<p>
						<?php 
							echo sprintf( __( 'Layouts plugin needs to be integrated with your theme. Before designing layouts, please see the %sLayouts theme integration guide%s.', 'ddl-layouts'),
										   '<a href="' . WPDLL_THEME_INTEGRATION_QUICK . '" target="_blank">',
										   '</a>');
										   
						?>
					</p>
				</div>
				
				<?php
			} else {
				$template_check->update_options('theme-' . $theme->get('Name'), true);
				$template_check->save_options();
			}
		}
		
		if ($pagenow == 'plugins.php' && !defined('WPV_VERSION')) {
				?>
			
				<div class="update-nag">
					<h3><?php _e('Install Views', 'ddl-layouts'); ?> </h3>
					<p>
						<?php 
							_e( 'To get the most out of Layouts, you should also have Views plugin active. Views will allow you to design the templates for content and load content into cells.', 'ddl-layouts');
						?>

					</p>
				</div>
				
				<?php
		}
		
	}

    function print_post_type_checkboxes_js(){

        if( user_can_assign_layouts() === false ){
            die( WPDD_Utils::ajax_caps_fail( __METHOD__ ) );
        }
        if( $_POST && wp_verify_nonce( $_POST['wp_nonce_create_layout'], 'wp_nonce_create_layout' ) )
        {
            $send = wp_json_encode( array( 'message' =>  $this->print_post_types_checkboxes(false, true, '', false ) ) );

        }
        else
        {
            $send = wp_json_encode( array( 'error' =>  __( sprintf('Nonce problem: apparently we do not know where the request comes from. %s', __METHOD__ ), 'ddl-layouts') ) );
        }

        die( $send );

    }

     function print_post_types_checkboxes_in_page(){

        echo $this->print_post_types_checkboxes(false, true, '', false );
     }

     function fix_woo_product_on_update( $original_product_id, $tr_product_id, $language ){
			$layout_slug = WPDD_Utils::page_has_layout( $original_product_id );

			$post = get_post( $original_product_id );

			if( $layout_slug  ){
            	return $this->update_single_post_layout( $layout_slug, $original_product_id, $post->post_type );
        	}

        	$layout_data = $this->get_layout_to_type_object( $post->post_type );

        	if( $layout_data === null ) return null;

        	$layout_id = (int) $layout_data->layout_id;

        	$this->update_post_meta_for_post_type( array( $original_product_id ), $layout_id );
	}

    public function assign_layout_to_newly_created_post( $post_id , $post_type )
    {
        $layout_slug = WPDD_Utils::page_has_layout( $post_id );

        if( $layout_slug  ){
            return $this->update_single_post_layout( $layout_slug, $post_id, $post_type['post_type'] );
        }

        $layout_data = $this->get_layout_to_type_object( $post_type['post_type'] );

        if( $layout_data === null ) return null;

        $layout_id = (int) $layout_data->layout_id;

        $ret = $this->update_post_meta_for_post_type( array( $post_id ), $layout_id );

        return $ret;
    }

    function assign_layout_to_child_created_post( $post, $parent ){

        if( !$post ) return null;

        $post_id = $post->ID;

        $post_type = $post->post_type;

        $layout_data = $this->get_layout_to_type_object( $post_type );

        if( $layout_data === null ) return $post;

        $layout_id = (int) $layout_data->layout_id;

        $ret = $this->update_post_meta_for_post_type( array( $post_id ), $layout_id );

        return $post;
    }
    /*
     * This function will cover posting by email, import from xml file and etc...
     */
    function assign_layout_to_posts_created_using_api($post_id){
        if( !$post_id ) return null;
        
        global $pagenow;

        $ret = null;
        // list of forbidden pages
        $forbidden_pages = array("post-new.php","edit.php","post.php");
        if(in_array($pagenow,$forbidden_pages) || isset($_POST['action']) && $_POST['action'] === 'inline-save' || isset($_POST['action']) && $_POST['action']==='post-quickdraft-save') return null;

        // check if it's an update and the resource has already a layout assigned
        $layout_slug = WPDD_Utils::page_has_layout( $post_id );

        // get post type
        $post_type = get_post_type($post_id);

        // if it has then keep it
        if( $layout_slug  ){
            return $this->update_single_post_layout( $layout_slug, $post_id, $post_type );
        }

        // get layout data for this post type
        $layout_data = $this->get_layout_to_type_object( $post_type );

        // set layout data
        if( $layout_data ){
            $layout_id = (int) $layout_data->layout_id;
            $ret = $this->update_post_meta_for_post_type( array( $post_id ), $layout_id );
        }
        
        return $ret;
    }

	// debug only
	public function post_type_single_template($tpl)
	{
		global $post;
		print 'post_type_single_template <br />';
		print_r( $post->post_type );
		print '<br>'.$tpl;
		return $tpl;
	}
	// debug only
	public function post_type_page_template($tpl)
	{
		global $post;
		print 'page_template <br />';
		print_r( $post->post_type );
		print '<br>'.$tpl;
		return $tpl;
	}

	public function init_admin()
	{
		$this->post_types = $this->get_post_types_from_wp();
	}


	public function get_post_types_from_wp( $out = 'objects' )
	{
		$args = array(
			'public'   => true,
			//'_builtin' => false
		);

		$output = $out; // names or objects, note names is the default
		$operator = 'and'; // 'and' or 'or'

		$post_types = get_post_types( $args, $output, $operator );

		// Do not remove attachment post type (Media). It's fine!
        //unset( $post_types['attachment'] );

		return $post_types;
	}

    public function no_templates_at_all()
    {
        $post_types = $this->get_post_types_from_wp();

        $bool = true;

        foreach( $post_types as $post_type )
        {
            if( $this->check_layout_template_page_exists( $post_type ) === true )
            {
                $bool = false;
                break;
            }
        }

        return apply_filters('ddl_no_templates_at_all', $bool);
    }

    public function get_post_types_with_templates( )
    {
        $post_types = $this->get_post_types_from_wp();

        $ret = array();

        foreach( $post_types as $post_type )
        {
            if( $this->check_layout_template_page_exists( $post_type ) === true )
            {
                $ret[] = $post_type->name;
            }
        }

        return $ret;
    }

	public function get_post_types_options()
	{
		$options = $this->post_types_options->get_options( self::DDL_POST_TYPES_OPTIONS );
		if ($options === '') {
			$options = array();
		}
		
		return $options;
	}

	public function get_post_types()
	{
		return $this->post_types;
	}

	public function post_type_assigned_in_layout( $post_type, $layout_id = false )
	{
		global $post;

		$id = $layout_id ? $layout_id : $post->ID;

		$options = $this->get_post_types_options();

		foreach( $options as $layout => $post_type_arr  )
		{
			if( in_array( $post_type, $post_type_arr) && self::KEY_PREFIX.$id == $layout )
			{
				return true;
			}
			else if( in_array( $post_type, $post_type_arr ) && self::KEY_PREFIX.$id != $layout )
			{
				return false;
			}

		}
		return true;
	}

	public function get_layout_to_type_object( $post_type )
	{
		$ret = new stdClass();
		$options = $this->get_post_types_options();

		foreach( $options as $layout => $post_type_arr  )
		{
			if( is_array($post_type_arr) && in_array( $post_type, $post_type_arr) ) {
				$layout = explode(self::KEY_PREFIX, $layout);
				$ret->layout_id = $layout[1];
				return $ret;
			}
		}

		return null;
	}

    public function get_post_type_layout( $post_type ){
        $layout_object = $this->get_layout_to_type_object( $post_type );

        if( null === $layout_object  ) return null;

        $post = get_post( $layout_object->layout_id );
        $slug = $post->post_name;
        return $slug;
    }

	public function get_layout_post_types( $layout_id )
	{
		$options = $this->get_post_types_options();

		return isset( $options[self::KEY_PREFIX.$layout_id] ) ? $options[self::KEY_PREFIX.$layout_id] : array();
	}

    public function handle_post_type_data_save( $id, $post_types_array, $remove_assignments = false )
    {

        if (!$id) return false;

        //TODO: I am not sure maybe we want to pass an null parameter to remove associations...
        if ( null === $post_types_array || is_array($post_types_array) === false ) return false;

        $layout_id = $id;

        // get post types options from DB
        $before_change = $this->get_post_types_options();

        $post_types_saved = isset($before_change[ self::KEY_PREFIX.$layout_id] ) ? $before_change[ self::KEY_PREFIX.$layout_id ] : array();

        // remove what should be removed for this layout first
        if (count($post_types_saved) === 0 && count($post_types_array) === 0) {
            return false;
        } else {
            $to_remove = array_diff($post_types_saved, $post_types_array);

            if (is_array($to_remove)) {
                foreach ($to_remove as $post_type) {
                    if ( $this->get_post_type_was_batched( $layout_id, $post_type ) ) {
                        if( $remove_assignments ){
                            $this->remove_post_meta_for_post_type( $post_type, $layout_id );
                        } else{
                            $this->remove_track_batched_post_types( $post_type, $layout_id );
                        }
                    }
                }
            }
        }

        // set options for current layout post_types
        $option = array( self::KEY_PREFIX . $layout_id => $post_types_array );

        // then we check if post types are already assigned somewhere else
        foreach ($post_types_array as $post_type) {
            $check = $this->get_layout_to_type_object($post_type);

            if ($check !== null) {

                if ( (int)$check->layout_id !== (int)$layout_id ) {

                    if ( $this->get_post_type_was_batched($check->layout_id, $post_type) ) {
                        if( $remove_assignments ){
                            $this->remove_post_meta_for_post_type($post_type, $check->layout_id);
                        } else{
                            $this->remove_track_batched_post_types( $post_type, $check->layout_id );
                        }
                    }
                    $option[self::KEY_PREFIX . $check->layout_id] = $this->post_types_options->remove_options_item(self::KEY_PREFIX . $check->layout_id, $post_type, self::DDL_POST_TYPES_OPTIONS);
                }

            }
        }

        $ret = $this->post_types_options->update_options(self::DDL_POST_TYPES_OPTIONS, $option);

        return $ret;
    }

	public function handle_set_option_and_bulk_at_once( $layout_id, $to_set, $to_bulk = null, $force_set = false )
	{
		if( !$layout_id ) return false;

		if( !is_array($to_set) || count( $to_set ) === 0 ) return false;

        // assign to options without bulk assigning to posts

		if( $force_set && $to_bulk === null )
		{
			$bulk = $to_set;
		} else {
			$bulk = $to_bulk;
		}

		if( !is_array( $bulk ) || count( $bulk ) === 0 ){
            $ret = $this->handle_post_type_data_save( $layout_id, $to_set, false  );
            return $ret;
        }

        // bulk assign to posts
		foreach( $bulk as $type )
		{
			$posts = $this->get_all_posts_of_post_type_obj( $type );
			$this->update_post_meta_for_post_type( $posts->ids, $layout_id );
			$this->track_batched_post_types( $type, $layout_id );
		}

        $ret = $this->handle_post_type_data_save( $layout_id, $to_set, true );

		return $ret;
	}

	private function remove_post_meta_for_post_type( $post_type, $layout_string )
	{
		$layout_id = explode('layout_', $layout_string );
		$layout_id = isset( $layout_id[1] ) ? $layout_id[1] : $layout_string;
		$posts = $this->get_all_posts_of_post_type_obj( $post_type );
		$layout = get_post( $layout_id );

		foreach( $posts->ids as $id )
		{
			$meta = get_post_meta( $id, self::META_KEY, true );
			if( $layout->post_name === $meta )
			{
                WPDD_Utils::remove_layout_assignment_to_post_object( $id, $meta, true );
			}
		}

		$this->remove_track_batched_post_types( $post_type, $layout_id );
	}

	public function purge_layout_post_type_data( $layout_id )
	{
		// get everyone not only the ones directly associated with the current layout
		// so if there are single associations with current they will be purged as well
		$post_types = $this->get_post_types_from_wp();

		if( is_array($post_types) && count($post_types) > 0 )
		{
			$this->clean_layout_post_type_option( $layout_id );

			foreach( $post_types as $post_type )
			{
				$this->remove_track_batched_post_types( $post_type->name, $layout_id );
				$this->remove_post_meta_for_post_type( $post_type->name, $layout_id );
			}
		}
	}

	public function post_type_is_in_layout( $slug, $current = false )
	{
		global $post;

		if( $current === false && is_object( $post ) === false ) return false;

		$id = $current ? $current : $post->ID;

		$options = $this->get_post_types_options();

		if( isset( $options[self::KEY_PREFIX.$id] ) && in_array( $slug, $options[self::KEY_PREFIX.$id] ) )
		{
			return true;
		}

		return false;
	}

	public function print_layout_post_types( $layout_id )
	{
		?>
		<ul>
			<?php
				$post_types = $this->get_layout_post_types( $layout_id );
				$has_one = false;
				
				if( sizeof($post_types) === 0 )
				{
					?>
						<li><?php echo _e('Not assigned to any post type.', 'ddl-layouts'); ?></li>
					<?php
				}
				else
				{
					foreach( $post_types as $post_type )
					{
						$count = $this->check_post_meta_assigned_for_post_type( $layout_id, $post_type );
		
					//	if( $count === -1 ) return;
		
						$post_type_obj = get_post_type_object( $post_type );
		
						// check in case the user changes theme or deactivates plugin and post type is not available anymore
						if( is_object( $post_type_obj ) )
						{
							$has_one = true;
		
							if( is_object($count) && $count->count_posts > 0 )
							{
								?>
									<li>
										<?php $this->print_post_meta_assigned_to_post_type( $layout_id, $post_type, $count, $post_type_obj ); ?>
									</li>
								<?php
							}
							else
							{
								?>
									<li><?php echo $post_type_obj->labels->name . ' '; ?></li>
								<?php
								
							}
						}
						else
						{
							if( $has_one === false ) {
								?>
									<li><?php echo _e('Not assigned to any post type.', 'ddl-layouts'); ?></li>
								<?php
							}
						}
					}
				}
			?>
		</ul>
		<?php
	}

	public function print_apply_to_all_link_in_layout_editor( $type, $checked, $current = false )
	{
		global $post;

		if( $current === false && is_object( $post ) === false ) return;

		$id = $current ? $current : $post->ID;

		$count = $this->check_post_meta_assigned_for_post_type( $id, $type->name, $current );

		if( !$checked || $count === -1 || $count === 0 ) return;

		$this->print_post_meta_assigned_to_post_type( $id, $type->name, $count, $type, true );
	}

	private function check_post_meta_assigned_for_post_type( $layout_id, $post_type )
	{
		global $wpdb;

		$posts =  $this->get_all_posts_of_post_type_obj( $post_type );

		if( $posts->count ===  0 ) return -1;

		$key = self::META_KEY;

		$layout = get_post($layout_id);
		$layout_slug = $layout->post_name;

		$count_meta = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->postmeta} WHERE
					meta_key='{$key}' AND meta_value='{$layout_slug}'
					AND post_id IN ({$posts->list})" );


		if( ( $count_meta - $posts->count ) >= 0 )
		{
			return 0;
		}

		$ret = new stdClass();

		$ret->count_posts = $posts->count;
		$ret->count_meta = $count_meta;
		$ret->post_list = $posts->list;

		return $ret;
	}

	private function get_all_posts_of_post_type_obj( $post_type )
	{
        $args = array(
            'fields' => 'ids',
            'posts_per_page' => -1,
            'post_type' => $post_type,
            // get only published posts
            'post_status' => 'any',
            //don't perform found posts query
            // leave the terms alone we don't need them
            'update_post_term_cache' => false,
            // leave the meta alone we don't need them
            'update_post_meta_cache' => false,
            // don't cache results
            'cache_results' => false,
        );

        $new_query = new WP_Query($args);

        $posts = $new_query->posts;

		return (object) array(
			'ids' => $posts,
			'count' => $new_query->found_posts,
			//'list' => "'" . implode( "','", $cpts ) . "'"
			'list' => implode( ",", $posts )
		);
	}

	public function get_layout_post_types_object( $layout_id )
	{
		$post_types = $this->get_layout_post_types( $layout_id );
		$has_one = false;
		$ret = array();

		if( sizeof($post_types) === 0 )
		{

			return false;

		}
		else
		{
			foreach( $post_types as $post_type )
			{
				$count = $this->check_post_meta_assigned_for_post_type( $layout_id, $post_type );

				$post_type_obj = get_post_type_object( $post_type );

				// check in case the user changes theme or deactivates plugin and post type is not available anymore
				if( is_object( $post_type_obj ) )
				{
					$has_one = true;
					$ret[] = $this->get_post_types_data_object( $layout_id, $post_type, $count, $post_type_obj );
				}
				else
				{
					if( $has_one === false ) {
						$ret[] = false;
					}
				}
			}
		}
		return $ret;
	}

	function get_post_types_data_object( $layout_id, $type, $count, $post_type )
	{
		$message = '';

		if( is_object( $count ) )
		{
			$missing = $count->count_posts - $count->count_meta;
			$post_num = $count->count_posts;
			$meta_num = $count->count_meta;
			$post_list = $count->post_list;
		}
		else
		{
			$missing = 0;
			$post_num = 0;
			$meta_num = 0;
			$post_list = '';
		}


		if ( ( $missing ) == 1 ) {
			$type_label = $post_type->labels->singular_name;
			$message = sprintf(__('%d %s uses a different Layout.', 'ddl-layouts'), $missing, $type_label);
		} elseif ( (  $missing ) > 1 ) {
			$type_label = $post_type->labels->name;
			$message = sprintf(__('%d %s use a different layout.', 'ddl-layouts'), $missing, $type_label);
		}

		$data = array(
			'layout_id' => $layout_id,
			'post_type' => $type,
			'post_num'=> $post_num,
			'meta_num' => $meta_num,
			'post_list' => $post_list,
			'missing' => $missing,
			'label' => $post_type->label,
			'singular' => $post_type->labels->singular_name,
			'plural' => $post_type->labels->name,
			'nonce' => wp_create_nonce( 'set-layout-for-cpt-nonce' ),
			'template_exists' => $this->check_layout_template_page_exists( $post_type ),
			'message' => $message
		);

		return $data;
	}

    public function get_post_types_posts_used($type)
    {

        if( $type === null ) return array();

        $post_status_black = array( 'pending', 'auto-draft', 'future', 'inherit', 'trash');
        $post_status_white = array( 'publish', 'draft', 'private' );

        $args = array(
            'fields' => 'ids',
            'posts_per_page' => -1,
            'post_type' => $type,
            // get only published posts
            'post_status' => $post_status_white,
            //don't perform found posts query
            // leave the terms alone we don't need them
            'update_post_term_cache' => false,
            // leave the meta alone we don't need them
            'update_post_meta_cache' => false,
            // don't cache results
            'cache_results' => false,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => WPDDL_LAYOUTS_META_KEY,
                    'compare' => 'EXISTS',
                )
            ),
            'suppress_filters' => true
            );

        $new_query = new WP_Query($args);
        
        $posts = $new_query->posts;

        $found = $new_query->found_posts;

		global $wpdb;

		$query = $wpdb->prepare("SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = '%s' AND post_status NOT IN (%s)", $type, implode("', '", $post_status_black) );
        $total = $wpdb->get_var( stripslashes($query) );
        $ret = (object) array('ids' => $posts, 'count' => $found, 'type' => $type, 'total' => $total );
		$wpdb->flush();
        return $ret;
    }

	private function print_post_meta_assigned_to_post_type( $layout_id, $type, $count, $post_type, $in_layout_page = false )
	{
		$data = $this->get_post_types_data_object( $layout_id, $type, $count, $post_type );

		ob_start(); ?>

		<?php echo $in_layout_page ? '' : $post_type->labels->name; ?>

		 <span class="js-alret-icon-hide-post alert-icon-hide-post"><a data-object="<?php echo htmlspecialchars( wp_json_encode( $data ) ); ?>" class="apply-for-all js-apply-layout-for-all-posts js-alert-icon-hide-<?php echo $type; ?> button button-small button-leveled icon-warning-sign fa fa-exclamation-triangle"> <?php echo sprintf(__('Use this layout for %d %s', 'ddl-layouts'), $data['missing'], $data['plural']); ?> </a></span></li>

		<?php ob_end_flush();

		include WPDDL_INC_ABSPATH.'/gui/templates/layout-assign-to-post-types.box.tpl.php';
	}

	public function check_layout_template_page_exists( $post_type )
	{
		global $wpddlayout;

		$template = $this->get_single_template( $post_type->name );

		$layout_template = $wpddlayout->templates_have_layout( array_flip($template) );

		if( sizeof( $layout_template ) > 0 )
		{
			return apply_filters( 'ddl_check_layout_template_page_exists', true, $post_type );
		}

		return apply_filters( 'ddl_check_layout_template_page_exists', false, $post_type );
	}
	
	public function check_layout_template_for_woocommerce ( $post_type ) {
		if (!function_exists('WC') || $post_type->name != 'product') {
			return '';
		}

		$woocommerce_views_available = class_exists('Class_WooCommerce_Views');
		
		// work out which template woocommerce will use.
		// partical copied from woocommerce file - class-wc-template-loader.php
		
		$find = array( 'woocommerce.php' );
		$file = '';

		$file 	= 'single-product.php';
		$find[] = $file;
		$find[] = WC()->template_path() . $file;

		if ( $file ) {
			$template = locate_template( array_unique( $find ) );
			if ( ! $template  ) {
				$template = WC()->plugin_path() . '/templates/' . $file;
			}
		}
		
		if ($woocommerce_views_available) {
			// see if we have a different template selected in woocommerce views.

			$template_in_db_wc_template = get_option('woocommerce_views_theme_template_file');
		
			if ((is_array($template_in_db_wc_template)) && (!(empty($template_in_db_wc_template))))  {
				$template_in_db_wc_template_value = key($template_in_db_wc_template);
				$template_file = $template_in_db_wc_template[$template_in_db_wc_template_value];
				if (file_exists($template_file)) {
					$template = $template_file;
				}
				
			}
		}
		
		$found = false;
		$file_data = @file_get_contents($template);
		if ($file_data !== false) {
			if (strpos($file_data, 'the_ddlayout') !== false) {
				$found = true;
			}
		}
		
		$message = '';		
		
		if (!$found) {
			$message = __("Your layout design will not yet show on WooCommerce products, because WooCommerce is using a template file that doesn't display the layout.", 'ddl-layouts');
			$message .= '<br>';
			if($woocommerce_views_available) {
				$message .= '<a href="' . admin_url('admin.php?page=wpv_wc_views'). '">' . __('Select a different template for WooCommerce products', 'ddl-layouts') . '</a>';
			} else {
				$message .= sprintf(__('Please check %shere%s to see how to set one up.', 'ddl-layouts'), '<a href="' . WPDLL_LEARN_ABOUT_SETTING_UP_TEMPLATE . '" target="_blank">', '</a>');
			}
		}
	
		return $message;
	}
	
	public function check_theme_has_any_templates() {
		$post_types = get_post_types(array(), 'objects');
		
		foreach ($post_types as $post_type) {
			if ( $this->check_layout_template_page_exists ($post_type)) {
				return true;
			}
		}
		
		return false;
	}

	private function get_layout_template_name_for_post_type( $post_type )
	{
		$template = 'default';

		if( $post_type === 'page' )
		{
			$template = "{$post_type}.php";
		}
		else if( $post_type === 'post' )
		{
			$template = "single.php";
		}
		else{
			$template = "single-{$post_type}.php";
		}

		return $template;
	}

	public function get_layout_template_for_post_type( $post_type )
	{
		global $wpddlayout;

		$main_template = $this->get_layout_template_name_for_post_type( $post_type );

		$template = $this->get_single_template( $post_type );

		$layout_template = $wpddlayout->templates_have_layout( array_flip($template) );

		if( in_array( $main_template, $layout_template) ) return $main_template;

        if( isset( $layout_template[0] ) === false ) {
            $ret = 'default';
        } else if( isset( $layout_template[0] ) === true && $post_type === 'page' ){
            $layout_template = $this->fix_order_for_pages( $layout_template );
            $ret = $layout_template[0];
        } else{
            $ret = $layout_template[0];
        }

		return $ret;
	}

    private function fix_order_for_pages( $layout_template ){

        if( in_array('page-layouts.php', $layout_template) === false ) return $layout_template;

        $index = array_search ( 'page-layouts.php', $layout_template );
        unset($layout_template[$index]);

        array_unshift($layout_template, 'page-layouts.php');

        return $layout_template;
    }

	public function get_single_template( $post_type )
	{
		$templates = array();

		if( $post_type === 'page' )
		{
            /** Thanks to http://wordpress.stackexchange.com/questions/83180/get-page-templates
            **  get_page_templates function is not defined in FE so we need to load it in order
            **  for this one to work
            **/
            if( !function_exists('get_page_templates') ) include_once ABSPATH . 'wp-admin/includes/theme.php';
            $templates[$post_type] = "{$post_type}.php";
			$templates += apply_filters( 'ddl-theme_page_templates', get_page_templates() );
		}
		else if( $post_type === 'post' )
		{
			$templates['single'] = "single.php";
		}
		else{
			$templates["single-{$post_type}"] = "single-{$post_type}.php";
			$templates['single'] = "single.php";
		}

		$templates['index'] = 'index.php';

       return $templates;
	}

	public function update_post_meta_for_post_type( $posts, $layout_id, $layout_slug = null )
	{
		$ret = array();

		foreach( $posts as $id )
		{
            if( null === $layout_slug ){
                $meta_value = get_post( $layout_id );
                $slug = $meta_value->post_name;
            }
            else{
                $slug = $layout_slug;
            }

			$post = get_post( $id );
                        
            $ret[] = $this->update_single_post_layout( $slug, $id, $post->post_type );
		}

		return $ret;
	}

    public function update_single_post_layout($slug, $post_id, $post_type, $template = false)
    {
        $tpl = $template ? $template : $this->get_layout_template_for_post_type($post_type);
        $ret = WPDD_Utils::assign_layout_to_post_object( $post_id, $slug, $tpl, get_post_meta($post_id, self::META_KEY, true) );;
        return $ret;
    }

	public function get_page_template( $post_id ){
		return get_post_meta( $post_id, '_wp_page_template', true );
	}

	public function set_layout_for_post_type_meta_callback()
	{
        if( user_can_assign_layouts() === false ){
            die( WPDD_Utils::ajax_caps_fail( __METHOD__ ) );
        }
		if( $_POST && wp_verify_nonce( $_POST['set-layout-for-cpt-nonce'], 'set-layout-for-cpt-nonce' ) )
		{
            global $wpddlayout, $wpdd_gui_editor;

			extract( $_POST, EXTR_SKIP );

			$posts = explode(',', stripcslashes($post_list) );

			$res = $this->update_post_meta_for_post_type( $posts, $layout_id );

			$this->track_batched_post_types( $_POST['post_type'], $layout_id );

			$data = $_POST;

			$data['results'] = $res;

			if( isset($_POST['in_listing_page']) && $_POST['in_listing_page'] == 'yes' )
			{
			    $send = $wpddlayout->listing_page->get_send(isset($_GET['status']) && $_GET['status'] === 'trash' ? $_GET['status'] : 'publish', false, $layout_id, $data, $_POST );
            }
			else
			{
				$send = wp_json_encode( array('message' => $data, 'where_used_html' => $wpdd_gui_editor->get_where_used_output( $layout_id ) ) );
			}
		}
		else
		{
			$send = wp_json_encode( array( 'error' =>  __( sprintf('Nonce problem: apparently we do not know where the request comes from. %s', __METHOD__ ), 'ddl-layouts') ) );
		}

		die( $send );
	}

	public function track_batched_post_types( $post_type, $layout_id )
	{
		$meta_key = self::DDL_POST_TYPES_WAS_BATCHED;

		$meta = get_post_meta($layout_id, $meta_key, true );

		if( empty( $meta ) || $meta == '' )
		{
			$push = array();
			$push[] = $post_type;
		}
		else
		{
			$push = $meta;
			$push[] = $post_type;
		}

		update_post_meta( $layout_id, $meta_key, array_unique($push) );
	}

	public function remove_track_batched_post_types( $post_type, $layout_id )
	{
		$meta_key = self::DDL_POST_TYPES_WAS_BATCHED;

		$meta = get_post_meta($layout_id, $meta_key, true );

		if ($meta) {
			$push = array_diff( $meta, array( $post_type ) );
	
			update_post_meta( $layout_id, $meta_key, $push );
		}
	}

	public function get_post_type_was_batched( $layout_id, $post_type )
	{
        if( !$layout_id ) return false;
		$meta = get_post_meta($layout_id, self::DDL_POST_TYPES_WAS_BATCHED, true );
		//( $meta );
		if( !is_array( $meta ) ) return false;
		return in_array( $post_type, $meta );
	}

    public function get_layout_batched_post_types( $layout_id )
    {
        return get_post_meta($layout_id, self::DDL_POST_TYPES_WAS_BATCHED, false );
    }

	public function print_post_types_checkboxes( $current = false, $do_not_show = false, $id_string = "", $show_ui = true, $show_edit = true )
	{
		$types = $this->get_post_types();
		ob_start();
		if ( sizeof($types) > 0 ) {
			include WPDDL_GUI_ABSPATH.'editor/templates/select-post-types.box.tpl.php';
		}
		return ob_get_clean();
	}



	public function set_layouts_post_types_on_usage_change()
	{
        if( user_can_assign_layouts() === false ){
            die( WPDD_Utils::ajax_caps_fail( __METHOD__ ) );
        }
		if( $_POST && wp_verify_nonce( $_POST['layout-set-change-post-types-nonce'], 'layout-set-change-post-types-nonce' ) )
		{
            $post_types = isset( $_POST[self::POST_TYPES_OPTION_NAME] ) && is_array( $_POST[self::POST_TYPES_OPTION_NAME] ) ? array_unique( $_POST[self::POST_TYPES_OPTION_NAME] ) : array();

            if( isset( $_POST['extras'] ) )
            {
                $extras = $_POST['extras'];

                if( isset( $extras['post_types'] ) && count( $extras['post_types'] ) > 0 ){
                    $types_to_batch = $extras['post_types'];
                }
            }

            if( isset($extras) && isset( $types_to_batch ) )
            {
                $send = wp_json_encode( array( 'message'=> array( 'changed' => $this->handle_set_option_and_bulk_at_once( $_POST['layout_id'], $post_types, null ), 'done' => 'yes' ) ) );

            } else {
                $send = wp_json_encode( array( 'message'=> array( 'changed' => $this->handle_post_type_data_save( $_POST['layout_id'], $post_types, true ), 'done' => 'yes' ) ) );

            }
		}
		else
		{
			$send = wp_json_encode( array( 'error' =>  __( sprintf('Nonce problem: apparently we do not know where the request comes from. %s', __METHOD__ ), 'ddl-layouts') ) );
		}

		die($send);
	}

	public function clean_layout_post_type_option( $layout_id )
	{
		return $this->post_types_options->delete_options( self::DDL_POST_TYPES_OPTIONS, 'layout_'.$layout_id );
	}
}