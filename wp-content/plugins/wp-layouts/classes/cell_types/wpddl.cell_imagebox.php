<?php
/*
 * Image-box cell type.
 * Bootstrap thumbnail component that displays box with image, header and text. Suitable for callout boxes, key features, services showcase etc.
 *
 */

if( ddl_has_feature('imagebox-cell') === false ){
	return;
}

if (!class_exists('Layouts_cell_imagebox')) {
    class Layouts_cell_imagebox{
        
            private $cell_type = 'imagebox-cell';
        
            function __construct() {
                add_action( 'init', array(&$this, 'register_imagebox_cell_init'), 12);
            }



            function register_imagebox_cell_init() {
                if (function_exists('register_dd_layout_cell_type')) {
                    
                    register_dd_layout_cell_type($this->cell_type, 
                        array(
                            'name' => __('Image box', 'ddl-layouts'),
                            'cell-image-url' => DDL_ICONS_SVG_REL_PATH . 'layouts-imagebox-cell.svg',
                            'description' => __('Display an image with a header and text. Suitable for callout boxes, key features, services showcase etc. Use the Visual Editor cell if you need more flexibility.', 'ddl-layouts'),
                            'category' => __('Text and media', 'ddl-layouts'),
                            'button-text' => __('Assign image box cell', 'ddl-layouts'),
                            'dialog-title-create' => __('Create a new image box cell', 'ddl-layouts'),
                            'dialog-title-edit' => __('Edit image box cell', 'ddl-layouts'),
                            'dialog-template-callback' => array(&$this,'imagebox_cell_dialog_template_callback'),
                            'cell-content-callback' => array(&$this,'imagebox_cell_content_callback'),
                            'cell-template-callback' => array(&$this,'imagebox_cell_template_callback'),
                            'has_settings' => true,
                            'preview-image-url' => DDL_ICONS_PNG_REL_PATH . 'image-box_expand-image.png',
                            'register-scripts' => array(
                                array('ddl-imagebox-cell-script', WPDDL_GUI_RELPATH . 'editor/js/ddl-imagebox-cell-script.js', array('jquery', 'underscore'), WPDDL_VERSION, true),
                            ),
                            'translatable_fields' => array(
                                'box_title' => array('title' => 'Image Box Title', 'type' => 'LINE'),
                                'box_content' => array('title' => 'Image Box Content', 'type' => 'AREA')
                            )
                        )
                    );
                }
            }
            
            
            
            
            function imagebox_cell_get_image_size_options(){
                $sizes =  WPDD_Utils::get_image_sizes();
                $html = ''; $selected = '';
                $sizes['original'] = array();
                foreach( $sizes as $size => $values ){
                    if( $size === 'original' ){
                        $selected = 'selected="selected"';
                        $html .= '<option value="'.$size.'"  '.$selected.'> '. ucfirst( $size ) .' </option>';
                    } else {
                        $html .= '<option value="'.$size.'"  '.$selected.'> '.ucfirst( $size ) .' - '. $values['width'] . 'X'. $values['height'] .' </option>';

                    }
                }

                return $html;
            }
            
            function imagebox_cell_dialog_template_callback() {
                
                ob_start();
                ?>

                <ul class="ddl-form js-form-image-box-wrap form-image-box-wrap">
                    <li class="js-ddl-media-field js-ddl-media-field-edit ddl-border-bottom">
                        <label for="<?php the_ddl_name_attr('box_image'); ?>" class="ddl-zero"><?php //_e('Image URL', 'ddl-layouts')  ?></label>
                        <input type="hidden" class="js-ddl-media-url" name="<?php the_ddl_name_attr('box_image'); ?>" />
                        <input type="hidden" name="<?php the_ddl_name_attr('box_image_org_w'); ?>" />
                        <input type="hidden" name="<?php the_ddl_name_attr('box_image_org_h'); ?>" />
                        <div class="ddl-form-button-wrap ddl-form-button-image-wrap">
                            <div class="ddl-imagebox-cell-preview-wrap">

                            </div>
                            <button class="button js-ddl-add-media js-ddl-media-imagebox button-primary"
                                    data-uploader-title="<?php _e('Choose an image', 'ddl-layouts') ?>"
                                    data-uploader-button-text="<?php _e('Insert image URL', 'ddl-layouts') ?>">
                                        <?php _e('Choose an image', 'ddl-layouts') ?>
                            </button>

                        </div>
                    </li>

                    <li>
                        <label for="<?php the_ddl_name_attr('box_title'); ?>"><?php _e('Caption title', 'ddl-layouts') ?>:</label>
                        <input type="text" name="<?php the_ddl_name_attr('box_title'); ?>">
                    </li>			
                    <li>
                        <label for="<?php the_ddl_name_attr('box_content'); ?>"><?php _e('Caption description', 'ddl-layouts') ?>:</label>
                        <textarea name="<?php the_ddl_name_attr('box_content'); ?>" rows="4"></textarea>
                    </li>
                    <li>
                        <label for="<?php the_ddl_name_attr('disable_bootstrap_thumbnail'); ?>" class="ddl-manual-width"></label>
                        <input type="checkbox" name="<?php the_ddl_name_attr('disable_bootstrap_thumbnail'); ?>"><span class="label"><?php _e('Disable Bootstrap "thumbnail"', 'ddl-layouts') ?></span>
                    </li>

                    <?php if( WPDDL_Framework::framework_supports_responsive_images() ): ?>

                        <li>
                            <label for="<?php the_ddl_name_attr('is_responsive_image'); ?>" class="ddl-manual-width"></label>
                            <input checked="checked" type="checkbox" name="<?php the_ddl_name_attr('is_responsive_image'); ?>"><span class="label"><?php _e('Display responsive image', 'ddl-layouts') ?></span>
                        </li>



                        <li class="ddl-border-bottom">
                            <label for="<?php the_ddl_name_attr('display_responsive_image'); ?>"><?php _e('Image effects', 'ddl-layouts') ?></label>

                            <select name="<?php the_ddl_name_attr('display_responsive_image'); ?>">
                                <option value=""><?php _e('None', 'ddl-layouts') ?></option>
                                <option value="img-circle"><?php _e('Circle', 'ddl-layouts') ?></option>
                                <option value="img-thumbnail"><?php _e('Thumbnail', 'ddl-layouts') ?></option>
                                <option value="img-rounded"><?php _e('Rounded', 'ddl-layouts') ?></option>
                            </select>
                        </li>

                    <?php endif; ?>


                    <li>
                        <label for="<?php the_ddl_name_attr('image_alignment'); ?>" class="ddl-manual-width-190"><?php _e('Alignment', 'ddl-layouts') ?>:</label>
                        <div class="ont-btn-group ddl-image_alignment-group" data-toggle="buttons">
                            <label class="ont-btn ont-btn-default ddl-button-group">
                                <input type="radio" id="q156" name="<?php the_ddl_name_attr('image_alignment'); ?>" value="left" /> Left
                            </label>
                            <label class="ont-btn ont-btn-default ddl-button-group">
                                <input type="radio" id="q157" name="<?php the_ddl_name_attr('image_alignment'); ?>" value="center" /> Center
                            </label>
                            <label class="ont-btn ont-btn-default ddl-button-group">
                                <input type="radio" id="q158" name="<?php the_ddl_name_attr('image_alignment'); ?>" value="right" /> Right
                            </label>
                            <label class="ont-btn ont-btn-default ddl-button-group">
                                <input type="radio" id="q159" name="<?php the_ddl_name_attr('image_alignment'); ?>" value="none" checked="checked" /> None
                            </label>
                        </div>
                    </li>
                    <li>
                        <label for="<?php the_ddl_name_attr('image_size'); ?>"><?php _e('Size', 'ddl-layouts') ?>:</label>
                        <select name="<?php the_ddl_name_attr('image_size'); ?>">
                            <?php echo $this->imagebox_cell_get_image_size_options(); ?>
                        </select>
                    </li>
                    <li>
                        <label for="ddl-layout-image_link_to"><?php _e('Link to', 'ddl-layouts') ?>:</label>
                        <select name="ddl-layout-image_link_to">

                        </select>
                    </li>
                    <li class="ddl-border-bottom">
                        <label for="<?php the_ddl_name_attr('image_link_url'); ?>"></label>
                        <input type="text" name="<?php the_ddl_name_attr('image_link_url'); ?>" value="" />
                    </li>

                    <li>
                        <label for="<?php the_ddl_name_attr('image_title'); ?>"><?php _e('Image title attribute', 'ddl-layouts') ?>:</label>
                        <input type="text" name="<?php the_ddl_name_attr('image_title'); ?>" value="" />
                    </li>
                    <li>
                        <label for="<?php the_ddl_name_attr('image_css_class'); ?>"><?php _e('Image CSS class', 'ddl-layouts') ?>:</label>
                        <input type="text" name="<?php the_ddl_name_attr('image_css_class'); ?>" value="" />
                    </li>

                    <li>
                        <label for="<?php the_ddl_name_attr('image_link_rel'); ?>"><?php _e('Link rel', 'ddl-layouts') ?>:</label>
                        <input type="text" name="<?php the_ddl_name_attr('image_link_rel'); ?>" value="" />
                    </li>
                    <li>
                        <label for="<?php the_ddl_name_attr('image_link_css_class'); ?>"><?php _e('Link CSS class', 'ddl-layouts') ?>:</label>
                        <input type="text" name="<?php the_ddl_name_attr('image_link_css_class'); ?>" value="" />
                    </li>
                    <li>
                        <label for="<?php the_ddl_name_attr('image_link_target'); ?>" class="ddl-manual-width"></label>

                        <input type="checkbox" name="<?php the_ddl_name_attr('image_link_target'); ?>"><span class="label"><?php _e('Open link in a new window/tab', 'ddl-layouts') ?></span>
                    </li>
                </ul>
                <?php
                return ob_get_clean();
            }
            
            
            // Callback function for displaying the cell in the editor.
            function imagebox_cell_template_callback() {
                ob_start();
                ?>
                        <div class="cell-content">

                                <p class="cell-name"><?php _e('Image box', 'ddl-layouts'); ?></p>
                                <div class="cell-preview">

                        <#
                            if( content && content.box_image ){
                                var parms = {
                                    org_w: content.box_image_org_w,
                                    org_h: content.box_image_org_h,
                                    effect:content.display_responsive_image,
                                    url:content.box_image,
                                    align: content.image_alignment
                                };

                                _.defaults(parms, {align:'none'});

                                if( content.box_image === '' || _.some( _.values( parms ), function( val ){ return typeof val === 'undefined' } ) ){

                            #>

                            <div class="ddl-image-box-preview">
                                    <img src="<?php echo WPDDL_RES_RELPATH . '/images/cell-icons/image-box.svg'; ?>" height="130px">
                            </div>

                                <# } else {
                                    var img = DDLayout.ImageBoxCell.prototype.returnImagePreviewAsHtmlString.call(this, parms);
                                    #>

                                        <div class="ddl-image-box-preview-image" style="min-height:{{img.h+5}}px">
                                        <# print( img.img ); #>
                                        </div>

                                    <#    }
                                    } else {#>

                                        <div class="ddl-image-box-preview">
                                            <img src="<?php echo WPDDL_RES_RELPATH . '/images/cell-icons/image-box.svg'; ?>" height="130px">
                                        </div>

                                    <# } #>


                                </div
                        </div>
                <?php
                return ob_get_clean();
            }
            
            
            // Callback function for display the cell in the front end.
            function imagebox_cell_content_callback() {
                ob_start();
                $title = get_ddl_field('box_title');
                $content = get_ddl_field('box_content');
                $responsive = get_ddl_field('display_responsive_image');
                $is_responsive = get_ddl_field('is_responsive_image');
                $additional_class = get_ddl_field('image_css_class');
                $allignment = get_ddl_field('image_alignment');
                $image_link_url = get_ddl_field('image_link_url');
                $image_link_rel = get_ddl_field('image_link_rel');
                $link_target = get_ddl_field('image_link_target');
                $img_title = get_ddl_field('image_title');
                $img_link_css_class = get_ddl_field('image_link_css_class');
                $size = get_ddl_field('image_size');
                $responsive_class = $is_responsive ? ' img-responsive' : '';
                $target = '';
                $align = '';
                $title_attr = '';
                $link_class = '';
                $classes = '';

                if( $is_responsive || $responsive != '' || $additional_class != '' ){
                    $responsive = $responsive ? $responsive.' ' : $responsive;
                    $classes = ' class="'.$responsive.$additional_class.$responsive_class.'"';
                }

                if( $allignment && $allignment !== 'none' ){
                    $align = 'align="'.$allignment.'"';
                }

                if( $link_target ){
                    $target = 'target="_blank"';
                }

                if( $img_title ){
                    $title_attr = 'title="'.$img_title.'"';
                }

                if( $img_link_css_class ){
                    $link_class = 'class="'.$img_link_css_class.'"';
                }
                    ?>

                <?php if ( get_ddl_field('disable_bootstrap_thumbnail') === false ){?>
                    <div class="<?php echo WPDDL_Framework::get_thumbnail_class();?>">
                <?php }?>

                    <?php if( $image_link_url !== '' ):?>
                        <a href="<?php echo $image_link_url;?>" rel="<?php echo $image_link_rel; ?>" <?php echo $target;?> <?php echo $link_class;?>>
                    <?php endif;?>

                    <?php if( $size === 'original'):?>
                        <img src="<?php the_ddl_field('box_image'); ?>" <?php echo $classes;?> <?php echo $align;?> <?php echo $title_attr;?>>
                    <?php else:
                        $img = get_attachment_field_url('box_image', $size);
                    ?>
                    <img src="<?php echo $img[0]; ?>" width="<?php echo $img[1]; ?>" height="<?php echo $img[2]; ?>" <?php echo $classes;?> <?php echo $align;?> <?php echo $title_attr;?> >
                    <?php endif;?>

                    <?php if( $image_link_url !== '' ):?>
                        </a>
                    <?php endif;?>

                    <?php if ($title || $content): ?>
                        <div class="caption text-center">
                            <?php if ($title): ?>
                                <h3>
                                    <?php echo $title; ?>
                                </h3>
                            <?php endif; ?>
                            <?php if ($content): ?>
                                <p>
                                    <?php echo $content ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ( !get_ddl_field('disable_bootstrap_thumbnail') ){?>
                </div>
                <?php 
                
                }
                
                return ob_get_clean();
            }
            
            
            
            

    }
    new Layouts_cell_imagebox();
}
