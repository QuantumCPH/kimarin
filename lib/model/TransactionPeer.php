<?php

class TransactionPeer extends BaseTransactionPeer {

    public static $credit_card_types = array(
        '2' => 'Visa/Dankort',
        '18' => 'Visa'
    );

    static public function AssignReceiptNumber(Transaction $transaction, PropelPDO $con = null) {
        if ($con === null) {
            $con = Propel::getConnection(TransactionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        if ($transaction->getTransactionStatusId() == 3) {
            $obj = new ReceiptNumbers();
            $obj->setParentId($transaction->getId());
            $obj->setDescription($transaction->getDescription());
            $obj->save();
            $transaction->setReceiptNo($obj->getId());
            $transaction->save();
        }
    }
    
    static public function AssignAgentReceiptNumber(AgentOrder $agent_order, PropelPDO $con = null) {
        if ($con === null) {
            $con = Propel::getConnection(TransactionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        if ($agent_order->getStatus() == 3) {
            $obj = new ReceiptNumbers();
            $obj->setParentId($agent_order->getId());
            $obj->setParent("Agent Order");
            $obj->setDescription($agent_order->getOrderDescription());
            $obj->save();
            $agent_order->setReceiptNo($obj->getId());
            $agent_order->save();
        }
    }
    
    static public function AssignB2bReceiptNumber(CompanyTransaction $transaction, PropelPDO $con = null) {
        if ($con === null) {
            $con = Propel::getConnection(CompanyTransactionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        if ($transaction->getTransactionStatusId() == 3) {
            $obj = new ReceiptNumbers();
            $obj->setParentId($transaction->getId());
            $obj->setDescription($transaction->getDescription());
            $obj->save();
            $transaction->setReceiptNo($obj->getId());
            $transaction->save();
        }
    }

}