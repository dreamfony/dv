(function ($, Drupal, drupalSettings) {

    Drupal.behaviors.WireframeOverlayBehavior = {
        attach: function (context, settings) {
            // can access setting from 'drupalSettings';
            var wireframe_overlay = drupalSettings.wireframe_overlay;

            if(wireframe_overlay) {

                var display;

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

                toggle_link.click(function () {
                    overlay_wrapper.toggle(display);
                });

            }
        }
    };

})(jQuery, Drupal, drupalSettings);