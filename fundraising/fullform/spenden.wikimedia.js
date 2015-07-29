$(function() {
	/* remove javascript notice rightaway */
	$( '#notice-wrapper' ).remove();

	/* country-specific validation patterns for zip codes */
	var countrySpecifics = {
		generic: {
			'post-code': {
				pattern: '{1,}',
				placeholder: 'z. B. 10117',
				title: 'Postleitzahl'
			},
			city: {
				placeholder: 'z. B. Berlin'
			},
			email: {
				placeholder: 'z. B. name@domain.com'
			}
		},
		DE: {
			'post-code': {
				pattern: '\\s*[0-9]{5}\\s*',
				placeholder: 'z. B. 10117',
				title: 'Fünfstellige Postleitzahl'
			},
			city: {
				placeholder: 'z. B. Berlin'
			},
			email: {
				placeholder: 'z. B. name@domain.de'
			}
		},
		AT: {
			'post-code': {
				pattern: '\\s*[1-9][0-9]{3}\\s*',
				placeholder: 'z. B. 4020',
				title: 'Vierstellige Postleitzahl'
			},
			city: {
				placeholder: 'z. B. Linz'
			},
			email: {
				placeholder: 'z. B. name@domain.at'
			}
		},
		CH: {
			'post-code': {
				pattern: '\\s*[1-9][0-9]{3}\\s*',
				placeholder: 'z. B. 3556',
				title: 'Vierstellige Postleitzahl'
			},
			city: {
				placeholder: 'z. B. Trub'
			},
			email: {
				placeholder: 'z. B. name@domain.ch'
			}
		}
	};

  $(document).ready( function () {
    if ( $( ".amount-custom :text" ).val() !== "" ) {
      $( ".display-amount" ).text( $( ".amount-custom :text" ).val() );
    }

    /* slide toggle */
    function initSlideToggle() {
      $( 'a.slide-toggle').click(function( e ) {
        var $toggle = $(this);

        if ($toggle.hasClass('active')) {
          $($toggle.attr('data-slide-rel'))
            .removeClass('opened')
            .slideUp( 600, checkInvisibleInput )
            .animate(
              { opacity: 0 },
              { queue: false, duration: 600 }
            );

          $toggle.removeClass('active');
        } else {
          $($toggle.attr('data-slide-rel'))
            .addClass('opened')
            .slideDown( 600, checkInvisibleInput )
            .animate(
              { opacity: 1 },
              { queue: false, duration: 600 }
            );

          $toggle.addClass('active');
        }

        e.preventDefault();
      });
    }


    /* check invisible input elems */
    function checkInvisibleInput() {
      // remove required attribute for hidden inputs
      $(':input.required:hidden').removeAttr('required');
      $(':input.required:visible').prop('required',true);
    }


    /* tab toggle */
    function initTabToggle() {
      $( 'a.tab-toggle').click(function( e ) {

        $($(this).attr('data-tab-group-rel')).find('.tab').addClass('no-display');
        $($(this).attr('data-tab-rel')).removeClass('no-display');

        checkInvisibleInput();

        e.preventDefault();
      });
    }


    /* tooltip */
    function initToolTip() {
      /* tooltip */
      $('.tooltip').tooltip({ position: { my: "right-15 center", at: "left center" } });
      $('.tooltip').click(function(e){
        e.preventDefault();
      });
    }


    /* wlightbox inline */
	function initWLightbox() {
  		$('section.container, footer').each(function(){
        	var $container = $(this);

        	if ($container.find('a.wlightbox').length < 1) return true;

        	$container.addClass('temp-show-for-js-calculating');

        	$container.find('a.wlightbox').each(function(){
				$( this ).wlightbox(
					getLightboxOptions(
						$( this ), isFooterElement( $( this ) )
					)
				);
        	});

        	$container.removeClass('temp-show-for-js-calculating')
      	});
	}

	function getLightboxOptions( $lBoxLink, isFooterElement ) {
		if( isFooterElement ) {
			return getOptionsForFooterLink( $lBoxLink );
		} else {
			return getOptionsForSidebarLink( $lBoxLink );
		}
	}

	function getOptionsForFooterLink( $lBoxLink ) {
		var $containerWLightbox = $('#main > .container');
		var position = $lBoxLink.offset().top - $( window ).height();

		return {
			container: $containerWLightbox,
			top: position,
			left: '128px',
			arrowBox: false,
			maxWidth: '686px'
		};
	}

	function getOptionsForSidebarLink( $lBoxLink ) {
		var $containerWLightbox = $('#main > .container');
		var top = $lBoxLink.offset().top - $containerWLightbox.offset().top + $lBoxLink.height();

		return {
			container: $containerWLightbox,
			top: top,
			right: '0px',
			arrowRight: '100px',
			maxWidth: '686px'
		};
	}

	/* determine whether the lightbox link is a child of the footer bar */
	function isFooterElement( $lBoxLink ) {
		return $( '#footer' ).has( $lBoxLink ).length > 0;
	}


    /* radio button toggle */
    function initRadioBtnToggle() {
      $( ':radio' ).change( function( e ){
        var slides = [];
        $(':radio.slide-toggle').each(function(){
          slides.push($(this).attr('data-slide-rel'));
        });

        $(':radio.slide-toggle').each(function(){
          var $slide = $($(this).attr('data-slide-rel'));

          if ($(this).is(':checked') == !$(this).hasClass('slide-toggle-invert')) {

            // show child if child is slide in another slide, prevent flickering / blopping slide children
            $.each(slides, function(index, item){
              if ($slide.has($(item)).length > 0 && $(':radio[data-slide-rel="' + item + '"]').is(':checked') && !$slide.hasClass('opened')) {
                $(item).stop().clearQueue().show().removeAttr('style');
              }
            });

            // open
            $slide
              .addClass('opened')
              .slideDown( 600, checkInvisibleInput )
              .animate(
                { opacity: 1 },
                { queue: false, duration: 600 }
              );
          } else {

            //close
            $slide
              .removeClass('opened')
              .slideUp( 600, checkInvisibleInput )
              .animate(
                { opacity: 0 },
                { queue: false, duration: 600 }
              );
          }

        });

        $( ':radio.tab-toggle').each(function(){
          if ($(this).is(':checked')) {
            $($(this).attr('data-tab-rel')).removeClass('no-display');
          } else {
            $($(this).attr('data-tab-rel'))
              .addClass('no-display');
          }

          checkInvisibleInput();
        });
        $( ':radio.tab-toggle:checked').each(function(){
          $($(this).attr('data-tab-rel'))
            .removeClass('no-display');

          checkInvisibleInput();
        });

      });

      $( ':radio' ).change();
    }


	  $( '#country' ).change( function() {
		  var countryCode = 'generic';
		  if( countrySpecifics[$( this ).val()] ) {
			  countryCode = $( this ).val();
		  }

		  $.each( countrySpecifics[countryCode], function( id, values ) {
			  var $field = $( '#' + id );
			  $.each( values, function( key, value ) {
				  $field.attr( key, value );
			  });
		  });
	  } );

    /* ajax form */
    function initAjaxForm() {
      $('form.ajax-form').each( function(){
        var $form = $(this);

        $form.ajaxForm({
          error: function(){
            $form.find('.message').remove();

            $form.append('<div class="message error">Die Nachricht konnte auf Grund eines Fehlers nicht verschickt werden!</div>');
          },
          success: function(e){
            $form.find('.message').remove();

            $form.append('<div class="message success">Vielen Dank! Die Nachricht wurde verschickt!</div>');
          }
        });
      });
    }



    /* amount-list */
    $('.amount-list').each(function(){
      var $container = $(this);

      $container.find(':radio').change(function(e){
        $('.display-amount').text($container.find(':radio:checked').val());
        if( $container.find( ':radio:checked' ).val() ) {
            $( ".amount-custom :text" ).val( "" );
        }
      });

      $container.find('.amount-custom :text').on('load change keyup paste focus', function() {
        var val = $.trim($(this).val());
        if (val == '') val = 0;
        //val = isNaN(parseInt(val)) ? 0 : parseInt(val);
        $('.display-amount').text(val);
      });

      /* uncheck all list items when user changes custom amount text field */
      $container.find('.amount-custom :text').bind('focus', function(){
        $container.find(':radio').prop('checked', false);
      });
    });


    /* personal data */
    $('#personal-data').each(function(){
      var $container = $(this);

      $container.find( ':radio' ).change( function( e ){
        // check #address-type-3 enable or disable #send-information
        $('#send-information').prop('disabled', $('#address-type-3').is(':checked'));
      });
    });


    /* donation-payment */
    $('#donation-payment').each(function(){
      var $container = $(this);

      /* change title and show related content */
      $container.find('.payment-type-list :radio' ).change( function( e ){

        $container.find('.section-title .h2').addClass('no-display');
        $container.find('.section-title .display-' + $(this).attr('id')).removeClass('no-display');

        $container.find('.tab-group .payment-type .tab').addClass('no-display');
        $container.find('.section-title .display-' + $(this).attr('id')).removeClass('no-display');
      });
    });


    /* become-member-submit */
    $('#become-member-submit').each(function(){
      var $container = $(this);

      /* change title and show related content */
      $container.find('a.button.slide-toggle' ).click(function() {
        $container.find('.box-footer').addClass('border-top');
      });
    });


    initSlideToggle();
    initTabToggle();
    initToolTip();
    initWLightbox();
    initRadioBtnToggle();
  });

//additional methods for form controlling

  $( ".interval-radio" ).click( function() {
    $( "#interval-hidden" ).val( $( "input[name='recurring']:checked" ).val() );
  });

  $( "#periode-1" ).click( function() {
    $( "#interval-hidden" ).val( "0" );
  });

  if( !$( "#periode-2" ).attr( "checked" ) ) $( "#periode-1" ).trigger( "click" );

	$( ".radio-payment" ).change( function(e) {
		if ( e.target.checked ) {
			switch ( $( this ).val() ) {
				case "UEB":
					$( "#donFormSubmit" ).html( 'Jetzt für Wikipedia spenden <span class="icon-ok"></span>' );
					$( "#donFormSubmit" ).attr( "name", "go_prepare--pay:ueberweisung" );
					$( "input[name='form']" ).attr( "value", "" );
					$( "#address-type-3" ).parent().show();
					$( "#tooltip-icon-addresstype" ).show();
					$( "#val-iframe" ).val( "" );
					break;
				case "BEZ":
					$( "#donFormSubmit" ).html( 'Weiter um Spende abzuschließen <span class="icon-ok"></span>' );
					$( "#donFormSubmit" ).attr( "name", "go_prepare--pay:einzug" );
					$( "input[name='form']" ).attr( "value", "10h16_Confirm" );
					$( "#address-type-3" ).parent().hide();
					$( "#tooltip-icon-addresstype" ).hide();
					$( "#val-iframe" ).val( "" );
					break;
				case "PPL":
					$( "#donFormSubmit" ).html( 'Jetzt für Wikipedia spenden <span class="icon-ok"></span>' );
					$( "#donFormSubmit" ).attr( "name", "go_prepare--pay:paypal" );
					$( "input[name='form']" ).attr( "value", "" );
					$( "#address-type-3" ).parent().show();
					$( "#tooltip-icon-addresstype" ).show();
					$( "#val-iframe" ).val( "" );
					break;
				case "MCP":
					$( "#donFormSubmit" ).html( 'Jetzt für Wikipedia spenden <span class="icon-ok"></span>' );
					$( "#donFormSubmit" ).attr( "name", "go_prepare--pay:micropayment-i" );
					$( "input[name='form']" ).attr( "value", "" );
					$( "#address-type-3" ).parent().show();
					$( "#tooltip-icon-addresstype" ).show();
					$( "#val-iframe" ).val( "micropayment-iframe" );
					break;
		}
	}
});


  $( document.commentForm ).on( "submit", function( event ) {
    event.preventDefault();
    var url = "../ajax.php?action=addComment";
    $.ajax( url, {
        data: $( this ).serialize(),
        dataType: "json",
        type: "POST",
        error: function(e){
          $( "#feedback" ).find('.message').remove();
          $( "#feedback" ).append('<div id="negative-feedback" class="message error">Die Nachricht konnte auf Grund eines Fehlers nicht verschickt werden!</div>');
        },
        success: function( response ) {
          $( "#feedback" ).find('.message').remove();
          $( "#feedback" ).append('<div id="positive-feedback" class="message success">' + response.message + '</div>');
        }
    });
  });

  /* trigger hidden membership fee on custom field */
  $('#amount-6').change(function() {
    $('#amount-custom').trigger( 'click' );
  });

  $('#periode-1').click(function() {
    $('#periode-2-list').find(':radio:checked').attr( 'checked', false );
  });

  $('#periode-2').click(function() {
    /*if( $( "periode-2-list > input[type=radio]:checked" ).length === 0 ) {
		$('#periode-2-1').trigger( 'click' );
		$('#periode-2-1').attr( 'checked', 'checked' );
		$( "#interval-hidden" ).val( $( "input[name='recurring']:checked" ).val() );
	}*/
  });

  $( "#donForm" ).bind("reset", function() {
    $( "span.validation" ).each( function() {
      $( this ).removeClass('icon-bug icon-ok');
      $( this ).addClass('icon-placeholder');
      $( '#bank-name' ).text( '' );
    });
    $( "input.invalid, input.valid" ).each( function() {
      $( this ).removeClass('invalid valid');
    });
  });

  $('.amount-list').each(function(){
    if ( $( ".amount-custom :text" ).val() !== "" ) {
      $( ".amount-custom :text" ).trigger( "change" );
    }

    /* periode-1 */
    $('#periode-1').change(function(e){
      if ( e.target.checked ) {
        $('.interval-radio').prop('checked', false);
        $('#interval-display').text($( "label[for='periode-1']" ).text());
      }
    });

    /* periode-2-list */
    $('.periode-2-list').each(function(){
      var $container = $(this);

      $container.find(':radio').change(function(e){
        if ( e.target.checked ) {
          $('#interval-display').text($( "label[for='" + $container.find(':radio:checked').attr('id') + "']" ).text());
        }
      });
    });

    /* periode-2-list */
    $('.payment-type-list').each(function(){
      var $container = $(this);

      $container.find(':radio').change(function(e){
        if ( $container.find( ':radio:checked' ).length > 0 ) {
            $('#payment-display').text(" per " + $( "label[for='" + $container.find(':radio:checked').attr('id') + "']" ).text());
        }
      });
    });
  });

  /* remove dots from custom amount */
  var customAmount = $( "#amount-8" ).val();
  if ( customAmount && customAmount.indexOf(".") >= 0 ) {
    $( "#amount-8" ).val( customAmount.replace( ".", "" ) );
  }

  $( "#address-type-3" ).click( function( e ) {
    var addressForm = $( '#street, #first-name, #last-name, #company-name, #post-code, #email, #city' );
    addressForm.val('').removeClass( "invalid" );
    addressForm.next().removeClass( "icon-bug" ).removeClass( "icon-ok").addClass( "icon-placeholder" );
    $( "#email" ).get( 0 ).setCustomValidity( "" );
  });

  /* trigger male salutation on adresstype person */
  $( "#address-type-1" ).click( function( e ) {
    if ( $( "#salutation-3" ).length > 0 && e.target.checked ) {
      $( "#salutation-2" ).trigger( 'click' );
    }
  });

  /* trigger hidden company salutation on adresstype company */
  $( "#address-type-2" ).click( function( e ) {
    if ( $( "#salutation-3" ).length > 0 && e.target.checked ) {
      $( "#salutation-3" ).trigger( 'click' );
    }
    /* disable amounts less than 100 euros for institutional/corporate members */
    if ( $( '#become-member' ).length > 0 ) {
      $( "#amount-1, #amount-2, #amount-3" ).attr( 'disabled', "disabled" );
    }
  });
  $( "#address-type-1" ).click( function( e ) {
    if ( $( '#become-member' ).length > 0 ) {
      $( "#amount-1, #amount-2, #amount-3" ).attr( 'disabled', false );
    }
  });
  if ( $( '#become-member' ).length > 0 && $( "#address-type-2" ).attr( "checked" ) === "checked" ) {
    $( "#amount-1, #amount-2, #amount-3" ).attr( 'disabled', "disabled" );
  }
  if ( $( '#membership-type-2' ).length > 0 ) {
	  $( "#membership-type-2" ).click( function( e ) {
		  $( "#address-type-2" ).parent().hide();
		  $( "#address-type-1" ).trigger( 'click' );
	  });
	  $( "#membership-type-1" ).click( function( e ) {
		  $( "#address-type-2" ).parent().show();
	  });
  }

	/* initially slide down payment options if params are missing */
	if( $( location ).attr( 'search' ).indexOf( 'expPayOpts=true' ) > 0 ) {
		$('.periode-2-list' ).parent()
			.addClass( 'opened' )
			.slideDown( 100 )
			.animate( { opacity: 1 }, { queue: false, duration: 100 } );
	}
});
