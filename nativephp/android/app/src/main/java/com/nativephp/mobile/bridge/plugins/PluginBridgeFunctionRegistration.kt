package com.nativephp.mobile.bridge.plugins

import androidx.fragment.app.FragmentActivity
import android.content.Context
import com.nativephp.mobile.bridge.BridgeFunctionRegistry
import com.nativephp.camera.CameraFunctions
import com.nativephp.camera.GalleryFunctions
import com.nativephp.device.DeviceFunctions
import com.nativephp.network.NetworkFunctions
import com.nativephp.system.SystemFunctions
import com.srwiez.plugins.mobilescreen.MobileScreenFunctions

// AUTO-GENERATED FILE - DO NOT EDIT
// Generated from installed NativePHP plugins

fun registerPluginBridgeFunctions(activity: FragmentActivity, context: Context) {
    val registry = BridgeFunctionRegistry.shared



    // Plugin: nativephp/mobile-camera
    registry.register("Camera.GetPhoto", CameraFunctions.GetPhoto(activity))

    // Plugin: nativephp/mobile-camera
    registry.register("Camera.RecordVideo", CameraFunctions.RecordVideo(activity))

    // Plugin: nativephp/mobile-camera
    registry.register("Camera.PickMedia", GalleryFunctions.PickMedia(activity))

    // Plugin: nativephp/mobile-device
    registry.register("Device.Vibrate", DeviceFunctions.Vibrate(activity))

    // Plugin: nativephp/mobile-device
    registry.register("Device.ToggleFlashlight", DeviceFunctions.ToggleFlashlight(activity))

    // Plugin: nativephp/mobile-device
    registry.register("Device.GetId", DeviceFunctions.GetId(activity))

    // Plugin: nativephp/mobile-device
    registry.register("Device.GetInfo", DeviceFunctions.GetInfo(activity))

    // Plugin: nativephp/mobile-device
    registry.register("Device.GetBatteryInfo", DeviceFunctions.GetBatteryInfo(activity))

    // Plugin: nativephp/mobile-network
    registry.register("Network.Status", NetworkFunctions.Status(activity))

    // Plugin: nativephp/mobile-system
    registry.register("System.OpenAppSettings", SystemFunctions.OpenAppSettings(activity))

    // Plugin: srwiez/nativephp-mobile-screen
    registry.register("MobileScreen.KeepAwake", MobileScreenFunctions.KeepAwake(activity))

    // Plugin: srwiez/nativephp-mobile-screen
    registry.register("MobileScreen.IsAwake", MobileScreenFunctions.IsAwake(activity))

    // Plugin: srwiez/nativephp-mobile-screen
    registry.register("MobileScreen.SetBrightness", MobileScreenFunctions.SetBrightness(activity))

    // Plugin: srwiez/nativephp-mobile-screen
    registry.register("MobileScreen.GetBrightness", MobileScreenFunctions.GetBrightness(activity))

    // Plugin: srwiez/nativephp-mobile-screen
    registry.register("MobileScreen.ResetBrightness", MobileScreenFunctions.ResetBrightness(activity))

    // Plugin: srwiez/nativephp-mobile-screen
    registry.register("MobileScreen.StartBrightnessListener", MobileScreenFunctions.StartBrightnessListener(activity))

    // Plugin: srwiez/nativephp-mobile-screen
    registry.register("MobileScreen.StopBrightnessListener", MobileScreenFunctions.StopBrightnessListener(activity))
}

/**
 * Register only bridge functions that require Context (not Activity).
 * Used by WorkManager workers for cold-boot background execution
 * when no Activity is available.
 */
fun registerContextOnlyBridgeFunctions(context: Context) {
    val registry = BridgeFunctionRegistry.shared

    // No context-only bridge functions registered
}