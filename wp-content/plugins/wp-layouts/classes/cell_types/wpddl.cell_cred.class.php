<?php
/*
 * CRED cell type.
 * Displays CRED Post Form
 *
 */

if( ddl_has_feature('cred-cell') === false ){
    return;
}

if( !class_exists('CRED_Cell') )
{
    class CRED_Cell{
        private $cell_type = 'cred-cell';

        function __construct(){
            add_action( 'init', array(&$this,'register_cred_cell_init'), 12 );
            add_action('wp_ajax_ddl_get_option_for_cred_form', array(&$this, 'ddl_get_option_for_cred_form_callback') );
            add_action('wp_ajax_ddl_delete_cred_forms', array(&$this,'ddl_delete_cred_forms') );
            add_action('wp_ajax_ddl_create_cred_form', array(&$this,'ddl_create_cred_form') );
        }

        function register_cred_cell_init() {
            if ( function_exists('register_dd_layout_cell_type') ) {
                register_dd_layout_cell_type ( $this->cell_type,
                    array (
                        'name'						=> __('CRED Post Form', 'ddl-layouts'),
                        'description'				=> __('Display a CRED Post Form which allows users to create and edit Posts, Pages and Custom content from the front-end.', 'ddl-layouts'),
                        'category'					=> __('Forms', 'ddl-layouts'),
                        'cell-image-url'					=> DDL_ICONS_SVG_REL_PATH.'cred-form.svg',
                        'button-text'				=> __('Assign CRED Post Form cell', 'ddl-layouts'),
                        'dialog-title-create'		=> __('Create a new CRED Post Form cell', 'ddl-layouts'),
                        'dialog-title-edit'			=> __('Edit CRED Post Form cell', 'ddl-layouts'),
                        'dialog-template-callback'	=> array(&$this, 'cred_cell_dialog_template_callback'),
                        'cell-content-callback'		=> array(&$this,'cred_cell_content_callback'),
                        'cell-template-callback'	=> array(&$this,'cred_cell_template_callback'),
                        'cell-class'				=> '',
                        'has_settings' => false,
                        'preview-image-url'			=>  DDL_ICONS_PNG_REL_PATH . 'CRED-form_expand-image.png',
                        'register-scripts'		   => array(
                            array( 'ddl-cred-cell-script', WPDDL_RELPATH . '/inc/gui/dialogs/js/cred-cell.js', array( 'jquery' ), WPDDL_VERSION, true ),
                        )
                    )
                );
            }
        }

        function cred_cell_dialog_template_callback() {
            ob_start();

            ?>

            <div class="ddl-form cred-edit-cells-form">
                <?php if (defined('CRED_FORMS_CUSTOM_POST_NAME')): ?>
                    <?php
                    require_once CRED_CLASSES_PATH."/CRED.php";
                    if (method_exists('CRED_CRED', 'is_embedded')) {
                        $cred_embedded = CRED_CRED::is_embedded();
                    } else {
                        $cred_embedded = false;
                    }
                    ?>

                    <?php if (!$cred_embedded): ?>
                        <fieldset>
                            <div class="fields-group">
                                <label class="radio">
                                    <input type="radio" name="cred-action" class="js-ddl-cred-form-create" value="new_form" >
                                    <?php _e('Create a new CRED Post Form', 'ddl-layouts');?>
                                </label>
                                <span class="desc ddl-form-indent js-ddl-newcred"><?php _e('CRED Post Forms allow you to create content or edit your content. Choose what you want this form to do and the type of content it will work with.', 'ddl-layouts'); ?></span>
                                <br class="js-ddl-newcred" />
                            </div>
                        </fieldset>

                        <fieldset class="js-ddl-newcred">
                            <legend><?php _e('This form will:', 'ddl-layouts'); ?></legend>
                            <select class="js-cred-new-mode ddl-form-indent">
                                <option value="new"><?php _e('Create content', 'ddl-layouts'); ?></option>
                                <option value="edit"><?php _e('Edit content', 'ddl-layouts'); ?></option>
                            </select>
                        </fieldset>

                        <fieldset class="js-ddl-newcred">
                            <?php  $post_types = CRED_Loader::get('MODEL/Fields')->getPostTypes(); ?>

                            <legend><?php _e('Content type:', 'ddl-layouts'); ?></legend>
                            <select class="js-cred-post-type ddl-form-indent">
                                <?php foreach($post_types as $post_type): ?>
                                    <option value="<?php echo $post_type['type']; ?>"><?php echo $post_type['name']; ?></option>
                                <?php endforeach; ?>
                            </select>

                        </fieldset>

                        <fieldset class="js-ddl-newcred">
                            <div class="fields-group ddl-form-indent">
                                <button class="button button-primary js-ddl-create-cred-form">
                                    <?php _e('Create Cell', 'ddl-layouts'); ?>
                                </button>
                                <p class="js-cred-form-create-error toolset toolset-alert-error alert ddl-form-input-alert" style="display:none">
                                </p>
                            </div>
                        </fieldset>
                    <?php endif; // end of full cred. ?>

                    <fieldset class="ddl-dialog-fieldset">
                        <div class="fields-group" <?php if ($cred_embedded) { echo 'style="display:none"'; } ?>>
                            <label class="radio">
                                <input type="radio" name="cred-action" class="js-ddl-cred-form-existing" value="existing" >
                                <?php _e('Use an existing CRED Post Form', 'ddl-layouts');?>
                            </label>
                        </div>
                    </fieldset>


                    <fieldset class="js-ddl-select-existing-cred">
                        <legend><?php _e('Form:', 'ddl-layouts'); ?></legend>
                        <select name="<?php the_ddl_name_attr('ddl_layout_cred_id'); ?>"
                                class="ddl-cred-select js-ddl-cred-select <?php if (!$cred_embedded) { echo 'ddl-form-indent'; } ?>"
                                data-new="<?php _e('create', 'ddl-layout'); ?>"
                                data-edit="<?php _e('edit', 'ddl-layouts'); ?>">

                            <option value=""><?php _e('--- Select form ---','ddl-layouts');?></option>';
                            <?php

                            $fm=CRED_Loader::get('MODEL/Forms');
                            $posts = $fm->getAllForms();

                            foreach ( $posts as $post ) :
                                $form = $fm->getForm($post->ID);
                                echo $this->ddl_cred_get_option_element($post->ID,
                                    $post->post_title,
                                    $form->fields['form_settings']->form['type'],
                                    $form->fields['form_settings']->post['post_type']);
                            endforeach;
                            ?>
                        </select>
                        <?php if (!$cred_embedded): ?>
                            <div class="fields-group ddl-form-indent">
                                <button class="button button-primary js-ddl-edit-cred-link"
                                        data-close-cred-text="<?php _e('Save and Close this form and return to the layout', 'ddl-layouts'); ?>"
                                        data-discard-cred-text="<?php _e('Close this form and discard any changes', 'ddl-layouts'); ?>">
                                    <?php _e('Create Cell', 'ddl-layouts'); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </fieldset>

                    <?php if ($cred_embedded): ?>
                        <fieldset>
                            <div class="fields-group">
                                <div class="toolset-alert toolset-alert-info">
                                    <?php _e('You are using the embedded version of CRED. Install and activate the full version of CRED and you will be able to create custom forms.', 'ddl-layouts'); ?>
                                    <br>
                                    <a class="fieldset-inputs" href="http://wp-types.com/home/cred/?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=cred-cell&utm_term=get-cred" target="_blank">
                                        <?php _e('About CRED', 'ddl-layouts');?>
                                    </a>

                                </div>
                            </div>
                        </fieldset>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="toolset-alert toolset-alert-info js-ddl-cred-not-activated">
                        <p>
                            <i class="icon-cred-logo ont-color-orange ont-icon-24"></i>
                            <?php _e('This cell requires the CRED plugin. Install and activate the CRED plugin and you will be able to create custom forms for creating and editing content.', 'ddl-layouts'); ?>
                            <br>
                            <br>

                            &nbsp;&nbsp;
                            <a class="fieldset-inputs" href="http://wp-types.com/home/cred/?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=cred-cell&utm_term=get-cred" target="_blank">
                                <?php _e('About CRED', 'ddl-layouts');?>
                            </a>

                        </p>
                    </div>
                <?php endif; ?>

                <div class="ddl-learn-more alignleft from-top-20">
                    <?php ddl_add_help_link_to_dialog(WPDLL_CRED_CELL, __('Learn about the CRED Post Form cell', 'ddl-layouts')); ?>
                </div>


            </div>

            <div id="ddl-cred-preview" style="display:none">
                -			<p><strong><?php _e('This form is used to %EDIT% %POST_TYPE%', 'ddl-layouts'); ?></strong></p>
                <div class="ddl-cred-preview">
                    <img src="<?php echo WPDDL_RES_RELPATH . '/images/cell-icons/cred-form.svg'; ?>" height="130px">
                </div>
            </div>

            <div id="ddl-cred-preview-cred-not-found" style="display:none">
                <h2><?php _e('The CRED Post Form was not found. It may have been deleted.', 'ddl-layouts'); ?></h2>
            </div>

            <?php

            echo wp_nonce_field('ddl_layout_cred_nonce', 'ddl_layout_cred_nonce', true, false);

            return ob_get_clean();
        }

        // Callback function for displaying the cell in the editor.
        function cred_cell_template_callback() {
            ob_start();
            ?>
            <div class="cell-content">

                <p class="cell-name"><?php _e('CRED Post Form', 'ddl-layouts'); ?></p>
                <div class="cell-preview">
                    <#
                        var preview = DDLayout.cred_cell.preview(content);
                        print( preview );
                        #>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }


        // Callback function for display the cell in the front end.
        function cred_cell_content_callback() {

            if (defined('CRED_FORMS_CUSTOM_POST_NAME')) {
                $fm=CRED_Loader::get('MODEL/Forms');
                $form = $fm->getForm(get_ddl_field('ddl_layout_cred_id'));

                if ($form) {
                    return do_shortcode('[cred-form form="' . get_ddl_field('ddl_layout_cred_id') . '"]');
                } else {
                    return WPDDL_Messages::cred_form_missing_message();
                }
            } else {
                return WPDDL_Messages::cred_missing_message();
            }
        }

        function ddl_create_cred_form(){

            if( WPDD_Utils::user_not_admin() ){
                die( __("You don't have permission to perform this action!", 'ddl-layouts') );
            }
            if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'],
                    'ddl_layout_cred_nonce')) {
                die('verification failed');
            }

            $result = array();

            if (defined( 'CRED_CLASSES_PATH' )) {
                require_once CRED_CLASSES_PATH."/CredFormCreator.php";
                $id = CredFormCreator::cred_create_form($_POST['name'], $_POST['mode'], $_POST['post_type']);
                $result['form_id'] = $id;
                if ($id) {
                    $result['option'] = $this->ddl_cred_get_option_element($id,
                        $_POST['name'],
                        $_POST['mode'],
                        $_POST['post_type']
                    );

                } else {
                    $result['error'] = __('Could not create the CRED Post Form', 'ddl-layouts');
                }
            }

            print wp_json_encode($result);

            die();
        }


        function ddl_delete_cred_forms(){

            if( WPDD_Utils::user_not_admin() ){
                die( __("You don't have permission to perform this action!", 'ddl-layouts') );
            }
            if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'],
                    'ddl_layout_cred_nonce')) {
                die('verification failed');
            }

            $cred_forms = $_POST['forms'];
            $fm=CRED_Loader::get('MODEL/Forms');
            foreach ($cred_forms as $form_id) {
                $fm->deleteForm($form_id);
            }
            die();
        }

        function ddl_cred_get_option_element( $id, $name, $type, $post_type_name ) {
            $type = $type == 'new' ? __('create', 'ddl-layout') : __('edit', 'ddl-layouts');
            $post_type = get_post_type_object( $post_type_name );

            if( is_object($post_type) === false ) return;

            $title = $name;

            ob_start();
            ?>
            <option value="<?php echo $id; ?>"
                    data-type="<?php echo $type; ?>"
                    data-post-type="<?php echo $post_type->label; ?>"
                    data-form-title="<?php echo $name; ?>" ><?php echo $title; ?></option>
            <?php
            $ret = ob_get_clean();
            return $ret;
        }

        function ddl_get_option_for_cred_form_callback () {
            global $wpdb;

            if( WPDD_Utils::user_not_admin() ){
                die( __("You don't have permission to perform this action!", 'ddl-layouts') );
            }
            if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'],
                    'ddl_layout_cred_nonce')) {
                die('verification failed');
            }

            $result = array();

            $fm=CRED_Loader::get('MODEL/Forms');

            $form = $fm->getForm($_POST['cred_id']);
            $post_title = $wpdb->get_var( $wpdb->prepare("SELECT post_title FROM $wpdb->posts WHERE ID = %d ",$_POST['cred_id']) );

            $result['option'] = $this->ddl_cred_get_option_element($_POST['cred_id'],
                $post_title,
                $form->fields['form_settings']->form['type'],
                $form->fields['form_settings']->post['post_type']);

            print wp_json_encode($result);

            die();
        }
    }

    new CRED_Cell();
}