document.addEventListener('DOMContentLoaded', function () {
    const select = document.querySelector('#whichPainting');
    const images = document.querySelectorAll('section.grid-container img');
    if (!select) return;

    function updateSelection() {
        for (const image of images) {
            image.classList.toggle('img-selected', image.dataset.value === select.value);
        }
    }
    for (const image of images) {
        image.addEventListener('click', function () {
            select.value = image.dataset.value;
            updateSelection();
        });
    }
    select.addEventListener('change', updateSelection);
    updateSelection();
});
