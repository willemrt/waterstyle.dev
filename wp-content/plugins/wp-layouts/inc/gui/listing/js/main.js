var DDLayout = DDLayout || {};


DDLayout.models = {};
DDLayout.models.abstract = {};
DDLayout.models.cells = {};
DDLayout.models.collections = {};

DDLayout.listing = {};
DDLayout.listing.views = {};
DDLayout.listing.models = {};
DDLayout.listing.views.abstract = {};

DDLayout_settings.DDL_JS.ns = head;
DDLayout_settings.DDL_JS.listing_open = {1:true, 2:true, 3:true};


DDLayout_settings.DDL_JS.ns.js(
    DDLayout_settings.DDL_JS.lib_path + "he/he.min.js",
    DDLayout_settings.DDL_JS.common_rel_path + "/utility/js/jstorage.min.js"
	, DDLayout_settings.DDL_JS.lib_path + "prototypes.js"
    , DDLayout_settings.DDL_JS.editor_lib_path + "backbone-overrides.js"

    , DDLayout_settings.DDL_JS.editor_lib_path + "models/abstract/Element.js"
    , DDLayout_settings.DDL_JS.editor_lib_path + "models/collections/Cells.js"
    , DDLayout_settings.DDL_JS.editor_lib_path + "models/collections/Rows.js"
    , DDLayout_settings.DDL_JS.editor_lib_path + "models/cells/Cell.js"
    , DDLayout_settings.DDL_JS.editor_lib_path + "models/cells/Spacer.js"
    , DDLayout_settings.DDL_JS.editor_lib_path + "models/cells/Row.js"
    , DDLayout_settings.DDL_JS.editor_lib_path + "models/cells/ThemeSectionRow.js"
    , DDLayout_settings.DDL_JS.editor_lib_path + "models/cells/Container.js"
    , DDLayout_settings.DDL_JS.editor_lib_path + "models/cells/Layout.js"

	, DDLayout_settings.DDL_JS.listing_lib_path + "models/ListingItem.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "models/ListingItems.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "models/ListingGroup.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "models/ListingGroups.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "models/ListingTable.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "views/abstract/CollectionView.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "views/ListingGroupView.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "views/ListingGroupsView.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "views/ListingItemView.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "views/ListingTableView.js"
    , DDLayout_settings.DDL_JS.listing_lib_path + "views/ListingDuplicateLayoutDialog.js"
    , DDLayout_settings.DDL_JS.res_path + "/js/ddl_change_layout_use_helper.js"
    , DDLayout_settings.DDL_JS.res_path + "/js/ddl-individual-assignment-manager.js"
    ,DDLayout_settings.DDL_JS.res_path + '/js/dd-layouts-parents-watcher.js'
);

(function($){
    WPV_Toolset.Utils.loader = WPV_Toolset.Utils.loader || new WPV_Toolset.Utils.Loader;
	DDLayout_settings.DDL_JS.ns.ready(function(){
        wp.hooks.doAction('ddl-wpml-language-switcher-build', $);
		DDLayout.listing_manager = new DDLayout.ListingMain($);
        WPV_Toolset.Utils.eventDispatcher.trigger('dd-layout-main-object-init');
	});
}(jQuery));


DDLayout.ListingMain = function($)
{
	var self = this
        , button_generic = $('.js-buttons-change-update');

	self._current_layout = null;
	self._post_types_change_nonce = null;
    self._current_dialog = null;

	self.init = function()
	{
			// create a namespace for our js templates to prevent conflict with reserved names in the global namespace
			_.templateSettings.variable = "ddl";
			var json = jQuery.parseJSON( WPV_Toolset.Utils.editor_decode64( jQuery('.js-hidden-json-textarea').text() ) ),
				listing_table = DDLayout.listing.models.ListingTable.get_instance( json );
                DDLayout.parents_watcher = DDLayout.ParentsWatcher($, self);
			    self.listing_table_view = new DDLayout.listing.views.ListingTableView({model:listing_table});

            self.handle_layout_post_types_change();
	};

	self.loadChangeUseDialog = function( data_obj )
	{
		var nonce = data_obj.nonce,
			layout_id = data_obj.layout_id,
			params = {
				action:'change_layout_usage_box',
				'layout-select-set-change-nonce':nonce,
				layout_id:layout_id
			};

        jQuery('#wpcontent').loaderOverlay('show', {class:'loader-overlay-high-z'});

		WPV_Toolset.Utils.do_ajax_post( params, {success:function(response){
			self._current_layout = response.message.layout_id;
			self._post_types_change_nonce = response.message.nonce;

            self._current_dialog = $('#ddl-change-layout-use-for-post-types-box-'+self._current_layout+'-'+data_obj.group +' .ddl-dialog-content');
            var  dialog_wrap = $('#ddl-change-layout-use-for-post-types-box-'+self._current_layout+'-'+data_obj.group);

            self._current_dialog.html( response.message.html_data );
            jQuery('#wpcontent').loaderOverlay('hide');
			jQuery.colorbox({
				href: '#ddl-change-layout-use-for-post-types-box-'+self._current_layout+'-'+data_obj.group,
				inline: true,
				open: true,
				closeButton:false,
				fixed: true,
				top: '50px',
                width:'750px',
                onLoad:function(){
                    DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('ddl-dialog-ass-load', dialog_wrap, self._current_layout );

                },
                onOpen:function()
                {
                    DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('ddl-dialog-ass-open', dialog_wrap, self._current_layout );
                    wp.hooks.doAction('ddl-wpml-init', dialog_wrap, self._current_layout, []);
                },
				onComplete: function() {
                    DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('ddl-dialog-ass-complete', dialog_wrap, self._current_layout);

				},
                onClosed:function(){
                   self.listing_table_view.eventDispatcher.trigger('changes_in_dialog_done');
                },
				onCleanup: function() {
                    self._current_dialog = null;
                    DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('assignment_dialog_close');
				}
			});
		}});
	};


    self.handle_layout_post_types_change = function()
    {
        jQuery(document).on('click', button_generic.selector, function(event){

            DDLayout.changeLayoutUseHelper.eventDispatcher.trigger( 'before_sending_data', event );

            var send = {}, extras = null;

            send[$(this).data('group')] = DDLayout.changeLayoutUseHelper.getLayoutOption($(this).data('group'));

            extras = DDLayout.changeLayoutUseHelper.get_creation_extras( );

            if( null !== extras ) send.extras = extras;

            self.do_ajax_change_call( event, send, 'js_change_layout_usage_for_'+$(this).data('group'), $(this).data('group') );

        });
    };


    self.do_ajax_change_call = function( event, data, action, name )
    {
        var params = {
                 action:action,
                'layout-set-change-post-types-nonce':self._post_types_change_nonce,
                 layout_id:self._current_layout,
                 ddl_lang:wp.hooks.applyFilters('ddl-js-apply-language', null),
                 html: 'listing'
            };

        params = _.extend( params, data );
        params['single_amount_to_show_in_dialog'] = DDLayout.changeLayoutUseHelper.get_current_post_list_handler().get_amount();

        jQuery( event.target ).prop( 'disabled', true).removeClass('button-primary').addClass('button-secondary');

        DDLayout.ChangeLayoutUseHelper.manageSpinner.addSpinner( event.target );

        self.listing_table_view.model.trigger('make_ajax_call',  params, function( model, response, object, args ){
            self.listing_table_view.current = +params.layout_id;
            DDLayout.ChangeLayoutUseHelper.manageSpinner.removeSpinner();
            DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('data_sent_to_server', jQuery(event.target).closest('div.js-change-wrap-box'), name, response.message );
        });
    };

	self.init();
};
