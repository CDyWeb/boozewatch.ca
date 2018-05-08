(function ($) {
  $.fn.tabs = function( options ) {
    var that=this;
    var settings = $.extend( { selected: 0 }, options );
    var ul=this.find('UL:first');
    if (ul.length==1) {
      ul.addClass('nav').addClass('nav-tabs');
      var page_index=0;
      var pages=[];
      var container=$('<div class="tab-content"></div>');
      ul.after(container);
      container=$(container[0]);
      ul.children().each(function(i,li) {
        if (li.nodeName=='LI') {
          li=$(li);
          var a=li.find('A:first');
          if (a.length==1) {
            a.attr('data-toggle','tab');
            if (page_index==settings.selected) li.addClass('active');
            var div_id=a.attr('href').replace(/^#/,'');
            var div=$('#'+div_id+':first');
            if (div.length==1) {
              div=$(div).detach();
              container.append(div);
              div.addClass('tab-pane');
              if (page_index==settings.selected) div.addClass('active');
            }
            page_index++;
          }
        }
      });
    }
    return this;
  };
} (jQuery));