(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.initCustomScrollBar = {
    attach: function () {
      $(window).on("load",function(){
        var chartBlockWrapper = $('.chart-block');

        chartBlockWrapper.mCustomScrollbar({
          axis: 'x',
          theme: 'dark-thin',
          autoExpandScrollbar: true,
          advanced: {
            autoExpandHorizontalScroll: true
          }
        });
      });
    }
  };

})(jQuery, Drupal);
