<template>
    <div ref="item"
         class="vue-grid-item"
         :class="[classObj, selected ? 'selected' : '', presentationMode ? 'presentation' : '', multiselectable ? 'multiselected' : '', gridActive && !selected ? 'unselected' : '' ]"
         :style="(multiselectable && selected ? multiselectedStyle : style)"
         @click="_handleItemClicked"
         @mouseenter="_handleMouseEnter"
         @mouseleave="_handleMouseLeave">
        <slot></slot>

        <div v-if="selectable" class="selected-section">
            <slot name="selected"></slot>
        </div>
        <span v-if="resizable" ref="handle" class="vue-resizable-handle"></span>
    </div>
</template>
<script>
    import {setTopLeft, setTopRight, setTransform} from '../helpers/utils';
    import {getControlPosition, createCoreData} from '../helpers/draggableUtils';
    import {getDocumentDir} from "../helpers/DOM";

    let interact = require("interactjs");

    export default {
        name: "GridItem",
        props: {
            item: {
                type: Object,
                required: true,
            },
			multiselectable: {
				type: Boolean,
				default: false
			},
			gridActive: {
				type: Boolean,
				default: false
			},
			selectedGame: {
				type: Object,
				default: null
			}
        },
        inject: ["eventBus"],
        data: function () {
            return {
                cols: 1,
                containerWidth: 100,
                rowHeight: 30,
                margin: [10, 10],
                maxRows: Infinity,
                draggable: null,
                resizable: null,

                isDragging: false,
                dragging: null,
                isResizing: false,
                resizing: null,
                lastX: NaN,
                lastY: NaN,
                lastW: NaN,
                lastH: NaN,
                style: {},
				multiselectedStyle: {},

                dragEventSet: false,
                resizeEventSet: false,

                previousW: null,
                previousH: null,
                previousX: null,
                previousY: null,
                innerX: this.item?.columnIndex ?? 0,
                innerY: this.item?.rowIndex ?? 0,
                innerW: this.item?.width ?? 1,
                innerH: this.item?.height ?? 1,
                selected: false,
                selectable: false,
                selectDisabled: false,
                presentationMode: false,
                hover: false,

                lastInteractivePos: null,
            }
        },
        created () {
            let self = this;

            // Accessible refernces of functions for removing in beforeDestroy
            self.updateWidthHandler = function (width) {
                self.updateWidth(width);
            };

            self.compactHandler = function (layout) {
                self.compact(layout);
            };

             self.setDraggableHandler = function (draggable) {
                 self.draggable = draggable;
            };

            self.setResizableHandler = function (resizable) {
                self.resizable = resizable;
            };

            self.setRowHeightHandler = function (rowHeight) {
                self.rowHeight = rowHeight;
            };

            self.setMaxRowsHandler = function (maxRows) {
                self.maxRows = maxRows;
            };

            self.setColNum = (colNum) => {
               self.cols = parseInt(colNum);
            }

            this.eventBus.$on('updateWidth', self.updateWidthHandler);
            this.eventBus.$on('compact', self.compactHandler);
            this.eventBus.$on('setDraggable', self.setDraggableHandler);
            this.eventBus.$on('setResizable', self.setResizableHandler);
            this.eventBus.$on('setRowHeight', self.setRowHeightHandler);
            this.eventBus.$on('setMaxRows', self.setMaxRowsHandler);
            this.eventBus.$on('directionchange', self.directionchangeHandler);
            this.eventBus.$on('setColNum', self.setColNum)

        },
        beforeDestroy: function(){
            let self = this;
            //Remove listeners
            this.eventBus.$off('updateWidth', self.updateWidthHandler);
            this.eventBus.$off('compact', self.compactHandler);
            this.eventBus.$off('setDraggable', self.setDraggableHandler);
            this.eventBus.$off('setResizable', self.setResizableHandler);
            this.eventBus.$off('setRowHeight', self.setRowHeightHandler);
            this.eventBus.$off('setMaxRows', self.setMaxRowsHandler);
            this.eventBus.$off('directionchange', self.directionchangeHandler);
            this.eventBus.$off('setColNum', self.setColNum);
            this.interactObj.unset() // destroy interact intance
        },
        mounted: function () {
            this.compatibleItem = this.item;
            if(this.compatibleItem ) {
                if(!this.compatibleItem.columnIndex) {
                    this.compatibleItem.columnIndex = 0;
                }

                if(!this.compatibleItem.rowIndex) {
                    this.compatibleItem.rowIndex = 0;
                }

                if(!this.compatibleItem.height) {
                    this.compatibleItem.height = 1;
                }

                if(!this.compatibleItem.width) {
                    this.compatibleItem.width = 1;
                }
            }

            this.cols = this.$parent.colNum;
            this.rowHeight = this.$parent.rowHeight;
            this.containerWidth = this.$parent.width !== null ? this.$parent.width : 100;
            this.margin = this.$parent.margin !== undefined ? this.$parent.margin : [10, 10];
            this.selectable = this.$parent.selectable;
            this.maxRows = this.$parent.maxRows;
            this.draggable = this.$parent.isDraggable;
            this.resizable = this.$parent.isResizable;
            this.presentationMode = this.$parent.presentationMode;
            this.createStyle();
        },
        watch: {
            draggable: function () {
                this.tryMakeDraggable();
            },
            resizable: function () {
                this.tryMakeResizable();
            },
            rowHeight: function () {
                this.createStyle();
                this.emitContainerResized();
            },
            cols: function () {
                this.tryMakeResizable();
                this.createStyle();
                this.emitContainerResized();
            },
            containerWidth: function () {
                this.tryMakeResizable();
                this.createStyle();
                this.emitContainerResized();
            },
            hover: function(newVal) {
                this.hover = newVal;
                this.createStyle();
            },
            item: function(newVal) {
                this.innerX = newVal?.columnIndex ?? 0;
                this.innerY = newVal?.rowIndex ?? 0;
                this.innerH = newVal?.height ?? 1;
                this.innerW = newVal?.width ?? 1;

                this.compatibleItem = newVal;
                this.createStyle();
            },
            'item.columnIndex': function (newVal) {
                this.innerX = newVal ?? 0;
                this.createStyle();
            },
            'item.rowIndex': function (newVal) {
                this.innerY = newVal ?? 0;
                this.createStyle();
            },
            'item.height': function (newVal) {
                this.innerH = newVal ?? 1
                this.createStyle();
            },
            'item.width': function (newVal) {
                this.innerW = newVal ?? 1;
                this.createStyle();
            },

            selected: function(newVal) {
                 if(newVal) {
                    this.eventBus.$emit("selected", this);
                 } else {
                    this.eventBus.$emit("deselected", this);
                 }
            },

			selectedGame: function(newVal) {
				if (!newVal) {
					return;
				}

				if (newVal.id == this.item.id) {
					if(this.selectDisabled || this.selected) {
						return;
					}

					if(this.selectable) {
						this.selected = !this.selected;
					}
				}
			}
        },
        computed: {
            classObj() {
                return {
                    'vue-resizable' : this.resizable,
                    'resizing' : this.isResizing,
                    'vue-draggable-dragging' : this.isDragging,
                    'disable-userselect': this.isDragging,
                    'no-touch': this.isAndroid && this.draggableOrResizable
                }
            },
            draggableOrResizable(){
                return (this.draggable || this.resizable);
            },
            isAndroid() {
                return navigator.userAgent.toLowerCase().indexOf("android") !== -1;
            }
        },
        methods: {

            _handleMouseEnter: function(e) {
                if(!this.presentationMode) {
                    this.hover = false;
                    return;
                }

                this.hover = true;
            },

            _handleMouseLeave: function(e) {
                 this.hover = false;
            },

            _handleItemClicked: function(e) {
                if(this.selectDisabled) {
                    return;
                }

                if(this.selectable) {
                    this.selected = !this.selected;
                }

                this.$emit("click", e);
            },

            createStyle: function () {
                if (this.compatibleItem.columnIndex + this.compatibleItem.width > this.cols) {
                    this.innerX = 0;
                    this.innerW = (this.compatibleItem.width > this.cols) ? this.cols : this.compatibleItem.width
                } else {
                  this.innerX = this.compatibleItem.columnIndex;
                  this.innerW = this.compatibleItem.width;
                }
                let pos = this.calcPosition(this.innerX, this.innerY, this.innerW, this.innerH);


                if (this.isDragging) {
                    pos.top = this.dragging.top;
                    pos.left = this.dragging.left;
                }
                if (this.isResizing) {
                    pos.width = this.resizing.width;
                    pos.height = this.resizing.height;
                }

                let style;
                if(!this.presentationMode) {
                    this.hover = false;
                }

                let scale = null;
                if(this.hover) {
                    if(this.compatibleItem.width > 1 && this.compatibleItem.height > 1) {
                        scale = 1.1;
                    } else {
                        scale = 1.2;
                    }
                }

                style = setTransform(scale, pos.top, pos.left, pos.width, pos.height);
                this.style = style;

                let multiselectedStyle = setTransform(scale, pos.top-3, pos.left-3, pos.width+6, pos.height+6);
                this.multiselectedStyle = multiselectedStyle;
            },
            emitContainerResized() {
                // this.style has width and height with trailing 'px'. The
                // resized event is without them
                let styleProps = {};
                for (let prop of ['width', 'height']) {
                    let val = this.style[prop];
                    let matches = val.match(/^(\d+)px$/);
                    if (! matches)
                        return;
                    styleProps[prop] = matches[1];
                }
                this.$emit("container-resized", this.compatibleItem.id, this.compatibleItem.height, this.compatibleItem.width, styleProps.height, styleProps.width);
            },
            handleResize: function (event) {
                const position = getControlPosition(event);
                // Get the current drag point from the event. This is used as the offset.
                if (position == null) return; // not possible but satisfies flow
                const {x, y} = position;

                const newSize = {width: 0, height: 0};
                let pos;
                switch (event.type) {
                    case "resizestart": {
                        this.previousW = this.innerW;
                        this.previousH = this.innerH;
                        pos = this.calcPosition(this.innerX, this.innerY, this.innerW, this.innerH);
                        newSize.width = pos.width;
                        newSize.height = pos.height;
                        this.resizing = newSize;
                        this.isResizing = true;
                        this.selected = false;
                        this.selectDisabled = true;
                        break;
                    }
                    case "resizemove": {
                        //console.log("### resize => " + event.type + ", lastW=" + this.lastW + ", lastH=" + this.lastH);
                        const coreEvent = createCoreData(this.lastW, this.lastH, x, y);
                        newSize.width = this.resizing.width + coreEvent.deltaX;
                        newSize.height = this.resizing.height + coreEvent.deltaY;

                        //console.log("### resize => " + event.type + ", deltaX=" + coreEvent.deltaX + ", deltaY=" + coreEvent.deltaY);
                        this.resizing = newSize;
                        break;
                    }
                    case "resizeend": {
                        //console.log("### resize end => x=" +this.innerX + " y=" + this.innerY + " w=" + this.innerW + " h=" + this.innerH);
                        pos = this.calcPosition(this.innerX, this.innerY, this.innerW, this.innerH);
                        newSize.width = pos.width;
                        newSize.height = pos.height;
//                        console.log("### resize end => " + JSON.stringify(newSize));
                        this.resizing = null;
                        this.isResizing = false;
                        setTimeout(() => {
                            this.selectDisabled = false;
                        },100);

                        break;
                    }
                }

                // Get new WH
                pos = this.calcWH(newSize.height, newSize.width);

                if (pos.h < 1) {
                    pos.h = 1;
                }
                if (pos.w < 1) {
                    pos.w = 1;
                }

                this.lastW = x;
                this.lastH = y;

                if (this.innerW !== pos.w || this.innerH !== pos.h) {
                    this.$emit("resize", this.compatibleItem.id, pos.h, pos.w, newSize.height, newSize.width);
                }
                if (event.type === "resizeend" && (this.previousW !== this.innerW || this.previousH !== this.innerH)) {
                    this.$emit("resized", this.compatibleItem.id, pos.h, pos.w, newSize.height, newSize.width);
                }

                let difference = this.lastInteractivePos && (this.lastInteractivePos.h != pos.h || this.lastInteractivePos.w != pos.w);
                this.eventBus.$emit("resizeEvent", event.type, this.compatibleItem.id, this.innerX, this.innerY, pos.h, pos.w, difference);

                if(event.type === "resizeend") {
                    this.lastInteractivePos = null;
                } else {
                    this.lastInteractivePos = JSON.parse(JSON.stringify(pos));
                }

            },
            handleDrag(event) {

                if (this.isResizing) return;

                const position = getControlPosition(event);

                // Get the current drag point from the event. This is used as the offset.
                if (position === null) return; // not possible but satisfies flow
                const {x, y} = position;

                // let shouldUpdate = false;
                let newPosition = {top: 0, left: 0};
                switch (event.type) {
                    case "dragstart": {
                        this.previousX = this.innerX;
                        this.previousY = this.innerY;

                        let parentRect = event.target.offsetParent.getBoundingClientRect();
                        let clientRect = event.target.getBoundingClientRect();
                        newPosition.left = clientRect.left - parentRect.left;
                        newPosition.top = clientRect.top - parentRect.top;
                        this.dragging = newPosition;
                        this.isDragging = true;
                        this.selected = false;
                        this.selectDisabled = true;
                        break;
                    }
                    case "dragend": {
                        if (!this.isDragging) return;
                        let parentRect = event.target.offsetParent.getBoundingClientRect();
                        let clientRect = event.target.getBoundingClientRect();
                        newPosition.left = clientRect.left - parentRect.left;
                        newPosition.top = clientRect.top - parentRect.top;
//                        console.log("### drag end => " + JSON.stringify(newPosition));
//                        console.log("### DROP: " + JSON.stringify(newPosition));
                        this.dragging = null;
                        this.isDragging = false;
                        // shouldUpdate = true;
                        setTimeout(() => {
                            this.selectDisabled = false;
                        },100);
                        break;
                    }
                    case "dragmove": {
                        const coreEvent = createCoreData(this.lastX, this.lastY, x, y);
                        newPosition.left = this.dragging.left + coreEvent.deltaX;
                        newPosition.top = this.dragging.top + coreEvent.deltaY;
//                        console.log("### drag => " + event.type + ", x=" + x + ", y=" + y);
//                        console.log("### drag => " + event.type + ", deltaX=" + coreEvent.deltaX + ", deltaY=" + coreEvent.deltaY);
                     //   console.log("### drag end => " + JSON.stringify(newPosition));
                        this.dragging = newPosition;
                        break;
                    }
                }

                // Get new XY
                let pos = this.calcXY(newPosition.top, newPosition.left);

                this.lastX = x;
                this.lastY = y;

                if (this.innerX !== pos.x || this.innerY !== pos.y) {
                    this.$emit("move", this.compatibleItem.id, pos.x, pos.y);
                }
                if (event.type === "dragend" && (this.previousX !== this.innerX || this.previousY !== this.innerY)) {
                    this.$emit("moved", this.compatibleItem.id, pos.x, pos.y);
                }

                let difference = this.lastInteractivePos && (this.lastInteractivePos.x != pos.x || this.lastInteractivePos.y != pos.y);
                this.eventBus.$emit("dragEvent", event.type, this.compatibleItem.id, pos.x, pos.y, this.innerH, this.innerW, difference);

                if(event.type === "dragend") {
                    this.lastInteractivePos = null;
                } else {
                    this.lastInteractivePos = JSON.parse(JSON.stringify(pos));
                }
            },
            calcPosition: function (x, y, w, h) {
                const colWidth = this.calcColWidth();
                let out = {
                        left: Math.round(colWidth * x + (x + 1) * this.margin[0]),
                        top: Math.round(this._getRowHeight(colWidth) * y + (y + 1) * this.margin[1]),
                        // 0 * Infinity === NaN, which causes problems with resize constriants;
                        // Fix this if it occurs.
                        // Note we do it here rather than later because Math.round(Infinity) causes deopt
                        width: w === Infinity ? w : Math.round(colWidth * w + Math.max(0, w - 1) * this.margin[0]),
                        height: h === Infinity ? h : Math.round(this._getRowHeight(colWidth) * h + Math.max(0, h - 1) * this.margin[1])
                    };

                return out;
            },

            _getRowHeight: function(fallback) {
                if(this.rowHeight === Infinity) {
                    return fallback;
                }

                return this.rowHeight
            },
            /**
             * Translate x and y coordinates from pixels to grid units.
             * @param  {Number} top  Top position (relative to parent) in pixels.
             * @param  {Number} left Left position (relative to parent) in pixels.
             * @return {Object} x and y in grid units.
             */
            calcXY(top, left) {
                const colWidth = this.calcColWidth();

                // left = colWidth * x + margin * (x + 1)
                // l = cx + m(x+1)
                // l = cx + mx + m
                // l - m = cx + mx
                // l - m = x(c + m)
                // (l - m) / (c + m) = x
                // x = (left - margin) / (coldWidth + margin)
                let x = Math.round((left - this.margin[0]) / (colWidth + this.margin[0]));
                let y = Math.round((top - this.margin[1]) / (this._getRowHeight(colWidth) + this.margin[1]));

                // Capping
                x = Math.max(Math.min(x, (this.cols+1) - this.innerW), 0);
                y = Math.max(Math.min(y, this.maxRows - this.innerH), 0);

                return {x, y};
            },
            // Helper for generating column width
            calcColWidth() {
                const colWidth = (this.containerWidth - (this.margin[0] * (this.cols + 1))) / this.cols;
               // console.log("### COLS=" + this.cols + " COL WIDTH=" + colWidth + " MARGIN " + this.margin[0]);
                return colWidth;
            },

            /**
             * Given a height and width in pixel values, calculate grid units.
             * @param  {Number} height Height in pixels.
             * @param  {Number} width  Width in pixels.
             * @return {Object} w, h as grid units.
             */
            calcWH(height, width) {
                const colWidth = this.calcColWidth();

                // width = colWidth * w - (margin * (w - 1))
                // ...
                // w = (width + margin) / (colWidth + margin)
                let w = Math.round((width + this.margin[0]) / (colWidth + this.margin[0]));
                let h = Math.round((height + this.margin[1]) / (this._getRowHeight(colWidth) + this.margin[1]));

                // Capping
                w = Math.max(Math.min(w, this.cols - this.innerX), 0);
                h = Math.max(Math.min(h, this.maxRows - this.innerY), 0);
                return {w, h};
            },
            updateWidth: function (width, colNum) {
                this.containerWidth = width;
                if (colNum !== undefined && colNum !== null) {
                    this.cols = colNum;
                }
            },
            compact: function () {
                this.createStyle();
            },
            tryMakeDraggable: function(){
                const self = this;
                if (this.interactObj === null || this.interactObj === undefined) {
                    this.interactObj = interact(this.$refs.item);
                }
                if (this.draggable) {
                     const opts = {
                        ignoreFrom: 'a, button',
                        allowFrom: null
                    };

                    this.interactObj.draggable(opts);
                    if (!this.dragEventSet) {
                        this.dragEventSet = true;
                        this.interactObj.on('dragstart dragmove dragend', function (event) {
                            self.handleDrag(event);
                        });
                    }
                } else {
                    this.interactObj.draggable({
                        enabled: false
                    });
                }
            },
            tryMakeResizable: function(){
                const self = this;
                if (this.interactObj === null || this.interactObj === undefined) {
                    this.interactObj = interact(this.$refs.item);
                }
                if (this.resizable) {
                    let maximum = this.calcPosition(0,0,Infinity,Infinity);
                    let minimum = this.calcPosition(0,0,1,1);

                    // console.log("### MAX " + JSON.stringify(maximum));
                    // console.log("### MIN " + JSON.stringify(minimum));

                    const opts = {
                        preserveAspectRatio: true,
                        edges: {
                            left: false,
                            right: ".vue-resizable-handle",
                            bottom: ".vue-resizable-handle",
                            top: false
                        },
                        ignoreFrom: 'a, button',
                        restrictSize: {
                            min: {
                                height: minimum.height,
                                width: minimum.width
                            },
                            max: {
                                height: maximum.height,
                                width: maximum.width
                            }
                        }
                    };

                    this.interactObj.resizable(opts);
                    if (!this.resizeEventSet) {
                        this.resizeEventSet = true;
                        this.interactObj
                            .on('resizestart resizemove resizeend', function (event) {
                                self.handleResize(event);
                            });
                    }
                } else {
                    this.interactObj.resizable({
                        enabled: false
                    });
                }
            },
            autoSize: function() {
                // ok here we want to calculate if a resize is needed
                this.previousW = this.innerW;
                this.previousH = this.innerH;

                let newSize=this.$slots.default[0].elm.getBoundingClientRect();
                let pos = this.calcWH(newSize.height, newSize.width);

                if (pos.h < 1) {
                    pos.h = 1;
                }
                if (pos.w < 1) {
                    pos.w = 1;
                }

                // this.lastW = x; // basically, this is copied from resizehandler, but shouldn't be needed
                // this.lastH = y;

                if (this.innerW !== pos.w || this.innerH !== pos.h) {
                    this.$emit("resize", this.compatibleItem.id, pos.h, pos.w, newSize.height, newSize.width);
                }
                if (this.previousW !== pos.w || this.previousH !== pos.h) {
                    this.$emit("resized", this.compatibleItem.id, pos.h, pos.w, newSize.height, newSize.width);
                    this.eventBus.$emit("resizeEvent", "resizeend", this.compatibleItem.id, this.innerX, this.innerY, pos.h, pos.w);
                }
            }
        },
    }
</script>
<style>

    .vue-grid-item.selected {
        margin-top: -25px;
        z-index: 2;
    }

	.vue-grid-item.multiselected {
		-webkit-transition: opacity 2s;
		-moz-transition: opacity 2s;
		-o-transition: opacity 2s;
		transition: opacity 2s;
	}

	.vue-grid-item.multiselected.selected {
		margin-top: 0px;
		box-shadow: 0px 0px 4px 3px black;
	}

	.vue-grid-item.multiselected.selected .img-thumbnail {
		background-color: yellow;
	}

	.vue-grid-item.multiselected.unselected {
		opacity: 0.8;
	}


    .vue-grid-item .selected-section  {
        opacity: 0;
        visibility: hidden;
        position: absolute;
        bottom: 0px;
        left: 50%;
        transform: translateX(-50%);
        min-width: 76px;
    }

     .vue-grid-item .selected-section a {
        transition: none;
    }

    .vue-grid-item.selected .selected-section {
         transition: opacity 200ms ease-in-out, bottom 250ms ease-in-out;
        z-index: 3;
        visibility: visible;
        opacity: 1;
        bottom: -39px;
    }

    .vue-grid-item {
        transition: left 100ms ease, top 100ms ease, right 100ms ease, margin 100ms ease;
        z-index: 1;
        display: inline-flex;
        transition-property: transform, left, top, right, margin-top;
        left: 0;
        right: auto;
        touch-action: none
    }

    .vue-grid-item.presentation {
        transition-duration: 500ms;
    }

     .vue-grid-item:hover {
        transition: all .5s cubic-bezier(0.42, 0, 0.09, 1.07);
     }

    .vue-grid-item > img {
        width: 100%;
    }

    .vue-grid-item.no-touch {
        -ms-touch-action: none;
        touch-action: none;
    }

    .vue-grid-item.resizing {
        opacity: 0.6;
        z-index: 3;
    }

    .vue-grid-item.vue-draggable-dragging {
        transition:none;
        z-index: 3;
    }

    .vue-grid-item.vue-grid-placeholder {
        background: var(--primary-color);
        opacity: 0.4;
        transition-duration: 100ms;
        z-index: 0;
        margin: 5px;
        border-radius: 16px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        -o-user-select: none;
        user-select: none;
    }

    .vue-grid-item:not(.vue-grid-placeholder) > .vue-resizable-handle {
        position: absolute;
        width: 20px;
        height: 20px;
        bottom: -2px;
        right: -2px;
        clip-path: polygon(100% 0,100% 100%,0 100%);
        background: var(--primary-color);
        background-position: bottom right;
        padding: 0 3px 3px 0;
        background-repeat: no-repeat;
        background-origin: content-box;
        box-sizing: border-box;
        cursor: se-resize;
    }

    .vue-grid-item.vue-grid-placeholder > .vue-resizable-handle {
        display: none;
    }

    @media only screen and (max-width: 800px)
    {
        .vue-grid-item > .vue-resizable-handle {
            width: 15px;
            height: 15px;
        }
    }

    @media only screen and (max-width: 565px)
    {
       .vue-grid-item > .vue-resizable-handle {
            bottom: -1px;
            right: -1px;
        }

        .vue-grid-item.vue-grid-placeholder {
             border-radius: 8px;
        }

    }

    .vue-grid-item.disable-userselect {
        user-select: none;
    }
</style>
