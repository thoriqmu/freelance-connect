import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import apiClient from '@/services/apiClient' 

window.Pusher = Pusher

const echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT,
  forceTLS: false,
  enabledTransports: ['ws'],

  authorizer: (channel: any) => {
    return {
      authorize: (socketId: string, callback: Function) => {
        apiClient.post('/broadcasting/auth', {
          socket_id: socketId,
          channel_name: channel.name
        }, {
          headers: {
             'Accept': 'application/json'
          }
        })
        .then(response => {
          const authPayload = response.data.auth 
                              ? response.data 
                              : (response.data.data || response.data);
                              
          callback(false, authPayload);
        })
        .catch(error => {
          callback(true, error);
        });
      }
    };
  },
});

;(window as any).Echo = echo;

export default echo;