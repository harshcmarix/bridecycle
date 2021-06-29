<?php

use yii\helpers\Html;

$this->title = 'Dashboard';
?>


<div class="row">
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3><?php echo $totalCustomer ?></h3>

                <p>Total Customer</p>
            </div>
            <div class="icon">
                <i class="fa fa-users"></i>
            </div>
            <!--            <a href="#" class="small-box-footer">-->
            <!--                More info <i class="fa fa-arrow-circle-right"></i>-->
            <!--            </a>-->
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <h3><?php echo $totalCustomerToday ?></h3>

                <p>New Customer <?php echo date('d m,Y') ?></p>
            </div>
            <div class="icon">
                <i class="fa fa-user-circle"></i>
            </div>
            <!--            <a href="#" class="small-box-footer">-->
            <!--                More info <i class="fa fa-arrow-circle-right"></i>-->
            <!--            </a>-->
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3><?php echo $totalProduct ?></h3>

                <p>Total Products</p>
            </div>
            <div class="icon">
                <i class="fa fa-product-hunt"></i>
            </div>
            <!--            <a href="#" class="small-box-footer">-->
            <!--                More info <i class="fa fa-arrow-circle-right"></i>-->
            <!--            </a>-->
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner">
                <h3><?php echo $totalOrder ?></h3>

                <p>Total Order Placed</p>
            </div>
            <div class="icon">
                <i class="fa fa-reorder"></i>
            </div>
            <!--            <a href="#" class="small-box-footer">-->
            <!--                More info <i class="fa fa-arrow-circle-right"></i>-->
            <!--            </a>-->
        </div>
    </div>


    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner">
                <h3><?php echo $totalOrderDeliveredAndCompleted ?></h3>

                <p>Order Delivered and Completed</p>
            </div>
            <div class="icon">
                <i class="fa fa-shopping-cart"></i>
            </div>
            <!--            <a href="#" class="small-box-footer">-->
            <!--                More info <i class="fa fa-arrow-circle-right"></i>-->
            <!--            </a>-->
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3><?php echo $totalOrderPending ?></h3>

                <p>Order Pending</p>
            </div>
            <div class="icon">
                <i class="fa fa-clock-o"></i>
            </div>
            <!--            <a href="#" class="small-box-footer">-->
            <!--                More info <i class="fa fa-arrow-circle-right"></i>-->
            <!--            </a>-->
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <h3>150</h3>

                <p>Ads Clicked </p>
            </div>
            <div class="icon">
                <i class="fa fa-hand-pointer-o"></i>
            </div>
            <!--            <a href="#" class="small-box-footer">-->
            <!--                More info <i class="fa fa-arrow-circle-right"></i>-->
            <!--            </a>-->
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>150</h3>

                <p>Total income</p>
            </div>
            <div class="icon">
                <i class="fa fa-money"></i>
            </div>
            <!--            <a href="#" class="small-box-footer">-->
            <!--                More info <i class="fa fa-arrow-circle-right"></i>-->
            <!--            </a>-->
        </div>
    </div>

</div>

