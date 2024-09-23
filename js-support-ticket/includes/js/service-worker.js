importScripts('https://www.gstatic.com/firebasejs/5.8.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/5.8.2/firebase-messaging.js');
// importScripts('https://www.gstatic.com/firebasejs/4.6.2/firebase-database.js');

var config = {
    apiKey: common.apiKey_firebase,
    authDomain: common.authDomain_firebase,
    databaseURL: common.databaseURL_firebase,
    projectId: common.projectId_firebase,
    storageBucket: common.storageBucket_firebase,
    messagingSenderId: common.messagingSenderId_firebase
  };
firebase.initializeApp(config);

const messaging = firebase.messaging();

var link = "";

messaging.setBackgroundMessageHandler(function(payload){
  return self.registration.pushManager.getSubscription().then(function(sub){
      link = payload.data.link;
      if(link.charAt(link.length - 1) == "/"){
        link = link+"E_14"+btoa(sub.endpoint);
      }else{
        link = link+"/E_14"+btoa(sub.endpoint);
      }
      return self.registration.showNotification(payload.data.title,{
          body: payload.data.body,
          icon: payload.data.icon,
      })
  });
});

self.addEventListener('notificationclick', function(e) {
  var notification = e.notification;
  clients.openWindow(link);
  notification.close();
});
