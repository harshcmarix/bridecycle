<?php

namespace app\components;

use yii\base\Component;
use Yii;
use yii\helpers\Url;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Exception;
use PayPal\Api\{
    Amount,
    Details,
    Item,
    ItemList,
    Payer,
    Payment,
    RedirectUrls,
    Transaction
};


class PaypalPayment extends Component
{

    /**
     * Use to make (create) Payment (by using subscription)
     * @return Payment
     */
    public function SubscriptionCreatePayment($subscription_package_id, $price, $packageName = null, $ownerId = null, $user_subdcription_id = null)
    {
        // p($subscription_package_id . " " . $price . " " . $packageName . " " . $ownerId . " " . $user_subdcription_id);
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                Yii::$app->params['paypal_client_id'], // ClientID
                Yii::$app->params['paypal_client_secret'] // ClientSecret
            )
        );

        $apiContext->setConfig(['mode' => Yii::$app->params['paypal_mode']]); // sandbox or live

        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $item = new Item();
        $item->setName("Subscription Package is: $packageName")
            //->setCurrency(Yii::$app->params['paypal_payment_currency'])
            ->setCurrency("USD")
            ->setQuantity(1)
            ->setSku("subscription_package_id=" . $subscription_package_id) // Similar to `item_number` in Classic API
            ->setPrice($price);

        $itemList = new ItemList();
        $itemList->setItems(array($item));
        $details = new Details();
        $details->setShipping(0)
            ->setTax(0)
            ->setSubtotal($price);

        $amount = new Amount();
        $amount->setCurrency("USD")
            ->setTotal($price)
            ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("Purchase subscription on " . date('d-m-Y H:i:s') . ".")
            ->setInvoiceNumber(uniqid());

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(Url::to(['/user-subscription/paypal-payment-response', 'is_success' => true, 'subscription_package_id' => $subscription_package_id, 'owner_id' => $ownerId, 'user_subdcription_id' => $user_subdcription_id], true))
            ->setCancelUrl(Url::to(['/user-subscription/paypal-payment-response', 'is_success' => false, 'subscription_package_id' => $subscription_package_id, 'owner_id' => $ownerId, 'user_subdcription_id' => $user_subdcription_id], true));

        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        $request = clone $payment;

        try {
            $payment->create($apiContext);

        } catch (\Exception $ex) {
            // \ResultPrinter::printError("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", null, $request, $ex);
            Yii::$app->session->setFlash('success', "Created Payment Using PayPal. Please visit the URL to Approve. " . "Payment ", $request, $payment);
            exit(1);
        }

        $approvalUrl = $payment->getApprovalLink();

        // \ResultPrinter::printResult("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", "<a href='$approvalUrl' >$approvalUrl</a>", $request, $payment);
        //Yii::$app->session->setFlash('success', "Created Payment Using PayPal. Please visit the URL to Approve. " . "Payment " . "<a href='$approvalUrl' >$approvalUrl</a>", $request, $payment);
        if (!empty($approvalUrl)) {
//            header('Location: ' . $approvalUrl);
            //die();
            $payment = $approvalUrl;
        }

        return $payment;
    }

    /**
     * return paypal api response in json array
     * @return Json array
     */
    public function JsonResponse($data)
    {
        if (!empty($data) && (is_array($data) || is_object($data))) {
            // Paysafecard api response convert into array
            $dataResult['id'] = $data->id;
            $dataResult['intent'] = $data->intent;
            $dataResult['state'] = $data->state;
            $dataResult['cart'] = $data->cart;
            $dataResult['payer']['payment_method'] = $data->getPayer()->getPaymentMethod();
            $dataResult['payer']['status'] = $data->getPayer()->getStatus();
            $dataResult['payer']['payer_info']['email'] = $data->getPayer()->getPayerInfo()->getEmail();
            $dataResult['payer']['payer_info']['first_name'] = $data->getPayer()->getPayerInfo()->getFirstName();
            $dataResult['payer']['payer_info']['last_name'] = $data->getPayer()->getPayerInfo()->getLastName();
            $dataResult['payer']['payer_info']['payer_id'] = $data->getPayer()->getPayerInfo()->getPayerId();
            $dataResult['payer']['payer_info']['shipping_address']['recipient_name'] = $data->getPayer()->getPayerInfo()->getShippingAddress()->getRecipientName();
            $dataResult['payer']['payer_info']['shipping_address']['line1'] = $data->getPayer()->getPayerInfo()->getShippingAddress()->getLine1();
            $dataResult['payer']['payer_info']['shipping_address']['line2'] = $data->getPayer()->getPayerInfo()->getShippingAddress()->getLine2();
            $dataResult['payer']['payer_info']['shipping_address']['city'] = $data->getPayer()->getPayerInfo()->getShippingAddress()->getCity();
            $dataResult['payer']['payer_info']['shipping_address']['state'] = $data->getPayer()->getPayerInfo()->getShippingAddress()->getState();
            $dataResult['payer']['payer_info']['shipping_address']['postal_code'] = $data->getPayer()->getPayerInfo()->getShippingAddress()->getPostalCode();
            $dataResult['payer']['payer_info']['shipping_address']['country_code'] = $data->getPayer()->getPayerInfo()->getShippingAddress()->getCountryCode();
            $dataResult['payer']['payer_info']['country_code'] = $data->getPayer()->getPayerInfo()->getCountryCode();
            $dataResult['transactions'][0]['amount']['total'] = $data->getTransactions()[0]->getAmount()->getTotal();
            $dataResult['transactions'][0]['amount']['currency'] = $data->getTransactions()[0]->getAmount()->getCurrency();
            $dataResult['transactions'][0]['amount']['details']['subtotal'] = $data->getTransactions()[0]->getAmount()->getDetails()->getSubtotal();
            $dataResult['transactions'][0]['amount']['details']['tax'] = $data->getTransactions()[0]->getAmount()->getDetails()->getTax();
            $dataResult['transactions'][0]['amount']['details']['shipping'] = $data->getTransactions()[0]->getAmount()->getDetails()->getShipping();
            $dataResult['transactions'][0]['payee']['merchant_id'] = $data->getTransactions()[0]->getPayee()->getMerchantId();
            $dataResult['transactions'][0]['payee']['email'] = $data->getTransactions()[0]->getPayee()->getEmail();
            $dataResult['transactions'][0]['description'] = $data->getTransactions()[0]->getDescription();
            $dataResult['transactions'][0]['invoice_number'] = $data->getTransactions()[0]->getInvoiceNumber();
            $dataResult['transactions'][0]['item_list']['items'][0]['name'] = $data->getTransactions()[0]->getItemList()->getItems()[0]->getName();
            $dataResult['transactions'][0]['item_list']['items'][0]['sku'] = $data->getTransactions()[0]->getItemList()->getItems()[0]->getSku();
            $dataResult['transactions'][0]['item_list']['items'][0]['price'] = $data->getTransactions()[0]->getItemList()->getItems()[0]->getPrice();
            $dataResult['transactions'][0]['item_list']['items'][0]['currency'] = $data->getTransactions()[0]->getItemList()->getItems()[0]->getCurrency();
            $dataResult['transactions'][0]['item_list']['items'][0]['quantity'] = $data->getTransactions()[0]->getItemList()->getItems()[0]->getQuantity();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['id'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getId();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['state'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getState();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['amount']['total'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getAmount()->getTotal();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['amount']['currency'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getAmount()->getCurrency();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['amount']['details']['subtotal'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getAmount()->getDetails()->getSubtotal();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['payment_mode'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getPaymentMode();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['protection_eligibility'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getProtectionEligibility();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['protection_eligibility_type'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getProtectionEligibilityType();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['transaction_fee']['value'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getTransactionFee()->getValue();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['transaction_fee']['currency'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getTransactionFee()->getCurrency();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['parent_payment'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getParentPayment();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['create_time'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getCreateTime();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['update_time'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getUpdateTime();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['links'][0]['href'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getLinks()[0]->getHref();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['links'][0]['rel'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getLinks()[0]->getRel();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['links'][0]['method'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getLinks()[0]->getMethod();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['links'][1]['href'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getLinks()[1]->getHref();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['links'][1]['rel'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getLinks()[1]->getRel();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['links'][1]['method'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getLinks()[1]->getMethod();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['links'][2]['href'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getLinks()[2]->getHref();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['links'][2]['rel'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getLinks()[2]->getRel();
            $dataResult['transactions'][0]['related_resources'][0]['sale']['links'][2]['method'] = $data->getTransactions()[0]->getRelatedResources()[0]->getSale()->getLinks()[2]->getMethod();
            $dataResult['redirect_urls']['return_url'] = $data->getRedirectUrls()->getReturnUrl();
            $dataResult['redirect_urls']['cancel_url'] = $data->getRedirectUrls()->getCancelUrl();
            $dataResult['create_time'] = $data->getCreateTime();
            $dataResult['update_time'] = $data->getUpdateTime();
            $dataResult['links'][0]['href'] = $data->getLinks()[0]->getHref();
            $dataResult['links'][0]['rel'] = $data->getLinks()[0]->getRel();
            $dataResult['links'][0]['method'] = $data->getLinks()[0]->getMethod();
            return json_encode($dataResult);
        } else {
            echo "{}";
        }
    }
}
