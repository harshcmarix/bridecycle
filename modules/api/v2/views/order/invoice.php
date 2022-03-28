<!DOCTYPE html>
<html>

<head>
    <title>Bride Cycle Invoice</title>
</head>

<body>
<div class="wrapper">

    <table style="width:100%;padding: 30px 30px;background: #f9f9f9;">
        <tbody>

        <tr>
            <td>
                <h1 style="font-size:28px;margin-bottom:15px;color:#191919;margin-top: 0px;">Bride Cycle</h1>
                <p style="font-size: 13px;margin: 3px 0px;">
                    <?php
                    $sellerName = "-";
                    $sellerContact = "-";
                    $sellerEmail = "-";

                    if (!empty($sellerDetail) && $sellerDetail instanceof \app\models\ShopDetail) {
                        if (!empty($sellerDetail->shop_name)) {
                            $sellerName = $sellerDetail->shop_name;
                        }

                        if (!empty($sellerDetail->shop_phone_number)) {
                            $sellerContact = $sellerDetail->shop_phone_number;
                        }

                        if (!empty($sellerDetail->shop_email)) {
                            $sellerEmail = $sellerDetail->shop_email;
                        }
                    } elseif (!empty($sellerDetail) && $sellerDetail instanceof \app\modules\api\v2\models\User) {
                        if (!empty($sellerDetail->first_name)) {
                            $sellerName = $sellerDetail->first_name . " " . $sellerDetail->last_name;
                        } elseif (!empty($sellerDetail->username)) {
                            $sellerName = $sellerDetail->username;
                        } else {
                            $sellerName = 'Bridecycle User';
                        }

                        if (!empty($sellerDetail->country_code) && !empty($sellerDetail->mobile)) {
                            $sellerContact = $sellerDetail->country_code . $sellerDetail->mobile;
                        }

                        if (!empty($sellerDetail->email)) {
                            $sellerEmail = $sellerDetail->email;
                        }
                    }
                    ?>
                    Name: <?php echo $sellerName ?></p>
                <p style="font-size: 13px;margin: 3px 0px;">
                    Phone: <?php echo $sellerContact ?></p>
                <p style="font-size: 13px;margin: 3px 0px;">
                    Email: <?php echo $sellerEmail ?></p>
                <address style="width:100%;font-style:normal;font-size: 13px;">
                    <!--                    Address: -->
                    <?php //echo isset($sellerAddress->address) ? $sellerAddress->address . ', ' . $sellerAddress->state . ', ' . $sellerAddress->country : '-' ?><!--</address>-->
                    Address: <?php echo isset($sellerAddress->address) ? $sellerAddress->address . ', ' . $sellerAddress->country : '-' ?></address>
            </td>
            <td>
                <img src="data:image/png;base64,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
                        " alt="" title="" style="width: 130px; float: right;vertical-align: middle;"/>
            </td>
        </tr>
        </tbody>
    </table>

    <table style="width:100%;padding: 0px 30px;margin-top: 20px;">
        <tbody>

        <tr>
<!--            <td>-->
<!--                <h2 style="font-size:18px;margin-bottom:5px;color:#191919;">Bill To:</h2>-->
<!--                <p style="font-size: 13px;margin: 3px 0px;">-->
<!--                    Name: --><?php //echo isset($buyerUser->first_name) ? $buyerUser->first_name . ' ' . $buyerUser->last_name : '-' ?><!--</p>-->
<!--                <p style="font-size: 13px;margin: 3px 0px;">-->
<!--                    Phone: --><?php //echo isset($buyerUser->mobile) ? $buyerUser->mobile : '-' ?><!--</p>-->
<!--                <p style="font-size: 13px;margin: 3px 0px;">-->
<!--                    Email: --><?php //echo isset($buyerUser->email) ? $buyerUser->email : '-' ?><!--</p>-->
<!--                <address class="col-5" style="width: 100%;font-style: normal;font-size: 13px;">-->
<!--                    Address: --><?php //echo isset($buyerUserAddress->address) ? $buyerUserAddress->address . ', ' . $buyerUserAddress->state . ', ' . $buyerUserAddress->country : '-' ?><!--</address>-->
<!--            </td>-->

            <td>
                <h2 style="font-size:18px;margin-bottom:5px;color:#191919;">Bill To:</h2>
                <p style="font-size: 13px;margin: 3px 0px;">
                    Name: <?php echo isset($order->name) ? $order->name : '-' ?></p>
                <p style="font-size: 13px;margin: 3px 0px;">
                    Phone: <?php echo isset($order->contact) ? $order->contact : '-' ?></p>
                <p style="font-size: 13px;margin: 3px 0px;">
                    Email: <?php echo isset($order->email) ? $order->email : '-' ?></p>
                <address class="col-5" style="width: 100%;font-style: normal;font-size: 13px;">
                    Address: <?php echo isset($buyerUserAddress->address) ? $buyerUserAddress->address . ', ' . $buyerUserAddress->state . ', ' . $buyerUserAddress->country : '-' ?></address>
            </td>

            <td>
                <table style="margin-left: auto;">
                    <tbody>
                    <tr>
                        <td style="font-size: 18px;">Invoice#</td>
                        <td style="font-size: 18px;"><?php echo isset($model->order_tracking_id) ? $model->order_tracking_id : '-' ?></td>
                    </tr>
                    <tr>
                        <td>Invoice Date:</td>
                        <td><?php echo $currentDate ?></td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>


    <div class="table-wrapper" style="padding: 30px 0;">
        <table class="table" style="width:100%;table-layout:fixed;border: 0;border-collapse: collapse;">
            <tr style="border: 0;border-collapse: collapse;">

                <th class="col-08"
                    style="width:8%;border:0;border-collapse:collapse;text-transform:uppercase;border-bottom:1px solid #f3f3f3;border-top:1px solid #f3f3f3;font-weight:600;padding:12px 0px 12px 30px;color:#2d2d2d;font-size:13px;text-align: right;">
                    SR.
                </th>
                <th class="col-30"
                    style="border: 0;border-collapse: collapse;border-collapse:collapse;text-transform:uppercase;border-bottom:1px solid #f3f3f3;border-top:1px solid #f3f3f3;font-weight:600;padding:12px 0px 12px 30px;color:#2d2d2d;font-size:13px;text-align: right;">
                    Product
                </th>
                <th class="text-right col-08"
                    style="text-align: right;border: 0;border-collapse: collapse;border-collapse:collapse;text-transform:uppercase;border-bottom:1px solid #f3f3f3;border-top:1px solid #f3f3f3;font-weight:600;padding:12px 0px 12px 30px;color:#2d2d2d;font-size:13px;text-align: right;">
                    Size
                </th>
                <th class="text-right col-08"
                    style="text-align: right;border: 0;border-collapse: collapse;border-collapse:collapse;text-transform:uppercase;border-bottom:1px solid #f3f3f3;border-top:1px solid #f3f3f3;font-weight:600;padding:12px 0px 12px 30px;color:#2d2d2d;font-size:13px;text-align: right;">
                    Qty
                </th>
                <th class="text-right col-20"
                    style="text-align: right;width: 20%;border: 0;border-collapse: collapse;border-collapse:collapse;text-transform:uppercase;border-bottom:1px solid #f3f3f3;border-top:1px solid #f3f3f3;font-weight:600;padding:12px 0px 12px 30px;color:#2d2d2d;font-size:13px;text-align: right;">
                    Rate
                </th>
                <th class="text-right col-20"
                    style="text-align: right;width: 20%;border: 0;border-collapse: collapse;border-collapse:collapse;text-transform:uppercase;border-bottom:1px solid #f3f3f3;border-top:1px solid #f3f3f3;font-weight:600;padding:12px 15px 12px 30px;color:#2d2d2d;font-size:13px;text-align: right;">
                    Amount
                </th>
            </tr>

            <tr style="border: 0;border-collapse: collapse;">
                <td class="col-08"
                    style="width: 8%;border: 0;border-collapse: collapse;text-align: right;padding: 7px 0px;">1
                </td>
                <td class="col-30"
                    style="border: 0;border-collapse: collapse;text-align: right;padding: 7px 0px;"><?php echo isset($product->name) ? $product->name : '-'; ?></td>
                <td class="text-right col-08"
                    style="text-align: right;border: 0;border-collapse: collapse;text-align: right;padding: 7px 0px;"><?php echo $model->size; ?></td>
                <td class="text-right col-08"
                    style="text-align: right;border: 0;border-collapse: collapse;text-align: right;padding: 7px 0px;"><?php echo number_format($model->quantity, 2); ?></td>
                <td class="text-right col-20"
                    style="text-align: right;width: 20%;border: 0;border-collapse: collapse;text-align: right;padding: 7px 0px;">

                    <?php
                    $productPrice = $product->referPrice;
                    ?>
                    <?php echo str_replace(".", ',', number_format($productPrice, 2)); ?></td>
                <td class="text-right col-20"
                    style="text-align: right;width: 20%;border: 0;border-collapse: collapse;text-align: right;padding: 7px 15px 7px 0px;"><?php echo str_replace(".", ',', number_format($productPrice * $model->quantity, 2)); ?></td>
            </tr>

            <tr class="total-amount sub-total" style="border: 0;border-collapse: collapse;background: #f9f9f9;">
                <td class="col-5 text-right"
                    style="text-align: right;width: 50%;border: 0;border-collapse: collapse;text-align: right;"
                    colspan="5">
                    <p style="padding-top: 20px;margin:0px;text-transform:uppercase;">Sub Total</p>
                </td>
                <td class="col-20 text-right"
                    style="text-align: right;width: 20%;border: 0;border-collapse: collapse;text-align: right;">
                    <p style="padding-top: 20px;margin:0px;padding-right: 15px;"><?php echo str_replace(".", ',', number_format($productPrice * $model->quantity, 2)); ?></p>
                </td>
            </tr>

            <tr class="total-amount" style="border: 0;border-collapse: collapse;background: #f9f9f9;">
                <td class="col-5 text-right"
                    style="text-align: right;width: 50%;border: 0;border-collapse: collapse;text-align: right;"
                    colspan="5">
                    <p style="padding:3px 0px 3px;font-size:13px;color:#2d2d2d;font-weight:600;text-transform:uppercase;margin:0px;font-size:16px;">
                        Shipping Cost</p>
                </td>
                <td class="col-20 text-right"
                    style="text-align: right;width: 20%;border: 0;border-collapse: collapse;text-align: center;">
                    <p style="padding:3px 0px 3px;margin:0px;text-align: right;padding-right: 15px;"><?php echo str_replace(".", ',', number_format($model->shipping_cost, 2)); ?></p>
                </td>
            </tr>

            <?php if (!empty($product) && $product instanceof \app\models\Product && $product->type == \app\models\Product::PRODUCT_TYPE_NEW) { ?>
                <tr class="total-amount" style="border: 0;border-collapse: collapse;background: #f9f9f9;">
                    <td class="col-5 text-right"
                        style="text-align: right;width: 50%;border: 0;border-collapse: collapse;text-align: right;"
                        colspan="5">
                        <p style="padding:3px 0px 3px;font-size:13px;color:#2d2d2d;font-weight:600;text-transform:uppercase;margin:0px;font-size:16px;">
                            Tax</p>
                    </td>
                    <td class="col-20 text-right"
                        style="text-align: right;width: 20%;border: 0;border-collapse: collapse;text-align: center;">
                        <p style="padding:3px 0px 3px;margin:0px;text-align: right;padding-right: 15px;"><?php echo str_replace(".", ',', number_format($transactionFeesAmount, 2)); ?></p>
                    </td>
                </tr>
            <?php } ?>

            <tr class="total-amount tax" style="border: 0;border-collapse: collapse;background: #f9f9f9;">
                <td class="col-5 text-right"
                    style="text-align: right;width: 50%;border: 0;border-collapse: collapse;text-align: right;"
                    colspan="5">
                    <p style="padding-bottom: 10px;margin:0px;font-size:18px;">Total</p>
                </td>
                <td class="col-20 text-right"
                    style="text-align: right;width: 20%;border: 0;border-collapse: collapse;text-align: center;">
                    <?php if (!empty($product) && $product instanceof \app\models\Product && $product->type == \app\models\Product::PRODUCT_TYPE_NEW) { ?>
                        <p style="padding-bottom: 10px;margin:0px;text-align: right;padding-right: 15px;font-size:18px;"><?php echo str_replace(".", ',', number_format(($order->total_amount - $product->option_price) + $transactionFeesAmount, 2)); ?></p>
                    <?php } else { ?>
                        <p style="padding-bottom: 10px;margin:0px;text-align: right;padding-right: 15px;font-size:18px;"><?php echo str_replace(".", ',', number_format($order->total_amount, 2)); ?></p>
                    <?php } ?>
                </td>
            </tr>

            <tr class="total-amount tax" style="border: 0;border-collapse: collapse;background: #f9f9f9;">
                <td class="col-5 text-right"
                    style="text-align: right;width: 50%;border: 0;border-collapse: collapse;text-align: right;"
                    colspan="5">
                    <p style="padding-top: 50px;padding-bottom: 10px;margin:0px;font-size:20px;color: #2d2d2d;font-weight: bold;">
                        Total</p>
                </td>
                <td class="col-20 text-right"
                    style="text-align: right;width: 20%;border: 0;border-collapse: collapse;text-align: center;">
                    <!--                    <p style="padding-top: 50px;padding-bottom: 10px;margin:0px;text-align: right;padding-right: 15px;font-size:20px;color: #2d2d2d;font-weight: bold;">-->
                    <?php //echo number_format($order->total_amount + $transactionFeesAmount, 2); ?><!--</p>-->
                    <?php if (!empty($product) && $product instanceof \app\models\Product && $product->type == \app\models\Product::PRODUCT_TYPE_NEW) { ?>
                        <p style="padding-top: 50px;padding-bottom: 10px;margin:0px;text-align: right;padding-right: 15px;font-size:20px;color: #2d2d2d;font-weight: bold;"><?php echo str_replace(".", ',', number_format(($order->total_amount - $product->option_price) + $transactionFeesAmount, 2)); ?></p>
                    <?php } else { ?>
                        <p style="padding-top: 50px;padding-bottom: 10px;margin:0px;text-align: right;padding-right: 15px;font-size:20px;color: #2d2d2d;font-weight: bold;"><?php echo str_replace(".", ',', number_format($order->total_amount, 2)); ?></p>
                    <?php } ?>
                </td>
            </tr>
        </table>

    </div>
</div>
</body>

</html>