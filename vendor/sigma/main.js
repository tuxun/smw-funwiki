
jQuery(document).ready(function ($) {
	
	$.getJSON( "ajax-v2.php",function( data ) {
		//alert(data);
		//dump(data);
		console.debug(data);
		data_in_graph(data );
	});
	$( ".filter_nb_members" ).change(function() {
		filter_by_nb_members() ;
	});
	$( ".filter_is_nb_members" ).change(function() {
		filter_by_nb_members() ;
	});
	$( ".node-type-check" ).change(function() {
		//alert('hohoho');
		if ($(this).attr('checkedb') == 'checked') {
			$(this).attr('checkedb', 'no');
		} else {
			$(this).attr('checkedb', 'checked');
		}
	
	
		filter_by_nb_members() ;
	});
	$( ".edge-type-check" ).change(function() {
		//alert('hohoho');
		if ($(this).attr('checkedb') == 'checked') {
			$(this).attr('checkedb', 'no');
		} else {
			$(this).attr('checkedb', 'checked');
			
			for (i = 0; i < E; i++) {
			console.debug( i);
				if (g.edges[i].edge_type == $(this).attr('value') ) {
				 
					var an_edge ={
						id: g.edges[i].id,
						source: g.edges[i].start,
						target: g.edges[i].end,
						size: 0.01,
						color: g.edges[i].color,
						hilight: g.edges[i].hilight,
						edge_type: g.edges[i].link_machine_name,
						type: 'arrow',
						hover_color: '#000'
					  };
					 ;
					s.graph.addEdge( an_edge ) ;
				}
			}
		}
	
	
		filter_by_nb_members() ;
	});
});


var s, filter, fa_config, E, g, N, TN, TE;
function filter_by_nb_members() {
		filter
		.undo('non-isolatesx', 'non-isolates', 'non-is-isolatesx', 'non-is-isolates', 'node-type-check-o', 'edge-type-check-o')
    .nodesBy(function(n) {
      return n.nb_members < $('#nb_mem_max').val();
    },'non-isolatesx')
    .nodesBy(function(n) {
      return n.nb_members >= $('#nb_mem_min').val();
    },'non-isolates')
     .nodesBy(function(n) {
      return n.nb_is_members < $('#nb_is_mem_max').val();
    },'non-is-isolatesx')
    .nodesBy(function(n) {
      return n.nb_is_members >= $('#nb_is_mem_min').val();
    },'non-is-isolates')
   .nodesBy(function(n) {
		var node_id_type = '#node-type-'+n.type ;
		
		return 'checked' == $(node_id_type).attr('checkedb');
    },'node-type-check-o')
   .edgesBy(function(n) {
		var edge_id_type = '#edge-type-'+n.edge_type ;
		//console.debug( n) ;
		if ('checked' != $(edge_id_type).attr('checkedb')) {
			  
			  s.graph.dropEdge(n.id) ;
			
		} else {
		
		
		}
		
		
		return 'checked' == $(edge_id_type).attr('checkedb');
    },'edge-type-check-o')
    .apply();
	$('.filter_nb_members').each(function() {
		var id_span = "#"+$(this).attr('id')+"_span" ;
		$(id_span).text($(this).val()) ;
	});
	
	
	if(s.isForceAtlas2Running()) { 
	
	} else {
		s.killForceAtlas2();
		s.startForceAtlas2(fa_config) ;
		setTimeout(function() { s.stopForceAtlas2(); },5000);
	}
}

 function data_in_graph( data ) {
		/*sigma.parsers.json('ajax.php', {
		  container: 'graph'
		});*/
		
		// Generate a random graph:
	
	
	var i,
    N = data.nodes.length
    TN = data.types_nodes.length;
    TE = data.types_edges.length ;
    g = data;	
	E = data.edges.length
		//console.debug(g.nodes);
		var color_type = [] ;
		color_type['Organization'] = '#5cb85c' ;
		 color_type['Person'] = '#337ab7' ;
		 color_type['Place'] = '#333333' ;
		 color_type['Cat-C3-A9gorie-3AProjet'] = '#FF66FF' ;
		 color_type['non-declare'] = '#d9534f' ;

	for (i = 0; i < N; i++) {
		angle = 0.1 * i;
		x=(1+angle)*Math.cos(angle);
		y=(1+angle)*Math.sin(angle);
		
		var type_node = g.nodes[i].type ;
	  g.nodes[i] ={
		id: g.nodes[i].id,
		label: g.nodes[i].label + " "+ g.nodes[i].nb_members + " - " + g.nodes[i].nb_is_members,
		nb_members: g.nodes[i].nb_members,
		nb_is_members: g.nodes[i].nb_is_members,
		type: g.nodes[i].type,
		x: x,
		y: y,
		size: (g.nodes[i].nb_members*800)+ (g.nodes[i].nb_is_members*100),
		color: color_type[type_node]
	  };
	 }
	for (i = 0; i < E; i++) {
	  g.edges[i] ={
		id: g.edges[i].id,
		source: g.edges[i].start,
		target: g.edges[i].end,
		size: 0.01,
		color: g.edges[i].color,
		hilight: g.edges[i].hilight,
		edge_type: g.edges[i].link_machine_name,
		type: 'arrow',
		hover_color: '#000'
	  };
	 
	}
	for (i = 0; i < TE; i++) {
		g.types_edges[i]['color']
		$('#form_types_liens').append('<label style="color:'+g.types_edges[i]['color']+'"><input class="edge-type-check" type="checkbox" id="edge-type-'+g.types_edges[i]['machine_name']+'" name="edge-type-'+g.types_edges[i]['machine_name']+'" value="'+g.types_edges[i]['machine_name']+'" checkedb="checked" checked="checked"> '+g.types_edges[i]['name']+'</label><br/>');
	}
		$('#form_types_liens').append('<hr/>');
	$( ".edge-type-check" ).change(function() {
		
		if ($(this).attr('checkedb') == 'checked') {
			$(this).attr('checkedb', 'no');
		} else {
			$(this).attr('checkedb', 'checked');
			for (i = 0; i < E; i++) {
			//
				if (g.edges[i].edge_type == $(this).attr('value') ) {
				 
					s.graph.addEdge({
						id: g.edges[i].id,
						source: g.edges[i].source,
						target: g.edges[i].target,
						size: 0.01,
						color: g.edges[i].color,
						hilight: g.edges[i].hilight,
						edge_type: g.edges[i].link_machine_name,
						type: 'arrow',
						hover_color: '#000'
					  } ) ;
					s.graph.draw();
					//s.refresh();
				}
			}
			
			//console.debug( s.graph.edges().length);
			
		}
	
	
		filter_by_nb_members() ;
	});
	
	// Instantiate sigma:
	sigma.renderers.def = sigma.renderers.canvas;
	s = new sigma({
		graph: g,
		container: 'graph',
		settings: {
			//doubleClickEnabled: false,
			labelSize: 'proportionnal',
			labelThreshold: 4,
			labelSizeRatio: 1.5,
			 minNodeSize: 2,
			maxNodeSize: 10,
			minEdgeSize: 0.01,
			maxEdgeSize: 0.5,
			//edgeHoverSizeRatio: 5,
			// enableEdgeHovering: true,
			//edgeHoverColor: 'edge',
			//defaultEdgeHoverColor: '#000',
			//edgeHoverSizeRatio: 1,
			//edgeHoverExtremities: true,
		}
	});
	fa_config = {
		linLogMode: false,
		worker: true, 
		outboundAttractionDistribution: true, 
		scalingRatio: 1,
		strongGravityMode: false,
		gravity: 0.1,
		barnesHutOptimize: false,
		
		barnesHutTheta: 100,
		slowDown: 0.1,
		adjustSizes: true,
		//edgeWeightInfluence:0.1,
		
		startingIterations: 1,
		iterationsPerRender: 10
		}
	s.killForceAtlas2() ;
	s.startForceAtlas2(fa_config);
		setTimeout(function() { s.stopForceAtlas2(); },5000);
	//
	
	filter = new sigma.plugins.filter(s);
	
	filter_by_nb_members() ;
	
	var dragListener = sigma.plugins.dragNodes(s, s.renderers[0]);
	
	// Bind the events:
s.bind('overNode outNode clickNode doubleClickNode rightClickNode', function(e) {
  console.log(e.type, e.data.node.label, e.data.captor);
});
s.bind('overEdge outEdge clickEdge doubleClickEdge rightClickEdge', function(e) {
  console.log(e.type, e.data.edge, e.data.captor);
});
s.bind('clickStage', function(e) {
  console.log(e.type, e.data.captor);
});
s.bind('doubleClickStage rightClickStage', function(e) {
  console.log(e.type, e.data.captor);
});
	s.bind('clickNode', function(e) {

		$.ajax({
		  dataType: "json",
		  url: "ajax-infos.php?query="+escape(e.data.node.id)
		}).done(function( data ) {
			console.log(data);
			var d = data ;
			var N = data.infos.length
			for (i = 0; i < N; i++) {
					d.infos[i].propriete = d.infos[i].propriete.replace(/\\/g, '')
				 if (d.infos[i].propriete == 'http://www.w3.org/2000/01/rdf-schema#label') {
					 var nom = d.infos[i].label ;
				 
				 }
				  if (d.infos[i].propriete == 'fiche') {
					 var fiche = d.infos[i].label ;
				 
				 }
				 
				 
				 if (d.infos[i].propriete == 'http://schema.org/address') {
					 var adresse = d.infos[i].label ;
				 
				 }
				  if (d.infos[i].propriete == 'http://smw.coopaxis.fr/id/Attribut-3ACode_APE') {
					 var ape = d.infos[i].label ;
				 
				 }
				 if (d.infos[i].propriete == 'http://smw.coopaxis.fr/id/Attribut-3ASiren') {
					 var siren = d.infos[i].label ;
				 
				 } 
				 if (d.infos[i].propriete == 'http://smw.coopaxis.fr/id/Attribut-3AStatus') {
					 var status = d.infos[i].label ;
				 
				 }
				  if (d.infos[i].propriete == 'http://smw.coopaxis.fr/id/Attribut-3AType') {
					 var type_organisation = d.infos[i].label ;
				 
				 }
				  if (d.infos[i].propriete == 'http://schema.org/url') {
					 var site = d.infos[i].label ;
				 
				 }
				 
			
			
			}
			
			$('#infos_selection').html('<h3>'+nom+'</h3>') ;
			if (typeof adresse != 'undefined') {
				$('#infos_selection').append('<p><span>Adresse:</span> <br/>'+adresse+'</p>') ;
			}
			if (typeof ape != 'undefined') {
				$('#infos_selection').append('<p><span>Code APE :</span> '+ape+'</p>') ;
			}
			if (typeof siren != 'undefined') {
				$('#infos_selection').append('<p><span>Code Siren :</span> '+siren+'</p>') ;
			}
			if (typeof status != 'undefined') {
				$('#infos_selection').append('<p><span>Status :</span> '+status+'</p>') ;
			}
			if (typeof type_organanisation != 'undefined') {
				$('#infos_selection').append('<p><span>Type :</span> '+type_organanisation+'</p>') ;
			}
			if (typeof site != 'undefined') {
				$('#infos_selection').append('<p><span>Site :</span> <a href="'+site+'" target=" blank">'+site+'</a></p>') ;
			}
			if (typeof fiche != 'undefined') {
			//$('#infos_selection').append('<p><span>Fiche wiki :</span> <a href="'+fiche+'" target=" blank">'+fiche+'</a></p>') ;
			}
		});
	});


 }
 
 function dump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }

    //alert(out);

    // or, if you wanted to avoid alerts...

    var pre = document.createElement('pre');
    pre.innerHTML = out;
    document.body.appendChild(pre)
}