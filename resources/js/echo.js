import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 6001,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 6001,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

// Удобная обёртка для добавления слушателей
window.EchoAddListener = (channelName, eventName, callback) => {
    if (!window.Echo) {
        console.error('Echo is not initialized yet!');
        return;
    }
    window.Echo.channel(channelName)
        .listen(eventName, callback);
};
