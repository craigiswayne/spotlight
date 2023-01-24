export default {
  data() {
    return {
      typeAheadPointer: null
    }
  },

  watch: {
    filteredOptions() {
      let items = this._computeItems(this.filteredOptions, true).concat(this._computeItems(this.filteredOptions, false));
      if(!items || items.length == 0) {
        return;
      }
      
      this.typeAheadPointer = items[0].slug;      
    }
  },

  methods: {
    /**
     * Move the typeAheadPointer visually up the list by
     * setting it to the previous selectable option.
     * @return {void}
     */
    typeAheadUp() {
      let items = this._computeItems(this.filteredOptions, true).concat(this._computeItems(this.filteredOptions, false));
      
      var selectedIndex = items.findIndex((item => {
        return item.slug == this.typeAheadPointer;
      }));

      if(selectedIndex > -1) {
        selectedIndex--;
      }

      if(selectedIndex < 1) {
        selectedIndex = items.length-1;
      }

      this.typeAheadPointer = items[selectedIndex].slug;     
      setTimeout(function() {
        this._handleFindHighlightedItem(null);
      }.bind(this), 50);
    },

    /**
     * Move the typeAheadPointer visually down the list by
     * setting it to the next selectable option.
     * @return {void}
     */
    typeAheadDown() {

      let items = this._computeItems(this.filteredOptions, true).concat(this._computeItems(this.filteredOptions, false));
      
      var selectedIndex = items.findIndex((item => {
        return item.slug == this.typeAheadPointer;
      }));

      if(selectedIndex > -1) {
        selectedIndex++;
      }

      if(selectedIndex == -1 || selectedIndex > items.length-1) {
        selectedIndex = 0;
      }

      this.typeAheadPointer = items[selectedIndex].slug;     
      setTimeout(function() {
        this._handleFindHighlightedItem(null);
      }.bind(this), 50);
    },

    /**
     * Select the option at the current typeAheadPointer position.
     * Optionally clear the search input on selection.
     * @return {void}
     */
    typeAheadSelect() {
      let items = this._computeItems(this.filteredOptions, true).concat(this._computeItems(this.filteredOptions, false));
      let match = items.find((item => {
        return item.slug == this.typeAheadPointer;
      }));
      
      if (match) {
        this.select(match);
      }
    },

    _computeItems: function(options, priority) {

      if(!options || options.length == 0) {
        return [];
      }

      if(!this.priorityProperty) {
        if(!priority) {
          return options;
        }

        return [];
      }
      var self = this;

      let filtered = options.filter(item => {
        if(!priority && (!item[self.priorityProperty] || item[self.priorityProperty].length == 0)) {
          return true;
        }

        if(priority && (item[self.priorityProperty] && item[self.priorityProperty].length > 0)) {
          return true;
        }
        
        return false;
      }).sort(function(a, b){
        
          let labelA = self.getOptionLabel(a).toLowerCase();
          let labelB = self.getOptionLabel(b).toLowerCase();

          if (labelA < labelB) {
            return -1;
          }

          if (labelA > labelB) {
            return 1;
          }

          return 0;
      });

      return filtered;

    },
  }
}
