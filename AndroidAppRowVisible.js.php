/**
 * Copyright 2004-present Facebook. All Rights Reserved.
 *
 * @providesModule ViewabilityHelper
 * @flow
 */
'use strict';

const ViewabilityConsts = {
  VIEWABLE_PERCENT_THRESHOLD: 50,
};

/**
* A row is said to be in a "viewable" state when either of the following
* is true:
* - it is occupying >= half of the viewport
* - it is entirely visible on screen
*/
var ViewabilityHelper = {
  computeViewableRows(
    rowFrames: Array<Object>,
    scrollOffsetY: number,
    viewportHeight: number
  ): Array<number> {
    var viewableRows = [];
    var firstVisible = -1;
    for (var idx = 0; idx < rowFrames.length; idx++) {
      var frame = rowFrames[idx];
      if (!frame) {
        continue;
      }
      var top = frame.y - scrollOffsetY;
      var bottom = top + frame.height;
      if ((top < viewportHeight) && (bottom > 0)) {
        firstVisible = idx;
        if (this._isViewable(top, bottom, viewportHeight)) {
          viewableRows.push(idx);
        }
      } else if (firstVisible >= 0) {
        break;
      }
    }
    return viewableRows;
  },
  _isViewable(top: number, bottom: number, viewportHeight: number): bool {
    return this._isEntirelyVisible(top, bottom, viewportHeight) ||
        this._getPercentOccupied(top, bottom, viewportHeight) >=
            ViewabilityConsts.VIEWABLE_PERCENT_THRESHOLD;
  },
  _getPercentOccupied(
    top: number,
    bottom: number,
    viewportHeight: number
  ): number {
    var visibleHeight = Math.min(bottom, viewportHeight) - Math.max(top, 0);
    visibleHeight = Math.max(0, visibleHeight);
    return Math.max(0, visibleHeight * 100 / viewportHeight);
  },
  _isEntirelyVisible(
    top: number,
    bottom: number,
    viewportHeight: number
  ): bool {
    return top >= 0 && bottom <= viewportHeight && bottom > top;
  }
};

module.exports = ViewabilityHelper;
