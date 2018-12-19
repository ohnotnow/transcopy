
window.Vue = require('vue').default;
import Api from './api';
window.api = new Api(window.api_url);

Vue.component('torrents', require('./components/Torrents.vue').default);

const app = new Vue({
    el: '#app'
});
