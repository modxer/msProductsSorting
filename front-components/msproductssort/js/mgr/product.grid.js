miniShop2.config.product_fields.splice(1,0,'sortproduct_idx','sortproduct_id','sortcategory_id');
miniShop2.config.grid_fields.splice(1,0,'sortproduct_idx');
//console.log(miniShop2.config);

Ext.override(miniShop2.grid.Products, {
    getColumns: function () {
        var columns = {
            id: {sortable: true, width: 40},
            sortproduct_idx: {width: 50, sortable: true, editor: {xtype: 'numberfield'}, header: 'Сортировка'},
            pagetitle: {width: 100, sortable: true, id: 'product-title', renderer: this._renderPagetitle},
            longtitle: {width: 50, sortable: true, editor: {xtype: 'textfield'}},
            description: {width: 100, sortable: false, editor: {xtype: 'textarea'}},
            alias: {width: 50, sortable: true, editor: {xtype: 'textfield'}},
            introtext: {width: 100, sortable: false, editor: {xtype: 'textarea'}},
            content: {width: 100, sortable: false, editor: {xtype: 'textarea'}},
            template: {width: 100, sortable: true, editor: {xtype: 'modx-combo-template'}},
            createdby: {width: 100, sortable: true, editor: {xtype: 'minishop2-combo-user', name: 'createdby'}},
            createdon: {
                width: 50,
                sortable: true,
                editor: {xtype: 'minishop2-xdatetime', timePosition: 'below'},
                renderer: miniShop2.utils.formatDate
            },
            editedby: {width: 100, sortable: true, editor: {xtype: 'minishop2-combo-user', name: 'editedby'}},
            editedon: {
                width: 50,
                sortable: true,
                editor: {xtype: 'minishop2-xdatetime', timePosition: 'below'},
                renderer: miniShop2.utils.formatDate
            },
            deleted: {width: 50, sortable: true, editor: {xtype: 'combo-boolean', renderer: 'boolean'}},
            deletedon: {
                width: 50,
                sortable: true,
                editor: {xtype: 'minishop2-xdatetime', timePosition: 'below'},
                renderer: miniShop2.utils.formatDate
            },
            deletedby: {width: 100, sortable: true, editor: {xtype: 'minishop2-combo-user', name: 'deletedby'}},
            published: {width: 50, sortable: true, editor: {xtype: 'combo-boolean', renderer: 'boolean'}},
            publishedon: {
                width: 50,
                sortable: true,
                editor: {xtype: 'minishop2-xdatetime', timePosition: 'below'},
                renderer: miniShop2.utils.formatDate
            },
            publishedby: {width: 100, sortable: true, editor: {xtype: 'minishop2-combo-user', name: 'publishedby'}},
            menutitle: {width: 100, sortable: true, editor: {xtype: 'textfield'}},
            menuindex: {width: 35, sortable: true, header: 'IDx', editor: {xtype: 'numberfield'}},
            uri: {width: 50, sortable: true, editor: {xtype: 'textfield'}},
            uri_override: {width: 50, sortable: true, editor: {xtype: 'combo-boolean', renderer: 'boolean'}},
            show_in_tree: {width: 50, sortable: true, editor: {xtype: 'combo-boolean', renderer: 'boolean'}},
            hidemenu: {width: 50, sortable: true, editor: {xtype: 'combo-boolean', renderer: 'boolean'}},
            richtext: {width: 100, sortable: true, editor: {xtype: 'combo-boolean', renderer: 'boolean'}},
            searchable: {width: 100, sortable: true, editor: {xtype: 'combo-boolean', renderer: 'boolean'}},
            cacheable: {width: 100, sortable: true, editor: {xtype: 'combo-boolean', renderer: 'boolean'}},

            'new': {width: 50, sortable: true, editor: {xtype: 'combo-boolean', renderer: 'boolean'}},
            favorite: {width: 50, sortable: true, editor: {xtype: 'combo-boolean', renderer: 'boolean'}},
            popular: {width: 50, sortable: true, editor: {xtype: 'combo-boolean', renderer: 'boolean'}},
            article: {width: 50, sortable: true, editor: {xtype: 'textfield'}},
            price: {width: 50, sortable: true, editor: {xtype: 'numberfield', decimalPrecision: 2}},
            old_price: {width: 50, sortable: true, editor: {xtype: 'numberfield', decimalPrecision: 2}},
            weight: {width: 50, sortable: true, editor: {xtype: 'numberfield', decimalPrecision: 3}},
            image: {width: 50, sortable: false, renderer: miniShop2.utils.renderImage, id: 'product-image'},
            thumb: {width: 50, sortable: false, renderer: miniShop2.utils.renderImage, id: 'product-thumb'},
            vendor: {
                width: 50,
                sortable: true,
                renderer: this._renderVendor,
                editor: {xtype: 'minishop2-combo-vendor'},
            },
            vendor_name: {width: 50, sortable: true, header: _('ms2_product_vendor')},
            made_in: {width: 50, sortable: true, editor: {xtype: 'minishop2-combo-autocomplete', name: 'made_in'}},
            //color: {width:50, sortable:false, editor: {xtype: 'minishop2-combo-options', name: 'color'}},
            //size: {width:50, sortable:false, editor: {xtype: 'minishop2-combo-options', name: 'size'}},
            //tags: {width:50, sortable:false, editor: {xtype: 'minishop2-combo-options', name: 'tags'}},
            actions: {
                header: _('ms2_actions'),
                id: 'actions',
                width: 75,
                sortable: false,
                renderer: miniShop2.utils.renderActions
            }
        };

        var i,add;
        for (i in miniShop2.plugin) {
            if (!miniShop2.plugin.hasOwnProperty(i)) {
                continue;
            }
            if (typeof(miniShop2.plugin[i]['getColumns']) == 'function') {
                add = miniShop2.plugin[i].getColumns();
                Ext.apply(columns, add);
            }
        }

        var option_columns= [];
        if (miniShop2.config['show_options']) {
            option_columns = this.getCategoryOptions(miniShop2.config);
        }

        var fields = [];
        for (i in miniShop2.config['grid_fields']) {
            if (!miniShop2.config['grid_fields'].hasOwnProperty(i)) {
                continue;
            }
            var field = miniShop2.config['grid_fields'][i];
            if (columns[field]) {
                Ext.applyIf(columns[field], {
                    header: _('ms2_product_' + field),
                    dataIndex: field
                });
                fields.push(columns[field]);
            }else if (option_columns[field]) {
                fields.push(option_columns[field]);
            }
        }

        return fields;
    }
})
