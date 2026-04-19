document.addEventListener('DOMContentLoaded', function () {
    const match = document.cookie.match(/(?:^|;\s*)noteskey=([^;]+)/);
    if (match) {
        document.getElementById('notekey').value = decodeURIComponent(match[1]);
    }
});
