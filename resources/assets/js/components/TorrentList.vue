/*
    - Make torrent list refresh with new torrents every ~10s?
    - Update incomplete torrents every ~10s?
    - Auto-copy new complete torrents?
*/
<template>
    <div>
        <ul>
            <li v-for="{index, torrent} in torrents">
                {{ torrent.name }}
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                torrents: [],
                lastChecked: 0
            }
        },

        mounted() {
            console.log('Component mounted.');
            this.getAllTorrents();
        },

        methods: {
            getAllTorrents() {
                axios.get('/api/torrents')
                    .then((response) => {
                        this.torrents = response.data.data;
                        this.lastChecked = Math.floor(Date.now() / 1000);
                    });
            },

            getUpdatedTorrents() {
                axios.get('/api/torrents?since=' + this.lastChecked)
                    .then((response) => {
                        console.log('hello');
                        console.log(response.data.data);
                    });
            }
        }
    }
</script>
