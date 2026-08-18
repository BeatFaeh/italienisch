<?php
declare(strict_types=1);

final class VerbRepository
{
    public function __construct(private mysqli $db)
    {
    }

    public function all(string $search = ''): array
    {
        $search = trim($search);
        if ($search === '') {
            $result = $this->db->query('SELECT id, verb, praesens, perfekt, futur, imperativ, endung FROM italienisch_verben ORDER BY verb ASC, id ASC');
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        if (ctype_digit($search)) {
            $stmt = $this->db->prepare('SELECT id, verb, praesens, perfekt, futur, imperativ, endung FROM italienisch_verben WHERE id = ? OR verb LIKE ? ORDER BY verb ASC, id ASC');
            if (!$stmt) {
                return [];
            }
            $id = (int)$search;
            $like = '%' . $search . '%';
            $stmt->bind_param('is', $id, $like);
        } else {
            $stmt = $this->db->prepare('SELECT id, verb, praesens, perfekt, futur, imperativ, endung FROM italienisch_verben WHERE verb LIKE ? OR praesens LIKE ? OR perfekt LIKE ? OR futur LIKE ? OR imperativ LIKE ? OR endung LIKE ? ORDER BY verb ASC, id ASC');
            if (!$stmt) {
                return [];
            }
            $like = '%' . $search . '%';
            $stmt->bind_param('ssssss', $like, $like, $like, $like, $like, $like);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    public function add(string $verb, string $praesens, string $perfekt, string $futur, string $imperativ, string $endung): bool
    {
        $stmt = $this->db->prepare('INSERT INTO italienisch_verben (verb, praesens, perfekt, futur, imperativ, endung) VALUES (?, ?, ?, ?, ?, ?)');
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('ssssss', $verb, $praesens, $perfekt, $futur, $imperativ, $endung);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function update(int $id, string $verb, string $praesens, string $perfekt, string $futur, string $imperativ, string $endung): bool
    {
        $stmt = $this->db->prepare('UPDATE italienisch_verben SET verb = ?, praesens = ?, perfekt = ?, futur = ?, imperativ = ?, endung = ? WHERE id = ?');
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('ssssssi', $verb, $praesens, $perfekt, $futur, $imperativ, $endung, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM italienisch_verben WHERE id = ?');
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
