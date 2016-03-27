(function ($) {
define([
	'text!elements/upfront-post-data/tpl/views.html'
], function(tpl) {

var l10n = Upfront.Settings.l10n.post_data_element;
var $template = $(tpl);

var Util = {
	/**
	 * Gets the current preset data from in-memory collection
	 *
	 * Used instead of the stored preset on server
	 *
	 * @param {String} element Element type
	 * @param {String} preset_id Preset ID
	 *
	 * @return {Object} Local preset data
	 */
	get_preset_data: function (element, preset_id) {
		var data = {};
		if (!element || !preset_id) return data;

		var prefix = element + '_elementPresets',
			all = (Upfront.mainData || {})[prefix] || []
		;
		return _.findWhere(all, {id: preset_id}) || data;
	}
};

var Views = {
	
	DEFAULT: 'post_data',

	_view: Backbone.View.extend({
		_do_cache: true,

		render: function (only_objects) {
			var me = this,
				model = Upfront.Util.model_to_json(this.model),
				props = model.properties || {},
				objects = model.objects || {},
				data_type = this.model.get_property_value_by_name("data_type"),
				current_preset = this.model.get_property_value_by_name("preset"),
				preset_data = Util.get_preset_data(data_type, current_preset),
				data = {
					props: props,
					objects: objects,
					preset_data: preset_data,
					post_id: this.element.postId
				}
			;

			if ( this.element.authorId ) {
				data.author_id = this.element.authorId;
			}
			if ( this.element.postDate ) {
				data.post_date = this.element.postDate;
			}
			this._post_data_load = Upfront.Util
				.post({
					action: "upfront_post-data-load",
					data: data
				})
				.success(function (response) {
					if (response.data && response.data.post_data) {
						me.render_object_view(response.data.post_data, only_objects);
						if ( me._do_cache ) {
							me._cached_data = response.data.post_data;
						}
						me.$el
							.empty()
							.removeClass('upfront_post-data-loading');
					}
					else { 
						me.$el
							.empty()
							.append(me.tpl.error({l10n: l10n}))
							.removeClass('upfront_post-data-loading');
					}
				})
				.error(function () {
					me.$el
						.empty()
						.append(me.tpl.error({l10n: l10n}))
						.removeClass('upfront_post-data-loading');
				})
			;
			this.$el
				.empty()
				.append(this.tpl.load({l10n: l10n}))
				.addClass('upfront_post-data-loading');
		},
		
		/**
		 * Re-render with the same cached data
		 * @param {Array} only_objects
		 */
		rerender: function (only_objects) {
			if ( this._cached_data ) {
				this.render_object_view(this._cached_data, only_objects);
			}
			else {
				this.render();
			}
		},
		
		/**
		 * Render the child object view
		 * @param {Object} data
		 * @param {Array} only_objects
		 */
		render_object_view: function (data, only_objects) {
			if ( ! _.isArray(only_objects) ) only_objects = [];
			var me = this;
			this.model.get('objects').each(function(object){
				var view = Upfront.data.object_views[object.cid],
					type = object.get_property_value_by_name('part_type')
				;
				if ( only_objects.length > 0 && ! _.contains(only_objects, type) ) return;
				if ( !view || !type || !data[type] ) return;
				view.render_view(data[type]);
				Upfront.Events.trigger('entity:object:refresh', view);
			});
		},
		
		tpl: {
			main: _.template($template.filter("#post-data").html()),
			error: _.template($template.filter("#error").html()),
			load: _.template($template.filter("#loading").html())
		}
	}),

};

Views.post_data = Views._view.extend({
	className: 'upfront_post-data-view upfront_post-data-post_data'
});

Views.author = Views._view.extend({
	className: 'upfront_post-data-view upfront_post-data-author'
});

Views.taxonomy = Views._view.extend({
	className: 'upfront_post-data-view upfront_post-data-taxonomy'
});

Views.featured_image = Views._view.extend({
	className: 'upfront_post-data-view upfront_post-data-featured_image'
});

Views.comments = Views._view.extend({
	className: 'upfront_post-data-view upfront_post-data-comments'
});

Views.meta = Views._view.extend({
	className: 'upfront_post-data-view upfront_post-data-meta'
});

return Views;

});
})(jQuery);
