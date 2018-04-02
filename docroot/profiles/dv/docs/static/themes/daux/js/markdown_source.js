$(function () {

    // initialize mermaid
    mermaid.initialize({startOnLoad: true});

    var html = $('body');

    // get mermaid objects from html
    var objects = getMermaidObjects(html);
    console.log(objects);

    // get mermaid syntax
    var mermaid_syntax = genMermaidSyntax(objects, html);
    console.log(mermaid_syntax);

    // append html and mermaid graph to document
    var append_element = $('.s-content');
    var toc = $('<div class="mermaid" id="toc">' + mermaid_syntax + '</div>');
    append_element.append(toc);

    // on click label
    toc.on("click", "g.label", function () {
        var markupid = $(this).parent('g').attr('id');
        $('html, body').animate({
            scrollTop: $('h2#' + markupid).offset().top - 100
        }, 100);

        setInactive();
        setActive(markupid);
    });

    // on click h2
    append_element.on("click", "h2", function () {
        var h2id = $(this).attr('id');
        $('html, body').animate({
            scrollTop: $('#toc').offset().top - 100
        }, 100);

        setInactive();
        setActive(h2id);
    });

});

/**
 * Set element Inactive.
 */
setInactive = function () {
    $('#toc g div').removeClass('active');
    $('.s-content h2').removeClass('active');
};

/**
 * Set element active.
 *
 * @param elementId
 */
setActive = function (elementId) {
    var active_g = $('#toc g#' + elementId + ' div');
    var active_h2 = $('.s-content h2#' + elementId);

    // mark active element for color change
    active_h2.addClass('active');
    active_g.addClass('active');
};


/**
 * Get Mermaid Objects
 *
 * @param html
 */
getMermaidObjects = function (html) {
    var objects = [];

    // grab each object
    html.find('h2').each(function (i) {
        var properties;
        var relations = [];
        var icons;
        var doc;
        var type = "normal";
        var relations_count = 0;

        h2 = $(this);
        properties = h2.next();
        $(properties).find('li').each(function (j) {

            if ($(this).length > 0) {

                text = $(this).text();
                var res = text.split(":");

                if (res[0] === 'relations' && res[1].length > 0) {
                    relations = res[1].split(",");
                    relations_count = relations.length;
                }
                if (res[0] === 'meta' && res[1].length > 0) {
                    icons = res[1].split(",");
                }

                if (res[0] === 'doc' && res[1].length > 0) {
                    doc = res[1].trim(); // + res[2].trim();
                }

                if (res[0] === 'type' && res[1].length > 0) {
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
 * Generate Mermaid Js Syntax
 *
 * @param objects
 * @returns {string}
 */
genMermaidSyntax = function (objects, html) {
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

    for (x = 0; x < objects_count; x++) {
        if (objects[x].type === 'decision') {
            mermaid_syntax = mermaid_syntax + "\n" + objects[x].id + '{"' + objects[x].label + '"}';
        } else {
            mermaid_syntax = mermaid_syntax + "\n" + objects[x].id + '["' + objects[x].label + '"]';
        }
        if (objects[x].hasOwnProperty('relations')) {
            if (objects[x].relations.length > 0 && typeof objects[x].relations[0] != 'undefined') {
                for (t = 0; t < objects[x].relations.length; t++) {
                    var relate_to;
                    var relation_id;
                    var relation_components = objects[x].relations[t].split('|');

                    if (relation_components.length === 2) {
                        relation_id = $("h2:contains(" + relation_components[1] + ")", html).attr('id');
                        relate_to = '|' + relation_components[0].trim() + '|' + relation_id;
                    } else {
                        relation_id = $("h2:contains(" + relation_components[0].trim() + ")", html).attr('id');
                        relate_to = relation_id;
                    }

                    mermaid_syntax = mermaid_syntax + "\n" + objects[x].id + '-->' + relate_to;
                }
            }
        }
    }

    return mermaid_syntax;
};

