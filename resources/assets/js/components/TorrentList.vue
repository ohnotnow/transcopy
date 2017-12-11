/*
    - Make torrent list refresh with new torrents every ~10s?
    - Update incomplete torrents every ~10s?
    - Auto-copy new complete torrents?
*/
<template>
    <div>
        <h3 class="text-xl shadow rounded p-4 bg-grey-lighter">
            <div class="inline-flex items-center">
                <div class="flex-1 mx-2">
                    <button title="Download" @click="copyTorrents">
                        <svg class="icon-button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 20a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-2-8V5h4v5h3l-5 5-5-5h3z"/></svg>
                    </button>
                </div>
                <div class="flex-1 mx-2">
                    <a href="#" title="Refresh list" @click="refreshTorrents">
                        <svg class="icon-button refresh-button" :class="{ spin: refreshing }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M14.66 15.66A8 8 0 1 1 17 10h-2a6 6 0 1 0-1.76 4.24l1.42 1.42zM12 10h8l-4 4-4-4z"/></svg>
                    </a>
                </div>
                <div class="flex mx-2">
                    {{ copyList }}
                </div>                    
            </div>
        </h3>

        <div class="py-8 px-4 border-l-2 border-grey">
            <div class="mb-4" v-for="(torrent, index) in torrents">
                <torrent-entry
                    :torrent="torrent"
                    @selected="select(torrent.id)"
                    @unselected="unselect(torrent.id)"
                >
                </torrent-entry>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                torrents: [],
                copies: [],
                copyList: '',
                refreshing: true
            }
        },

        mounted() {
            this.getAllTorrents();
        },

        methods: {
            getAllTorrents() {
                axios.get('/api/torrents')
                    .then((response) => {
                        this.refreshing = false;
                        this.torrents = response.data.data;
                    });
            },

            select(id) {
                this.copies.push(id);
                console.log(this.copies);
            },

            unselect(id) {
                var index = this.copies.indexOf(id);
                this.copies.splice(index, 1);
                console.log(this.copies);
            },

            copyTorrents() {
                console.log(this.copies);
                axios.post('/api/copy/torrents', {copies: this.copies})
                    .then((response) => {
                        this.copyList = 'Copying: ' + response.data.data.message;
                        this.copies = [];
                    });
            },

            refreshTorrents() {
                this.refreshing = true;
                axios.post('/api/refresh/torrents')
                    .then((response) => {
                        this.copyList = '';
                        this.copies = [];
                        this.torrents = response.data.data;
                        this.refreshing = false;
                    })
                    .catch((error) => {
                        this.copies = [];
                        this.copyList = 'Error';
                        this.refreshing = false;
                    });
            }
        }
    }
</script>
