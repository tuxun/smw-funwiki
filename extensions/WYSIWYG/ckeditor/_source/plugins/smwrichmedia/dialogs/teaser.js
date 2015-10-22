CKEDITOR.dialog.add( 'SMWrichmedia', function( editor ) {

	return {
        title: 'Richmedia extension',
        minWidth : 390,
		minHeight : 230,
        buttons: [
            CKEDITOR.dialog.okButton
        ],
		contents : [
			{
				id : 'tab1',
				label : '',
				title : '',
				expand : true,
				padding : 0,
				elements : [
					{
						type: 'html',
                        html:
                            '<style type="text/css">' +
								'.cke_smwrm_container' +
								'{' +
									'color:#000 !important;' +
									'padding:10px 10px 0;' +
									'margin-top:5px' +
								'}' +
								'.cke_smwrm_container p' +
								'{' +
									'margin: 0 0 10px;' +
								'}' +
								'.cke_smwrm_container a' +
								'{' +
									'cursor:pointer !important;' +
									'color:blue !important;' +
									'text-decoration:underline !important;' +
								'}' +
							'</style>' +
							'<div class="cke_smwrm_container">' +
                                '<p>' +
                                    'You have requested to open the Upload Wizzard, which is helpful if you want to<br/>' +
                                    'upload and tag media in one step.' +
                                '</p>' +
                                '<p>' +
                                    'In order to use this feature you require the "Rich Media extension" which you<br/>'+
                                    'can download here for free: click here to download<br/>'+
                                    '(<a href="http://smwforum.ontoprise.com/smwforum/index.php/Rich_Media_extension">'+
                                    'http://smwforum.ontoprise.com/smwforum/index.php/Rich_Media_extension</a>)' +
                                '</p>' +
                            '</div>'
					}
				 ]
			}
		 ]

	};

} );