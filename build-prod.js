// build-prod.js
import { build } from "esbuild";

const builds = [
  {
    entryPoints: ["src/web3-entry.js"],
    outfile: "assets/js/libs/web3.iife.js",
    globalName: "solanaWeb3",
  },
  {
    entryPoints: ["src/spl-entry.js"],
    outfile: "assets/js/libs/spl-token.iife.js",
    globalName: "splToken",
  },
];

async function runBuilds() {
  for (const cfg of builds) {
    await build({
      ...cfg,
      bundle: true,
      format: "iife",
      minify: true,
      target: ["es2020"],
      define: {
        "process.env.NODE_ENV": '"production"',
      },
      treeShaking: true,
    });
    console.log(`✅ Built ${cfg.outfile}`);
  }
}

runBuilds().catch((err) => {
  console.error("❌ Build failed:", err);
  process.exit(1);
});
