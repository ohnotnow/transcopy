<template>
  <div>
    <h3 class="text-xl shadow-md rounded p-4 bg-grey-dark mb-4">
        <div class="inline-flex items-center text-grey-lightest">
            <div class="flex-1 mx-2 relative">
                <button title="Download" @click.prevent="copy" class="text-grey-light hover:text-grey" :class="{ 'flashIt': eventHappened }">
                    <svg class="w-8 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M10 20a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-2-8V5h4v5h3l-5 5-5-5h3z"/>
                    </svg>
                </button>
                <span v-show="numberToCopy > 0" class="absolute pin-b -mx-2 text-green-lightest bg-green-darkest px-1 rounded-lg shadow-lg text-base opacity-50" v-text="numberToCopy">
                </span>
            </div>
            <div class="flex-1 mx-2">
                <button title="Refresh list" @click.prevent="refresh" class="text-grey-light hover:text-grey">
                    <svg class="w-8 fill-current refresh-button" :class="{ spin: refreshing }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M14.66 15.66A8 8 0 1 1 17 10h-2a6 6 0 1 0-1.76 4.24l1.42 1.42zM12 10h8l-4 4-4-4z"/>
                    </svg>
                </button>
            </div>
            <div class="flex mx-2" v-show="error">
                {{ error }}
            </div>
        </div>
    </h3>

    <transition-group name="fadeIn" tag="span">
      <div
        v-for="torrent in torrentList"
        :key="torrent.id"
      >
        <torrent
          :value="torrent"
          @update="updateTorrent"
        />
      </div>
    </transition-group>

  </div>
</template>

<script>
import Torrent from "./Torrent.vue";

export default {
  components: { Torrent },

  data() {
    return {
      torrentList: [],
      refreshing: false,
      eventHappened: false,
      error: ""
    };
  },

  computed: {
    numberToCopy() {
      return this.torrentList.reduce(
        (sum, torrent) => sum + torrent.is_selected,
        0
      );
    }
  },

  watch: {
    torrentList() {
      this.updateCache();
    }
  },

  mounted() {
    this.refresh();
    const cachedTorrents = localStorage.getItem("torrents");
    if (cachedTorrents && cachedTorrents instanceof Array) {
      this.torrentList = cachedTorrents;
    }
  },

  methods: {
    updateCache() {
      // set localstorage with cached most recent ~150 torrents
    },

    copy() {
      const torrentsToCopy = this.torrentList
        .filter(torrent => {
          return torrent.is_selected;
        })
        .map(torrent => {
          return torrent.id;
        });

      if (torrentsToCopy.length === 0) {
        return;
      }

      axios.post("/api/copy", { copies: torrentsToCopy }).then(response => {
        this.torrentList.forEach(torrent => {
          torrent.is_selected = false;
          torrent.is_queued = true;
        });
      });
    },

    refresh() {
      this.refreshing = true;
      axios.get("/api/refresh").then(response => {
        this.torrentList = response.data.data;
        this.refreshing = false;
      });
    },

    updateTorrent(torrent) {
      const index = this.torrentList.findIndex(tor => tor.id === torrent.id);
      if (index === -1) {
        return;
      }
      this.torrentList = [
        ...this.torrentList.slice(0, index),
        torrent,
        ...this.torrentList.slice(index + 1)
      ];
    }
  }
};
</script>