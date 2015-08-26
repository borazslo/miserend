$(document).ready(function() {

  $(document).on('click','#quit',function(){
    console.log('click');
      $.ajax({
            url: "ajax.php",
            dataType: "text",
            data: {
              q: 'Exit',
            },
            success: function( data ) {
              location.reload();
            },
            error: function( data ) {
            }
          });
    return false;
  });


  $(document).on('click','.javitva',function(){  
            console.log('ok');
            //event.preventDefault();
            $( this ).nextAll(" .alap:first ").toggle()
  });

  $('[title]').tooltip();

 $( ".emailmenu" ).menu();

        $('#tkereses').on('submit', function(e) { //use on if jQuery 1.7+
            e.preventDefault();  //prevent form from submitting               
            var data = $('#tvaros').val() + '&' + $('#tkulcsszo').val() + '&' + $('#tehm').val();
            ga('send','event','Search','templom',data);
            $(this).unbind('submit').submit();
        });
    
        $('#mkereses').on('submit', function(e) { //use on if jQuery 1.7+
            e.preventDefault();  //prevent form from submitting                                             
            var data = $("#mmikor option:selected").text() + '&' + $('#mmikor2').val() + '&' + $('#mvaros').val() + '&' + $('#mehm').val() + '&' + $('#mnyelv').val() + '&' + $('#mzene').val() + '&' + $('#mdiak').val();
            ga('send','event','Search','mise',data);
            $(this).unbind('submit').submit();
        });

        $('#form_church_getdetails').on('click', function(e) {               
            $('#form_church_details').toggle('slow');
            $('#form_church_getdetails').toggleClass('glyphicon-minus-sign glyphicon-plus-sign');
        });

    $('#form_mass_getdetails').on('click', function(e) {                 
            $('#form_mass_details').toggle('slow');
            $('#form_mass_getdetails').toggleClass('glyphicon-minus-sign glyphicon-plus-sign');            
        });


    $('#password2').on('input', function() { 
        if($('#password1').val() != $(this).val() || $(this).val() == '') {
              $('#password2').parent().find('.form-control-feedback').addClass("glyphicon-warning-sign").removeClass("glyphicon-ok");
              $('#password2').parent().addClass("has-error").removeClass("has-success");

              //$('#passwordcheck').attr("title","A két jelszó nem egyezik!");
        } else {
              $('#password2').parent().find('.form-control-feedback').removeClass("glyphicon-warning-sign").addClass("glyphicon-ok");
              $('#password2').parent().removeClass("has-error").addClass("has-success");

              //$('#password2').parent().find('.form-control-feedback').attr("title","Minden rendben!");
        }
    });
    $('#password1').on('input', function() { 
        if($('#password2').val() != $(this).val() || $(this).val() == '') {
              $('#password2').parent().find('.form-control-feedback').addClass("glyphicon-warning-sign").removeClass("glyphicon-ok");
              $('#password2').parent().addClass("has-error").removeClass("has-success");

              //$('#passwordcheck').attr("title","A két jelszó nem egyezik!");
        } else {
              $('#password2').parent().find('.form-control-feedback').removeClass("glyphicon-warning-sign").addClass("glyphicon-ok");
              $('#password2').parent().removeClass("has-error").addClass("has-success");

              //$('#passwordcheck').attr("title","Minden rendben!");
        }
    });

    $('#username').on('input', function() { 

        $.ajax({
            url: "ajax.php",
            dataType: "text",
            data: {
              q: 'CheckUsername',
              text: this.value
            },
            success: function( data ) {
              if(data == 0) {
                $('#username').parent().find('.form-control-feedback').addClass("glyphicon-warning-sign").removeClass("glyphicon-ok");
                $('#username').parent().addClass("has-error").removeClass("has-success");
              } else {
                $('#username').parent().find('.form-control-feedback').removeClass("glyphicon-warning-sign").addClass("glyphicon-ok");
                $('#username').parent().removeClass("has-error").addClass("has-success");
              }
              console.log(data);
              console.log('ok');
            },
            error: function( data ) {
              console.log(data);
              console.log('1err');
            }
          });


      });

       
      


   $("#varos").autocomplete({
        source: function( request, response ) {
          $.ajax({
            url: "ajax.php",
            dataType: "JSON",
            data: {
              q: 'AutocompleteCity',
              text: request.term
            },
            success: function( data ) {
              //console.log(data);
              //console.log('ok');              
              response( 
                $.map( data.results, function( item ) {
                return {
                      label: item.label,
                      value: item.value
                  }
              }));
            } ,
            error: function( data ) {
              console.log(data);
              //console.log('1err');
            }
          });
        },
        minLength: 2,
       }).each(function() {
          $(this).data("ui-autocomplete")._renderItem = function(ul, item) {
              return $("<li></li>").data("item.ui-autocomplete", item).append(
              item.label)
              .appendTo(ul);
          };
      });    


  });

  $(document).on('click','.massinfo',function(){    
      $( this ).next().toggle('slow');
  });

  // favorites
  $(document).on('click','#star',function(){
    var $this= $(this);

    if($(this).hasClass('grey')) var method = 'add';
    else var method = 'del';
    var tid = $(this).attr("data-tid");

    $.ajax({
       type:"POST",
       url:"ajax.php?q=Favorite",
       data:"tid="+tid+"&method="+method,
       success:function(response){
          $("#star").toggleClass("grey yellow");          
          if($("#star").hasClass('grey')) $("#star").attr('title', 'Kattintásra hozzáadás a kedvencekhez.');
          else $("#star").attr('title', 'Kattintásra törlés a kedvencek közül.');
      }, 
    });
  
  });


   $(document).on('click','.reliable',function(){

      if($(this).hasClass('check')) {
          if($(this).hasClass('lightgrey')) {
                var reliable = 'i';       
          } else {
                var reliable = '?';       
          }
      } else if($(this).hasClass('alert')) {
          if($(this).hasClass('lightgrey')) {
                var reliable = 'n';       
          } else {
                var reliable = '?';       
          }
      } 

      var rid = $(this).parent().parent().attr("data-rid");
      var here = $(this);

      $.ajax({
             type:"POST",
             url:"ajax.php?q=SwitchReliable",
             data:"rid="+rid+"&reliable="+reliable,
             success:function(response){
              console.log(response);
                if(here.hasClass('check')) {
                    if(here.hasClass('lightgrey')) {
                          here.parent().parent().find('.alert').removeClass('red');
                          here.parent().parent().find('.alert').addClass('lightgrey')
                    } 
                    here.toggleClass("lightgrey green");
                } else if(here.hasClass('alert')) {
                    if(here.hasClass('lightgrey')) {
                          here.parent().parent().find('.check').removeClass('green');
                          here.parent().parent().find('.check').addClass('lightgrey')
                    } else {
                    }
                    here.toggleClass("lightgrey red");
                } 

                var email = here.parent().parent().attr('data-email');
                if(email !== '') {
                    $("[data-email='" + email +"']").each(function() {
                        if(reliable == 'i') {
                            $(this).find('.check').removeClass('lightgrey').addClass('green');
                            $(this).find('.alert').removeClass('red').addClass('lightgrey');
                        } else if (reliable == 'n') {
                            $(this).find('.check').removeClass('green').addClass('lightgrey');
                            $(this).find('.alert').removeClass('lightgrey').addClass('red');
                        } else if (reliable == '?') {
                            $(this).find('.check').removeClass('green').addClass('lightgrey');
                            $(this).find('.alert').removeClass('red').addClass('lightgrey');
                        }    
                    });
                }
            }, 
        });        
  




 

/* */
});