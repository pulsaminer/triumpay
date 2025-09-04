@echo off
echo Downloading updated SPL Token IIFE library...
powershell -Command "Invoke-WebRequest -Uri 'https://unpkg.com/@solana/spl-token@0.3.8/lib/index.iife.min.js' -OutFile 'assets/js/libs/spl-token.iife.js'"
echo Download complete!
pause