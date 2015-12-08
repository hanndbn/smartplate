<div class="alert <?php echo $class; ?> alert-dismissible m-t-md">
    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    <?php
    if (is_array($message)) {
        foreach ($message as $ms) {
            echo $ms . "</br>";
        }
    } else {
        echo $message;
    }
    ?>
</div>

<script>
    $(function() {
//    $(".alert").fadeTo(4000, 500).slideUp(500, function(){
//        $(".alert").alert('close');
//    });
    })    
</script>