(function ($) {
define([
	'scripts/upfront/inline-panels/item',
	'scripts/upfront/inline-panels/control'
], function (Item, Control) {
	var l10n = Upfront.mainData.l10n.image_element;

	var TooltipControl = Control.extend({
		multiControl: true,

		events: {
			'click': 'onClickControl',
			'click .upfront-inline-panel-item': 'selectItem'
		},

		initialize: function() {
			var me = this;
			$(document).click(function(e){
				var	target = $(e.target);

				if(target.closest('#page').length && target[0] !== me.el && !target.closest(me.el).length && me.isOpen) {
					me.close();
				}
			});
		},

		onClickControl: function(e){
			if (this.isDisabled) {
				return;
			}

			e.preventDefault();

			this.clicked(e);

			this.$el.siblings('.upfront-control-dialog-open').removeClass('upfront-control-dialog-open');

			if (this.isOpen) {
				this.close();
			} else {
				this.open();
			}
		},

		open: function() {
			this.isOpen = true;
			this.$el.addClass('upfront-control-dialog-open');
		},

		close: function() {
			this.isOpen = false;
			this.$el.removeClass('upfront-control-dialog-open');
		},

		render: function() {
			Item.prototype.render.call(this, arguments);
			var captionControl = this.$('.uimage-caption-control'),
				me = this,
				selectedItem
			;

			if(!this.$el.hasClass('uimage-caption-control-item')) {
				this.$el.addClass('uimage-caption-control-item');
			}

			if(!captionControl.length){
				captionControl = $('<div class="uimage-caption-control inline-panel-control-dialog"></div>');
				this.$el.append(captionControl);
			}
			_.each(this.sub_items, function(item, key){
				if(key === me.selected){
					item.setIsSelected(true);
				} else {
					item.setIsSelected(false);
				}
				item.render();
				item.$el.find('i').addClass('upfront-icon-region-caption');
				captionControl.append(item.$el);
				me.listenTo(item, 'click', me.selectItem);
			});

			selectedItem = this.sub_items[this.selected];
					if(selectedItem){
							if( typeof selectedItem.icon !== 'undefined' ){
									this.$el.children('i').addClass('upfront-icon-region-' + selectedItem.icon);
							}else if( typeof selectedItem.label !== 'undefined' ){
									this.$el.find('.tooltip-content').append( ': ' +  selectedItem.label );
							}
					}
		},

		get_selected_item: function () {
			return this.selected;
		},

		selectItem: function(e){
			var found = false,
				target = $(e.target).is('i') ? $(e.target) : $(e.target).find('i');

			_.each(this.sub_items, function(item, key){
				if(target.hasClass('upfront-icon-region-' + item.icon)) {
					found = key;
				}

				if( !found && $(e.target).closest('.upfront-inline-panel-item').attr('id') === item.id ){
					found = key;
				}

			});

			if(found){
				this.selected = found;
				this.render();
				this.trigger('select', found);
			}
		},

		setDisabled: function(isDisabled) {
			this.isDisabled = isDisabled;
			if (isDisabled) {
				this.tooltip = l10n.ctrl.caption_position_disabled;
			} else {
				this.tooltip = l10n.ctrl.caption_display;
			}
		}
	});

	return TooltipControl;
});
})(jQuery);
