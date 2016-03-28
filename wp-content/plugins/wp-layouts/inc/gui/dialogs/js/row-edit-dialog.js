// row-edit-dialog.js

DDLayout.RowDialog = function($)
{
    var self = this;

    _.extend( DDLayout.RowDialog.prototype, new DDLayout.Dialogs.Prototype(jQuery) );

    self.init = function() {

        jQuery(document).on('click', '.js-row-dialog-edit-save,.js-row-dialog-edit-add-row', {dialog: self}, function(event) {
            event.preventDefault();

	        var $css_input_id = jQuery( 'input.js-edit-css-id', jQuery(this).parent().parent()),
		        value = $css_input_id.val();

	        if( DDLayout.ddl_admin_page.htmlAttributesHandler.check_id_exists( $css_input_id, value ) )
	        {
                event.data.dialog._save( jQuery(this) );
	        }
        });

        jQuery(document).on('click', '#ddl-row-edit .js-ddl-show', {dialog: self}, function(event) {
            event.preventDefault();
            jQuery('.js-front-end-options').show();
            jQuery('.js-ddl-show').hide();
            jQuery('.js-ddl-hide').show();

        });

        jQuery(document).on('click', '#ddl-row-edit .js-ddl-hide', {dialog: self}, function(event) {
            event.preventDefault();
            jQuery('.js-front-end-options').hide();
            jQuery('.js-ddl-show').show();
            jQuery('.js-ddl-hide').hide();

        });
		
		jQuery('#js-row-edit-mode figure').on('click', function(event) {
			jQuery('#js-row-edit-mode figure').each( function () {
				jQuery(this).removeClass('selected');
			})
			jQuery(this).addClass('selected');

			var radio = jQuery(this).closest('li').find('input[name="row_type"]');
			radio.trigger('click');

		});

    };

    // TODO: ADD JS PREFIXES
    // TODO: Assign repetitive elements to variables. for example
    // var $layoutType = jQuery('#ddl-row-edit #ddl-row-edit-layout-type');
    self.show = function(mode, row_view) {

        self.setCachedElement( row_view.model.toJSON() );

        var has_child_layout_alone = self.row_doesnt_render_in_front_end( row_view.model );

        if( has_child_layout_alone ){
            jQuery('.ddl-tab-right').hide();
        } else {
            jQuery('.ddl-tab-right').show();
        }

        jQuery('.js-edit-dialog-close').css('float', 'left')


		if (row_view.is_top_level_row() && has_child_layout_alone === false ) {
			jQuery('#js-row-edit-mode').show();
            jQuery('#js-row-not-render-message').hide();

            jQuery('.js-css-styling-controls').removeClass('from-top-0').show();
            jQuery('.ddl-dialog-content').removeClass('pad-top-0');
            jQuery('.js-ddl-form-row').removeClass('ddl-zero');

		} else if( row_view.is_top_level_row() && has_child_layout_alone ) {
            jQuery('#js-row-edit-mode').hide();
            jQuery('#js-row-not-render-message').show();
            jQuery('.js-css-styling-controls').hide();
            jQuery('.ddl-dialog-content').removeClass('pad-top-0');
            jQuery('.js-ddl-form-row').removeClass('ddl-zero');
            jQuery('.js-row-dialog-edit-save').removeClass('button-primary').prop('disabled', true);
        } else {
			jQuery('#js-row-edit-mode').hide();
            jQuery('#js-row-not-render-message').hide();

            jQuery('.ddl-dialog-content').addClass('pad-top-0');
            jQuery('.js-css-styling-controls').addClass('from-top-0').css('border-top', 0).show();
            jQuery('.js-ddl-form-row').addClass('ddl-zero');
		}

        if (mode == 'edit') {
            jQuery('#ddl-row-edit').data('mode', 'edit-row');
            jQuery('#ddl-row-edit').data('row_view', row_view);

            jQuery('input[name="ddl-row-edit-row-name"]').val(row_view.model.get('name'));
	        jQuery('input.js-edit-css-class', jQuery('#ddl-row-edit') ).val(row_view.model.get('additionalCssClasses'));
	        jQuery('input.js-edit-css-id', jQuery('#ddl-row-edit') ).val( row_view.model.get('cssId') );
            jQuery('#ddl-row-edit select[name="ddl_tag_name"]').val( row_view.model.get('tag') );

            jQuery('#ddl-row-edit .js-dialog-edit-title').show();
            jQuery('#ddl-row-edit .js-row-dialog-edit-save').show();

            jQuery('#ddl-row-edit .js-dialog-add-title').hide();
            jQuery('#ddl-row-edit .js-row-dialog-edit-add-row').hide();

	        jQuery('#ddl-row-edit #ddl-row-edit-layout-type').parent().hide();


			self._set_row_mode(row_view.model.get('mode'));

        } else if (mode == 'add') {
            jQuery('#ddl-row-edit').data('mode', 'add-row');
            jQuery('#ddl-row-edit').data('row_view', row_view);

            jQuery('input[name="ddl-row-edit-row-name"]').val('');
            jQuery('input[name="ddl-row-edit-row-class-name"]').val('');
			jQuery('input[name="ddl-row-edit-css-id"]').val('');
            jQuery('#ddl-row-edit select[name="ddl_tag_name"]').val('div');

            jQuery('#ddl-row-edit .js-dialog-edit-title').hide();
            jQuery('#ddl-row-edit .js-row-dialog-edit-save').hide();

            jQuery('#ddl-row-edit .js-dialog-add-title').show();
            jQuery('#ddl-row-edit .js-row-dialog-edit-add-row').show();


            jQuery('#ddl-row-edit #ddl-row-edit-layout-type').parent().show();

            if (!row_view.can_add_fixed_row_below_this()) {
                // disable layout selection
                jQuery('#ddl-row-edit #ddl-row-edit-layout-type').val('fluid');
                jQuery('#ddl-row-edit #ddl-row-edit-layout-type').prop('disabled', 'disabled');
                jQuery('.js-only-fluid-message').show();
            } else {
                jQuery('#ddl-row-edit #ddl-row-edit-layout-type').prop('disabled', false);
                jQuery('.js-only-fluid-message').hide();
            }

			self._set_row_mode('normal');

        }

        jQuery.colorbox({
            href: '#ddl-row-edit',
            closeButton:false,
            onComplete: function() {

            },
            onCleanup:function(){
                jQuery('.js-css-styling-controls').removeClass('from-top-0').show();
                jQuery('.ddl-dialog-content').removeClass('pad-top-0');
                jQuery('.js-ddl-form-row').removeClass('ddl-zero');
                jQuery('.js-row-dialog-edit-save').addClass('button-primary').prop('disabled', false);
            }
        });
    };


    self._save = function (caller) {

        var target_row_view = jQuery('#ddl-row-edit').data('row_view');

        if (jQuery('#ddl-row-edit').data('mode') == 'add-row') {

            var layout_type = jQuery('select[name="ddl-row-edit-layout-type"]').val();
            target_row_view.addRow(jQuery('input[name="ddl-row-edit-row-name"]').val(),
                                   jQuery('input[name="ddl-row-edit-row-class-name"]').val(),
                                   layout_type);

        } else if (jQuery('#ddl-row-edit').data('mode') == 'edit-row') {


            DDLayout.ddl_admin_page.save_undo();

            var target_row = target_row_view.model;

            target_row.set('name', jQuery('input[name="ddl-row-edit-row-name"]').val());
            target_row.set('additionalCssClasses', jQuery('input.js-edit-css-class', jQuery('#ddl-row-edit') ).val() );
            target_row.set('cssId', jQuery('input.js-edit-css-id', jQuery('#ddl-row-edit') ).val() );
            target_row.set('tag', jQuery('#ddl-row-edit select[name="ddl_tag_name"]').val() );
			target_row.set( 'mode', self._get_row_mode() );

	       // target_row.set('cssClass',jQuery('#ddl-row-edit #ddl-row-edit-layout-type').val() );

            //target_row_view.render();

            DDLayout.ddl_admin_page.save_layout_from_dialog( caller, target_row_view, self.cached_element, false, self );
        }

        if( self.is_save_and_close(caller) ) jQuery.colorbox.close();
        return false;
    };

	self._set_row_mode = function (mode) {
		jQuery('#ddl-row-edit input[name="row_type"]').each( function () {
			var figure = jQuery(this).closest('li').find('figure');
			if (jQuery(this).val() == mode) {
				jQuery(this).prop('checked', true);
				figure.addClass('selected');
			} else {
				jQuery(this).prop('checked', false);
				figure.removeClass('selected');
			}
		});


	}

	self._get_row_mode = function () {
		var mode = 'normal';
		jQuery('#ddl-row-edit input[name="row_type"]').each( function () {
			if (jQuery(this).is(':checked')) {
				mode = jQuery(this).val();
			}
		});

		return mode;
	}

    self.init();
};