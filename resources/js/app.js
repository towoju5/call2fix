import './bootstrap';

import Ably from 'ably';

const client = new Ably.Realtime('your-ably-api-key');

// Subscribe to a channel
const channel = client.channels.get('test-channel');

// Subscribe to messages
channel.subscribe('message', (message) => {
  console.log('New message received:', message.data);
});

// Send a message to the channel
channel.publish('message', 'Hello from Laravel!');
