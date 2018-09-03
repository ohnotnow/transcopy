
window.Vue = require('vue');
import Api from './api';
window.api = new Api(window.api_url);

Vue.component('torrents', require('./components/Torrents.vue'));

const app = new Vue({
    el: '#app'
});
