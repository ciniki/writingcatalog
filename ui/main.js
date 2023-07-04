//
// The writingcatalog app to manage an artists collection
//
function ciniki_writingcatalog_main() {
    this.toggleOptions = {
        'no':'No',
        'yes':'Yes',
    };
    this.itemFlags = {
        '1':{'name':'For Sale'},
        '2':{'name':'Sold'},
        };
    this.noyesToggles = {
        'no':'No',
        'yes':'Yes',
        };
    this.webFlags = {
        '1':{'name':'Hide'},
        '5':{'name':'Category Highlight'},
        };
    this.monthOptions = {
        '0':'Unspecified',
        '1':'January',
        '2':'February',
        '3':'March',
        '4':'April',
        '5':'May',
        '6':'June',
        '7':'July',
        '8':'August',
        '9':'September',
        '10':'October',
        '11':'November',
        '12':'December',
        };
    this.dayOptions = {
        '0':'Unspecified',
        '1':'1',
        '2':'2',
        '3':'3',
        '4':'4',
        '5':'5',
        '6':'6',
        '7':'7',
        '8':'8',
        '9':'9',
        '10':'10',
        '11':'11',
        '12':'12',
        '13':'13',
        '14':'14',
        '15':'15',
        '16':'16',
        '17':'17',
        '18':'18',
        '19':'19',
        '20':'20',
        '21':'21',
        '22':'22',
        '23':'23',
        '24':'24',
        '25':'25',
        '26':'26',
        '27':'27',
        '28':'28',
        '29':'29',
        '30':'30',
        '31':'31',
        };
    this.statusOptions = {
        '10':'NFS',
        '20':'For Sale',
        '50':'Sold',
        '60':'Private Collection',
        '70':'Artist Collection',
        };
    this.cur_type = null;
    this.init = function() {

        //
        // Setup the main panel to list the collection
        //
        this.menu = new M.panel('Writing Catalog',
            'ciniki_writingcatalog_main', 'menu',
            'mc', 'medium', 'sectioned', 'ciniki.writingcatalog.main.menu');
        this.menu.data = {};
        this.menu.sections = {
            'items':{'label':'', 'type':'simplegrid', 'num_cols':2,
                'addTxt':'Add',
                'addFn':'M.ciniki_writingcatalog_main.itemEdit(\'M.ciniki_writingcatalog_main.showMenu();\',0);',
                },
            };
        this.menu.sectionData = function(s) { 
            return this.data[s];
        };
        this.menu.cellValue = function(s, i, j, d) {
            switch(j) {
                case 0: return d.item.type_text;
                case 1: return d.item.title;
            }
        };
        this.menu.rowFn = function(s, i, d) {
            return 'M.ciniki_writingcatalog_main.itemShow(\'M.ciniki_writingcatalog_main.showMenu();\', \'' + d.item.id + '\');'; 
        };
        this.menu.addButton('add', 'Add', 'M.ciniki_writingcatalog_main.itemEdit(\'M.ciniki_writingcatalog_main.showMenu();\',0);');
//      this.menu.addButton('tools', 'Tools', 'M.ciniki_writingcatalog_main.tools.show(\'M.ciniki_writingcatalog_main.showMenu();\');');
        this.menu.addClose('Back');

        //
        // Display information about a item of writing
        //
        this.item = new M.panel('Writing',
            'ciniki_writingcatalog_main', 'item',
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.writingcatalog.main.edit');
        this.item.writingcatalog_id = 0;
        this.item.next_item_id = 0;
        this.item.prev_item_id = 0;
        this.item.data = null;
        this.item.list = null;
        this.item.writingcatalog_id = 0;
        this.item.sections = {
            '_image':{'label':'Image', 'aside':'yes', 'type':'imageform', 'fields':{
                'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'history':'no'},
            }},
            'info':{'label':'Public Information', 'aside':'yes', 'list':{
                'type_text':{'label':'Type'},
                'title':{'label':'Title', 'type':'text'},
                'subtitle':{'label':'Subtitle', 'type':'text'},
                'catalog_number':{'label':'Number'},
                'completed':{'label':'Completed'},
                'categories':{'label':'Categories', 'visible':'no'},
                'website':{'label':'Website'},
            }},
            'synopsis':{'label':'Synopsis', 'type':'htmlcontent', 'visible':function() {return M.ciniki_writingcatalog_main.item.data.synopsis!=''?'yes':'no';}},
            'description':{'label':'Description', 'type':'htmlcontent', 'visible':function() {return M.ciniki_writingcatalog_main.item.data.description!=''?'yes':'no';}},
            'content':{'label':'', 'type':'htmlcontent', 'visible':function() {return M.ciniki_writingcatalog_main.item.data.content!=''?'yes':'no';}},
            'inspiration':{'label':'Inspiration', 'type':'htmlcontent', 'visible':function() {return M.ciniki_writingcatalog_main.item.data.inspiration!=''?'yes':'no';}},
            'awards':{'label':'Awards', 'type':'htmlcontent', 'visible':function() {return M.ciniki_writingcatalog_main.item.data.awards!=''?'yes':'no';}},
            'notes':{'label':'Notes', 'type':'htmlcontent', 'visible':function() {return M.ciniki_writingcatalog_main.item.data.notes!=''?'yes':'no';}},
            'reviews':{'label':'Reviews', 'visible':'no', 'type':'simplegrid', 'num_cols':1,
                'addTxt':'Add Review',
                'addFn':'M.ciniki_writingcatalog_main.contentEdit(\'M.ciniki_writingcatalog_main.itemShow();\',\'0\',\'10\',M.ciniki_writingcatalog_main.item.writingcatalog_id);',
                },
            'samples':{'label':'Samples', 'visible':'no', 'type':'simplegrid', 'num_cols':1,
                'addTxt':'Add Sample',
                'addFn':'M.ciniki_writingcatalog_main.contentEdit(\'M.ciniki_writingcatalog_main.itemShow();\',\'0\',\'20\',M.ciniki_writingcatalog_main.item.writingcatalog_id);',
                },
            'orderinginfo':{'label':'Purchase Options', 'visible':'no', 'type':'simplegrid', 'num_cols':1,
                'addTxt':'Add Purchasing Option',
                'addFn':'M.ciniki_writingcatalog_main.contentEdit(\'M.ciniki_writingcatalog_main.itemShow();\',\'0\',\'30\',M.ciniki_writingcatalog_main.item.writingcatalog_id);',
                },
            'images':{'label':'Additional Images', 
                'active':function() {return M.modFlagSet('ciniki.writingcatalog', 0x0800);},
                'type':'simplethumbs'},
            '_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
                'active':function() {return M.modFlagSet('ciniki.writingcatalog', 0x0800);},
                'addTxt':'Add Additional Image',
                'addFn':'M.startApp(\'ciniki.writingcatalog.images\',null,\'M.ciniki_writingcatalog_main.itemShow();\',\'mc\',{\'writingcatalog_id\':M.ciniki_writingcatalog_main.item.writingcatalog_id,\'add\':\'yes\'});',
                },
            '_buttons':{'label':'', 'buttons':{
                'edit':{'label':'Edit', 'fn':'M.ciniki_writingcatalog_main.itemEdit(\'M.ciniki_writingcatalog_main.itemShow();\',M.ciniki_writingcatalog_main.item.writingcatalog_id);'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_writingcatalog_main.itemDelete();'},
            }},
        };
        this.item.sectionData = function(s) {
            if( s == 'synopsis' || s == 'description' || s == 'content' || s == 'inspiration' || s == 'awards' || s == 'notes' ) {
                return this.data[s].replace(/\n/g, '<br/>');
            }
            if( s == 'info' ) { return this.sections[s].list; }
            return this.data[s];
        };
        this.item.listLabel = function(s, i, d) {
            switch (s) {
                case 'info': return d.label;
            }
        };
        this.item.listValue = function(s, i, d) {
            if( i == 'completed' ) {
                var com = '';
                if( this.data['month'] > 0 ) {
                    com += M.ciniki_writingcatalog_main.monthOptions[this.data['month']] + ' ';
                    if( this.data['day'] > 0 ) {
                        com += M.ciniki_writingcatalog_main.dayOptions[this.data['day']] + ', ';
                    }
                }
                return com + this.data['year'];
            }
            return this.data[i];
        };
        this.item.fieldValue = function(s, i, d) {
            if( i == 'synopsis' || i == 'description' || i == 'notes' ) { 
                return this.data[i].replace(/\n/g, '<br/>');
            }
            return this.data[i];
        };
        this.item.cellValue = function(s, i, j, d) {
            if( s == 'reviews' || s == 'samples' || s == 'orderinginfo' ) {
                switch (j) {
                    case 0: return d.content.title;
                }
            }
        };
        this.item.rowFn = function(s, i, d) {
            if( s == 'reviews' || s == 'samples' || s == 'orderinginfo' ) {
                return 'M.ciniki_writingcatalog_main.contentEdit(\'M.ciniki_writingcatalog_main.itemShow();\',\'' + d.content.id + '\');';
            }
        };
        this.item.noData = function(s) {
            return '';
        };
        this.item.prevButtonFn = function() {
            if( this.prev_item_id > 0 ) {
                return 'M.ciniki_writingcatalog_main.itemShow(null,\'' + this.prev_item_id + '\');';
            }
            return null;
        };
        this.item.nextButtonFn = function() {
            if( this.next_item_id > 0 ) {
                return 'M.ciniki_writingcatalog_main.itemShow(null,\'' + this.next_item_id + '\');';
            }
            return null;
        };
        this.item.thumbFn = function(s, i, d) {
            return 'M.startApp(\'ciniki.writingcatalog.images\',null,\'M.ciniki_writingcatalog_main.itemShow();\',\'mc\',{\'writingcatalog_image_id\':\'' + d.image.id + '\'});';
        };
        this.item.addDropImage = function(iid) {
            var rsp = M.api.getJSON('ciniki.writingcatalog.imageAdd',
                {'tnid':M.curTenantID, 'image_id':iid,
                    'writingcatalog_id':M.ciniki_writingcatalog_main.item.writingcatalog_id});
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            return true;
        };
        this.item.addDropImageRefresh = function() {
            if( M.ciniki_writingcatalog_main.item.writingcatalog_id > 0 ) {
                var rsp = M.api.getJSONCb('ciniki.writingcatalog.get', {'tnid':M.curTenantID, 
                    'writingcatalog_id':M.ciniki_writingcatalog_main.item.writingcatalog_id, 'images':'yes'}, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        }
                        M.ciniki_writingcatalog_main.item.data.images = rsp.item.images;
                        M.ciniki_writingcatalog_main.item.refreshSection('images');
                    });
            }
        };
        this.item.addButton('edit', 'Edit', 'M.ciniki_writingcatalog_main.itemEdit(\'M.ciniki_writingcatalog_main.itemShow();\',M.ciniki_writingcatalog_main.item.writingcatalog_id);');
        this.item.addButton('next', 'Next');
//      this.item.addLeftButton('website', 'Preview', 'M.showWebsite(\'/gallery/category/\'+M.ciniki_writingcatalog_main.item.data.category+\'/\'+M.ciniki_writingcatalog_main.item.data.permalink);');
        this.item.addClose('Back');
        this.item.addLeftButton('prev', 'Prev');

        //
        // The panel to display the edit form
        //
        this.edit = new M.panel('Writing',
            'ciniki_writingcatalog_main', 'edit',
            'mc', 'medium mediumaside', 'sectioned', 'ciniki.writingcatalog.main.edit');
        this.edit.writingcatalog_id = 0;
        this.edit.form_id = 30;
        this.edit.data = null;
        this.edit.cb = null;
        this.edit.forms = {};
        this.edit.formtabs = {'label':'', 'field':'type', 
            'tabs':{
                'book':{'label':'Book', 'field_id':30},
//              'shortstory':{'label':'Short Story', 'field_id':31},
//              'article':{'label':'Article', 'field_id':32},
                'poetry':{'label':'Poetry', 'field_id':60},
            }};
        this.edit.forms.book = {
            '_image':{'label':'Image', 'aside':'yes', 'type':'imageform', 'fields':{
                'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
                }},
            'info':{'label':'Catalog Information', 'aside':'yes', 'type':'simpleform', 'fields':{
                'title':{'label':'Title', 'type':'text', },
                'subtitle':{'label':'Subtitle', 'type':'text', },
                'catalog_number':{'label':'Number', 'type':'text', 'size':'small', },
                'year':{'label':'Year', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes'},
                'month':{'label':'Month', 'type':'select', 'options':this.monthOptions},
                'day':{'label':'Day', 'type':'select', 'options':this.dayOptions},
                }},
            '_categories':{'label':'Categories', 'aside':'yes', 'active':'no', 'type':'simpleform', 'fields':{
                'categories':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'New Category'},
                }},
            '_synopsis':{'label':'Synopsis', 'type':'simpleform', 'fields':{
                'synopsis':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
                }},
            '_description':{'label':'Description', 'type':'simpleform', 'fields':{
                'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
                }},
            '_notes':{'label':'Notes', 'type':'simpleform', 'fields':{
                'notes':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
                }},
            '_website':{'label':'Website Information', 'type':'simpleform', 'fields':{
                'webflags_1':{'label':'Visible', 'type':'flagtoggle', 'field':'webflags', 'bit':0x01, 'default':'on'},
                }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_writingcatalog_main.itemSave();'},
                }},
        };
        this.edit.forms.poetry = {
            '_image':{'label':'Image', 'aside':'yes', 'type':'imageform', 'fields':{
                'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
                }},
            'info':{'label':'Catalog Information', 'aside':'yes', 'type':'simpleform', 'fields':{
                'title':{'label':'Title', 'type':'text', },
                'subtitle':{'label':'Subtitle', 'type':'text', },
                'catalog_number':{'label':'Number', 'type':'text', 'size':'small', },
                'year':{'label':'Year', 'type':'text', 'size':'small', 'livesearch':'yes', 'livesearchempty':'yes'},
                'month':{'label':'Month', 'type':'select', 'options':this.monthOptions},
                'day':{'label':'Day', 'type':'select', 'options':this.dayOptions},
                }},
            '_categories':{'label':'Categories', 'aside':'yes', 'active':'no', 'type':'simpleform', 'fields':{
                'categories':{'label':'', 'hidelabel':'yes', 'type':'tags', 'tags':[], 'hint':'New Category'},
                }},
            '_synopsis':{'label':'Synopsis', 'type':'simpleform', 'fields':{
                'synopsis':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
                }},
            '_description':{'label':'Description', 'type':'simpleform', 'fields':{
                'description':{'label':'', 'type':'textarea', 'size':'medium', 'hidelabel':'yes'},
                }},
            '_content':{'label':'Poem', 'type':'simpleform', 'fields':{
                'content':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
                }},
            '_inspiration':{'label':'Inspiration', 'type':'simpleform', 'fields':{
                'inspiration':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
                }},
            '_awards':{'label':'Awards', 'type':'simpleform', 'fields':{
                'awards':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
                }},
            '_notes':{'label':'Notes', 'type':'simpleform', 'fields':{
                'notes':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
                }},
            '_website':{'label':'Website Information', 'type':'simpleform', 'fields':{
                'webflags_1':{'label':'Visible', 'type':'flagtoggle', 'field':'webflags', 'bit':0x01, 'default':'on'},
                }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_writingcatalog_main.itemSave();'},
                }},
        };
        this.edit.forms.shortstory = this.edit.forms.book;
        this.edit.forms.article = this.edit.forms.book;
        this.edit.form_id = 30;
        this.edit.sections = this.edit.forms.book;
        this.edit.fieldValue = function(s, i, d) { 
            return this.data[i]; 
        }
        this.edit.sectionData = function(s) {
            return this.data[s];
        };
        this.edit.liveSearchCb = function(s, i, value) {
            if( i == 'year' ) {
                var rsp = M.api.getJSONBgCb('ciniki.writingcatalog.searchField', {'tnid':M.curTenantID, 'field':i, 'start_needle':value, 'limit':15},
                    function(rsp) {
                        M.ciniki_writingcatalog_main.edit.liveSearchShow(s, i, M.gE(M.ciniki_writingcatalog_main.edit.panelUID + '_' + i), rsp.results);
                    });
            }
        };
        this.edit.liveSearchResultValue = function(s, f, i, j, d) {
            if( (f == 'category' || f == 'media' || f == 'location' || f == 'year' ) && d.result != null ) { return d.result.name; }
            return '';
        };
        this.edit.liveSearchResultRowFn = function(s, f, i, j, d) { 
            if( f == 'year' && d.result != null ) {
                return 'M.ciniki_writingcatalog_main.edit.updateField(\'' + s + '\',\'' + f + '\',\'' + escape(d.result.name) + '\');';
            }
        };
        this.edit.updateField = function(s, fid, result) {
            M.gE(this.panelUID + '_' + fid).value = unescape(result);
            this.removeLiveSearch(s, fid);
        };
        this.edit.fieldHistoryArgs = function(s, i) {
            return {'method':'ciniki.writingcatalog.itemHistory', 
                'args':{'tnid':M.curTenantID, 'writingcatalog_id':this.writingcatalog_id, 'field':i}};
        }
        this.edit.addDropImage = function(iid) {
            M.ciniki_writingcatalog_main.edit.setFieldValue('image_id', iid);
            return true;
        };
        this.edit.deleteImage = function(fid) {
            this.setFieldValue(fid, 0);
            return true;
        };
        this.edit.addButton('save', 'Save', 'M.ciniki_writingcatalog_main.itemSave();');
        this.edit.addClose('Cancel');

        //
        // The panel to display the edit form
        //
        this.content = new M.panel('Content',
            'ciniki_writingcatalog_main', 'content',
            'mc', 'medium', 'sectioned', 'ciniki.writingcatalog.main.edit');
        this.content.content_id = 0;
        this.content.form_id = 1;
        this.content.data = null;
        this.content.cb = null;
        this.content.formtabs = {'label':'', 'field':'content_type', 
            'tabs':{
                'review':{'label':'Review', 'visible':'yes', 'field_id':10},
                'sample':{'label':'Sample', 'visible':'yes', 'field_id':20},
                'orderinfo':{'label':'Purchasing', 'visible':'yes', 'field_id':30},
            }};
        this.content.forms = {};
        this.content.forms.review = {
            '_content':{'label':'Review', 'type':'simpleform', 'fields':{
                'content':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
                }},
            'info':{'label':'', 'type':'simpleform', 'fields':{
                'title':{'label':'Author', 'type':'text', },
                'sequence':{'label':'Order', 'type':'text', 'size':'small'},
                }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_writingcatalog_main.contentSave();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_writingcatalog_main.contentDelete();'},
                }},
        };
        this.content.forms.sample = {
            'info':{'label':'Sample', 'type':'simpleform', 'fields':{
                'title':{'label':'Title', 'type':'text', },
                'sequence':{'label':'Order', 'type':'text', 'size':'small'},
                }},
            '_content':{'label':'Content', 'type':'simpleform', 'fields':{
                'content':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
                }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_writingcatalog_main.contentSave();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_writingcatalog_main.contentDelete();'},
                }},
        };
        this.content.forms.orderinfo = {
            'info':{'label':'Purchasing Option', 'type':'simpleform', 'fields':{
                'title':{'label':'Title', 'type':'text', },
                'sequence':{'label':'Order', 'type':'text', 'size':'small'},
                }},
            '_content':{'label':'Content', 'type':'simpleform', 'fields':{
                'content':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
                }},
            'paypal':{'label':'Paypal', 'type':'simpleform', 'fields':{
                'paypal_business':{'label':'Tenant Email', 'type':'text'},
                'paypal_price':{'label':'Price', 'type':'text', 'size':'small'},
                'paypal_currency':{'label':'Currency', 'type':'toggle', 'default':'CAD', 'toggles':{'CAD':'CAD', 'USD':'USD'}},
                }},
            '_buttons':{'label':'', 'buttons':{
                'save':{'label':'Save', 'fn':'M.ciniki_writingcatalog_main.contentSave();'},
                'delete':{'label':'Delete', 'fn':'M.ciniki_writingcatalog_main.contentDelete();'},
                }},
        };
        this.content.sections = this.content.forms.review;
        this.content.fieldValue = function(s, i, d) { 
            return this.data[i]; 
        }
        this.content.sectionData = function(s) {
            return this.data[s];
        };
        this.content.addButton('save', 'Save', 'M.ciniki_writingcatalog_main.contentSave();');
        this.content.addClose('Cancel');

        //
        // The tools available to work on customer records
        //
        this.tools = new M.panel('Catalog Tools',
            'ciniki_writingcatalog_main', 'tools',
            'mc', 'narrow', 'sectioned', 'ciniki.writingcatalog.main.tools');
        this.tools.data = {};
        this.tools.sections = {
            'tools':{'label':'Adjustments', 'list':{
                'categories':{'label':'Update Category Names', 'fn':'M.startApp(\'ciniki.writingcatalog.fieldupdate\', null, \'M.ciniki_writingcatalog_main.tools.show();\',\'mc\',{\'field\':\'category\',\'fieldname\':\'Categories\'});'},
                'media':{'label':'Update Media', 'fn':'M.startApp(\'ciniki.writingcatalog.fieldupdate\', null, \'M.ciniki_writingcatalog_main.tools.show();\',\'mc\',{\'field\':\'media\',\'fieldname\':\'Media\'});'},
                'location':{'label':'Update Locations', 'fn':'M.startApp(\'ciniki.writingcatalog.fieldupdate\', null, \'M.ciniki_writingcatalog_main.tools.show();\',\'mc\',{\'field\':\'location\',\'fieldname\':\'Locations\'});'},
                'years':{'label':'Update Years', 'fn':'M.startApp(\'ciniki.writingcatalog.fieldupdate\', null, \'M.ciniki_writingcatalog_main.tools.show();\',\'mc\',{\'field\':\'year\',\'fieldname\':\'Years\'});'},
            }},
            'tools1':{'label':'', 'list':{
                '_cats':{'label':'Category Details', 'fn':'M.startApp(\'ciniki.writingcatalog.categories\', null, \'M.ciniki_writingcatalog_main.tools.show();\');'},
            }},
            };
        this.tools.addClose('Back');
    }

    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) { args = eval(aG); }

        //
        // Create container
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_writingcatalog_main', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        }

        this.item.sections.reviews.visible = (M.curTenant.modules['ciniki.writingcatalog'].flags&0x0100) > 0?'yes':'no';
        this.item.sections.samples.visible = (M.curTenant.modules['ciniki.writingcatalog'].flags&0x0200) > 0?'yes':'no';
        this.item.sections.orderinginfo.visible = (M.curTenant.modules['ciniki.writingcatalog'].flags&0x0400) > 0?'yes':'no';

        this.content.formtabs.tabs.review.visible = (M.curTenant.modules['ciniki.writingcatalog'].flags&0x0100) > 0?'yes':'no';
        this.content.formtabs.tabs.sample.visible = (M.curTenant.modules['ciniki.writingcatalog'].flags&0x0200) > 0?'yes':'no';
        this.content.formtabs.tabs.orderinfo.visible = (M.curTenant.modules['ciniki.writingcatalog'].flags&0x0400) > 0?'yes':'no';

        this.item.sections.info.list.categories.visible = (M.curTenant.modules['ciniki.writingcatalog'].flags&0x04) > 0?'yes':'no';

        //
        // Set lists to visible if enabled
        //
        for(i in this.edit.forms) {
            if( (M.curTenant.modules['ciniki.writingcatalog'].flags&0x04) > 0 ) {
                this.edit.forms[i]._categories.active = 'yes';
            } else {
                this.edit.forms[i]._categories.active = 'no';
            }
        }

        if( args.writingcatalog_id != null && args.writingcatalog_id == 0 ) {
            this.itemEdit(cb, 0);
        } else if( args.writingcatalog_id != null && args.writingcatalog_id != '' ) {
            this.itemShow(cb, args.writingcatalog_id);
        } else {
            this.showMenu(cb);
        }
    }

    this.showMenu = function(cb) {
        M.api.getJSONCb('ciniki.writingcatalog.itemList', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_writingcatalog_main.menu;
            p.data = rsp;
            p.refresh();
            p.show(cb);
        });
    };

    this.itemShow = function(cb, iid, list) {
        if( iid != null ) { this.item.writingcatalog_id = iid; }
        if( list != null ) { this.item.list = list; }
        M.api.getJSONCb('ciniki.writingcatalog.itemGet', 
            {'tnid':M.curTenantID, 'writingcatalog_id':this.item.writingcatalog_id, 'images':'yes', 'content':'yes'}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_writingcatalog_main.item;
                p.reset();
                p.data = rsp.item;
//              p.sections.description.visible=(rsp.item.description!=null&&rsp.item.description!='')?'yes':'no';
//              p.sections.notes.visible=(rsp.item.notes!=null&&rsp.item.notes!='')?'yes':'no';

                // Setup next/prev buttons
                p.prev_item_id = 0;
                p.next_item_id = 0;
                if( p.list != null ) {
                    for(i in p.list) {
                        if( p.next_item_id == -1 ) {
                            p.next_item_id = p.list[i].item.id;
                            break;
                        } else if( p.list[i].item.id == p.writingcatalog_id ) {
                            // Flag to pickup next item
                            p.next_item_id = -1;
                        } else {
                            p.prev_item_id = p.list[i].item.id;
                        }
                    }
                }
                p.refresh();
                p.show(cb);
            });
    };

    this.refreshItemImages = function() {
        if( M.ciniki_writingcatalog_main.item.writingcatalog_id > 0 ) {
            var rsp = M.api.getJSONCb('ciniki.writingcatalog.get', 
                {'tnid':M.curTenantID, 'writingcatalog_id':M.ciniki_writingcatalog_main.item.writingcatalog_id, 'images':'yes'}, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    var p = M.ciniki_writingcatalog_main.item;
                    p.data.images = rsp.item.images;
                    p.refreshSection('images');
                    p.show();
                });
        }
    };

    this.itemEdit = function(cb, iid) {
        if( iid != null ) { this.edit.writingcatalog_id = iid; }
        M.api.getJSONCb('ciniki.writingcatalog.itemGet', {'tnid':M.curTenantID, 'writingcatalog_id':this.edit.writingcatalog_id, 'categories':'yes'}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_writingcatalog_main.edit;
            p.formtab = null;
            var categories = [];
            for(i in rsp.categories) {
                categories.push(rsp.categories[i].tag.name);
            }
            for(i in p.forms) {
                p.forms[i]._categories.fields.categories.tags = categories;
            }
            p.data = rsp.item;
            p.refresh();
            p.show(cb);
        });
    };

    this.itemSave = function() {
        if( this.edit.writingcatalog_id > 0 ) {
            var c = this.edit.serializeFormData('no');
            if( c != '' ) {
                var nv = this.edit.formFieldValue(this.edit.sections.info.fields.title, 'title');
                if( nv != this.edit.fieldValue('info', 'title') && nv == '' ) {
                    M.alert('You must specifiy a title');
                    return false;
                }
                M.api.postJSONFormData('ciniki.writingcatalog.itemUpdate', 
                    {'tnid':M.curTenantID, 'writingcatalog_id':this.edit.writingcatalog_id}, c,
                        function(rsp) {
                            if( rsp.stat != 'ok' ) {
                                M.api.err(rsp);
                                return false;
                            } else {
                                M.ciniki_writingcatalog_main.edit.close();
                            }
                        });
            } 
        } else {
            var c = this.edit.serializeFormData('yes');
            var nv = this.edit.formFieldValue(this.edit.sections.info.fields.title, 'title');
            if( nv == '' ) {
                M.alert('You must specifiy a title');
                return false;
            }
            M.api.postJSONFormData('ciniki.writingcatalog.itemAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                } else {
                    M.ciniki_writingcatalog_main.edit.close();
                }
            });
        }
    };

    this.itemDelete = function() {
        M.confirm('Are you sure you want to delete \'' + this.item.data.title + '\'?  All information will be removed. There is no way to get the information back once deleted.',null,function() {
            var rsp = M.api.getJSONCb('ciniki.writingcatalog.itemDelete', {'tnid':M.curTenantID, 'writingcatalog_id':M.ciniki_writingcatalog_main.item.writingcatalog_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_writingcatalog_main.item.close();
            });
        });
    };

    this.contentEdit = function(cb, cid, ctype, wid) {
        if( cid != null ) { this.content.content_id = cid; }
        if( wid != null ) { this.content.writingcatalog_id = wid; }
        var args = {'tnid':M.curTenantID, 'content_id':this.content.content_id, 'writingcatalog_id':this.content.writingcatalog_id};
        if( this.content.content_id == 0 && ctype != null ) {
            args['content_type'] = ctype;
        }
        M.api.getJSONCb('ciniki.writingcatalog.contentGet', args, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_writingcatalog_main.content;
            p.reset();
            p.data = rsp.content;
            p.refresh();
            p.show(cb);
        });
    };

    this.contentSave = function() {
        if( this.content.content_id > 0 ) {
            var c = this.content.serializeForm('no');
            if( c != '' ) {
                M.api.postJSONCb('ciniki.writingcatalog.contentUpdate', 
                    {'tnid':M.curTenantID, 'content_id':this.content.content_id}, c, function(rsp) {
                        if( rsp.stat != 'ok' ) {
                            M.api.err(rsp);
                            return false;
                        } else {
                            M.ciniki_writingcatalog_main.content.close();
                        }
                    });
            } else {
                this.content.close();
            }
        } else {
            var c = this.content.serializeForm('yes');
            c += 'writingcatalog_id=' + this.content.writingcatalog_id + '&';
            M.api.postJSONCb('ciniki.writingcatalog.contentAdd', {'tnid':M.curTenantID}, c, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                } else {
                    M.ciniki_writingcatalog_main.content.close();
                }
            });
        }
    };

    this.contentDelete = function() {
        M.confirm('Are you sure you want to delete this content?',null,function() {
            var rsp = M.api.getJSONCb('ciniki.writingcatalog.contentDelete', {'tnid':M.curTenantID, 'content_id':M.ciniki_writingcatalog_main.content.content_id}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                M.ciniki_writingcatalog_main.content.close();
            });
        });
    };

}
