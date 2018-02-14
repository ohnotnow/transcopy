<template>
    <div>
        <label class="hover:text-grey-darkest" :class="{ error: this.broken }">
            <input type="checkbox" @change="changed" v-model="checked" :value="entry.id">
            {{ entry.name }}
            <span class="opacity-50">
                ({{ entry.size }})
                <span class="pulse" v-show="isCopying()">
                    Copying
                </span>
                <span v-show="isIncomplete()">
                    ETA: {{ entry.eta }}
                    Done: {{ entry.percent }}%
                </span>
                <span v-show="entry.copied" title="Already copied">
                    <svg class="icon-small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/></svg>
                </span>
                <span v-show="entry.should_copy" title="Copy queued">
                    <svg class="icon-small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2 2c0-1.1.9-2 2-2h12a2 2 0 0 1 2 2v18l-8-4-8 4V2zm2 0v15l6-3 6 3V2H4z"/></svg>
                </span>
            </span>
        </label>
            <a href="#" class="pulse" v-show="copyFailed()" @click="clearFlags">
                Copy Failed
            </a>
    </div>
</template>

<script>
    export default {
        props: ['torrent'],

        data() {
            return {
                entry: this.torrent,
                checked: false,
                broken: false
            }
        },

        mounted() {
            Event.$on('copying', (id, event) => {
                if (this.entry.id == id) {
                    this.checked = false;
                    if (this.isComplete()) {
                        this.entry.copying = true;
                    }
                }
            });
            this.checkForUpdates();
        },

        methods: {
            update() {
                axios.get('/api/torrents/' + this.entry.torrent_id)
                    .then((response) => {
                        this.entry = response.data.data;
                        this.broken = false;
                        this.checkForUpdates();
                    })
                    .catch((error) => {
                        this.$emit('error');
                        this.broken = true;
                        this.checkForUpdates();
                    });
            },

            checkForUpdates() {
                if (this.shouldUpdate()) {
                    setTimeout(this.update, this.randomDelay());
                }
            },

            changed() {
                if (this.checked) {
                    this.$emit('selected', this.entry.id);
                } else {
                    this.$emit('unselected', this.entry.id);
                }
            },

            randomDelay() {
                let min = 4000;
                let max = 6000;
                return Math.floor(Math.random() * (max - min)) + min;
            },

            isCopying() {
                return this.entry.copying;
            },

            isIncomplete() {
                return this.entry.incomplete;
            },

            isComplete() {
                return ! this.isIncomplete();
            },

            copyFailed() {
                if (this.entry.copy_failed) {
                    this.broken = true;
                }
                return this.entry.copy_failed;
            },

            shouldUpdate() {
                if (this.isCopying() || this.isIncomplete() || this.copyFailed()) {
                    return true;
                }
                return false;
            },

            clearFlags() {
                axios.delete('/api/torrents/' + this.entry.id + '/clear-flags')
                    .then((response) => {
                        this.checkForUpdates();
                    })
                    .catch((error) => {
                        this.$emit('error');
                        this.broken = true;
                        this.checkForUpdates();
                    });
            }
        }
    }
</script>
