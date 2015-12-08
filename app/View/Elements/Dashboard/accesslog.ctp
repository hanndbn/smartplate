<?php

App::uses('AccessLogController', 'Controller');

if (isset($detail)) {
    $osData = $this->requestAction(array('controller' => 'accesslogs', 'action' => 'getDataInDetail'));
} else {
    $osData = $this->requestAction(array('controller' => 'accesslogs', 'action' => 'getalldata'));
}

?>
<div class="of-none">

    <div class="column access_status">
        <div class="access_wrap">
            <!--Count Access to day- this month-Total-->
            <?php echo __('本日'); ?>:<span class="fs18 red"><?php echo $osData['daily'] ?></span>　
            <?php echo __('当月'); ?>:<span class="fs18 red"><?php echo $osData['monthly'] ?></span>　 
            <?php echo __('累計'); ?>:<span class="fs18 red"><?php echo $osData['total'] ?></span>
        </div>
        <div class="row">
            <div id="canvas-holder" class="col-md-6">
                <div id="osChart"></div>
            </div>
            <!--Use Pie chart-->
            <div id="bar-holder" class="col-md-6">
                <div id="accessLogChart"></div>
            </div>
        </div>
    </div>
</div>
<!--Use Pie chart-->
<script type="text/javascript">

    var osChart =
            {
                "type": "pie",
                "background-color": "transparent",
                "title": {
                    "background-color": "none",
                    "font-weight": "normal",
                    "font-family": "Arial",
                    "font-color": "#ffffff",
                    "height": "40px"
                },
                "plot": {
                    "animation": {
                        "delay": 0,
                        "effect": 2,
                        "speed": "300",
                        "method": "0",
                        "sequence": "1"
                    },
                    "value-box":{
                        "placement":"in",
                        "connected":true,
                        "text":"%t : %v"
                    },
                    //"slice":35,
                },
                "series": [
                    {
                        "text": "Android",
                        "values": [<?php echo intval($osData['android']) ?>],
                        "background-color": "#7BE861",
                        "border-color": "#A8A8AD",
                        "border-width": "0px",
                        "shadow": 0,
                        "tooltip": {
                            "background-color": "#F06E6E",
                            "font-color": "#ffffff",
                            "border-radius": "6px",
                            "shadow": false,
                            "padding": "5px 10px"
                        },
                    },
                    {
                        "text": "iPhone",
                        "values": [<?php echo intval($osData['iphone']) ?>],
                        "background-color": "#F06E6E",
                        "border-color": "#A8A8AD",
                        "border-width": "0px",
                        "shadow": 0,
                        "tooltip": {
                            "background-color": "#7BE861",
                            "font-color": "#ffffff",
                            "border-radius": "6px",
                            "shadow": false,
                            "padding": "5px 10px"
                        }
                    },
                    {
                        "text": "Other",
                        "values": [<?php echo intval($osData['other']) ?>],
                        "background-color": "#7A7DE6",
                        "border-color": "#A8A8AD",
                        "border-width": "0px",
                        "shadow": 0,
                        "tooltip": {
                            "background-color": "#7A7DE6",
                            "font-color": "#ffffff",
                            "border-radius": "6px",
                            "shadow": false,
                            "padding": "5px 10px"
                        }
                    }
                ]
            }

    var accessLogChart =
            {
                "type": "pie",
                "background-color": "transparent",
                "title": {
                    "background-color": "none",
                    "font-weight": "normal",
                    "font-family": "Arial",
                    "font-color": "#ffffff",
                    "height": "40px"
                },
                "plot": {
                    "animation": {
                        "delay": 0,
                        "effect": 2,
                        "speed": "300",
                        "method": "0",
                        "sequence": "1"
                    },
                    "value-box":{
                        "placement":"in",
                        "connected":true,
                        "text":"%t : %v"
                    }
                },
                "series": [
                    {
                        "text": "NFC",
                        "values": [<?php echo intval($osData['nfc']) ?>],
                        "background-color": "#F06E6E",
                        "border-color": "#A8A8AD",
                        "border-width": "0px",
                        "shadow": 0,
                        "tooltip": {
                            "background-color": "#F06E6E",
                            "font-color": "#ffffff",
                            "border-radius": "6px",
                            "shadow": false,
                            "padding": "5px 10px"
                        },
                    },
                    {
                        "text": "QR",
                        "values": [<?php echo intval($osData['qr']) ?>],
                        "background-color": "#7BE861",
                        "border-color": "#A8A8AD",
                        "border-width": "0px",
                        "shadow": 0,
                        "tooltip": {
                            "background-color": "#7BE861",
                            "font-color": "#ffffff",
                            "border-radius": "6px",
                            "shadow": false,
                            "padding": "5px 10px"
                        }
                    }
                ]
            }

    zingchart.render({
        id: "osChart",
        data: osChart,
        height: 250,
        width: "100%"
    });
    zingchart.render({
        id: "accessLogChart",
        data: accessLogChart,
        height: 250,
        width: "100%"
    });


</script>

