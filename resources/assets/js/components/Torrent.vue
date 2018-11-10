<template>
  <div
    class="flex text-grey-light hover:text-grey hover:cursor-pointer text-xl font-light tracking-wide mb-2"
    @click="toggleSelected"
  >
    <span
      class="mr-2"
      :class="{'text-green-light': theTorrent.is_selected}"
      v-html="icon"
    />
    <span class="mr-2 hover:text-grey">
      {{ theTorrent.name }}
      ({{ theTorrent.size }})
    </span>
    <span v-if="isActive()" class="text-grey-dark hover:text-grey">
      <span v-if="isDownloading()">
        Downloading ETA {{ theTorrent.eta }} / {{ theTorrent.percent }}%
      </span>
      <span
        v-if="isCopying()"
        class="pulse"
      >
        Copying
      </span>
      <span
        v-if="error"
        class="text-red"
        @click="clearFlags"
      >
        {{ error }}
      </span>
      <span
        v-if="copyFailed()"
        class="text-red pulse"
        @click="clearFlags"
      >
        Copy Failed
      </span>
    </span>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: Object,
      default() {
        return {
          id: -1,
          name: "n/a",
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
      theTorrent: this.value,
      error: ""
    };
  },

  computed: {
    icon() {
      // return svg depending on copying/queued/other state
      if (this.wasCopied()) {
        return `<svg xmlns="http://www.w3.org/2000/svg" class="icon-small" fill="currentColor" viewBox="0 0 20 20"><path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/></svg>`;
      }
      if (this.isCopying()) {
        return `<svg xmlns="http://www.w3.org/2000/svg" class="icon-small pulse spin" fill="currentColor" viewBox="0 0 20 20"><path class="heroicon-ui" d="M5.41 16H18a2 2 0 0 0 2-2 1 1 0 0 1 2 0 4 4 0 0 1-4 4H5.41l2.3 2.3a1 1 0 0 1-1.42 1.4l-4-4a1 1 0 0 1 0-1.4l4-4a1 1 0 1 1 1.42 1.4L5.4 16zM6 8a2 2 0 0 0-2 2 1 1 0 0 1-2 0 4 4 0 0 1 4-4h12.59l-2.3-2.3a1 1 0 1 1 1.42-1.4l4 4a1 1 0 0 1 0 1.4l-4 4a1 1 0 0 1-1.42-1.4L18.6 8H6z"/></svg>`;
      }
      if (this.isQueued()) {
        return `<svg class="icon-small" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M2 2c0-1.1.9-2 2-2h12a2 2 0 0 1 2 2v18l-8-4-8 4V2zm2 0v15l6-3 6 3V2H4z"/></svg>`;
      }
      if (this.copyFailed()) {
        return `<svg class="icon-small text-red pulse" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zM9 5v6h2V5H9zm0 8v2h2v-2H9z"/></svg>`;
      }
      return `<svg class="icon-small" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"/></svg>`;
    }
  },

  mounted() {
    this.refreshIfActive();
  },

  methods: {
    update() {
      this.$emit("update", this.theTorrent);
      this.refreshIfActive();
    },

    toggleSelected() {
      this.theTorrent.is_selected = !this.theTorrent.is_selected;
      this.update();
      this.$emit("toggled");
    },

    async refresh() {
      const torrent = await api.getTorrent(this.theTorrent.id);
      if (torrent) {
        this.theTorrent = torrent;
        this.update();
        this.error = "";
      } else {
        this.error = api.error;
      }
      this.refreshIfActive();
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

    clearFlags() {
      this.theTorrent.is_copying = false;
      this.theTorrent.is_queued = false;
      this.theTorrent.is_selected = false;
      this.error = "";
      this.update();
    },

    isActive() {
      return this.isCopying() || this.isQueued() || this.isDownloading() || this.error;
    },

    isCopying() {
      return this.theTorrent.copying;
    },

    copyFailed() {
      return this.theTorrent.copy_failed;
    },

    wasCopied() {
      return this.theTorrent.copied;
    },

    isQueued() {
      return this.theTorrent.should_copy;
    },

    isDownloading() {
      return this.theTorrent.incomplete;
    },

    randomWait() {
      let min = 1000;
      let max = 5000;
      return Math.floor(Math.random() * (max - min + 1)) + min;
    }
  }
};
</script>