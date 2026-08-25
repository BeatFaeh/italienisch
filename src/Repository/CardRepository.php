<?php
declare(strict_types=1);
final class CardRepository
{
    public function __construct(private mysqli $db) {}

    private function fields(string $direction, string $type): array
    {
        $direction = $direction === 'it-de' ? 'it-de' : 'de-it';
        $type = $type === 'satz' ? 'satz' : 'wort';
        if ($type === 'satz') {
            return $direction === 'de-it' ? ['satz_de','satz_it'] : ['satz_it','satz_de'];
        }
        return $direction === 'de-it' ? ['wort_de','wort_it'] : ['wort_it','wort_de'];
    }

    private function validWhere(string $q, string $a): string
    {
        return "$q IS NOT NULL AND TRIM($q) <> '' AND $a IS NOT NULL AND TRIM($a) <> ''";
    }

    public function random(string $direction='de-it', string $type='wort', ?int $lesson=null): ?array
    {
        [$q,$a] = $this->fields($direction,$type);
        $sql = "SELECT id,wort_de,wort_it,satz_de,satz_it,lektion,$q AS frage,$a AS antwort FROM italienisch_woerter_und_saetze WHERE ".$this->validWhere($q,$a);
        if ($lesson !== null) $sql .= ' AND lektion = '.(int)$lesson;
        $sql .= ' ORDER BY RAND() LIMIT 1';
        $result = $this->db->query($sql);
        return $result ? ($result->fetch_assoc() ?: null) : null;
    }

    /**
     * Liefert zufällige Karten. Bei $limit = null werden alle passenden Karten geliefert.
     */
    public function randomMany(?int $limit, string $direction='de-it', string $type='wort', ?int $lesson=null): array
    {
        [$q,$a] = $this->fields($direction,$type);
        $sql = "SELECT id,wort_de,wort_it,satz_de,satz_it,lektion,$q AS frage,$a AS antwort FROM italienisch_woerter_und_saetze WHERE ".$this->validWhere($q,$a);
        if ($lesson !== null) $sql .= ' AND lektion = '.(int)$lesson;
        $sql .= ' ORDER BY RAND()';
        if ($limit !== null) {
            $limit = max(1, $limit);
            $sql .= ' LIMIT '.$limit;
        }
        $rows=[];
        $result=$this->db->query($sql);
        while ($result && $row=$result->fetch_assoc()) $rows[]=$row;
        return $rows;
    }

    public function findById(int $id, string $direction='de-it', string $type='wort'): ?array
    {
        if ($id <= 0) return null;
        [$q,$a] = $this->fields($direction,$type);
        $stmt=$this->db->prepare("SELECT id,wort_de,wort_it,satz_de,satz_it,lektion,$q AS frage,$a AS antwort FROM italienisch_woerter_und_saetze WHERE id=? AND ".$this->validWhere($q,$a).' LIMIT 1');
        if(!$stmt)return null; $stmt->bind_param('i',$id); $stmt->execute(); $row=$stmt->get_result()->fetch_assoc(); $stmt->close(); return $row?:null;
    }

    public function findByTerm(string $term, string $direction='de-it', string $type='wort', ?int $lesson=null): ?array
    {
        $rows = $this->searchByTerm($term, $direction, $type, $lesson, 1);
        return $rows[0] ?? null;
    }

    /**
     * Sucht gezielt nach einem Wort bzw. Satz in beiden Sprachen.
     * Exakte Treffer werden vor Teiltreffern angezeigt.
     */
    public function searchByTerm(
        string $term,
        string $direction='de-it',
        string $type='wort',
        ?int $lesson=null,
        int $limit=50
    ): array {
        $term = trim($term);
        if ($term === '') return [];

        [$q,$a] = $this->fields($direction,$type);

        // Die Suche bezieht sich immer auf die Wörter, unabhängig davon,
        // ob aktuell Wort- oder Satzkarten angezeigt werden. Dadurch findet
        // z. B. "cappu" auch "cappuccino".
        $de = 'wort_de';
        $it = 'wort_it';
        $limit = max(1, min(100, $limit));

        $sql = "SELECT id,wort_de,wort_it,satz_de,satz_it,lektion,$q AS frage,$a AS antwort
                FROM italienisch_woerter_und_saetze
                WHERE ($de LIKE CONCAT('%',?,'%') OR $it LIKE CONCAT('%',?,'%'))
                  AND ".$this->validWhere($q,$a);

        if ($lesson !== null) {
            $sql .= ' AND lektion='.(int)$lesson;
        }

        $sql .= " ORDER BY
                    CASE
                        WHEN LOWER(TRIM($de)) = LOWER(TRIM(?)) THEN 0
                        WHEN LOWER(TRIM($it)) = LOWER(TRIM(?)) THEN 0
                        WHEN LOWER(TRIM($de)) LIKE CONCAT(LOWER(TRIM(?)),'%') THEN 1
                        WHEN LOWER(TRIM($it)) LIKE CONCAT(LOWER(TRIM(?)),'%') THEN 1
                        ELSE 2
                    END,
                    COALESCE(lektion,999999), id ASC
                  LIMIT $limit";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('ssssss', $term, $term, $term, $term, $term, $term);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($result && $row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    public function all(?int $lesson=null): array
    {
        $sql='SELECT id,wort_de,wort_it,satz_de,satz_it,lektion FROM italienisch_woerter_und_saetze';
        if($lesson!==null)$sql.=' WHERE lektion='.(int)$lesson;
        $sql.=' ORDER BY COALESCE(lektion,999999), id ASC';
        $rows=[];$result=$this->db->query($sql);while($result&&$row=$result->fetch_assoc())$rows[]=$row;return $rows;
    }

    public function learningCards(string $direction='de-it', string $type='wort', ?int $lesson=null, string $search=''): array
    {
        [$q,$a]=$this->fields($direction,$type);
        $sql="SELECT id,wort_de,wort_it,satz_de,satz_it,lektion,$q AS frage,$a AS antwort FROM italienisch_woerter_und_saetze WHERE ".$this->validWhere($q,$a);
        $types='';$params=[];
        if($lesson!==null)$sql.=' AND lektion='.(int)$lesson;
        if(trim($search)!==''){
            $sql.=" AND (wort_de LIKE CONCAT('%',?,'%') OR wort_it LIKE CONCAT('%',?,'%') OR satz_de LIKE CONCAT('%',?,'%') OR satz_it LIKE CONCAT('%',?,'%'))";
            $types='ssss';$params=[$search,$search,$search,$search];
        }
        $sql.=' ORDER BY COALESCE(lektion,999999),id ASC';
        if($types===''){ $result=$this->db->query($sql); }
        else { $stmt=$this->db->prepare($sql); if(!$stmt)return[]; $stmt->bind_param($types,...$params);$stmt->execute();$result=$stmt->get_result(); }
        $rows=[];while($result&&$row=$result->fetch_assoc())$rows[]=$row; if(isset($stmt))$stmt->close();return$rows;
    }

    public function count(string $direction='de-it', string $type='wort', ?int $lesson=null): int
    {
        [$q,$a]=$this->fields($direction,$type);
        $sql='SELECT COUNT(*) anzahl FROM italienisch_woerter_und_saetze WHERE '.$this->validWhere($q,$a);
        if($lesson!==null)$sql.=' AND lektion='.(int)$lesson;
        $row=$this->db->query($sql)?->fetch_assoc(); return(int)($row['anzahl']??0);
    }

    public function lessons(): array
    {
        $rows=[];$result=$this->db->query('SELECT DISTINCT lektion FROM italienisch_woerter_und_saetze WHERE lektion IS NOT NULL ORDER BY lektion ASC');
        while($result&&$row=$result->fetch_assoc())$rows[]=(int)$row['lektion']; return$rows;
    }


    public function adminSearch(string $term='', string $field='all', ?int $limit=50): array
    {
        $term = trim($term);
        $field = in_array($field, ['all','id','de','it'], true) ? $field : 'all';
        if ($limit !== null) $limit = max(1, $limit);
        $limitSql = $limit !== null ? ' LIMIT '.$limit : '';

        if ($term === '') {
            $sql = 'SELECT id,wort_de,wort_it,satz_de,satz_it,lektion FROM italienisch_woerter_und_saetze ORDER BY COALESCE(lektion,999999), id ASC';
            if ($limit !== null) $sql .= ' LIMIT '.$limit;
            $rows=[]; $result=$this->db->query($sql); while($result&&$row=$result->fetch_assoc()) $rows[]=$row; return $rows;
        }

        if ($field === 'id') {
            if (!ctype_digit($term)) return [];
            $stmt = $this->db->prepare(
                'SELECT id,wort_de,wort_it,satz_de,satz_it,lektion
                 FROM italienisch_woerter_und_saetze
                 WHERE id = ?
                 ORDER BY id ASC' . $limitSql
            );
            if (!$stmt) return [];
            $id = (int)$term;
            $stmt->bind_param('i', $id);
        } elseif ($field === 'de') {
            $stmt = $this->db->prepare(
                "SELECT id,wort_de,wort_it,satz_de,satz_it,lektion
                 FROM italienisch_woerter_und_saetze
                 WHERE wort_de LIKE CONCAT('%',?,'%')
                 ORDER BY COALESCE(lektion,999999), id ASC" . $limitSql
            );
            if (!$stmt) return [];
            $stmt->bind_param('s', $term);
        } elseif ($field === 'it') {
            $stmt = $this->db->prepare(
                "SELECT id,wort_de,wort_it,satz_de,satz_it,lektion
                 FROM italienisch_woerter_und_saetze
                 WHERE wort_it LIKE CONCAT('%',?,'%')
                 ORDER BY COALESCE(lektion,999999), id ASC" . $limitSql
            );
            if (!$stmt) return [];
            $stmt->bind_param('s', $term);
        } else {
            if (ctype_digit($term)) {
                $stmt = $this->db->prepare(
                    "SELECT id,wort_de,wort_it,satz_de,satz_it,lektion
                     FROM italienisch_woerter_und_saetze
                     WHERE id = ?
                        OR wort_de LIKE CONCAT('%',?,'%')
                        OR wort_it LIKE CONCAT('%',?,'%')
                     ORDER BY COALESCE(lektion,999999), id ASC" . $limitSql
                );
                if (!$stmt) return [];
                $id = (int)$term;
                $stmt->bind_param('iss', $id, $term, $term);
            } else {
                $stmt = $this->db->prepare(
                    "SELECT id,wort_de,wort_it,satz_de,satz_it,lektion
                     FROM italienisch_woerter_und_saetze
                     WHERE wort_de LIKE CONCAT('%',?,'%')
                        OR wort_it LIKE CONCAT('%',?,'%')
                     ORDER BY COALESCE(lektion,999999), id ASC" . $limitSql
                );
                if (!$stmt) return [];
                $stmt->bind_param('ss', $term, $term);
            }
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($result && $row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }


    public function adminCount(string $term='', string $field='all'): int
    {
        $term = trim($term);
        $field = in_array($field, ['all','id','de','it'], true) ? $field : 'all';
        if ($term === '') {
            $row=$this->db->query('SELECT COUNT(*) AS anzahl FROM italienisch_woerter_und_saetze')?->fetch_assoc();
            return (int)($row['anzahl']??0);
        }
        if ($field === 'id') {
            if (!ctype_digit($term)) return 0;
            $stmt=$this->db->prepare('SELECT COUNT(*) AS anzahl FROM italienisch_woerter_und_saetze WHERE id=?');
            if(!$stmt)return 0; $id=(int)$term; $stmt->bind_param('i',$id);
        } elseif ($field === 'de') {
            $stmt=$this->db->prepare("SELECT COUNT(*) AS anzahl FROM italienisch_woerter_und_saetze WHERE wort_de LIKE CONCAT('%',?,'%')");
            if(!$stmt)return 0; $stmt->bind_param('s',$term);
        } elseif ($field === 'it') {
            $stmt=$this->db->prepare("SELECT COUNT(*) AS anzahl FROM italienisch_woerter_und_saetze WHERE wort_it LIKE CONCAT('%',?,'%')");
            if(!$stmt)return 0; $stmt->bind_param('s',$term);
        } else {
            if (ctype_digit($term)) {
                $stmt=$this->db->prepare("SELECT COUNT(*) AS anzahl FROM italienisch_woerter_und_saetze WHERE id=? OR wort_de LIKE CONCAT('%',?,'%') OR wort_it LIKE CONCAT('%',?,'%')");
                if(!$stmt)return 0; $id=(int)$term; $stmt->bind_param('iss',$id,$term,$term);
            } else {
                $stmt=$this->db->prepare("SELECT COUNT(*) AS anzahl FROM italienisch_woerter_und_saetze WHERE wort_de LIKE CONCAT('%',?,'%') OR wort_it LIKE CONCAT('%',?,'%')");
                if(!$stmt)return 0; $stmt->bind_param('ss',$term,$term);
            }
        }
        $stmt->execute(); $row=$stmt->get_result()->fetch_assoc(); $stmt->close();
        return (int)($row['anzahl']??0);
    }

    public function add(string $wortDe,string $wortIt,string $satzDe,string $satzIt,?int $lektion): bool
    {
        $stmt=$this->db->prepare('INSERT INTO italienisch_woerter_und_saetze (wort_de,wort_it,satz_de,satz_it,lektion) VALUES (?,?,?,?,?)');
        if(!$stmt)return false;$stmt->bind_param('ssssi',$wortDe,$wortIt,$satzDe,$satzIt,$lektion);$ok=$stmt->execute();$stmt->close();return$ok;
    }
    public function update(int $id,string $wortDe,string $wortIt,string $satzDe,string $satzIt,?int $lektion): bool
    {
        $stmt=$this->db->prepare('UPDATE italienisch_woerter_und_saetze SET wort_de=?,wort_it=?,satz_de=?,satz_it=?,lektion=? WHERE id=?');
        if(!$stmt)return false;$stmt->bind_param('ssssii',$wortDe,$wortIt,$satzDe,$satzIt,$lektion,$id);$ok=$stmt->execute();$stmt->close();return$ok;
    }
    public function delete(int $id): bool
    {
        $stmt=$this->db->prepare('DELETE FROM italienisch_woerter_und_saetze WHERE id=?');if(!$stmt)return false;$stmt->bind_param('i',$id);$ok=$stmt->execute();$stmt->close();return$ok;
    }
}
