<template>
    <div ref="item" class="vue-grid-layout" :style="mergedStyle"
        @mouseenter="_handleMouseEnter"
        @mouseleave="_handleMouseLeave">
        <slot></slot>
        <grid-item v-show="isDragging"
                   ref="placeholder"
                   class="vue-grid-placeholder"
                   :item="placeholder"
				   :multiselectable="multiselectable"
				   :grid-active="selectedGridItems && selectedGridItems.length > 0">
        </grid-item>
    </div>
</template>
<script>
    import Vue from 'vue';
    var elementResizeDetectorMaker = require("element-resize-detector");

    import {bottom, compact, compactAdv, getLayoutItem, moveElement, validateLayout, cloneLayout, getAllCollisions} from '../helpers/utils';
    import {getBreakpointFromWidth, getColsFromBreakpoint, findOrGenerateResponsiveLayout} from "../helpers/responsiveUtils";
    import GridItem from './GridItem.vue'
    import {addWindowEventListener, removeWindowEventListener} from "../helpers/DOM";

    export default {
        name: "GridLayout",
		model: {
			prop: "selectedItems",
			event: "item-selected"
		},
        provide() {
            return {
                eventBus: null
            }
        },
        components: {
            GridItem,
        },
        props: {
            // If true, the container height swells and contracts to fit contents
            autoSize: {
                type: Boolean,
                default: true
            },
            colNum: {
                type: Number,
                default: 7
            },
            rowHeight: {
                type: Number,
                default: Infinity
            },
            maxRows: {
                type: Number,
                default: Infinity
            },
            margin: {
                type: Array,
                default: function () {
                    return [10, 10];
                }
            },
            selectable: {
                type: Boolean,
                default: false
            },
			multiselectable: {
				type: Boolean,
				default: false
			},
            isDraggable: {
                type: Boolean,
                default: true
            },
            isResizable: {
                type: Boolean,
                default: true
            },
            isMirrored: {
                type: Boolean,
                default: false
            },
            layout: {
                type: Array,
                required: true,
            },
            responsive: {
                type: Boolean,
                default: false
            },
            presentationMode: {
                type: Boolean,
                default: true
            },
            breakpoints:{
                type: Object,
                default: function(){return{ lg: 1200, md: 996, sm: 768, xs: 480, xxs: 0 }}
            },
            cols:{
                type: Object,
                default: function(){return{ lg: 12, md: 10, sm: 6, xs: 4, xxs: 2 }},
            },
            preventCollision: {
                type: Boolean,
                default: false
            },
			selectedItems: {
				type: Array
			}
        },
        data: function () {
            return {
                width: null,
                mergedStyle: {},
                lastLayoutLength: 0,
                isDragging: false,
                placeholder: {
                    columnIndex: 0,
                    rowIndex: 0,
                    width: 0,
                    height: 0,
                    id: -1
                },
                layouts: {}, // array to store all layouts from different breakpoints
                lastBreakpoint: null, // store last active breakpoint
                originalLayout: null, // store original Layout
                selectedGridItem: null,
				selectedGridItems: this.selectedItems,
                reactiveLayout: this.layout,
                lastLayout: null,
            };
        },
        created () {
            const self = this;

			// Accessible refernces of functions for removing in beforeDestroy
			self.resizeEventHandler = function(eventType, i, x, y, h, w, dif) {
				if (self.multiselectable) {
					if (!self.selectedGridItems) {
						return false;
					}
					self._deselectItem(i);
				}
				else {
					if(this.selectedGridItem) {
						this.selectedGridItem.selected = false;
						this.selectedGridItem = null;
					}
				}

				self.resizeEvent(eventType, i, x, y, h, w, dif);
			};

			self.dragEventHandler = function(eventType, i, x, y, h, w, dif) {
				if (self.multiselectable) {
					if (!self.selectedGridItems) {
						return false;
					}
					self._deselectItem(i);
				}
				else {
					if(this.selectedGridItem) {
						this.selectedGridItem.selected = false;
						this.selectedGridItem = null;
					}
				}

				self.dragEvent(eventType, i, x, y, h, w, dif);
			};

			self._handleSelected = function(gridItem) {
				if (self.multiselectable) {
					if (!self.selectedGridItems) {
						return false;
					}

					let index = self.selectedGridItems.findIndex(o => o.item.id == gridItem.item.id);
					if (index == -1) {
						let newItems = self.selectedGridItems;
						newItems.push(gridItem);
						this.$emit("update", newItems);
					}
				}
				else {
					if(this.selectedGridItem && this.selectedGridItem.item.id != gridItem.item.id) {
						this.selectedGridItem.selected = false;
					}

					this.selectedGridItem = gridItem;
				}
			};

			self._handleDeselected = function(gridItem) {
				if (self.multiselectable) {
					if (!self.selectedGridItems) {
						return false;
					}
					self._deselectItem(gridItem.item.id);
				}
				else {
					if(this.selectedGridItem && this.selectedGridItem.item.id != gridItem.item.id) {
						return;
					}

					this.selectedGridItem = null;
				}
			};

            self._provided.eventBus = new Vue();
            self.eventBus = self._provided.eventBus;
            self.eventBus.$on('resizeEvent', self.resizeEventHandler);
            self.eventBus.$on('dragEvent', self.dragEventHandler);
            self.eventBus.$on('selected', self._handleSelected);
            self.eventBus.$on('deselected', self._handleDeselected);
            self.$emit('layout-created', self.reactiveLayout);
        },
        beforeDestroy: function(){
            //Remove listeners
            this.eventBus.$off('resizeEvent', this.resizeEventHandler);
            this.eventBus.$off('dragEvent', this.dragEventHandler);
			this.eventBus.$destroy();
            removeWindowEventListener("resize", this.onWindowResize);
			this.erd.uninstall(this.$refs.item);
        },
        beforeMount: function() {
            this.$emit('layout-before-mount', this.reactiveLayout);
        },
        mounted: function() {
            this.$emit('layout-mounted', this.reactiveLayout);
            this.$nextTick(function () {
                validateLayout(this.reactiveLayout);

                this.originalLayout = this.layout;
                const self = this;
                this.$nextTick(function() {
                    self.onWindowResize();

                    self.initResponsiveFeatures();

                    //self.width = self.$el.offsetWidth;
                    addWindowEventListener('resize', self.onWindowResize);

                    this._computeCompact(self.reactiveLayout);

                    self.updateHeight();
                    self.$nextTick(function () {
                        this.erd = elementResizeDetectorMaker({
                            strategy: "scroll", //<- For ultra performance.
                            // See https://github.com/wnr/element-resize-detector/issues/110 about callOnAdd.
                            callOnAdd: false,
                        });
                        this.erd.listenTo(self.$refs.item, function () {
                            self.onWindowResize();
                        });
                    });
                });
            });
        },
        watch: {
            width: function (newval, oldval) {
                const self = this;
                this.$nextTick(function () {
                    //this.$broadcast("updateWidth", this.width);
                    this.eventBus.$emit("updateWidth", this.width);
                    if (oldval === null) {
                        /*
                            If oldval == null is when the width has never been
                            set before. That only occurs when mouting is
                            finished, and onWindowResize has been called and
                            this.width has been changed the first time after it
                            got set to null in the constructor. It is now time
                            to issue layout-ready events as the GridItems have
                            their sizes configured properly.

                            The reason for emitting the layout-ready events on
                            the next tick is to allow for the newly-emitted
                            updateWidth event (above) to have reached the
                            children GridItem-s and had their effect, so we're
                            sure that they have the final size before we emit
                            layout-ready (for this GridLayout) and
                            item-layout-ready (for the GridItem-s).

                            This way any client event handlers can reliably
                            invistigate stable sizes of GridItem-s.
                        */
                        this.$nextTick(() => {
                            this.$emit('layout-ready', self.reactiveLayout);
                        });
                    }
                    this.updateHeight();
                });
            },
            layout: function (newVal, oldVal) {
                this.reactiveLayout = newVal;

                // speed up processing when editing on admin screen
                if(!this.presentationMode && oldVal && oldVal.length > 0) {
                    this.updateHeight();
                    return;
                }

                if(this._computeCompact(this.reactiveLayout)) {
                    return;
                }
                this.layoutUpdate();
            },
            colNum: function (val) {
                this.eventBus.$emit("setColNum", val);
            },
            rowHeight: function() {
                this.eventBus.$emit("setRowHeight", this.rowHeight);
            },
            isDraggable: function() {
                this.eventBus.$emit("setDraggable", this.isDraggable);
            },
            isResizable: function() {
                this.eventBus.$emit("setResizable", this.isResizable);
            },
            responsive() {
                if (!this.responsive) {
                    this.$emit('update:layout', this.originalLayout);
                    this.eventBus.$emit("setColNum", this.colNum);
                }
                this.onWindowResize();
            },
            maxRows: function() {
                this.eventBus.$emit("setMaxRows", this.maxRows);
            },
			selectedItems: function(newVal) {
				this.selectedGridItems = newVal;
			}
        },
        methods: {
			_deselectItem: function(id) {
				let index = this.selectedGridItems.findIndex(o => o.item.id == id);
				if (index != -1) {
					let newItems = this.selectedGridItems;
					newItems.splice(index, 1);
					this.$emit("update", newItems);
				}
			},
            _computeCompact: function(layout) {

                let newLayout = compactAdv(this.colNum, layout);
                if(JSON.stringify(newLayout) != JSON.stringify(layout)) {
                    this.$emit('layout-changed', newLayout);
                    return true;
                }

                return false;

            },

            _handleMouseEnter: function(e) {
                this.$emit("mouseenter", e);
            },

            _handleMouseLeave: function(e) {
                this.$emit("mouseleave", e);
            },

            layoutUpdate() {
                if (this.reactiveLayout !== undefined && this.originalLayout !== null) {
                    if (this.reactiveLayout.length !== this.originalLayout.length) {
                        //console.log("### LAYOUT UPDATE!", this.reactiveLayout.length, this.originalLayout.length);

                        let diff = this.findDifference(this.reactiveLayout, this.originalLayout);
                        if (diff.length > 0){
                            // console.log(diff);
                            if (this.reactiveLayout.length > this.originalLayout.length) {
                                this.originalLayout = this.originalLayout.concat(diff);
                            } else {
                                this.originalLayout = this.originalLayout.filter(obj => {
                                    return !diff.some(obj2 => {
                                        return obj.id === obj2.id;
                                    });
                                });
                            }
                        }

                        this.lastLayoutLength = this.reactiveLayout.length;
                        this.initResponsiveFeatures();
                    }

                    compact(this.colNum, this.reactiveLayout);
                    this.eventBus.$emit("updateWidth", this.width);
                    this.updateHeight();
                }
            },
            updateHeight: function () {
                setTimeout(function() {
                        this.mergedStyle = {
                            height: this.containerHeight()
                        };
                }.bind(this), 100);
            },
            onWindowResize: function () {
                if (this.$refs !== null && this.$refs.item !== null && this.$refs.item !== undefined) {
                    this.width = this.$refs.item.offsetWidth;
                }
                this.eventBus.$emit("resizeEvent");
            },
            containerHeight: function () {
                if (!this.autoSize) {
                    return;
                }

                let defaultHeight = this.rowHeight;
                if(defaultHeight == Infinity) {
                    defaultHeight = this.$refs.placeholder.calcColWidth();
                }
                return bottom(this.reactiveLayout) * (defaultHeight + this.margin[1]) + this.margin[1] + 'px';
            },
            dragEvent: function (eventName, id, columnIndex, rowIndex, height, width, dif) {

                if(eventName === "dragstart") {
                    this.lastLayout = JSON.parse(JSON.stringify(this.reactiveLayout));
                } else if(eventName == "dragend") {
                    this.lastLayout = null;
                } else if(eventName === "dragmove" && dif && this.lastLayout) {
                    this.reactiveLayout = JSON.parse(JSON.stringify(this.lastLayout));
                    this.$emit('layout-changed', this.reactiveLayout);
                }

                //console.log(eventName + " id=" + id + ", x=" + columnIndex + ", y=" + rowIndex);
                let l = getLayoutItem(this.reactiveLayout, id);
                //GetLayoutItem sometimes returns null object
                if (l === undefined || l === null){
                    l = {columnIndex:0, rowIndex:0, width: 1, height: 1}
                }

                if (eventName === "dragmove" || eventName === "dragstart") {
                    let newPlaceholder = {};
                    newPlaceholder.id = id;
                    newPlaceholder.columnIndex = l.columnIndex;
                    newPlaceholder.rowIndex = l.rowIndex;
                    newPlaceholder.width = width;
                    newPlaceholder.height = height;
                    this.placeholder = newPlaceholder;

                    this.$nextTick(function() {
                        this.isDragging = true;
                    });
                    //this.$broadcast("updateWidth", this.width);
                    this.eventBus.$emit("updateWidth", this.width);
                } else {
                    this.$nextTick(function() {
                        this.isDragging = false;
                    });
                }

                if(dif) {
                    // Move the element to the dragged location.
                    this.reactiveLayout = moveElement(this.colNum, this.reactiveLayout, l, columnIndex, rowIndex, true, this.preventCollision);
                    //console.log(`columnIndex: ${columnIndex} | rowIndex: ${rowIndex}`);
                    compact(this.colNum, this.reactiveLayout);
                    this.updateHeight();

                    // Async update, so update is after other ui manipulation.
                    setTimeout(function() {
                        this.eventBus.$emit("compact");
                    }.bind(this), 10);
                } else {
                    // needed because vue can't detect changes on array element properties and updates dragged item
                    this.eventBus.$emit("compact");
                }

                if (eventName === 'dragend') {
                    this.$emit('layout-changed', this.reactiveLayout);
                }
            },
            resizeEvent: function (eventName, id, columnIndex, rowIndex, height, width, dif) {

                if(eventName === "resizestart") {
                    this.lastLayout = JSON.parse(JSON.stringify(this.reactiveLayout));
                } else if(eventName == "resizeend") {
                    this.lastLayout = null;
                } else if(eventName === "resizemove" && dif && this.lastLayout) {
                    this.reactiveLayout = JSON.parse(JSON.stringify(this.lastLayout));
                    this.$emit('layout-changed', this.reactiveLayout);
                }

                let l = getLayoutItem(this.reactiveLayout, id);
                //GetLayoutItem sometimes return null object
                if (l === undefined || l === null){
                    l = {height:0, width:0}
                }

                let hasCollisions;
                if (this.preventCollision) {
                    const collisions = getAllCollisions(this.reactiveLayout, { ...l, width, height }).filter(
                        layoutItem => layoutItem.id !== l.id
                    );
                    hasCollisions = collisions.length > 0;

                    // If we're colliding, we need adjust the placeholder.
                    if (hasCollisions) {
                        // adjust width && height to maximum allowed space
                        let leastX = Infinity,
                        leastY = Infinity;
                        collisions.forEach(layoutItem => {
                        if (layoutItem.columnIndex > l.columnIndex) leastX = Math.min(leastX, layoutItem.columnIndex);
                        if (layoutItem.rowIndex > l.rowIndex) leastY = Math.min(leastY, layoutItem.rowIndex);
                        });

                        if (Number.isFinite(leastX)) l.width = leastX - l.columnIndex;
                        if (Number.isFinite(leastY)) l.height = leastY - l.rowIndex;
                    }
                }

                if (!hasCollisions) {
                    // Set new width and height.
                    l.width = width;
                    l.height = height;
                }

                if (eventName === "resizestart" || eventName === "resizemove") {

                    let newPlaceholder = {};
                    newPlaceholder.id = id;
                    newPlaceholder.columnIndex = columnIndex;
                    newPlaceholder.rowIndex = rowIndex;
                    newPlaceholder.width = l.width;
                    newPlaceholder.height = l.height;
                    this.placeholder = newPlaceholder;

                    this.$nextTick(function() {
                        this.isDragging = true;
                    });
                    //this.$broadcast("updateWidth", this.width);
                    this.eventBus.$emit("updateWidth", this.width);

                } else {
                    this.$nextTick(function() {
                        this.isDragging = false;
                    });
                }

                if (this.responsive) this.responsiveGridLayout();

                if(dif) {
                    compact(this.colNum, this.reactiveLayout);
                    this.updateHeight();
                    // Async update, so update is after other ui manipulation.
                    setTimeout(function() {
                        this.eventBus.$emit("compact");
                    }.bind(this), 10);
                } else {
                    // needed because vue can't detect changes on array element properties and updates dragged item
                    this.eventBus.$emit("compact");
                }

                if (eventName === 'resizeend') {
                    this.$emit('layout-changed', this.reactiveLayout);
                }
            },

            // finds or generates new layouts for set breakpoints
            responsiveGridLayout(){

                let newBreakpoint = getBreakpointFromWidth(this.breakpoints, this.width);
                let newCols = getColsFromBreakpoint(newBreakpoint, this.cols);

                // save actual layout in layouts
                if(this.lastBreakpoint != null && !this.layouts[this.lastBreakpoint])
                    this.layouts[this.lastBreakpoint] = cloneLayout(this.reactiveLayout);

                // Find or generate a new layout.
                let layout = findOrGenerateResponsiveLayout(
                    this.originalLayout,
                    this.layouts,
                    this.breakpoints,
                    newBreakpoint,
                    this.lastBreakpoint,
                    newCols
                );

                // Store the new layout.
                this.layouts[newBreakpoint] = layout;

                // new prop sync
                this.$emit('update:layout', layout);

                this.lastBreakpoint = newBreakpoint;
                this.eventBus.$emit("setColNum", getColsFromBreakpoint(newBreakpoint, this.cols));
            },

            // clear all responsive layouts
            initResponsiveFeatures(){
                // clear layouts
                this.layouts = {};
            },

            // find difference in layouts
            findDifference(layout, originalLayout){

                //Find values that are in result1 but not in result2
                let uniqueResultOne = layout.filter(function(obj) {
                    return !originalLayout.some(function(obj2) {
                        return obj.id === obj2.id;
                    });
                });

                //Find values that are in result2 but not in result1
                let uniqueResultTwo = originalLayout.filter(function(obj) {
                    return !layout.some(function(obj2) {
                        return obj.id === obj2.id;
                    });
                });

                //Combine the two arrays of unique entries#
                return uniqueResultOne.concat(uniqueResultTwo);
            }
        },
    }
</script>
<style>
    .vue-grid-layout {
        position: relative;
        transition: height 200ms ease;
    }

</style>