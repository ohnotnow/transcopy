<script>
export default {
  props: ["items", "searchables"],

  computed: {
    filteredItems() {
      const results = this.items
        .filter(item => {
          return this.itemToString(item)
            .join("")
            .toLowerCase()
            .includes(this.filterText.toLowerCase());
        })
        .sort((a, b) => {
          if (this.sortOrder) {
            return this.sortColumn
              ? b[this.sortColumn]
                  .toString()
                  .localeCompare(a[this.sortColumn].toString())
              : 0;
          }
          return this.sortColumn
            ? a[this.sortColumn]
                .toString()
                .localeCompare(b[this.sortColumn].toString())
            : 0;
        });
        this.$emit('filtered', {text: this.filterText, matches: results});
        return results;
    }
  },

  data() {
    return {
      filterText: "",
      sortColumn: null,
      sortOrder: false // false = a-z, true = z-a
    };
  },

  watch: {
    filterText() {
    }
  },

  methods: {
    itemToString(item) {
      if (!this.searchables) {
        return Object.values(item);
      }
      return this.searchables.split(",").map(term => item[term]);
    },

    setSortColumn(column) {
      if (this.sortColumn == column) {
        this.sortOrder = !this.sortOrder;
      } else {
        this.sortOrder = true;
      }
      this.sortColumn = column;
      this.$emit('sorted', {column: this.sortColumn, order: this.sortOrder});
    }
  },

  render() {
    return this.$scopedSlots.default({
      items: this.filteredItems,
      inputAttrs: {
        value: this.filterText
      },
      inputEvents: {
        input: e => {
          this.filterText = e.target.value;
        }
      },
      sortOn: this.setSortColumn
    });
  }
};
</script>