<?php
namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Session\Container;
use Exception;


use Zend\Db\Sql\Where;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use Zend\Stdlib\DateTime;

class CommonTable {

    protected $tableGateway;
    public $branch_id;
    public $userId;
    
    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        
        $session = new Container('Branch');
        $branch = $session->branch_id;
        $this->branch_id = $branch;
        
        $session = new Container('User');
        $user = $session->userId;
        $this->userId = $user;
    }
    
    public function fetchAll(){
    	
    	$adapter = $this->tableGateway->getAdapter();
    	$sql = new Sql($adapter);
    	
    	$select = $sql->select();
    	$select->from('report_url_list');
    	
    	$statement = $sql->prepareStatementForSqlObject($select);
    	
    	$result = $statement->execute();
    	
    	$resultset = new ResultSet();
    	$resultset->initialize($result);
    	
    	return $resultset->toArray();
    	
    }
    
    public function insert($table, $data){
    	
    	$adapter = $this->tableGateway->getAdapter();
    	$sql = new Sql($adapter);
    	
    	$sql  = "INSERT INTO ".$table." ";
    	$sql .= " (`".implode("`, `", array_keys($data))."`)";
    	$sql .= " VALUES ";
    	$sql .= "('". implode("', '", $data)."') ";
    	
    	
    	$result = $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    	
    	return $result;
    	
    }
    
    public function get($table = null, $column = null, $w = null, $limit = null, $order_by = null){
    	
    	$adapter = $this->tableGateway->getAdapter();
    	
    	$projectTable;
    	
    	if($table != null){
    		$projectTable = new TableGateway($table, $adapter);
    	}else{
    		echo "Table Name is Null";
    	}
    	
    	
    	$rowset = $projectTable->select(function(Select $select) use ($w, $column, $limit, $order_by) {
    		
    		if($column != null){
    			$select->columns($column);
    		}
    		
    		if($w != null){
    			
    			$select->where($w,\Zend\Db\Sql\Where::OP_AND);
    		}
    		
    		if($limit != null){
    			$select->limit($limit);
    		}
    		
    		if($order_by != null){
    			$select->order($order_by[1]." ".$order_by[0]);
    		}
    		
    		//echo $select->getSqlString(); die();
    		
    	});
    		
    		return $rowset->toArray();   die;
    		
    }
    
    public function delete($table, $w){
    	
    	$adapter = $this->tableGateway->getAdapter();
    	
    	$projectTable;
    	
    	if($table != null){
    		$projectTable = new TableGateway($table, $adapter);
    	}else{
    		$projectTable = new TableGateway('account_master', $adapter);
    	}
    	
    	
    	$rowset = $projectTable->delete(function(Delete $delete) use ($w) {
    		
    		if($w != null){
    			
    			$delete->where($w,\Zend\Db\Sql\Where::OP_AND);
    		}else{
    			echo "Must be Need Where";
    		}
    		
    		
    		//echo $delete->getSqlString(); die;
    	});
    		
    		return $rowset->toArray();   die;
    }
    
    public function update($table, $where, $data_arr){
    	
    }
    
    public function getReport($table, $where, $to, $from){
    	
    	
    		$adapter = $this->tableGateway->getAdapter();
    		$sql = new Sql($adapter);
    		$sql = " SELECT * ";
    		$sql .= " FROM ".$table." "; 
    		$sql .= " WHERE ".$where." Between '".$to."' AND ";
    		$sql .= " '".$from."' ";
    		
    		$result = $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    		$resultset = new ResultSet();
    		$resultset->initialize($result);
    		
    		return $resultset->toArray();
    	
    	
    }
    
    public function getBranchwithpermission() {
    	/*
    	 * SELECT branch_master.branch_name as branch_name, branch_master.branch_id as branch_id
    	 * from branch_master
    	 * LEFT JOIN branch_permission ON branch_permission.branch_id = branch_master.branch_id
    	 * LEFT JOIN city_master on city_master.city_id = branch_permission.city_id
    	 * LEFT JOIN users ON users.user_id = branch_permission.user_id
    	 * WHERE users.user_id = 2
    	 */
    	$session = new Container('User');
    	$userId = $session->userId;
    	
    	$adapter = $this->tableGateway->getAdapter();
    	$sql = new Sql($adapter);
    	$select = $sql->select()->columns(array('ID' => 'branch_id', 'Name' => 'branch_name'))
    	->from('branch_master')
    	->join('branch_permission', 'branch_permission.branch_id = branch_master.branch_id')
    	->join('city_master', 'city_master.city_id = branch_permission.city_id')
    	->join('users','users.user_id = branch_permission.user_id');
    	
    	$where = new Where();
    	$where->equalTo('users.user_id', $userId);
    	$select->where($where);
    	$statement = $sql->prepareStatementForSqlObject($select);
    	//         echo $select->getSqlString();
    	//         die();
    	//echo "<br>";
    	
    	$result = $statement->execute();
    	$resultset = new ResultSet();
    	$resultset->initialize($result);
    	return $resultset->toArray();
    }

}
