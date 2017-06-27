<?php
namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Session\Container;
use Exception;


use Zend\Db\Sql\Where;
use Zend\Db\Sql\Sql;
use Zend\Validator\Db\RecordExists;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Insert;

use Zend\Stdlib\DateTime;

class CommonTable {

    protected $tableGateway;
    public $branch_id;
    public $userId;

	public $query;
    
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

	/*
	Table Name: $table
	Column Name: $column_name
	Record: $record
	If Exist Return 1 otherwise 0
	*/
	public function data_exist($table, $column_name, $record){ 

		$adapter = $this->tableGateway->getAdapter();
		$validator = new RecordExists(
			array(
				'table'   => $table,
				'field'   => $column_name,
				'adapter' => $adapter
			)
		);
		if($validator->isValid($record)){
			return 0;
		}else{
			return 1;                    	
		}

	}
    

	/*
	Table Name: $table
	Data: $data
	$data = array('column_name' => value);
	*/
    public function insert($table, $data){
    	
    	$adapter = $this->tableGateway->getAdapter();
    	$sql = new Sql($adapter);
    	
    	$sql  = "INSERT INTO ".$table." ";
    	$sql .= " (`".implode("`, `", array_keys($data))."`)";
    	$sql .= " VALUES ";
    	$sql .= "('". implode("', '", $data)."') ";
    	
		// Set Insert Query in Global Object
		$this->setQuery($sql);
    	
    	$result = $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    	
    	return $result;
    	
    }
    
	/*
	Table Name = $table = 'table_name'
	Column = $column = array('column1', column2);
	If Column Param not Pass it;s return * value
	WHere = $w = array('column_name' => 'value');
	Default where condition set with AND oprator
	Limit = $limit = 'value';
	Order By = $order_by = array('Order Tye', 'column_name');
	Order Type = ASC/DESC
	*/
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
    		
			// Set Select Query in Global Var
			$this->setQuery($select->getSqlString());
    		//echo $select->getSqlString(); die();
    		
    	});
    		
    		return $rowset->toArray();   die;
    		
    }
    

	/*
	Table Name = $table
	Where = $w = array('column_name' => 'value');
	Default where cluse is AND
	*/
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
    		
			//Set Delete Query in Global Var
    		$this->setQuery($delete->getSqlString());
    	});
    		
    		return $rowset->toArray();   die;
    }
    
	/*

	*/
    public function update($table, $where, $data_arr){
    	
    }
    
	/*
	Table Name = $table = 'table_name',
	Where = $where = array('column_name');
	Start = $to = 'to_value',
	End = $from = 'from_value'
	*/
    public function getBetween($table, $where, $to, $from){
    	
    	
    		$adapter = $this->tableGateway->getAdapter();
    		$sql = new Sql($adapter);
    		$sql = " SELECT * ";
    		$sql .= " FROM ".$table." "; 
    		$sql .= " WHERE ".$where." Between '".$to."' AND ";
    		$sql .= " '".$from."' ";

			// Set Select with Between Query
			$this->setQuery($sql);
    		
    		$result = $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    		$resultset = new ResultSet();
    		$resultset->initialize($result);
    		
    		return $resultset->toArray();
    	
    	
    }
    
	/*
	Example of Join Query In ZF2
	*/
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
		
		$this->setQuery($select->getSqlString());
    	//         echo $select->getSqlString();
    	//         die();
    	//echo "<br>";
    	
    	$result = $statement->execute();
    	$resultset = new ResultSet();
    	$resultset->initialize($result);
    	return $resultset->toArray();
    }


	// Set Query as String
	public function setQuery($query){
    	$this->query = $query;
    }
    
	// Get/Return Query as String
    public function getQuery(){
    	return $this->query;
    }

}
