<?php
$plateData = ($this->request->prefix == 'system') ? $this->requestAction(array('prefix' => 'system', 'controller' => 'accesslogs', 'action' => 'plateStatus')) : $this->requestAction(array('controller' => 'accesslogs', 'action' => 'plateStatus'));
$plateDate = date('M.d');
$plateCrMonth = date('F');
$plateMax = $plateData['monthly'] + 10;
?>
<!--
<h2><?php echo __('プレート稼働状況') ?></h2>

<div class="column access_content">
    <div class="block-pie">
        <?php echo __('今月のプラチナプレート（アクセス1.000超）数：') ?>
        <span class="fs18 red"><?php echo $plateData['platinum'] ?></span>
        <div id="plateChart"></div>
    </div>
</div>
-->

<script type="text/javascript">

    var plateChart =
            {
                "graphset": [
                    {
                        "type": "hbar",
                        "background-color": "transparent",
                        "plot": {
                            "alpha": 0.8,
                            "animation": {
                                "delay": 10,
                                "effect": 4,
                                "speed": "1000",
                                "method": 0,
                                "sequence": "0"
                            }
                        },
                        "scale-x": {
                            "values": ["<?php echo $plateCrMonth ?>", "<?php echo $plateDate ?>"],
                            "line-color": "#55717c",
                            "offset-y": "4px",
                            "tick": {
                                "size": "10px",
                                "line-color": "#55717c",
                                "line-width": "1px",
                                "visible": false
                            },
                            "guide": {
                                "visible": false
                            },
                            "item": {
                                "font-size": "10px",
                                "font-family": "Arial",
                                "font-color": "#333333"
                            },
                        },
                        "scale-y": {
                            "line-color": "none",
                            "values": "0:<?php echo $plateMax ?>:10",
                            "multiplier": true,
                            "guide": {
                                "line-style": "solid",
                                "line-color": "#5e606c",
                                "alpha": 1
                            },
                            "tick": {
                                "visible": false
                            },
                            "item": {
                                "padding-left": "2px",
                                "font-size": "10px",
                                "font-family": "Arial",
                                "font-color": "#333333"
                            },
                        },
                        "plotarea": {"background-color": "#fff", "margin-left": "85px"},
                        "series": [
                            {
                                // monthly - daily
                                "values": [<?php echo intval($plateData['monthly']) . ',' . intval($plateData['daily']) ?>],
                                "background-color": "#57dce5 #1B7E85",
                                "tooltip": {
                                    "background-color": "#1B7E85",
                                    "font-color": "#ffffff",
                                    "border-radius": "6px",
                                    "shadow": false,
                                    "padding": "5px 10px"
                                }
                            }
                        ]
                    }
                ]
            };
    window.onload = function() {
        zingchart.render({
            id: "plateChart",
            data: plateChart,
            height: 200,
            width: "100%"
        });
    }
</script>