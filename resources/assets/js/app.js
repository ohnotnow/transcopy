
window.Vue = require('vue');
import Api from './Api';

Vue.component('torrents', require('./components/Torrents.vue'));

const app = new Vue({
    el: '#app'
});
