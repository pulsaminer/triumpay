import esbuild from "esbuild";

await esbuild.build({
  entryPoints: ["src/spl-entry.js"],
  bundle: true,
  outfile: "assets/js/libs/spl-token.iife.js",
  format: "iife",
  globalName: "splToken",
  minify: true,
  target: ["es2020"],
  define: {
    "process.env.NODE_ENV": '"production"',
  },
  treeShaking: false,   // ✅ keep all exports, no dead-code removal
});
