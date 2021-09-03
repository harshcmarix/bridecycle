<html>
<head>
    <title>Invoice-<?php echo $order->id ?></title>
</head>
<body>
<p>orderId:<?php echo $order->id ?></p>

<p></p>
<p>Seller
    Name: <?php echo (!empty($sellerDetail)) ? $sellerDetail->shop_name : $seller->first_name . " " . $seller->last_name; ?></p>
<p>Seller Phone: <?php echo (!empty($sellerDetail)) ? $sellerDetail->shop_phone_number : $seller->mobile; ?></p>

<p></p>
<p>Product:</p>
<table>
    <tr>
        <th>Name</th>
        <th>Purchase Date</th>
        <th>SubTotal</th>
        <th>Total</th>
    </tr>
    <tr>
        <td><?php echo $product->name ?></td>
        <td><?php echo $order->created_at ?></td>
        <td><?php echo $model->price ?></td>
        <td><?php echo $model->price ?></td>
    </tr>
</table>
</body>
</html>