(() => {
    'use strict';

    const form = document.getElementById('exam-form');
    const evaluateButton = document.getElementById('evaluate-exam');
    const result = document.getElementById('exam-result');

    if (!form || !evaluateButton || !result) {
        return;
    }

    const questions = [...form.querySelectorAll('.exam-question')];
    const answeredCount = document.getElementById('answered-count');
    const remainingCount = document.getElementById('remaining-count');

    function selectedAnswer(question) {
        return question.querySelector('input[type="radio"]:checked');
    }

    function updateProgress() {
        const answered = questions.filter(selectedAnswer).length;
        const remaining = questions.length - answered;

        answeredCount.textContent = String(answered);
        remainingCount.textContent = String(remaining);
        evaluateButton.disabled = remaining !== 0;

        questions.forEach((question) => {
            const status = question.querySelector('.exam-status');
            status.textContent = selectedAnswer(question)
                ? 'Beantwortet'
                : 'Noch nicht beantwortet';
        });
    }

    form.addEventListener('change', updateProgress);

    evaluateButton.addEventListener('click', () => {
        if (questions.some((question) => !selectedAnswer(question))) {
            updateProgress();
            return;
        }

        let score = 0;

        questions.forEach((question) => {
            const correct = question.dataset.correct;
            const selected = selectedAnswer(question);
            const labels = [...question.querySelectorAll('.exam-option')];

            labels.forEach((label) => {
                const input = label.querySelector('input');
                input.disabled = true;

                if (input.value === correct) {
                    label.classList.add('correct');
                }

                if (input.checked && input.value !== correct) {
                    label.classList.add('wrong');
                }
            });

            const status = question.querySelector('.exam-status');

            if (selected.value === correct) {
                score += 1;
                status.textContent = '✓ Richtig';
                status.classList.add('correct');
            } else {
                status.textContent = '✗ Falsch';
                status.classList.add('wrong');
            }
        });

        const percent = Math.round((score / questions.length) * 100);
        document.getElementById('score-value').textContent = String(score);
        document.getElementById('score-percent').textContent = `${percent} % richtig`;

        let message = 'Weiterlernen lohnt sich.';
        if (percent >= 90) {
            message = 'Ausgezeichnet – sehr sicher beherrscht.';
        } else if (percent >= 75) {
            message = 'Sehr gutes Ergebnis.';
        } else if (percent >= 60) {
            message = 'Solide Grundlage – einige Themen lohnen sich zur Wiederholung.';
        }

        document.getElementById('score-message').textContent = message;

        evaluateButton.disabled = true;
        result.hidden = false;
        result.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    updateProgress();
})();
