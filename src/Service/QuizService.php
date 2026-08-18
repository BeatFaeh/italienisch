<?php
declare(strict_types=1);
final class QuizService
{
    public function __construct(private mysqli $db) {}
    private function answerColumn(string $direction,string $type): string
    {
        $itDe=$direction==='it-de';$satz=$type==='satz';
        if($satz)return $itDe?'satz_de':'satz_it';
        return $itDe?'wort_de':'wort_it';
    }
    public function build(array $card,string $direction='de-it',string $type='wort',?int $lesson=null): array
    {
        $correct=trim((string)$card['antwort']);$answers=[$correct];$column=$this->answerColumn($direction,$type);
        $sql="SELECT DISTINCT $column AS antwort FROM italienisch_woerter_und_saetze WHERE $column IS NOT NULL AND TRIM($column)<>'' AND TRIM($column)<>?";
        if($lesson!==null)$sql.=' AND lektion='.(int)$lesson;
        $sql.=' ORDER BY RAND() LIMIT 3';
        $stmt=$this->db->prepare($sql);if($stmt){$stmt->bind_param('s',$correct);$stmt->execute();$r=$stmt->get_result();while($row=$r->fetch_assoc()){ $v=trim((string)$row['antwort']);if($v!==''&&!in_array($v,$answers,true))$answers[]=$v;} $stmt->close();}
        $options=[];$correctKey='';if(count($answers)===4){shuffle($answers);foreach(['A','B','C','D'] as $i=>$letter){$options[$letter]=$answers[$i];if($answers[$i]===$correct)$correctKey=$letter;}}
        return['options'=>$options,'correct_key'=>$correctKey];
    }
}
