import { Buffer } from "buffer";
import * as spl from "@solana/spl-token";

// ✅ Force Buffer global
if (typeof window !== "undefined") {
  window.Buffer = Buffer;
}

// ✅ Explicitly copy functions/constants you want
const {
  getAssociatedTokenAddress,
  createTransferInstruction,
  createAssociatedTokenAccountInstruction,
  createInitializeAccountInstruction,
  createCloseAccountInstruction,
  createApproveInstruction,
  createRevokeInstruction,
  createInitializeMintInstruction,
  createMintToInstruction,
  createBurnInstruction,
  createSetAuthorityInstruction,
  ASSOCIATED_TOKEN_PROGRAM_ID,
  TOKEN_PROGRAM_ID
} = spl;

// ✅ Attach to window
if (typeof window !== "undefined") {
  window.splToken = {
    getAssociatedTokenAddress,
    createTransferInstruction,
    createAssociatedTokenAccountInstruction,
    createInitializeAccountInstruction,
    createCloseAccountInstruction,
    createApproveInstruction,
    createRevokeInstruction,
    createInitializeMintInstruction,
    createMintToInstruction,
    createBurnInstruction,
    createSetAuthorityInstruction,
    ASSOCIATED_TOKEN_PROGRAM_ID,
    TOKEN_PROGRAM_ID
  };
}

// ✅ Export explicitly for Node/Tests
export {
  getAssociatedTokenAddress,
  createTransferInstruction,
  createAssociatedTokenAccountInstruction,
  createInitializeAccountInstruction,
  createCloseAccountInstruction,
  createApproveInstruction,
  createRevokeInstruction,
  createInitializeMintInstruction,
  createMintToInstruction,
  createBurnInstruction,
  createSetAuthorityInstruction,
  ASSOCIATED_TOKEN_PROGRAM_ID,
  TOKEN_PROGRAM_ID
};
