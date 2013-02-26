<div style="border:1px red solid;height:100px;width:100px;">
<?php echo $this->_plugin( 'lang', array("这是备注") ); ?><br/>
<?php echo $this->_plugin( 'lang', array("欢迎mick回来") ); ?><br/>
<?php echo $this->_plugin( 'url', array("member/login/active/user/mick") ); ?>
<?php $record=array('a','b','c','d','e'); ?>
<br/><select name="<?php echo "abc"; ?>" class="<?php echo "abc"; ?>" vab="<?php echo "nishishei"; ?>"><?php foreach ( $record as $key=>$val ) {
            echo '<option value="'.$key.'">'.$val.'</option>';
            } ?></select>
<br/><?php $name="def";foreach ( $record as $key => $val ) {
            $checked=$key=="1"?'checked':'';
            echo '<input type="radio" name="'.$name.'" '.$checked.' value="'.$key.'"/> '.$val;
            } ?>
<br/><?php $name="ghi";foreach ( $record as $key => $val ) {
            $checked=$key=="2"?'checked':'';
            echo '<input type="checkbox" name="'.$name.'" '.$checked.' value="'.$key.'"/> '.$val;
            } ?>
<br/><input type="text" name="<?php echo "jkl"; ?>" value="<?php echo "danshine"; ?>" class="<?php echo "nasdj;f"; ?>" url="<?php echo "lwer3452"; ?>" sldfj="<?php echo "3452345234"; ?>"/>
<?php $value="aaaaaaaaaaaa bbbbbbbbbbbbbbb ccccccccccccc"; ?>
<br/><textarea name="<?php echo "mno"; ?>" cols="<?php echo "100"; ?>"><?php echo $value; ?></textarea>
</div>