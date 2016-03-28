<?php
/*
 * CRED cell type.
 * Displays CRED User Form
 *
 */

if( ddl_has_feature('cred-user-cell') === false ){
    return;
}

if (!class_exists('CRED_User_Cell')) {
    class CRED_User_Cell
    {
        private $cell_type = 'cred-user-cell';

        function __construct()
        {
            add_action('init', array(&$this, 'register_cred_user_cell_init'), 12);
            add_action('wp_ajax_ddl_get_option_for_cred_user_form', array(&$this, 'ddl_get_option_for_cred_user_form_callback'));
            add_action('wp_ajax_ddl_delete_cred_user_forms', array(&$this, 'ddl_delete_cred_user_forms'));
            add_action('wp_ajax_ddl_create_cred_user_form', array(&$this, 'ddl_create_cred_user_form'));
        }

        function register_cred_user_cell_init()
        {
            if (function_exists('register_dd_layout_cell_type')) {
                register_dd_layout_cell_type($this->cell_type,
                    array(
                        'name' => __('CRED User Form', 'ddl-layouts'),
                        'description' => __('Display a CRED User Form which allows users to create and edit users from the front-end.', 'ddl-layouts'),
                        'category' => __('Forms', 'ddl-layouts'),
                        'cell-image-url' => DDL_ICONS_SVG_REL_PATH . 'cred-user-form.svg',
                        'button-text' => __('Assign CRED User Form cell', 'ddl-layouts'),
                        'dialog-title-create' => __('Create a new CRED User Form cell', 'ddl-layouts'),
                        'dialog-title-edit' => __('Edit CRED User Form cell', 'ddl-layouts'),
                        'dialog-template-callback' => array(&$this,'cred_user_cell_dialog_template_callback'),
                        'cell-content-callback' => array(&$this,'cred_user_cell_content_callback'),
                        'cell-template-callback' => array(&$this,'cred_user_cell_template_callback'),
                        'cell-class' => '',
                        'has_settings' => false,
                        'preview-image-url' => DDL_ICONS_PNG_REL_PATH . 'CRED-user-form_expand-image.png',
                        'register-scripts' => array(
                            array('ddl-cred-user-cell-script', WPDDL_RELPATH . '/inc/gui/dialogs/js/cred-user-cell.js', array('jquery'), WPDDL_VERSION, true),
                        )
                    )
                );
            }
        }

        function cred_user_cell_dialog_template_callback()
        {
            ob_start();

            ?>

            <div class="ddl-form cred-edit-cells-form">
                <?php if ( defined('CRED_USER_FORMS_CUSTOM_POST_NAME') ): ?>
                    <?php
                    require_once CRED_CLASSES_PATH . "/CRED.php";
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
                                    <input type="radio" name="cred-user-action" class="js-ddl-cred-user-form-create"
                                           value="new_form">
                                    <?php _e('Create a new User Form', 'ddl-layouts'); ?>
                                </label>
                                <span
                                    class="desc ddl-form-indent js-ddl-newcred"><?php _e('CRED User Forms allow you to create users. Choose what you want this form to do and the user role you want to assign.', 'ddl-layouts'); ?></span>
                                <br class="js-ddl-newcred"/>
                            </div>
                        </fieldset>

                        <fieldset class="js-ddl-newcred">
                            <legend><?php _e('This form will:', 'ddl-layouts'); ?></legend>
                            <select class="js-cred-user-new-mode ddl-form-indent">
                                <option value="new"><?php _e('Create user', 'ddl-layouts'); ?></option>
                                <option value="edit"><?php _e('Edit users', 'ddl-layouts'); ?></option>
                            </select>
                        </fieldset>

                        <fieldset>
                            <legend><?php _e('User Role:', 'ddl-layouts'); ?></legend>
                            <select class="js-cred-user_role ddl-form-indent"
                                    name="<?php the_ddl_name_attr('cred_user_role'); ?>">
                                <?php
                                global $wp_roles;
                                $user_roles = $wp_roles->roles;
                                foreach ($user_roles as $k => $v) {
                                    if (get_ddl_field('cred_user_role') == $k) {
                                        ?>
                                        <option value="<?php echo $k; ?>"
                                                selected="selected"><?php echo $v['name']; ?></option><?php
                                    } elseif( get_ddl_field('cred_user_role') != $k &&  $k == 'subscriber') {
                                        ?>
                                        <option selected="selected" value="<?php echo $k; ?>"><?php echo $v['name']; ?></option><?php
                                    } else{ ?>
                                        <option value="<?php echo $k; ?>"><?php echo $v['name']; ?></option>
                                   <?php }
                                }
                                ?>
                            </select>
                        </fieldset>

                        <fieldset class="ddl-dialog-fieldset">
                            <legend><?php _e('Settings:', 'ddl-layouts'); ?></legend>
                            
                                <span><input
                                        class="js-cred-user-autogenerate_username ddl-form-indent js-cred-user-autogenerate"
                                        type="checkbox" name="<?php the_ddl_name_attr('autogenerate_username'); ?>"
                                        value="1" checked="checked"> autogenerate username</span>

                              <span> <input
                                        class="js-cred-user-autogenerate_password ddl-form-indent js-cred-user-autogenerate"
                                        type="checkbox" name="<?php the_ddl_name_attr('autogenerate_password'); ?>"
                                        value="1" checked="checked"> autogenerate password</span>
                            <span> <input
                                    class="js-cred-user-autogenerate_nickname ddl-form-indent js-cred-user-autogenerate"
                                    type="checkbox" name="<?php the_ddl_name_attr('autogenerate_nickname'); ?>"
                                    value="1" checked="checked"> autogenerate nickname</span>

                        </fieldset>


                        <fieldset class="js-ddl-newcred">
                            <div class="fields-group ddl-form-indent">
                                <button class="button button-primary js-ddl-create-cred-user-form">
                                    <?php _e('Create Cell', 'ddl-layouts'); ?>
                                </button>
                                <p class="js-cred-user-form-create-error toolset toolset-alert-error alert ddl-form-input-alert"
                                   style="display:none">
                                </p>
                            </div>
                        </fieldset>
                    <?php endif; // end of full cred. ?>

                    <fieldset class="ddl-dialog-fieldset">
                        <div class="fields-group" <?php if ($cred_embedded) {
                            echo 'style="display:none"';
                        } ?>>
                            <label class="radio">
                                <input type="radio" name="cred-user-action" class="js-ddl-cred-user-form-existing"
                                       value="existing">
                                <?php _e('Use an existing Form', 'ddl-layouts'); ?>
                            </label>
                        </div>
                    </fieldset>


                    <fieldset class="js-ddl-select-existing-cred">
                        <legend><?php _e('Form:', 'ddl-layouts'); ?></legend>
                        <select name="<?php the_ddl_name_attr('ddl_layout_cred_user_id'); ?>"
                                class="ddl-cred-user-select js-ddl-cred-user-select <?php if (!$cred_embedded) {
                                    echo 'ddl-form-indent';
                                } ?>"
                                data-new="<?php _e('create', 'ddl-layout'); ?>"
                                data-edit="<?php _e('edit', 'ddl-layouts'); ?>">

                            <option value=""><?php _e('--- Select form ---', 'ddl-layouts'); ?></option>
                            ';
                            <?php

                            $fm = CRED_Loader::get('MODEL/UserForms');
                            $posts = $fm->getAllForms();

                            foreach ($posts as $post) :
                                $form = $fm->getForm($post->ID);
                                echo $this->ddl_cred_user_get_option_element($post->ID,
                                    $post->post_title,
                                    $form->fields['form_settings']->form['type'],
                                    $post->post_type, $k);
                            endforeach;
                            ?>
                        </select>
                        <input type="hidden" name="<?php the_ddl_name_attr('cred-user-post-type'); ?>"
                               class="js-cred-user-post-type" value="<?php echo CRED_USER_FORMS_CUSTOM_POST_NAME; ?>"/>
                        <?php if (!$cred_embedded): ?>
                            <div class="fields-group ddl-form-indent">
                                <button class="button button-primary js-ddl-edit-cred-user-link"
                                        data-close-cred-user-text="<?php _e('Save and Close this form and return to the layout', 'ddl-layouts'); ?>"
                                        data-discard-cred-user-text="<?php _e('Close this form and discard any changes', 'ddl-layouts'); ?>">
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
                                    <a class="fieldset-inputs"
                                       href="http://wp-types.com/home/cred/?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=cred-user-cell&utm_term=get-cred"
                                       target="_blank">
                                        <?php _e('About CRED', 'ddl-layouts'); ?>
                                    </a>

                                </div>
                            </div>
                        </fieldset>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="toolset-alert toolset-alert-info js-ddl-cred-user-not-activated">
                        <p>
                            <i class="icon-cred-user-logo ont-color-orange ont-icon-24"></i>
                            <?php _e('This cell requires the CRED plugin version 1.4 or above. Install and activate the CRED plugin and you will be able to create custom forms for creating and editing content.', 'ddl-layouts'); ?>
                            <br>
                            <br>

                            &nbsp;&nbsp;
                            <a class="fieldset-inputs"
                               href="http://wp-types.com/home/cred/?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=cred-user-cell&utm_term=get-cred"
                               target="_blank">
                                <?php _e('About CRED', 'ddl-layouts'); ?>
                            </a>

                        </p>
                    </div>
                <?php endif; ?>

                <div class="ddl-learn-more alignleft from-top-20">
                    <?php ddl_add_help_link_to_dialog(WPDLL_CRED_CELL, __('Learn about the CRED cell', 'ddl-layouts')); ?>
                </div>


            </div>

            <div id="ddl-cred-user-preview" style="display:none">
                - <p><strong><?php _e('This form is used to %EDIT% %POST_TYPE%', 'ddl-layouts'); ?></strong></p>

                <div class="ddl-cred-user-preview">
                    <img src="<?php echo WPDDL_RES_RELPATH . '/images/cell-icons/cred-user-form.svg'; ?>" height="130px">
                </div>
            </div>

            <div id="ddl-cred-user-preview-cred-user-not-found" style="display:none">
                <h2><?php _e('The CRED User Form was not found. It may have been deleted.', 'ddl-layouts'); ?></h2>
            </div>

            <?php

            echo wp_nonce_field('ddl_layout_cred_user_nonce', 'ddl_layout_cred_user_nonce', true, false);

            return ob_get_clean();
        }

        // Callback function for displaying the cell in the editor.
        function cred_user_cell_template_callback()
        {
            ob_start();
            ?>
            <div class="cell-content">

                <p class="cell-name"><?php _e('CRED User Form', 'ddl-layouts'); ?></p>

                <div class="cell-preview">
                    <#
                        var preview = DDLayout.cred_user_cell.preview(content);
                        print( preview );
                        #>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }


        // Callback function for display the cell in the front end.
        function cred_user_cell_content_callback()
        {

            if (defined('CRED_USER_FORMS_CUSTOM_POST_NAME')) {
                $fm = CRED_Loader::get('MODEL/UserForms');
                $form = $fm->getForm(get_ddl_field('ddl_layout_cred_user_id'));

                if ($form) {
                    return do_shortcode('[cred-user-form form="' . $form->form->post_title . '"]');
                } else {
                    return WPDDL_Messages::cred_form_missing_message();
                }
            } else {
                return WPDDL_Messages::cred_missing_message();
            }
        }

        function ddl_create_cred_user_form()
        {

            if (WPDD_Utils::user_not_admin()) {
                die(__("You don't have permission to perform this action!", 'ddl-layouts'));
            }
            if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'],
                    'ddl_layout_cred_user_nonce')
            ) {
                die('verification failed');
            }

            $result = array();

            if (defined('CRED_CLASSES_PATH')) {

                require_once CRED_CLASSES_PATH . "/CredUserFormCreator.php";
                //public static function cred_user_create_form($name, [new/edit], $user_type = 'subscriber', $autogenerate_un = false, $autogenerate_p = false, $post_type = 'user') {
                $autogenerate_u = $_POST['autogenerate_user'] === "1" ? true : false;
                $autogenerate_p = $_POST['autogenerate_password'] === "1" ? true : false;
                $autogenerate_n = $_POST['autogenerate_nickname'] === "1" ? true : false;

                $id = CredUserFormCreator::cred_create_form($_POST['name'], $_POST['mode'], array($_POST['user_role']), $autogenerate_u, $autogenerate_p, $autogenerate_n);
                $result['form_id'] = $id;

                if ($id) {
                    $result['option'] = $this->ddl_cred_user_get_option_element($id, $_POST['name'], $_POST['mode'], $_POST['post_type'], $_POST['user_role']);
                } else {
                    $result['error'] = __('Could not create the CRED User form', 'ddl-layouts');
                }
            }

            print wp_json_encode($result);

            die();
        }


        function ddl_delete_cred_user_forms()
        {

            if (WPDD_Utils::user_not_admin()) {
                die(__("You don't have permission to perform this action!", 'ddl-layouts'));
            }
            if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'],
                    'ddl_layout_cred_user_nonce')
            ) {
                die('verification failed');
            }

            $cred_user_forms = $_POST['forms'];
            $fm = CRED_Loader::get('MODEL/UserForms');
            foreach ($cred_user_forms as $form_id) {
                $fm->deleteForm($form_id);
            }
            die();
        }

        function ddl_cred_user_get_option_element($id, $name, $type, $post_type_name, $level)
        {

            $type = $type == 'new' ? __('create', 'ddl-layout') : __('edit', 'ddl-layouts');

            if ($post_type_name === 'user') {
                $post_type_name = CRED_USER_FORMS_CUSTOM_POST_NAME;
            }

            $post_type = get_post_type_object($post_type_name);

            if (is_object($post_type) === false) return;

            $title = $name;

            ob_start();
            ?>
            <option value="<?php echo $id; ?>"
                    data-type="<?php echo $type; ?>"
                    data-post-type="<?php echo $post_type->label; ?>"
                    data-user-level="<?php echo $level;?>"
                    data-form-title="<?php echo $name; ?>"><?php echo $title; ?></option>
            <?php
            $ret = ob_get_clean();
            return $ret;
        }

        function ddl_get_option_for_cred_user_form_callback()
        {
            global $wpdb;

            if (WPDD_Utils::user_not_admin()) {
                die(__("You don't have permission to perform this action!", 'ddl-layouts'));
            }
            if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'],
                    'ddl_layout_cred_user_nonce')
            ) {
                die('verification failed');
            }

            $result = array();

            $fm = CRED_Loader::get('MODEL/UserForms');

            $form = $fm->getForm($_POST['cred_user_id']);
            $post_title = $wpdb->get_var($wpdb->prepare("SELECT post_title FROM $wpdb->posts WHERE ID = %d ", $_POST['cred_user_id']));

            $result['option'] = $this->ddl_cred_user_get_option_element($_POST['cred_user_id'],
                $post_title,
                $form->fields['form_settings']->form['type'],
                $form->fields['form_settings']->post['post_type'], $_POST['user_role']);

            print wp_json_encode($result);

            die();

        }
    }

    new CRED_User_Cell();
}
