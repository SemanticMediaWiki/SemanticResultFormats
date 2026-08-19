# d3chart

## Updating the `d3` Library in `SemanticResultFormats`

The version is managed via `npm` and bundled into the extension using a script.

### How It Works

`d3` is a regular `devDependencies`/`dependencies` entry in the extension's root
`package.json`. After `npm install` runs, the `postinstall` script
(`resources/jquery/d3/scripts/copy-and-minify-d3.js`) runs automatically and:

1. **Locates the installed `d3` bundle**

   Depending on the installed major version, it picks the correct pre-built
   file from `node_modules/d3`:

   | d3 major version | Source file                        |
   |-------------------|-------------------------------------|
   | >= 5              | `node_modules/d3/dist/d3.min.js`   |
   | 4                 | `node_modules/d3/build/d3.min.js`  |
   | <= 3              | `node_modules/d3/d3.min.js`        |

2. **Copies the result**

   The file is copied to:
   ```
   resources/jquery/d3/d3.min.js
   ```
   prefixed with `/*@nomin*/` so MediaWiki's ResourceLoader does not attempt
   to re-minify an already-minified file.

   No further patching is needed: since `d3` v4, its UMD wrapper assigns
   itself to `globalThis.d3` (and therefore `window.d3` in a browser) when
   loaded outside a CommonJS/AMD environment, which is what the per-chart
   modules in this extension (`ext.srf.d3.chart.*.js`) rely on.

### To Update `d3`

1. Open `package.json` and change the `d3` version:

   ```json
   "dependencies": {
       "d3": "7.9.0"
   }
   ```

2. Run:

   ```bash
   npm install
   ```

   This downloads the new `d3` version and re-runs the `postinstall` script,
   which regenerates `resources/jquery/d3/d3.min.js`.

3. Verify the result:

   * The `postinstall` script must finish without errors.
   * `resources/jquery/d3/d3.min.js` should start with `/*@nomin*/`.
   * Manually check the charts (`ext.srf.d3.chart.bubble.js`,
     `ext.srf.d3.chart.treemap.js`) still render, since a major `d3` version
     bump can include breaking API changes independent of the bundling
     mechanism described here.

4. If crossing a major version boundary (e.g. 3 → 4, 4 → 5), double check
   that the source path table above still matches the layout of the new
   `d3` npm package — it has changed between major releases in the past. If
   downgrading below `d3` v4 (unlikely), note that its UMD wrapper did not
   yet assign itself to a global automatically — in that case a manual
   global assignment would need to be reintroduced.
