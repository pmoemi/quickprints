import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import sharp from 'sharp';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const svg = fs.readFileSync(path.join(__dirname, 'launcher-icon.svg'));
const outDir = path.join(__dirname, 'icons');

for (const size of [192, 512, 1024]) {
  const out = path.join(outDir, `icon-${size}.png`);
  await sharp(svg, { density: Math.max(192, size / 2) })
    .resize(size, size)
    .png({ compressionLevel: 9, palette: true })
    .toFile(out);
  console.log(`Wrote ${out}`);
}
