// Extending element functionality and bind them to element.
jQuery.fn.extend(
	{
		yoast_ga_table: function () {
			'use strict';

			/**
			 * Is is possible to have more graph holders, so they will be taken and looped by each to do all
			 * magic for each element.
			 *
			 */
			return this.each(
				function () {

					var element = jQuery(this);
					var table_id = jQuery(element).attr('id');			// Getting ID-attribute from element

					var table = {
						/**
						 * Initialize the object. This will get the data and set the events
						 */
						init: function () {
							this.get_data();
						},

						/**
						 * Doing AJAX response to get the data for the graph
						 */
						get_data: function () {

							var data = {
								action      : 'yoast_dashboard_tabledata',
								_ajax_nonce : yoast_ga_dashboard_nonce,
								graph_id    : table_id,
								period      : 'lastmonth',
								dimension_id: jQuery(element).attr('data-dimension')
							};

							jQuery.getJSON(ajaxurl, data, this.parse_response);
						},

						/**
						 * Parsing the JSON that is returned from request. Method will set the data, axis mapping and
						 * after all setting, it will create the graph
						 *
						 * @param response
						 */
						parse_response: function (response) {
							element.find('table').dataTable(
								{
									columns : [
										{data: 'name'},
										{data: 'value'}
									],
									data    : response.data,
									order   : [1, 'dsc'],

									// Translations
									language: {
										aria             : {
											sortAscending : dashboard_translate.sort_ascending,
											sortDescending: dashboard_translate.sort_descending
										},
										emptyTable       : dashboard_translate.empty_table,
										info             : dashboard_translate.info,
										infoEmpty        : dashboard_translate.info_empty,
										infoFiltered     : dashboard_translate.info_filtered,
										lengthMenu       : dashboard_translate.length_menu,
										loadingRecords   : dashboard_translate.loading_records,
										paginate         : {
											first   : dashboard_translate.pagination_first,
											last    : dashboard_translate.pagination_last,
											next    : dashboard_translate.pagination_next,
											previous: dashboard_translate.pagination_previous
										},
										processing       : dashboard_translate.processing,
										search           : '_INPUT_',
										searchPlaceholder: dashboard_translate.search_placeholder,
										zeroRecords      : dashboard_translate.zero_records
									}
								}
							);
						}
					};

					table.init();
				}
			);

		},

		yoast_ga_graph: function () {
			'use strict';

			/**
			 * Is is possible to have more graph holders, so they will be taken and looped by each to do all
			 * magic for each element.
			 *
			 */
			return this.each(
				function () {

					var element = jQuery(this);
					var graph_id = jQuery(element).attr('id');			// Getting ID-attribute from element
					var target = document.getElementById(graph_id);	// Element obtaining doing the W3c way

					// Object for doing the magic
					var graph = {
						data       : [],		// Placeholder for all getted data
						axis       : {		// The values for X and Y axis
							x: [],
							y: []
						},
						hover      : [],
						width      : 780,	// The width of the graph
						height     : 240,
						graph      : '',		// Graph element
						graph_axis : {		// The axis for X and Y
							x: '',
							y: ''
						},
						graph_hover: [],	// Hover element

						/**
						 * Initialize the object. This will get the data and set the events
						 */
						init: function () {
							this.get_data();
							this.add_events();
						},

						/**
						 * Adding the update event on object
						 *
						 */
						add_events: function () {
							var _this = this;
							jQuery(element).on('graph_update', function (event, response) {
								_this.update(response, _this);
							});
						},

						/**
						 * Doing AJAX response to get the data for the graph
						 */
						get_data: function () {

							var data = {
								action     : 'yoast_dashboard_graphdata',
								_ajax_nonce: yoast_ga_dashboard_nonce,
								graph_id   : graph_id,
								period     : 'lastmonth'
							};

							jQuery.getJSON(ajaxurl, data, this.parse_response);
						},

						/**
						 * Parsing the JSON that is returned from request. Method will set the data, axis mapping and
						 * after all setting, it will create the graph
						 *
						 * @param response
						 */
						parse_response: function (response) {
							graph.set_data(response.data);

							if (response.mapping.x !== undefined) {
								graph.set_x_axis_mapping(response.mapping.x);
							}

							if (response.mapping.y !== undefined) {
								graph.set_y_axis_mapping(response.mapping.y);
							}

							if (response.mapping.hover !== undefined) {
								graph.set_hover_mapping(response.mapping.hover);
							}

							graph.create();
						},

						/**
						 * Setting the data with all values
						 *
						 * @param data
						 */
						set_data: function (data) {
							this.data = data;
						},

						/**
						 * Adding data to graph.data object
						 *
						 * @param data_to_add
						 */
						add_data: function (data_to_add) {
							graph.data.push(data_to_add);
						},

						/**
						 * Setting the x-axis with all mapping values
						 *
						 * @param mapping
						 */
						set_x_axis_mapping: function (mapping) {
							graph.axis.x = mapping;
						},

						/**
						 * Add value to the x axis
						 *
						 * @param mapping_to_add
						 */
						add_x_axis_mapping: function (mapping_to_add) {
							this.axis.x.push(mapping_to_add);
						},

						/**
						 * Setting the x-axis with all mapping values
						 *
						 * @param mapping
						 */
						set_y_axis_mapping: function (mapping) {
							graph.axis.y = mapping;
						},

						/**
						 * Setting the x-axis with all mapping values
						 *
						 * @param mapping
						 */
						set_hover_mapping: function (mapping) {
							graph.hover = mapping;
						},

						/**
						 * Add value to the x axis
						 *
						 * @param mapping_to_add
						 */
						add_y_axis_mapping: function (mapping_to_add) {
							this.axis.y.push(mapping_to_add);
						},

						/**
						 * Creating all magic: the graph, axises and hovers and render graph after everything is created
						 */
						create: function () {
							this.truncate_element();
							this.create_graph();
							this.create_axis();
							this.create_hover();
							this.render();
						},

						/**
						 * Empty element to be sure nothing is displayed multiple times
						 */
						truncate_element: function () {
							element.find('> div').html('');
						},

						/**
						 * Creating the graph
						 */
						create_graph: function () {
							this.graph = new Rickshaw.Graph(
								{
									element : target.querySelector('.yoast-graph-holder'),
									width   : this.width,
									height  : this.height,
									series  : [{
										name  : element.attr('data-label'),
										color : '#e6f4fa',
										stroke: '#058dc7',
										data  : this.data
									}],
									renderer     : 'area',
									stroke       : true,
									interpolation: 'linear',
									padding : {
										top   : 0.10,
										bottom: 0.02
									}
								}
							);
						},

						/**
						 * Holder for creating the axises, this method will call this.create_x_axis and this.create_y_axis
						 */
						create_axis: function () {
							this.create_x_axis();
							this.create_y_axis();
						},

						/**
						 * Creating the x_axis
						 */
						create_x_axis: function () {
							if (target.querySelector('.yoast-graph-xaxis') !== null) {
								this.graph_axis.x = new Rickshaw.Graph.Axis.X(
									{
										element      : target.querySelector('.yoast-graph-xaxis'),
										graph        : this.graph,
										tickFormat   : this.format_axis_x,
										grid         : true,
										orientation  : 'bottom',
										tickValues   : Object.keys(this.axis.x)
									}
								);
							}
						},

						/**
						 * Creating the y_axis
						 *
						 */
						create_y_axis: function () {
							if (target.querySelector('.yoast-graph-xaxis') !== null) {
								this.graph_axis.y = new Rickshaw.Graph.Axis.Y(
									{
										element    : target.querySelector('.yoast-graph-yaxis'),
										graph      : this.graph,
										orientation: 'left',
										height     : '240',
										// If n is 0 return emptystring, to prevent zero displayed on graph
										tickFormat : function (n) {
											return (n === 0) ? '' : n;
										}
									}
								);
							}

						},

						/**
						 * Creating hover details on graph
						 *
						 */
						create_hover: function () {
							this.graph_hover = new Rickshaw.Graph.HoverDetail(
								{
									graph    : this.graph,
									formatter: function (series, x, y) {
										var value = graph.hover[x];

										if (element.attr('data-percent') === '1') {
											value += '%';
										}
										var swatch = '<span class="detail_swatch" style="background-color: ' + series.color + '"></span>';
										var content = swatch + value + '<br />';
										return content;
									}
								}
							);

						},

						/**
						 * Render the graph object
						 */
						render: function () {
							this.graph.render();
						},

						/**
						 * Returns the formatting for the x-axis
						 *
						 * @param number
						 * @returns number
						 */
						format_axis_x: function (number) {
							return graph.axis.x[number];
						},

						/**
						 * Will be used to update the graph with new data
						 * @param response
						 * @param _this
						 */
						update: function (response, _this) {
							_this.add_data(response.data);
							_this.add_x_axis_mapping(response.mapping);

							_this.graph.update();
							_this.render();
						}
					};

					graph.init();
				}
			);

		},

		/**
		 * Adding function to object to pass some response data to event graph_update
		 *
		 * This can be used for updating the graph
		 *
		 * @param response
		 */
		yoast_ga_graph_update: function (response) {
			'use strict';
			jQuery(this).trigger('graph_update', [response]);
		},

		/**
		 * Adding function to object to pass some response data to event graph_update
		 *
		 * This can be used for updating the graph
		 *
		 * @param response
		 */
		yoast_ga_table_update: function (response) {
			'use strict';
			jQuery(this).trigger('table_update', [response]);
		}
	}
);

function dimension_switch(switch_select) {
	'use strict';

	switch_select = jQuery(switch_select);

	var list_tables = switch_select.closest('.wpseotab').find('.yoast-data-table');
	var select_options = '';

	jQuery.each(list_tables, function (num, table) {
		select_options += '<option value="' + jQuery(table).attr('id') + '">' + jQuery(table).attr('data-label') + '</option>';
	});

	switch_select.append(select_options);

	// First we have to hide all tables except the first one.
	list_tables.not(':eq(0)').hide();

	setTimeout(
		function () {
			switch_select.chosen().change(function () {
				// Hide all elements
				list_tables.hide();

				// Show the selected on
				jQuery('#' + jQuery(this).val()).show();
			});
		},
		1500
	);

}

jQuery(
	function () {
		'use strict';

		jQuery('.yoast-graph').yoast_ga_graph();
		jQuery('.yoast-data-table').yoast_ga_table();


		/*
		 setTimeout(
		 function() {
		 jQuery('#graph-visitors').yoast_ga_graph_update(
		 {
		 data   : {x: 30, y: 10},
		 mapping: ['andy']
		 }
		 );
		 },
		 500
		 );
		 */


	}
);