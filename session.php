<?php

class SysSession implements SessionHandlerInterface
{
    private $conn;
   
    public function open($savePath, $sessionName)
    {
		try {
			$this->conn = new PDO('mysql:host=localhost;port=3306;dbname=session', 'root', '');
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			return false;
		}
		return true;	
    }
	
    public function close()
    {
        $this->conn = null;
        return true;
    }
	
    public function read($session_id)
    {
		$sql = "SELECT session_data FROM sessions WHERE session_id = :session_id AND session_expiration > now()";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':session_id',$session_id);
		$stmt->execute();
		if ($row = $stmt->fetch(PDO::FETCH_NAMED))
            return $row["session_data"];
        else 
            return "";
    }
	
    public function write($session_id, $session_data)
    {
		if ($session_data !== '') {
			$sql = "REPLACE INTO sessions SET session_Id = :session_id, session_expiration = DATE_ADD(now(), interval :maxlifetime second), session_data = :session_data";
			$stmt = $this->conn->prepare($sql);
			$stmt->bindValue(':session_id',$session_id);
			$stmt->bindValue(':maxlifetime',intval(ini_get('session.gc_maxlifetime')));
			$stmt->bindValue(':session_data',$session_data);
			return $stmt->execute();
		}
		return true;
    }
	
    public function destroy($session_id)
    {
        $sql = "DELETE FROM sessions WHERE session_id =:session_id";
		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':session_id',$session_id);
		return $stmt->execute();		
    }
	
    public function gc($maxlifetime)
    {
		$sql = "DELETE FROM sessions WHERE session_expiration < now()";
		$stmt = $this->conn->prepare($sql);
		return $stmt->execute();
    }
}

//unit test
$handler = new SysSession();
session_set_save_handler($handler, true);
session_start();
var_dump($_SESSION);
//$_SESSION["nama"]="hendra";
session_gc();
?>