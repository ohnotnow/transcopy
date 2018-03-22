<template>
    <div>
        <label class="text-grey-light hover:text-grey" @click="toggle">

            <span v-if="broken" class="text-white text-red shadow">
                <svg class="icon-small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zM9 5v6h2V5H9zm0 8v2h2v-2H9z"/></svg>
            </span>
            <span v-else-if="entry.copied" title="Already copied">
                <svg class="icon-small" :class="{ 'text-green-light': checked }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/></svg>
            </span>
            <span v-else-if="entry.should_copy" title="Copy queued">
                <svg class="icon-small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2 2c0-1.1.9-2 2-2h12a2 2 0 0 1 2 2v18l-8-4-8 4V2zm2 0v15l6-3 6 3V2H4z"/></svg>
            </span>
            <span v-else>
                <svg class="icon-small" :class="{ 'text-green-light': checked }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"/></svg>
            </span>

            <span class="pl-1">
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
                </span>
            </span>
        </label>
            <a href="#" class="pulse appearance-none text-red no-underline" v-show="copyFailed()" @click="clearFlags">
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
                axios.get('/api/torrents/' + this.entry.id)
                    .then((response) => {
                        this.$emit('success');
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

            toggle() {
                this.checked = ! this.checked;
                this.changed();
            },

            randomDelay() {
                let min = 500;
                let max = 2000;
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

            shouldCopy() {
                return this.entry.should_copy;
            },

            copyFailed() {
                if (this.entry.copy_failed) {
                    this.broken = true;
                }
                return this.entry.copy_failed;
            },

            shouldUpdate() {
                if (this.isCopying() || this.isIncomplete() || this.copyFailed() || this.shouldCopy()) {
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
