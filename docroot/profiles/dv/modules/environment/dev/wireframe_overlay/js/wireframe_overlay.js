(function ($, Drupal, drupalSettings) {

    Drupal.behaviors.WireframeOverlayBehavior = {
        attach: function (context, settings) {
            // can access setting from 'drupalSettings';
            var wireframe_overlay = drupalSettings.wireframe_overlay;

            if(wireframe_overlay) {

                var toolbar = $('#toolbar-bar');
                var toggle_link = $('<div class="toolbar-tab"><a href="#" class="wf-toggle toolbar-item">Wireframe</a></div>');
                toolbar.once('toggle-link').append(toggle_link);

                var wf_wrapper = $('<div>', {id: 'wireframe'});
                var overlay_wrapper = $('<div>', {id: 'wireframe-overlay'});

                $("body").once('wf-wrapper').append(wf_wrapper);

                wf_wrapper.once('wf-overlay').append(overlay_wrapper);

                var wf_image = $('<img>', {src: drupalSettings.wireframe_overlay.image, class: 'wf-image'});
                overlay_wrapper.once('wf-image').append(wf_image);

                var wf_label = $('<p>', {class: "wf-label"}).text(drupalSettings.wireframe_overlay.label);
                overlay_wrapper.once('wf-label').append(wf_label);

                var wf_description = $('<p>', {class: "wf-description"}).text(drupalSettings.wireframe_overlay.description);
                overlay_wrapper.once('wf-description').append(wf_description);

                // slider stuff
                var wf_slider = $('<div>', {id: 'wf-slider'});
                var wf_slider_opacity = 37;

                toolbar.once('wf-slider').append(wf_slider);

                wf_slider.slider({
                    range: "min",
                    value: wf_slider_opacity,
                    min: 1,
                    max: 100,
                    slide: function( event, ui ) {
                        wf_slider_opacity = ui.value;
                        wf_image.css('opacity', wf_slider_opacity/100);
                    }
                });

                var toggle_link_position = toggle_link.offset();

                wf_slider.css('top', toggle_link_position.top + 28);
                wf_slider.css('left', toggle_link_position.left - 20);

                toggle_link.click(function () {
                    overlay_wrapper.toggle();
                    wf_image.css('opacity', wf_slider_opacity/100);
                    wf_slider.toggle();
                    // save opacity to a cookie?
                });
            }
        }
    };

})(jQuery, Drupal, drupalSettings);