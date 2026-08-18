<?php
declare(strict_types=1);
final class SettingsRepository
{
    public function __construct(private mysqli $db) {}
    public function get(string $name, string $fallback = ''): string
    {
        $stmt = $this->db->prepare('SELECT einstellungswert FROM italienisch_einstellungen WHERE einstellungsname = ? LIMIT 1');
        if (!$stmt) return $fallback;
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $value = (string)($row['einstellungswert'] ?? '');
        return $value !== '' ? $value : $fallback;
    }
    public function save(string $name, string $value): bool
    {
        $stmt = $this->db->prepare('INSERT INTO italienisch_einstellungen (einstellungsname,einstellungswert) VALUES (?,?) ON DUPLICATE KEY UPDATE einstellungswert=VALUES(einstellungswert)');
        if (!$stmt) return false;
        $stmt->bind_param('ss', $name, $value);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
