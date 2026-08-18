<?php
declare(strict_types=1);

final class GrammarRepository
{
    private string $keywordColumn = 'stichwort';
    private ?string $contentColumn = 'erklaerung';
    private string $pdfColumn = 'pdf';

    public function __construct(private mysqli $db)
    {
        $this->detectColumns();
    }

    private function detectColumns(): void
    {
        $result = @$this->db->query('SHOW COLUMNS FROM italienisch_grammatik');
        if (!$result) return;
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = (string)$row['Field'];
        }
        $pick = static function(array $candidates, array $available): ?string {
            foreach ($candidates as $candidate) if (in_array($candidate, $available, true)) return $candidate;
            return null;
        };
        $this->keywordColumn = $pick(['stichwort','begriff','thema','titel'], $columns) ?? $this->keywordColumn;
        $this->contentColumn = $pick(['erklaerung','erklärung','beschreibung','inhalt','text','grammatik'], $columns);
        $this->pdfColumn = $pick(['pdf'], $columns) ?? $this->pdfColumn;
    }

    private function q(string $column): string
    {
        return '`' . str_replace('`', '``', $column) . '`';
    }

    private function selectList(): string
    {
        $content = $this->contentColumn !== null ? $this->q($this->contentColumn) : "''";
        return 'id, ' . $this->q($this->keywordColumn) . ' AS stichwort, ' . $content . ' AS erklaerung, ' . $this->q($this->pdfColumn) . ' AS pdf';
    }

    public function all(string $search = ''): array
    {
        $search = trim($search);
        $select = $this->selectList();
        $keyword = $this->q($this->keywordColumn);
        $content = $this->contentColumn !== null ? $this->q($this->contentColumn) : null;
        if ($search === '') {
            $result = $this->db->query("SELECT $select FROM italienisch_grammatik ORDER BY $keyword ASC, id ASC");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }
        $searchParts = ["$keyword LIKE ?"];
        if ($content !== null) $searchParts[] = "$content LIKE ?";
        if (ctype_digit($search)) $searchParts[] = 'id = ?';
        $sql = "SELECT $select FROM italienisch_grammatik WHERE " . implode(' OR ', $searchParts) . " ORDER BY $keyword ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $like = '%' . $search . '%';
        if (ctype_digit($search)) {
            $id = (int)$search;
            if ($content !== null) $stmt->bind_param('ssi', $like, $like, $id);
            else $stmt->bind_param('si', $like, $id);
        } else {
            if ($content !== null) $stmt->bind_param('ss', $like, $like);
            else $stmt->bind_param('s', $like);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    public function add(string $stichwort, string $erklaerung, string $pdf): bool
    {
        if ($this->contentColumn !== null) {
            $sql = 'INSERT INTO italienisch_grammatik (' . $this->q($this->keywordColumn) . ', ' . $this->q($this->contentColumn) . ', ' . $this->q($this->pdfColumn) . ') VALUES (?, ?, ?)';
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('sss', $stichwort, $erklaerung, $pdf);
        } else {
            $sql = 'INSERT INTO italienisch_grammatik (' . $this->q($this->keywordColumn) . ', ' . $this->q($this->pdfColumn) . ') VALUES (?, ?)';
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('ss', $stichwort, $pdf);
        }
        $ok = $stmt->execute(); $stmt->close(); return $ok;
    }

    public function update(int $id, string $stichwort, string $erklaerung, string $pdf): bool
    {
        if ($this->contentColumn !== null) {
            $sql = 'UPDATE italienisch_grammatik SET ' . $this->q($this->keywordColumn) . ' = ?, ' . $this->q($this->contentColumn) . ' = ?, ' . $this->q($this->pdfColumn) . ' = ? WHERE id = ?';
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('sssi', $stichwort, $erklaerung, $pdf, $id);
        } else {
            $sql = 'UPDATE italienisch_grammatik SET ' . $this->q($this->keywordColumn) . ' = ?, ' . $this->q($this->pdfColumn) . ' = ? WHERE id = ?';
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('ssi', $stichwort, $pdf, $id);
        }
        $ok = $stmt->execute(); $stmt->close(); return $ok;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM italienisch_grammatik WHERE id = ?');
        if (!$stmt) return false;
        $stmt->bind_param('i', $id); $ok = $stmt->execute(); $stmt->close(); return $ok;
    }

    public function renamePdfReference(string $oldName, string $newName): void
    {
        $pdf = $this->q($this->pdfColumn);
        $oldCandidates = [$oldName, 'grammatik/' . $oldName, './' . $oldName];
        $newCandidates = [$newName, 'grammatik/' . $newName, './' . $newName];
        $stmt = $this->db->prepare("UPDATE italienisch_grammatik SET $pdf = ? WHERE $pdf = ?");
        if (!$stmt) return;
        foreach ($oldCandidates as $i => $old) { $new = $newCandidates[$i]; $stmt->bind_param('ss', $new, $old); $stmt->execute(); }
        $stmt->close();
    }

    public function clearPdfReference(string $name): void
    {
        $pdf = $this->q($this->pdfColumn);
        $candidates = [$name, 'grammatik/' . $name, './' . $name];
        $stmt = $this->db->prepare("UPDATE italienisch_grammatik SET $pdf = '' WHERE $pdf = ?");
        if (!$stmt) return;
        foreach ($candidates as $candidate) { $stmt->bind_param('s', $candidate); $stmt->execute(); }
        $stmt->close();
    }
}
