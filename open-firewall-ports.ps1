# ============================================================
# JALANKAN SCRIPT INI SEBAGAI ADMINISTRATOR!
# Klik kanan file ini → "Run with PowerShell" → Klik "Yes" di UAC
# ============================================================

Write-Host "Opening firewall ports for NativePHP Android development..." -ForegroundColor Cyan

# Port 8000 — Laravel/Artisan dev server (DB Proxy, Web App)
netsh advfirewall firewall delete rule name="Laravel Dev Server (Port 8000)" | Out-Null
netsh advfirewall firewall add rule name="Laravel Dev Server (Port 8000)" dir=in action=allow protocol=TCP localport=8000
Write-Host "  [OK] Port 8000 (Laravel) opened." -ForegroundColor Green

# Port 5000 — Python AI Core (CBIR)
netsh advfirewall firewall delete rule name="AI Core Python (Port 5000)" | Out-Null
netsh advfirewall firewall add rule name="AI Core Python (Port 5000)" dir=in action=allow protocol=TCP localport=5000
Write-Host "  [OK] Port 5000 (AI Core) opened." -ForegroundColor Green

# Port 3306 — MySQL (jika perlu akses langsung di masa depan)
netsh advfirewall firewall delete rule name="MySQL Dev (Port 3306)" | Out-Null
netsh advfirewall firewall add rule name="MySQL Dev (Port 3306)" dir=in action=allow protocol=TCP localport=3306
Write-Host "  [OK] Port 3306 (MySQL) opened." -ForegroundColor Green

Write-Host ""
Write-Host "All ports opened! Your physical Android device should now connect." -ForegroundColor Yellow
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
