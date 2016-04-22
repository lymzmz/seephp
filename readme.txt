 b

/*
            //C create
        $colle = kernel::singleApp('base')->collection('orders');
        //$colle->add( $data, false );
        $order_id = $colle->add( $colle->record( $data ), true );

        //kernel::singleApp('base')->model('orders')->insert( $data );
            //C end

            //U update
        $record = kernel::singleApp('base')->collection('orders')->record( $data );
        $record->order_id = $order_id;
        $record->memo .= '__做过一次修改';
        kernel::singleApp('base')->collection('orders')->up( $record );

        //kernel::app('base')->single()->model('orders')->update( array('memo' => '又一次修改'), array('order_id' => 1) );
            //U end

            //D delete
        $colle = kernel::singleApp('base')->collection('orders');
        $record = $colle->record( $colle->dumpByorder_id( 89, '*' ) );
        $colle->del( $record, false );

        //kernel::single()->app('base')->model('orders')->delete( array('order_id' => 1) );
            //D end
        
        $colle->performOperations();
*/
            //R read
        $colle = see_engine_kernel::single()->collection('orders');
//        $result = $colle->setBuilder( 'base_deliverys.bn,base_members.member_id,order_id,order_bn,memo', array('order_id|<'=>66), array('order_id'=>'desc'), null , 1, 5 )->listAll();

        //$result = kernel::single()->app('base')->model('orders')->findResult( array('memo', 'order_id'), array('order_id' => 1) );
            //R end