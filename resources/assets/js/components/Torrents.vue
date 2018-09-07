<template>
  <div>
    <h3 class="text-xl shadow-md rounded p-4 bg-grey-dark mb-4">
      <div class="inline-flex items-center text-grey-lightest">
        <div class="flex-1 mx-2 relative">
          <button
            title="Download"
            class="text-grey-light hover:text-grey"
            :class="{ 'flashIt': torrentToggled }"
            @click.prevent="copy"
          >
            <svg
              class="w-8 fill-current"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
            >
              <path d="M10 20a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-2-8V5h4v5h3l-5 5-5-5h3z"/>
            </svg>
          </button>
          <span
            v-show="numberToCopy > 0"
            class="absolute pin-b -mx-2 text-green-lightest bg-green-darkest px-1 rounded-lg shadow-lg text-base opacity-50"
            v-text="numberToCopy"
          />
        </div>
        <div class="flex-1 mx-2">
          <button
            title="Refresh list"
            class="text-grey-light hover:text-grey"
            @click.prevent="refresh"
          >
            <svg
              class="w-8 fill-current refresh-button"
              :class="{ spin: refreshing }"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
            >
              <path d="M14.66 15.66A8 8 0 1 1 17 10h-2a6 6 0 1 0-1.76 4.24l1.42 1.42zM12 10h8l-4 4-4-4z"/>
            </svg>
          </button>
        </div>
        <div
          v-show="error"
          class="flex mx-2"
        >
          {{ error }}
        </div>
      </div>
    </h3>

    <transition-group
      name="fadeIn"
      tag="span"
    >
      <div
        v-for="torrent in torrentList"
        :key="torrent.id"
      >
        <torrent
          :value="torrent"
          @update="updateTorrent"
          @toggled="flashCopyIcon"
          @error="setError"
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
      torrentToggled: false,
      error: ""
    };
  },

  computed: {
    numberToCopy() {
      return this.selectedTorrents.length;
    },
    selectedTorrents() {
      return this.torrentList.filter(torrent => torrent.is_selected);
    }
  },

  watch: {
    torrentList() {
      this.updateCache();
    }
  },

  mounted() {
    this.refresh();
    // const cachedTorrents = localStorage.getItem("torrents");
    // if (cachedTorrents) {
    //   this.torrentList = JSON.parse(cachedTorrents);
    // }
  },

  methods: {
    updateCache() {
      localStorage.setItem(
        "torrents",
        JSON.stringify(this.torrentList.slice(0, 150))
      );
    },

    async copy() {
      const torrentsToCopy = this.selectedTorrents.map(torrent => {
        return torrent.id;
      });

      if (torrentsToCopy.length === 0) {
        return;
      }

      const response = await api.copy(torrentsToCopy);
      if (response) {
        this.torrentList.forEach(torrent => {
          torrent.is_selected = false;
          torrent.should_copy = true;
        });
      }
    },

    setError(error) {
      this.error = error;
    },

    async refresh() {
      this.refreshing = true;
      const torrents = await api.refresh();
      if (torrents) {
        this.torrentList = torrents;
        this.refreshing = false;
        this.error = "";
      } else {
        this.setError("Could not get current torrent list");
      }
    },

    updateTorrent(torrent) {
      const index = this.torrentList.findIndex(tor => tor.id == torrent.id);
      if (index === -1) {
        return;
      }

      torrent.is_selected = this.torrentList[index].is_selected;

      this.torrentList = [
        ...this.torrentList.slice(0, index - 1),
        torrent,
        ...this.torrentList.slice(index + 1)
      ];
    },

    flashCopyIcon() {
      this.torrentToggled = true;
      setTimeout(() => {
        this.torrentToggled = false;
      }, 500);
    }
  }
};
</script>