const { createElement, useEffect } = window.wp.element;
const { registerPaymentMethod, usePaymentEvent } = window.wc.wcBlocksRegistry;

const ntpSettings = window.wc.wcSettings.getSetting( 'netopiapayments_data', {} );
const ntpLabel = window.wp.htmlEntities.decodeEntities( ntpSettings.title ) || window.wp.i18n.__( 'NETOPIA Payments', 'netopiapayments' );


let selectedPaymentMethod = 'credit_card'; // Default value

document.addEventListener('change', function(event) {
  if (event.target.name === 'netopia_method_pay') {
     // Just Debuging
    console.log(event.target.value);
    // console.log(ntpSettings);
   
    selectedPaymentMethod = event.target.value;
    const hiddenInput = document.getElementById('netopia_selected_method');
        if (hiddenInput) {
            hiddenInput.value = selectedPaymentMethod;
        }
  } 
});


const ntpContent = (props) => {
  const { eventRegistration, emitResponse } = props;
  const { onPaymentProcessing } = eventRegistration;


  useEffect(() => {
    const unsubscribe = onPaymentProcessing(async () => {
    const customDataIsValid = !!selectedPaymentMethod.length;
      
      /**
       * If the value of "selectedPaymentMethod" is not empty will be pass as "netopia_method_pay" to checkout API
       */
      if (customDataIsValid) {
        return {
          type: emitResponse.responseTypes.SUCCESS,
          meta: {
            paymentMethodData: {
              netopia_method_pay: selectedPaymentMethod,
            },
          },
        };
      }

      return {
        type: emitResponse.responseTypes.ERROR,
        message: 'There was an error',
      };
    });

    return () => {
      unsubscribe();
    };
  }, [onPaymentProcessing, emitResponse]);

  return createElement(
    'div',
    null,
    createElement('div', {
      dangerouslySetInnerHTML: { __html: window.wp.htmlEntities.decodeEntities(ntpSettings.description || '') }
    }),
    createElement('div', {
      dangerouslySetInnerHTML: { __html: ntpSettings.custom_html || '' }
    }),
    createElement('input', {
      type: 'hidden',
      id: 'netopia_selected_method',
      name: 'netopia_selected_method',
      value: selectedPaymentMethod
    })
  );
};

const ntp_Block_Gateway = {
  name: 'netopiapayments',
  label: ntpLabel,
  content: createElement(ntpContent, {}),
  edit: createElement(ntpContent, {}),
  canMakePayment: () => true,
  ariaLabel: ntpLabel,
  supports: {
    features: ntpSettings.supports,
  },
};
  
registerPaymentMethod(ntp_Block_Gateway);