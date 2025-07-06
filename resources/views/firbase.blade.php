<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <title>Valet System Notifications</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

  <!-- إشعار FCM يظهر داخل الصفحة -->
  <div id="fcm-alert" style="
    display: none;
    position: fixed;
    top: 80px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    background-color: #007bff;
    color: white;
    padding: 15px 25px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    font-family: sans-serif;
    min-width: 300px;
    max-width: 80%;
    text-align: center;
    transition: all 0.4s ease-in-out;
  ">
    <strong id="fcm-title" style="display:block; font-size: 1.1em;"></strong>
    <span id="fcm-body"></span>
  </div>

  <!-- سكربت Firebase و FCM -->
  <script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/11.8.1/firebase-app.js";
    import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.8.1/firebase-analytics.js";
    import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/11.8.1/firebase-messaging.js";

    const firebaseConfig = {
      apiKey: "AIzaSyDLcDybmGxi7MHIo6Mr9WGy5P1dzh209S4",
      authDomain: "valet-system-95b47.firebaseapp.com",
      projectId: "valet-system-95b47",
      storageBucket: "valet-system-95b47.firebasestorage.app",
      messagingSenderId: "774555012520",
      appId: "1:774555012520:web:b0dc35bcd4fb4a6e7be19d",
      measurementId: "G-GKGD1LVM9E"
    };

    const app = initializeApp(firebaseConfig);
    const analytics = getAnalytics(app);
    const messaging = getMessaging(app);

    Notification.requestPermission().then((permission) => {
      if (permission === "granted") {
        navigator.serviceWorker.register("/firebase-messaging-sw.js")
          .then((registration) => {
            console.log("✅ Service Worker Registered");

            getToken(messaging, {
              vapidKey: "BOjA2-ZHUWmltsbnOyhgU2Msy4Im793nEaviRWY25MmKtwuR-DpxdWpee_nWH4f5gCCj3M8bHHhbto6iADsaV0I", // استبدله من Firebase Console
              serviceWorkerRegistration: registration
            }).then((currentToken) => {
              if (currentToken) {
                console.log("📱 FCM Token:", currentToken);
                // يمكنك إرسال التوكن للسيرفر هنا عبر AJAX أو Fetch
              } else {
                console.log("⚠️ No registration token available.");
              }
            }).catch((err) => {
              console.log("❌ Error retrieving token: ", err);
            });
          });
      } else {
        console.log("🚫 Notifications permission denied.");
      }
    });

    onMessage(messaging, (payload) => {
      console.log("📨 Message received. ", payload);

      const title = payload.notification?.title || "New Notification";
      const body = payload.notification?.body || "";

      document.getElementById("fcm-title").textContent = title;
      document.getElementById("fcm-body").textContent = body;

      const alertDiv = document.getElementById("fcm-alert");
      alertDiv.style.display = "block";
      alertDiv.style.opacity = "1";
      alertDiv.style.top = "80px";

      setTimeout(() => {
        alertDiv.style.opacity = "0";
        alertDiv.style.top = "50px";
      }, 5000);

      setTimeout(() => {
        alertDiv.style.display = "none";
      }, 6000);
    });
  </script>

</body>
</html>
