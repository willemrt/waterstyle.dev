global._ = require('underscore');
var assert = require('assert'),
	extend = require('./lib/extend')
;

describe('Models', function () {


	describe('Post', function () {
		
		before(function () {
			/**
			 * Mock jQuery as global object
			 *
			 * @type {Object}
			 */
			global.jQuery = {};

			/**
			 * Mock Backbone as local object
			 *
			 * @type {Object}
			 */
			var Backbone = {
				Events: {},
				Model: function () {
					this.attributes = {};
				},
				Collection: function () {},
			};

			/**
			 * Mock Model/Collection extend
			 *
			 * @type {function}
			 */
			Backbone.Model.extend = Backbone.Collection.extend = extend;

			/**
			 * Mock model setter
			 *
			 * @param {String} key Storage key
			 * @param {mixed} value Value to store
			 */
			Backbone.Model.prototype.set = function (key, value) {
				this.attributes[key] = value;
			}

			/**
			 * Mock Upfront global object
			 *
			 * @type {Object}
			 */
			global.Upfront = {
				Util: {
					format_date: function(date, show_time, show_seconds){
						if (!date || !date.getFullYear) {
							if (date && date.length) {
								// Attempt to convert to proper object
								var old_date = date;
								date = new Date(Date.parse(date));
								if (!date) return old_date;
							}
							// If we're still here, and still have no date... bad luck
							if (!date|| !date.getFullYear) return date;
						}
						var output = date.getFullYear() + '/',
							day = date.getDate(),
							month = (date.getMonth()+1)
						;
						if(day < 10) day = '0' + day;
						if(month < 10) month = '0' + month;

						output += month + '/' + day;

						if(show_time){
							var hours = date.getHours(),
								minutes = date.getMinutes()
							;
							output += ' ' +
								(hours < 10 ? '0' : '') +
								hours + ':' +
								(minutes < 10 ? '0' : '') +
								minutes
							;
							if(show_seconds){
								var seconds = date.getSeconds();
									output += ':' +
										(seconds < 10 ? '0' : '') +
										seconds
								;
							}
						}
						return output;
					}
				}
			};

			/**
			 * Testable object which receives the model definitions
			 *
			 * @type {Object}
			 */
			global.Testable = {};

			/**
			 * Mock define implementation.
			 * Assigns whatever gets defined to the Testable object
			 */
			global.define = function () {
				var args = Array.prototype.slice.call(arguments),
					cback = args.pop()
				;
				Testable = cback.apply(this, [Backbone]);
			};

			require('../../scripts/upfront/upfront-models');
		});


		it('should instantiate post model', function (done) {
			var post = new Testable.Models.Post();
			assert.ok(post instanceof Testable.Models.Post);
			assert.deepEqual(post.modelName, 'post');
			done();
		});

		it('should get/set local date', function (done) {
			var post = new Testable.Models.Post();
			var expected_date = new Date(),
				received_date
			;
			post.set('post_date', expected_date);
			received_date = post.get('post_date');
			assert.deepEqual(expected_date.getYear(), received_date.getYear());
			assert.deepEqual(expected_date.getMonth(), received_date.getMonth());
			assert.deepEqual(expected_date.getDate(), received_date.getDate());
			assert.deepEqual(expected_date.getHours(), received_date.getHours());
			assert.deepEqual(expected_date.getMinutes(), received_date.getMinutes());
			assert.deepEqual(expected_date.getSeconds(), received_date.getSeconds());
			done();
		});

	});
});
