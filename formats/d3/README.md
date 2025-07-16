# d3chart 

## Updating `d3chart` Library in `SemanticResultFormats`

The version is managed via `npm` and bundled into the extension using a script.

### How It Works

After running `npm install`, the following happens automatically:

1. **Copying `d3.min.js`**  

   A custom script (`copy-and-minify-d3.js`) copies the `d3.min.js` file from:
   ```
   node_modules/d3
   ```

   to:

   ```
   resources/jquery/d3/d3.min.js
   ```

### To Update `d3chart`

1. Open `package.json` and change the version under `"d3"`

    ```
    "dependencies": {
        "d3": "3.0.4"
    }
    ```
2. Run:

   ```bash
   npm install
   ```

#### This will:

* Download the new version of D3
* Automatically copy the updated `d3.min.js` to the expected location via the script