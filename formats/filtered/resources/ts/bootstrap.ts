import { Filtered } from "./Filtered/Filtered";

declare const mw: any;

const CONFIG_VAR_PREFIX = "srfFilteredConfig-";

// Configs seen so far. SMW replaces mw.config entries when a deferred query is
// loaded, so they are accumulated here instead of being re-read from scratch.
const knownConfigs: Record<string, any> = {};

function collectConfigs(): Record<string, any> {
    // legacy: aggregated config of pages cached before the per-instance vars
    Object.assign(knownConfigs, mw.config.get("srfFilteredConfig") || {});

    Object.keys(mw.config.values).forEach(key => {
        if (key.indexOf(CONFIG_VAR_PREFIX) === 0) {
            knownConfigs[key.slice(CONFIG_VAR_PREFIX.length)] = mw.config.get(key);
        }
    });

    return knownConfigs;
}

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

initItems(collectConfigs());

mw.hook("smw.deferred.query").add((container: JQuery) => {
    const cfg = collectConfigs();

    container.find(".filtered-spinner").hide();
    container.find(".filtered-views").show();
    container.find(".filtered-filters").show();

    initItems(cfg, container);
});
