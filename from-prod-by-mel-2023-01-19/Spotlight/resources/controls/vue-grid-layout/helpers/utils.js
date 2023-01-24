// @flow
/*export type LayoutItemRequired = {w, h, x, y, i};
export type LayoutItem = LayoutItemRequired &
                         {minW?, minH?, maxW?, maxH?,
                          moved?, static?,
                          isDraggable?, isResizable?};
export type Layout = Array<LayoutItem>;*/
// export type Position = {left, top, width, height};
/*
export type DragCallbackData = {
  node: HTMLElement,
  x, y,
  deltaX, deltaY,
  lastX, lastY
};
*/
// export type DragEvent = {e: Event} & DragCallbackData;
//export type Size = {width, height};
// export type ResizeEvent = {e: Event, node: HTMLElement, size: Size};

// const isProduction = process.env.NODE_ENV === 'production';
/**
 * Return the bottom coordinate of the layout.
 *
 * @param  {Array} layout Layout array.
 * @return {Number}       Bottom coordinate.
 */
export function bottom(layout) {
  let max = 0, bottomY;
  for (let i = 0, len = layout.length; i < len; i++) {
    bottomY = layout[i].rowIndex + layout[i].height;
    if (bottomY > max) max = bottomY;
  }
  return max;
}

export function cloneLayout(layout) {
  const newLayout = Array(layout.length);
  for (let i = 0, len = layout.length; i < len; i++) {
    newLayout[i] = cloneLayoutItem(layout[i]);
  }
  return newLayout;
}

// Fast path to cloning, since this is monomorphic
export function cloneLayoutItem(layoutItem) {
  /*return {
    width.width, height.height, x.columnIndex, y.rowIndex, id.id,
    minW.minW, maxW.maxW, minH.minH, maxH.maxH,
    moved(layoutItem.moved), static(layoutItem.static),
    // These can be null
    isDraggable.isDraggable, isResizable.isResizable
  };*/
    return JSON.parse(JSON.stringify(layoutItem));
}

/**
 * Given two layoutitems, check if they collide.
 *
 * @return {Boolean}   True if colliding.
 */
export function collides(l1, l2) {
  
  if (l1 === l2) {
    return false; // same element
  }

  if ((l1.columnIndex + l1.width) <= l2.columnIndex) {
    return false; // l1 is left of l2
  }

  if (l1.columnIndex >= (l2.columnIndex + l2.width)) {
    return false; // l1 is right of l2
  }

  if ((l1.rowIndex + l1.height) <= l2.rowIndex) {
    return false; // l1 is above l2
  }

  if (l1.rowIndex >= (l2.rowIndex + l2.height)) {
    return false; // l1 is below l2
  }

  return true; // boxes overlap
}

export function compactAdv(colNum, layout) {
  let lastLayout = JSON.parse(JSON.stringify(layout));
  let newLayout = [];
  let i = 0;
  while(JSON.stringify(lastLayout) != JSON.stringify(newLayout)) {
      if(newLayout.length > 0) {
          i++;   
          lastLayout = JSON.parse(JSON.stringify(newLayout));       
      }

     //Safety break/brake
      if(i > 10) {
          break;
      }
      
      newLayout = compact(colNum, JSON.parse(JSON.stringify(lastLayout)));                                           
  }
  
  if(i > 0) {                                
     return newLayout     
  } 

  return layout;
}


/**
 * Given a layout, compact it. This involves going down each y coordinate and removing gaps
 * between items.
 *
 * @param  {Array} layout Layout.
 * @param  {Boolean} verticalCompact Whether or not to compact the layout
 *   vertically.
 * @return {Array}       Compacted Layout.
 */
export function compact(colNum, layout) {
  // Statics go in the compareWith array right away so items flow around them.
  const compareWith = [];
  // We go through the items by row and column.
  const sorted = sortLayoutItemsByRowCol(layout);
  // Holding for new items.
  const out = Array(layout.length);

  // Keep track of the last smallest tile best position, meaning that no other tile could go higher - so we don't need to compute higher than cursor.
  let cursor = null;

  for (let i = 0, len = sorted.length; i < len; i++) {
    let l = sorted[i];    
    l = compactItem(colNum, compareWith, l, cursor);
    if(l.width == 1 && l.height == 1) {
      cursor = l;
    }

    // Add to comparison array. We only collide with items before this one.
    // Statics are already in this array.
    compareWith.push(l);

    // Add to output array to make sure they still come out in the right order.
    out[layout.indexOf(l)] = l;

    // Clear moved flag, if it exists.
    l.moved = false;
  }
  
  return out;
}

/**
 * Compact an item in the layout.
 */
export function compactItem(colNum, compareWith, l, cursor) {

  if((l.columnIndex + l.width) > colNum) {
    l.columnIndex = colNum - l.width;
    l.rowIndex++;
  }

  let lastPosition = {rowIndex: l.rowIndex, columnIndex: l.columnIndex};
  let collides = getFirstCollision(compareWith, l);  
  
  while(!l.static) {
    
    l.columnIndex--;

    //Out of bounds check
    if(l.columnIndex < 0 && l.rowIndex > 0) {
      l.rowIndex--;      
      l.columnIndex = colNum-l.width;
    } 
    
    // No need to process further than the cursor.
    if(cursor) {
      if(l.columnIndex < cursor.columnIndex && l.rowIndex <= cursor.rowIndex) {
        collides = true;
        break;      
      }
    }

    // Safety brake/break
    if(l.columnIndex < 0 || l.rowIndex < 0) {
      collides = true;
      break;      
    }

    collides = getFirstCollision(compareWith, l);  
    // If no collide record this as the best new position
    if(!collides) {
      lastPosition = {rowIndex: l.rowIndex, columnIndex: l.columnIndex};
    }
  }
  
  // If circling back didn't find any available spots, we need to move forwards.
  if(collides) {
    //Go back to last known position (don't need to repeat last few checks)
    l.rowIndex = lastPosition.rowIndex;
    l.columnIndex = lastPosition.columnIndex;

    // Rows have no limit, so at some point this while loop will find a spot with no collisions.
    while((collides = getFirstCollision(compareWith, l))) {
      l.columnIndex = collides.columnIndex + collides.width;
      if((l.columnIndex + l.width) > colNum) {
        l.rowIndex++;
        l.columnIndex = 0;
      }
    }
  }

  return l;
}

/**
 * Given a layout, make sure all elements fit within its bounds.
 *
 * @param  {Array} layout Layout array.
 * @param  {Number} bounds Number of columns.
 */
export function correctBounds(layout, bounds) {
  const collidesWith = [];
  for (let i = 0, len = layout.length; i < len; i++) {
    const l = layout[i];
    // Overflows right
    if (l.columnIndex + l.width > bounds.cols) l.columnIndex = bounds.cols - l.width;
    // Overflows left
    if (l.columnIndex < 0) {
      l.columnIndex = 0;
      l.width = bounds.cols;
    }
    collidesWith.push(l);
  }
  return layout;
}

/**
 * Get a layout item by ID. Used so we can override later on if necessary.
 *
 * @param  {Array}  layout Layout array.
 * @param  {String} id     ID
 * @return {LayoutItem}    Item at ID.
 */
export function getLayoutItem(layout, id) {
  for (let i = 0, len = layout.length; i < len; i++) {
    if (layout[i].id === id) return layout[i];
  }
}

/**
 * Returns the first item this layout collides with.
 * It doesn't appear to matter which order we approach this from, although
 * perhaps that is the wrong thing to do.
 *
 * @param  {Object} layoutItem Layout item.
 * @return {Object|undefined}  A colliding layout item, or undefined.
 */
export function getFirstCollision(layout, layoutItem) {
  for (let i = 0, len = layout.length; i < len; i++) {
    if (collides(layout[i], layoutItem)) {
      return layout[i];
    }
  }
}

export function getAllCollisions(layout, layoutItem) {
  return layout.filter((l) => collides(l, layoutItem));
}

/**
 * Move an element. Responsible for doing cascading movements of other elements.
 *
 * @param  {Array}      layout Full layout to modify.
 * @param  {LayoutItem} l      element to move.
 * @param  {Number}     [x]    X position in grid units.
 * @param  {Number}     [y]    Y position in grid units.
 * @param  {Boolean}    [isUserAction] If true, designates that the item we're moving is
 *                                     being dragged/resized by th euser.
 */
export function moveElement(colNum, layout, l, columnIndex, rowIndex, isUserAction, preventCollision) {
  if (l.static) return layout;

  // Short-circuit if nothing to do.
  //if (l.rowIndex === rowIndex && l.columnIndex === columnIndex) return layout;

  const oldColumnIndex = l.columnIndex;
  const oldRowIndex = l.rowIndex;

  //const movingUp = rowIndex && l.rowIndex > rowIndex;  

  // This is quite a bit faster than extending the object
  if (typeof columnIndex === 'number') {
    l.columnIndex = columnIndex;
  }

  if (typeof rowIndex === 'number') {
    l.rowIndex = rowIndex;
  }

  l.moved = true;

  // If this collides with anything, move it.
  // When doing this comparison, we have to sort the items we compare with
  // to ensure, in the case of multiple collisions, that we're getting the
  // nearest collision.
   let sorted = sortLayoutItemsByRowCol(layout);
 
 // if (movingUp) sorted = sorted.reverse();
  const collisions = getAllCollisions(sorted, l);

  if (preventCollision && collisions.length) {
    l.columnIndex = oldColumnIndex;
    l.rowIndex = oldRowIndex;
    l.moved = false;
    return layout;
  }

  // Move each item that collides away from this element.
  for (let i = 0, len = collisions.length; i < len; i++) {
    const collision = collisions[i];
    // console.log('resolving collision between', l.id, 'at', l.rowIndex, 'and', collision.id, 'at', collision.rowIndex);

    // Short circuit so we can't infinite loop
    if (collision.moved) {
      continue;
    }

    // This makes it feel a bit more precise by waiting to swap for just a bit when moving up.
    /*if (l.rowIndex > collision.rowIndex && l.rowIndex - collision.rowIndex > collision.height / 4) {
      continue;
    }*/

    layout = moveElementAwayFromCollision(colNum, layout, l, collision, isUserAction);
  }

  return layout;
}

/**
 * This is where the magic needs to happen - given a collision, move an element away from the collision.
 * We attempt to move it up if there's room, otherwise it goes below.
 *
 * @param  {Array} layout            Full layout to modify.
 * @param  {LayoutItem} collidesWith Layout item we're colliding with.
 * @param  {LayoutItem} itemToMove   Layout item we're moving.
 * @param  {Boolean} [isUserAction]  If true, designates that the item we're moving is being dragged/resized
 *                                   by the user.
 */
export function moveElementAwayFromCollision(colNum, layout, collidesWith,
                                             itemToMove, isUserAction) {

  const preventCollision = false // we're already colliding

  
  // If there is enough space above the collision to put this element, move it there.
  // We only do this on the main collision as this can get funky in cascades and cause
  // unwanted swapping behavior.
  if (isUserAction) {
    // Make a mock item so we don't modify the item here, only modify in moveElement.
    const fakeItem = {
      columnIndex: itemToMove.columnIndex,
      rowIndex: itemToMove.rowIndex,
      width: itemToMove.width,
      height: itemToMove.height,
      id: '-1'
    };
    if(collidesWith.columnIndex - itemToMove.width < 0) {

      if(fakeItem.rowIndex > 0) {
        fakeItem.columnIndex = colNum - itemToMove.width;
        fakeItem.rowIndex--;
      }

    } else {
      fakeItem.columnIndex = collidesWith.columnIndex - itemToMove.width;
    }
    
    if (!getFirstCollision(layout, fakeItem)) {
      return moveElement(colNum, layout, itemToMove, fakeItem.columnIndex, fakeItem.rowIndex, preventCollision);
    }
  }
  
  // Previously this was optimized to move below the collision directly, but this can cause problems
  // with cascading moves, as an item may actually leapflog a collision and cause a reversal in order.
  let newColumnIndex = itemToMove.columnIndex + 1;
  let newRowIndex = itemToMove.rowIndex;
  if(newColumnIndex >= colNum) {
    newColumnIndex = 0;
    newRowIndex++;
  }
  return moveElement(colNum, layout, itemToMove, newColumnIndex, newRowIndex, preventCollision);
}

/**
 * Helper to convert a number to a percentage string.
 *
 * @param  {Number} num Any number
 * @return {String}     That number as a percentage.
 */
export function perc(num) {
  return num * 100 + '%';
}

export function setTransform(scale, top, left, width, height) {
  // Replace unitless items with px
  let translate = "translate3d(" + left + "px," + top + "px, 0)";
  if(scale) {
    translate += ` scale(${scale})`;
  }

  let style = {
    transform: translate,
    WebkitTransform: translate,
    MozTransform: translate,
    msTransform: translate,
    OTransform: translate,
    width: width + "px",
    height: (height == 'auto') ? width + "px" : height + "px",
    position: 'absolute'    
  };

  if(scale != null) {
    style.zIndex = 2;
    style.opacity = 1;
  }

  return style;

}

export function setTopLeft(top, left, width, height) {
    return {
        top: top + "px",
        left: left + "px",
        width: width + "px",
        height: (height == 'auto') ? width + "px" : height + "px",
        position: 'absolute'
    };
}
/**
 * Just like the setTopLeft method, but instead, it will return a right property instead of left.
 *
 * @param top
 * @param right
 * @param width
 * @param height
 * @returns {{top, right, width, height, position}}
 */
export function setTopRight(top, right, width, height) {
    return {
        top: top + "px",
        right: right+ "px",
        width: width + "px",
        height: (height == 'auto') ? width + "px" : height + "px",
        position: 'absolute'
    };
}


/**
 * Get layout items sorted from top left to right and down.
 *
 * @return {Array} Array of layout objects.
 * @return {Array}        Layout, sorted static items first.
 */
export function sortLayoutItemsByRowCol(layout) {

  let repaired = [].concat(layout).map(function(item) {
    if(item.columnIndex == null || item.columnIndex < 0) {
      item.columnIndex = 0;
    }
  
    if(item.rowIndex == null || item.rowIndex < 0) {
      item.rowIndex = 0;
    }
  
    if(item.width == null || item.width < 0) {
      item.width = 1;
    }
  
    if(item.height == null || item.height < 0) {
      item.height = 1;
    }

    return item;
  });
 
  return repaired.sort(function(a, b) {
    if (a.rowIndex > b.rowIndex || (a.rowIndex === b.rowIndex && a.columnIndex > b.columnIndex)) {
      return 1;
    }
    return -1;
  });
}

/**
 * Generate a layout using the initialLayout and children as a template.
 * Missing entries will be added, extraneous ones will be truncated.
 *
 * @param  {Array}  initialLayout Layout passed in through props.
 * @param  {String} breakpoint    Current responsive breakpoint.
 * @param  {Boolean} verticalCompact Whether or not to compact the layout vertically.
 * @return {Array}                Working layout.
 */
/*
export function synchronizeLayoutWithChildren(initialLayout, children: Array<React.Element>|React.Element,
                                              cols, verticalCompact) {
  // ensure 'children' is always an array
  if (!Array.isArray(children)) {
    children = [children];
  }
  initialLayout = initialLayout || [];

  // Generate one layout item per child.
  let layout = [];
  for (let i = 0, len = children.length; i < len; i++) {
    let newItem;
    const child = children[i];

    // Don't overwrite if it already exists.
    const exists = getLayoutItem(initialLayout, child.key || "1" /!* FIXME satisfies Flow *!/);
    if (exists) {
      newItem = exists;
    } else {
      const g = child.props._grid;

      // Hey, this item has a _grid property, use it.
      if (g) {
        if (!isProduction) {
          validateLayout([g], 'ReactGridLayout.children');
        }
        // Validated; add it to the layout. Bottom 'y' possible is the bottom of the layout.
        // This allows you to do nice stuff like specify {y: Infinity}
        if (verticalCompact) {
          newItem = cloneLayoutItem({...g, y: Math.min(bottom(layout), g.rowIndex), i: child.key});
        } else {
          newItem = cloneLayoutItem({...g, y: g.rowIndex, i: child.key});
        }
      }
      // Nothing provided: ensure this is added to the bottom
      else {
        newItem = cloneLayoutItem({w: 1, h: 1, x: 0, y: bottom(layout), i: child.key || "1"});
      }
    }
    layout[i] = newItem;
  }

  // Correct the layout.
  layout = correctBounds(layout, {cols: cols});
  layout = compact(layout, verticalCompact);

  return layout;
}
*/

/**
 * Validate a layout. Throws errors.
 *
 * @param  {Array}  layout        Array of layout items.
 * @param  {String} [contextName] Context name for errors.
 * @throw  {Error}                Validation error.
 */
export function validateLayout(layout, contextName) {
  contextName = contextName || "Layout";
  const subProps = ['columnIndex', 'rowIndex', 'width', 'height'];
  if (!Array.isArray(layout)) throw new Error(contextName + " must be an array!");
  for (let i = 0, len = layout.length; i < len; i++) {
    const item = layout[i];
    for (let j = 0; j < subProps.length; j++) {
      if (typeof item[subProps[j]] !== 'number') {
        throw new Error('VueGridLayout: ' + contextName + '[' + i + '].' + subProps[j] + ' must be a number!');
      }
    }
    if (item.static !== undefined && typeof item.static !== 'boolean') {
      throw new Error('VueGridLayout: ' + contextName + '[' + i + '].static must be a boolean!');
    }
  }
}

// Flow can't really figure this out, so we just use Object
export function autoBindHandlers(el, fns) {
  fns.forEach((key) => el[key] = el[key].bind(el));
}



/**
 * Convert a JS object to CSS string. Similar to React's output of CSS.
 * @param obj
 * @returns {string}
 */
export function createMarkup(obj) {
    var keys = Object.keys(obj);
    if (!keys.length) return '';
    var i, len = keys.length;
    var result = '';

    for (i = 0; i < len; i++) {
        var key = keys[i];
        var val = obj[key];
        result += hyphenate(key) + ':' + addPx(key, val) + ';';
    }

    return result;
}


/* The following list is defined in React's core */
export var IS_UNITLESS = {
    animationIterationCount: true,
    boxFlex: true,
    boxFlexGroup: true,
    boxOrdinalGroup: true,
    columnCount: true,
    flex: true,
    flexGrow: true,
    flexPositive: true,
    flexShrink: true,
    flexNegative: true,
    flexOrder: true,
    gridRow: true,
    gridColumn: true,
    fontWeight: true,
    lineClamp: true,
    lineHeight: true,
    opacity: true,
    order: true,
    orphans: true,
    tabSize: true,
    widows: true,
    zIndex: true,
    zoom: true,

    // SVG-related properties
    fillOpacity: true,
    stopOpacity: true,
    strokeDashoffset: true,
    strokeOpacity: true,
    strokeWidth: true
};


/**
 * Will add px to the end of style values which are Numbers.
 * @param name
 * @param value
 * @returns {*}
 */
export function addPx(name, value) {
    if(typeof value === 'number' && !IS_UNITLESS[ name ]) {
        return value + 'px';
    } else {
        return value;
    }
}


/**
 * Hyphenate a camelCase string.
 *
 * @param {String} str
 * @return {String}
 */

export var hyphenateRE = /([a-z\d])([A-Z])/g;

export function hyphenate(str) {
    return str.replace(hyphenateRE, '$1-$2').toLowerCase();
}


export function findItemInArray(array, property, value) {
    for (var i=0; i < array.length; i++)
        if (array[i][property] == value)
            return true;

    return false;
}

export function findAndRemove(array, property, value) {
    array.forEach(function (result, index) {
        if (result[property] === value) {
            //Remove from array
            array.splice(index, 1);
        }
    });
}
