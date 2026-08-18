<?php
declare(strict_types=1);
final class ExamService
{
    public function __construct(private CardRepository $cards, private QuizService $quiz) {}

    /**
     * $requested = null bedeutet: alle verfügbaren Fragen.
     */
    public function build(?int $requested=25, string $direction='de-it', string $type='satz', ?int $lesson=null): array
    {
        $cards = $this->cards->randomMany($requested, $direction, $type, $lesson);
        $questions = [];

        // Antwortpool einmalig aufbauen. Das ist auch bei 1002 bzw. "alle" deutlich
        // schneller als pro Prüfungsfrage eine zusätzliche Datenbankabfrage auszuführen.
        $answerPool = [];
        foreach ($cards as $card) {
            $answer = trim((string)($card['antwort'] ?? ''));
            if ($answer !== '' && !in_array($answer, $answerPool, true)) {
                $answerPool[] = $answer;
            }
        }

        foreach ($cards as $card) {
            $correct = trim((string)($card['antwort'] ?? ''));
            if ($correct === '') continue;

            $distractors = array_values(array_filter(
                $answerPool,
                static fn(string $answer): bool => $answer !== $correct
            ));

            if (count($distractors) < 3) {
                // Fallback für sehr kleine Lektionen.
                $quiz = $this->quiz->build($card, $direction, $type, $lesson);
                if (count($quiz['options']) !== 4 || $quiz['correct_key'] === '') continue;
                $options = $quiz['options'];
                $correctKey = $quiz['correct_key'];
            } else {
                shuffle($distractors);
                $answers = [$correct, $distractors[0], $distractors[1], $distractors[2]];
                shuffle($answers);
                $options = [];
                $correctKey = '';
                foreach (['A','B','C','D'] as $i => $letter) {
                    $options[$letter] = $answers[$i];
                    if ($answers[$i] === $correct) $correctKey = $letter;
                }
            }

            $questions[] = [
                'id' => (int)$card['id'],
                'frage' => (string)$card['frage'],
                'options' => $options,
                'correct_key' => $correctKey,
                'lektion' => $card['lektion'],
            ];
        }

        return [
            'requested' => $requested,
            'questions' => $questions,
            'count' => count($questions),
        ];
    }
}
