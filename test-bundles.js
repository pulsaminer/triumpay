// test-bundles.js
import fs from "fs";
import vm from "vm";
import { TextEncoder, TextDecoder } from "util";
import { Buffer } from "buffer";
import crypto from "crypto";

// Small helpers to mimic browser APIs often used by web3 libs
const atob = (b64) => Buffer.from(b64, "base64").toString("binary");
const btoa = (str) => Buffer.from(str, "binary").toString("base64");

// Create a browser-like global for the VM
const sandbox = {
  console,
  // timers & URL APIs (usually not required for just loading)
  setTimeout,
  clearTimeout,
  URL,
  URLSearchParams,

  // Browser-like globals that the bundles may touch
  TextEncoder,
  TextDecoder,
  Buffer,                 // 👈 fix the "Buffer is not defined" error
  atob,
  btoa,
  crypto: crypto.webcrypto,

  // Make `window` and `self` point to the global (browser-style)
  window: undefined,      // filled right after we create the object
  self: undefined,
};
// Point window/self back to the global object (the sandbox itself)
sandbox.window = sandbox;
sandbox.self = sandbox;

// Utility to load an IIFE bundle into the sandbox
function loadBundle(file) {
  const code = fs.readFileSync(file, "utf8");
  vm.runInNewContext(code, sandbox, { filename: file });
}

// Load your built bundles (adjust paths if needed)
loadBundle("assets/js/libs/web3.iife.js");
loadBundle("assets/js/libs/spl-token.iife.js");

// Grab the globals your builds expose
const solanaWeb3 = sandbox.window.solanaWeb3;
const splToken    = sandbox.window.splToken;

// ---- Smoke tests (print types) ----
console.log("=== web3 exports ===");
console.log("PublicKey:", typeof solanaWeb3?.PublicKey);
console.log("Transaction:", typeof solanaWeb3?.Transaction);

console.log("\n=== spl-token exports ===");
console.log("getAssociatedTokenAddress:", typeof splToken?.getAssociatedTokenAddress);
console.log("createTransferInstruction:", typeof splToken?.createTransferInstruction);
console.log("ASSOCIATED_TOKEN_PROGRAM_ID:", typeof splToken?.ASSOCIATED_TOKEN_PROGRAM_ID);

// ---- Hard assertions (exit non-zero if something is missing) ----
function assertIsFunction(name, v) {
  if (typeof v !== "function") {
    throw new Error(`${name} is not a function (got ${typeof v})`);
  }
}
function assertIsObject(name, v) {
  if (typeof v !== "object" || v == null) {
    throw new Error(`${name} is not an object (got ${typeof v})`);
  }
}

assertIsFunction("solanaWeb3.PublicKey", solanaWeb3?.PublicKey);
assertIsFunction("solanaWeb3.Transaction", solanaWeb3?.Transaction);

assertIsFunction("splToken.getAssociatedTokenAddress", splToken?.getAssociatedTokenAddress);
assertIsFunction("splToken.createTransferInstruction", splToken?.createTransferInstruction);
assertIsObject("splToken.ASSOCIATED_TOKEN_PROGRAM_ID", splToken?.ASSOCIATED_TOKEN_PROGRAM_ID);

console.log("\nAll required exports look good ✅");
