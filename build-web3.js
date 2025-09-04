import esbuild from "esbuild";

await esbuild.build({
  entryPoints: ["src/web3-entry.js"],
  bundle: true,
  outfile: "assets/js/libs/web3.iife.js",
  format: "iife",
  globalName: "solanaWeb3",
  minify: true,
  target: ["es2020"],
  define: {
    "process.env.NODE_ENV": '"production"',
  },
  treeShaking: false,
});
