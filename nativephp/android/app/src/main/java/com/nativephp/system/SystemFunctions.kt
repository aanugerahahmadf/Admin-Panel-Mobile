package com.nativephp.system

import android.content.Context
import android.content.Intent
import android.net.Uri
import android.provider.Settings
import android.util.Log
import com.nativephp.mobile.bridge.BridgeError
import com.nativephp.mobile.bridge.BridgeFunction

/**
 * Functions related to system-level operations
 * Namespace: "System.*"
 */
object SystemFunctions {

    /**
     * Open the app's settings screen in the device settings
     * This allows users to manage permissions they've granted or denied
     * Returns:
     *   - success: boolean - True if successfully opened
     */
    class OpenAppSettings(private val context: Context) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            Log.d("System.OpenAppSettings", "Opening app settings")

            return try {
                val intent = Intent(Settings.ACTION_APPLICATION_DETAILS_SETTINGS).apply {
                    data = Uri.fromParts("package", context.packageName, null)
                    addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
                }
                context.startActivity(intent)

                Log.d("System.OpenAppSettings", "Successfully opened app settings")
                mapOf("success" to true)
            } catch (e: Exception) {
                Log.e("System.OpenAppSettings", "Error opening app settings: ${e.message}", e)
                throw BridgeError.ExecutionFailed("Failed to open app settings: ${e.message}")
            }
        }
    }
}