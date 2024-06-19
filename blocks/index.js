console.log("The Block js file - Secound Try - 2");

const ntpSettings = window.wc.wcSettings.getSetting( 'netopiapayments_data', {} );
const ntpLabel = window.wp.htmlEntities.decodeEntities( ntpSettings.title ) || window.wp.i18n.__( 'NETOPIA Payments', 'netopiapayments' );

let selectedPaymentMethod = '';

document.addEventListener('change', function(event) {
  if (event.target.name === 'netopia_method_pay') {
    console.log(event.target.value);
    selectedPaymentMethod = event.target.value;
  }
});

/**
 * To show the module description on checkout page
 */
const ntpContent = () => {
    return window.wp.htmlEntities.decodeEntities( ntpSettings.description || '' );
};



/**
 * To show the NETOPIA Payments Methods as readiobox
 * Uncomment the following lines for ntpContent var
 */
//console.log(ntpSettings.payment_methods);

// const ntpContent = () => {
//   const ntpSettings = window.wc.wcSettings.getSetting('netopiapayments_data', {});
//   const ntpLabel = window.wp.htmlEntities.decodeEntities(ntpSettings.title) || window.wp.i18n.__('NETOPIA Payments', 'netopiapayments');
//   const ntpContentText = window.wp.i18n.__('Choose your payment method:', 'netopiapayments');
//   const paymentMethods = ntpSettings.payment_methods || [];

//   const paymentOptions = paymentMethods.map((method) => (
//     React.createElement('li', null,
//       React.createElement('input', {
//         type: 'radio',
//         name: 'netopia_method_pay',
//         id: `netopia_method_pay_${method}`,
//         value: method
//       }),
//       React.createElement('label', { htmlFor: `netopia_method_pay_${method}` }, method.toUpperCase())
//     )
//   ));

//   return React.createElement('div', null,
//     React.createElement('h3', null, ntpLabel),
//     React.createElement('p', null, ntpContentText),
//     React.createElement('ul', null, paymentOptions)
//   );
// };


const ntp_Block_Gateway = {
    name: 'netopiapayments',
    label: ntpLabel,
    content: Object( window.wp.element.createElement )( ntpContent, null ),
    edit: Object( window.wp.element.createElement )( ntpContent, null ),
    canMakePayment: () => true,
    ariaLabel: ntpLabel,
    supports: {
        features: ntpSettings.supports,
    },
};
window.wc.wcBlocksRegistry.registerPaymentMethod( ntp_Block_Gateway );