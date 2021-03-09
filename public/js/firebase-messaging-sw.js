// Give the service worker access to Firebase Messaging.
// Note that you can only use Firebase Messaging here, other Firebase libraries
// are not available in the service worker.
importScripts('https://www.gstatic.com/firebasejs/5.3.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/5.3.0/firebase-messaging.js');

// Initialize the Firebase app in the service worker by passing in the
// messagingSenderId.
firebase.initializeApp({
  'messagingSenderId': location.search.split('messagingSenderId=')[1]
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  // Customize notification here
  const notificationTitle = 'Background Message Title';
  const notificationOptions = {
    body: 'Background Message body.',
    icon: 'https://dev.plugins.db-dzine.com/fire-push/wp-content/uploads/sites/40/2017/08/totaldesk-fav-32.png'
  };

  return self.registration.showNotification(notificationTitle,
      notificationOptions);
});