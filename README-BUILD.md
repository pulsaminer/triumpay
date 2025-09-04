# Solana IIFE Build Instructions

This document explains how to build the real IIFE (Immediately Invoked Function Expression) versions of the Solana libraries for use in the Trium-X project.

## Prerequisites

Make sure you have Node.js installed on your system.

## Setup

1. Navigate to the trium-X directory:
   ```
   cd trium-X
   ```

2. Install the required dependencies:
   ```
   npm install
   ```

## Downloading Pre-built IIFE Files

Instead of building the IIFE files yourself, you can download pre-built versions:

```
npm run download-iife
```

This will download the latest IIFE builds from unpkg CDN and place them in the correct location.

## Building the IIFE Files

### Option 1: Build Individual Libraries

Build the Solana Web3.js library:
```
npm run build-web3
```

Build the SPL Token library:
```
npm run build-spl
```

### Option 2: Build Both Libraries

Build both libraries at once:
```
npm run build-all
```

## Output

The build process will generate the following files:
- `assets/js/libs/web3.iife.js` - The Solana Web3.js library in IIFE format
- `assets/js/libs/spl-token.iife.js` - The SPL Token library in IIFE format

These files can be used directly in your HTML without any CSP issues:

```html
<script src="/assets/js/libs/web3.iife.js"></script>
<script src="/assets/js/libs/spl-token.iife.js"></script>
<script>
  // The libraries are now available as global objects:
  // window.solanaWeb3 and window.splToken
</script>
```

## Usage in Trium-X

The footer already includes the necessary script tags:
```html
<script src="/assets/js/libs/web3.iife.js"></script>
<script src="/assets/js/libs/spl-token.iife.js"></script>
```

The staking page and wallet.js files use the libraries like this:
```javascript
const { Connection, PublicKey, Transaction } = solanaWeb3;
const { getAssociatedTokenAddress, createTransferInstruction } = splToken;
```

## Troubleshooting

If you encounter any issues during the build process:

1. Make sure all dependencies are installed:
   ```
   npm install
   ```

2. Check that you're using a compatible version of Node.js (v16 or higher recommended)

3. If there are TypeScript errors, you may need to install additional dev dependencies:
   ```
   npm install --save-dev typescript @types/node
   ```

4. If the build scripts don't work, you can also try building manually with esbuild:
   ```
   npx esbuild node_modules/@solana/web3.js/lib/index.browser.esm.js --bundle --outfile=assets/js/libs/web3.iife.js --format=iife --global-name=solanaWeb3 --minify
   npx esbuild node_modules/@solana/spl-token/lib/index.browser.esm.js --bundle --outfile=assets/js/libs/spl-token.iife.js --format=iife --global-name=splToken --minify