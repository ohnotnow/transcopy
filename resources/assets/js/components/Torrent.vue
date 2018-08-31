<template>
  <div class="flex">
    <span @click="toggleSelected">
      {{ icon }}
    </span>
    <span>
      {{ theTorrent.title }}
      {{ theTorrent.size }}
    </span>
    <span v-if="isActive()">
      <span v-if="isDownloading()">
        Downloading {{ theTorrent.eta }}
      </span>
      <span
        v-if="isCopying()"
        class="pulse"
      >
        Copying
      </span>
    </span>
  </div>
</template>

<script>
export default {
  props: {
    torrent: {
      type: Object,
      default() {
        return {
          id: -1,
          title: "n/a",
          size: "n/a",
          eta: "n/a",
          was_copied: false,
          is_downloading: false,
          is_copying: false,
          is_queued: false,
          is_selected: false
        };
      }
    }
  },

  data() {
    return {
      theTorrent: this.value
    };
  },

  computed: {
    icon() {
      // return svg depending on copying/queued/other state
      return true;
    }
  },

  methods: {
    update() {
      this.$emit("update", this.theTorrent);
    },

    toggleSelected() {
      this.theTorrent.is_selected = !this.theTorrent.is_selected;
      this.update();
    },

    refresh() {
      axios
        .get(route("api.torrent.show", this.theTorrent.id))
        .then(response => {
          this.theTorrent = response.data.data;
          this.update();
          this.refreshIfActive();
        });
    },

    refreshIfActive() {
      this.$nextTick(() => {
        if (this.isActive()) {
          setTimeout(() => {
            this.refresh();
          }, this.randomWait());
        }
      });
    },

    isActive() {
      return this.isCopying() || this.isQueued() || this.isDownloading();
    },

    isCopying() {
      return this.theTorrent.is_copying;
    },

    isQueued() {
      return this.theTorrent.is_queued;
    },

    isDownloading() {
      return this.theTorrent.is_downloading;
    },

    randomWait() {
      let min = 1000;
      let max = 5000;
      return Math.floor(Math.random() * (max - min + 1)) + min;
    }
  }
};
