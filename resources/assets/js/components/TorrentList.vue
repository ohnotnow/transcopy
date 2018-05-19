<template>
    <div>
        <h3 class="text-xl shadow-md rounded p-4 bg-grey-dark mb-4">
            <div class="inline-flex items-center text-grey-lightest">
                <div class="flex-1 mx-2 relative">
                    <button title="Download" @click="copyTorrents" class="text-grey-light hover:text-grey" :class="{ 'flashIt': eventHappened }">
                        <svg class="w-8 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M10 20a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-2-8V5h4v5h3l-5 5-5-5h3z"/>
                        </svg>
                    </button>
                    <span v-show="copies.length > 0" class="absolute pin-b -mx-2 text-green-lightest bg-green-darkest px-1 rounded-lg shadow-lg text-base opacity-50" v-text="copies.length">
                    </span>
                </div>
                <div class="flex-1 mx-2">
                    <button title="Refresh list" @click="refreshTorrents" class="text-grey-light hover:text-grey">
                        <svg class="w-8 fill-current refresh-button" :class="{ spin: refreshing }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M14.66 15.66A8 8 0 1 1 17 10h-2a6 6 0 1 0-1.76 4.24l1.42 1.42zM12 10h8l-4 4-4-4z"/>
                        </svg>
                    </button>
                </div>
                <div class="flex mx-2" v-show="serverError">
                    {{ serverError }}
                </div>
            </div>
        </h3>

        <div class="py-8 px-4 border border-grey-dark rounded shadow-md bg-grey-dark ">
            <div class="mb-4" v-for="(torrent, index) in torrentList" :key="torrent.id">
                <torrent-entry
                    :torrent="torrent"
                    @selected="select(torrent.id)"
                    @unselected="unselect(torrent.id)"
                    @error="serverError = 'Error refreshing ' + torrent.name"
                    @success="clearErrorFor(torrent)"
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
                refreshing: true,
                serverError: '',
                eventHappened: false,
                temporaryTorrents: [],
                fakeTorrents: [],
                faders: [],
            }
        },

        computed: {
            torrentList() {
                if (this.torrents.length > 0) {
                    return this.torrents;
                }
                return this.fakeTorrents;
            }
        },

        mounted() {
            var storedTorrents = localStorage.getItem('torrents');
            if (storedTorrents) {
                this.temporaryTorrents = JSON.parse(storedTorrents);
                this.fadeInFakes();
            }
            this.refreshTorrents();
            window.addEventListener('beforeunload', this.saveTorrents)
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
                this.eventHappened = true;
                setTimeout(() => {this.eventHappened = false;}, 500);
            },

            unselect(id) {
                var index = this.copies.indexOf(id);
                this.copies.splice(index, 1);
                this.eventHappened = true;
                setTimeout(() => {this.eventHappened = false;}, 500);
            },

            copyTorrents() {
                this.serverError = '';
                axios.post('/api/copy/torrents', {copies: this.copies})
                    .then((response) => {
                        this.markTorrentsAsCopying();
                    })
                    .catch((error) => {
                        this.serverError = error.response.data.message;
                        console.log('Error while copying torrents');
                    });
            },

            markTorrentsAsCopying() {
                this.copies.forEach(id => {
                    Event.$emit('copying', id);
                });
                this.copies = [];
            },

            refreshTorrents() {
                this.refreshing = true;
                this.serverError = '';
                //this.torrents = [];
                axios.post('/api/refresh/torrents')
                    .then((response) => {
                        this.torrents = response.data.data;
                        this.saveTorrents();
                        this.refreshing = false;
                        this.faders.forEach(fader => clearTimeout(fader));
                    })
                    .catch((error) => {
                        this.copyList = 'Error';
                        this.refreshing = false;
                        this.serverError = error.response.data.message;
                    });
            },

            clearErrorFor(torrent) {
                if (this.serverError.indexOf(torrent.name) > -1) {
                    this.serverError = '';
                }
            },

            fadeInFakes() {
                if (this.temporaryTorrents.length <= 0) {
                    return;
                }
                var torrent = this.temporaryTorrents.shift();
                this.fakeTorrents.push(torrent);
                this.faders.push(setTimeout(this.fadeInFakes, 100));
            },

            saveTorrents() {
                localStorage.setItem('torrents', JSON.stringify(this.torrents.slice(0, 100)));
            }
        }
    }
</script>
