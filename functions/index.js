const functions = require('firebase-functions');
const admin = require('firebase-admin');

// Initialize Firebase Admin SDK
admin.initializeApp();

// Automatically create Firestore user record when Firebase Auth user is created
exports.createUserRecord = functions.auth.user().onCreate((user) => {
  return admin.firestore().collection("users").doc(user.uid).set({
    uid: user.uid,
    email: user.email,
    displayName: user.displayName || `${user.email.split('@')[0]}`,
    firstName: user.displayName ? user.displayName.split(' ')[0] : user.email.split('@')[0],
    lastName: user.displayName ? user.displayName.split(' ').slice(1).join(' ') : '',
    avatar: 'cat1.jpg',
    firebaseUid: user.uid,
    authProvider: 'firebase',
    isActive: true,
    createdAt: admin.firestore.FieldValue.serverTimestamp(),
    updatedAt: admin.firestore.FieldValue.serverTimestamp(),
    stats: {
      totalComments: 0,
      totalLikes: 0,
      totalReviews: 0
    }
  });
});

// Optional: Clean up Firestore when user is deleted from Firebase Auth
exports.deleteUserRecord = functions.auth.user().onDelete((user) => {
  return admin.firestore().collection("users").doc(user.uid).delete();
});
