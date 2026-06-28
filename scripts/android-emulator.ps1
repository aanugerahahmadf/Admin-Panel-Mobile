# NativePHP Android — set SDK/AVD on D: then launch emulator or run app.
# Usage:
#   .\scripts\android-emulator.ps1 list
#   .\scripts\android-emulator.ps1 start
#   .\scripts\android-emulator.ps1 run

$ErrorActionPreference = "Stop"

$SdkRoot = if ($env:ANDROID_SDK_ROOT) { $env:ANDROID_SDK_ROOT } else { "D:\Android\Sdk" }
$AvdHome = if ($env:ANDROID_AVD_HOME) { $env:ANDROID_AVD_HOME } else { "D:\Android\avd" }
$AvdName = if ($env:NATIVEPHP_ANDROID_AVD) { $env:NATIVEPHP_ANDROID_AVD } else { "small_phone" }

$env:ANDROID_SDK_ROOT = $SdkRoot
$env:ANDROID_HOME = $SdkRoot
$env:ANDROID_AVD_HOME = $AvdHome
$env:PATH = "$SdkRoot\platform-tools;$SdkRoot\emulator;$env:PATH"

$Emulator = "$SdkRoot\emulator\emulator.exe"

function Show-Avds {
    & $Emulator -list-avds
}

$action = if ($args.Count -gt 0) { $args[0] } else { "start" }

switch ($action) {
    "list" {
        Show-Avds
    }
    "start" {
        Write-Host "Starting AVD: $AvdName (AVD home: $AvdHome)"
        Start-Process -FilePath $Emulator -ArgumentList "-avd", $AvdName -WindowStyle Normal
        Write-Host "Waiting for boot..."
        & "$SdkRoot\platform-tools\adb.exe" wait-for-device
        do {
            Start-Sleep -Seconds 2
            $boot = & "$SdkRoot\platform-tools\adb.exe" shell getprop sys.boot_completed 2>$null
        } while ($boot.Trim() -ne "1")
        Write-Host "Emulator ready."
        & "$SdkRoot\platform-tools\adb.exe" devices
    }
    "run" {
        Set-Location (Split-Path $PSScriptRoot -Parent)
        php artisan native:run android
    }
    default {
        Write-Host "Usage: .\scripts\android-emulator.ps1 [list|start|run]"
    }
}
