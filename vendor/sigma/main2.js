jQuery(document).ready(function ($) {

sigma.renderers.def = sigma.renderers.canvas;
sigma.parsers.gexf(
    'http://smw.coopaxis.fr/carto/carto.gexf',
    { // Here is the ID of the DOM element that
      // will contain the graph:
      container: 'query-wrapper'
    },
    function(s) {
		var dragListener = sigma.plugins.dragNodes(s, s.renderers[0]);
    }
  );
  
   }
  ); 