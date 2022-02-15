<?php
    $items = array(
        array(
            'up' => '1',
            'down' => '&nbsp;',
            'clazz' => 'element',
            'attribute'=> 'data-type="number"'
        ),
        array(
            'up' => '2',
            'down' => 'ABC',
            'clazz' => 'element',
            'attribute'=> 'data-type="number"'
        ),
        array(
            'up' => '3',
            'down' => 'DEF',
            'clazz' => 'element',
            'attribute'=> 'data-type="number"'
        ),
        array(
            'up' => '4',
            'down' => 'GHI',
            'clazz' => 'element',
            'attribute'=> 'data-type="number"'
        ),
        array(
            'up' => '5',
            'down' => 'JKL',
            'clazz' => 'element',
            'attribute'=> 'data-type="number"'
        ),
        array(
            'up' => '6',
            'down' => 'MNO',
            'clazz' => 'element',
            'attribute'=> 'data-type="number"'
        ),
        array(
            'up' => '7',
            'down' => 'PQRS',
            'clazz' => 'element',
            'attribute'=> 'data-type="number"'
        ),
        array(
            'up' => '8',
            'down' => 'TUV',
            'clazz' => 'element',
            'attribute'=> 'data-type="number"'
        ),
        array(
            'up' => '9',
            'down' => 'WXYZ',
            'clazz' => 'element',
            'attribute'=> 'data-type="number"'
        ),
        array(
            'up' => '*',
            'down' => '',
            'clazz' => 'element',
            'attribute'=> 'data-type="number"'
        ),
        array(
            'up' => '0',
            'down' => '+',
            'clazz' => 'element',
            'attribute'=> 'data-type="number"'
        ),
        array(
            'up' => '#',
            'down' => '',
            'clazz' => 'element',
            'attribute'=> 'data-type="number"'
        )
    );
?>
<div class="sip-phone-container">
    <div class="bg-phone sip-phone-number-panel">
        <div class="current-number">
            <span class="element" data-type="number"></span> <a href="" class="element ico-phone-clear hide" data-type="clear"></a>
        </div>
        <div class="block-number">
            <?php
            foreach($items as $item){
                echo "<div class='item ".$item['clazz']."' ".$item['attribute']." ><span class='up'>".$item['up']."</span><span class='down'>".$item['down']."</span></div>";
            }
            ?>
        </div>
    </div>
    <div class="bg-phone sip-phone-bottom-panel">
        <a href="javascript:void(0)" class="are-you-calling">
            <div class="parent-avatar">
                <span class="list-view-avatar" style="background-image: url(/static/images/lock_thumb-mini.jpg);"></span>
            </div>
            <span class="text">
                <span class="name">Georgeo Vella</span>
                <span class="sign">Sunny day, Many work</span>
            </span>
        </a>
        <div class="conversation-block">
            <span class="time hide">00:00</span>
            <div class="btn-list-icons">
                <span class="element handset hide" data-type="handset">
                    <i class="fa fa-phone" aria-hidden="true"></i>
                </span>
                <span class="element microphone disable" data-type="microphone">
                    <i class="fa fa-microphone" aria-hidden="true"></i>
                    <i class="fa fa-microphone-slash" aria-hidden="true"></i>
                </span>
                <span class="element set" data-type="set"></span>
            </div>
        </div>
    </div>
</div>
