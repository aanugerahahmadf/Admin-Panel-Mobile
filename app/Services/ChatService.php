<?php

namespace App\Services;

use App\Filament\User\Resources\OrderResource;
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

        $inbox = Inbox::whereJsonContains('user_ids', $userId)
            ->whereJsonContains('user_ids', $admin->id)
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
        // Avoid sending duplicate context cards for the same product in a short time
        $lastMessage = $inbox->messages()->latest()->first();
        if ($lastMessage && isset($lastMessage->meta['id']) && $lastMessage->meta['id'] == $meta['id']) {
            return $lastMessage;
        }

        $message = Message::create([
            'inbox_id' => $inbox->id,
            'user_id' => Auth::id(),
            'message' => __('Saya menanyakan tentang '.($meta['type'] == 'product' ? 'produk' : 'paket').' ini: ').$meta['name'],
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
        $type = $order->package_id ? 'package' : 'product';
        $item = $order->package ?? $order->product;

        $message = Message::create([
            'inbox_id' => $inbox->id,
            'user_id' => $order->user_id,
            'message' => __('Halo Admin, saya baru saja membuat pesanan baru dengan nomor: ').$order->order_number,
            'meta' => [
                'type' => $type,
                'id' => $item->id,
                'name' => $item->name,
                'price' => $order->total_price,
                'image' => $item->image_url,
                'url' => OrderResource::getUrl('index').'?tableFilters[id][value]='.$order->id,
                'is_order' => true,
                'order_number' => $order->order_number,
                'order_status' => $order->status,
            ],
        ]);

        // Dispatch bot reply for new order
        if ($order->user && ! $order->user->hasRole('super_admin')) {
            SendBotReply::dispatch($message->id)->delay(now()->addSeconds(5));
        }

        return $message;
    }
}
