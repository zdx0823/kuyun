<?php
/**
 * 数据库访问类
 */
class Db{
	private $where = array();
	private $field = '*';
	private $order = '';
	private $limit = 0;
	private $pdo = null;
	public function __construct($argu){
		$host = $argu['host'];
		$dbname = $argu['dbname'];
		$name = $argu['name'];
		$pass = $argu['pass'];
		$this->pdo = new PDO("mysql:host={$host};dbname={$dbname}",$name,$pass);
	}
	// 指定表名称
	public function table($table){
		$this->table = $table;
		return $this;
	}
	// 指定查询字段
	public function field($field){
		$this->field = $field;
		return $this;
	}
	// 指定排序条件
	public function order($order){
		$this->order = $order;
		return $this;
	}
	// 指定查询数量
	public function limit($limit){
		$this->limit = $limit;
		return $this;
	}
	// 指定where条件
	public function where($where){
		$this->where = $where;
		return $this;
	}
	// 返回一条数据记录
	public function item(){
		$sql = $this->_build_sql('select').' limit 1';
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return isset($res[0]) ? $res[0] : false;
	}
	// 返回多条数据记录
	public function lists(){
		$sql = $this->_build_sql('select');
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	// 查询数据总数
	public function count(){
		$sql = $this->_build_sql('count');
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		$total = $stmt->fetchColumn(0);
		return $total;
	}
	// 分页
	public function pages($page,$pageSize = 10,$path = '/'){
		$count = $this->count();
		$this->limit = ($page - 1) * $pageSize .','.$pageSize;
		$data = $this->lists();
		$pages = $this->_subPages($page,$pageSize,$count,$path);
		return array('total'=>$count,'data'=>$data,'pages'=>$pages);
	}
	// 添加数据
	public function insert($data){
		$sql = $this->_build_sql('insert',$data);
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		return $stmt->rowCount();
	}
	// 删除数据并返回受影响的行数
	public function delete(){
		$sql = $this->_build_sql('delete');
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		return $stmt->rowCount();
	}
	// 更新数据
	public function update($data){
		//$sql = "update article set title='数据库更新' where id=21"
		$sql = $this->_build_sql('update',$data);
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute();
		return $stmt->rowCount();
	}
	// 构造sql语句
	private function _build_sql($type,$data=null){
		$sql = '';
		// 查询
		if($type=='select'){
			$where = $this->_build_where();
			$sql = "select {$this->field} from {$this->table} {$where}";
			if($this->order){
				$sql .= " order by {$this->order}";
			}
			if($this->limit){
				$sql .=" limit {$this->limit}";
			}
		}
		// count
		if($type == 'count'){
			$where = $this->_build_where();
			$field_list = explode(',',$this->field);
			$field = count($field_list)>1 ? '*' : $this->field;
			$sql = "select count({$field}) from {$this->table} {$where}";
		}
		// 添加
		if($type=='insert'){
			//$sql = "insert into article(uid,cid,title,pv)values(2,3,'数据库添加',8)";
			$sql = "insert into {$this->table}";
			$fields = $values = [];
			foreach ($data as $key => $val) {
				$fields[] = $key;
				$values[] = is_string($val) ? "'".$val."'" : $val;
			}
			$sql .= "(".implode(',', $fields).")values(".implode(',', $values).")";
		}
		// 删除
		if($type == 'delete'){
			$where = $this->_build_where();
			$sql = "delete from {$this->table} {$where}";
		}
		// 更新
		if($type == 'update'){
			// 生成where条件
			$where = $this->_build_where();
			// 生成set
			$str = '';
			foreach ($data as $key => $val) {
				$val = is_string($val) ? "'".$val."'" : $val;
				$str .= "{$key}={$val},";
			}
			$str = rtrim($str,',');
			$str = $str?" set {$str}":'';
			$sql = "update {$this->table} {$str} {$where}";
		}
		return $sql;
	}
	// 组装where条件字符串
	private function _build_where(){
		$where = '';
		if(is_array($this->where)){	// 数组方式
			foreach ($this->where as $key => $value) {
				// value如果是字符串，给value两边加'','value'
				$value = is_string($value) ? "'".$value."'" : $value;
				$where .= "`{$key}`={$value} and ";
			}
		}else{	// 字符串方式
			$where = $this->where;
		}
		$where = rtrim($where,'and ');
		$where = $where==''?'':"where {$where}";
		return $where;
	}
}