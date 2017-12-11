<template>
    <div>
        <label :class="{ error: this.broken }">
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
            </span>
        </label>
    </div>
</template>

<script>
    export default {
        props: ['torrent'],

        data() {
            return {
                entry: this.torrent,
                counter: 0,
                checked: false,
                broken: false
            }
        },

        computed: {
            copying() {
                return this.entry.copying;
            }
        },

        watch: {
            copying() {
                this.checked = false;
                setTimeout(this.update, this.randomDelay());
            }
        },

        mounted() {
            if (this.shouldUpdate()) {
                setTimeout(this.update, this.randomDelay());
            }
        },

        methods: {
            update() {
                let val = this.counter;
                this.counter++;
                console.log('HELLO ' + val + ' / ' + this.entry.torrent_id);
                axios.get('/api/torrents/' + this.entry.torrent_id)
                    .then((response) => {
                        this.entry = response.data.data;
                        if (this.shouldUpdate()) {
                            console.log('      ' + val + ' / ' + this.entry.torrent_id);
                            setTimeout(this.update, this.randomDelay());
                        }
                    })
                    .catch((error) => {
                        this.$emit('error');
                        this.broken = true;
                    });
            },

            changed() {
                if (this.checked) {
                    this.$emit('selected');
                } else {
                    this.$emit('unselected');
                }
            },
            
            randomDelay() {
                let min = 2000;
                let max = 5000;
                return Math.floor(Math.random() * (max - min)) + min;
            },

            isCopying() {
                return this.entry.copying;
            },

            isIncomplete() {
                return this.entry.incomplete;
            },

            shouldUpdate() {
                if (this.isCopying() || this.isIncomplete()) {
                    return true;
                }
                return false;
            }
        }
    }
</script>
