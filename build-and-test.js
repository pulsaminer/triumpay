// build-and-test.js
import { build } from "esbuild";
import fs from "fs";
import vm from "vm";
import { TextEncoder, TextDecoder } from "util";
import { Buffer } from "buffer";
import crypto from "crypto";
import chokidar from "chokidar";
import express from "express";
import { WebSocketServer } from "ws";

// Helpers for browser-like environment in tests
const atob = (b64) => Buffer.from(b64, "base64").toString("binary");
const btoa = (str) => Buffer.from(str, "binary").toString("base64");

// -------- BUILD CONFIG --------
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

// -------- BUILD FUNCTION --------
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
      treeShaking: false,
    });
    console.log(`✅ Built ${cfg.outfile}`);
  }
}

// -------- TEST FUNCTION --------
function loadBundle(file, sandbox) {
  const code = fs.readFileSync(file, "utf8");
  vm.runInNewContext(code, sandbox, { filename: file });
}

function runTests() {
  const sandbox = {
    console,
    setTimeout,
    clearTimeout,
    URL,
    URLSearchParams,
    TextEncoder,
    TextDecoder,
    Buffer,
    atob,
    btoa,
    crypto: crypto.webcrypto,
  };
  sandbox.window = sandbox;
  sandbox.self = sandbox;

  loadBundle("./assets/js/libs/web3.iife.js", sandbox);
  loadBundle("./assets/js/libs/spl-token.iife.js", sandbox);

  const solanaWeb3 = sandbox.window.solanaWeb3;
  const splToken = sandbox.window.splToken;

  console.log("=== web3 exports ===");
  console.log("PublicKey:", typeof solanaWeb3?.PublicKey);
  console.log("Transaction:", typeof solanaWeb3?.Transaction);

  console.log("\n=== spl-token exports ===");
  console.log("getAssociatedTokenAddress:", typeof splToken?.getAssociatedTokenAddress);
  console.log("createTransferInstruction:", typeof splToken?.createTransferInstruction);
  console.log("ASSOCIATED_TOKEN_PROGRAM_ID:", typeof splToken?.ASSOCIATED_TOKEN_PROGRAM_ID);

  // Assertions
  function assertIsFunction(name, v) {
    if (typeof v !== "function") throw new Error(`${name} is not a function (got ${typeof v})`);
  }
  function assertIsObject(name, v) {
    if (typeof v !== "object" || v == null) throw new Error(`${name} is not an object (got ${typeof v})`);
  }

  assertIsFunction("solanaWeb3.PublicKey", solanaWeb3?.PublicKey);
  assertIsFunction("solanaWeb3.Transaction", solanaWeb3?.Transaction);

  assertIsFunction("splToken.getAssociatedTokenAddress", splToken?.getAssociatedTokenAddress);
  assertIsFunction("splToken.createTransferInstruction", splToken?.createTransferInstruction);
  assertIsObject("splToken.ASSOCIATED_TOKEN_PROGRAM_ID", splToken?.ASSOCIATED_TOKEN_PROGRAM_ID);

  console.log("\n✅ All exports look good!");
}

// -------- MAIN BUILD + TEST --------
async function buildAndTest() {
  try {
    await runBuilds();
    runTests();
    broadcastReload(); // tell browsers to reload
  } catch (err) {
    console.error("❌ Build or test failed:", err);
  }
}

// -------- DEV SERVER + LIVE RELOAD --------
const app = express();
const PORT = 3000;

// Serve your project root (adjust if needed)
app.use(express.static("."));

const server = app.listen(PORT, () => {
  console.log(`\n🚀 Dev server running at http://localhost:${PORT}`);
});

// WebSocket server for reload events
const wss = new WebSocketServer({ server });
function broadcastReload() {
  wss.clients.forEach((client) => {
    if (client.readyState === 1) {
      client.send("reload");
    }
  });
}

// Inject reload script into HTML
app.use((req, res, next) => {
  const send = res.send;
  res.send = function (body) {
    if (typeof body === "string" && body.includes("</body>")) {
      body = body.replace(
        "</body>",
        `<script>
          const ws = new WebSocket("ws://" + location.host);
          ws.onmessage = (msg) => { if (msg.data === "reload") location.reload(); };
        </script></body>`
      );
    }
    return send.call(this, body);
  };
  next();
});

// -------- WATCH MODE --------
const watcher = chokidar.watch(
  ["src/spl-entry.js", "src/web3-entry.js", "build-spl.js", "build-web3.js"],
  { persistent: true }
);

console.log("\n👀 Watching for changes in src/ and build configs...");

watcher.on("change", async (path) => {
  console.log(`\n🔄 File changed: ${path}`);
  await buildAndTest();
});

// Initial run
await buildAndTest();
