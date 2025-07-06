importScripts("https://www.gstatic.com/firebasejs/11.8.1/firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs/11.8.1/firebase-messaging-compat.js");

// إعداد Firebase داخل الـ Service Worker
firebase.initializeApp({
  apiKey: "AIzaSyDLcDybmGxi7MHIo6Mr9WGy5P1dzh209S4",
  authDomain: "valet-system-95b47.firebaseapp.com",
  projectId: "valet-system-95b47",
  storageBucket: "valet-system-95b47.firebasestorage.app",
  messagingSenderId: "774555012520",
  appId: "1:774555012520:web:b0dc35bcd4fb4a6e7be19d",
  measurementId: "G-GKGD1LVM9E"
});

// تفعيل خدمة Firebase Messaging
const messaging = firebase.messaging();

// الاستماع للإشعار عند وصوله في الخلفية
messaging.onBackgroundMessage(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);

  const notificationTitle = payload.notification?.title || 'New Notification';
  const notificationOptions = {
    body: payload.notification?.body || '',
    icon: '/icon.png' // أيقونة الإشعار (اختياري)
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});
