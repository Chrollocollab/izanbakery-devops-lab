<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NewOrderPlaced extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Order $order)
    {
    }

    /**
     * Telegram is sent once via an anonymous route (see CartController); admin
     * users get a database record plus a Reverb broadcast for the live bell
     * dropdown. Keeping both behind one class means a single AnonymousNotifiable
     * dispatch never fans the Telegram message out once per admin user.
     */
    public function via(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable
            ? ['telegram']
            : ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->name,
            'total' => (float) $this->order->total,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $itemLines = $this->order->items->map(
            fn ($item) => "- {$item->quantity}x {$item->product_name}"
        )->implode("\n");

        $orderUrl = route('admin.order.show', $this->order->id);

        $content = "*New order placed*\n"
            . "Order: `{$this->order->order_number}`\n"
            . "Customer: {$this->order->name} ({$this->order->phone})\n"
            . "Total: RM " . number_format((float) $this->order->total, 2) . "\n"
            . "{$itemLines}\n\n"
            . "[View order]({$orderUrl})";

        return TelegramMessage::create()
            ->to(config('services.telegram-bot-api.admin_chat_id'))
            ->content($content);
    }
}
