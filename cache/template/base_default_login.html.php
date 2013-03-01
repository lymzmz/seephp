<div><?php echo $error; ?></div>
<form action="<?php echo see_engine_kernel::url("base/default/dologin"); ?>" method="post">
<input type="text" name="<?php echo "username"; ?>"/><br/>
<input type="text" name="<?php echo "password"; ?>"/><br/>
<input type="submit" value="submit"/>
</form> 