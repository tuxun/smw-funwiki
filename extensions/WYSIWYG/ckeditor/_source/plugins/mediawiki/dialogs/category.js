//
// File: WYSIWYG/ckeditor/plugins/mediawiki/dialogs/category.js
//
// Versions:
// 08.01.14 RL  More advanced dialog based on link.js, selection list for existing categories
//
CKEDITOR.dialog.add( 'MWCategory', function( editor ) {
{
    // need this to use the getSelectedLink function from the plugin
    var plugin = CKEDITOR.plugins.link;
    var searchTimer;
	var OnCategoryChange = function() {

        var dialog = this.getDialog();

        var StartSearch = function() {
            var	e = dialog.getContentElement( 'mwCategoryTab1', 'categoryValue' ),
                value = e.getValue().Trim().replace(/ /g,'_'); //12.12.14 RL ' '=>'_'

			//Commented out to get list of categories when dialog is opened.
            //if ( value.length < 1  )
            //         return ;

            SetSearchMessage( editor.lang.mwplugin.searching ) ;

            // Make an Ajax search for the categories.
            window.parent.sajax_request_type = 'GET' ;
            window.parent.sajax_do_call( 'wfSajaxSearchCategoryCKeditor', [value], LoadSearchResults ) ;
			
        }

        var LoadSearchResults = function ( result ) {
            var results = result.responseText.split( '\n' ),
                select = dialog.getContentElement( 'mwCategoryTab1', 'categoryList' );

            ClearSearch() ;

            if ( results.length == 0 || ( results.length == 1 && results[0].length == 0 ) ) {
                SetSearchMessage( editor.lang.mwplugin.noCategoryFound ) ;
            }
            else {
                if ( results.length == 1 )
                    SetSearchMessage( editor.lang.mwplugin.oneCategoryFound ) ;
                else      //List contains one extra line... .why ?????
                    SetSearchMessage( results.length - 1  + editor.lang.mwplugin.manyCategoryFound ) ;

                for ( var i = 0 ; i < results.length ; i++ )
                    select.add ( results[i], results[i] );  //Is this correct?
					//select.add ( results[i].replace(/_/g, ' '), results[i] );
            }
        }

        var ClearSearch = function() {
            var	e = dialog.getContentElement( 'mwCategoryTab1', 'categoryList' );
            e.items = [];
            var div = document.getElementById(e.domId),
                select = div.getElementsByTagName('select')[0];
            while ( select.options.length > 0 )
                select.remove( 0 )
        }

        var SetSearchMessage = function ( message ) {
            var	e = dialog.getContentElement( 'mwCategoryTab1', 'searchMsg' );
            e.html = message;
            document.getElementById(e.domId).innerHTML = message;
        }

        var e = dialog.getContentElement( 'mwCategoryTab1', 'categoryValue' );
		var category = e.getValue().Trim();

        if ( searchTimer )
            window.clearTimeout( searchTimer ) ;
        
        //Commented out to get list of categories when dialog is opened.
        //if ( category.length < 1 ) {
        //    ClearSearch() ;
        //    SetSearchMessage( editor.lang.mwplugin.startTyping ) ;
        //    return ;
        //}

        SetSearchMessage( editor.lang.mwplugin.stopTyping ) ;
        searchTimer = window.setTimeout( StartSearch, 500 ) ;

    }
    var CategorySelected = function() {
        var dialog = this.getDialog(),
            target = dialog.getContentElement( 'mwCategoryTab1', 'categoryValue' ),
            select = dialog.getContentElement( 'mwCategoryTab1', 'categoryList' );
        //target.setValue(select.getValue().replace(/_/g, ' '));
		target.setValue(select.getValue());
    }
	var loadElements = function( editor, selection, element ) {

	    var category = null;
		var sortkey = null;
		
		element.editMode = true;

		//Get values of category and sort key 
		category = element.getText().replace(/ /g, '_');  //08.09.14 RL Added replace
		if ( element.hasAttribute('sort') ) { //12.12.14 RL
			sortkey = element.getAttribute('sort');
		}

        if ( category.length > 0 )
         this.setValueOf( 'mwCategoryTab1','categoryValue', category );
        else
         this.setValueOf( 'mwCategoryTab1','categoryValue', "" );

        if ( sortkey.length > 0 )
          this.setValueOf( 'mwCategoryTab1','sortkeyValue', sortkey );
        else
          this.setValueOf( 'mwCategoryTab1','sortkeyValue', "" );
	}
	   
        return {
            title : editor.lang.mwplugin.categoryTitle, //'Add/modify category'
            minWidth : 350,
            minHeight : 500,
            resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
			contents : [
				{
					id : 'mwCategoryTab1',
					label : 'Add category',
                    title : 'Add category',
					elements :
					[
                        {
                            id: 'categoryValue',
                            type: 'text',
                            label: editor.lang.mwplugin.category,
                            title: 'Write name of category',
                            style: 'border: 1px;',
                            onKeyUp: OnCategoryChange,
							onChange: OnCategoryChange  
							// ^---onChange was the only way to get list of categories updated when dialog was opened.
							// Is there better way to do initial update of category list???
                        },
                        {
                            id: 'searchMsg',
                            type: 'html',
                            style: 'font-size: smaller; font-style: italic;',
                            html: editor.lang.mwplugin.startTyping
                        },
						{
							id : 'sortkeyValue',
							type : 'text',
							label : editor.lang.mwplugin.categorySort,
							title : 'Sort key',
							style: 'border: 1px;',
							setup: function(element){
								this.setValue(element.getAttribute('sortkeyValue'));
							}
						}, 
                        {
                            id: 'categoryList',
                            type: 'select',
                            size: 25,
                            label: editor.lang.mwplugin.selfromCategoryList, //'Select from list of existing categories:'
                            title: 'Category list',
                            required: false,
                            style: 'border: 1px; width:100%;',
                            onChange: CategorySelected,
                            items: [  ]
                        }
		            ]
                }
            ],

            onOk : function() {

                //var editor = this.getContentElement( 'mwCategoryTab1', 'categoryValue'),
                //    link = editor.getValue().Trim().replace(/ /g, '_'),
                //    attributes = {href : link, _cke_saved_href : link};
				
				var editor = this.getParentEditor();
				var category = this.getValueOf( 'mwCategoryTab1', 'categoryValue' ).Trim().replace(/_/g,' ');
				var sortkey  = this.getValueOf( 'mwCategoryTab1', 'sortkeyValue' ).Trim().replace(/_/g,' ');

				//Build html syntax fox category like this:
				//  <span> class="fck_mw_category" sort="SName">CName</span>
				//  <span> _fcknotitle="true" class="fck_mw_category" sort="PName">CName</span>
	
				if ( category.length > 0 ) {
					var realElement = CKEDITOR.dom.element.createFromHtml('<span></span>');

					//Name FCK class for category element.	
					realElement.setAttribute('class','fck_mw_category');					
					
					//User gave only name of category => set sort key to be eq. to first letter of page name
					if ( sortkey.length == 0 ) {
						sortkey = mw.config.get( 'wgPageName' ).trim().substr(0,1).toUpperCase(); //12.12.14 RL
						realElement.setAttribute('_fcknotitle','true'); //12.12.14 RL
					}	

					if ( sortkey.length > 0 )
						realElement.setAttribute( 'sort', sortkey ); 
						
					//Name of category	 
					if ( category.length > 0 )
						realElement.setText( category );
											
					//Are there any additional attributes used by html format?  
					var fakeElement = editor.createFakeElement( realElement , 'FCK__MWCategory', 'span', false );
					editor.insertElement(fakeElement);
				}
            },

    		onShow : function()
        	{
				// clear old selection list from a previous call
                var editor = this.getParentEditor(),
                    e = this.getContentElement( 'mwCategoryTab1', 'categoryList' );
                    e.items = [];
                var div = document.getElementById(e.domId),
                    select = div.getElementsByTagName('select')[0];
                while ( select.options.length > 0 )
                    select.remove( 0 );
                e = this.getContentElement( 'mwCategoryTab1', 'searchMsg' );
                var message = editor.lang.mwplugin.startTyping;
                e.html = message;
                document.getElementById(e.domId).innerHTML = message;
	
				/*This was taken from first simple dialog for category definitions.*/
				this.editObj = false;
				this.fakeObj = false;
				this.editMode = false;
		
				var selection = editor.getSelection();
				var ranges = selection.getRanges();
				var element = selection.getSelectedElement();
				var seltype = selection.getType();

				//12.12.14 RL CKEDITOR.SELECTION_NONE=0 (no selection), CKEDITOR.SELECTION_TEXT=2, CKEDITOR.SELECTION_ELEMENT=3
				if ( (seltype == CKEDITOR.SELECTION_TEXT || seltype == CKEDITOR.SELECTION_ELEMENT) && element.getAttribute( 'class' ) == 'FCK__MWCategory' )
				{
					this.fakeObj = element;
					element = editor.restoreRealElement( this.fakeObj );
					loadElements.apply( this, [ editor, selection, element ] );
					selection.selectElement( this.fakeObj );
				}
				else if ( seltype == CKEDITOR.SELECTION_TEXT )
				{
                    //if ( CKEDITOR.env.ie ) //27.02.14 RL->				                                            //09.09.14 RL->   
                    //    this.setValueOf( 'mwCategoryTab1','categoryValue', selection.document.$.selection.createRange().text ); 
                    //else                   //27.02.14 RL<- 
                    //    this.setValueOf( 'mwCategoryTab1','categoryValue', selection.getNative() );

                    this.setValueOf( 'mwCategoryTab1','categoryValue', selection.getSelectedText().replace(/ /g,'_') ); //09.09.14 RL<- 
                }
				this.getContentElement( 'mwCategoryTab1', 'categoryValue' ).focus();
        	}
        }
}
} );
