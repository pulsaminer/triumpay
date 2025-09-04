// ==============================
// Triumpay Wallet Integration
// ==============================

const RPC_ENDPOINT = (typeof SOLANA_RPC_ENDPOINT !== 'undefined' && SOLANA_RPC_ENDPOINT)
  ? SOLANA_RPC_ENDPOINT
  : 'https://api.mainnet-beta.solana.com';

const TRDX_MINT = '7EV2VjMrdZuJLbdZ39279TbRqkW8zWFwbLTQeg5swSyK';

// Toggle this flag to force manual connect
let forceManualConnect = false;

// ------------------------------
// Phantom helpers
// ------------------------------
function isPhantomInstalled() {
  return typeof window.solana !== 'undefined' && window.solana.isPhantom;
}

async function connectPhantomWallet() {
  if (!isPhantomInstalled()) throw new Error('Phantom Wallet is not installed');
  try {
    const { publicKey } = await window.solana.connect();
    return publicKey.toString();
  } catch (err) {
    throw new Error('Failed to connect to Phantom Wallet: ' + (err?.message || err));
  }
}

async function disconnectPhantomWallet() {
  if (!isPhantomInstalled()) throw new Error('Phantom Wallet is not installed');
  try {
    await window.solana.disconnect();
    const el = document.getElementById('wallet_balance');
    if (el) {
      el.textContent = 'Wallet disconnected';
      el.style.display = 'block';
    }
    console.log(' Wallet disconnected');
    return true;
  } catch (err) {
    console.error('❌ Failed to disconnect Phantom Wallet:', err);
    throw new Error('Failed to disconnect from Phantom Wallet: ' + (err?.message || err));
  }
}

// ------------------------------
// Live TRDX price
// ------------------------------
async function getTRDXPrice() {
  try {
    const url = `https://api.dexscreener.com/latest/dex/tokens/${TRDX_MINT}`;
    const res = await fetch(url);
    const data = await res.json();
    const price = data?.pairs?.[0]?.priceUsd;
    if (price) return parseFloat(price);
  } catch (e) {
    console.error('Failed to fetch TRDX price:', e);
  }
  return 0.0000014;
}

// ------------------------------
// TRDX Balance helper
// ------------------------------
async function getTRDXBalance(ownerAddress) {
  if (!isPhantomInstalled()) throw new Error('Phantom Wallet is not installed');
  if (!window.solana.isConnected) throw new Error('Wallet is not connected');
  if (typeof solanaWeb3 === 'undefined') throw new Error('Solana Web3 not loaded');

  const connection = new solanaWeb3.Connection(RPC_ENDPOINT, 'confirmed');
  const owner = ownerAddress ? new solanaWeb3.PublicKey(ownerAddress) : window.solana.publicKey;
  const tokenMint = new solanaWeb3.PublicKey(TRDX_MINT);

  try {
    const resp = await connection.getParsedTokenAccountsByOwner(owner, { mint: tokenMint });
    if (!resp || !resp.value || resp.value.length === 0) {
      return 0;
    }

    let total = 0;
    for (const acc of resp.value) {
      const info = acc.account.data.parsed.info.tokenAmount;
      const ui = parseFloat(info.uiAmountString || '0');
      if (!isNaN(ui)) total += ui;
    }
    return total;
  } catch (err) {
    console.error('Error getting TRDX balance:', err);
    return 0;
  }
}

// ------------------------------
// Send TRDX transfer
// ------------------------------
async function sendTRDXTransaction(amount, receiverAddressParam) {
  if (!isPhantomInstalled()) throw new Error('Phantom Wallet is not installed');
  if (!window.solana.isConnected) throw new Error('Wallet is not connected. Please connect your Phantom Wallet first.');
  if (typeof solanaWeb3 === 'undefined' || typeof splToken === 'undefined') throw new Error('Solana Web3 or SPL Token library not found');

  const connection = new solanaWeb3.Connection(RPC_ENDPOINT, 'confirmed');
  const tokenMintAddress = new solanaWeb3.PublicKey(TRDX_MINT);
  const receiverPubKey = new solanaWeb3.PublicKey(receiverAddressParam);
  const fromWallet = window.solana.publicKey;

  const fromTokenAccount = await splToken.getAssociatedTokenAddress(tokenMintAddress, fromWallet);
  const toTokenAccount = await splToken.getAssociatedTokenAddress(tokenMintAddress, receiverPubKey);

  const tx = new solanaWeb3.Transaction();

  const toInfo = await connection.getAccountInfo(toTokenAccount);
  if (!toInfo) {
    tx.add(
      splToken.createAssociatedTokenAccountInstruction(
        fromWallet,
        toTokenAccount,
        receiverPubKey,
        tokenMintAddress
      )
    );
  }

  let currentBalance = 0;
  try {
    const tokenAccounts = await connection.getParsedTokenAccountsByOwner(fromWallet, { mint: tokenMintAddress });
    if (tokenAccounts.value.length > 0) {
      const parsed = tokenAccounts.value[0].account.data.parsed;
      currentBalance = parseFloat(parsed.info.tokenAmount.uiAmountString) || 0;
    }
  } catch (_) {}

  if (currentBalance < amount) {
    throw new Error(`Insufficient TRDX balance. You have ${currentBalance}, need ${amount}.`);
  }

  const tokenAmount = BigInt(Math.round(amount * 1_000_000));

  tx.add(
    splToken.createTransferInstruction(
      fromTokenAccount,
      toTokenAccount,
      fromWallet,
      tokenAmount,
      [],
      splToken.TOKEN_PROGRAM_ID
    )
  );

  const { blockhash, lastValidBlockHeight } = await connection.getLatestBlockhash('finalized');
  tx.recentBlockhash = blockhash;
  tx.lastValidBlockHeight = lastValidBlockHeight;
  tx.feePayer = fromWallet;

  try {
    const signed = await window.solana.signTransaction(tx);
    const sig = await connection.sendRawTransaction(signed.serialize());
    await connection.confirmTransaction({ signature: sig, blockhash, lastValidBlockHeight }, 'confirmed');
    console.log('✅ TRDX transaction sent:', sig);
    return sig;
  } catch (err) {
    console.error('❌ Transaction error:', err);
    if (err?.logs) console.error('Simulation logs:', err.logs);
    throw new Error('Failed to send transaction: ' + (err?.message || err));
  }
}

// ------------------------------
// Formatting helpers
// ------------------------------
function formatTRDXAmount(amount) { return parseFloat(amount).toFixed(6) + ' TRDX'; }
function formatUSDAmount(amount) { return '$' + parseFloat(amount).toFixed(2); }
async function convertUSDToTRDX(usdAmount) { return usdAmount / (await getTRDXPrice()); }
async function convertTRDXToUSD(trdxAmount) { return trdxAmount * (await getTRDXPrice()); }

// ------------------------------
// Public API
// ------------------------------
window.TriumpayWallet = {
  isPhantomInstalled,
  connectPhantomWallet,
  disconnectPhantomWallet,
  sendTRDXTransaction,
  getTRDXPrice,
  getTRDXBalance,
  formatTRDXAmount,
  formatUSDAmount,
  convertUSDToTRDX,
  convertTRDXToUSD
};

// ------------------------------
// Boot with auto/manual connect toggle
// ------------------------------
document.addEventListener('DOMContentLoaded', async () => {
  console.log('Triumpay Wallet integration loaded');
  if (isPhantomInstalled()) {
    console.log('Phantom Wallet is installed');
    try {
      if (!forceManualConnect) {
        // Auto-connect if user already trusted the site
        const resp = await window.solana.connect({ onlyIfTrusted: true });
        if (resp?.publicKey) {
          console.log('Auto-connected:', resp.publicKey.toString());
          if (typeof window.showWalletBalance === 'function') {
            await window.showWalletBalance();
          }
        }
      } else {
        console.log('⚠️ forceManualConnect enabled: waiting for user to click connect');
      }
    } catch (e) {
      console.log('User has not yet trusted this site, waiting for manual connect.');
    }

    if (typeof window.showWalletBalance === 'function') {
      try {
        window.solana?.on?.('connect', window.showWalletBalance);
        window.solana?.on?.('accountChanged', window.showWalletBalance);
        window.solana?.on?.('disconnect', () => {
          const el = document.getElementById('wallet_balance');
          if (el) {
            el.textContent = 'Wallet disconnected';
            el.style.display = 'block';
          }
        });
      } catch (e) {}
    }
  } else {
    console.log('Phantom Wallet is not installed');
  }
});
