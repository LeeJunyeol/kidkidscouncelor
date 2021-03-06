<?php
class AnswerModel {
    public $conn;
    
    public function __construct($conn){
        $this->conn = $conn;
        require_once "../Util/Util.php";
    }

    function getCountAll(){
        try {
            $stmt = $this->conn->query("SELECT count(*) FROM answers");
            $rowCount = $stmt->fetch(PDO::FETCH_NUM);

            if(!$stmt->execute()){
                print_r($stmt->errorInfo());
                exit;
            };
            return $rowCount;
        } catch (PDOException $e) {
            print $e->getMessage();
            exit;
        }
    }

    function deleteById($id){
        try {
            $stmt = $this->conn->prepare("DELETE FROM answers WHERE answer_id = :answer_id");
            $stmt->bindParam(':answer_id', $id);
            if(!$stmt->execute()){
                print_r($stmt->errorInfo());
                exit;
            };
            return true;
        } catch (PDOException $e) {
            print $e->getMessage();
            exit;
        }
    }

    function getMyAnswerRecent5($userId){
        try {
            $sql = "SELECT * FROM answers WHERE user_id = '$userId' ORDER BY create_date DESC LIMIT 5";
            $stmt = $this->conn->prepare($sql);
            if(!$stmt->execute()){
                print_r($stmt->errorInfo());
                exit;
            }
            $answers = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $answers[] = $row;
            }
            return $answers;
        } catch (PDOException $e) {
            print $e->getMessage();
            exit;
        }
    }

    function searchByKeywords($origin, $kewords){
        $sql = "SELECT * FROM answers WHERE content LIKE '%$origin%'";
        foreach ($kewords as $key => $value) {
            $sql = $sql . " UNION SELECT * FROM answers WHERE content LIKE '%$value%'";
        }
        $stmt = $this->conn->prepare($sql);
        if(!$stmt->execute()){
            print_r($stmt->errorInfo());
            exit;
        };
        $answers = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $answers[] = $row;
        }
        return $answers;
    }

    function add($answer){
        $stmt = $this->conn->prepare("INSERT INTO answers (question_id, user_id, content) VALUES (:question_id, :user_id, :content)");
        $stmt->bindParam(':question_id', $answer['question_id']);
        $stmt->bindParam(':user_id', $answer['user_id']);
        $stmt->bindParam(':content', $answer['content']);
        
        if($stmt->execute()){
            return $this->conn->lastInsertId();
        } else {
            return false;
        }
    }

    function getForPage($offset, $limit){
        try {
            $sql = "SELECT * FROM answers LIMIT $offset, $limit";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $answers = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $answers[] = $row;
            }
            return $answers;
        } catch (PDOException $e) {
            print $e->getMessage();
            exit;
        }
    }

    function updateSelection($answerId, $selection){
        if($selection === true){
            try {
                $sql = "UPDATE answers SET selection = :selection WHERE answer_id = :answer_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':answer_id', $answerId);
                $stmt->bindParam(':selection', $selection);
                if(!$stmt->execute()){
                    print_r($stmt->errorInfo());
                    exit;
                };
                return $stmt->fetchObject();
            } catch (PDOException $e) {
                print $e->getMessage();
                exit;
            }
        }

    }

    function getById($id){
        $stmt = $this->conn->prepare("SELECT * FROM answers WHERE answer_id = :answer_id");
        $stmt->bindValue(':answer_id', $id);
        $stmt->execute();
        $answer = $stmt->fetchObject();
        return $answer;
    }
        
    function getJoinVoteAndUserById($id){
        $stmt = $this->conn->prepare("SELECT a.answer_id, a.question_id, a.user_id as author, a.content, a.create_date, u.user_pic
        , a.modify_date, u.user_type as label, v.user_id, IFNULL(v.vote, 0) AS vote
        FROM answers AS a 
        LEFT JOIN votes AS v ON a.answer_id = v.answer_id
        LEFT JOIN users AS u ON a.user_id = u.user_id
        WHERE a.answer_id = :answer_id");
        $stmt->bindValue(':answer_id', $id);
        $stmt->execute();
        $answer = $stmt->fetchObject();
        return $answer;
    }

    function getByQuestionIdAndUserId($questionId, $userId){
        $sql = "SELECT a.answer_id, a.question_id, a.user_id AS author, a.content, a.create_date, a.modify_date, u.user_type AS label, IFNULL(plus.plus_vote_cnt, 0) AS plus_vote_cnt, IFNULL(minus.minus_vote_cnt, 0) AS minus_vote_cnt FROM answers AS a 
        LEFT JOIN (SELECT answer_id, COUNT(*) AS plus_vote_cnt FROM votes WHERE vote = 1 GROUP BY answer_id) AS plus ON plus.answer_id = a.answer_id
        LEFT JOIN (SELECT answer_id, COUNT(*) AS minus_vote_cnt FROM votes WHERE vote = -1 GROUP BY answer_id) AS minus ON minus.answer_id = a.answer_id
        LEFT JOIN users AS u ON a.user_id = u.user_id
        WHERE question_id = :question_id";
        // $stmt = $this->conn->prepare("SELECT a.answer_id, a.question_id, a.user_id as author, a.content, a.create_date
        // , a.modify_date, u.user_type as label, a.title, v.user_id, IFNULL(v.vote, 0) AS vote
        // FROM answers AS a 
        // LEFT JOIN votes AS v ON a.answer_id = v.answer_id 
        // LEFT JOIN users AS u ON a.user_id = u.user_id
        // WHERE a.question_id = :question_id");
        $stmt->bindValue(':question_id', $questionId);
        $stmt->execute();
        $results = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $results[] = $row;
        }

        $grouped = array_group_by($results, 'answer_id'); // answer_id를 기준으로 그룹
        $finalAnswers = array();
        
        // vote를 합쳐서 votesum을 구하고, 내가 투표한 답변이라면 내 정보를 추가
        foreach ($grouped as &$value) {
            $initial = array_shift($value); 
            if(!isset($userId)){
                if($initial['user_id'] == $userId){
                    $initial['myuser'] = $initial['user_id'];
                    $initial['myvote'] = $initial['vote'];
                }
            }
        
            $initial['votesum'] = $initial['vote'];
            
            $t = array_reduce($value, function($result, $item) { 
                if(!isset($userId)){
                    if($result['user_id'] == $item['user_id']){
                        $result['myuser'] = $item['user_id'];
                        $result['myvote'] = $item['vote'];
                    }
                }
                $result['votesum'] += $item['vote'];

                return $result;
            }, $initial);
            array_push($finalAnswers, $t);
        }
        usort ($finalAnswers, array("AnswerModel", "cmp"));
        
        return $finalAnswers;
    }

    function getByQuestionId($questionId){
        $sql = "SELECT a.answer_id, a.question_id, a.user_id AS author, a.content, a.create_date, a.modify_date, a.selection, u.user_pic,
        u.user_type AS label, IFNULL(plus.plus_vote_cnt, 0) AS plus_vote_cnt, IFNULL(minus.minus_vote_cnt, 0) AS minus_vote_cnt FROM answers AS a 
        LEFT JOIN (SELECT answer_id, COUNT(*) AS plus_vote_cnt FROM votes WHERE vote = 1 GROUP BY answer_id) AS plus ON plus.answer_id = a.answer_id
        LEFT JOIN (SELECT answer_id, COUNT(*) AS minus_vote_cnt FROM votes WHERE vote = -1 GROUP BY answer_id) AS minus ON minus.answer_id = a.answer_id
        LEFT JOIN users AS u ON a.user_id = u.user_id
        WHERE question_id = :question_id ORDER BY a.selection DESC";
        // $sql = "SELECT a.answer_id, a.question_id, a.user_id as author, a.content, a.create_date
        // , a.modify_date, u.user_type as label, a.title, v.user_id, IFNULL(v.vote, 0) AS vote
        // FROM answers AS a 
        // LEFT JOIN votes AS v ON a.answer_id = v.answer_id 
        // LEFT JOIN users AS u ON a.user_id = u.user_id
        // WHERE a.question_id = :question_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':question_id', $questionId);
        $stmt->execute();
        $results = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $results[] = $row;
        }

        // $grouped = array_group_by($results, 'answer_id'); // answer_id를 기준으로 그룹
        // $finalAnswers = array();
        
        // // vote를 합쳐서 votesum을 구하고, 내가 투표한 답변이라면 내 정보를 추가
        // foreach ($grouped as &$value) {
        //     $initial = array_shift($value); 
        //     $initial['votesum'] = $initial['vote'];
            
        //     $t = array_reduce($value, function($result, $item) { 
        //         $result['votesum'] += $item['vote'];
        //         return $result;
        //     }, $initial);
        //     array_push($finalAnswers, $t);
        // }
        // usort ($finalAnswers, array("AnswerModel", "cmp"));
        
        return $results;
    }

    function getJoinOnAnswerByQuestionId($questionId){
        $stmt = $this->conn->prepare("SELECT c.opinion_id, o.user_id
        , o.answer_id, c.content
        , c.parent_idx, c.level, c.seq
        , c.create_date, c.modify_date 
        FROM answers as a JOIN opinions as o ON a.answer_id = c.answer_id WHERE a.question_id=:question_id");
        $stmt->bindValue(':question_id', $questionId);
        $stmt->execute();
        $results = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $results[] = $row;
        }
        return $results;
    }

    function deleteByQuestionId($questionId){
        // var_dump($questionId);
        // exit;
        $sql = "DELETE FROM `answers` WHERE `question_id`=:question_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":question_id", $questionId, PDO::PARAM_INT);
        if($stmt->execute()){
            echo "됨";
            return true;
        } else {
            echo "안됨";
            return false;
        }
    }

    // 투표합계 정렬하는 사용자함수.
    static function cmp($a, $b)
    {
        if ($a['votesum'] == $b['votesum']) {
            return 0;
        }
        return ($a['votesum'] > $b['votesum']) ? -1 : 1;
    }
}
?>