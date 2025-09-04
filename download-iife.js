// Script to download pre-built IIFE files for Solana libraries
// This script can be run with Node.js to fetch the IIFE builds directly

import fs from 'fs';
import https from 'https';
import path from 'path';
import { fileURLToPath } from 'url';

// Get __dirname equivalent in ES modules
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// URLs for the IIFE builds
const IIFE_URLS = {
  web3: 'https://unpkg.com/@solana/web3.js@1.98.4/lib/index.iife.min.js',
  splToken: 'https://unpkg.com/@solana/spl-token@0.3.8/lib/index.iife.min.js'
};

// Output directory
const OUTPUT_DIR = path.join(__dirname, 'assets', 'js', 'libs');

// Function to download a file
function downloadFile(url, outputPath) {
  return new Promise((resolve, reject) => {
    console.log(`Downloading ${url}...`);
    
    const file = fs.createWriteStream(outputPath);
    
    https.get(url, (response) => {
      if (response.statusCode !== 200) {
        reject(new Error(`Failed to download ${url}: ${response.statusCode} ${response.statusMessage}`));
        return;
      }
      
      response.pipe(file);
      
      file.on('finish', () => {
        file.close();
        console.log(`Downloaded ${outputPath}`);
        resolve();
      });
      
      file.on('error', (err) => {
        fs.unlink(outputPath, () => {}); // Delete the file async
        reject(err);
      });
    }).on('error', (err) => {
      reject(err);
    });
  });
}

// Main function
async function downloadIIFEFiles() {
  console.log('Downloading Solana IIFE files...');
  
  try {
    // Ensure output directory exists
    if (!fs.existsSync(OUTPUT_DIR)) {
      fs.mkdirSync(OUTPUT_DIR, { recursive: true });
    }
    
    // Download web3.iife.js
    await downloadFile(IIFE_URLS.web3, path.join(OUTPUT_DIR, 'web3.iife.js'));
    
    // Download spl-token.iife.js
    await downloadFile(IIFE_URLS.splToken, path.join(OUTPUT_DIR, 'spl-token.iife.js'));
    
    console.log('All IIFE files downloaded successfully!');
    console.log('Files are now available in:', OUTPUT_DIR);
  } catch (error) {
    console.error('Error downloading IIFE files:', error.message);
    process.exit(1);
  }
}

// Run the script
downloadIIFEFiles();