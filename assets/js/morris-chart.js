(function ($, Drupal) {

    'use strict';

    Drupal.behaviors.kpiAnalyticsRenderMorris = {
        attach: function (context, settings) {
            $(context).find('div.morris_chart').once('renderChart').each(function () {
                var uuid = $(this).attr('id');
                var options = settings.kpi_analytics.morris.chart[uuid].options;

                if (!options.plugin) {
                  options.plugin = 'Line';
                }

                new Morris[options.plugin](options);
            });
        }
    };

})(jQuery, Drupal);
