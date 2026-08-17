<?php

namespace App\Http\Controllers\Api;

use App\Enums\Messages\MediaCollectionType;
use App\Http\Controllers\Controller;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Jobs\SendBotReply;
use App\Models\Inbox;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function getConversations(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('Tidak terautentikasi'),
            ], 401);
        }

        // Fetch inboxes where the user is a participant
        $inboxes = Inbox::whereJsonContains('user_ids', $user->id, 'and', false)
            ->with(['messages' => function ($query): void {
                $query->latest()->limit(1);
            }])
            ->get(['*']);

        $adminUser = User::whereHas('roles', function ($q) {
            $q->where('name', 'super_admin');
        })->first(['*']);
        $adminId = $adminUser?->id ?? 1;
        $isAdmin = $user->hasRole('super_admin');
        $data = $inboxes->map(function (Inbox $inbox) use ($user, $adminId, $isAdmin) {
            $lastMessage = $inbox->messages->first();
            $userIds = $inbox->user_ids ?? [];
            $otherId = collect($userIds)->first(fn ($id) => (int) $id !== (int) $user->id);
            $otherUser = $otherId ? User::find($otherId, ['*']) : null;

            $title = $isAdmin
                ? ($otherUser ? __('Chat dengan :name', ['name' => $otherUser->full_name ?: $otherUser->username ?: __('Pelanggan')]) : $inbox->title ?? __('Chat Bantuan'))
                : __('Chat dengan Admin');
            $otherPayload = [
                'id' => $otherUser?->id ?? $adminId,
                'name' => $otherUser ? ($otherUser->full_name ?: $otherUser->username ?? __('Pengguna')) : __('Admin'),
                'profile_photo' => $otherUser?->avatar_url ?? null,
            ];

            return [
                'id' => $inbox->id,
                'title' => $title,
                'other_user' => $otherPayload,
                'last_message' => $lastMessage ? [
                    'id' => $lastMessage->id,
                    'message' => $lastMessage->message,
                    'created_at' => $lastMessage->created_at->toIso8601String(),
                    'sender_id' => $lastMessage->user_id,
                ] : null,
                'unread_count' => 0,
                'created_at' => $inbox->created_at->toIso8601String(),
                'updated_at' => $inbox->updated_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'current_user_is_super_admin' => $isAdmin,
        ]);
    }

    public function getMessages($inboxId)
    {
        $user = Auth::user();
        $inbox = Inbox::findOrFail($inboxId, ['*']);
        $userIds = $inbox->user_ids ?? [];
        if (! in_array((int) $user->id, array_map('intval', $userIds))) {
            return response()->json(['success' => false, 'message' => __('Tidak terautentikasi')], 403);
        }

        $adminUser = User::whereHas('roles', function ($q) {
            $q->where('name', 'super_admin');
        })->first(['*']);
        $adminId = $adminUser?->id ?? 1;
        $otherId = collect($userIds)->first(fn ($id) => (int) $id !== (int) $user->id);
        $otherUser = $otherId ? User::find($otherId, ['*']) : null;

        $messages = Message::where('inbox_id', $inbox->id)
            ->with(['sender', 'attachments'])
            ->oldest()
            ->get(['*']);

        $data = $messages->map(function (Message $message) use ($user) {
            $sender = $message->sender;

            return [
                'id' => $message->id,
                'message' => $message->message,
                'sender_id' => $message->user_id,
                'sender_name' => $sender?->full_name ?? $sender?->username ?? __('Tidak dikenal'),
                'is_me' => $message->user_id === $user->id,
                'read_by' => [],
                'attachments' => $message->attachments->map(fn (Media $m) => [
                    'id' => $m->id,
                    'url' => str_replace('/storage/', '/media/', $m->getFullUrl()),
                    'original_url' => str_replace('/storage/', '/media/', $m->getFullUrl()),
                    'name' => $m->name,
                    'size' => $m->size,
                    'mime_type' => $m->mime_type,
                ])->values(),
                'meta' => $message->meta,
                'created_at' => $message->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'other_user' => $otherUser ? [
                'id' => $otherUser->id,
                'name' => $otherUser->full_name ?: $otherUser->username ?? __('Admin'),
                'profile_photo' => $otherUser->avatar_url ?? null,
            ] : [
                'id' => $adminId,
                'name' => __('Admin'),
                'profile_photo' => null,
            ],
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'inbox_id' => 'required',
            'message' => 'required_without_all:attachment,type,order_id|nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,m4v,webm,3gp|max:51200',
            'type' => 'nullable|string',
            'item_id' => 'nullable|integer',
            'item_name' => 'nullable|string',
            'item_price' => 'nullable|numeric',
            'item_image' => 'nullable|string',
            'order_id' => 'nullable|integer',
        ]);

        $user = Auth::user();
        $inbox = Inbox::find($request->inbox_id, ['*']);
        if (! $inbox) {
            return response()->json(['success' => false, 'message' => __('Kotak pesan tidak ditemukan')], 404);
        }
        $userIds = $inbox->user_ids ?? [];
        if (! in_array((int) $user->id, array_map('intval', $userIds))) {
            return response()->json(['success' => false, 'message' => __('Tidak terautentikasi')], 403);
        }

        $meta = null;
        if ($request->filled('order_id')) {
            $order = Order::with(['package.media', 'product.media'])->find((int) $request->input('order_id'));
            if ($order) {
                $firstMedia = $order->package?->media?->first() ?? $order->product?->media?->first();
                $meta = [
                    'is_order' => true,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'name' => $order->package?->name ?? $order->product?->name ?? '',
                    'image' => $firstMedia ? str_replace('/storage/', '/media/', $firstMedia->getFullUrl()) : '',
                ];
            }
        } elseif ($request->filled('type') && $request->filled('item_id')) {
            $meta = [
                'type' => $request->input('type'),
                'id' => (int) $request->input('item_id'),
                'name' => $request->input('item_name', ''),
                'price' => $request->input('item_price'),
                'image' => $request->input('item_image', ''),
            ];
        }

        $message = Message::create([
            'inbox_id' => $request->inbox_id,
            'user_id' => $user->id,
            'message' => $request->input('message', ''),
            'meta' => $meta,
        ]);

        if ($request->hasFile('attachment')) {
            $message->addMedia($request->file('attachment'))
                ->toMediaCollection(MediaCollectionType::FILAMENT_MESSAGES->value);
        }

        if (! $user->hasRole('super_admin')) {
            SendBotReply::dispatch($message->id)->delay(now()->addSeconds(5));
        }

        $message->load('attachments');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $message->id,
                'inbox_id' => $message->inbox_id,
                'user_id' => $message->user_id,
                'message' => $message->message,
                'meta' => $message->meta,
                'created_at' => $message->created_at->toIso8601String(),
                'attachments' => $message->attachments->map(fn (Media $m) => [
                    'id' => $m->id,
                    'url' => str_replace('/storage/', '/media/', $m->getFullUrl()),
                    'original_url' => str_replace('/storage/', '/media/', $m->getFullUrl()),
                    'name' => $m->name,
                    'size' => $m->size,
                    'mime_type' => $m->mime_type,
                ]),
            ],
        ], 201);
    }

    public function startConversation(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $adminUser = User::whereHas('roles', function ($q) {
            $q->where('name', 'super_admin');
        })->first(['*']);
        $adminId = $adminUser ? (int) $adminUser->id : 1;

        $withUserId = $request->input('with_user_id');

        if ($withUserId) {
            $withUserId = (int) $withUserId;
            if ($withUserId === (int) $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('Tidak bisa chat dengan diri sendiri.'),
                ], 400);
            }
            $inbox = Inbox::whereJsonContains('user_ids', (int) $user->id, 'and', false)
                ->whereJsonContains('user_ids', $withUserId, 'and', false)
                ->first(['*']);
            if (! $inbox) {
                $inbox = Inbox::create([
                    'user_ids' => [(int) $user->id, $withUserId],
                    'title' => __('Chat Bantuan'),
                ]);
            }
        } else {
            $targetId = $adminId;

            if ((int) $user->id === $targetId) {
                $other = User::whereHas('roles', function ($q) {
                    $q->where('name', 'super_admin');
                })->where('id', '!=', $user->id)->first(['id']);
                if ($other) {
                    $targetId = (int) $other->id;
                } else {
                    $anyUser = User::where('id', '!=', $user->id)->first(['id']);
                    if ($anyUser) {
                        $targetId = (int) $anyUser->id;
                    }
                }
            }

            if ((int) $user->id === $targetId) {
                return response()->json([
                    'success' => false,
                    'message' => __('Tidak ada pengguna lain untuk memulai chat.'),
                ], 400);
            }

            $inbox = Inbox::whereJsonContains('user_ids', (int) $user->id, 'and', false)
                ->whereJsonContains('user_ids', $targetId, 'and', false)
                ->first(['*']);
            if (! $inbox) {
                $inbox = Inbox::create([
                    'user_ids' => [(int) $user->id, $targetId],
                    'title' => __('Chat Bantuan'),
                ]);
            }
        }

        // Kirim context message jika ada item/order info dari request
        $type = $request->input('type');
        $itemId = $request->input('item_id');
        $orderId = $request->input('order_id');

        if ($orderId) {
            $order = Order::find((int) $orderId);
            if ($order) {
                ChatService::sendOrderMessage($inbox, $order);
            }
        } elseif ($type && $itemId) {
            ChatService::sendContextMessage($inbox, [
                'type' => $type,
                'id' => (int) $itemId,
                'name' => $request->input('item_name', ''),
                'price' => $request->input('item_price'),
                'image' => $request->input('item_image', ''),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $inbox->id,
            ],
        ], 201);
    }

    /**
     * Daftar customer untuk superadmin (pilih siapa yang mau diajak chat).
     */
    public function getCustomersForChat(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        if (! $user->hasRole('super_admin')) {
            return response()->json(['success' => false, 'message' => __('Tidak terautentikasi')], 403);
        }
        $customers = User::where('id', '!=', $user->id)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'username', 'email']);

        return response()->json([
            'success' => true,
            'data' => $customers->map(fn ($u) => [
                'id' => $u->id,
                'full_name' => $u->full_name,
                'username' => $u->username,
                'email' => $u->email,
            ]),
        ]);
    }

    public function getUnreadCount()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => 0,
            ],
        ]);
    }
}
