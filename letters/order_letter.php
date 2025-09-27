<html>

<head>
    <title>confirm</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        html {
            margin: 0px;
            width: 100%;
        }

        body {
            background-color: rgb(52, 46, 46);
            /* background-color:black; */
            color: white;
            width: 100%;
            margin: 0px;
        }

        .full {
            display: grid;
            padding: 20px;
            /* background-color: rgb(52, 46, 46); */

            background-color: black;
            color: white;
        }

        .c-logo {
            display: grid;
            place-items: center;
            text-align: center;
        }

        .c-logo>a {
            text-align: center;
        }

        .c-logo>a>img {
            height: 50px;
            width: 50px;
            border-radius: 50%;
            margin-bottom: 20px;
        }

        .c-name {
            padding: 10px 5px;
            font-size: 13px;
        }

        .c-pics {
            display: grid;
            place-items: center;
        }

        .c-pics>img {
            height: 250px;
            width: 100%;
            border-radius: 10px;
        }

        .c-msg {
            margin: 20px 5px;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .c-add {
            margin: 20px 5px 0px 5px;
            font-size: 12px;
            text-align: center;
            border: solid 1px rgb(214, 189, 189);
            padding-bottom: 10px;
            border-radius: 10px;
        }

        .socials {
            display: grid;
            place-items: center;
            text-align: center;
        }

        .contact {
            margin: 10px 5px;
            font-size: 11px;
            text-align: center;
            color: rgb(214, 189, 189);
            border-top: 1px solid rgb(214, 189, 189);
            padding-top: 10px;
        }

        .copywright {
            font-size: 11px;
            text-align: center;
            color: rgb(214, 189, 189);
            margin: 5px;
        }

        .social-img {
            height: 40px;
            width: 40px;
            border-radius: 50%;

        }

        .c-note {
            margin: 5px 0px;
            color: blue;
            font-size: blue;
        }

        .prod {
            display: inline-block;
            width: 95%;
            place-items: center;
            margin: 10px 10px;
            white-space: nowrap;
            text-align: center;
        }

        .prod>span {
            /* all: unset; */
            width: 100%;
            overflow-x: scroll;
            text-align: center;
            white-space: nowrap;
            margin-bottom: 2px;
        }

        .prod-img {
            height: 80px;
            width: 80px;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <div class='full'>
        <div class='c-name'>Hello <?php echo ($name); ?> <p>Thanks <?php echo ($name); ?></p>
        </div>
        <div class='c-msg'>Your product <i style='font-size:11px;'>prod-<?php echo ($id); ?></i></div>
        <div class="prod">
            <?php
            foreach ($products as $product) {
                echo "
            <span class='p-image'>
                <img class='prod-img' src='{$product->image}' alt='products'>
            </span>";
            }
            ?>
        </div>


        <div class='c-table'>
            <table style="width:100%;">
                <<tr>
                    <td style="width:30%; background-color:rgb(3, 54, 105); color:white; padding: 8px; text-align: left; font-weight:bold; font-size:11px;">
                        Order item(s)
                    </td>
                    <td style="width:70%; color:white; padding: 8px; text-align: left; font-size:13px; background-color:rgb(57, 87, 117);">
                        <?php
                        foreach ($products as $product) {
                            echo "<p>" . htmlspecialchars($product->name) . " x " . htmlspecialchars($product->quantity) . "</p>";
                        }
                        ?>
                    </td>
                    </tr>


                    <tr>
                        <td style="width:30%; background-color:rgb(3, 54, 105); color:white; padding: 8px; text-align: left; font-weight:bold; font-size:11px;">Order status</td>
                        <td style="width:70%; color:white; padding: 8px; text-align: left; font-size:12px; background-color:rgb(57, 87, 117);"><?php echo ($status); ?></td>
                    </tr>
                    <tr>
                        <td style="width:30%; background-color:rgb(3, 54, 105); color:white; padding: 8px; text-align: left; font-weight:bold; font-size:11px;">Payment method</td>
                        <td style="width:70%; color:white; padding: 8px; text-align: left; font-size:12px; background-color:rgb(57, 87, 117);"><?php echo ($payment_method); ?></td>
                    </tr>


                    <tr>
                        <td style="width:30%; background-color:rgb(3, 54, 105); color:white; padding: 8px; text-align: left; font-weight:bold; font-size:11px;">Total amount</td>
                        <td style="width:70%; color:white; padding: 8px; text-align: left; font-size:12px; background-color:rgb(57, 87, 117);">₦<?php echo (number_format($total)); ?>.00</td>
                    </tr>

                    <tr>
                        <td style="width:30%; background-color:rgb(3, 54, 105); color:white; padding: 8px; text-align: left; font-weight:bold; font-size:11px;">Amount paid</td>
                        <td style="width:70%; color:white; padding: 8px; text-align: left; font-size:12px; background-color:rgb(57, 87, 117);">₦<?php echo (number_format($amountPaid)); ?>.00</td>
                    </tr>

                    <tr>
                        <td style="width:30%; background-color:rgb(3, 54, 105); color:white; padding: 8px; text-align: left; font-weight:bold; font-size:11px;">Balance</td>
                        <td style="width:70%; color:white; padding: 8px; text-align: left; font-size:12px; background-color:rgb(57, 87, 117);">₦<?php echo (number_format($balance)); ?>.00</td>
                    </tr>

            </table>
        </div>

        <p class='copywright'>Copyright © <?php echo date("Y"); ?></p>
    </div>
</body>

</html>
