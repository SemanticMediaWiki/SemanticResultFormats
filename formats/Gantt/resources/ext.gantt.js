/*!
 * @file
 * @ingroup SMW
 *
 * @licence GNU GPL v2+
 * @author Sebastian Schmid
 */

( function ($, mw) {

    'use strict';

    mw.loader.using(['mediawiki.api', 'ext.mermaid']).then(function () {

        $(document).ready(function () {

            $('.srf-gantt').each(function () {

                var id = $(this).attr('id') + '-diagram';
                var data = $(this).data('mermaid');

                $(this).find('.mermaid-dots').hide();
                $(this).append('<div id=' + id + '> ' + data.content + ' </div>');
                mermaid.initialize(data.config);
                mermaid.init(undefined, $("#" + id));

                // replace <esc>35</esc> with # Tag
                $(".srf-gantt svg text:contains('<esc>35</esc>')").each(function () {
                    var text = $(this).text().replace('<esc>35</esc>', '#');
                    $(this).text(text);
                });

                // word wrapping in <text>
                var sectionTitleNodes = document.querySelectorAll('text.sectionTitle');
                for (var n in sectionTitleNodes)
                    if (sectionTitleNodes.hasOwnProperty(n))
                        forceTextWrappingOn(sectionTitleNodes[n], data.config.gantt.leftPadding - 37);
            });
        });
    });

    function forceTextWrappingOn(node, width) {
        var svgns = "http://www.w3.org/2000/svg";

        if(node.firstChild != null){
            var chars = node.firstChild.nodeValue.split(' '),
                x = parseInt(node.getAttribute('x'), 10),
                y = parseInt(node.getAttribute('y'), 10),
                index = 0,
                tspan, tspanWidth, textNode;

            node.removeChild(node.firstChild);

            for (var c in chars) {
                if (chars.hasOwnProperty(c)) {
                    tspanWidth = tspan == null ? 0 : tspan.getComputedTextLength();
                    if (tspanWidth > width || tspanWidth === 0) {
                        if(index !== 0){
                            y = y + 10;
                        }
                        tspan = document.createElementNS(svgns, 'tspan');
                        tspan.setAttribute('x', x);
                        tspan.setAttribute('y', y);
                        node.appendChild(tspan);
                        index = 0;
                    }

                    textNode = document.createTextNode(index === 0 ? chars[c] : " " + chars[c]);
                    tspan.appendChild(textNode);
                    index++;
                }
            }
        }
    }

}(jQuery, mediaWiki) );
