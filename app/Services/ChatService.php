<?php

namespace App\Services;

use App\Filament\User\Resources\PackageResource;
use App\Filament\User\Resources\ProductResource;
use App\Jobs\SendBotReply;
use App\Models\Inbox;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatService
{
    /**
     * Get or create an inbox between a user and the first super admin.
     */
    public static function getOrCreateInboxWithAdmin(int $userId): Inbox
    {
        $admin = User::whereHas('roles', function ($q) {
            $q->where('name', 'super_admin');
        })->first();

        if (! $admin) {
            throw new \Exception('Super Admin not found.');
        }

        $inbox = Inbox::query()
            ->whereJsonContains('user_ids', $userId, 'and', false)
            ->whereJsonContains('user_ids', $admin->id, 'and', false)
            ->first();

        if (! $inbox) {
            $inbox = Inbox::create([
                'user_ids' => [$userId, $admin->id],
            ]);
        }

        return $inbox;
    }

    /**
     * Send a context message (product/package card) to an inbox.
     */
    public static function sendContextMessage(Inbox $inbox, array $meta): Message
    {
        // Avoid sending duplicate context cards for the same item in a short time
        // Only skip if the last message is also a context card (not an order card) for the same item
        $lastMessage = $inbox->messages()->latest('id')->first();
        if (
            $lastMessage
            && isset($lastMessage->meta['id'])
            && $lastMessage->meta['id'] == $meta['id']
            && empty($lastMessage->meta['is_order'])
        ) {
            return $lastMessage;
        }

        $message = Message::create([
            'inbox_id' => $inbox->id,
            'user_id' => Auth::id(),
            'message' => __('Saya menanyakan tentang :itemType ini: :name', [
                'itemType' => __($meta['type'] == 'product' ? 'Produk' : 'Paket'),
                'name' => $meta['name'] ?? '',
            ]),
            'meta' => $meta,
        ]);

        // Dispatch bot reply if user is not admin
        if (Auth::user() && ! Auth::user()->hasRole('super_admin')) {
            SendBotReply::dispatch($message->id)->delay(now()->addSeconds(5));
        }

        return $message;
    }

    /**
     * Send an order confirmation message (order card) to an inbox.
     */
    public static function sendOrderMessage(Inbox $inbox, Order $order): Message
    {
        $admin = User::whereHas('roles', function ($q) {
            $q->where('name', 'super_admin');
        })->first();

        $type = $order->package_id ? 'package' : 'product';
        $item = $order->package ?? $order->product;

        $message = Message::create([
            'inbox_id' => $inbox->id,
            'user_id' => $admin ? $admin->id : $order->user_id, // Kirim atas nama Admin
            'message' => __('Halo Kak :userName, pesanan baru Anda telah kami terima dengan nomor: :orderNumber. Silakan lakukan pembayaran agar pesanan segera diproses.', [
                'userName' => $order->user->name,
                'orderNumber' => $order->order_number,
            ]),
            'meta' => [
                'type' => $type,
                'id' => $item->id,
                'name' => $item->name,
                'price' => $order->total_price,
                'image' => $item->image_url,
                'url' => $order->package_id ? PackageResource::getUrl() : ProductResource::getUrl(),
                'is_order' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'order_status' => $order->status,
                'payment_status' => $order->payment_status->getLabel(),
            ],
        ]);

        return $message;
    }
}
