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
    <span class="mr-2">
      {{ theTorrent.name }}
      ({{ theTorrent.size }})
    </span>
    <span v-if="isActive()">
      <span v-if="isDownloading()">
        Downloading ETA {{ theTorrent.eta }}
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
      theTorrent: this.value
    };
  },

  mounted() {
    this.refreshIfActive();
  },

  computed: {
    icon() {
      // return svg depending on copying/queued/other state
      if (this.is_selected) {
        return "<span>Selected</span>";
      }
      if (this.isCopying()) {
        return "<span>Copying</span>";
      }
      if (this.isQueued()) {
        return `<svg class="icon-small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2 2c0-1.1.9-2 2-2h12a2 2 0 0 1 2 2v18l-8-4-8 4V2zm2 0v15l6-3 6 3V2H4z"/></svg>`;
      }
      return `<svg class="icon-small" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"/></svg>`;
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
        .get("/api/torrent/" + this.theTorrent.id)
        .then(response => {
          this.theTorrent = response.data.data;
          this.update();
          this.refreshIfActive();
        })
        .catch(error => {
          console.log(error);
          this.$emit("error", error);
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