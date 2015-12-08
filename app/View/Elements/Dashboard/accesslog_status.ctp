<?php
// get default type
$default_type = "Daily";
// get current date
$current_date = date("Y-m-d");
?>
<div id="type-filter" class="of-none p-b-md">    
    <select id="highchart-fillter" class="">
        <option value="Daily" >Daily</option>
        <option value="Weekly">Weekly</option>
        <option value="Monthly">Monthly</option>
        <option value="Custom">Custom</option>
    </select>
    <div id="prev-button" class="" title="Previous"><a href="#"></a></div>
    <div id="next-button" class="" title="Next"><a href="#"></a></div>
    <div id="period">
        <input type="text" id="from" name="from">
        <label for="to">～</label>
        <input type="text" id="to" name="to">
        <input type="button" id="datePickerBtn" class="imgBtn wide hightlight-btn m-sm" value="<?php echo __('確認')?>"/>
    </div>
</div>

<div id="chart_title"></div>
<div id="chart_date"></div>
<div id="chart_container" ></div>

<script type="text/javascript">
    var current_type = '<?php echo $default_type; ?>';
    var current_date = '<?php echo $current_date; ?>';
    var myNav = 0;

    var buildUrl = function(base, key, value) {
        var sep = (base.indexOf('?') > -1) ? '&' : '?';
        return base + sep + key + '=' + value;
    };
    /**
     * Call ajax to show chart
     *
     * @param {string} ctype current type of chart (daily, weekly, monthly)
     * @param {date} cdate current date
     * @param {int} mNav Type of Navigation button (Previous, Next)
     * @return {boll} true
     */
    function ajaxCall(ctype, cdate, mNav) {
        var data = {type: ctype, mydate: cdate, myNav: mNav};
        var web_root = "<?php echo $this->webroot ?>";
        var now = new Date().getTime();
        var url = buildUrl(web_root + "accesslogs/getstatus", '_t', now);
        $.ajax({
            type: "POST",
            data: data,
            url: url,
            success: function(rs) {
                var elements = JSON.parse(rs);
                var nfc = elements[0];
                var qr = elements[1];
                var total = elements[2];
                var catd = elements[3];
                current_date = elements[4];
                drawLineChart(nfc, qr, total, catd);
            },
            error: function() {
            }
        });
        return true;
    }
    
    function refresh() {
      ajaxCall(current_type,current_date,0);
    }
    
    /**
     * Draw Line chart to show record of NFC, QR and Total
     *
     * @param array [nfc, qr, total, catd]
     */
    function drawLineChart(nfc, qr, total, catd) {
        if ($('#lineChart') !== 'undefined') {
            $('#lineChart').remove();
            $('#chart_container').append('<div id ="lineChart" ></div>');
        } else {
            $('#chart_container').append('<div id ="lineChart" ></div>');
        }
        // Formate data
        catd = catd.split(",");
        nfc = nfc.split(",");
        temp = [];
        for (var i = 0; i < nfc.length; i++) {
            temp[i] = parseFloat(nfc[i]);

        }
        nfc = temp;
        temp = [];
        qr = qr.split(",");
        for (var i = 0; i < qr.length; i++) {
            temp[i] = parseFloat(qr[i]);

        }
        qr = temp;
        temp = [];


        total = total.split(",");
        for (var i = 0; i < total.length; i++) {
            temp[i] = parseFloat(total[i]);

        }
        total = temp;
        temp = [];
        var max = Math.max.apply(Math, total) + 10;
        
        var date_title = '';
          date_obj = new Date(current_date);
        if( current_type == 'Daily') { 
          date_title = current_date;
        } else if ( current_type == 'Weekly' ) {
          date_title =  date_obj.getFullYear() + '-' + (date_obj.getMonth() + 1);
        } else if ( current_type == 'Monthly' ) {
          date_title =  date_obj.getFullYear() + '-' + (date_obj.getMonth() + 1);
        } else {
          date_title = '';
        }
        $('#chart_title').text('<?php echo __('アクセス状況')?>'+' (JST)');
        $('#chart_date').text(date_title);
        
        // draw chart: 
        var lineChart =
                {
                    "graphset": [
                        {
                            "type": "area",
                            "background-color": "#fff",
                            "utc": true,
                            "title": {
                                "y": "7px",
                                "text": "",
                                "background-color": "#fff",
                                "font-size": "24px",
                                "font-color": "#333",
                                "height": "25px"
                            },
                            "plotarea": {
                                "margin": "100px 75px 80px 75px",
                                "background-color": "#fff"
                            },
                            "legend": {
                                "layout": "float",
                                "background-color": "none",
                                "border-width": 0,
                                "shadow": 0,
                                "margin": "35px -130px auto auto",
                                "item": {
                                    "font-color": "#333",
                                    "font-size": "14px"
                                }
                            },
                            "scale-x": {
                                "values": catd,
                                "shadow": 0,
                                "line-color": "#333",
                                "tick": {
                                    "line-color": "#333"
                                },
                                "guide": {
                                    "line-color": "#333"
                                },
                                "item": {
                                    "font-color": "#333"
                                },
                                "minor-ticks": 0,
                                "step":"date",
                                "transform":{
                                    "type":"hour",
                                    "all":"%Hh",
                                    "item":{
                                        "visible":false
                                    }
                                }
                            },
                            "scale-y": {
                                "values": "0:" + max + ":10",
                                "line-color": "#333",
                                "shadow": 0,
                                "tick": {
                                    "line-color": "#333"
                                },
                                "guide": {
                                    "line-color": "#333",
//                                    "line-style": "dashed"
                                },
                                "item": {
                                    "font-color": "#333"
                                },
                                "label": {
                                    "text": "",
                                    "font-color": "#333",
                                    "font-angle": 360,
                                    "offset-y": "-140px",
                                    "offset-x": "40px"
                                },
                                "minor-ticks": 0,
                                "thousands-separator": ","
                            },
//                            "plotarea":{ "background-color":"#fff", "margin-left":"85px" },
                            "crosshair-x": {
                                "line-color": "#333",
                                "value-label": {
                                    "border-radius": "5px",
                                    "border-width": "1px",
                                    "border-color": "#333",
                                    "padding": "5px",
                                    "font-weight": "bold"
                                },
                                "scale-label": {
                                    "font-color": "#00baf0",
                                    "background-color": "#333"
                                }
                            },
                            "tooltip": {
                                "visible": false
                            },
                            "plot": {
                                "tooltip-text": "%t views: %v<br>%k",
                                "alpha-area":0.2,
                                "shadow": 0,
                                "line-width": "3px",
                                "marker": {
                                    "type": "circle",
                                    "size": 3
                                },
                                "multiplier":true,
                                "value-box":{
                                    "visible":true,
                                    "rules":[
                                                {
                                                    "rule":"%v <= 0",
                                                    "visible":"false"
                                                }
                                            ]
                                },
                                "hover-marker": {
                                    "type": "circle",
                                    "size": 4,
                                    "border-width": "1px"
                                }
                            },
                            "series": [
                                {
                                    "values": total,
                                    "text": "Total",
                                    "line-color": "#007790",
                                    "legend-marker": {
                                        "type": "circle",
                                        "size": 5,
                                        "background-color": "#007790",
                                        "font-color": "#333",
                                        "border-width": 1,
                                        "shadow": 0,
                                        "border-color": "#69dbf1"
                                    },
                                    "marker": {
                                        "background-color": "#007790",
                                        "font-color": "#333",
                                        "border-width": 1,
                                        "shadow": 0,
                                        "border-color": "#69dbf1"
                                    }

                                },
                                {
                                    "values": qr,
                                    "text": "QR",
                                    "line-color": "#009872",
                                    "legend-marker": {
                                        "type": "circle",
                                        "size": 5,
                                        "background-color": "#009872",
                                        "border-width": 1,
                                        "shadow": 0,
                                        "border-color": "#69f2d0"
                                    },
                                    "marker": {
                                        "background-color": "#009872",
                                        "border-width": 1,
                                        "shadow": 0,
                                        "border-color": "#69f2d0"
                                    }

                                },
                                {
                                    "values": nfc,
                                    "text": "NFC",
                                    "line-color": "#da534d",
                                    "legend-marker": {
                                        "type": "circle",
                                        "size": 5,
                                        "background-color": "#da534d",
                                        "border-width": 1,
                                        "shadow": 0,
                                        "border-color": "#faa39f"
                                    },
                                    "marker": {
                                        "background-color": "#da534d",
                                        "border-width": 1,
                                        "shadow": 0,
                                        "border-color": "#faa39f"
                                    }

                                }
                            ]
                        }
                    ]
                }

        zingchart.render({
            id: "lineChart",
            data: lineChart,
            height: 400,
            width: "93%"
        });
    }
    $(document).ready(function() {
        $("#from").datepicker({
            defaultDate: "+1w",
            inline: true,
            numberOfMonths: 1,
            dateFormat: 'yy-mm-dd',
            maxDate: 0,
            onClose: function(selectedDate) {
                $("#to").datepicker("option", "minDate", selectedDate);
            }
        });
        $("#to").datepicker({
            defaultDate: "+1w",
            inline: true,
            numberOfMonths: 1,
            dateFormat: 'yy-mm-dd',
            maxDate: 0,
            onClose: function(selectedDate) {
                $("#from").datepicker("option", "maxDate", selectedDate);
            }
        });
        $('#datePickerBtn').click(function() {
            var fromVal = $('#from').val(),
                    toVal = $('#to').val();
            if (fromVal == '' || toVal == '') {
                alert('<?php echo __('Input date should not be empty.') ?>');
                return false;
            } else {
                var data = {from: fromVal, to: toVal},
                web_root = "<?php echo $this->webroot ?>",
                        now = new Date().getTime(),
                        url = buildUrl(web_root + "accesslogs/ajaxDatePicker", '_t', now);
                $.ajax({
                    type: "POST",
                    data: data,
                    url: url,
                    success: function(rs) {
                        $('#highchart-fillter').val('Custom');
                        current_type = $(this).find(':selected').attr('value');
                        var elements = JSON.parse(rs);
                        var nfc = elements[0];
                        var qr = elements[1];
                        var total = elements[2];
                        var catd = elements[3];
                        drawLineChart(nfc, qr, total, catd);
                    },
                    error: function() {
                    }
                });
            }
        });
        // init chart:
        $('#highchart-fillter').val('Daily');
        ajaxCall(current_type, current_date, myNav);
        /*Bind event to Chart and Nav*/
        $('#highchart-fillter').change(function(e) {
            $('#from').val('');
            $('#to').val('');
            e.preventDefault();
            $('#next-button').show();
            current_type = $(this).find(':selected').attr('value');
            var currentdate = new Date();
            crDate = currentdate.getFullYear() + "-" + (currentdate.getMonth() + 1) + "-" + currentdate.getDate();
            myNav = 0;
            ajaxCall(current_type, crDate, myNav);
        });

        $('#prev-button').click(function() {
            myNav = -1;
            ajaxCall(current_type, current_date, myNav);
            $('#next-button').show();
        });

        $('#next-button').click(function(e) {
            /*var currentdate = new Date(),
             crDate = currentdate.getFullYear() + "-" + (currentdate.getMonth() + 1) + "-" + currentdate.getDate();*/
            var today = new Date(),
                    dd = today.getDate(),
                    mm = today.getMonth() + 1, //January is 0!
                    yyyy = today.getFullYear(),
                    current;

            if (dd < 10) {
                dd = '0' + dd
            }

            if (mm < 10) {
                mm = '0' + mm
            }

            current = yyyy + '-' + mm + '-' + dd;

            if (current_date == current)
            {
                console.log(current_date);
                $(this).hide();
                e.preventDefault();
                return;
            }
            myNav = 1;

            ajaxCall(current_type, current_date, myNav);
        })
    });
    setInterval("refresh()",1000*60*2);
</script>

