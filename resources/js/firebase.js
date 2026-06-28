import { initializeApp } from 'firebase/app'

const firebaseConfig = {
  apiKey: 'AIzaSyAe4lkjdYnfuVmoIP2BEucbAhss5MKj3qg',
  authDomain: 'wedding-decorasi-flower.firebaseapp.com',
  databaseURL: 'https://wedding-decorasi-flower-default-rtdb.firebaseio.com',
  projectId: 'wedding-decorasi-flower',
  storageBucket: 'wedding-decorasi-flower.firebasestorage.app',
  messagingSenderId: '283198742077',
  appId: '1:283198742077:web:f19a35812923ba25f79454',
}

const app = initializeApp(firebaseConfig)

export default app
