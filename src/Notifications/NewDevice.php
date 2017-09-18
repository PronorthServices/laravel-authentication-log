<?php

namespace Yadahan\AuthenticationLog\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Notifications\Notification;
use Yadahan\AuthenticationLog\AuthenticationLog;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Notifications\Messages\SlackMessage;

class NewDevice extends Notification
{
    use Queueable;

    /**
     * The authentication log.
     *
     * @var \Yadahan\AuthenticationLog\AuthenticationLog
     */
    public $authenticationLog;

    /**
     * Create a new notification instance.
     *
     * @param  \Yadahan\AuthenticationLog\AuthenticationLog  $authenticationLog
     * @return void
     */
    public function __construct(AuthenticationLog $authenticationLog)
    {
        $this->authenticationLog = $authenticationLog;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $notifiable->notifyAuthenticationLogVia();
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Login from a new device')
            ->markdown('vendor.authentication-log.emails.new', [
                'account' => $notifiable,
                'time' => $this->authenticationLog->login_at,
                'ipAddress' => $this->authenticationLog->ip_address,
                'browser' => $this->authenticationLog->user_agent,
            ]);
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->from(config('app.name'))
            ->warning()
            ->content('Your '.config('app.name').' account logged in from a new device.')
            ->attachment(function ($attachment) use ($notifiable) {
                $attachment->fields([
                    'Account' => $notifiable->email,
                    'Time' => $this->authenticationLog->login_at->toCookieString(),
                    'IP Address' => $this->authenticationLog->ip_address,
                    'Browser' => $this->authenticationLog->user_agent,
                ]);
            });
    }

    /**
     * Get the Nexmo / SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\NexmoMessage
     */
    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
            ->content('Your '.config('app.name').' account logged in from a new device.');
    }
}
