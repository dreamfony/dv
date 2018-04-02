(function ($, Drupal, drupalSettings, WireframeOverlay) {


    /**
     * Wireframe Overlay module namespace.
     *
     * @namespace
     *
     */
    WireframeOverlay = WireframeOverlay || {};

    WireframeOverlay.active_g = [];
    WireframeOverlay.active_h2 = [];

    /**
     * Behaviour
     *
     * @type {{attach: Drupal.behaviors.MarkdownSourceBehavior.attach}}
     */
    Drupal.behaviors.MarkdownSourceBehavior = {
        attach: function (context, settings) {

            if(!Drupal.behaviors.MarkdownSourceBehavior.done){
                // get wireframe settings - currently unused
                var wireframe_overlay = drupalSettings.wireframe_overlay;

                // initialize mermaid
                mermaid.initialize({startOnLoad:true});

                // convert markdown to parsable html
                var converter = new showdown.Converter();
                var url = 'https://raw.githubusercontent.com/dreamfony/dv/develop/docroot/profiles/dv/docs/workflow.md';

                $.ajax({
                    url: url,
                    type: 'get',
                    async: false,
                    cache: false,
                    success: function(markdown) {

                        var html = $( '<div id="md-html">' + converter.makeHtml( markdown ) + '</div>');

                        // get mermaid objects from html
                        var objects = WireframeOverlay.getMermaidObjects(html);
                        console.log(objects);

                        // add proper indentations to lists
                        html = WireframeOverlay.addIndentations(html);

                        // get mermaid syntax
                        var mermaid_syntax = WireframeOverlay.genMermaidSyntax(objects, html);
                        console.log(mermaid_syntax);

                        // append html and mermaid graph to document
                        var append_element = $('.region--content');
                        var append_element_position = append_element.offset().right;
                        append_element.once('wf-slider').append(html);

                        WireframeOverlay.toc = $('<div class="mermaid" id="toc">' + mermaid_syntax + '</div>');
                        append_element.once('mermaid').append( WireframeOverlay.toc );
/*

                        var top = WireframeOverlay.toc.offset().top;

                        $(window).scroll(function (event) {
                            var y = $(this).scrollTop();
                            if (y >= top)
                                WireframeOverlay.toc.addClass('fixed');
                            else
                                WireframeOverlay.toc.removeClass('fixed');
                            WireframeOverlay.toc.width( WireframeOverlay.toc.parent().width() );
                        });
*/
                    }
                });

                // on click label
                WireframeOverlay.toc.on("click", "g.label", function() {

                    var markupid = $(this).parent('g').attr('id');
                    $('html, body').animate({
                        scrollTop: $('h2#' + markupid).offset().top - 100
                    }, 100);

                    WireframeOverlay.setInactive();
                    WireframeOverlay.setActive(markupid);
                });

                // on click h2
                $("#md-html").on("click", "h2", function() {

                    var h2id = $(this).attr('id');
                    $('html, body').animate({
                        scrollTop: $('#toc').offset().top - 100
                    }, 100);

                    WireframeOverlay.setInactive();
                    WireframeOverlay.setActive(h2id);
                });


                Drupal.behaviors.MarkdownSourceBehavior.done = true;

            }
        }
    };

    /**
     * Set element Inactive.
     */
    WireframeOverlay.setInactive = function () {
        if(WireframeOverlay.active_h2 instanceof jQuery && WireframeOverlay.active_h2.hasClass('active')) {
            WireframeOverlay.active_h2.removeClass('active');
        }

        if(WireframeOverlay.active_g instanceof jQuery && WireframeOverlay.active_g.hasClass('active')) {
            WireframeOverlay.active_g.removeClass('active');
        }
    };

    /**
     * Set element active.
     *
     * @param elementId
     */
    WireframeOverlay.setActive = function (elementId) {

        var active_g = $('#toc g#' + elementId + ' div');
        var active_h2 = $('#md-html h2#' + elementId);

        // mark active element for color change
        active_h2.addClass('active');
        active_g.addClass('active');

        WireframeOverlay.active_h2 = active_h2;
        WireframeOverlay.active_g = active_g;
    };


    /**
     * Get Mermaid Objects
     *
     * @param html
     */
    WireframeOverlay.getMermaidObjects = function (html) {

        var objects = [];

        // grab each object
        html.once("getobjects").find('h2').each(function (i) {
            var properties;
            var relations = [];
            var icons;
            var doc;
            var type = "normal";
            var relations_count = 0;

            h2 = $(this);
            properties = h2.next();
            $(properties).find('li').each(function (j) {

                if($(this).length > 0) {

                    text = $(this).text();
                    var res = text.split(":");

                    if(res[0] === 'relations' && res[1].length > 0) {
                        relations = res[1].split(",");
                        relations_count = relations.length;
                    }
                    if(res[0] === 'meta' && res[1].length > 0) {
                        icons = res[1].split(",");
                    }

                    if(res[0] === 'doc' && res[1].length > 0) {
                        doc = res[1].trim() + res[2].trim();
                    }

                    if(res[0] === 'type' && res[1].length > 0) {
                        type = res[1].trim();
                    }

                }
            });

            objects.push({
                id: h2.attr('id'),
                label: h2.text(),
                type: type,
                relations: relations,
                icons: icons,
                doc: doc
            });
        });

        return objects;
    };

    /**
     * Add Indentations on markup lists
     *
     * @param html
     * @returns {*}
     */
    WireframeOverlay.addIndentations = function(html) {
        $('li', html).each(function (i) {
            el = $(this);
            if($('ul', el).length > 0) {
                html.find(el).addClass("indent");
            }
        });

        return html;
    };

    /**
     * Generate Mermaid Js Syntax
     *
     * @param objects
     * @returns {string}
     */
    WireframeOverlay.genMermaidSyntax = function (objects, html) {

        // EXAMPLE Syntax:
        //
        // graph TD
        // development["Development"]
        // development-->coderepository
        // coderepository["Code repository"]
        // coderepository-->documentation
        // coderepository-->continousintegration
        // documentation["Documentation"]
        // continousintegration["Continous integration"]
        // continousintegration-->notifications
        // notifications["Notifications"]
        // notifications-->testpassed
        // testpassed{"Test passed?"}
        // testpassed-->|Yes| artifact
        // testpassed-->|No| development
        // artifact["Artifact"]

        var objects_count = objects.length;

        var mermaid_syntax = "graph TD";

        for(x=0; x < objects_count; x++) {
            if(objects[x].type === 'decision') {
                mermaid_syntax = mermaid_syntax + "\n" + objects[x].id + '{"' + objects[x].label + '"}';
            } else {
                mermaid_syntax = mermaid_syntax + "\n" + objects[x].id + '["' + objects[x].label + '"]';
            }
            if(objects[x].hasOwnProperty('relations')) {
                if(objects[x].relations.length > 0 && typeof objects[x].relations[0] != 'undefined') {
                    for(t=0; t < objects[x].relations.length; t++) {
                        var relate_to;
                        var relation_id;
                        var relation_components = objects[x].relations[t].split('|');

                        if(relation_components.length === 2) {
                            relation_id = $( "h2:contains(" + relation_components[1] + ")", html ).attr('id');
                            relate_to = '|' + relation_components[0].trim() + '|' + relation_id;
                        } else {
                            relation_id = $( "h2:contains(" + relation_components[0].trim() + ")", html ).attr('id');
                            relate_to = relation_id;
                        }

                        mermaid_syntax = mermaid_syntax + "\n" + objects[x].id + '-->' + relate_to;
                    }
                }
            }
        }

        return mermaid_syntax;
    };


    /**
     * This function is unused try to figure out how to make it work
     * for inclusion of other md files
     *
     * @param url
     */
    WireframeOverlay.getMarkdown = function (url) {
        $.ajax({
            url: url,
            type: 'get',
            async: false,
            success: function(markdown) {
                return markdown;
            }
        });
    };

})(jQuery, Drupal, drupalSettings);