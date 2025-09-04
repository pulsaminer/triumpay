import { Buffer } from "buffer";
import * as web3 from "@solana/web3.js";

// make Buffer available globally
if (typeof window !== "undefined") {
  window.Buffer = Buffer;

  // ✅ force-attach the full web3 module to global
  window.solanaWeb3 = web3;
}

export * from "@solana/web3.js";
