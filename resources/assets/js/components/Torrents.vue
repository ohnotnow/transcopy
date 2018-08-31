<template>
  <div>

    <nav class="flex">
      <span @click="copy">Copy</span>
      <span
        :class="{'spin': refreshing}"
        @click="refresh"
      >
        Refresh
      </span>
    </nav>

    <transition name="fadeIn">
      <div
        v-for="torrent in torrentList"
        :key="torrent.id"
      >
        <torrent
          :torrent="torrent"
          @update="updateTorrent"
        />
      </div>
    </transition>

  </div>
</template>

<script>
export default {
  props: {
    torrents: {
      type: Array,
      default() {
        return {
          torrents: []
        };
      }
    }
  },

  data() {
    return {
      torrentList: this.torrents,
      refreshing: false
    };
  },

  watch: {
    torrentList() {
      this.updateCache();
    }
  },

  mounted() {
    this.refresh();
    const cachedTorrents = localStorage.getItem("torrents");
    if (cachedTorrents) {
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

      axios.post(route("api.torrent.copy", torrentsToCopy)).then(response => {
        this.torrentList.forEach(torrent => {
          torrent.is_selected = false;
          torrent.is_queued = true;
        });
      });
    },

    refresh() {
      this.refreshing = true;
      axios.get(route("api.torrent.index")).then(response => {
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