import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

export const echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY as string,
  wsHost: import.meta.env.VITE_PUSHER_HOST as string,
  wsPort: Number(import.meta.env.VITE_PUSHER_PORT),
  cluster: 'mt1',
  forceTLS: false,
  encrypted: false,
  disableStats: true,
  enabledTransports: ['ws', 'wss'],
})
