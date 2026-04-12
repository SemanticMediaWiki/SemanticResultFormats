const fs = require('fs');
const path = require('path');
const rootDir = path.resolve(__dirname, '../../../../');

function getD3Version() {
  const pkgPath = path.join(rootDir, '/node_modules/d3/package.json');
  if (!fs.existsSync(pkgPath)) {
    console.error("❌ D3 is not installed.");
    process.exit(1);
  }
  const pkg = JSON.parse(fs.readFileSync(pkgPath, 'utf8'));
  const major = parseInt(pkg.version.split('.')[0], 10);
  return { version: pkg.version, major };
}

function addGlobalD3Assignment(content) {
  const regex = /this\.d3\s*=\s*([a-zA-Z0-9_$]+)\s*;?\s*\}/;

  const match = content.match(regex);
  if (match) {
    const d3VarName = match[1];
    const insert = `this.d3=${d3VarName};if(typeof window !== "undefined")window.d3=${d3VarName};`;
    return content.replace(regex, `${insert}}`);
  }

  return content;
}

function copyMinifiedD3WithNomin() {
  const { version, major } = getD3Version();

  let srcRel;
  if (major >= 5) {
    srcRel = 'node_modules/d3/dist/d3.min.js';
  } else if (major === 4) {
    srcRel = 'node_modules/d3/build/d3.min.js';
  } else {
    srcRel = 'node_modules/d3/d3.min.js';
  }

  const src = path.resolve(rootDir, srcRel);
  const destDir = path.resolve(rootDir, 'resources/jquery/d3');
  const dest = path.join(destDir, 'd3.min.js');

  if (!fs.existsSync(src)) {
    console.error(`❌ Could not find d3.min.js at expected location: ${srcRel}`);
    process.exit(1);
  }

  const content = fs.readFileSync(src, 'utf8');

  // add /*@nomin*/ at the beginning of the file and prepend global d3 assignment if needed
  const newContent = `/*@nomin*/\n` + addGlobalD3Assignment(content);
  fs.mkdirSync(destDir, { recursive: true });
  fs.writeFileSync(dest, newContent, 'utf8');

  console.log(`✅ Prepended /*@nomin*/ to d3.min.js`);
  console.log(`✅ Minified file d3.min.js successfully created for D3 version ${version}`);
}

copyMinifiedD3WithNomin();
