//
// The app to add/edit writingcatalog images
//
function ciniki_writingcatalog_images() {
    this.webFlags = {
        '1':{'name':'Visible'},
        };
    this.init = function() {
        //
        // The panel to display the edit form
        //
        this.edit = new M.panel('Edit Image',
            'ciniki_writingcatalog_images', 'edit',
            'mc', 'medium', 'sectioned', 'ciniki.writingcatalog.images.edit');
        this.edit.default_data = {};
        this.edit.data = {};
        this.edit.writingcatalog_id = 0;
        this.edit.sections = {
            '_image':{'label':'Image', 'type':'imageform', 'fields':{
                'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
                }},
            'info':{'label':'Information', 'type':'simpleform', 'fields':{
                'name':{'label':'Title', 'type':'text', },
//              'webflags':{'label':'Website', 'type':'flags', 'join':'yes', 'flags':this.webFlags},
                }},
            '_website':{'label':'Website Information', 'fields':{
                'webflags_1':{'label':'Visible', 'type':'flagtoggle', 'field':'webflags', 'bit':0x01, 'default':'on'},
                }},
            '_description':{'label':'Description', 'type':'simpleform', 'fields':{
                'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
                }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_writingcatalog_images.saveImage();'},
                'delete':{'label':'Delete', 'visible':'no', 'fn':'M.ciniki_writingcatalog_images.deleteImage();'},
                }},
        };
        this.edit.fieldValue = function(s, i, d) { 
            if( this.data[i] != null ) {
                return this.data[i]; 
            } 
            return ''; 
        };
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.writingcatalog.imageHistory',
                'args':{'tnid':M.curTenantID, 
                'writingcatalog_image_id':M.ciniki_writingcatalog_images.edit.writingcatalog_image_id, 'field':i}};
        };
        this.edit.addDropImage = function(iid) {
            M.ciniki_writingcatalog_images.edit.setFieldValue('image_id', iid, null, null);
            return true;
        };
        this.edit.sectionGuidedTitle = function(s) {
            if( s == '_image' ) {
                if( this.data.image_id != null && this.data.image_id > 0 ) {
                    return this.sections[s]['gtitle-edit'];
                } else {
                    return this.sections[s]['gtitle-add'];
                }
            }
            if( this.sections[s] != null && this.sections[s].gtitle != null ) { return this.sections[s].gtitle; }
            return null;
        };
        this.edit.sectionGuidedText = function(s) {
            if( s == '_image' ) {
                if( this.data.image_id != null && this.data.image_id > 0 ) {
                    return this.sections[s]['gtext-edit'];
                } else {
                    return this.sections[s]['gtext-add'];
                }
            }
            if( s == '_buttons' ) {
                if( this.sections[s].buttons.delete.visible == 'yes' ) {
                    return this.sections[s]['gtext-edit'];
                } else {
                    return this.sections[s]['gtext-add'];
                }
            }
            if( this.sections[s] != null && this.sections[s].gtext != null ) { return this.sections[s].gtext; }
            return null;
        };
        this.edit.sectionGuidedMore = function(s) {
            if( s == '_image' ) {
                if( this.data.image_id != null && this.data.image_id > 0 ) {
                    return this.sections[s]['gmore-edit'];
                } else {
                    return this.sections[s]['gmore-add'];
                }
            }
            if( s == '_buttons' ) {
                if( this.sections[s].buttons.delete.visible == 'yes' ) {
                    return this.sections[s]['gmore-edit'];
                }
            }
            if( this.sections[s] != null && this.sections[s].gmore != null ) { return this.sections[s].gmore; }
            return null;
        };
        this.edit.addButton('save', 'Save', 'M.ciniki_writingcatalog_images.saveImage();');
        this.edit.addClose('Cancel');
    };

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_writingcatalog_images', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        }

        if( args.add != null && args.add == 'yes' ) {
            this.showEdit(cb, 0, args.writingcatalog_id);
        } else if( args.writingcatalog_image_id != null && args.writingcatalog_image_id > 0 ) {
            this.showEdit(cb, args.writingcatalog_image_id);
        }
        return false;
    }

    this.showEdit = function(cb, iid, eid) {
        if( iid != null ) { this.edit.writingcatalog_image_id = iid; }
        if( eid != null ) { this.edit.writingcatalog_id = eid; }
        this.edit.reset();
        if( this.edit.writingcatalog_image_id > 0 ) {
            this.edit.sections._buttons.buttons.delete.visible = 'yes';
            var rsp = M.api.getJSONCb('ciniki.writingcatalog.imageGet', 
                {'tnid':M.curTenantID, 'writingcatalog_image_id':this.edit.writingcatalog_image_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_writingcatalog_images.edit.data = rsp.image;
                    M.ciniki_writingcatalog_images.edit.refresh();
                    M.ciniki_writingcatalog_images.edit.show(cb);
                });
        } else {
            this.edit.data = {};
            this.edit.sections._buttons.buttons.delete.visible = 'no';
            this.edit.refresh();
            this.edit.show(cb);
        }
    };

    this.saveImage = function() {
        if( this.edit.writingcatalog_image_id > 0 ) {
            var c = this.edit.serializeFormData('no');
            if( c != '' ) {
                var rsp = M.api.postJSONFormData('ciniki.writingcatalog.imageUpdate', 
                    {'tnid':M.curTenantID, 
                    'writingcatalog_image_id':this.edit.writingcatalog_image_id}, c,
                        function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } else {
                                M.ciniki_writingcatalog_images.edit.close();
                            }
                        });
            } else {
                this.edit.close();
            }
        } else {
            var c = this.edit.serializeFormData('yes');
            var rsp = M.api.postJSONFormData('ciniki.writingcatalog.imageAdd', 
                {'tnid':M.curTenantID, 'writingcatalog_id':this.edit.writingcatalog_id}, c,
                    function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } else {
                            M.ciniki_writingcatalog_images.edit.close();
                        }
                    });
        }
    };

    this.deleteImage = function() {
        M.confirm('Are you sure you want to delete this image?',null,function() {
            var rsp = M.api.getJSONCb('ciniki.writingcatalog.imageDelete', {'tnid':M.curTenantID, 
                'writingcatalog_image_id':M.ciniki_writingcatalog_images.edit.writingcatalog_image_id}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_writingcatalog_images.edit.close();
                });
        });
    };
}
