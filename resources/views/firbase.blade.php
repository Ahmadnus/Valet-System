<script type="module">
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/11.5.0/firebase-app.js";
  import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.5.0/firebase-analytics.js";
  // TODO: Add SDKs for Firebase products that you want to use
  // https://firebase.google.com/docs/web/setup#available-libraries

  // Your web app's Firebase configuration
  // For Firebase JS SDK v7.20.0 and later, measurementId is optional
  const firebaseConfig = {
    apiKey: "AIzaSyDLcDybmGxi7MHIo6Mr9WGy5P1dzh209S4",
    authDomain: "valet-system-95b47.firebaseapp.com",
    projectId: "valet-system-95b47",
    storageBucket: "valet-system-95b47.firebasestorage.app",
    messagingSenderId: "774555012520",
    appId: "1:774555012520:web:b0dc35bcd4fb4a6e7be19d",
    measurementId: "G-GKGD1LVM9E"
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const analytics = getAnalytics(app);
  console.log(getToken());


</script>