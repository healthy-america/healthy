/*
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_GiftCard
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function(Component){
    "use strict";

    return Component.extend({

        /**
         * Delete record
         *
         * @param {Number} index - row index
         *
         */
        deleteRecord: function (index, recordId) {
            var recordInstance,
                lastRecord,
                childs,
                recordsData;

            if (this.deleteProperty) {
                recordsData = this.recordData();
                recordInstance = _.find(this.elems(), function (elem) {
                    return elem.index === index;
                });
                recordInstance.destroy();
                this.elems([]);
                this._updateCollection();
                this.removeMaxPosition();
                recordsData[recordInstance.index][this.deleteProperty] = this.deleteValue;
                this.recordData(recordsData);
                this.recordData.valueHasMutated();
                childs = this.getChildItems();

                if (childs.length > this.elems().length) {
                    this.addChild(false, childs[childs.length - 1][this.identificationProperty], false);
                }
            } else {
                this.update = true;

                if (~~this.currentPage() === this.pages()) {
                    lastRecord =
                        _.findWhere(this.elems(), {
                            index: this.startIndex + this.getChildItems().length - 1
                        }) ||
                        _.findWhere(this.elems(), {
                            index: (this.startIndex + this.getChildItems().length - 1).toString()
                        });

                    lastRecord.destroy();
                }

                this.removeMaxPosition();
                recordsData = this._getDataByProp(recordId);
                this._updateData(recordsData);
                this.update = false;
            }

            this._reducePages();
            this._sort();
        }
    });
});


