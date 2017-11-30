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
                torrents: []
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
                    });
            },

            getChangedTorrents() {
                axios.get('/api/torrents/updates')
                    .then((response) => {
                        console.log('hello');
                    });
            }
        }
    }
</script>
