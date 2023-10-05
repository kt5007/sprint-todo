class UserCheckboxManager {
    constructor(userCheckboxSelector, userAllSelector, dataIds) {
        this.checkboxes = document.querySelectorAll(userCheckboxSelector);
        this.selectAllCheckbox = document.querySelector(userAllSelector);

        this.selectAllCheckbox.addEventListener('change', () => {
            this.checkboxes.forEach((cb) => {
                cb.checked = this.selectAllCheckbox.checked;
            });

            this.updateTableVisibility();
        });

        this.checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                this.updateTableVisibility();
            });
        });
    }

    updateTableVisibility() {
        const selectedUserIds = [];
        this.checkboxes.forEach((cb) => {
            if (cb.checked && cb.value !== 'all') {
                selectedUserIds.push(cb.value);
            }
        });

        this.checkboxes.forEach((cb) => {
            if (cb.value === 'all') {
                cb.checked = selectedUserIds.length === this.checkboxes.length - 1;
            }
        });

        const tableRows = document.querySelectorAll('#sort-table tbody tr');

        tableRows.forEach((row) => {
            const userIds = row.getAttribute(dataIds).split(',').map((id) => {
                return id.trim();
            });

            if (
                this.selectAllCheckbox.checked ||
                selectedUserIds.length === 0 ||
                selectedUserIds.every((id) => {
                    return userIds.includes(id);
                })
            ) {
                row.style.display = ''; // 表示
            } else {
                row.style.display = 'none'; // 非表示
            }
        });
    }

    // 初回ページ読み込み時にも実行
    updateTableVisibility();
}
