<table>  
<?php foreach ( $result as $val ) { ?>
<tr>
<td><?php echo $val['order_bn']; ?></td><td><?php echo $val['memo']; ?></td>
</tr> 
<?php } ?>
</table>
<?php echo see_engine_kernel::lang($aaa['bbb']['ccc'][1]['ddd']); ?><br/>
<a href="<?php echo see_engine_kernel::url("base/member/index/id/1/name/mick"); ?>"><?php echo see_engine_kernel::url("base/member/index/id/1/name/mick"); ?></a>
<br/>
<?php if ($result['name'][0]['age']==3) { ?><?php echo $result[0]['memo']; ?><?php } else { ?>
<?php $record="abc"; $data=array('a','b',$record); ?>
<?php echo $data[2]; ?>
<?php } ?>
<?php $this->display( "test.html" ); ?>