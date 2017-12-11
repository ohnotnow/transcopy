<template>
    <div>
        <label>
            <input type="checkbox" @change="changed" v-model="checked" :value="entry.id">
            {{ entry.name }}
            <span class="opacity-50">
                ({{ entry.size }})
                <span v-show="isIncomplete">
                    ETA: {{ entry.eta }}
                    Done: {{ entry.percent }}%
                </span>
                <span v-show="entry.copied" title="Already copied">
                    (COPIED)
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
                checked: false
            }
        },

        computed: {
            isIncomplete() {
                return this.entry.incomplete;
            }
        },

        mounted() {
            if (this.isIncomplete) {
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
                        if (this.isIncomplete) {
                            console.log('      ' + val + ' / ' + this.entry.torrent_id);
                            setTimeout(this.update, this.randomDelay());
                        }
                    });
            },

            changed() {
                console.log(this.checked);
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
            }
        }
    }
</script>
