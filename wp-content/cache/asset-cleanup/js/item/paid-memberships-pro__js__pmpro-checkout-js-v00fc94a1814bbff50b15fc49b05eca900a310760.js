/*!/wp-content/plugins/paid-memberships-pro/js/pmpro-checkout.js*/
jQuery(document).ready(function(){if(pmpro.show_discount_code){jQuery('#other_discount_code_toggle').attr('href','javascript:void(0);');jQuery('#other_discount_code_toggle').click(function(){jQuery('#other_discount_code_tr').show();jQuery('#other_discount_code_p').hide();jQuery('#pmpro_other_discount_code').focus()});jQuery('#pmpro_other_discount_code').keyup(function(){jQuery('#pmpro_discount_code').val(jQuery('#pmpro_other_discount_code').val())});jQuery('#pmpro_other_discount_code').blur(function(){jQuery('#pmpro_discount_code').val(jQuery('#pmpro_other_discount_code').val())});jQuery('#pmpro_discount_code').keyup(function(){jQuery('#pmpro_other_discount_code').val(jQuery('#pmpro_discount_code').val())});jQuery('#pmpro_discount_code').blur(function(){jQuery('#pmpro_other_discount_code').val(jQuery('#pmpro_discount_code').val())});jQuery('#other_discount_code_button').click(function(){var code=jQuery('#pmpro_other_discount_code').val();var level_id=jQuery('#pmpro_level').val();if(!level_id){level_id=jQuery('#level').val()}
if(code){jQuery('.pmpro_discount_code_msg').hide();jQuery('#other_discount_code_button').attr('disabled','disabled');jQuery.ajax({url:pmpro.ajaxurl,type:'GET',timeout:pmpro.ajax_timeout,dataType:'html',data:"action=applydiscountcode&code="+code+"&pmpro_level="+level_id+"&msgfield=pmpro_message",error:function(xml){alert('Error applying discount code [1]');jQuery('#other_discount_code_button').removeAttr('disabled')},success:function(responseHTML){if(responseHTML=='error'){alert('Error applying discount code [2]')}else{jQuery('#pmpro_message').html(responseHTML)}
jQuery('#other_discount_code_button').removeAttr('disabled')}})}});jQuery('#discount_code_button').click(function(){var code=jQuery('#pmpro_discount_code').val();var level_id=jQuery('#pmpro_level').val();if(code){jQuery('.pmpro_discount_code_msg').hide();jQuery('#pmpro_discount_code_button').attr('disabled','disabled');jQuery.ajax({url:pmpro.ajaxurl,type:'GET',timeout:pmpro.ajax_timeout,dataType:'html',data:"action=applydiscountcode&code="+code+"&pmpro_level="+level_id+"&msgfield=discount_code_message",error:function(xml){alert('Error applying discount code [1]');jQuery('#pmpro_discount_code_button').removeAttr('disabled')},success:function(responseHTML){if(responseHTML=='error'){alert('Error applying discount code [2]')}else{jQuery('#discount_code_message').html(responseHTML)}
jQuery('#pmpro_discount_code_button').removeAttr('disabled')}})}})}
if(typeof jQuery('#AccountNumber').validateCreditCard=='function'){jQuery('#AccountNumber').validateCreditCard(function(result){var cardtypenames={"amex":"American Express","diners_club_carte_blanche":"Diners Club Carte Blanche","diners_club_international":"Diners Club International","discover":"Discover","jcb":"JCB","laser":"Laser","maestro":"Maestro","mastercard":"Mastercard","visa":"Visa","visa_electron":"Visa Electron"};if(result.card_type)
jQuery('#CardType').val(cardtypenames[result.card_type.name]);else jQuery('#CardType').val('Unknown Card Type')})}
jQuery('form#pmpro_form').submit(function(){jQuery('input[type=submit]',this).attr('disabled','disabled');jQuery('input[type=image]',this).attr('disabled','disabled');jQuery('#pmpro_processing_message').css('visibility','visible')});jQuery('.pmpro_checkout-field').each(function(){var isRequired=jQuery(this).hasClass('pmpro_checkout-field-required')||jQuery(this).find('.pmpro_required').length>0;if(isRequired){var $lastInput=jQuery(this).find('.pmpro_display-field').length?jQuery(this).find('.pmpro_display-field:last').find('input, select').last():jQuery(this).find('input, select').last();if(!$lastInput.nextAll('.pmpro_asterisk').length){$lastInput.after('<span class="pmpro_asterisk"> <abbr title="Required Field">*</abbr></span>')}}});jQuery('.pmpro_checkout-field-radio').each(function(){if(jQuery(this).find('span').hasClass('pmpro_asterisk')){jQuery(this).find(".pmpro_asterisk").remove();jQuery(this).find('label').first().append('<span class="pmpro_asterisk"> <abbr title="Required Field">*</abbr></span>')}});jQuery('span.pmpro_asterisk').each(function(){var prev=jQuery(this).prev();if(prev.is('p')){jQuery(this).insertBefore(prev)}});jQuery('.pmpro_error').bind("change keyup input",function(){jQuery(this).removeClass('pmpro_error')});jQuery('#pmpro_discount_code').keydown(function(e){if(e.keyCode==13){e.preventDefault();jQuery('#pmpro_discount_code_button').click()}});if(pmpro.discount_code_passed_in){jQuery('#pmpro_discount_code_button').hide();jQuery('#pmpro_discount_code').bind('change keyup',function(){jQuery('#pmpro_discount_code_button').show()})}
jQuery('#pmpro_other_discount_code').keydown(function(e){if(e.keyCode==13){e.preventDefault();jQuery('#other_discount_code_button').click()}});jQuery("input[name=submit-checkout]").after('<input type="hidden" name="javascriptok" value="1" />');jQuery('#pmpro_message').bind("DOMSubtreeModified",function(){setTimeout(function(){pmpro_copyMessageToBottom()},200)});function pmpro_copyMessageToBottom(){jQuery('#pmpro_message_bottom').html(jQuery('#pmpro_message').html());jQuery('#pmpro_message_bottom').attr('class',jQuery('#pmpro_message').attr('class'));if(jQuery('#pmpro_message').is(":visible")){jQuery('#pmpro_message_bottom').show()}else{jQuery('#pmpro_message_bottom').hide()}}
if(pmpro.update_nonce){jQuery.ajax({url:pmpro.ajaxurl,type:'POST',data:{action:'pmpro_get_checkout_nonce'}}).done(function(response){jQuery('input[name="pmpro_checkout_nonce"]').val(response)})}});function pmpro_getCheckoutFormDataForCheckoutLevels(){const checkoutFormData=jQuery("#level, #pmpro_level, #discount_code, #pmpro_form .pmpro_alter_price").serializeArray();const sensitiveCheckoutRequestVars=pmpro.sensitiveCheckoutRequestVars;for(var i=0;i<checkoutFormData.length;i++){if(sensitiveCheckoutRequestVars.includes(checkoutFormData[i].name)){checkoutFormData.splice(i,1);i--}}
return jQuery.param(checkoutFormData)}
;