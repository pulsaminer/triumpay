# Solana IIFE Libraries

This directory contains the IIFE (Immediately Invoked Function Expression) builds of the Solana libraries.

## Files

- `web3.iife.js` - Solana Web3.js library (built from @solana/web3.js)
- `spl-token.iife.js` - SPL Token library (built from @solana/spl-token)

## Building

To build these files, run the build scripts from the project root:

```
npm run build-all
```

This will generate the real IIFE files that expose:
- `window.solanaWeb3` for the Web3.js library
- `window.splToken` for the SPL Token library

## Usage

The footer of the Trium-X application already includes these scripts:

```html
<script src="/assets/js/libs/web3.iife.js"></script>
<script src="/assets/js/libs/spl-token.iife.js"></script>
```

Once loaded, you can use the libraries like this:

```javascript
const { Connection, PublicKey, Transaction } = solanaWeb3;
const { getAssociatedTokenAddress, createTransferInstruction } = splToken;