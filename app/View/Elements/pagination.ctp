<p class="paging">
    <?php
    echo $this->Paginator->prev('« ', array(), null, array('class' => 'disabled'));
    $numbers = $this->Paginator->numbers(array('class' => 'numbers'));
    if (empty($numbers)) {
        $numbers = 1; // or any markup you need
    }
    echo $numbers;
    echo $this->Paginator->next(' »', array(), null, array('class' => 'disabled'));
    echo '<br>';
    echo $this->Paginator->counter(
            '({:start}'.__('～'). '{:end} '.__('件').' / {:count} '.__('件'). ')'
    );
    ?>
</p>