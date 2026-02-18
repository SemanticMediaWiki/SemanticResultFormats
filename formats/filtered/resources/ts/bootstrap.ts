import { Filtered } from "./Filtered/Filtered";

declare const mw: any;

let config: Record<string, any> = mw.config.get("srfFilteredConfig") || {};

function initItems(cfg: Record<string, any>, root?: JQuery) {
    Object.keys(cfg).forEach(id => {
        const selector = root ? root.find("#" + id) : $("#" + id);
        const el = selector.first();

        if (!el.length) {
        	return;
        }

        if (el.data("filtered-init")) return;
        el.data("filtered-init", true);

        const f = new Filtered(el, cfg[id]);
        f.run();
    });
}

initItems(config);

mw.hook("smw.deferred.query").add((container: JQuery) => {
    const cfg = mw.config.get("srfFilteredConfig") || {};

    container.find(".filtered-spinner").hide();
    container.find(".filtered-views").show();
    container.find(".filtered-filters").show();

    initItems(cfg, container);
});
