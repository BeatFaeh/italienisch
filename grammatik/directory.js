(() => {
    'use strict';

    const table = document.getElementById('file-table');
    const search = document.getElementById('file-search');
    const noResults = document.getElementById('no-results');

    if (!table) {
        return;
    }

    const tbody = table.querySelector('tbody');
    const headers = table.querySelectorAll('thead th[data-sort]');

    let sortColumn = 'name';
    let sortDirection = 'asc';

    function visibleRows() {
        return [...tbody.querySelectorAll('tr')]
            .filter((row) => !row.hidden);
    }

    function updateNumbers() {
        visibleRows().forEach((row, index) => {
            const numberCell = row.querySelector('.number-column');

            if (numberCell) {
                numberCell.textContent = String(index + 1);
                numberCell.dataset.value = String(index + 1);
            }
        });
    }

    function normalize(value) {
        return String(value ?? '')
            .trim()
            .toLocaleLowerCase('de-CH');
    }

    function columnIndexFor(sortName) {
        const mapping = {
            number: 0,
            name: 1,
            type: 2,
            size: 3,
            modified: 4,
        };

        return mapping[sortName] ?? 1;
    }

    function getSortValue(row, columnIndex) {
        const cell = row.cells[columnIndex];

        if (!cell) {
            return '';
        }

        return cell.dataset.value ?? cell.textContent ?? '';
    }

    function compareValues(a, b, type) {
        if (type === 'number' || type === 'size' || type === 'modified') {
            return Number(a) - Number(b);
        }

        return String(a).localeCompare(
            String(b),
            'de-CH',
            {
                numeric: true,
                sensitivity: 'base',
            }
        );
    }

    function sortTable(sortName, direction) {
        const columnIndex = columnIndexFor(sortName);
        const rows = [...tbody.querySelectorAll('tr')];

        rows.sort((rowA, rowB) => {
            const valueA = getSortValue(rowA, columnIndex);
            const valueB = getSortValue(rowB, columnIndex);

            const result = compareValues(
                valueA,
                valueB,
                sortName
            );

            return direction === 'asc'
                ? result
                : -result;
        });

        rows.forEach((row) => tbody.appendChild(row));

        headers.forEach((header) => {
            header.classList.remove('sort-asc', 'sort-desc');

            if (header.dataset.sort === sortName) {
                header.classList.add(
                    direction === 'asc'
                        ? 'sort-asc'
                        : 'sort-desc'
                );
            }
        });

        updateNumbers();
    }

    headers.forEach((header) => {
        header.addEventListener('click', () => {
            const requestedColumn = header.dataset.sort;

            if (!requestedColumn) {
                return;
            }

            if (sortColumn === requestedColumn) {
                sortDirection = sortDirection === 'asc'
                    ? 'desc'
                    : 'asc';
            } else {
                sortColumn = requestedColumn;
                sortDirection = 'asc';
            }

            sortTable(sortColumn, sortDirection);
        });
    });

    if (search) {
        search.addEventListener('input', () => {
            const query = normalize(search.value);

            [...tbody.querySelectorAll('tr')].forEach((row) => {
                const haystack = normalize(row.dataset.search);

                row.hidden =
                    query !== ''
                    && !haystack.includes(query);
            });

            const hasVisibleRows = visibleRows().length > 0;

            if (noResults) {
                noResults.hidden = hasVisibleRows;
            }

            table.parentElement.hidden = !hasVisibleRows;

            updateNumbers();
        });
    }

    sortTable(sortColumn, sortDirection);
})();
