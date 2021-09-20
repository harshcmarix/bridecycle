<html>
<head>
    <title>Invoice-<?php echo $order->id ?></title>
</head>
<body>

</br>
<h1><center>Bride Cycle Invoice</center></h1>

</br>
<div style="display:block;width: 100%">
    <p style="display:inline-block;float: left;"><strong>Order Id: </strong><?php echo $order->id ?></p>
    <p style="display:inline-block;float: right;"><strong>Invoice Date: </strong><?php echo $currentDate ?></p>
</div>

</br>
<hr style="border:2px solid grey;display:block;width: 100%;">

</br></br>
<p><strong>Seller Information:</strong></p>
<table class="table table-bordered table-striped" style="width: 100%;border: solid 1px gray;">
    <thead class="thead-dark">
        <tr style="text-align: left;">
            <th style="border-bottom: solid 1px gray;">Shop Name</th>
            <th style="border-bottom: solid 1px gray;">Seller Name</th>
            <th style="border-bottom: solid 1px gray;">Seller Phone</th>
            <th style="border-bottom: solid 1px gray;">Seller Email</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td><?php echo (!empty($sellerDetail)) ? $sellerDetail[0]->shop_name : $seller->first_name . " " . $seller->last_name; ?></td>
            <td><?php echo (!empty($seller)) ? $seller->first_name . " " . $seller->last_name : '-'; ?></td>
            <td><?php echo (!empty($sellerDetail)) ? $sellerDetail[0]['shop_phone_number'] : $seller->mobile; ?></td>
            <td><?php echo (!empty($sellerDetail)) ? $sellerDetail[0]['shop_email'] : $seller->email; ?></td>
        </tr>
    </tbody>
</table>


</br></br>
<p><strong>Product Information:</strong></p>
<table class="table table-bordered table-striped" style="width: 100%;border: solid 1px gray;">
    <thead class="thead-dark">
        <tr style="text-align: left;">
            <th style="border-bottom: solid 1px gray;">Name</th>
            <th style="border-bottom: solid 1px gray;">Purchase Date</th>
            <th style="border-bottom: solid 1px gray;">Shipping Cost</th>
            <th style="border-bottom: solid 1px gray;">SubTotal</th>
            <th style="border-bottom: solid 1px gray;">Total</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td><?php echo $product->name ?></td>
            <td><?php echo $order->created_at ?></td>
            <td><?php echo $model->shipping_cost ?></td>
            <td><?php echo $product->price ?></td>
            <td><?php echo $order->total_amount ?></td>
        </tr>
    </tbody>
</table>

</body>
</html>