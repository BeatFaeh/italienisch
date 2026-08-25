<?php
declare(strict_types=1);

final class VerbRepository
{
    public function __construct(private mysqli $db)
    {
    }

    /**
     * Liefert Verben mit Volltext-/Teilwortsuche über Italienisch, Deutsch
     * und sämtliche Konjugationsfelder. $sort: it oder de.
     */
    public function all(string $search = '', string $sort = 'it'): array
    {
        $search = trim($search);
        $sort = $sort === 'de' ? 'de' : 'it';
        $orderBy = $sort === 'de'
            ? 'verb_de ASC, verb_it ASC, id ASC'
            : 'verb_it ASC, verb_de ASC, id ASC';

        $select = 'SELECT id, verb_it, verb_de, praesens, perfekt, futur, imperativ, endung FROM italienisch_verben';

        if ($search === '') {
            $result = $this->db->query($select . ' ORDER BY ' . $orderBy);
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        $like = '%' . $search . '%';

        if (ctype_digit($search)) {
            $stmt = $this->db->prepare(
                $select . ' WHERE id = ? OR verb_it LIKE ? OR verb_de LIKE ? OR praesens LIKE ? OR perfekt LIKE ? OR futur LIKE ? OR imperativ LIKE ? OR endung LIKE ? ORDER BY ' . $orderBy
            );
            if (!$stmt) {
                return [];
            }
            $id = (int)$search;
            $stmt->bind_param('isssssss', $id, $like, $like, $like, $like, $like, $like, $like);
        } else {
            $stmt = $this->db->prepare(
                $select . ' WHERE verb_it LIKE ? OR verb_de LIKE ? OR praesens LIKE ? OR perfekt LIKE ? OR futur LIKE ? OR imperativ LIKE ? OR endung LIKE ? ORDER BY ' . $orderBy
            );
            if (!$stmt) {
                return [];
            }
            $stmt->bind_param('sssssss', $like, $like, $like, $like, $like, $like, $like);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    public function add(
        string $verbIt,
        string $verbDe,
        string $praesens,
        string $perfekt,
        string $futur,
        string $imperativ,
        string $endung
    ): bool {
        $stmt = $this->db->prepare(
            'INSERT INTO italienisch_verben (verb_it, verb_de, praesens, perfekt, futur, imperativ, endung) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('sssssss', $verbIt, $verbDe, $praesens, $perfekt, $futur, $imperativ, $endung);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function update(
        int $id,
        string $verbIt,
        string $verbDe,
        string $praesens,
        string $perfekt,
        string $futur,
        string $imperativ,
        string $endung
    ): bool {
        $stmt = $this->db->prepare(
            'UPDATE italienisch_verben SET verb_it = ?, verb_de = ?, praesens = ?, perfekt = ?, futur = ?, imperativ = ?, endung = ? WHERE id = ?'
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('sssssssi', $verbIt, $verbDe, $praesens, $perfekt, $futur, $imperativ, $endung, $id);
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
