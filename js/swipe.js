document.addEventListener('DOMContentLoaded', () => {
    // Sadece mobilde çalışsın
    if (window.innerWidth > 768) return;

    const rows = document.querySelectorAll('.table tbody tr');

    // Stil ekle (Dinamik olarak)
    const style = document.createElement('style');
    style.innerHTML = `
        .swipe-action {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 140px; /* GENİŞLETİLDİ: 100px -> 140px */
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
            color: white;
            z-index: 1;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .swipe-action.left-action {
            right: 100%; /* Sol kenarın hemen dışı */
            background-color: #ef4444; /* Kırmızı - SİL */
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
            justify-content: flex-end;
            padding-right: 1.5rem; /* Padding artırıldı */
        }
        .swipe-action.right-action {
            left: 100%; /* Sağ kenarın hemen dışı */
            background-color: #3b82f6; /* Mavi - DÜZENLE */
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
            justify-content: flex-start;
            padding-left: 1.5rem; /* Padding artırıldı */
        }
        .table tbody tr {
            position: relative;
            transform-style: preserve-3d; 
            background: white;
            z-index: 2; /* Content üstte */
        }
        body {
            overflow-x: hidden;
        }
    `;
    document.head.appendChild(style);

    rows.forEach(row => {
        let startX;
        let currentX;
        let isDragging = false;

        const editBtn = row.querySelector('a.btn-primary');
        const deleteBtn = row.querySelector('a.btn-danger');

        let editUrl = editBtn ? editBtn.href : null;
        let deleteUrl = deleteBtn ? deleteBtn.href : null;

        const deleteConfirmMsg = deleteBtn && deleteBtn.getAttribute('onclick')
            ? deleteBtn.getAttribute('onclick').match(/'([^']+)'/)[1]
            : 'Silmek istediğinize emin misiniz?';

        if (!editUrl && !deleteUrl) return;

        row.style.transition = 'transform 0.2s ease';

        let leftActionEl = null;
        let rightActionEl = null;

        if (deleteUrl) {
            const leftAction = document.createElement('div');
            leftAction.className = 'swipe-action left-action';
            leftAction.innerHTML = 'SİL <span style="font-size:1.4em; margin-left:8px;">🗑</span>';
            row.appendChild(leftAction);
            leftActionEl = leftAction;
        }

        if (editUrl) {
            const rightAction = document.createElement('div');
            rightAction.className = 'swipe-action right-action';
            rightAction.innerHTML = '<span style="font-size:1.4em; margin-right:8px;">✎</span> DÜZENLE';
            row.appendChild(rightAction);
            rightActionEl = rightAction;
        }

        row.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isDragging = true;
            row.style.transition = 'none';
        }, { passive: true });

        row.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            currentX = e.touches[0].clientX;
            const diff = currentX - startX;

            if (Math.abs(diff) < 10) return;

            // Limitler
            if (!deleteUrl && diff > 0) return;
            if (!editUrl && diff < 0) return;

            // Limit Artırıldı: 150px
            const translateX = Math.max(Math.min(diff, 150), -150);
            row.style.transform = `translateX(${translateX}px)`;

            // OPACITY KONTROLÜ
            if (diff > 0 && leftActionEl) {
                // Sağa çekiliyor -> SİL
                leftActionEl.style.opacity = Math.min(diff / 60, 1);
                if (rightActionEl) rightActionEl.style.opacity = 0;
            } else if (diff < 0 && rightActionEl) {
                // Sola çekiliyor -> DÜZENLE
                const absDiff = Math.abs(diff);
                rightActionEl.style.opacity = Math.min(absDiff / 60, 1);
                if (leftActionEl) leftActionEl.style.opacity = 0;
            }

        }, { passive: true });

        row.addEventListener('touchend', () => {
            if (!isDragging) return;
            isDragging = false;
            row.style.transition = 'transform 0.3s ease';

            const diff = currentX - startX;

            // Eşik değeri artırıldı: 120
            if (diff > 120 && deleteUrl) {
                if (confirm(deleteConfirmMsg)) {
                    row.style.transform = `translateX(100vw)`;
                    window.location.href = deleteUrl;
                } else {
                    resetRow(row);
                }
            } else if (diff < -120 && editUrl) {
                row.style.transform = `translateX(-100vw)`;
                setTimeout(() => { window.location.href = editUrl; }, 300);
            } else {
                resetRow(row);
            }
        });

        function resetRow(r) {
            r.style.transform = 'translateX(0)';
            if (leftActionEl) leftActionEl.style.opacity = 0;
            if (rightActionEl) rightActionEl.style.opacity = 0;
        }
    });

    const container = document.querySelector('.container');
    if (container && rows.length > 0) {
        const existingHint = document.getElementById('swipe-hint');
        if (existingHint) existingHint.remove();

        const hint = document.createElement('div');
        hint.id = 'swipe-hint';
        hint.style.textAlign = 'center';
        hint.style.fontSize = '0.8rem';
        hint.style.color = '#64748b';
        hint.style.backgroundColor = '#f1f5f9';
        hint.style.padding = '0.5rem';
        hint.style.borderRadius = '0.5rem';
        hint.style.marginBottom = '1rem';
        hint.style.fontWeight = '500';
        hint.innerHTML = '← Sola çek: <b>DÜZENLE</b> &nbsp;|&nbsp; Sağa çek: <b>SİL</b> →';

        const target = document.querySelector('.card') || document.querySelector('.table-responsive');
        if (target) {
            target.parentNode.insertBefore(hint, target);
        }
    }
});
