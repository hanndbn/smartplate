<div id="main" class="list">
    <?php
    echo $this->Form->create('Bookmark', array('type' => 'file'));
    ?>
    <table style="width: 100%;">
        <tbody>   
            <tr>
                <th class="typeB">種別</th>
                <td><?php
                    echo $this->Form->input('Bookmark.kind', array('options' => array(
                            0 => 'Youtube',
                            1 => 'Facebook',
                            2 => 'Twitter',
                            3 => 'LINE',
                            4 => 'Google+',
                            5 => 'FourSquare'), 'label' => FALSE));
                    ?></td>
            </tr>
            <tr>
                <th class="typeB">名前</th>
                <td><?php echo $this->Form->input('Bookmark.name', array('type' => 'text', 'label' => FALSE)); ?></td>
            </tr>
            <tr>
                <th class="typeB">ラベル</th>
                <td>
                    <select name="label"><option value=""></option><option value="K&amp;D Corporate">K&amp;D Corporate</option><option value="K&amp;D Products">K&amp;D Products</option><option value="K&amp;D Connected Gift">K&amp;D Connected Gift</option><option value="Tomo Hagiwara">Tomo Hagiwara</option><option value="Aquabit Spirals">Aquabit Spirals</option><option value="App">App</option><option value="Phone Call">Phone Call</option><option value="Contact">Contact</option><option value="Check-In">Check-In</option><option value="SmartPlate">SmartPlate</option><option value="Other Products">Other Products</option><option value="Android">Android</option></select>      
                    <input type="text" name="Label['label']">    
                </td>
            </tr>
            <tr>
                <th class="typeB">バーコード</th>
                <td><?php echo $this->Form->input('Bookmark.code', array('type' => 'text', 'label' => FALSE)); ?></td>
            </tr>
            <?php echo $this->Form->input('Bookmark.team_id', array('type' => 'hidden', 'value' => 1, 'label' => FALSE)); ?>
            <tr>
                <th class="typeB">リンク先</th>
                <td><?php echo $this->Form->input('Bookmark.url', array('type' => 'text', 'label' => FALSE)); ?></td>
            </tr>

            <tr>
                <th class="typeB">有効</th>
                <td><?php echo $this->Form->input('Bookmark.visible', array('type' => 'checkbox', 'checked' => true, 'label' => FALSE)); ?></td>
            </tr>

            <tr>
                <th class="typeB">位置情報取得</th>
                <td><?php echo $this->Form->input('Bookmark.gps', array('type' => 'checkbox', 'checked' => false, 'label' => FALSE)); ?></td>
            </tr>

            <tr>
                <th class="typeB">Image</th>
                <td><?php echo $this->Form->input('Bookmark.image', array('type' => 'file', 'label' => FALSE)); ?></td>
            </tr>

            <tr>
                <td style="text-align:center;" colspan="2">
                    <input type="submit" class="imgBtn wide" value="登録する">
                </td>
            </tr>
        </tbody></table>
</div>