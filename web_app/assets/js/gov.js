$( document ).ready(function() {
	/*Aumentar tamaÃ±o de la fuente*/
    $('.max-fontsize').click(function(){
      curSize= parseInt($('html').css('font-size')) + 2;
      if(curSize<=24)
        $('html').css('font-size', curSize);
    }); 
    
    /*Disminuir tamaÃ±o de la fuente*/
    $('.min-fontsize').click(function(){
      curSize= parseInt($('html').css('font-size')) - 2;
      if(curSize>=14)
        $('html').css('font-size', curSize);
    }); 
    
    /*Reiniciar tamaÃ±o de la fuente*/
    $('#a_normal').click(function(){
        $('html').css('font-size', 'initial');
    }); 
	
	$(".contrast-ref").click(function(){
  if($("html").hasClass("contrast")){	
	$("html").removeClass("contrast");
	$("html").css({ "-webkit-filter" : '', 'filter' : '' , 'background-color' : '' } );
  }else{
	$("html").addClass("contrast");
	$("html").css("-webkit-filter","invert(1)");
	$("html").css("filter","invert(1)");
	$("html").css("background-color","#fff");
	
  }
	});
	
});