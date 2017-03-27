(function ($, Drupal) {
    Drupal.behaviors.kpiAnalyticsRenderMorris = {
        attach: function (context, settings) {
            $(context).find('div.morris_line').once('renderLine').each(function () {
                var uuid = $(this).attr('id');
                var options = settings.kpi_analytics.morris.line[uuid].options;
                Morris.Line(options);
            });
        }
    };
})(jQuery, Drupal);
