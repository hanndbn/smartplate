<?php
$conetentData = ($this->request->prefix == 'system') ? $this->requestAction(array('prefix' => 'system', 'controller' => 'accesslogs', 'action' => 'contentStatus')) : $this->requestAction(array('controller' => 'accesslogs', 'action' => 'contentStatus'));
$conetentDate = date('M.d');
$conetentCrMonth = date('F');
$conetentMax = $conetentData['monthly'] + 10;
?>
<!--
<h2><?php echo __('コンテンツ稼働状況') ?></h2>

<div class="column access_content">
    <div id="contentChart"></div>
</div>
-->
<script type="text/javascript">

    var contentChart =
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
                            "values": ["<?php echo $conetentCrMonth ?>", "<?php echo $conetentDate ?>"],
                            "line-color": "#55717c",
                            "offset-y": "1px",
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
                            "values": "0:<?php echo $conetentMax ?>:10",
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
                                "values": [<?php echo intval($conetentData['monthly']) . ',' . intval($conetentData['daily']) ?>],
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

        zingchart.render({
            id: "contentChart",
            data: contentChart,
            height: 200,
            width: "100%"
        });
    
</script>