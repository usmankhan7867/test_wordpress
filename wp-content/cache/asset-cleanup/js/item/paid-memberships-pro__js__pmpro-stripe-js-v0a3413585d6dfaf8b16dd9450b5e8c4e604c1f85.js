/*!/wp-content/plugins/paid-memberships-pro/js/pmpro-stripe.js*/
var pmpro_require_billing;jQuery(document).ready(function($){var stripe,elements,cardNumber,cardExpiry,cardCvc;if(pmproStripe.user_id){stripe=Stripe(pmproStripe.publishableKey,{stripeAccount:pmproStripe.user_id,locale:'auto'})}else{stripe=Stripe(pmproStripe.publishableKey,{locale:'auto'})}
elements=stripe.elements();cardNumber=elements.create('cardNumber');cardExpiry=elements.create('cardExpiry');cardCvc=elements.create('cardCvc');if($('#AccountNumber').length>0){cardNumber.mount('#AccountNumber')}
if($('#Expiry').length>0){cardExpiry.mount('#Expiry')}
if($('#CVV').length>0){cardCvc.mount('#CVV')}
function pmpro_set_checkout_for_stripe_card_authentication(){$('input[type=submit]',this).attr('disabled','disabled');$('input[type=image]',this).attr('disabled','disabled');$('#pmpro_processing_message').css('visibility','visible')}
if('undefined'!==typeof(pmproStripe.paymentIntent)){if('requires_action'===pmproStripe.paymentIntent.status){pmpro_set_checkout_for_stripe_card_authentication();stripe.handleCardAction(pmproStripe.paymentIntent.client_secret).then(pmpro_stripeResponseHandler)}}
if('undefined'!==typeof(pmproStripe.setupIntent)){if('requires_action'===pmproStripe.setupIntent.status){pmpro_set_checkout_for_stripe_card_authentication();stripe.handleCardSetup(pmproStripe.setupIntent.client_secret).then(pmpro_stripeResponseHandler)}}
if(typeof pmpro_require_billing==='undefined'){pmpro_require_billing=pmproStripe.pmpro_require_billing}
$('.pmpro_form').submit(function(event){if(event.isDefaultPrevented()){return}
if($('input[name="pmpro_level"]').length===0&&$('input[name="level"]').length===0){return}
var name,address;event.preventDefault();if(typeof pmpro_require_billing==='undefined'||pmpro_require_billing){if($('#baddress1').length){address={line1:$('#baddress1').length?$('#baddress1').val():'',line2:$('#baddress2').length?$('#baddress2').val():'',city:$('#bcity').length?$('#bcity').val():'',state:$('#bstate').length?$('#bstate').val():'',postal_code:$('#bzipcode').length?$('#bzipcode').val():'',country:$('#bcountry').length?$('#bcountry').val():'',}}
if($('#bfirstname').length&&$('#blastname').length){name=$.trim($('#bfirstname').val()+' '+$('#blastname').val())}
stripe.createPaymentMethod('card',cardNumber,{billing_details:{address:address,name:name,}}).then(pmpro_stripeResponseHandler);return!1}else{this.submit();return!0}});if($('#payment-request-button').length){var paymentRequest=null;jQuery.noConflict().ajax({url:pmproStripe.restUrl+'pmpro/v1/checkout_level',dataType:'json',data:pmpro_getCheckoutFormDataForCheckoutLevels(),success:function(data){if(data.hasOwnProperty('initial_payment')){paymentRequest=stripe.paymentRequest({country:pmproStripe.accountCountry,currency:pmproStripe.currency,total:{label:pmproStripe.siteName,amount:Math.round(data.initial_payment*100),},requestPayerName:!0,requestPayerEmail:!0,});var prButton=elements.create('paymentRequestButton',{paymentRequest:paymentRequest,});paymentRequest.canMakePayment().then(function(result){if(result){prButton.mount('#payment-request-button')}else{$('#payment-request-button').hide()}});paymentRequest.on('paymentmethod',function(event){$('#pmpro_btn-submit').attr('disabled','disabled');$('#pmpro_processing_message').css('visibility','visible');$('#payment-request-button').hide();event.complete('success');pmpro_stripeResponseHandler(event)})}}});jQuery('form').submit(function(){jQuery('#payment-request-button').hide()});function stripeUpdatePaymentRequestButton(){jQuery.noConflict().ajax({url:pmproStripe.restUrl+'pmpro/v1/checkout_level',dataType:'json',data:pmpro_getCheckoutFormDataForCheckoutLevels(),success:function(data){if(data.hasOwnProperty('initial_payment')){paymentRequest.update({total:{label:pmproStripe.siteName,amount:Math.round(data.initial_payment*100),},})}}})}
if(pmproStripe.updatePaymentRequestButton){$(".pmpro_alter_price").change(function(){stripeUpdatePaymentRequestButton()})}}
function pmpro_stripeResponseHandler(response){var form,data,card,paymentMethodId;form=$('#pmpro_form, .pmpro_form');if(response.error){$('.pmpro_btn-submit-checkout,.pmpro_btn-submit').removeAttr('disabled');$('#pmpro_processing_message').css('visibility','hidden');$('#pmpro_message').text(response.error.message).addClass('pmpro_error').removeClass('pmpro_alert').removeClass('pmpro_success').attr('role','alert').show()}else if(response.paymentMethod){paymentMethodId=response.paymentMethod.id;card=response.paymentMethod.card;form.append('<input type="hidden" name="payment_method_id" value="'+paymentMethodId+'" />');if($('#CardType[name=CardType]').length){$('#CardType').val(card.brand)}else{form.append('<input type="hidden" name="CardType" value="'+card.brand+'"/>')}
form.append('<input type="hidden" name="AccountNumber" value="XXXXXXXXXXXX'+card.last4+'"/>');form.append('<input type="hidden" name="ExpirationMonth" value="'+('0'+card.exp_month).slice(-2)+'"/>');form.append('<input type="hidden" name="ExpirationYear" value="'+card.exp_year+'"/>');form.get(0).submit()}else if(response.paymentIntent||response.setupIntent){$('#pmpro_message').text(pmproStripe.msgAuthenticationValidated).addClass('pmpro_success').removeClass('pmpro_alert').removeClass('pmpro_error').show();paymentMethodId=pmproStripe.paymentIntent?pmproStripe.paymentIntent.payment_method.id:pmproStripe.setupIntent.payment_method.id;card=pmproStripe.paymentIntent?pmproStripe.paymentIntent.payment_method.card:pmproStripe.setupIntent.payment_method.card;if(pmproStripe.paymentIntent){form.append('<input type="hidden" name="payment_intent_id" value="'+pmproStripe.paymentIntent.id+'" />')}
if(pmproStripe.setupIntent){form.append('<input type="hidden" name="setup_intent_id" value="'+pmproStripe.setupIntent.id+'" />')}
form.append('<input type="hidden" name="payment_method_id" value="'+paymentMethodId+'" />');if($('#CardType[name=CardType]').length){$('#CardType').val(card.brand)}else{form.append('<input type="hidden" name="CardType" value="'+card.brand+'"/>')}
form.append('<input type="hidden" name="AccountNumber" value="XXXXXXXXXXXX'+card.last4+'"/>');form.append('<input type="hidden" name="ExpirationMonth" value="'+('0'+card.exp_month).slice(-2)+'"/>');form.append('<input type="hidden" name="ExpirationYear" value="'+card.exp_year+'"/>');form.get(0).submit();return!0}}})
;