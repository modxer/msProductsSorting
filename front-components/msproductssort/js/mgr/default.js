Ext.ComponentMgr.onAvailable('minishop2-grid-products', function(){
    this.config.baseParams.action = "mgr/product/customgetlist";
    this.config.save_action = 'mgr/product/customupdatefromgrid';
});
