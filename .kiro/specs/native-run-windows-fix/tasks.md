# Implementation Plan: Native Run Windows Fix

## Overview

Fix the `php artisan native:run` command on Windows by addressing four issues in sequence:
1. TTY crash in the Gradle build step (immediate blocker — the error you're seeing now)
2. xcopy fallback missing root-level files (causes `composer.json` not found)
3. Missing guard before `composer install` (improves diagnostics)
4. Temp dir path length verification (prevents MAX_PATH failures during Composer extraction)

All fixes are applied to local trait overrides in `app/Traits/NativePHP/` — no vendor files are modified.

## Task Dependency Graph

```json
{
  "waves": [
    ["1", "2", "4", "5"],
    ["3"]
  ]
}
```

## Tasks

- [ ] 1. Fix TTY mode crash on Windows in RunsAndroid trait
  - **File**: `app/Traits/NativePHP/RunsAndroid.php`
  - In `runTheAndroidBuild()`, the Windows branch calls `$process->tty()` unconditionally (only guarded by `--no-tty` option check), but `Process::timeout(0)` returns an `Illuminate\Process\PendingProcess` which calls `Symfony\Component\Process\Process::setTty(true)` — this throws `RuntimeException: TTY mode is not supported on Windows platform.`
  - Remove the `->tty()` call entirely from the Windows branch (TTY is a Unix concept; Windows uses direct output instead)
  - The `--no-tty` guard is irrelevant on Windows since TTY is never supported; the Windows branch should never call `->tty()`
  - Keep the `--no-tty` guard only in the non-Windows (Linux/macOS) branch where it is meaningful
  - **Acceptance**: Running `php artisan native:run android` on Windows no longer throws `RuntimeException: TTY mode is not supported on Windows platform.`

- [ ] 2. Replace xcopy fallback with PHP-native recursive copy in PreparesBuild trait
  - **File**: `app/Traits/NativePHP/PreparesBuild.php`
  - In `platformOptimizedCopy()`, when robocopy fails (exit code >= 8), the current fallback uses `xcopy "{$source}\*" "{$destination}\" /E /I /Y /Q` which skips root-level files (known xcopy behavior with `\*` pattern), causing `composer.json` to be missing in the temp dir
  - Replace the xcopy fallback with a PHP-native recursive copy using `RecursiveDirectoryIterator` that respects `$excludedDirs`
  - After the copy completes, verify that `composer.json` exists in `$destination`; if not, log a clear error and throw an exception (do not silently continue)
  - The robocopy primary path (exit code 0–7) must remain unchanged
  - Linux/macOS rsync path must remain unchanged
  - **Acceptance**: When robocopy fails on Windows, all root-level files including `composer.json` are present in the temp dir after fallback copy

- [ ] 3. Add composer.json presence guard before running composer install
  - **Depends on**: Task 2
  - **File**: `app/Traits/NativePHP/PreparesBuild.php`
  - In `prepareLaravelBundle()`, after the `platformOptimizedCopy` task completes, add an explicit check: if `$tempDir/composer.json` does not exist, log the error and call `\Laravel\Prompts\error()` + `exit(1)` with a clear message pointing to the copy step
  - This guard implements requirement 2.2 from the bugfix spec and prevents a confusing "Composer could not find a composer.json file" error from masking the real root cause
  - **Acceptance**: If copy fails to produce `composer.json`, the build stops immediately with a clear diagnostic message instead of a cryptic Composer error

- [ ] 4. Verify and document temp dir path length to avoid Windows MAX_PATH
  - **File**: `app/Traits/NativePHP/PreparesBuild.php`
  - The current temp dir is `NATIVEPHP_BUILD_TEMP_DIR . '\\'` (e.g. `D:\Temp\`) — verify the local override does NOT append a timestamp suffix that would lengthen the path
  - Add a log line showing the resolved temp dir path and its character length so it is visible in the build log
  - If the resolved path exceeds 50 characters for the base, shorten it to `sys_get_temp_dir() . '\\np-' . time()` to keep total paths under 260 chars for typical package names
  - **Acceptance**: The resolved temp dir path is logged with its length; Composer package extraction does not fail due to MAX_PATH

- [ ] 5. Verify trait conflict resolution covers platformOptimizedCopy override
  - **File**: `app/Console/Commands/NativeRunCommand.php`
  - Confirm that `LocalPreparesBuild::platformOptimizedCopy insteadof PlatformFileOperations` is present and correctly resolves the conflict so the fixed PHP-native fallback (Task 2) is actually called at runtime
  - Add a code comment explaining why this override is necessary (xcopy fallback bug)
  - No functional change needed if already wired correctly — this is a verification + documentation task
  - **Acceptance**: `NativeRunCommand` uses `App\Traits\NativePHP\PreparesBuild::platformOptimizedCopy` (not `PlatformFileOperations::platformOptimizedCopy`) at runtime

## Notes

- All fixes go into `app/Traits/NativePHP/` — never modify files under `vendor/`
- Task 1 is the immediate blocker (the TTY error you see in the terminal). Fix it first to unblock the Gradle build step.
- Tasks 2–3 address the copy pipeline bugs that cause `composer.json` to be missing. These only manifest when robocopy fails (exit code >= 8), which can happen due to long paths, antivirus, or permission issues.
- Task 4 is a verification task — the current `.env` value `NATIVEPHP_BUILD_TEMP_DIR=D:\Temp\NativeBuild` is already short enough, but the log line will confirm it at runtime.
- Task 5 is a documentation/verification task — the `insteadof` wiring is already in place per the current `NativeRunCommand.php`.
